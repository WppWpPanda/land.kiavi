<?php
/**
 * Wpp_List_Table — Universal, Reusable Database Table Display Class
 *
 * This class enables developers to display data from any WordPress database table
 * on the **frontend or backend** with full support for:
 *
 * - Pagination
 * - Column sorting
 * - Search (with optional AJAX)
 * - Custom column rendering (e.g., "Edit", "Delete")
 * - Responsive design
 * - XSS and SQL injection protection
 * - No dependency on `WP_List_Table`
 * - Works in plugins, themes, or standalone includes
 *
 * Designed to be extended. Cannot be used directly.
 *
 * @package           WppLibs\Table
 * @subpackage        Core
 * @author            Your Name <your.email@example.com>
 * @copyright         2025 Your Name
 * @license           GPL-2.0-or-later
 *
 * @version           2.5.0
 * @since             1.0.0
 *
 * @abstract
 *
 * @link              https://developer.wordpress.org/reference/classes/wpdb/
 * @link              https://www.phpdoc.org/docs/latest/guides/getting-started.html
 * @link              https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/
 * @link              https://developer.wordpress.org/plugins/security/data-validation/
 * @link              https://developer.wordpress.org/plugins/javascript/enqueuing/
 *
 * @example
 *     // Step 1: Include the class
 *     require_once 'Wpp_List_Table.php';
 *
 *     // Step 2: Create a child class
 *     class ProductTable extends Wpp_List_Table {
 *         protected $table_name         = 'products';
 *         protected $primary_key        = 'id';
 *         protected $columns            = [
 *             'id'          => 'ID',
 *             'name'        => 'Product Name',
 *             'price'       => 'Price',
 *             'actions'     => 'Actions'  // Virtual column
 *         ];
 *         protected $sortable_columns   = [ 'id', 'name', 'price' ];
 *         protected $searchable_columns = [ 'name' ];
 *         protected $show_search        = false; // Hide search form
 *         protected $per_page           = 10;
 *     }
 *
 *     // Step 3: Render the table
 *     $table = new ProductTable();
 *     echo $table->display(); // Outputs full HTML table
 *
 * @example
 *     // Disable asset loading (use your own CSS/JS)
 *     class MinimalTable extends Wpp_List_Table {
 *         protected $enqueue_assets = false;
 *         // ... other settings
 *     }
 */
if ( ! defined( 'ABSPATH' ) && ! ( isset( $GLOBALS['wpdb'] ) && class_exists( 'wpdb' ) ) ) {
	return;
}

// Ensure jQuery is loaded for AJAX support
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_script( 'jquery' );
} );
add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_script( 'jquery' );
} );

/**
 * Abstract class Wpp_List_Table
 *
 * A fully self-contained, reusable class for displaying database tables.
 *
 * Features:
 * - No dependency on `WP_List_Table`
 * - Safe for early inclusion (e.g., in plugin files)
 * - Compatible with Query Monitor (QM_DB)
 * - Supports custom column rendering
 * - Handles PHP 8.1+ deprecation warnings
 * - Automatically escapes SQL to prevent injection
 *
 * This class must be extended. Child classes must define:
 * - $table_name
 * - $columns
 *
 * @abstract
 * @since 1.0.0
 */
abstract class Wpp_List_Table {

	/**
	 * Name of the database table (without prefix).
	 *
	 * Will be automatically prefixed with WordPress table prefix (e.g., 'wp_' or 'custom_').
	 *
	 * Example: 'products' → becomes `wp_products`
	 *
	 * @var string
	 * @access protected
	 * @since 1.0.0
	 *
	 * @example
	 *     protected $table_name = 'products';
	 */
	protected $table_name;

	/**
	 * Primary key of the table (usually 'id').
	 *
	 * Used for default sorting and record identification.
	 *
	 * @var string
	 * @access protected
	 * @since 1.0.0
	 *
	 * @example
	 *     protected $primary_key = 'id';
	 */
	protected $primary_key = 'id';

	/**
	 * Table columns: 'column_name' => 'Display Label'.
	 *
	 * Keys can be real database fields or virtual (e.g., 'actions').
	 * Values are human-readable labels.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0.0
	 *
	 * @example
	 *     protected $columns = [
	 *         'id'        => 'ID',
	 *         'name'      => 'Name',
	 *         'edit_link' => 'Edit'  // Virtual column
	 *     ];
	 */
	protected $columns = array();

	/**
	 * Columns that support sorting.
	 *
	 * If empty, sorting is disabled.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0.0
	 *
	 * @example
	 *     protected $sortable_columns = [ 'id', 'name' ];
	 */
	protected $sortable_columns = array();

