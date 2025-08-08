<?php
/**
 * WPP Auth Plugin
 *
 * A lightweight, modular authentication system for WordPress that provides login, registration,
 * and password recovery via AJAX-powered modal windows. Designed for easy integration and customization.
 *
 * @package    WPP_Auth
 * @subpackage Core
 * @author     WP_Panda <panda@wp-panda.pro>
 * @copyright  2025 WP_Panda
 * @license    GNU General Public License v3.0
 * @version    1.9
 * @link       https://github.com/wp-panda/wpp-auth
 *
 * @see        https://developer.wordpress.org/plugins/
 * @see        https://developer.wordpress.org/reference/functions/wp_enqueue_script/
 * @see        https://developer.wordpress.org/reference/functions/wp_localize_script/
 * @see        https://developer.wordpress.org/reference/functions/wp_signon/
 * @see        https://developer.wordpress.org/reference/functions/wp_create_user/
 * @see        https://developer.wordpress.org/reference/functions/wp_generate_password/
 * @see        https://developer.wordpress.org/reference/functions/wp_mail/
 * @see        https://www.php.net/manual/en/function.sanitize-text-field.php
 * @see        https://www.php.net/manual/en/function.sanitize-email.php
 * @see        https://www.php.net/manual/en/function.sanitize-key.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access to the file
}

/**
 * Class Wpp_Auth
 *
 * Handles authentication flows (login, register, forgot password) through a modal window.
 * Supports dynamic form availability control, custom redirects, and clean URL rewriting.
 *
 * This class is designed to be instantiated once with configuration arguments.
 * It hooks into WordPress lifecycle actions such as:
 * - `init`: To register custom rewrite rules.
 * - `wp_enqueue_scripts`: To load CSS/JS assets.
 * - `wp_footer`: To inject the modal HTML into the page.
 * - AJAX actions: To handle form submissions without page reloads.
 *
 * Example usage:
 * ```
 * new Wpp_Auth([
 *     'login_slug'    => 'my-login',
 *     'redirect_to'   => home_url('/dashboard'),
 *     'allowed_forms' => ['login', 'register']
 * ]);
 * ```
 *
 * After initialization, you can trigger the modal using:
 * ```html
 * <a href="#" class="open-auth-modal">Login</a>
 * ```
 *
 * @since 1.0
 */
class Wpp_Auth {

	/**
	 * The slug used for the custom login page (e.g., /wpp-login).
	 *
	 * @var string
	 * @since 1.0
	 */
	private $login_slug;

	/**
	 * URL to redirect users after successful login.
	 *
	 * @var string
	 * @since 1.0
	 */
	private $redirect_to;

	/**
	 * List of allowed forms (e.g., 'login', 'register', 'forgot-password').
	 *
	 * Used to conditionally disable certain forms without removing code.
	 *
	 * @var string[]
	 * @since 1.5
	 */
	private $allowed_forms;

