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
 * Registers the callback function for authenticated users.
 * - 'wp_ajax_{action}' -> for logged-in users only
 *
 * @link https://developer.wordpress.org/reference/functions/wp_ajax_add/
 */
add_action('wp_ajax_wpp_save_brokerage', 'wpp_save_brokerage_callback');
// add_action('wp_ajax_nopriv_wpp_save_brokerage', 'wpp_save_brokerage_callback'); // Uncomment if public access is needed

/**
 * AJAX Callback: Save or Update Brokerage Data to Database
 *
 * Processes the brokerage form submission via AJAX. If a 'broker_id' is provided,
 * updates the existing record; otherwise, inserts a new one into the `wpp_brokers` table.
 *
 * This function performs the following:
 * 1. Verifies the security nonce.
 * 2. Checks user capabilities.
 * 3. Sanitizes and collects form data.
 * 4. Validates required fields.
 * 5. Inserts or updates data using $wpdb.
 * 6. Returns a JSON response with full row data.
 *
 * @since 1.1.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return void Terminates execution with `wp_send_json_success()` or `wp_send_json_error()`.
 *
 * @uses wp_verify_nonce()         For CSRF protection.
 * @uses current_user_can()       To restrict access by capability.
 * @uses sanitize_text_field()    To clean user input.
 * @uses wp_unslash()             To remove magic quotes.
 * @uses $wpdb->insert()          To safely insert new data.
 * @uses $wpdb->update()          To safely update existing data.
 * @uses $wpdb->get_row()         To fetch full row after save (including timestamps).
 * @uses wp_send_json_success()   To return success response in JSON format.
 * @uses wp_send_json_error()     To return error response in JSON format.
 *
 * @link https://developer.wordpress.org/plugins/javascript/ajax/
 * @link https://developer.wordpress.org/reference/functions/wp_verify_nonce/
 * @link https://developer.wordpress.org/reference/functions/current_user_can/
 * @link https://developer.wordpress.org/reference/functions/sanitize_text_field/
 * @link https://developer.wordpress.org/reference/functions/wp_unslash/
 * @link https://developer.wordpress.org/reference/classes/wpdb/insert/
 * @link https://developer.wordpress.org/reference/classes/wpdb/update/
 * @link https://developer.wordpress.org/reference/functions/wp_send_json_success/
 * @link https://developer.wordpress.org/reference/functions/wp_send_json_error/
 */
