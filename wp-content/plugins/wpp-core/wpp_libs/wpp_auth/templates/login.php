<?php
/**
 * Login Form Template for Wpp_Auth Modal System
 *
 * This template file renders the login form inside the modal window.
 * It is conditionally loaded via AJAX or directly on the `/wpp-login` page.
 * The form includes fields for username and password, submission button,
 * error messaging container, and navigation links to "Register" and "Forgot Password".
 *
 * Conditional logic ensures that navigation links are only shown if the corresponding
 * forms are enabled in the `Wpp_Auth` configuration (`$allowed_forms`).
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
 * @see        https://developer.wordpress.org/themes/template-files-section/partial-and-miscellaneous-templates/
 * @see        https://html.spec.whatwg.org/multipage/forms.html
 * @see        https://www.w3.org/WAI/tutorials/forms/
 * @see        https://developer.wordpress.org/plugins/security/nonces/
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login Form
 *
 * Displays a clean, accessible login form with:
 * - Username input (text field)
 * - Password input (password field)
 * - Submit button
 * - Error message container (populated via JavaScript on failed login)
 * - Dynamic links to registration and password recovery (if allowed)
 *
 * @uses $this->is_form_allowed() to conditionally hide links (passed via JS context in `wppAuthAjax.allowedForms`)
 * @see  Wpp_Auth::load_form() For inclusion logic
 * @see  wpp-auth.js For AJAX submission handling
 */
?>
<h3><?php _e( 'Login', 'wpp-auth' ); ?></h3>

<form id="wpp-login-form" method="post">
    <!-- Username Field -->
    <label for="wpp-username"><?php _e( 'Username', 'wpp-auth' ); ?></label>
    <input
            type="text"
            id="wpp-username"
            name="username"
            required
            autocomplete="username"
    >

    <!-- Password Field -->
    <label for="wpp-password"><?php _e( 'Password', 'wpp-auth' ); ?></label>
    <input
            type="password"
            id="wpp-password"
            name="password"
            required
            autocomplete="current-password"
    >

    <!-- Submit Button -->
    <button type="submit">
		<?php _e( 'Log In', 'wpp-auth' ); ?>
    </button>

    <!-- Error Message Container -->
    <!-- Populated dynamically by JavaScript on login failure -->
    <div class="wpp-error-message" aria-live="polite"></div>
</form>

<!-- Navigation Links -->
<!-- Only display links if the target form is allowed in plugin settings -->
<?php if ( in_array( 'register', $this->allowed_forms ?? [], true ) ) : ?>
    <p>
        <a href="#" data-form="register">
			<?php _e( 'Register', 'wpp-auth' ); ?>
        </a>
    </p>
<?php endif; ?>

<?php if ( in_array( 'forgot-password', $this->allowed_forms ?? [], true ) ) : ?>
    <p>
        <a href="#" data-form="forgot-password">
			<?php _e( 'Forgot password?', 'wpp-auth' ); ?>
        </a>
    </p>
<?php endif;