<?php
/**
 * Create and Update Plugin Database Tables
 *
 * This function ensures that all required custom database tables for the plugin
 * are created or updated during plugin activation or version upgrade.
 * It uses WordPress's `dbDelta()` to safely handle schema changes and tracks
 * table versions using WordPress options.
 *
 * @package LandVent_Manager_Panel
 * @subpackage Database
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Function: Create or Update Custom Database Tables
 *
 * This function is typically called during plugin activation or upgrade.
 * It checks the current version of each custom table against the expected version,
 * and runs `dbDelta()` to create or update the schema if needed.
 *
 * Features:
 * - Version-controlled schema management.
 * - Safe SQL generation using `dbDelta()` rules.
 * - Support for multiple custom tables.
 * - Automatic rewrite rule flush.
 * - Optional endpoint registration.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return void
 *
 * @uses get_option()               To retrieve stored table versions.
 * @uses update_option()            To save new table versions after update.
 * @uses dbDelta()                  To create or update table structure safely.
 * @uses require_once()             To load WordPress upgrade utilities.
 * @uses flush_rewrite_rules()      To refresh permalink rules (if using custom endpoints).
 * @uses version_compare()          To compare version strings.
 *
 * @link https://developer.wordpress.org/reference/functions/dbdelta/
 * @link https://developer.wordpress.org/reference/functions/get_option/
 * @link https://developer.wordpress.org/reference/functions/update_option/
 * @link https://developer.wordpress.org/reference/functions/flush_rewrite_rules/
 * @link https://developer.wordpress.org/reference/functions/version_compare/
 */
function trello_create_db_tables() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	// -------------------------------
	// 1. Define Current Schema Versions
	// -------------------------------
	// Each key corresponds to a table. Increment version to trigger update.
	// Example: 'appraiser' => '1.1' will run SQL if current version < 1.1
	$current_versions = [
		'trello'      => '1.0',
		'loans'       => '1.0',
		'appraiser'   => '1.1',
		'companies'   => '1.1',
		'brokers'     => '1.1',
		'law_firm'    => '1.1'
	];

	// -------------------------------
	// 2. Retrieve Installed Versions from Database
	// -------------------------------
	// Default to '0' if option doesn't exist
	$installed_versions = [
		'trello'      => get_option('trello_db_version', '0'),
		'loans'       => get_option('loans_db_version', '0'),
		'appraiser'   => get_option('appraiser_db_version', '0'),
		'companies'   => get_option('companies_db_version', '0'),
		'brokers'     => get_option('brokers_db_version', '0'),
		'law_firm'    => get_option('law_firm_db_version', '0')
	];

	// -------------------------------
	// 3. Define Table Names with WordPress Prefix
	// -------------------------------
	$table_trello      = $wpdb->prefix . 'wpp_trello_columns';
	$table_loans       = $wpdb->prefix . 'wpp_loans_full_data';
	$table_appraiser   = $wpdb->prefix . 'wpp_appraiser';
	$table_companies   = $wpdb->prefix . 'wpp_companies';
	$table_brokers     = $wpdb->prefix . 'wpp_brokers';
	$table_law_firm    = $wpdb->prefix . 'wpp_law_firm';

	// -------------------------------
	// 4. Define SQL Schema for Each Table
	// -------------------------------
	// IMPORTANT: dbDelta() has strict formatting rules:
	// - Each field line must end with a comma or closing parenthesis.
	// - Use KEY instead of INDEX.
	// - Use `PRIMARY KEY (column)` with space.
	// - Use backticks around table names if needed.
	//
	// @link https://developer.wordpress.org/reference/functions/dbdelta/#notes

	$sql_trello = "CREATE TABLE $table_trello (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        column_order int(11) NOT NULL DEFAULT '0',
        card_ids text NOT NULL DEFAULT '[]',
        PRIMARY KEY (id)
    ) $charset_collate;";

	$sql_loans = "CREATE TABLE $table_loans (
        loan_id VARCHAR(100) NOT NULL,
        change_time DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        loan_data LONGTEXT NOT NULL,
        PRIMARY KEY (loan_id)
    ) $charset_collate;";

	$sql_appraiser = "CREATE TABLE $table_appraiser (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        appr_name varchar(255) NOT NULL,
        appr_address varchar(255),
        appr_city varchar(100),
        appr_county varchar(100),
        appr_state varchar(50),
        appr_zip varchar(20),
        appr_phone varchar(50),
        appr_fax varchar(50),
        appr_email varchar(100),
        appr_title varchar(100),
        appr_website varchar(255),
        appr_contact text,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

	$sql_companies = "CREATE TABLE $table_companies (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        comp_title_company_name varchar(255) NOT NULL,
        comp_address varchar(255),
        comp_city varchar(100),
        comp_county varchar(100),
        comp_state varchar(50),
        comp_zip_code varchar(20),
        comp_phone varchar(50),
        comp_toll_free varchar(50),
        comp_fax varchar(50),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

	$sql_brokers = "CREATE TABLE $table_brokers (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        brok_brokerage_name varchar(255) NOT NULL,
        brok_parent_brokerage varchar(255),
        brok_address varchar(255),
        brok_city varchar(100),
        brok_county varchar(100),
        brok_state varchar(50),
        brok_zip_code varchar(20),
        brok_broker_bdm varchar(255),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

	$sql_law_firm = "CREATE TABLE $table_law_firm (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        law_firm_name varchar(255) NOT NULL,
        law_address varchar(255),
        law_city varchar(100),
        law_county varchar(100),
        law_state varchar(50),
        law_zip_code varchar(20),
        law_phone varchar(50),
        law_toll_free varchar(50),
        law_fax varchar(50),
        law_website varchar(255),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

	// -------------------------------
	// 5. Load WordPress Upgrade Functions
	// -------------------------------
	// Required for dbDelta()
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	// -------------------------------
	// 6. Create or Update Tables Based on Version
	// -------------------------------
	foreach ($current_versions as $key => $version) {
		if (version_compare($installed_versions[$key], $version, '<')) {
			$sql_var = "sql_$key";
			if (isset($$sql_var)) {
				// Run dbDelta to create or update table
				dbDelta($$sql_var);

				// Update version option to prevent re-run
				update_option("{$key}_db_version", $version);
			}
		}
	}

	// -------------------------------
	// 7. Optional: Register Custom Endpoints
	// -------------------------------
	// If your plugin uses REST or custom rewrite rules
	if (class_exists('LandVent_Manager_Endpoints')) {
		LandVent_Manager_Endpoints::get_instance()->add_endpoints();
	}

	// -------------------------------
	// 8. Flush Rewrite Rules
	// -------------------------------
	// Only do this on activation (not on every page load)
	// Consider using a flag to avoid performance impact
	flush_rewrite_rules();
}