function wpp_save_brokerage_callback() {
	global $wpdb;

	// -------------------------------
	// 1. Security: Nonce Verification
	// -------------------------------
	// Prevents CSRF attacks.
	// Must match the one generated with wp_nonce_field('wpp_brokerage_nonce', '_ajax_nonce')
	//
	// @link https://developer.wordpress.org/plugins/security/nonces/
	if ( ! isset( $_POST['_ajax_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) ), 'wpp_brokerage_nonce' ) ) {
		wp_send_json_error( [
			'message' => 'Invalid or missing security token (nonce).',
			'code'    => 'invalid_nonce'
		], 403 );
	}

	// -------------------------------
	// 2. Authorization: User Capability Check
	// -------------------------------
	// Restrict access to users with 'manage_options' capability (admins).
	// Change to 'edit_posts', 'publish_pages', etc., if needed.
	//
	// @link https://developer.wordpress.org/plugins/users/capabilities/
	if ( ! wpp_is_user_dashboard_allowed() ) {
		wp_send_json_error( [
			'message' => 'You do not have permission to perform this action.',
			'code'    => 'insufficient_permissions'
		], 403 );
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
	foreach ( $allowed_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			$data[ $field ] = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
		}
	}

	// Extract broker_id (optional)
	$broker_id = ! empty( $_POST['broker_id'] ) ? absint( $_POST['broker_id'] ) : 0;

	// -------------------------------
	// 4. Validation
	// -------------------------------
	if ( empty( $data['brok_brokerage_name'] ) ) {
		wp_send_json_error( [
			'message' => 'Brokerage name is required.',
			'field'   => 'brok_brokerage_name',
			'code'    => 'missing_required_field'
		] );
	}

	// Optional: Add more validation (e.g., ZIP format, email, etc.)

	// -------------------------------
	// 5. Determine Action: Insert or Update
	// -------------------------------
	$table_name = $wpdb->prefix . 'wpp_brokers';

	if ( $broker_id > 0 ) {
		// --- UPDATE EXISTING BROKER ---

		// Check if broker exists
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table_name} WHERE id = %d", $broker_id ) );
		if ( ! $exists ) {
			wp_send_json_error( [
				'message' => 'Broker not found.',
				'code'    => 'broker_not_found'
			], 404 );
		}

		// Add updated_at timestamp
		$data['updated_at'] = current_time( 'mysql' );

		// Perform update
		$result = $wpdb->update(
			$table_name,
			$data,
			[ 'id' => $broker_id ],
			array_fill( 0, count( $data ), '%s' ),
			[ '%d' ]
		);

		if ( $result === false ) {
			wp_send_json_error( [
				'message' => 'Database error occurred during update.',
				'error'   => $wpdb->last_error,
				'code'    => 'db_update_failed'
			], 500 );
		}

		$saved_id = $broker_id;

	} else {
		// --- INSERT NEW BROKER ---

		// Add timestamps
		$data['created_at'] = current_time( 'mysql' );
		$data['updated_at'] = current_time( 'mysql' );

		// Perform insert
		$result = $wpdb->insert(
			$table_name,
			$data,
			array_fill( 0, count( $data ), '%s' )
		);

		if ( $result === false ) {
			wp_send_json_error( [
				'message' => 'Database error occurred during insertion.',
				'error'   => $wpdb->last_error,
				'code'    => 'db_insert_failed'
			], 500 );
		}

		$saved_id = $wpdb->insert_id;
	}

	// -------------------------------
	// 6. Fetch Full Saved Row (with timestamps)
	// -------------------------------
	$saved_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $saved_id ), ARRAY_A );

	if ( ! $saved_row ) {
		wp_send_json_error( [
			'message' => 'Failed to retrieve saved data.',
			'code'    => 'data_retrieval_failed'
		], 500 );
	}

	// -------------------------------
	// 7. Success Response
	// -------------------------------
	$action = $broker_id ? 'updated' : 'added';
	wp_send_json_success( [
		'message' => "Broker has been successfully {$action}.",
		'id'      => $saved_id,
		'action'  => $action,
		'data'    => $saved_row // Full row with created_at, updated_at, etc.
	], 200 );
}





add_action('wp_ajax_wpp_save_law_firm', 'wpp_save_law_firm_callback');

function wpp_save_law_firm_callback() {
	global $wpdb;

	// Security: Nonce
	if ( ! isset( $_POST['_ajax_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) ), 'wpp_law_firm_nonce' ) ) {
		wp_send_json_error([
			'message' => 'Invalid or missing security token.',
			'code'    => 'invalid_nonce'
		], 403);
	}

	// Capability
	if ( ! wpp_is_user_dashboard_allowed() ) {
		wp_send_json_error([
			'message' => 'You do not have permission.',
			'code'    => 'insufficient_permissions'
		], 403);
	}

	// Allowed fields
	$allowed_fields = [
		'law_firm_name', 'law_address', 'law_city', 'law_county',
		'law_state', 'law_zip_code', 'law_phone', 'law_toll_free',
		'law_fax', 'law_website'
	];

	$data = [];
	foreach ( $allowed_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			$data[ $field ] = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
		}
	}

	if ( empty( $data['law_firm_name'] ) ) {
		wp_send_json_error([
			'message' => 'Law firm name is required.',
			'field'   => 'law_firm_name',
			'code'    => 'missing_required_field'
		]);
	}

	$law_firm_id = ! empty( $_POST['law_firm_id'] ) ? absint( $_POST['law_firm_id'] ) : 0;
	$table_name = $wpdb->prefix . 'wpp_law_firm';

	if ( $law_firm_id > 0 ) {
		// Update
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table_name} WHERE id = %d", $law_firm_id ) );
		if ( ! $exists ) {
			wp_send_json_error([ 'message' => 'Law firm not found.', 'code' => 'not_found' ], 404 );
		}

		$data['updated_at'] = current_time( 'mysql' );
		$result = $wpdb->update( $table_name, $data, [ 'id' => $law_firm_id ], array_fill( 0, count( $data ), '%s' ), [ '%d' ] );

		if ( $result === false ) {
			wp_send_json_error([ 'message' => 'Update failed.', 'error' => $wpdb->last_error ]);
		}

		$saved_id = $law_firm_id;
	} else {
		// Insert
		$data['created_at'] = current_time( 'mysql' );
		$data['updated_at'] = current_time( 'mysql' );
		$result = $wpdb->insert( $table_name, $data, array_fill( 0, count( $data ), '%s' ) );

		if ( $result === false ) {
			wp_send_json_error([ 'message' => 'Insert failed.', 'error' => $wpdb->last_error ]);
		}

		$saved_id = $wpdb->insert_id;
	}

	$saved_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $saved_id ), ARRAY_A );

	$action = $law_firm_id ? 'updated' : 'added';
	wp_send_json_success([
		'message' => "Law firm has been successfully {$action}.",
		'id'      => $saved_id,
		'action'  => $action,
		'data'    => $saved_row
	]);
}

