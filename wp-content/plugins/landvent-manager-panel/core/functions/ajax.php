<?php

defined( 'ABSPATH' ) || exit;
/**
 * Hook: Register AJAX Actions for ZIP Download
 *
 * Registers the callback function for both logged-in and (optionally) non-logged-in users.
 * - 'wp_ajax_{action}'     -> for logged-in users
 * - 'wp_ajax_nopriv_{action}' -> for non-logged-in users (e.g., clients with links)
 *
 * @link https://developer.wordpress.org/reference/functions/add_action/
 * @link https://developer.wordpress.org/plugins/javascript/ajax/
 */
add_action('wp_ajax_wpp_download_all_documents', 'wpp_handle_download_all_documents');
//add_action('wp_ajax_nopriv_wpp_download_all_documents', 'wpp_handle_download_all_documents');

/**
 * AJAX Callback: Generate and Serve ZIP Archive of Loan Documents
 *
 * Handles the request to download all documents for a given loan ID as a ZIP file.
 * The function:
 * 1. Verifies the security nonce.
 * 2. Validates the loan ID.
 * 3. Checks if the document directory exists and contains files.
 * 4. Creates a ZIP archive using the ZipArchive class.
 * 5. Serves the file for download.
 * 6. Cleans up by deleting the temporary ZIP file.
 *
 * This function terminates script execution after sending the file or an error.
 *
 * @since 1.0.0
 *
 * @return void
 *     - On success: Sends ZIP file headers and content, then exits.
 *     - On failure: Terminates with `wp_die()` and error message.
 *
 * @uses wp_verify_nonce()         To prevent CSRF attacks.
 * @uses wp_upload_dir()           To get the correct upload directory path.
 * @uses RecursiveIteratorIterator To recursively scan directories.
 * @uses RecursiveDirectoryIterator To iterate over directory contents.
 * @uses ZipArchive                To create and manage ZIP files.
 * @uses wp_die()                  To safely terminate with an error message.
 *
 * @link https://developer.wordpress.org/plugins/security/nonces/
 * @link https://developer.wordpress.org/reference/functions/wp_upload_dir/
 * @link https://www.php.net/manual/en/class.recursiveiteratoriterator.php
 * @link https://www.php.net/manual/en/class.recursivedirectoryiterator.php
 * @link https://www.php.net/manual/en/class.ziparchive.php
 */
