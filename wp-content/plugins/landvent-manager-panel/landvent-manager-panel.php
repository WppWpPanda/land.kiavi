<?php
/*
Plugin Name: LandVent Manager Panel
Plugin URI: https://example.com
Description: Простая панель управления для менеджеров с шаблоном дашборда.
Version: 1.0
Author: WP Panda
Author URI: https://wppanda.com
License: GPL2
*/

/**
 * LandVent Manager Panel - Main Plugin File
 *
 * A comprehensive management dashboard plugin for loan managers,
 * featuring Trello-style boards, document tracking, brokerage management,
 * and dynamic form configurations. Designed for real estate or financial workflows.
 *
 * This file initializes the plugin, defines constants, loads dependencies,
 * registers activation routines, and bootstraps all functionality.
 *
 * @package LandVent_Manager_Panel
 * @author WP Panda
 * @license GPL-2.0-or-later
 * @link https://example.com
 * @version 1.0.0
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * Define Plugin Constants
 *
 * These constants provide easy access to the plugin's file system path and URL.
 * Used throughout the plugin to load assets, templates, and include files.
 *
 * @since 1.0.0
 */
if (!defined('WPP_LOAN_MANAGER_PATH')) {
	/**
	 * Absolute server path to the plugin directory.
	 *
	 * Example: /var/www/html/wp-content/plugins/landvent-manager-panel/
	 *
	 * @var string
	 */
	define('WPP_LOAN_MANAGER_PATH', plugin_dir_path(__FILE__));
}

if (!defined('WPP_LOAN_MANAGER_URL')) {
	/**
	 * Base URL to the plugin directory.
	 *
	 * Example: https://yoursite.com/wp-content/plugins/landvent-manager-panel/
	 *
	 * @var string
	 */
	define('WPP_LOAN_MANAGER_URL', plugin_dir_url(__FILE__));
}

/**
 * Plugin Activation Hook
 *
 * Registers a callback to run when the plugin is activated.
 * Ensures database tables are created or updated on activation.
 *
 * Uses an anonymous function to:
 * - Check if the function `trello_create_db_tables` exists.
 * - Load the activation file only if needed.
 * - Execute the setup routine.
 *
 * This approach improves reliability by ensuring the activation logic
 * is loaded at the exact moment it's needed.
 *
 * @since 1.0.0
 *
 * @see trello_create_db_tables() in /core/activation.php
 * @link https://developer.wordpress.org/reference/functions/register_activation_hook/
 */
register_activation_hook(__FILE__, function () {
	// Ensure the database setup function is available
	if (!function_exists('trello_create_db_tables')) {
		$activation_file = WPP_LOAN_MANAGER_PATH . 'core/activation.php';
		if (file_exists($activation_file)) {
			require_once $activation_file;
		} else {
			error_log('LandVent Manager Panel: Activation file not found: ' . $activation_file);
			return;
		}
	}

	// Run the database and initialization routine
	trello_create_db_tables();
});

/**
 * Load Core Module Files
 *
 * Includes essential plugin components in a structured order:
 * 1. Resources (custom post types, taxonomies)
 * 2. Templates (dashboard, pages, UI rendering)
 *
 * @since 1.0.0
 */
require_once WPP_LOAN_MANAGER_PATH . 'core/resources.php';
require_once WPP_LOAN_MANAGER_PATH . 'core/templates.php';

/**
 * Load Functional Modules
 *
 * These files contain logic for specific features:
 *
 * - navs.php         : Admin menu and submenu registration
 * - trello-columns.php: Trello board column management
 * - init.php         : General initialization hooks and filters
 * - endpoints.php    : REST API or AJAX endpoint registration
 *
 * @since 1.0.0
 */
require_once WPP_LOAN_MANAGER_PATH . 'core/functions/navs.php';
require_once WPP_LOAN_MANAGER_PATH . 'core/functions/trello-columns.php';
require_once WPP_LOAN_MANAGER_PATH . 'core/functions/init.php';
require_once WPP_LOAN_MANAGER_PATH . 'core/functions/endpoints.php';

/**
 * Load Configuration Files
 *
 * These files define form fields, UI sections, and metadata structures
 * used across the dashboard. Each corresponds to a specific data module.
 *
 * @since 1.0.0
 */
require_once WPP_LOAN_MANAGER_PATH . 'core/config/term-sheet-details.php';     // Term Sheet form fields
require_once WPP_LOAN_MANAGER_PATH . 'core/config/property-details.php';       // Property Info
require_once WPP_LOAN_MANAGER_PATH . 'core/config/applicant-info.php';         // Borrower/Application Info
require_once WPP_LOAN_MANAGER_PATH . 'core/config/additional-reserve.php';     // Reserve Requirements
require_once WPP_LOAN_MANAGER_PATH . 'core/config/attorney.php';               // Attorney/Title Contact
require_once WPP_LOAN_MANAGER_PATH . 'core/config/conditions.php';             // Loan Conditions
require_once WPP_LOAN_MANAGER_PATH . 'core/config/documents.php';              // Document Checklist
require_once WPP_LOAN_MANAGER_PATH . 'core/config/fees.php';                   // Fee Structures
require_once WPP_LOAN_MANAGER_PATH . 'core/config/investors.php';              // Investor Details
require_once WPP_LOAN_MANAGER_PATH . 'core/config/payments.php';               // Payment Schedules
require_once WPP_LOAN_MANAGER_PATH . 'core/config/required-documents.php';     // Required Docs List
require_once WPP_LOAN_MANAGER_PATH . 'core/config/title-company.php';          // Title & Escrow Info

/**
 * Plugin Notes
 *
 * - This plugin uses a modular architecture for scalability.
 * - All UI templates are loaded via `templates.php`.
 * - AJAX endpoints are registered in `endpoints.php`.
 * - Database schema is managed in `core/activation.php`.
 * - Assets (CSS/JS) are enqueued in a separate file (likely loaded in `init.php`).
 *
 * @todo Consider using autoloading or class-based structure in future versions.
 * @todo Add uninstall.php to clean up options and tables on deletion.
 */

// End of file


// Инициализируем глобальную переменную $loan
function my_setup_global_loan() {
	global $wp_query, $loan;
	$loan_id = isset($wp_query->query_vars['loan_id']) ? absint($wp_query->query_vars['loan_id']) : 0;
}
add_action('wp', 'my_setup_global_loan');