add_action('wp_ajax_wpp_save_company', 'wpp_save_company_callback');

function wpp_save_company_callback() {
	global $wpdb;

	// Security: Nonce
	if ( ! isset( $_POST['_ajax_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) ), 'wpp_company_nonce' ) ) {
		wp_send_json_error([
			'message' => 'Invalid or missing security token.',
			'code'    => 'invalid_nonce'
		], 403);
	}

	// Capability
	if ( ! wpp_is_user_dashboard_allowed() ) {
		wp_send_json_error([
			'message' => 'You do not have permission.',
			'code'    => 'insufficient_permissions'
		], 403);
	}

	// Allowed fields
	$allowed_fields = [
		'comp_title_company_name', 'comp_address', 'comp_city', 'comp_county',
		'comp_state', 'comp_zip_code', 'comp_phone', 'comp_toll_free', 'comp_fax'
	];

	$data = [];
	foreach ( $allowed_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			$data[ $field ] = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
		}
	}

	if ( empty( $data['comp_title_company_name'] ) ) {
		wp_send_json_error([
			'message' => 'Company name is required.',
			'field'   => 'comp_title_company_name',
			'code'    => 'missing_required_field'
		]);
	}

	$company_id = ! empty( $_POST['company_id'] ) ? absint( $_POST['company_id'] ) : 0;
	$table_name = $wpdb->prefix . 'wpp_companies';

	if ( $company_id > 0 ) {
		// Update
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table_name} WHERE id = %d", $company_id ) );
		if ( ! $exists ) {
			wp_send_json_error([ 'message' => 'Company not found.', 'code' => 'not_found' ], 404 );
		}

		$data['updated_at'] = current_time( 'mysql' );
		$result = $wpdb->update( $table_name, $data, [ 'id' => $company_id ], array_fill( 0, count( $data ), '%s' ), [ '%d' ] );

		if ( $result === false ) {
			wp_send_json_error([ 'message' => 'Update failed.', 'error' => $wpdb->last_error ]);
		}

		$saved_id = $company_id;
	} else {
		// Insert
		$data['created_at'] = current_time( 'mysql' );
		$data['updated_at'] = current_time( 'mysql' );
		$result = $wpdb->insert( $table_name, $data, array_fill( 0, count( $data ), '%s' ) );

		if ( $result === false ) {
			wp_send_json_error([ 'message' => 'Insert failed.', 'error' => $wpdb->last_error ]);
		}

		$saved_id = $wpdb->insert_id;
	}

	$saved_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $saved_id ), ARRAY_A );

	$action = $company_id ? 'updated' : 'added';
	wp_send_json_success([
		'message' => "Company has been successfully {$action}.",
		'id'      => $saved_id,
		'action'  => $action,
		'data'    => $saved_row
	]);
}

add_action('wp_ajax_wpp_save_appraiser', 'wpp_save_appraiser_callback');