function wpp_handle_download_all_documents() {
	// -------------------------------
	// 1. Security: Nonce Verification
	// -------------------------------
	// Prevents cross-site request forgery (CSRF) by validating a one-time token.
	// Must match the one generated with wp_nonce_field('wpp_upload_nonce', 'nonce')
	//
	// @link https://developer.wordpress.org/plugins/security/nonces/
	if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wpp_upload_nonce')) {
		wp_die('Security check failed. Invalid or missing token.', 'Security Error', ['response' => 403]);
	}

	// -------------------------------
	// 2. Input Validation: Loan ID
	// -------------------------------
	// Ensure loan_id is provided and is a valid integer
	if (!isset($_POST['loan_id'])) {
		wp_die('Invalid request: Missing loan ID.', 'Bad Request', ['response' => 400]);
	}

	$loan_id = intval($_POST['loan_id']);
	if ($loan_id <= 0) {
		wp_die('Invalid loan ID.', 'Bad Request', ['response' => 400]);
	}

	// -------------------------------
	// 3. Locate Document Directory
	// -------------------------------
	$upload_dir = wp_upload_dir();
	$documents_dir = $upload_dir['basedir'] . '/documents/' . $loan_id . '/';

	// Check if directory exists
	if (!is_dir($documents_dir)) {
		wp_die('No documents folder found for this loan.', 'Not Found', ['response' => 404]);
	}

	// -------------------------------
	// 4. Collect Files for Archiving
	// -------------------------------
	$files = [];

	try {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($documents_dir, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ($iterator as $file) {
			if ($file->isFile()) {
				$files[] = $file->getPathname();
			}
		}
	} catch (Exception $e) {
		wp_die('Error reading document directory: ' . $e->getMessage(), 'Server Error', ['response' => 500]);
	}

	// Check if any files were found
	if (empty($files)) {
		wp_die('No documents found in the loan folder.', 'No Content', ['response' => 204]);
	}

	// -------------------------------
	// 5. Generate ZIP Archive
	// -------------------------------
	$zip_filename = 'loan_' . $loan_id . '_documents_' . date('Y-m-d_H-i-s') . '.zip';
	$zip_filepath = $upload_dir['basedir'] . '/documents/' . $loan_id . '/' . $zip_filename;

	$zip = new ZipArchive();
	$result = $zip->open($zip_filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

	if ($result !== true) {
		wp_die('Could not create ZIP archive. Error code: ' . $result, 'ZIP Error', ['response' => 500]);
	}

	foreach ($files as $file_path) {
		// Add file to ZIP with relative path (no full server path)
		$relative_path = str_replace($documents_dir, '', $file_path);
		$zip->addFile($file_path, $relative_path);
	}

	$zip->close();

	// -------------------------------
	// 6. Serve File for Download
	// -------------------------------
	// Clear any output that might have been sent
	if (ob_get_level()) {
		ob_end_clean();
	}

	// Set headers to force download
	header('Content-Type: application/zip');
	header('Content-Disposition: attachment; filename="' . basename($zip_filepath) . '"');
	header('Content-Length: ' . filesize($zip_filepath));
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');

	// Output file content
	readfile($zip_filepath);

	// -------------------------------
	// 7. Cleanup: Delete Temporary ZIP
	// -------------------------------
	// Remove the ZIP file after sending it to the user
	unlink($zip_filepath);

	// Terminate script to prevent further output
	exit;
}


/**
 * Hook: Register AJAX Actions
 *
 * Registers the callback function for both authenticated and unauthenticated users.
 * - 'wp_ajax_{action}'     -> for logged-in users
 * - 'wp_ajax_nopriv_{action}' -> for non-logged-in users (optional)
 *
 * @link https://developer.wordpress.org/reference/functions/wp_ajax_add/
 */
add_action('wp_ajax_wpp_save_brokerage', 'wpp_save_brokerage_callback');
//add_action('wp_ajax_nopriv_wpp_save_brokerage', 'wpp_save_brokerage_callback'); // Uncomment if public submission is needed

/**
 * AJAX Callback: Save Brokerage Data to Database
 *
 * Processes the brokerage form submission via AJAX, validates and sanitizes input,
 * and inserts it into the `wpp_brokers` custom table.
 *
 * This function performs the following:
 * 1. Verifies the security nonce.
 * 2. Checks user capabilities.
 * 3. Sanitizes and collects form data.
 * 4. Validates required fields.
 * 5. Inserts data into the database using $wpdb.
 * 6. Returns a JSON response (success or error).
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return void Terminates execution with `wp_send_json_success()` or `wp_send_json_error()`.
 *
 * @uses wp_verify_nonce()         For CSRF protection.
 * @uses current_user_can()       To restrict access by capability.
 * @uses sanitize_text_field()    To clean user input.
 * @uses wp_unslash()             To remove magic quotes.
 * @uses $wpdb->insert()          To safely insert data into the database.
 * @uses wp_send_json_success()   To return success response in JSON format.
 * @uses wp_send_json_error()     To return error response in JSON format.
 *
 * @link https://developer.wordpress.org/plugins/javascript/ajax/
 * @link https://developer.wordpress.org/reference/functions/wp_verify_nonce/
 * @link https://developer.wordpress.org/reference/functions/current_user_can/
 * @link https://developer.wordpress.org/reference/functions/sanitize_text_field/
 * @link https://developer.wordpress.org/reference/functions/wp_unslash/
 * @link https://developer.wordpress.org/reference/classes/wpdb/insert/
 * @link https://developer.wordpress.org/reference/functions/wp_send_json_success/
 * @link https://developer.wordpress.org/reference/functions/wp_send_json_error/
 */
function wpp_save_brokerage_callback() {
	global $wpdb;

	// -------------------------------
	// 1. Security: Nonce Verification
	// -------------------------------
	// Nonce (Number Used Once) prevents CSRF attacks.
	// Must match the one generated with wp_nonce_field('wpp_brokerage_nonce', '_ajax_nonce')
	//
	// @link https://developer.wordpress.org/plugins/security/nonces/
	if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_ajax_nonce'])), 'wpp_brokerage_nonce')) {
		wp_send_json_error([
			'message' => 'Invalid or missing security token (nonce).',
			'code'    => 'invalid_nonce'
		], 403);
	}

	// -------------------------------
	// 2. Authorization: User Capability Check
	// -------------------------------
	// Restrict access to users with 'manage_options' capability (typically administrators).
	// Change to 'edit_posts', 'publish_pages', etc., if needed.
	//
	// @link https://developer.wordpress.org/plugins/users/capabilities/
	if (!current_user_can('manage_options')) {
		wp_send_json_error([
			'message' => 'You do not have permission to perform this action.',
			'code'    => 'insufficient_permissions'
		], 403);
	}

	// -------------------------------
	// 3. Data Collection & Sanitization
	// -------------------------------
	// Define allowed fields to prevent unwanted data insertion
	$allowed_fields = [
		'brok_brokerage_name',
		'brok_parent_brokerage',
		'brok_address',
		'brok_city',
		'brok_county',
		'brok_state',
		'brok_zip_code',
		'brok_broker_bdm'
	];

	$data = [];

	foreach ($allowed_fields as $field) {
		if (isset($_POST[$field])) {
			// Sanitize input: remove slashes and clean string
			$data[$field] = sanitize_text_field(wp_unslash($_POST[$field]));
		}
	}

	// -------------------------------
	// 4. Validation
	// -------------------------------
	// Ensure required fields are not empty
	if (empty($data['brok_brokerage_name'])) {
		wp_send_json_error([
			'message' => 'Brokerage name is required.',
			'field'   => 'brok_brokerage_name',
			'code'    => 'missing_required_field'
		]);
	}

	// Optional: Add more validation (e.g., ZIP format, email, etc.)

	// -------------------------------
	// 5. Database Insertion
	// -------------------------------
	$table_name = $wpdb->prefix . 'wpp_brokers';

	// Insert data into the database
	// Format: '%s' = string, '%d' = integer, '%f' = float
	// Since all fields are strings, we use '%s' for all
	$insert_result = $wpdb->insert(
		$table_name,
		$data,
		array_fill(0, count($data), '%s') // Apply '%s' format to each value
	);

	// Check for database error
	if ($insert_result === false) {
		wp_send_json_error([
			'message' => 'Database error occurred.',
			'error'   => $wpdb->last_error,
			'code'    => 'db_insert_failed'
		], 500);
	}

	$inserted_id = $wpdb->insert_id;

	// -------------------------------
	// 6. Success Response
	// -------------------------------
	// Return success response with inserted ID and confirmation message
	wp_send_json_success([
		'message' => 'Brokerage has been successfully added.',
		'id'      => $inserted_id,
		'data'    => $data // Optionally return sanitized data
	], 200);
}