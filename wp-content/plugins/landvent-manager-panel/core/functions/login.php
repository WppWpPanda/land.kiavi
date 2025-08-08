<?php
/**
 * WPP Authentication Bootstrap
 *
 * Initializes the Wpp_Auth system and renders a dynamic Login/Logout button.
 * This file is responsible for:
 * - Instantiating the `Wpp_Auth` class with custom configuration.
 * - Outputting a themed button that opens the modal or logs the user out.
 * - Ensuring compatibility by checking class existence.
 *
 * @package    Landvent_Manager_Panel
 * @subpackage Core/Functions
 * @author     WP_Panda <panda@wp-panda.pro>
 * @copyright  2025 WP_Panda
 * @license    GNU General Public License v3.0
 * @version    1.0
 * @link       https://github.com/wp-panda/landvent-manager-panel
 *
 * @see        https://developer.wordpress.org/reference/functions/add_action/
 * @see        https://developer.wordpress.org/reference/functions/class_exists/
 * @see        https://developer.wordpress.org/reference/functions/is_user_logged_in/
 * @see        https://developer.wordpress.org/reference/functions/wp_logout_url/
 * @see        https://developer.wordpress.org/reference/functions/home_url/
 * @see        https://www.php.net/manual/en/function.sprintf.php
 * @see        https://www.php.net/manual/en/function.esc-attr.php
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * Initializes the authentication system and outputs the Login/Logout button.
 *
 * This function:
 * - Checks if the `Wpp_Auth` class is available to prevent fatal errors.
 * - Determines the user's login status.
 * - Sets appropriate text, class, and URL for the button.
 * - Outputs a secure, escaped anchor tag with the `open-auth-modal` class,
 *   which triggers the modal window when clicked (handled by JavaScript).
 *
 * Example output:
 * - For logged-out users: `<a href="#" class="open-auth-modal wpp-auth-button login">Login</a>`
 * - For logged-in users: `<a href="https://yoursite.com/wp-login.php?action=logout..." class="wpp-auth-button logout">Logout</a>`
 *
 * @since 1.0
 * @return void
 * @global bool $current_user
 */
function wpp_setup_auth_and_button() {
	// Ensure the Wpp_Auth class is loaded before proceeding
	// Prevents "Fatal error: Class 'Wpp_Auth' not found"
	if ( ! class_exists( 'Wpp_Auth' ) ) {
		return;
	}

	// Determine user authentication state
	$is_logged_in = is_user_logged_in();

	// Define button properties based on login status
	$button_text  = $is_logged_in ? 'Logout' : 'Login';
	$icon  = $is_logged_in ? 'out' : 'in';
	$button_class = $is_logged_in ? 'logout' : 'login';
	$url          = $is_logged_in ? wp_logout_url( home_url() . '/manager-dashboard' ) : '#';

	/**
	 * Outputs the formatted button HTML.
	 *
	 * Uses `printf()` for safe string interpolation.
	 * Escapes the class attribute with `esc_attr()` to prevent XSS.
	 *
	 * @see printf()       https://www.php.net/manual/en/function.printf.php
	 * @see esc_attr()     https://developer.wordpress.org/reference/functions/esc_attr/
	 */
	printf(
		'<a href="%s" class="open-auth-modal wpp-auth-button %s"><i class="fas fa-sign-%s-alt"></i><span>%s</span></a>',
		$url,
		esc_attr( $button_class ),
		$icon,
		$button_text
	);
}

/**
 * Bootstraps the Wpp_Auth system after all plugins are loaded.
 *
 * Uses the `plugins_loaded` hook to ensure:
 * - The `Wpp_Auth` class is available (loaded by its plugin).
 * - WordPress is fully initialized.
 *
 * Instantiates `Wpp_Auth` with the following configuration:
 * - Custom login slug: `wpp-login` → accessible at `/wpp-login`
 * - Redirect URL: `/manager-dashboard` after successful login
 * - Only the login form is enabled (registration and password recovery disabled)
 *
 * @hooked plugins_loaded
 * @since 1.0
 * @see    plugins_loaded https://developer.wordpress.org/reference/hooks/plugins_loaded/
 * @see    Wpp_Auth::__construct() For available arguments
 */
add_action( 'plugins_loaded', function () {
	new Wpp_Auth( [
		'login_slug'    => 'wpp-login',
		'redirect_to'   => home_url( '/manager-dashboard' ),
		'allowed_forms' => [ 'login' ], // Only enable login; disable register & forgot-password
	] );
} );