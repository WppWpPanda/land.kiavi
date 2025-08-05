<?php
/**
 * Renders the Manager Dashboard Sidebar Menu with Active Item Highlighting
 *
 * This function outputs a dynamic sidebar navigation menu for the Manager Dashboard,
 * conditionally rendering either main dashboard pages or loan-specific sections
 * based on the current endpoint. It supports active state highlighting and uses
 * Font Awesome icons for visual cues.
 *
 * @package    WP_Loan_Manager_Pro
 * @subpackage Dashboard
 * @since      1.0.0
 * @author     WP_Panda <panda@wp-panda.pro>
 * @license    GNU General Public License v3.0
 * @link       https://developer.wordpress.org/reference/functions/home_url/
 * @link       https://www.php.net/manual/en/function.sanitize-key.php
 * @link       https://developer.wordpress.org/reference/functions/esc_attr/
 * @link       https://developer.wordpress.org/reference/functions/esc_url/
 * @link       https://developer.wordpress.org/reference/functions/esc_html/
 *
 * @uses       wpp_is_manager_dashboard() Determines the current dashboard endpoint (assumed custom function).
 *
 * @hook       wpp_lmp_nav_side Runs in the sidebar area of the manager dashboard.
 *
 * @example
 * // Add this to your template where sidebar nav is expected:
 * do_action( 'wpp_lmp_nav_side' );
 *
 * // Or call directly (not recommended unless context is ensured):
 * if ( function_exists( 'lmp_render_sidebar_menu' ) ) {
 *     lmp_render_sidebar_menu();
 * }
 *
 * @todo       Refactor to use translatable strings via __() for internationalization.
 * @todo       Add filter hook to allow plugins/themes to modify menu items.
 * @todo       Implement caching for menu output if performance becomes an issue.
 * @todo       Validate that `wpp_is_manager_dashboard()` returns expected values.
 * @todo       Consider moving base URL to a configuration constant or option.
 */