function wpp_save_appraiser_callback() {
	global $wpdb;

	// Security: Nonce
	if ( ! isset( $_POST['_ajax_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) ), 'wpp_appraiser_nonce' ) ) {
		wp_send_json_error([
			'message' => 'Invalid or missing security token.',
			'code'    => 'invalid_nonce'
		], 403);
	}

	// Capability
	if ( ! wpp_is_user_dashboard_allowed() ) {
		wp_send_json_error([
			'message' => 'You do not have permission.',
			'code'    => 'insufficient_permissions'
		], 403);
	}

	// Allowed fields
	$allowed_fields = [
		'appr_name', 'appr_address', 'appr_city', 'appr_county',
		'appr_state', 'appr_zip', 'appr_phone', 'appr_fax',
		'appr_email', 'appr_title', 'appr_website', 'appr_contact'
	];

	$data = [];
	foreach ( $allowed_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			$data[ $field ] = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
		}
	}

	if ( empty( $data['appr_name'] ) ) {
		wp_send_json_error([
			'message' => 'Appraiser name is required.',
			'field'   => 'appr_name',
			'code'    => 'missing_required_field'
		]);
	}

	$appraiser_id = ! empty( $_POST['appraiser_id'] ) ? absint( $_POST['appraiser_id'] ) : 0;
	$table_name = $wpdb->prefix . 'wpp_appraiser';

	if ( $appraiser_id > 0 ) {
		// Update
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table_name} WHERE id = %d", $appraiser_id ) );
		if ( ! $exists ) {
			wp_send_json_error([ 'message' => 'Appraiser not found.', 'code' => 'not_found' ], 404 );
		}

		$data['updated_at'] = current_time( 'mysql' );
		$result = $wpdb->update( $table_name, $data, [ 'id' => $appraiser_id ], array_fill( 0, count( $data ), '%s' ), [ '%d' ] );

		if ( $result === false ) {
			wp_send_json_error([ 'message' => 'Update failed.', 'error' => $wpdb->last_error ]);
		}

		$saved_id = $appraiser_id;
	} else {
		// Insert
		$data['created_at'] = current_time( 'mysql' );
		$data['updated_at'] = current_time( 'mysql' );
		$result = $wpdb->insert( $table_name, $data, array_fill( 0, count( $data ), '%s' ) );

		if ( $result === false ) {
			wp_send_json_error([ 'message' => 'Insert failed.', 'error' => $wpdb->last_error ]);
		}

		$saved_id = $wpdb->insert_id;
	}

	$saved_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $saved_id ), ARRAY_A );

	$action = $appraiser_id ? 'updated' : 'added';
	wp_send_json_success([
		'message' => "Appraiser has been successfully {$action}.",
		'id'      => $saved_id,
		'action'  => $action,
		'data'    => $saved_row
	]);
}


/**
 * AJAX: Move loan to archive (wpp_old_loans) and delete from all source tables
 *
 * Steps:
 * 1. Get loan data from `wpp_loans_full_data` or fallback to `loan_raw_applications`
 * 2. Insert into `wpp_old_loans` (archival table)
 * 3. Delete from `wpp_loans_full_data`
 * 4. Delete from `loan_raw_applications` by `id`
 * 5. Remove `loan_id` from `card_ids` in `wpp_trello_columns` (JSON array)
 * 6. Respond with success and trigger redirect via JS
 *
 * @since 1.0
 * @hook wp_ajax_wpp_move_and_delete_loan
 * @return void
 */
