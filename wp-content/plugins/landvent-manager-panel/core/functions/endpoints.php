<?php
/**
 * LandVent Manager Endpoints Class
 *
 * This class handles the creation and management of custom endpoints for the Manager Dashboard.
 * It registers rewrite rules, query variables, and securely handles requests based on user roles.
 * The system supports theme override templates, plugin fallbacks, and access control.
 *
 * @package LandVent
 * @subpackage Manager
 * @since 1.0.0
 * @author WP_Panda <panda@wp-panda.pro>
 * @link https://developer.wordpress.org/plugins/woocommerce/woocommerce-conditional-functions/ current_user_can()
 * @link https://developer.wordpress.org/reference/functions/add_rewrite_rule/ add_rewrite_rule()
 * @link https://developer.wordpress.org/reference/functions/locate_template/ locate_template()
 * @link https://developer.wordpress.org/reference/functions/set_query_var/ set_query_var()
 * @link https://developer.wordpress.org/reference/functions/get_query_var/ get_query_var()
 * @link https://developer.wordpress.org/reference/functions/add_filter/ add_filter()
 * @link https://developer.wordpress.org/reference/functions/add_action/ add_action()
 * @link https://www.php.net/manual/en/language.oop5.patterns.php#singleton-pattern Singleton Pattern
 * @link https://developer.wordpress.org/plugins/rewrite/ WordPress Rewrite API
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class LandVent_Manager_Endpoints
 *
 * Manages custom URL endpoints for the Manager Dashboard in WordPress.
 * Implements a singleton pattern to prevent multiple instances.
 * Registers rewrite rules for URLs like `/manager-dashboard`, `/manager-dashboard/law-firms-clerks`, etc.
 * Ensures only authorized users (admins or 'loans_manager' role) can access these pages.
 * Supports template overriding from the active theme and falls back to plugin templates.
 *
 * @since 1.0.0
 */
class LandVent_Manager_Endpoints {

	/**
	 * Holds the single instance of this class.
	 *
	 * @since 1.0.0
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Returns the singleton instance of this class.
	 *
	 * Ensures only one instance of the endpoint manager exists at any time.
	 * This prevents duplicate actions/filters and maintains consistency.
	 *
	 * @since 1.0.0
	 * @return self
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * Initializes hooks for:
	 * - Adding custom rewrite rules on 'init'
	 * - Registering custom query variables via 'query_vars' filter
	 * - Handling requests during 'template_redirect'
	 *
	 * @since 1.0.0
	 * @see add_action() https://developer.wordpress.org/reference/functions/add_action/
	 * @see add_filter() https://developer.wordpress.org/reference/functions/add_filter/
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'add_endpoints' ), 10 );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 10, 1 );
		add_action( 'template_redirect', array( $this, 'handle_requests' ), 10 );
	}

	/**
	 * Adds custom rewrite rules for the Manager Dashboard.
	 *
	 * Registers URL patterns that map to internal query variables.
	 * These rules allow clean URLs like:
	 * - /manager-dashboard
	 * - /manager-dashboard/law-firms-clerks
	 * - /manager-dashboard/loan/123
	 *
	 * Rules are added with 'top' priority to ensure they are matched before other rules.
	 *
	 * @since 1.0.0
	 * @return void
	 * @global WP_Rewrite $wp_rewrite WordPress rewrite component.
	 * @link https://developer.wordpress.org/reference/functions/add_rewrite_rule/
	 * @link https://developer.wordpress.org/plugins/rewrite/customizing-permalinks/
	 *
	 * Example:
	 *   Visiting `/manager-dashboard` triggers `manager_dashboard=main`
	 *   Visiting `/manager-dashboard/brokers` triggers `manager_dashboard=brokers`
	 *   Visiting `/manager-dashboard/loan/456` triggers `manager_dashboard=loan&loan_id=456`
	 */
	public function add_endpoints() {
		// Main dashboard endpoint
		add_rewrite_rule(
			'^manager-dashboard/?$',                     // Match: /manager-dashboard or /manager-dashboard/
			'index.php?manager_dashboard=main',          // Internal: set query var `manager_dashboard` to 'main'
			'top'                                        // Priority: high so it's not overridden
		);

		// Child sections: law-firms-clerks, title-companies, brokers, appraisers
		add_rewrite_rule(
			'^manager-dashboard/(law-firms-clerks|title-companies|brokers|appraisers)/?$', // Capture group $matches[1]
			'index.php?manager_dashboard=$matches[1]',   // Set manager_dashboard to captured value
			'top'
		);

		// Loan detail page with numeric ID
		add_rewrite_rule(
			'^manager-dashboard/loan/([0-9]+)/?$',       // Match loan ID (digits only)
			'index.php?manager_dashboard=loan&loan_id=$matches[1]', // Set both query vars
			'top'
		);

		/**
		 * @todo Flush rewrite rules only once after plugin activation, not on every `init`.
		 *       Use register_activation_hook() to flush rules during activation.
		 *       Otherwise, this can cause performance issues.
		 *       See: https://developer.wordpress.org/reference/functions/flush_rewrite_rules/
		 */
	}

