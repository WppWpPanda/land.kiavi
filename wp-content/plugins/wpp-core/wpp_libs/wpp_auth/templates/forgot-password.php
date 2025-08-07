<?php
/**
 * Password Recovery Form Template for Wpp_Auth Modal System
 *
 * This template renders the "Forgot Password" form inside the authentication modal.
 * It allows users to enter their email address to request a password reset.
 * The form is processed via AJAX and triggers a temporary password generation and email delivery.
 *
 * Conditional logic ensures that the "Login" navigation link is only displayed
 * if the login form is enabled in the plugin's `allowed_forms` configuration.
 *
 * @package    WPP_Auth
 * @subpackage Templates
 * @author     WP_Panda <panda@wp-panda.pro>
 * @copyright  2025 WP_Panda
 * @license    GNU General Public License v3.0
 * @version    1.0
 * @link       https://github.com/wp-panda/wpp-auth
 *
 * @see        https://developer.wordpress.org/reference/functions/_e/
 * @see        https://developer.wordpress.org/reference/functions/__/
 * @see        https://html.spec.whatwg.org/multipage/forms.html#e-mail-state-(type=email)
 * @see        https://www.w3.org/WAI/tutorials/forms/
 * @see        https://developer.wordpress.org/plugins/security/nonces/
 * @see        https://developer.wordpress.org/reference/functions/sanitize_email/
 * @see        https://developer.wordpress.org/reference/functions/wp_generate_password/
 * @see        https://developer.wordpress.org/reference/functions/wp_mail/
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Forgot Password Form
 *
 * Displays a simple, secure form for password recovery:
 * - Email input field (type="email" for validation)
 * - Submit button
 * - Error message container (populated via JavaScript on invalid input or user not found)
 * - Dynamic link to login form (if allowed)
 *
 * @uses $this->is_form_allowed() to conditionally hide the login link
 * @see  Wpp_Auth::load_form() For inclusion logic
 * @see  wpp-auth.js For AJAX submission handling
 * @see  Wpp_Auth::ajax_forgot_password() For server-side processing
 */
?>
    <h3><?php _e( 'Forgot Password?', 'wpp-auth' ); ?></h3>

    <form id="wpp-forgot-password-form" method="post">
        <!-- Email Field -->
        <label for="wpp-forgot-email"><?php _e( 'Email', 'wpp-auth' ); ?></label>
        <input
                type="email"
                id="wpp-forgot-email"
                name="email"
                required
                autocomplete="email"
                placeholder="<?php esc_attr_e( 'your@email.com', 'wpp-auth' ); ?>"
        >

        <!-- Submit Button -->
        <button type="submit">
			<?php _e( 'Reset Password', 'wpp-auth' ); ?>
        </button>

        <!-- Error Message Container -->
        <!-- Populated dynamically by JavaScript if email is invalid or user not found -->
        <div class="wpp-error-message" aria-live="polite"></div>
    </form>

    <!-- Navigation Link -->
    <!-- Only display "Login" link if the login form is allowed in plugin settings -->
<?php if ( in_array( 'login', $this->allowed_forms ?? [], true ) ) : ?>
    <p>
        <a href="#" data-form="login">
			<?php _e( 'Log In', 'wpp-auth' ); ?>
        </a>
    </p>
<?php endif;