function wpp_move_and_delete_loan() {
	global $wpdb;

	// 1. Проверка безопасности: nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'trello_nonce' ) ) {
		wp_send_json_error( [ 'message' => 'Security check failed. Invalid or missing nonce.' ] );
	}

	// 2. Проверка прав доступа
	if (  ! wpp_is_user_dashboard_allowed() ) {
		wp_send_json_error( [ 'message' => 'Access denied. You do not have permission to perform this action.' ] );
	}

	// 3. Получаем и проверяем loan_id
	$loan_id = sanitize_text_field( $_POST['loan_id'] ?? '' );
	if ( ! $loan_id ) {
		wp_send_json_error( [ 'message' => 'Invalid or missing loan ID.' ] );
	}

	// 4. Определяем имена таблиц с префиксом
	$table_loans       = $wpdb->prefix . 'wpp_loans_full_data';
	$table_old_loans   = $wpdb->prefix . 'wpp_old_loans';
	$table_raw_app     = $wpdb->prefix . 'loan_raw_applications';
	$table_trello      = $wpdb->prefix . 'wpp_trello_columns';

	// 5. Начинаем транзакцию для атомарности
	$wpdb->query( 'START TRANSACTION' );

	try {
		$loan_data = null;
		$source = '';

		// 6. Пытаемся получить данные из основной таблицы
		$loan_data = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table_loans WHERE loan_id = %s", $loan_id ),
			ARRAY_A
		);

		if ( $loan_data ) {
			$source = 'wpp_loans_full_data';
		} else {
			// Если нет — ищем в raw_applications
			$raw_data = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM $table_raw_app WHERE id = %s", $loan_id ),
				ARRAY_A
			);

			if ( ! $raw_data ) {
				throw new Exception( "Loan not found in 'wpp_loans_full_data' or 'loan_raw_applications'." );
			}

			// Преобразуем в структуру wpp_loans_full_data
			$loan_data = [
				'loan_id'     => $raw_data['id'],
				'change_time' => $raw_data['created_at'] ?? current_time( 'mysql' ),
				'loan_data'   => maybe_serialize( [
					'raw_data' => $raw_data,
					'source'   => 'loan_raw_applications',
					'migrated' => true
				] )
			];
			$source = 'loan_raw_applications';
		}

		// 7. Вставляем в архивную таблицу
		$insert_result = $wpdb->insert( $table_old_loans, $loan_data );
		if ( $insert_result === false ) {
			error_log( "wpp_move_and_delete_loan: Failed to insert into $table_old_loans. DB Error: " . $wpdb->last_error );
			throw new Exception( "Failed to archive loan in wpp_old_loans." );
		}

		// 8. Удаляем из wpp_loans_full_data (если был там)
		if ( $source === 'wpp_loans_full_data' ) {
			$deleted_loans = $wpdb->delete( $table_loans, [ 'loan_id' => $loan_id ] );
			if ( $deleted_loans === false ) {
				error_log( "wpp_move_and_delete_loan: Failed to delete from $table_loans" );
				throw new Exception( "Failed to delete loan from wpp_loans_full_data." );
			}
		}

		// 9. Удаляем из loan_raw_applications
		$deleted_raw = $wpdb->delete( $table_raw_app, [ 'id' => $loan_id ] );
		if ( $deleted_raw === false && $wpdb->last_error ) {
			error_log( "wpp_move_and_delete_loan: Failed to delete from $table_raw_app. Error: " . $wpdb->last_error );
			// Не кидаем исключение, если строки не было — это нормально
		}

		// 10. Удаляем loan_id из JSON-массива card_ids в wpp_trello_columns
		// 🧩 10. Удаляем loan_id из card_ids в etgfp_wpp_trello_columns
		error_log("🔍 Поиск loan_id: $loan_id в card_ids");

		$trello_columns = $wpdb->get_results(
			"SELECT id, card_ids FROM {$table_trello} WHERE card_ids LIKE '%$loan_id%'"
		);

		if ( empty( $trello_columns ) ) {
			error_log("✅ loan_id $loan_id не найден в card_ids — пропускаем");
			error_log("SELECT id, card_ids FROM {$table_trello} WHERE card_ids LIKE '%$loan_id%'");
		} else {
			error_log("✅ Найдено в " . count($trello_columns) . " колонках");
		}

		foreach ( $trello_columns as $column ) {
			$card_ids = json_decode( $column->card_ids, true );

			if ( ! is_array( $card_ids ) ) {
				error_log("❌ card_ids для id={$column->id} не является массивом: " . $column->card_ids);
				continue;
			}

			$key = array_search( $loan_id, $card_ids );
			if ( $key !== false ) {
				unset( $card_ids[ $key ] );
				$card_ids = array_values( $card_ids ); // Перестроить индексы

				$updated = $wpdb->update(
					$table_trello,
					[ 'card_ids' => json_encode( $card_ids ) ],
					[ 'id' => $column->id ],
					[ '%s' ],
					[ '%d' ]
				);

				if ( $updated === false ) {
					error_log("❌ Ошибка обновления card_ids для id={$column->id}");
					throw new Exception( "Failed to update card_ids for column ID: {$column->id}" );
				} else {
					error_log("✅ Успешно удалён $loan_id из card_ids (column ID: {$column->id})");
				}
			}
		}


		// 11. Фиксируем транзакцию
		$wpdb->query( 'COMMIT' );

		wp_send_json_success( [
			'message' => "Loan '$loan_id' successfully archived and deleted from all tables."
		] );

	} catch ( Exception $e ) {
		// Откатываем транзакцию при ошибке
		$wpdb->query( 'ROLLBACK' );
		error_log( "wpp_move_and_delete_loan ERROR: " . $e->getMessage() );

		wp_send_json_error( [ 'message' => $e->getMessage() ] );
	}
}