	/**
	 * Columns included in search queries.
	 *
	 * Search uses `LIKE %keyword%` on these fields.
	 *
	 * @var array
	 * @access protected
	 * @since 1.0.0
	 *
	 * @example
	 *     protected $searchable_columns = [ 'name', 'email' ];
	 */
	protected $searchable_columns = array();

	/**
	 * Number of items to display per page.
	 *
	 * @var int
	 * @access protected
	 * @since 1.0.0
	 *
	 * @example
	 *     protected $per_page = 10;
	 */
	protected $per_page = 20;

	/**
	 * Whether to display the search form.
	 *
	 * Set to `false` to hide the search input.
	 *
	 * @var bool
	 * @access protected
	 * @since 2.4.0
	 *
	 * @example
	 *     protected $show_search = false;
	 */
	protected $show_search = true;

	/**
	 * Whether to automatically enqueue CSS and JS assets.
	 *
	 * If true, loads:
	 * - wpp-table.css
	 * - wpp-table.js (with AJAX support)
	 *
	 * @var bool
	 * @access protected
	 * @since 1.1.0
	 *
	 * @example
	 *     protected $enqueue_assets = false;
	 */
	protected $enqueue_assets = true;

	/**
	 * WordPress database object.
	 *
	 * May be original $wpdb or a proxy (e.g., QM_DB from Query Monitor).
	 *
	 * @var wpdb
	 * @access protected
	 * @since 1.0.0
	 */
	protected $db;

	/**
	 * Current page number (from 'paged' GET parameter).
	 *
	 * @var int
	 * @access protected
	 * @since 1.0.0
	 */
	protected $current_page = 1;

	/**
	 * Column used for sorting.
	 *
	 * @var string
	 * @access protected
	 * @since 1.0.0
	 */
	protected $orderby = '';

	/**
	 * Sort direction: 'ASC' or 'DESC'.
	 *
	 * @var string
	 * @access protected
	 * @since 1.0.0
	 */
	protected $order = 'ASC';

	/**
	 * Search query string (from 's' GET parameter).
	 *
	 * @var string
	 * @access protected
	 * @since 1.0.0
	 */
	protected $search = '';

	/**
	 * Full table name with WordPress prefix.
	 *
	 * @var string
	 * @access protected
	 * @since 1.0.0
	 */
	protected $full_table_name;

	/**
	 * Constructor
	 *
	 * Initializes database connection, validates table and columns,
	 * processes GET parameters, and optionally enqueues assets.
	 *
	 * Uses `global $wpdb` to ensure compatibility with proxies like QM_DB.
	 *
	 * @since 1.0.0
	 * @throws Exception If table doesn't exist or columns are not defined
	 *
	 * @example
	 *     $table = new CustomTable();
	 *     echo $table->display();
	 */
	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;

		if ( empty( $this->table_name ) ) {
			_doing_it_wrong( __CLASS__, 'Property $table_name must be defined.', '1.0.0' );
			return;
		}

		$this->full_table_name = $this->db->prefix . $this->table_name;

		// Check if table exists in database
		$table_exists = $this->db->get_var(
			$this->db->prepare( "SHOW TABLES LIKE %s", $this->full_table_name )
		);

		if ( $table_exists !== $this->full_table_name ) {
			error_log( "Wpp_List_Table: Table {$this->full_table_name} does not exist in the database." );
			return;
		}

		if ( empty( $this->columns ) ) {
			_doing_it_wrong( __CLASS__, 'Columns ($columns) must be defined.', '1.0.0' );
			return;
		}