	/**
	 * Constructor: Initializes the plugin with user-defined settings.
	 *
	 * Sets up default values, sanitizes input, and registers all necessary hooks.
	 *
	 * @param array $args {
	 *     Optional. Array of configuration options.
	 *
	 *     @type string   $login_slug    Slug for login page. Default 'wpp-login'.
	 *     @type string   $redirect_to   Redirect URL after login. Default `admin_url()`.
	 *     @type string[] $allowed_forms List of enabled forms. Default all three.
	 * }
	 *
	 * @since 1.0
	 * @see   sanitize_title()     For slug sanitization.
	 * @see   esc_url_raw()        For URL escaping.
	 * @see   add_action()         For hook registration.
	 * @see   add_filter()         For modifying login URLs.
	 */
	public function __construct( $args = [] ) {
		// Define default configuration
		$defaults = [
			'login_slug'    => 'wpp-login',
			'redirect_to'   => admin_url(),
			'allowed_forms' => [ 'login', 'register', 'forgot-password' ], // All forms enabled by default
		];

		// Merge user args with defaults
		$args = wp_parse_args( $args, $defaults );

		// Sanitize and assign properties
		$this->login_slug    = sanitize_title( $args['login_slug'] );
		$this->redirect_to   = esc_url_raw( $args['redirect_to'] );
		$this->allowed_forms = array_map( 'sanitize_key', (array) $args['allowed_forms'] );


		// Register WordPress hooks
		add_action( 'init', [ $this, 'add_rewrite_rule' ] );
		// add_action( 'template_redirect', [ $this, 'handle_custom_login_page' ] ); // Temporarily disabled
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_ajax_wpp_auth_load_form', [ $this, 'ajax_load_form' ] );
		add_action( 'wp_ajax_nopriv_wpp_auth_load_form', [ $this, 'ajax_load_form' ] );
		add_action( 'wp_ajax_wpp_auth_login', [ $this, 'ajax_login' ] );
		add_action( 'wp_ajax_nopriv_wpp_auth_login', [ $this, 'ajax_login' ] );
		// add_action( 'wp_ajax_wpp_auth_register', [ $this, 'ajax_register' ] );
		// add_action( 'wp_ajax_nopriv_wpp_auth_register', [ $this, 'ajax_register' ] );
		// add_action( 'wp_ajax_wpp_auth_forgot_password', [ $this, 'ajax_forgot_password' ] );
		// add_action( 'wp_ajax_nopriv_wpp_auth_forgot_password', [ $this, 'ajax_forgot_password' ] );
		add_filter( 'login_url', [ $this, 'custom_login_url' ], 10, 2 );
		add_action( 'wp_footer', [ $this, 'render_modal_in_footer' ] );
	}

	/**
	 * Adds a custom rewrite rule for the login page.
	 *
	 * Maps `/wpp-login` to a query var `wpp_login=1`, allowing custom handling.
	 * Uses `top` position to ensure it takes precedence over other rules.
	 *
	 * @since 1.0
	 * @return void
	 * @see   add_rewrite_rule()    https://developer.wordpress.org/reference/functions/add_rewrite_rule/
	 * @see   add_rewrite_tag()     https://developer.wordpress.org/reference/functions/add_rewrite_tag/
	 * @global WP_Rewrite $wp_rewrite
	 */
	public function add_rewrite_rule() {
		add_rewrite_rule(
			"^{$this->login_slug}/?$",           // Match /wpp-login or /wpp-login/
			'index.php?wpp_login=1',             // Internally rewrite to index.php with query var
			'top'                                // Priority: high
		);

		// Register the query variable so WordPress recognizes it
		add_rewrite_tag( '%wpp_login%', '([^&]+)' );
	}

	/**
	 * Handles requests to the custom login page.
	 *
	 * If the `wpp_login` query var is set and user is not logged in,
	 * displays the modal login page and exits.
	 *
	 * @since 1.0
	 * @return void
	 * @global WP_Query $wp_query
	 */
	public function handle_custom_login_page() {
		global $wp_query;

		if ( get_query_var( 'wpp_login' ) && ! is_user_logged_in() ) {
			$this->show_login_modal();
			exit;
		}
	}

	/**
	 * Outputs the full modal login page (header, modal, footer).
	 *
	 * Used when visiting `/wpp-login`. Sends HTTP 200 header,
	 * loads theme header/footer, and renders the login form inside a hidden modal.
	 *
	 * @since 1.0
	 * @return void
	 * @see   get_header()     https://developer.wordpress.org/reference/functions/get_header/
	 * @see   get_footer()     https://developer.wordpress.org/reference/functions/get_footer/
	 */
	public function show_login_modal() {
		header( 'HTTP/1.1 200 OK' );
		get_header();

		echo '<div class="wpp-auth-modal" style="display:none;">';
		echo '<div class="wpp-auth-modal-content">';

		// Close button
		echo '<span class="close-modal">&times;</span>';

		// Load login form if allowed
		if ( $this->is_form_allowed( 'login' ) ) {
			$this->load_form( 'login' );
		} else {
			echo '<p>' . __( 'Login form is currently disabled.', 'wpp-auth' ) . '</p>';
		}

		echo '</div></div>';

		get_footer();
	}