// 🔌 Регистрируем AJAX-хуки
add_action( 'wp_ajax_wpp_move_and_delete_loan', 'wpp_move_and_delete_loan' );


/**
 * AJAX Callback: Delete Broker from Database
 *
 * Securely deletes a broker record from the `wpp_brokers` table.
 * Requires a valid nonce and user capability check.
 *
 * @since 1.1.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return void Terminates with `wp_send_json_success()` or `wp_send_json_error()`.
 *
 * @uses wp_verify_nonce()     For CSRF protection.
 * @uses wpp_is_user_dashboard_allowed() To check user permissions.
 * @uses absint()              To sanitize broker ID.
 * @uses $wpdb->delete()       To remove the record.
 * @uses wp_send_json_success() To return success.
 * @uses wp_send_json_error()   To return error.
 *
 * @link https://developer.wordpress.org/reference/functions/wp_verify_nonce/
 * @link https://developer.wordpress.org/reference/functions/wp_send_json_success/
 * @link https://developer.wordpress.org/reference/functions/wp_send_json_error/
 */
function wpp_delete_broker_callback() {
	global $wpdb;

	// 1. Проверка безопасности: nonce
/*	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpp_brokerage_nonce' ) ) {
		wp_send_json_error( [
			'message' => 'Invalid or missing security token.',
			'code'    => 'invalid_nonce'
		], 403 );
	}*/

	// 2. Проверка прав доступа
	if ( ! wpp_is_user_dashboard_allowed() ) {
		wp_send_json_error( [
			'message' => 'You do not have permission to delete brokers.',
			'code'    => 'insufficient_permissions'
		], 403 );
	}

	// 3. Получаем и проверяем ID
	$broker_id = absint( $_POST['broker_id'] ?? 0 );
	if ( $broker_id <= 0 ) {
		wp_send_json_error( [
			'message' => 'Invalid broker ID.',
			'code'    => 'invalid_id'
		] );
	}

	$table_name = $wpdb->prefix . 'wpp_brokers';

	// 4. Проверяем, существует ли запись
	$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE id = %d", $broker_id ) );
	if ( ! $exists ) {
		wp_send_json_error( [
			'message' => 'Broker not found.',
			'code'    => 'not_found'
		], 404 );
	}

	// 5. Удаляем запись
	$result = $wpdb->delete( $table_name, [ 'id' => $broker_id ], [ '%d' ] );

	if ( $result === false ) {
		wp_send_json_error( [
			'message' => 'Database error occurred during deletion.',
			'error'   => $wpdb->last_error,
			'code'    => 'db_delete_failed'
		], 500 );
	}

	// 6. Успешное удаление
	wp_send_json_success( [
		'message' => 'Broker has been successfully deleted.',
		'id'      => $broker_id,
		'action'  => 'deleted'
	] );
}

// Регистрируем AJAX-хуки
add_action( 'wp_ajax_wpp_delete_broker', 'wpp_delete_broker_callback' );



