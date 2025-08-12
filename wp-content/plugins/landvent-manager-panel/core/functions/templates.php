<?php
defined( 'ABSPATH' ) || exit;

function landvent_manager_header_main() {
	get_header(); ?>
    <div class="container-fluid">
    <div class="row">

    <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
        <div class="wpp-iside">
			<?php do_action( 'wpp_lmp_nav_side' ) ?>
        </div>
    </nav>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
<?php }


function landvent_manager_footer_main() { ?>
    </main>
    </div>
    </div>

	<?php get_footer();
}


/**
 * Render a link to the 'manager-dashboard' page with a home Dashicon if the user has access.
 *
 * This function checks if the current context allows access to the manager dashboard
 * via the `wpp_is_manager_dashboard()` function. If it does NOT return false (i.e., returns true
 * or any truthy value), it generates a styled link to the page with the slug 'manager-dashboard',
 * including the Dashicons 'home' icon.
 *
 * If the check fails or the page doesn't exist, nothing is returned (or rendered).
 *
 * @return void Outputs HTML directly (recommended for templates). Use _get variant for return.
 *
 * @package     WP_Panda
 * @subpackage  Dashboard
 * @author      WP_Panda <panda@wp-panda.pro>
 * @copyright   2025 WP_Panda. All rights reserved.
 * @license     GNU/GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @version     1.0.0
 *
 * @uses wpp_is_manager_dashboard() bool|int Returns truthy if user can access manager dashboard; false otherwise.
 *
 * @example
 *     wpp_render_manager_dashboard_link();
 *     // Output: <a href="https://yoursite.com/manager-dashboard" class="wpp-manager-link">
 *     //           <span class="dashicons dashicons-home"></span> Dashboard
 *     //         </a>
 *
 * @example (Conditional usage)
 *     if ( wpp_is_manager_dashboard() ) {
 *         wpp_render_manager_dashboard_link();
 *     }
 */
function wpp_render_manager_dashboard_link() {

	// Step 1: Check if the user is allowed to view the manager dashboard
	// We use "not false" logic as specified: any value except `false` means allowed
	if ( wpp_is_manager_dashboard() === false ) {
		// No access — do not render anything
		return;
	}


	$safe_url = esc_url( get_home_url() . '/manager-dashboard' );

	// Step 4: Translate the link text (for multilingual support)
	$link_text = esc_html__( 'Main Dashboard Page' );

	// Step 5: Output the HTML link with Dashicon
	// Uses inline Dashicon (dashicons-home) — requires Dashicons to be enqueued on the page
	// If Dashicons are not loaded, add: wp_enqueue_style( 'dashicons' );
	echo '<a href="' . $safe_url . '" class="wpp-manager-link" title="' . $link_text . '">';
	echo '<i class="fas fa-home" aria-hidden="true"></i>'; // Alternatives: fa-home, fa-chart-pie, fa-th-large
	echo ' ' . $link_text;
	echo '</a>';
}