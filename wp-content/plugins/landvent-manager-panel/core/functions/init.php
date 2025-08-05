<?php
/**
 * Loan Manager Pro - Main Plugin Bootstrap File
 *
 * Initializes the plugin by loading required modules, managing action hooks,
 * and handling database updates. This file serves as the entry point for the
 * Loan Manager Pro plugin, ensuring all components are properly registered
 * and configured.
 *
 * @package    WP_Loan_Manager_Pro
 * @subpackage Core
 * @since      1.0.0
 * @author     WP_Panda <panda@wp-panda.pro>
 * @license    GNU General Public License v3.0
 *
 * @link       https://developer.wordpress.org/reference/functions/add_action/
 * @link       https://developer.wordpress.org/plugins/hooks/
 * @link       https://developer.wordpress.org/plugins/plugin-basics/best-practices/
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load Required Component Files
 *
 * Includes essential modules for full plugin functionality.
 * Each file handles a specific domain:
 *
 * @file get-colums-data.php     Retrieves dynamic table column data.
 * @file save-loans.php          Handles saving and updating loan records.
 * @file helpers.php             Utility functions used across the plugin.
 * @file ajax.php                AJAX handlers for frontend interactions.
 * @file templates.php           Template rendering logic for dashboard views.
 * @file wpp-core-filters.php    Filters modifying core plugin behavior.
 * @file brokers-table.php       Custom WP_List_Table for broker management.
 * @file law-table.php           Custom WP_List_Table for law firm management.
 * @file companies-table.php     Custom WP_List_Table for title company management.
 * @file appraisers-table.php    Custom WP_List_Table for appraiser management.
 *
 * @todo Replace manual includes with an autoloader (e.g., PSR-4) for better scalability.
 * @todo Wrap in try-catch or file_exists() checks to prevent fatal errors if files are missing.
 * @todo Consider lazy-loading non-critical files on demand.
 */
require_once 'get-colums-data.php';
require_once 'save-loans.php';
require_once 'helpers.php';
require_once 'ajax.php';
require_once 'templates.php';
require_once 'wpp-core-filters.php';
require_once 'brokers-table.php';
require_once 'law-table.php';
require_once 'companies-table.php';
require_once 'appraisers-table.php';

/**
 * Remove Default Loan Content Sections
 *
 * Disables specific loan detail page sections via `wpp_lmp_loan_content` action hook.
 * This customization allows selective display of loan terms based on business logic.
 *
 * Hooked to `init` with priority 50 to ensure it runs after all actions are registered.
 *
 * @hook init
 * @priority 50
 * @see wpp_lmp_loan_content - Action hook in loan template where sections are rendered.
 *
 * Removed sections:
 * - Additional Reserve (priority 40)
 * - Milestones (priority 60)
 * - Conditions (priority 80)
 * - Investors (priority 90)
 * - Attorney (priority 100)
 * - Required Documents (priority 130)
 *
 * @note Commented-out removals indicate sections that may be conditionally enabled later.
 *
 * @example To re-enable a section:
 *   remove_action('wpp_lmp_loan_content', 'wpp_term_milestones', 60);
 *
 * @link https://developer.wordpress.org/reference/functions/remove_action/
 * @link https://developer.wordpress.org/reference/functions/add_action/
 */
add_action( 'init', function () {
	remove_action( 'wpp_lmp_loan_content', 'wpp_term_additional_reserve', 40 );
	// remove_action( 'wpp_lmp_loan_content', 'wpp_term_fees', 50 ); // Example: fees kept active
	remove_action( 'wpp_lmp_loan_content', 'wpp_term_milestones', 60 );
	// remove_action( 'wpp_lmp_loan_content', 'wpp_term_payments', 70 ); // Payments kept active
	remove_action( 'wpp_lmp_loan_content', 'wpp_term_conditions', 80 );
	remove_action( 'wpp_lmp_loan_content', 'wpp_term_investors', 90 );
	remove_action( 'wpp_lmp_loan_content', 'wpp_term_attorney', 100 );
	// remove_action( 'wpp_lmp_loan_content', 'wpp_term_title_company', 110 ); // Title company kept active
	// remove_action( 'wpp_lmp_loan_content', 'wpp_term_required_documents', 120 ); // Kept at 120?
	remove_action( 'wpp_lmp_loan_content', 'wpp_term_required_documents', 130 );
}, 50 );

/**
 * Check and Apply Database Schema Updates on Plugin Load
 *
 * This function compares the current stored version of each database table
 * against the expected version and triggers schema updates if necessary.
 *
 * It ensures that database tables are up-to-date with the latest structure,
 * especially after plugin updates. The actual table creation logic should
 * be implemented in `trello_create_db_tables()`.
 *
 * @since 1.0.0
 *
 * @return void
 *
 * @uses get_option()            To retrieve stored DB version.
 * @uses version_compare()       To compare version strings.
 * @uses trello_create_db_tables() To perform schema updates (assumed defined elsewhere).
 *
 * @global string $wp_version    Used to determine compatibility context.
 *
 * @link https://developer.wordpress.org/reference/functions/get_option/
 * @link https://www.php.net/manual/en/function.version-compare.php
 *
 * @example Version Option Format:
 *   add_option('brokers_db_version', '1.0');
 *
 * @todo Move this function into a dedicated updater class or file.
 * @todo Replace with WordPress Plugin Updater pattern using register_activation_hook.
 * @todo Only run updates when version actually changes (avoid redundant checks).
 * @todo Add logging to track update execution and results.
 * @todo Implement rollback mechanism for failed updates.
 * @todo Replace 'trello_create_db_tables()' with modular update routines per component.
 */
// Uncomment to enable automatic update checks on every page load (development only)
// add_action( 'plugins_loaded', 'check_db_updates' );
function check_db_updates() {
	// Define current expected versions for each component
	$current_versions = [
		'trello'     => '1.0',
		'loans'      => '1.0',
		'appraiser'  => '1.0',
		'companies'  => '1.0',
		'brokers'    => '1.0',
		'law_firm'   => '1.0'
	];

	$need_update = false;

	// Check if any component is behind the expected version
	foreach ( $current_versions as $key => $version ) {
		$stored_version = get_option( "{$key}_db_version", '0' );
		if ( version_compare( $stored_version, $version, '<' ) ) {
			$need_update = true;
			break;
		}
	}

	// Trigger schema update if needed
	if ( $need_update ) {
		trello_create_db_tables(); // Assumed function creates/alters tables
	}
}

/**
 * Suggested Improvement: Use Activation Hook Instead
 *
 * For better performance and reliability, use WordPress activation hooks
 * instead of checking on every `plugins_loaded` event.
 *
 * @example
 *
 * register_activation_hook( __FILE__, 'wpp_loan_manager_activate' );
 *
 * function wpp_loan_manager_activate() {
 *     check_db_updates(); // Run once on activation
 * }
 *
 * This prevents unnecessary database queries on every page load.
 */