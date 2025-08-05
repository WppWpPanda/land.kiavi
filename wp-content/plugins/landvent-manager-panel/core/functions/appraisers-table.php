<?php
/**
 * Appraisers_Table — Display and Manage Appraisers from Database
 *
 * A child class of Wpp_List_Table that displays appraisers
 * with custom actions (Edit, Delete) and full data from the `wpp_appraisers` table.
 *
 * Features:
 * - Pagination
 * - Sorting by name, city, state
 * - Search by name, city, phone, email
 * - Custom "Actions" column (Edit/Delete)
 * - Virtual column support (no DB query)
 * - Responsive design
 * - Secure nonce-based deletion
 * - Enqueues custom JavaScript for enhanced interaction
 *
 * @package           WppLibs\Examples
 * @subpackage        Appraisers
 * @author            WP_Panda <panda@wp-panda.pro>
 * @copyright         2025 WP_Panda
 * @license           GPL-2.0-or-later
 *
 * @version           1.0.0
 * @since             1.0.0
 *
 * @link              https://developer.wordpress.org/reference/functions/add_query_arg/
 * @link              https://developer.wordpress.org/reference/functions/admin_url/
 * @link              https://developer.wordpress.org/reference/functions/wp_nonce_url/
 * @link              https://developer.wordpress.org/reference/functions/esc_url/
 * @link              https://developer.wordpress.org/reference/functions/esc_html_e/
 * @link              https://developer.wordpress.org/reference/functions/esc_attr_e/
 * @link              https://developer.wordpress.org/reference/functions/wp_enqueue_script/
 *
 * @example
 *     $table = new Appraisers_Table();
 *     echo $table->display();
 *
 * @todo Add "View" button linking to frontend profile
 * @todo Add bulk delete action
 * @todo Add column toggle (show/hide)
 * @todo Add export to CSV button
 * @todo Add filtering by state
 */
if ( ! class_exists( 'Wpp_List_Table' ) ) {
	$class_file = WP_PLUGIN_DIR . '/wpp-core/wpp_libs/wpp_list_table/Wpp_List_Table.php';

	if ( file_exists( $class_file ) ) {
		require_once $class_file;
	} else {
		echo '<div class="error"><p>❌ Error: Wpp_List_Table.php not found at: ' . esc_html( $class_file ) . '</p></div>';
		return;
	}
}

// Define URL to assets directory for CSS/JS loading
// Can be overridden in main plugin/theme
if ( ! defined( 'WPP_TABLE_URL' ) ) {
	define( 'WPP_TABLE_URL', WP_PLUGIN_URL . '/wpp-core/wpp_libs/wpp_list_table/' );
}

/**
 * Class Appraisers_Table
 *
 * Displays appraisers from the `wpp_appraisers` database table with Edit and Delete actions.
 *
 * Uses virtual columns to avoid querying non-existent fields like "actions".
 * Optionally enqueues a custom JavaScript file for enhanced functionality.
 *
 * @extends Wpp_List_Table
 * @since 1.0.0
 */
class Appraisers_Table extends Wpp_List_Table {

	/**
	 * Name of the database table (without prefix).
	 *
	 * Will be prefixed with WordPress table prefix (e.g., 'wp_' or custom).
	 *
	 * @var string
	 * @access protected
	 * @since 1.0.0
	 */
	protected $table_name = 'wpp_appraiser';

	/**
	 * Primary key of the table.
	 *
	 * Used for sorting and record identification.
	 *
	 * @var string
	 * @access protected
	 * @since 1.0.0
	 */
	protected $primary_key = 'id';

	/**
	 * Number of items per page.
	 *
	 * @var int
	 * @access protected
	 * @since 1.0.0
	 */
	protected $per_page = 30;

	/**
	 * Whether to show the search form.
	 *
	 * Set to false to hide search input.
	 *
	 * @var bool
	 * @access protected
	 * @since 1.0.0
	 */
	protected $show_search = false;

	/**
	 * Whether to automatically enqueue CSS and JS assets.
	 *
	 * Set to false if using custom styles/scripts.
	 *
	 * @var bool
	 * @access protected
	 * @since 1.0.0
	 */
	protected $enqueue_assets = true;

	/**
	 * Whether to enqueue a custom JavaScript file for appraiser-specific logic.
	 *
	 * Set to false to disable loading of `appraisers-table.js`.
	 *
	 * @var bool
	 * @access protected
	 * @since 1.0.0
	 */
	protected $enqueue_custom_js = true;