		$this->handle_request();
		$this->maybe_enqueue_assets();
	}

	/**
	 * Process incoming GET parameters safely.
	 *
	 * Prevents PHP 8.1+ deprecation warnings by checking for null.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @reference https://www.php.net/manual/en/function.filter-input.php
	 * @reference https://developer.wordpress.org/reference/functions/sanitize_key/
	 * @reference https://developer.wordpress.org/reference/functions/sanitize_text_field/
	 */
	protected function handle_request() {
		$this->current_page = max( 1, absint( filter_input( INPUT_GET, 'paged', FILTER_DEFAULT ) ?: 1 ) );

		$orderby = filter_input( INPUT_GET, 'orderby', FILTER_DEFAULT );
		if ( $orderby !== null && $orderby !== false ) {
			$orderby = sanitize_key( $orderby );
			if ( in_array( $orderby, $this->sortable_columns, true ) ) {
				$this->orderby = $orderby;
			}
		}

		$order = filter_input( INPUT_GET, 'order', FILTER_DEFAULT );
		$this->order = ( $order !== null && $order !== false && strtoupper( (string) $order ) === 'DESC' ) ? 'DESC' : 'ASC';

		$search = filter_input( INPUT_GET, 's', FILTER_DEFAULT );
		$this->search = ( $search !== null && $search !== false ) ? trim( sanitize_text_field( $search ) ) : '';
	}

	/**
	 * Enqueue CSS and JS assets if enabled.
	 *
	 * Loads:
	 * - CSS: wpp-table.css
	 * - JS: wpp-table.js (depends on jQuery)
	 *
	 * Delays script localization to `wp_loaded` to avoid calling `wp_create_nonce()`
	 * before WordPress is fully initialized.
	 *
	 * @since 1.1.0
	 * @access protected
	 *
	 * @reference https://developer.wordpress.org/reference/functions/wp_enqueue_style/
	 * @reference https://developer.wordpress.org/reference/functions/wp_enqueue_script/
	 * @reference https://developer.wordpress.org/reference/functions/wp_localize_script/
	 * @reference https://developer.wordpress.org/reference/functions/add_action/
	 */
	protected function maybe_enqueue_assets() {
		if ( ! $this->enqueue_assets || ! function_exists( 'wp_enqueue_style' ) ) {
			return;
		}

		$version = '2.5.0';
		$dir_url = $this->get_assets_url();

		wp_enqueue_style(
			'wpp-list-table',
			$dir_url . 'css/wpp-table.css',
			array(),
			$version
		);

		wp_enqueue_script(
			'wpp-list-table',
			$dir_url . 'js/wpp-table.js',
			array( 'jquery' ),
			$version,
			true
		);

		// Delay localization until WordPress is fully loaded
		add_action( 'wp_loaded', array( $this, 'localize_script_after_init' ), 20 );
	}

	/**
	 * Localize script only after WordPress is fully initialized.
	 *
	 * Prevents "Call to undefined function wp_create_nonce()" when included early.
	 *
	 * @since 2.4.1
	 * @access public (called via hook)
	 *
	 * @reference https://developer.wordpress.org/reference/functions/wp_create_nonce/
	 * @reference https://developer.wordpress.org/reference/functions/admin_url/
	 */
	public function localize_script_after_init() {
		if ( ! function_exists( 'wp_create_nonce' ) || ! function_exists( 'admin_url' ) ) {
			return;
		}

		wp_localize_script(
			'wpp-list-table',
			'wppTableConfig',
			array(
				'ajaxurl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'wpp_table_nonce' ),
				'containerId' => 'wpp-table-container-' . md5( $this->table_name ),
			)
		);
	}

	/**
	 * Get the base URL to the assets directory.
	 *
	 * Can be overridden using WPP_TABLE_URL constant.
	 *
	 * @return string URL to /assets/ directory
	 * @since 1.1.0
	 * @access protected
	 *
	 * @example
	 *     define('WPP_TABLE_URL', get_template_directory_uri() . '/wpp_libs/wpp_list_table/');
	 */
	protected function get_assets_url() {
		if ( defined( 'WPP_TABLE_URL' ) ) {
			return WPP_TABLE_URL;
		}

		$dir = dirname( __FILE__ );
		$url = str_replace(
			str_replace( '\\', '/', WP_CONTENT_DIR ),
			content_url(),
			$dir
		);

		return trailingslashit( str_replace( '\\', '/', $url ) ) . 'assets/';
	}

	/**
	 * Build SQL query for fetching data.
	 *
	 * Uses regex-based column escaping for compatibility with QM_DB (which lacks esc_sql).
	 *
	 * @return string SQL query
	 * @since 1.0.0
	 * @access protected
	 *
	 * @reference https://developer.wordpress.org/reference/classes/wpdb/prepare/
	 * @reference https://developer.wordpress.org/reference/classes/wpdb/esc_like/
	 */
	protected function get_query() {
		global $wpdb;

		// Safely escape column names using regex (no esc_sql method call)
		$escaped_columns = array();
		foreach ( array_keys( $this->columns ) as $col ) {
			$escaped_columns[] = preg_replace( '/[^a-zA-Z0-9_]/', '', $col );
		}
		$select_cols = implode( ', ', $escaped_columns );

		$query = "SELECT {$select_cols} FROM {$this->full_table_name}";

		// WHERE for search
		$where_parts = array();
		$search_like = '%' . $wpdb->esc_like( $this->search ) . '%';

		foreach ( $this->searchable_columns as $col ) {
			$safe_col = preg_replace( '/[^a-zA-Z0-9_]/', '', $col );
			$where_parts[] = $wpdb->prepare( "{$safe_col} LIKE %s", $search_like );
		}

		if ( ! empty( $this->search ) && ! empty( $where_parts ) ) {
			$query .= ' WHERE (' . implode( ' OR ', $where_parts ) . ')';
		}

		// Sorting
		if ( ! empty( $this->orderby ) ) {
			$orderby = preg_replace( '/[^a-zA-Z0-9_]/', '', $this->orderby );
			$order   = $this->order === 'DESC' ? 'DESC' : 'ASC';
			$query  .= " ORDER BY {$orderby} {$order}";
		} else {
			$pk = preg_replace( '/[^a-zA-Z0-9_]/', '', $this->primary_key );
			$query .= " ORDER BY {$pk} ASC";
		}

		return $query;
	}

	/**
	 * Get items for current page.
	 *
	 * @return array List of associative arrays (database rows)
	 * @since 1.0.0
	 *
	 * @example
	 *     $items = $table->get_items();
	 *     foreach ( $items as $item ) {
	 *         echo $item['name'];
	 *     }
	 */
	public function get_items() {
		$query = $this->get_query();
		$offset = ( $this->current_page - 1 ) * $this->per_page;
		$query .= $this->db->prepare( " LIMIT %d OFFSET %d", $this->per_page, $offset );

		return $this->db->get_results( $query, ARRAY_A );
	}

	/**
	 * Get total number of items (for pagination).
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function get_total_items() {
		global $wpdb;

		$query = "SELECT COUNT(*) FROM {$this->full_table_name}";

		if ( ! empty( $this->search ) && ! empty( $this->searchable_columns ) ) {
			$where_parts = array();
			$search_like = '%' . $wpdb->esc_like( $this->search ) . '%';
			foreach ( $this->searchable_columns as $col ) {
				$safe_col = preg_replace( '/[^a-zA-Z0-9_]/', '', $col );
				$where_parts[] = $wpdb->prepare( "{$safe_col} LIKE %s", $search_like );
			}
			$query .= ' WHERE (' . implode( ' OR ', $where_parts ) . ')';
		}

		return (int) $this->db->get_var( $query );
	}

	/**
	 * Get total number of pages.
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function get_total_pages() {
		$total = $this->get_total_items();
		return (int) ceil( $total / $this->per_page );
	}

	/**
	 * Generate sort URL for a column.
	 *
	 * @param string $column Column name
	 * @return string URL with sort parameters
	 * @since 1.0.0
	 */
	protected function get_sort_url( $column ) {
		$order = 'ASC';
		if ( $this->orderby === $column ) {
			$order = ( $this->order === 'ASC' ) ? 'DESC' : 'ASC';
		}

		$args = array(
			'orderby' => $column,
			'order'   => $order,
		);

		if ( $this->search ) {
			$args['s'] = $this->search;
		}
		if ( $this->current_page > 1 ) {
			$args['paged'] = $this->current_page;
		}

		return add_query_arg( $args );
	}

	/**
	 * Generate pagination link for a page number.
	 *
	 * @param int $page Page number
	 * @return string URL
	 * @since 1.0.0
	 */
	protected function get_pagenum_link( $page ) {
		$args = array();
		if ( $page > 1 ) {
			$args['paged'] = $page;
		}
		if ( $this->orderby ) {
			$args['orderby'] = $this->orderby;
		}
		if ( $this->order ) {
			$args['order'] = $this->order;
		}
		if ( $this->search ) {
			$args['s'] = $this->search;
		}
		return add_query_arg( $args );
	}

	/**
	 * Check if a column is currently being sorted.
	 *
	 * @param string $column Column name
	 * @return bool
	 * @since 1.0.0
	 */
	protected function is_sorted( $column ) {
		return $this->orderby === $column;
	}

	/**
	 * Get sort arrow indicator.
	 *
	 * @param string $column Column name
	 * @return string Arrow symbol
	 * @since 1.0.0
	 */
	protected function get_sort_arrow( $column ) {
		if ( ! $this->is_sorted( $column ) ) {
			return '';
		}
		return ( 'ASC' === $this->order ) ? ' ▲' : ' ▼';
	}

	/**
	 * Render the content of a single cell.
	 *
	 * Can be overridden in child class.
	 * Supports custom methods: column_{name}().
	 *
	 * @param array  $item       Row from database
	 * @param string $column_name Column name
	 * @return string HTML content
	 * @since 2.4.0
	 *
	 * @example
	 *     public function column_actions( $item ) {
	 *         return '<a href="#">Edit</a>';
	 *     }
	 */
	protected function render_cell( $item, $column_name ) {
		$method = 'column_' . $column_name;
		if ( method_exists( $this, $method ) ) {
			return $this->$method( $item );
		}

		return esc_html( $item[ $column_name ] ?? '' );
	}

	/**
	 * Main method: render the full HTML table.
	 *
	 * Includes optional search form, table, and pagination.
	 *
	 * @return string HTML output
	 * @since 1.0.0
	 *
	 * @example
	 *     echo $table->display();
	 */
	public function display() {
		if ( ! $this->db || ! $this->full_table_name ) {
			return '<p>Error: Table not initialized.</p>';
		}

		$items        = $this->get_items();
		$total_pages  = $this->get_total_pages();
		$container_id = 'wpp-table-container-' . md5( $this->table_name );

		ob_start();
		?>
        <div id="<?php echo esc_attr( $container_id ); ?>" class="wpp-table-container">
            <!-- Search Form (optional) -->
			<?php if ( $this->show_search ): ?>
                <form method="get" class="wpp-table-search-form">
					<?php foreach ( $_GET as $key => $value ): ?>
						<?php if ( ! in_array( $key, [ 's', 'paged', 'orderby', 'order' ] ) ): ?>
                            <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
						<?php endif; ?>
					<?php endforeach; ?>

                    <input
                            type="text"
                            name="s"
                            placeholder="<?php esc_attr_e( 'Search...', 'wpp' ); ?>"
                            value="<?php echo esc_attr( $this->search ); ?>"
                            class="wpp-table-search-input"
                    />
                    <button type="submit" class="wpp-table-search-button">
						<?php esc_html_e( 'Search', 'wpp' ); ?>
                    </button>
					<?php if ( $this->search ): ?>
                        <a href="<?php echo remove_query_arg( 's' ); ?>" class="wpp-table-clear-search">
							<?php esc_html_e( 'Clear', 'wpp' ); ?>
                        </a>
					<?php endif; ?>
                </form>
			<?php endif; ?>

            <!-- Data Table -->
            <table class="wpp-data-table">
                <thead>
                <tr>
					<?php foreach ( $this->columns as $key => $title ): ?>
                        <th>
							<?php if ( in_array( $key, $this->sortable_columns ) ): ?>
                                <a href="<?php echo esc_url( $this->get_sort_url( $key ) ); ?>">
									<?php echo esc_html( $title ); ?>
                                    <span class="sort-arrow"><?php echo $this->get_sort_arrow( $key ); ?></span>
                                </a>
							<?php else: ?>
								<?php echo esc_html( $title ); ?>
							<?php endif; ?>
                        </th>
					<?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
				<?php if ( ! empty( $items ) ): ?>
					<?php foreach ( $items as $item ): ?>
                        <tr>
							<?php foreach ( array_keys( $this->columns ) as $column ): ?>
                                <td><?php echo $this->render_cell( $item, $column ); ?></td>
							<?php endforeach; ?>
                        </tr>
					<?php endforeach; ?>
				<?php else: ?>
                    <tr>
                        <td colspan="<?php echo count( $this->columns ); ?>" class="wpp-no-items">
							<?php esc_html_e( 'No items found.', 'wpp' ); ?>
                        </td>
                    </tr>
				<?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
			<?php $this->display_pagination(); ?>
        </div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render pagination controls.
	 *
	 * @since 1.0.0
	 */
	protected function display_pagination() {
		$total_pages = $this->get_total_pages();
		if ( $total_pages <= 1 ) {
			return;
		}

		$current = $this->current_page;
		$links   = array();
		$range   = 2;

		$links[] = 1;

		if ( $current - $range > 2 ) {
			$links[] = '...';
		}

		$start = max( 2, $current - $range );
		$end   = min( $total_pages - 1, $current + $range );

		for ( $i = $start; $i <= $end; $i++ ) {
			$links[] = $i;
		}

		if ( $current + $range < $total_pages - 1 ) {
			$links[] = '...';
		}

		if ( $total_pages > 1 ) {
			$links[] = $total_pages;
		}

		echo '<div class="wpp-pagination">';
		foreach ( $links as $link ) {
			if ( $link === '...' ) {
				echo '<span class="dots">&hellip;</span>';
			} elseif ( $link == $current ) {
				echo '<span class="current">' . $link . '</span>';
			} else {
				printf(
					'<a href="%s">%d</a>',
					esc_url( $this->get_pagenum_link( $link ) ),
					$link
				);
			}
		}
		echo '</div>';
	}
}