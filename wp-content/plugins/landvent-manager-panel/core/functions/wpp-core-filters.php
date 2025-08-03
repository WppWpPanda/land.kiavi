<?php
defined( 'ABSPATH' ) || exit;
/**
 * Adds body classes for manager dashboard endpoints
 *
 * @since 1.0.0
 */
function wpp_add_manager_endpoint_body_classes($classes) {
	$current_endpoint = wpp_is_manager_dashboard();

	if ($current_endpoint) {
		$classes[] = 'wpp-lmd-point';
		$classes[] = 'wpp-lmd-point-' . sanitize_html_class($current_endpoint);

		// For loan pages
		if (strpos($current_endpoint, 'loan') === 0) {
			$classes[] = 'wpp-lmd-loan';
			$loan_id = get_query_var('loan_id', 0);
			if ($loan_id) {
				$classes[] = 'wpp-lmd-loan-' . absint($loan_id);
			}
		}
	}

	return $classes;
}
add_filter('body_class', 'wpp_add_manager_endpoint_body_classes');