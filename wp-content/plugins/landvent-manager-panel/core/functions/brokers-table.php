<?php
/**
 * Brokers_Table — Display and Manage Brokers from Database
 *
 * A child class of Wpp_List_Table that displays real estate brokers
 * with custom actions (Edit, Delete) and full data from the `wpp_brokers` table.
 *
 * Features:
 * - Pagination
 * - Sorting by ID, Name, City, State
 * - Search by name, city, BDM
 * - Custom "Actions" column (Edit/Delete)
 * - Virtual column support (no DB query)
 * - Responsive design
 * - Secure nonce-based deletion
 *
 * @package           WppLibs\Examples
 * @subpackage        Brokers
 * @author            WP_Panda <panda@wp-panda.pro>
 * @copyright         2025 WP_Panda
 * @license           GPL-2.0-or-later
 *
 * @version           1.1.0
 * @since             1.0.0
 *
 * @link              https://developer.wordpress.org/reference/functions/add_query_arg/
 * @link              https://developer.wordpress.org/reference/functions/admin_url/
 * @link              https://developer.wordpress.org/reference/functions/wp_nonce_url/
 * @link              https://developer.wordpress.org/reference/functions/esc_url/
 * @link              https://developer.wordpress.org/reference/functions/esc_html_e/
 * @link              https://developer.wordpress.org/reference/functions/esc_attr_e/
 *
 * @example
 *     $table = new Brokers_Table();
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
	// Try to detect URL relative to this file
	//define( 'WPP_TABLE_URL', plugin_dir_url( __FILE__ ) . '../wpp_libs/wpp_list_table/' );
	// Fallback: define manually
	 define( 'WPP_TABLE_URL',  WP_PLUGIN_URL . '/wpp-core/wpp_libs/wpp_list_table/' );
}

/**
 * Class Brokers_Table
 *
 * Displays brokers from the `wpp_brokers` database table with Edit and Delete actions.
 *
 * Uses virtual columns to avoid querying non-existent fields like "actions".
 *
 * @extends Wpp_List_Table
 * @since 1.0.0
 */
class Brokers_Table extends Wpp_List_Table {

	/**
	 * Name of the database table (without prefix).
	 *
	 * Will be prefixed with WordPress table prefix (e.g., 'wp_' or custom).
	 *
	 * @var string
	 * @access protected
	 * @since 1.0.0
	 */
	protected $table_name = 'wpp_brokers';

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
	 * Whether to automatically enqueue CSS and JS.
	 *
	 * Set to false if using custom styles/scripts.
	 *
	 * @var bool
	 * @access protected
	 * @since 1.0.0
	 */
	protected $enqueue_assets = true;

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
		'id'                      => 'ID',
		'brok_brokerage_name'     => 'Brokerage Name',
		'brok_parent_brokerage'   => 'Parent Brokerage',
		'brok_city'               => 'City',
		'brok_state'              => 'State',
		'brok_broker_bdm'         => 'Broker/BDM',
		'actions'                 => 'Actions' // Virtual column — not in DB
	);

	/**
	 * Columns that support sorting.
	 *
	 * Must be real database fields.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0.0
	 *
	 * @example
	 *     Sorting URL: ?orderby=brok_city&order=ASC
	 */
	protected $sortable_columns = array(
		'id',
		'brok_brokerage_name',
		'brok_city',
		'brok_state',
		'brok_broker_bdm'
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
		'brok_brokerage_name',
		'brok_parent_brokerage',
		'brok_city',
		'brok_state',
		'brok_broker_bdm',
		'brok_address'
	);

	/**
	 * List of virtual columns (not in database).
	 *
	 * These are NOT included in SQL SELECT or WHERE clauses.
	 * Content is generated via `column_{name}()` methods.
	 *
	 * @var array
	 * @access protected
	 * @since 1.1.0
	 *
	 * @example
	 *     'actions' is rendered by column_actions()
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
				'page' => 'edit-broker',
				'id'   => $item['id']
			),
			admin_url( 'admin.php' )
		);

		// Build delete URL with nonce for security
		$delete_url = add_query_arg(
			array(
				'page'   => 'manage-brokers',
				'action' => 'delete_broker',
				'id'     => $item['id']
			),
			admin_url( 'admin.php' )
		);
		$delete_url = wp_nonce_url( $delete_url, 'delete_broker_' . $item['id'], 'broker_nonce' );

		// Create Edit button
		$actions = sprintf(
			'<a href="%s" class="button button-small" style="font-size:12px;padding:4px 8px;">✏️ Edit</a>',
			esc_url( $edit_url )
		);

		// Create Delete button with confirmation
		$actions .= ' ' . sprintf(
				'<a href="%s" class="button button-small" style="color:#a00;font-size:12px;padding:4px 8px;" onclick="return confirm(\'Are you sure you want to delete this broker?\nThis action cannot be undone.\')">🗑️ Delete</a>',
				esc_url( $delete_url )
			);

		return $actions;
	}

	/**
	 * Optional: Customize brokerage name display.
	 *
	 * @param array $item
	 * @return string
	 * @since 1.1.0
	 */
	public function column_brok_brokerage_name( $item ) {
		return '<strong>' . esc_html( $item['brok_brokerage_name'] ) . '</strong>';
	}

	/**
	 * Debug: Log SQL query to debug.log
	 *
	 * Uncomment to debug SQL issues.
	 *
	 * @return string SQL query
	 * @since 1.0.0
	 */
	// protected function get_query() {
	//     $query = parent::get_query();
	//     error_log( 'Brokers_Table Query: ' . $query );
	//     return $query;
	// }
}
