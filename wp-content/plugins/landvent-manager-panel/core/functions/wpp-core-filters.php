<?php
defined( 'ABSPATH' ) || exit;
/**
 * Adds custom body classes for Manager Dashboard endpoints in the frontend.
 *
 * This function enhances the body class list with specific CSS classes
 * based on the current Manager Dashboard endpoint being viewed.
 * It enables granular styling and JavaScript targeting for different
 * sections of the Manager Dashboard (e.g., loan management, settings, etc.).
 *
 * @package WP_Panda_Loan_Manager
 * @subpackage Manager_Dashboard
 * @since 1.0.0
 *
 * @author WP_Panda <panda@wp-panda.pro>
 *
 * @link https://developer.wordpress.org/reference/functions/body_class/ WordPress `body_class()` function
 * @link https://developer.wordpress.org/reference/functions/add_filter/ `add_filter()` documentation
 * @link https://www.php.net/manual/en/function.sanitize-html-class.php `sanitize_html_class()` in PHP
 * @link https://developer.wordpress.org/reference/functions/get_query_var/ `get_query_var()` usage
 *
 * @example
 * // When on the "loan-details" endpoint with loan_id=42, the following classes are added:
 * // <body class="... wpp-lmd-point wpp-lmd-point-loan-details wpp-lmd-loan wpp-lmd-loan-42">
 *
 * @todo Add support for user role-based classes (e.g., wpp-lmd-role-manager)
 * @todo Introduce context-specific classes for mobile/desktop views
 * @todo Consider caching the endpoint detection result for performance
 *
 * @param array $classes The existing array of body classes.
 *
 * @return array Modified array of body classes with Manager Dashboard-specific additions.
 */
function wpp_add_manager_endpoint_body_classes( $classes ) {
	// Prevent execution in admin area, CLI, or REST requests
	// @see https://developer.wordpress.org/reference/functions/is_admin/
	// @see https://developer.wordpress.org/rest-api/
	if ( is_admin() || defined( 'WP_CLI' ) || defined( 'REST_REQUEST' ) ) {
		return $classes;
	}

	/**
	 * Detect the current Manager Dashboard endpoint.
	 *
	 * This function (assumed to be defined elsewhere in the plugin)
	 * returns a string (e.g., 'loan-list', 'loan-details') if the current page
	 * is a Manager Dashboard endpoint, or false otherwise.
	 *
	 * @since 1.0.0
	 * @return string|false The current endpoint slug or false if not applicable.
	 *
	 * @note Ensure `wpp_is_manager_dashboard()` is defined before this hook runs.
	 * @see wpp_is_manager_dashboard()
	 */
	$current_endpoint = wpp_is_manager_dashboard();

	// If no valid endpoint is detected, return original classes early
	if ( ! $current_endpoint ) {
		return $classes;
	}

	/**
	 * Add base class indicating that a Manager Dashboard point is active.
	 *
	 * This class can be used for global dashboard styling resets or layout rules.
	 *
	 * Example: .wpp-lmd-point { ... }
	 */
	$classes[] = 'wpp-lmd-point';

	/**
	 * Add endpoint-specific class.
	 *
	 * Sanitizes the endpoint name to ensure it's safe for use in HTML class attributes.
	 *
	 * @see sanitize_html_class() - Ensures the string is a valid HTML class name
	 * @link https://developer.wordpress.org/reference/functions/sanitize_html_class/
	 *
	 * Example: 'loan-details' → 'wpp-lmd-point-loan-details'
	 */
	$classes[] = 'wpp-lmd-point-' . sanitize_html_class( $current_endpoint );

	/**
	 * Special handling for endpoints starting with 'loan' (e.g., loan-view, loan-edit).
	 *
	 * Adds a shared class for all loan-related pages to enable collective styling.
	 *
	 * Example: .wpp-lmd-loan { background: #f8f9fa; }
	 */
	if ( strpos( $current_endpoint, 'loan' ) === 0 ) {
		$classes[] = 'wpp-lmd-loan';

		/**
		 * Retrieve the 'loan_id' from the current query variables.
		 *
		 * Uses WordPress's `get_query_var()` to safely fetch the 'loan_id' parameter.
		 * Defaults to 0 if not set.
		 *
		 * @link https://developer.wordpress.org/reference/functions/get_query_var/
		 *
		 * Note: Ensure 'loan_id' is registered as a public query variable
		 * via `add_rewrite_endpoint()` or `add_query_var()`.
		 */
		$loan_id = get_query_var( 'loan_id', 0 );

		/**
		 * If a valid loan ID is present, add a class specific to that loan.
		 *
		 * Uses `absint()` to ensure the ID is a positive integer,
		 * preventing injection or invalid values.
		 *
		 * @link https://developer.wordpress.org/reference/functions/absint/
		 *
		 * Example: loan_id = 123 → class: wpp-lmd-loan-123
		 */
		if ( $loan_id ) {
			$classes[] = 'wpp-lmd-loan-' . absint( $loan_id );
		}
	}

	/**
	 * Return the modified classes array.
	 *
	 * These classes will be output by `body_class()` in the theme's <body> tag.
	 *
	 * @see body_class() - Typically used in header.php
	 * @example <body <?php body_class(); ?>>
	 */
	return $classes;
}

/**
 * Hook into WordPress's 'body_class' filter to inject custom classes.
 *
 * The 'body_class' filter allows themes and plugins to add dynamic classes
 * to the <body> HTML element for better CSS targeting.
 *
 * @hooked to 'body_class'
 * @priority 10 (default)
 * @param array $classes Original body classes
 * @uses wpp_add_manager_endpoint_body_classes() - Callback function
 *
 * @link https://developer.wordpress.org/reference/hooks/body_class/
 */
add_filter( 'body_class', 'wpp_add_manager_endpoint_body_classes' );