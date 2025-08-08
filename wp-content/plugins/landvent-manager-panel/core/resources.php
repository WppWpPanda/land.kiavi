<?php
/**
 * Enqueue Plugin Styles and Scripts
 *
 * Registers and loads CSS and JavaScript assets for the LandVent Manager Panel plugin
 * on the frontend of the site. This includes Font Awesome, custom styles, and interactive scripts.
 * It also localizes JavaScript variables for AJAX communication.
 *
 * @package LandVent_Manager_Panel
 * @subpackage Assets
 * @since 1.0.0
 */

// Prevent direct access
defined( 'ABSPATH' ) || exit;

/**
 * Enqueue Frontend Styles and Scripts
 *
 * This function is hooked to `wp_enqueue_scripts` and loads:
 * - Font Awesome (from CDN)
 * - Main plugin stylesheet
 * - Trello-style UI stylesheet
 * - JavaScript for Trello board interactivity (with jQuery UI Sortable)
 * - Frontend utility script
 * - Localized AJAX variables for secure JavaScript-to-PHP communication
 *
 * Versioning is handled using file modification time (`filemtime`) to bust cache
 * when assets are updated. Falls back to current timestamp if file not found.
 *
 * @return void
 *
 * @since 1.0.0
 *
 * @uses wp_enqueue_style()     To register CSS files.
 * @uses wp_enqueue_script()    To register JavaScript files.
 * @uses wp_localize_script()   To pass PHP variables (e.g., AJAX URL) to JS.
 * @uses admin_url()            To generate the admin-ajax.php URL.
 * @uses wp_create_nonce()      To generate a security token for AJAX requests.
 * @uses file_exists()          To check if asset file exists.
 * @uses filemtime()            To get file modification time for cache-busting.
 *
 * @link https://developer.wordpress.org/reference/functions/wp_enqueue_style/
 * @link https://developer.wordpress.org/reference/functions/wp_enqueue_script/
 * @link https://developer.wordpress.org/reference/functions/wp_localize_script/
 * @link https://developer.wordpress.org/reference/functions/admin_url/
 * @link https://developer.wordpress.org/reference/functions/wp_create_nonce/
 */
function wpp_enqueue_Loan_styles() {

	global $loan_id;

	// -------------------------------
	// 1. Load Font Awesome from CDN
	// -------------------------------
	// Font Awesome provides scalable vector icons used across the UI.
	// Loaded from CDN for performance and ease of use.
	//
	// Note: There's a typo in the URL (extra space) — fixed below.
	wp_enqueue_style(
		'font-awesome',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', // Removed trailing space
		array(),
		'5.15.4'
	);

	// -------------------------------
	// 2. Main Plugin Stylesheet
	// -------------------------------
	wp_enqueue_style(
		'landvent-manager-panel', // Handle name
		WPP_LOAN_MANAGER_URL . 'assets/css/landvent-manager-panel.css', // Source URL
		array(), // No dependencies
		file_exists( WPP_LOAN_MANAGER_PATH . 'assets/css/landvent-manager-panel.css' )
			? filemtime( WPP_LOAN_MANAGER_PATH . 'assets/css/landvent-manager-panel.css' ) // Version: file change time
			: time() // Fallback: current timestamp
	);

	// -------------------------------
	// 3. Trello-Style UI Stylesheet
	// -------------------------------
	wp_enqueue_style(
		'trello-style',
		WPP_LOAN_MANAGER_URL . 'assets/css/trello-style.css',
		array(), // No dependencies
		file_exists( WPP_LOAN_MANAGER_PATH . 'assets/css/trello-style.css' )
			? filemtime( WPP_LOAN_MANAGER_PATH . 'assets/css/trello-style.css' )
			: time()
	);

	// -------------------------------
	// 4. Trello JavaScript (Interactive Board)
	// -------------------------------
	// Depends on:
	// - 'jquery': Core jQuery library
	// - 'jquery-ui-sortable': For drag-and-drop card/column sorting
	wp_enqueue_script(
		'trello-script',
		WPP_LOAN_MANAGER_URL . 'assets/js/trello-script.js',
		array( 'jquery', 'jquery-ui-sortable' ),
		file_exists( WPP_LOAN_MANAGER_PATH . 'assets/js/trello-script.js' )
			? filemtime( WPP_LOAN_MANAGER_PATH . 'assets/js/trello-script.js' )
			: time(),
		true // Load in footer
	);

	// -------------------------------
	// 5. Frontend Utility Script
	// -------------------------------
	// Lightweight script for frontend interactions (e.g., modals, buttons)
	wp_enqueue_script(
		'trello-script-ft',
		WPP_LOAN_MANAGER_URL . 'assets/js/frontend.js',
		array( 'jquery' ),
		file_exists( WPP_LOAN_MANAGER_PATH . 'assets/js/frontend.js' )
			? filemtime( WPP_LOAN_MANAGER_PATH . 'assets/js/frontend.js' )
			: time(),
		true
	);

	// -------------------------------
	// 6. Localize Script: Pass AJAX Data to JavaScript
	// -------------------------------
	// Makes PHP variables available in JavaScript (specifically for 'trello-script')
	// Provides:
	// - ajax_url: Endpoint for admin-ajax.php
	// - nonce: Security token to verify AJAX requests

	$array = array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'trello_nonce' )
	);



	if ( ! empty( $loan_id ) ) {
		$array['loan_id'] = $loan_id;
	}

	wp_localize_script( 'trello-script', 'trello_vars', $array );

	// Optional: You can also localize for 'trello-script-ft' if needed
	// wp_localize_script('trello-script-ft', 'frontend_vars', [...]);
}

// -------------------------------
// 7. Hook: Register Enqueue Function
// -------------------------------
// Loads assets on the frontend
add_action( 'wp_enqueue_scripts', 'wpp_enqueue_Loan_styles' );

/**
 * Notes:
 *
 * - Use `admin_enqueue_scripts` if loading in admin area.
 * - Ensure jQuery UI Sortable is available (included in WordPress core).
 * - The original URL had a space: '...css  ' — this has been corrected.
 * - Cache-busting via `filemtime()` ensures users get updated assets.
 * - Always use `true` for $in_footer when possible to improve page load performance.
 */