	/**
	 * Adds custom query variables so WordPress recognizes them in URLs.
	 *
	 * Without registering query vars, WordPress would ignore `manager_dashboard` and `loan_id`.
	 * This allows `get_query_var('manager_dashboard')` to work properly.
	 *
	 * @since 1.0.0
	 * @param array $vars Existing query variables.
	 * @return array Modified list of query variables.
	 * @link https://developer.wordpress.org/reference/hooks/query_vars/
	 *
	 * Example:
	 *   After registration, visiting `/manager-dashboard/loan/789` allows:
	 *   - get_query_var('manager_dashboard') => 'loan'
	 *   - get_query_var('loan_id') => '789'
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'manager_dashboard'; // e.g., 'main', 'law-firms-clerks', 'loan'
		$vars[] = 'loan_id';            // e.g., 123

		return $vars;
	}

	/**
	 * Handles incoming requests to Manager Dashboard endpoints.
	 *
	 * Checks if the current request matches a Manager Dashboard page.
	 * Verifies user permissions (must be admin or have 'loans_manager' role).
	 * Loads the appropriate template or shows an access denied message.
	 *
	 * Hooked to 'template_redirect' to act before template loading.
	 *
	 * @since 1.0.0
	 * @global WP_Query $wp_query Current query object.
	 * @return void
	 * @link https://developer.wordpress.org/reference/hooks/template_redirect/
	 */
	public function handle_requests() {
		global $wp_query;

		// Check if this is a Manager Dashboard request
		$dashboard_page = $wp_query->get( 'manager_dashboard' );

		if ( ! $dashboard_page ) {
			return; // Not a relevant request, do nothing
		}

		// Authorization check
		$has_permission = current_user_can( 'manage_options' ) || $this->has_role( 'loans_manager' );

		if ( ! $has_permission ) {
			// User is not logged in or lacks required role
			$this->fallback_template_not_logged( $dashboard_page );
			exit;
		}

		// Load the appropriate template based on the requested page
		$this->load_template( $dashboard_page );
	}

	/**
	 * Checks if the current user has a specific role.
	 *
	 * Uses wp_get_current_user() to retrieve user data and checks role membership.
	 *
	 * @since 1.0.0
	 * @param string $role Role name to check (e.g., 'loans_manager').
	 * @return bool True if user has the role, false otherwise.
	 * @global WP_User $current_user Current logged-in user object.
	 * @link https://developer.wordpress.org/reference/functions/wp_get_current_user/
	 * @link https://developer.wordpress.org/reference/classes/wp_user/
	 *
	 * Example:
	 *   $this->has_role('loans_manager') → true if user has that role
	 */
	private function has_role( $role ) {
		$user = wp_get_current_user();
		return in_array( $role, (array) $user->roles, true );
	}