function lmp_render_sidebar_menu() {
	// Ensure we're inside WordPress context
	defined( 'ABSPATH' ) || exit;

	/**
	 * Determine the current manager dashboard endpoint.
	 *
	 * @see wpp_is_manager_dashboard() - Custom helper function assumed to return:
	 *        - string (e.g., 'main', 'law-firms-clerks', 'loan')
	 *        - false if not on dashboard
	 *
	 * @since 1.0.0
	 */
	$current_endpoint = wpp_is_manager_dashboard();

	// Exit early if not on any manager dashboard page
	if ( ! $current_endpoint ) {
		return;
	}

	/**
	 * Base URL for all non-loan menu items.
	 *
	 * @see home_url() - Retrieves the site's home URL with optional path.
	 *                   https://developer.wordpress.org/reference/functions/home_url/
	 *
	 * @var string
	 */
	$base_url = home_url( '/manager-dashboard/' );

	/**
	 * Array to hold all menu items before rendering.
	 *
	 * Each item will have:
	 *   - title (string): Display text
	 *   - icon (string, optional): Font Awesome icon name (without 'fa-')
	 *   - url (string): Absolute or anchor URL
	 *   - id (string, optional): HTML ID attribute (used for loan anchors)
	 *   - active (bool): Whether this item should be highlighted
	 *
	 * @var array
	 */
	$menu_items = [];

	/**
	 * Tracks the currently active section within the loan view.
	 *
	 * Determined via $_GET['section'], sanitized for security.
	 *
	 * @var string
	 */
	$current_loan_section = '';

	// Handle loan-specific anchor-based navigation
	if ( $current_endpoint === 'loan' ) {
		/**
		 * Sanitize the 'section' query parameter to prevent XSS.
		 *
		 * @see sanitize_key() - Limits input to lowercase alphanumeric, underscores, and dashes.
		 *                       Ideal for slugs and keys.
		 *                       https://developer.wordpress.org/reference/functions/sanitize_key/
		 */
		$current_loan_section = isset( $_GET['section'] )
			? sanitize_key( $_GET['section'] )
			: 'title-company'; // Default fallback section
	}

	// Build menu items based on current context
	if ( $current_endpoint !== 'loan' ) {
		/**
		 * Define main dashboard navigation items.
		 *
		 * These are top-level pages outside the loan detail view.
		 *
		 * @var array
		 */
		$main_menu_items = [
			'main'             => [ 'title' => 'Home',               'icon' => 'home' ],
			'law-firms-clerks' => [ 'title' => 'Law Firms & Clerks', 'icon' => 'users' ],
			'title-companies'  => [ 'title' => 'Title Companies',    'icon' => 'building' ],
			'brokers'          => [ 'title' => 'Brokers',            'icon' => 'exchange-alt' ],
			'appraisers'       => [ 'title' => 'Appraisers',         'icon' => 'search-dollar' ],
		];

		foreach ( $main_menu_items as $slug => $item ) {
			/**
			 * Construct URL:
			 * - For 'main', use base URL only
			 * - Otherwise, append slug to base URL
			 */
			$url = $slug === 'main' ? $base_url : $base_url . $slug;

			/**
			 * Determine active state:
			 * - True if current endpoint matches this menu item's slug
			 */
			$active = $current_endpoint === $slug;

			/**
			 * Add formatted item to menu array
			 */
			$menu_items[] = [
				'title'  => $item['title'],
				'icon'   => $item['icon'],
				'url'    => $url,
				'active' => $active,
			];

			// Note: Commented-out logic appears redundant or incorrect
			// Original: if( empty($slug) && 'main' === $current_endpoint ) { ... }
			// Since $slug is array key, it's never empty in this loop.
			// Likely debugging code; left as-is but not executed.
		}
	} else {
		/**
		 * Define loan-specific section anchors.
		 *
		 * These correspond to in-page sections via scroll anchors (`#section-name`).
		 *
		 * @var array
		 */
		$loan_menu_items = [
			'applicant-info'      => [ 'title' => 'Applicant Info' ],
			'property-details'    => [ 'title' => 'Property Details' ],
			'term-sheet-details'  => [ 'title' => 'Term Sheet Details' ],
			'additional-reserves' => [ 'title' => 'Additional Reserves' ],
			'fees'                => [ 'title' => 'Fees' ],
			'milestones'          => [ 'title' => 'Milestones' ],
			'payments'            => [ 'title' => 'Payments' ],
			'conditions'          => [ 'title' => 'Conditions' ],
			'investors'           => [ 'title' => 'Investors' ],
			'attorney'            => [ 'title' => 'Attorney' ],
			'title-company'       => [ 'title' => 'Title Company' ],
			'required-documents'  => [ 'title' => 'Required Documents' ],
			'documents'           => [ 'title' => 'Documents' ],
		];

		foreach ( $loan_menu_items as $slug => $item ) {
			/**
			 * Add loan section item using anchor links
			 */
			$menu_items[] = [
				'title'  => $item['title'],
				'url'    => '#' . $slug,                      // Anchor link
				'id'     => 'menu-' . $slug,                  // Unique ID for JS/CSS targeting
				'active' => $current_loan_section === $slug,  // Highlight if matches current section
			];
		}
	}

	/**
	 * Render the final unordered list of navigation items.
	 *
	 * Only proceed if there are menu items to display.
	 *
	 * @see https://getbootstrap.com/docs/5.3/components/navs-tabs/ - Bootstrap nav styles
	 */
	if ( ! empty( $menu_items ) ) {
		echo '<ul class="nav flex-column">';

		foreach ( $menu_items as $item ) {
			/**
			 * Generate active CSS classes.
			 *
			 * Adds 'active' and 'current-point' if item is currently selected.
			 *
			 * @var string
			 */
			$active_class = $item['active'] ? ' active current-point' : '';

			/**
			 * Generate icon HTML if icon is specified.
			 *
			 * Uses Font Awesome 5+ syntax: <i class="fas fa-icon-name"></i>
			 *
			 * @see https://fontawesome.com/v5/docs/web/setup/get-started
			 *
			 * @var string
			 */
			$icon_html = isset( $item['icon'] )
				? '<i class="fas fa-' . esc_attr( $item['icon'] ) . ' me-2"></i>'
				: '';

			?>
            <li class="nav-item">
                <a class="nav-link<?php echo esc_attr( $active_class ); ?>"
                   href="<?php echo esc_url( $item['url'] ); ?>"
					<?php echo isset( $item['id'] ) ? 'id="' . esc_attr( $item['id'] ) . '"' : ''; ?>>
					<?php echo $icon_html . esc_html( $item['title'] ); ?>
                </a>
            </li>
			<?php
		}

		echo '</ul>';
	}
}

/**
 * Hook the sidebar menu renderer into the designated action.
 *
 * @hooked wpp_lmp_nav_side - Triggered in the manager dashboard sidebar template.
 * @priority 10 - Default priority
 * @uses add_action() - WordPress function to register actions.
 * @link https://developer.wordpress.org/reference/functions/add_action/
 */
add_action( 'wpp_lmp_nav_side', 'lmp_render_sidebar_menu' );