	/**
	 * Table columns: 'db_column_or_virtual' => 'Label'.
	 *
	 * Real columns are queried from DB.
	 * Virtual columns (like 'actions') are rendered via methods.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0.0
	 *
	 * @reference https://developer.wordpress.org/reference/classes/wpdb/get_results/
	 */
	protected $columns = array(
		'id'        => 'ID',
		'appr_name' => 'Name',
		'appr_city' => 'City',
		'appr_state' => 'State',
		'appr_phone' => 'Phone',
		'appr_email' => 'Email',
		'actions'   => 'Actions' // Virtual column — not in DB
	);

	/**
	 * Columns that support sorting.
	 *
	 * Must be real database fields.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0.0
	 */
	protected $sortable_columns = array(
		'id', 'appr_name', 'appr_city', 'appr_state', 'appr_phone', 'appr_email'
	);

	/**
	 * Columns included in search queries.
	 *
	 * Search uses `LIKE %keyword%` on these fields.
	 * Virtual columns are automatically excluded.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0.0
	 *
	 * @reference https://developer.wordpress.org/reference/classes/wpdb/prepare/
	 */
	protected $searchable_columns = array(
		'appr_name', 'appr_city', 'appr_state', 'appr_phone', 'appr_email', 'appr_address'
	);

	/**
	 * List of virtual columns (not in database).
	 *
	 * These are NOT included in SQL SELECT or WHERE clauses.
	 * Content is generated via `column_{name}()` methods.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0.0
	 */
	protected $virtual_columns = array(
		'actions'
	);

	/**
	 * Render the "Actions" column with Edit and Delete buttons.
	 *
	 * Uses WordPress core functions for URL and security.
	 *
	 * @param array $item Single row from database
	 * @return string HTML for the cell
	 * @since 1.0.0
	 *
	 * @reference https://developer.wordpress.org/reference/functions/add_query_arg/
	 * @reference https://developer.wordpress.org/reference/functions/admin_url/
	 * @reference https://developer.wordpress.org/reference/functions/wp_nonce_url/
	 * @reference https://developer.wordpress.org/reference/functions/esc_url/
	 */
	public function column_actions( $item ) {
		// Build edit URL
		$edit_url = add_query_arg(
			array(
				'page' => 'edit-appraiser',
				'id'   => $item['id']
			),
			admin_url( 'admin.php' )
		);

		// Build delete URL with nonce for security
		$delete_url = add_query_arg(
			array(
				'page'   => 'manage-appraisers',
				'action' => 'delete_appraiser',
				'id'     => $item['id']
			),
			admin_url( 'admin.php' )
		);
		$delete_url = wp_nonce_url( $delete_url, 'delete_appraiser_' . $item['id'], 'appraiser_nonce' );

		// Create Edit button
		$actions = sprintf(
			'<a href="#" class="button button-small appr-edit" style="font-size:12px;padding:4px 8px;">✏️ Edit</a>'
		);

		// Create Delete button with confirmation
		$actions .= ' ' . sprintf(
				'<a href="%s" class="button button-small appr-delete" style="color:#a00;font-size:12px;padding:4px 8px;" onclick="return confirm(\'Are you sure you want to delete this appraiser?\\nThis action cannot be undone.\')">🗑️ Delete</a>',
				esc_url( $delete_url )
			);

		return $actions;
	}

	/**
	 * Optional: Highlight appraiser name
	 *
	 * @param array $item
	 * @return string
	 * @since 1.0.0
	 */
	public function column_appr_name( $item ) {
		return '<strong>' . esc_html( $item['appr_name'] ) . '</strong>';
	}

	/**
	 * Enqueue custom JavaScript for appraiser interactions (e.g., modal, AJAX).
	 *
	 * Loads: /wpp-core/wpp_libs/wpp_list_table/js/appraisers-table.js
	 * Version: file modification time (cache busting)
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function enqueue_custom_js() {
		if ( ! $this->enqueue_custom_js || ! function_exists( 'wp_enqueue_script' ) ) {
			return;
		}

		$js_url = WPP_LOAN_MANAGER_URL . 'assets/js/appraisers-table.js';
		$js_path = str_replace( content_url(), WP_CONTENT_DIR, $js_url );

		// Use file modification time as version for cache busting
		$version = @filemtime( $js_path ) ?: false;

		wp_enqueue_script(
			'wpp-appraisers-table-js',
			$js_url,
			array( 'jquery' ),
			$version,
			true
		);

		// Optional: Localize script
		// wp_localize_script( 'wpp-appraisers-table-js', 'wppAppraisersConfig', array(
		//     'ajax_url' => admin_url( 'admin-ajax.php' ),
		//     'nonce'    => wp_create_nonce( 'wpp_appraiser_nonce' )
		// ) );
	}

	/**
	 * Constructor
	 *
	 * Extends parent constructor and enqueues custom JS.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		$this->enqueue_custom_js();
	}
}