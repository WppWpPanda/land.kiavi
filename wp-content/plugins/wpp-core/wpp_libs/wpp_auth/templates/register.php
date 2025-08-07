<?php
/**
 * Registration Form Template for Wpp_Auth Modal System
 *
 * This template renders the user registration form inside the authentication modal.
 * It is dynamically loaded via AJAX or displayed directly on the `/wpp-login` page.
 * The form collects username, email, and password, and includes client/server validation feedback.
 *
 * Conditional logic ensures navigation links are only shown if the target form
 * is enabled in the `Wpp_Auth` configuration (`$allowed_forms`).
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
 * @see        https://html.spec.whatwg.org/multipage/forms.html#registration-form
 * @see        https://www.w3.org/WAI/tutorials/forms/
 * @see        https://developer.wordpress.org/plugins/security/nonces/
 * @see        https://developer.wordpress.org/reference/functions/sanitize_user/
 * @see        https://developer.wordpress.org/reference/functions/sanitize_email/
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registration Form
 *
 * Displays a secure, accessible registration form with:
 * - Username input (text field)
 * - Email input (email type for validation)
 * - Password input (password field)
 * - Submit button
 * - Error message container (populated via JavaScript on validation or server errors)
 * - Dynamic link to login form (if allowed)
 *
 * @uses $this->is_form_allowed() to conditionally hide the login link
 * @see  Wpp_Auth::load_form() For inclusion logic
 * @see  wpp-auth.js For AJAX submission handling
 * @see  Wpp_Auth::ajax_register() For server-side processing
 */
?>
    <h3><?php _e( 'Register', 'wpp-auth' ); ?></h3>

    <form id="wpp-register-form" method="post">
        <!-- Username Field -->
        <label for="wpp-reg-username"><?php _e( 'Username', 'wpp-auth' ); ?></label>
        <input
                type="text"
                id="wpp-reg-username"
                name="username"
                required
                autocomplete="username"
                pattern="[a-zA-Z0-9_\-]{3,}"
                title="<?php esc_attr_e( 'Must be at least 3 characters, using letters, numbers, underscore or hyphen.', 'wpp-auth' ); ?>"
        >

        <!-- Email Field -->
        <label for="wpp-reg-email"><?php _e( 'Email', 'wpp-auth' ); ?></label>
        <input
                type="email"
                id="wpp-reg-email"
                name="email"
                required
                autocomplete="email"
        >

        <!-- Password Field -->
        <label for="wpp-reg-password"><?php _e( 'Password', 'wpp-auth' ); ?></label>
        <input
                type="password"
                id="wpp-reg-password"
                name="password"
                required
                autocomplete="new-password"
                minlength="6"
        >

        <!-- Submit Button -->
        <button type="submit">
			<?php _e( 'Register', 'wpp-auth' ); ?>
        </button>

        <!-- Error Message Container -->
        <!-- Populated dynamically by JavaScript on validation or server-side errors -->
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