	/**
	 * Renders the authentication modal in the site footer on every page.
	 *
	 * Ensures the modal is always available in the DOM so it can be opened
	 * instantly via JavaScript (e.g., by clicking `.open-auth-modal`).
	 *
	 * Only shown to non-logged-in users.
	 *
	 * @since 1.7
	 * @return void
	 * @hook  wp_footer
	 */
	public function render_modal_in_footer() {
		if ( is_user_logged_in() ) {
			return; // Do not show for authenticated users
		}
		?>
        <div class="wpp-auth-modal" style="display:none;">
            <div class="wpp-auth-modal-content">
                <span class="close-modal">&times;</span>
				<?php $this->load_form( 'login' ); ?>
            </div>
        </div>
		<?php
	}

	/**
	 * Loads a template file for a given form type.
	 *
	 * Includes the corresponding PHP file from the `templates/` directory.
	 * Only loads the form if it's allowed in `$this->allowed_forms`.
	 *
	 * @param string $form_type One of 'login', 'register', 'forgot-password'.
	 * @since 1.0
	 * @return void
	 * @see   plugin_dir_path() https://developer.wordpress.org/reference/functions/plugin_dir_path/
	 */
	private function load_form( $form_type ) {
		if ( ! $this->is_form_allowed( $form_type ) ) {
			return; // Form is disabled
		}

		$template_path = plugin_dir_path( __FILE__ ) . 'templates/';
		$file          = $template_path . $form_type . '.php';

		if ( file_exists( $file ) ) {
			include $file;
		} else {
			echo '<p>' . __( 'Form not found.', 'wpp-auth' ) . '</p>';
		}
	}

	/**
	 * Checks whether a specific form type is allowed.
	 *
	 * Used to conditionally enable/disable forms based on plugin configuration.
	 *
	 * @param string $form_type The form identifier.
	 * @return bool True if allowed, false otherwise.
	 * @since 1.5
	 * @see   sanitize_key() https://developer.wordpress.org/reference/functions/sanitize_key/
	 */
	private function is_form_allowed( $form_type ) {
		return in_array( sanitize_key( $form_type ), $this->allowed_forms, true );
	}