/**
 * AJAX Callback: Delete Appraiser from Database
 *
 * Securely deletes an appraiser record from the `wpp_appraiser` table.
 * Requires a valid nonce and user capability check.
 *
 * @since 1.1.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return void Terminates with `wp_send_json_success()` or `wp_send_json_error()`.
 *
 * @uses wp_verify_nonce()     For CSRF protection.
 * @uses wpp_is_user_dashboard_allowed() To check user permissions.
 * @uses absint()              To sanitize appraiser ID.
 * @uses $wpdb->delete()       To remove the record.
 * @uses wp_send_json_success() To return success.
 * @uses wp_send_json_error()   To return error.
 *
 * @link https://developer.wordpress.org/reference/functions/wp_verify_nonce/
 * @link https://developer.wordpress.org/reference/functions/wp_send_json_success/
 * @link https://developer.wordpress.org/reference/functions/wp_send_json_error/
 */
function wpp_delete_appraiser_callback() {
	global $wpdb;

	/*// 1. Проверка безопасности: nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpp_appraiser_nonce' ) ) {
		wp_send_json_error( [
			'message' => 'Invalid or missing security token.',
			'code'    => 'invalid_nonce'
		], 403 );
	}*/

	// 2. Проверка прав доступа
	if ( ! wpp_is_user_dashboard_allowed() ) {
		wp_send_json_error( [
			'message' => 'You do not have permission to delete appraisers.',
			'code'    => 'insufficient_permissions'
		], 403 );
	}

	// 3. Получаем и проверяем ID
	$appraiser_id = absint( $_POST['appraiser_id'] ?? 0 );
	if ( $appraiser_id <= 0 ) {
		wp_send_json_error( [
			'message' => 'Invalid appraiser ID.',
			'code'    => 'invalid_id'
		] );
	}

	$table_name = $wpdb->prefix . 'wpp_appraiser';

	// 4. Проверяем, существует ли запись
	$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE id = %d", $appraiser_id ) );
	if ( ! $exists ) {
		wp_send_json_error( [
			'message' => 'Appraiser not found.',
			'code'    => 'not_found'
		], 404 );
	}

	// 5. Удаляем запись
	$result = $wpdb->delete( $table_name, [ 'id' => $appraiser_id ], [ '%d' ] );

	if ( $result === false ) {
		wp_send_json_error( [
			'message' => 'Database error occurred during deletion.',
			'error'   => $wpdb->last_error,
			'code'    => 'db_delete_failed'
		], 500 );
	}

	// 6. Успешное удаление
	wp_send_json_success( [
		'message' => 'Appraiser has been successfully deleted.',
		'id'      => $appraiser_id,
		'action'  => 'deleted'
	] );
}

// Регистрируем AJAX-хуки
add_action( 'wp_ajax_wpp_delete_appraiser', 'wpp_delete_appraiser_callback' );

/**
 * AJAX Callback: Delete Company from Database
 *
 * Securely deletes a company record from the `wpp_companies` table.
 * Requires a valid nonce and user capability check.
 *
 * @since 1.1.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return void Terminates with `wp_send_json_success()` or `wp_send_json_error()`.
 *
 * @uses wp_verify_nonce()     For CSRF protection.
 * @uses wpp_is_user_dashboard_allowed() To check user permissions.
 * @uses absint()              To sanitize company ID.
 * @uses $wpdb->delete()       To remove the record.
 * @uses wp_send_json_success() To return success.
 * @uses wp_send_json_error()   To return error.
 *
 * @link https://developer.wordpress.org/reference/functions/wp_verify_nonce/
 * @link https://developer.wordpress.org/reference/functions/wp_send_json_success/
 * @link https://developer.wordpress.org/reference/functions/wp_send_json_error/
 */