	/**
	 * Loads the appropriate template for the requested dashboard page.
	 *
	 * Template loading priority:
	 * 1. Child/parent theme: `landvent-manager/{page}.php` or `landvent-manager-dashboard/{page}.php`
	 * 2. Plugin directory: `{plugin_path}/public/templates/manager-dashboard/{page}.php`
	 * 3. Fallback: Basic HTML output via fallback_template()
	 *
	 * Sets query vars (`loan_id`, `dashboard_page`) for use in templates.
	 *
	 * @since 1.0.0
	 * @param string $template_name The requested dashboard section (e.g., 'main', 'loan').
	 * @global WP_Query $wp_query Used to get `loan_id` query variable.
	 * @return void
	 * @link https://developer.wordpress.org/reference/functions/locate_template/
	 * @link https://developer.wordpress.org/reference/functions/set_query_var/
	 *
	 * Example:
	 *   Requesting `/manager-dashboard/law-firms-clerks` calls $this->load_template('law-firms-clerks');
	 */
	public function load_template( $template_name ) {
		global $wp_query;

		// Get optional loan ID from query
		$loan_id = absint( $wp_query->get( 'loan_id', 0 ) );

		// 1. Try to find template in active theme (child theme first)
		$theme_templates = array(
			"landvent-manager/{$template_name}.php",
			"landvent-manager-dashboard/{$template_name}.php"
		);
		$theme_template = locate_template( $theme_templates );

		if ( $theme_template ) {
			include $theme_template;
			return;
		}

		// 2. Try plugin's built-in template
		$plugin_template_path = WPP_LOAN_MANAGER_PATH . "public/templates/manager-dashboard/{$template_name}.php";

		if ( file_exists( $plugin_template_path ) ) {
			// Make variables available in template scope
			set_query_var( 'loan_id', $loan_id );
			set_query_var( 'dashboard_page', $template_name );

			include $plugin_template_path;
			return;
		}

		// 3. Final fallback: basic inline HTML
		$this->fallback_template( $template_name );
	}

	/**
	 * Renders a fallback template when no theme/plugin template is found.
	 *
	 * Outputs minimal HTML with headers/footers and a contextual message.
	 * Used for development or when templates are missing.
	 *
	 * @since 1.0.0
	 * @param string $template_name The requested page (e.g., 'main', 'law-firms-clerks').
	 * @return void
	 * @link https://developer.wordpress.org/reference/functions/get_header/
	 * @link https://developer.wordpress.org/reference/functions/get_footer/
	 */
	public function fallback_template( $template_name ) {
		get_header();

		echo '<div class="landvent-manager-container">';

		switch ( $template_name ) {
			case 'main':
				echo '<h1>Manager Dashboard</h1>';
				echo '<p>Welcome to the Manager Dashboard</p>';
				break;

			case 'law-firms-clerks':
				echo '<h1>Law Firms & Clerks</h1>';
				break;

			case 'title-companies':
				echo '<h1>Title Companies</h1>';
				break;

			case 'brokers':
				echo '<h1>Brokers</h1>';
				break;

			case 'appraisers':
				echo '<h1>Appraisers</h1>';
				break;

			case 'loan':
				$loan_id = get_query_var( 'loan_id', 0 );
				echo '<h1>Loan Details</h1>';
				echo '<p>Viewing loan ID: ' . intval( $loan_id ) . '</p>';
				break;

			default:
				echo '<h1>Page Not Found</h1>';
				echo '<p>The requested page could not be found.</p>';
				break;
		}

		echo '</div>';

		get_footer();
	}

	/**
	 * Renders an access denied page for unauthorized users.
	 *
	 * Shown when a user tries to access the dashboard without proper permissions.
	 * Displays a localized message and wraps content in standard header/footer.
	 *
	 * @since 1.0.0
	 * @param string $template_name The originally requested page (for logging/debugging).
	 * @return void
	 */
	public function fallback_template_not_logged( $template_name ) {
		get_header();

		echo '<div class="landvent-manager-container" style="display: flex;align-content: center;align-items: center;justify-content: center;height: calc( 100vh - 100px);">';
		echo '<h1>' . esc_html__( 'You must be logged in to access this section.', 'landvent' ) . '</h1>';
		echo '</div>';

		get_footer();
	}
}

// Initialize the endpoints system using singleton pattern
// This ensures the class is loaded and hooks are registered
LandVent_Manager_Endpoints::get_instance();

/**
 * @todo Implement proper rewrite flush on plugin activation/deactivation.
 *       Currently, rewrite rules are added on every `init`, but flushing should happen only once.
 *       Example:
 *         register_activation_hook( __FILE__, 'flush_rewrite_rules' );
 *         register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
 *
 * @todo Add nonce verification for any POST requests handled within dashboard templates.
 * @todo Add logging for unauthorized access attempts.
 * @todo Support for REST API endpoints as an alternative to page-based routing.
 * @todo Internationalization (i18n): Wrap all UI strings with __() or esc_html__().
 * @todo Unit testing: Mock WP_Query and test handle_requests() behavior.
 * @todo Add capability mapping instead of hardcoding 'loans_manager' role.
 * @todo Add support for custom capabilities (e.g., 'view_manager_dashboard').
 */