	/**
	 * Enqueues required CSS and JavaScript assets.
	 *
	 * Loads:
	 * - `wpp-auth.css`: Styles for the modal and forms.
	 * - `wpp-auth.js`: Handles modal open/close, AJAX submission.
	 *
	 * Also localizes script data (`wppAuthAjax`) for use in JavaScript.
	 *
	 * @since 1.0
	 * @return void
	 * @hook  wp_enqueue_scripts
	 * @see   wp_enqueue_style()     https://developer.wordpress.org/reference/functions/wp_enqueue_style/
	 * @see   wp_enqueue_script()    https://developer.wordpress.org/reference/functions/wp_enqueue_script/
	 * @see   wp_localize_script()   https://developer.wordpress.org/reference/functions/wp_localize_script/
	 * @see   plugins_url()          https://developer.wordpress.org/reference/functions/plugins_url/
	 */
	public function enqueue_scripts() {

		wp_enqueue_style(
			'wpp-auth-css',
			plugins_url( 'assets/css/wpp-auth.css', __FILE__ ),
			[],
			'1.9'
		);

		wp_enqueue_script(
			'wpp-auth-js',
			plugins_url( 'assets/js/wpp-auth.js', __FILE__ ),
			[ 'jquery' ],
			'1.9',
			true // In footer
		);

		// Make PHP variables available in JavaScript
		wp_localize_script(
			'wpp-auth-js',
			'wppAuthAjax',
			[
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'wpp-auth-nonce' ),
				'redirectUrl'  => $this->redirect_to,
				'allowedForms' => $this->allowed_forms, // Allow JS to hide disabled buttons
			]
		);
	}

	/**
	 * Overrides the default WordPress login URL.
	 *
	 * Changes `wp_login_url()` output to point to the custom slug (e.g., `/wpp-login`).
	 * Preserves any redirect parameter.
	 *
	 * @param string $login_url Original login URL.
	 * @param string $redirect  Optional redirect URL after login.
	 * @return string Modified login URL.
	 * @since 1.0
	 * @hook  login_url
	 * @see   add_query_arg() https://developer.wordpress.org/reference/functions/add_query_arg/
	 */
	public function custom_login_url( $login_url, $redirect ) {
		$login_url = home_url( '/' ) . $this->login_slug;
		if ( $redirect ) {
			$login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url );
		}
		return $login_url;
	}

	/**
	 * AJAX handler: Dynamically loads a form via AJAX.
	 *
	 * Used to switch between login, register, and forgot-password forms
	 * without reloading the page.
	 *
	 * @since 1.0
	 * @return void
	 * @hook  wp_ajax_wpp_auth_load_form
	 * @hook  wp_ajax_nopriv_wpp_auth_load_form
	 * @see   check_ajax_referer() https://developer.wordpress.org/reference/functions/check_ajax_referer/
	 * @see   wp_send_json_success() https://developer.wordpress.org/reference/functions/wp_send_json_success/
	 * @see   wp_send_json_error()   https://developer.wordpress.org/reference/functions/wp_send_json_error/
	 */
	public function ajax_load_form() {
		check_ajax_referer( 'wpp-auth-nonce', 'security' );

		$form_type = sanitize_text_field( $_POST['form_type'] );

		if ( ! $this->is_form_allowed( $form_type ) ) {
			wp_send_json_error( [ 'message' => __( 'This form is disabled.', 'wpp-auth' ) ] );
		}

		$allowed = [ 'login', 'register', 'forgot-password' ];
		if ( ! in_array( $form_type, $allowed, true ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid form type.', 'wpp-auth' ) ] );
		}

		ob_start();
		$this->load_form( $form_type );
		$html = ob_get_clean();

		wp_send_json_success( [ 'html' => $html ] );
	}

	/**
	 * AJAX handler: Processes user login.
	 *
	 * Uses `wp_signon()` to authenticate credentials.
	 * Returns JSON response with success/failure status and redirect URL.
	 *
	 * @since 1.0
	 * @return void
	 * @hook  wp_ajax_wpp_auth_login
	 * @hook  wp_ajax_nopriv_wpp_auth_login
	 * @see   wp_signon() https://developer.wordpress.org/reference/functions/wp_signon/
	 */
	public function ajax_login() {
		check_ajax_referer( 'wpp-auth-nonce', 'security' );

		if ( ! $this->is_form_allowed( 'login' ) ) {
			wp_send_json_error( [ 'message' => __( 'Login is temporarily disabled.', 'wpp-auth' ) ] );
		}

		$username = sanitize_text_field( $_POST['username'] );
		$password = sanitize_text_field( $_POST['password'] );

		$user = wp_signon( [ 'user_login' => $username, 'user_password' => $password ], false );

		if ( is_wp_error( $user ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid username or password.', 'wpp-auth' ) ] );
		} else {
			wp_send_json_success( [
				'message'  => __( 'You have successfully logged in.', 'wpp-auth' ),
				'redirect' => $this->redirect_to,
			] );
		}
	}

	/**
	 * AJAX handler: Processes user registration.
	 *
	 * Sanitizes input, validates required fields, and creates a new user.
	 * Returns JSON response with result.
	 *
	 * @since 1.0
	 * @return void
	 * @hook  wp_ajax_wpp_auth_register
	 * @hook  wp_ajax_nopriv_wpp_auth_register
	 * @see   wp_create_user() https://developer.wordpress.org/reference/functions/wp_create_user/
	 */
	public function ajax_register() {
		check_ajax_referer( 'wpp-auth-nonce', 'security' );

		if ( ! $this->is_form_allowed( 'register' ) ) {
			wp_send_json_error( [ 'message' => __( 'Registration is temporarily disabled.', 'wpp-auth' ) ] );
		}

		$username = sanitize_text_field( $_POST['username'] );
		$email    = sanitize_email( $_POST['email'] );
		$password = sanitize_text_field( $_POST['password'] );

		$errors = [];

		if ( empty( $username ) ) {
			$errors[] = __( 'Username is required.', 'wpp-auth' );
		}

		if ( empty( $email ) || ! is_email( $email ) ) {
			$errors[] = __( 'Please enter a valid email address.', 'wpp-auth' );
		}

		if ( empty( $password ) ) {
			$errors[] = __( 'Password is required.', 'wpp-auth' );
		}

		if ( ! empty( $errors ) ) {
			wp_send_json_error( [ 'errors' => $errors ] );
		}

		$user_id = wp_create_user( $username, $password, $email );

		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Registration error. User may already exist.', 'wpp-auth' ) ] );
		} else {
			wp_send_json_success( [
				'message'  => __( 'You have successfully registered!', 'wpp-auth' ),
				'redirect' => $this->redirect_to,
			] );
		}
	}

	/**
	 * AJAX handler: Handles password recovery.
	 *
	 * Finds user by email, generates a new password, sets it, and emails it.
	 * Redirects to login page after success.
	 *
	 * @since 1.0
	 * @return void
	 * @hook  wp_ajax_wpp_auth_forgot_password
	 * @hook  wp_ajax_nopriv_wpp_auth_forgot_password
	 * @see   get_user_by()         https://developer.wordpress.org/reference/functions/get_user_by/
	 * @see   wp_generate_password() https://developer.wordpress.org/reference/functions/wp_generate_password/
	 * @see   wp_set_password()     https://developer.wordpress.org/reference/functions/wp_set_password/
	 * @see   wp_mail()             https://developer.wordpress.org/reference/functions/wp_mail/
	 */
	public function ajax_forgot_password() {
		check_ajax_referer( 'wpp-auth-nonce', 'security' );

		if ( ! $this->is_form_allowed( 'forgot-password' ) ) {
			wp_send_json_error( [ 'message' => __( 'Password recovery is temporarily unavailable.', 'wpp-auth' ) ] );
		}

		$email = sanitize_email( $_POST['email'] );

		if ( empty( $email ) || ! is_email( $email ) ) {
			wp_send_json_error( [ 'message' => __( 'Please enter a valid email address.', 'wpp-auth' ) ] );
		}

		$user = get_user_by( 'email', $email );

		if ( ! $user ) {
			wp_send_json_error( [ 'message' => __( 'No user found with that email address.', 'wpp-auth' ) ] );
		}

		$new_pass = wp_generate_password();
		wp_set_password( $new_pass, $user->ID );

		$subject = __( 'Password Recovery', 'wpp-auth' );
		$message = sprintf( __( 'Your new password: %s', 'wpp-auth' ), $new_pass );
		wp_mail( $email, $subject, $message );

		wp_send_json_success( [
			'message'  => __( 'New password has been sent to your email.', 'wpp-auth' ),
			'redirect' => home_url( '/' . $this->login_slug ),
		] );
	}
}

/*
 * @todo Implement form switching animations.
 * @todo Add reCAPTCHA support for security.
 * @todo Add social login integration (Google, Facebook).
 * @todo Add email confirmation on registration.
 * @todo Add rate limiting for login attempts.
 * @todo Add password strength meter.
 * @todo Add "Remember Me" option.
 * @todo Allow modal size customization.
 * @todo Add internationalization (.pot file generation).
 * @todo Write PHPUnit tests.
 * @todo Add REST API endpoint support.
 * @todo Add dark mode toggle.
 */