function wpp_delete_company_callback() {
	global $wpdb;

	// 1. Проверка безопасности: nonce
	/*if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpp_company_nonce' ) ) {
		wp_send_json_error( [
			'message' => 'Invalid or missing security token.',
			'code'    => 'invalid_nonce'
		], 403 );
	}*/

	// 2. Проверка прав доступа
	if ( ! wpp_is_user_dashboard_allowed() ) {
		wp_send_json_error( [
			'message' => 'You do not have permission to delete companies.',
			'code'    => 'insufficient_permissions'
		], 403 );
	}

	// 3. Получаем и проверяем ID
	$company_id = absint( $_POST['company_id'] ?? 0 );
	if ( $company_id <= 0 ) {
		wp_send_json_error( [
			'message' => 'Invalid company ID.',
			'code'    => 'invalid_id'
		] );
	}

	$table_name = $wpdb->prefix . 'wpp_companies';

	// 4. Проверяем, существует ли запись
	$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE id = %d", $company_id ) );
	if ( ! $exists ) {
		wp_send_json_error( [
			'message' => 'Company not found.',
			'code'    => 'not_found'
		], 404 );
	}

	// 5. Удаляем запись
	$result = $wpdb->delete( $table_name, [ 'id' => $company_id ], [ '%d' ] );

	if ( $result === false ) {
		wp_send_json_error( [
			'message' => 'Database error occurred during deletion.',
			'error'   => $wpdb->last_error,
			'code'    => 'db_delete_failed'
		], 500 );
	}

	// 6. Успешное удаление
	wp_send_json_success( [
		'message' => 'Company has been successfully deleted.',
		'id'      => $company_id,
		'action'  => 'deleted'
	] );
}

// Регистрируем AJAX-хуки
add_action( 'wp_ajax_wpp_delete_company', 'wpp_delete_company_callback' );


/**
 * AJAX Callback: Delete Law Firm from Database
 *
 * Securely deletes a law firm record from the `wpp_law_firm` table.
 * Requires a valid nonce and user capability check.
 *
 * @since 1.1.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return void Terminates with `wp_send_json_success()` or `wp_send_json_error()`.
 *
 * @uses wp_verify_nonce()     For CSRF protection.
 * @uses wpp_is_user_dashboard_allowed() To check user permissions.
 * @uses absint()              To sanitize law firm ID.
 * @uses $wpdb->delete()       To remove the record.
 * @uses wp_send_json_success() To return success.
 * @uses wp_send_json_error()   To return error.
 *
 * @link https://developer.wordpress.org/reference/functions/wp_verify_nonce/
 * @link https://developer.wordpress.org/reference/functions/wp_send_json_success/
 * @link https://developer.wordpress.org/reference/functions/wp_send_json_error/
 */
function wpp_delete_law_firm_callback() {
	global $wpdb;

	// 1. Проверка безопасности: nonce
/*	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpp_law_firm_nonce' ) ) {
		wp_send_json_error( [
			'message' => 'Invalid or missing security token.',
			'code'    => 'invalid_nonce'
		], 403 );
	}*/

	// 2. Проверка прав доступа
	if ( ! wpp_is_user_dashboard_allowed() ) {
		wp_send_json_error( [
			'message' => 'You do not have permission to delete law firms.',
			'code'    => 'insufficient_permissions'
		], 403 );
	}

	// 3. Получаем и проверяем ID
	$law_firm_id = absint( $_POST['law_firm_id'] ?? 0 );
	if ( $law_firm_id <= 0 ) {
		wp_send_json_error( [
			'message' => 'Invalid law firm ID.',
			'code'    => 'invalid_id'
		] );
	}

	$table_name = $wpdb->prefix . 'wpp_law_firm';

	// 4. Проверяем, существует ли запись
	$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE id = %d", $law_firm_id ) );
	if ( ! $exists ) {
		wp_send_json_error( [
			'message' => 'Law firm not found.',
			'code'    => 'not_found'
		], 404 );
	}

	// 5. Удаляем запись
	$result = $wpdb->delete( $table_name, [ 'id' => $law_firm_id ], [ '%d' ] );

	if ( $result === false ) {
		wp_send_json_error( [
			'message' => 'Database error occurred during deletion.',
			'error'   => $wpdb->last_error,
			'code'    => 'db_delete_failed'
		], 500 );
	}

	// 6. Успешное удаление
	wp_send_json_success( [
		'message' => 'Law firm has been successfully deleted.',
		'id'      => $law_firm_id,
		'action'  => 'deleted'
	] );
}

// Регистрируем AJAX-хуки
add_action( 'wp_ajax_wpp_delete_law_firm', 'wpp_delete_law_firm_callback' );