<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Защита от прямого доступа
}

/**
 * Класс Wpp_Auth — простая авторизация, регистрация и восстановление пароля в модальном окне
 */
class Wpp_Auth {
	private $login_slug;
	private $redirect_to;

	/**
	 * Конструктор с настройками
	 *
	 * @param array $args
	 */
	public function __construct( $args = [] ) {
		$defaults = [
			'login_slug'  => 'wpp-login',           // URL страницы входа
			'redirect_to' => admin_url(),           // Куда редиректить после входа
		];

		$args              = wp_parse_args( $args, $defaults );
		$this->login_slug  = sanitize_title( $args['login_slug'] );
		$this->redirect_to = esc_url_raw( $args['redirect_to'] );

		// Добавляем хуки
		add_action( 'init', [ $this, 'add_rewrite_rule' ] );
		add_action( 'template_redirect', [ $this, 'handle_custom_login_page' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_ajax_wpp_auth_load_form', [ $this, 'ajax_load_form' ] );
		add_action( 'wp_ajax_nopriv_wpp_auth_load_form', [ $this, 'ajax_load_form' ] );
		add_action( 'wp_ajax_wpp_auth_login', [ $this, 'ajax_login' ] );
		add_action( 'wp_ajax_nopriv_wpp_auth_login', [ $this, 'ajax_login' ] );
		add_action( 'wp_ajax_wpp_auth_register', [ $this, 'ajax_register' ] );
		add_action( 'wp_ajax_nopriv_wpp_auth_register', [ $this, 'ajax_register' ] );
		add_action( 'wp_ajax_wpp_auth_forgot_password', [ $this, 'ajax_forgot_password' ] );
		add_action( 'wp_ajax_nopriv_wpp_auth_forgot_password', [ $this, 'ajax_forgot_password' ] );
		add_filter( 'login_url', [ $this, 'custom_login_url' ], 10, 2 );
	}

	/**
	 * Добавляем правило ЧПУ: /wpp-login → кастомная страница входа
	 */
	public function add_rewrite_rule() {
		add_rewrite_rule(
			"^{$this->login_slug}/?$",
			'index.php?wpp_login=1',
			'top'
		);
		add_rewrite_tag( '%wpp_login%', '([^&]+)' );
	}

	/**
	 * Обработка запроса к /wpp-login
	 */
	public function handle_custom_login_page() {
		global $wp_query;

		if ( get_query_var( 'wpp_login' ) && ! is_user_logged_in() ) {
			$this->show_login_modal();
			exit;
		}
	}

	/**
	 * Показываем модальное окно с формой входа
	 */
	public function show_login_modal() {
		header( 'HTTP/1.1 200 OK' );
		get_header();

		echo '<div class="wpp-auth-modal" style="display:none;">';
		echo '<div class="wpp-auth-modal-content">';

		// Крестик закрытия
		echo '<span class="close-modal">&times;</span>';

		// Подгружаем форму входа по умолчанию
		$this->load_form( 'login' );

		echo '</div></div>';

		get_footer();
	}

	/**
	 * Загрузка формы из шаблона
	 *
	 * @param string $form_type
	 */
	private function load_form( $form_type ) {
		$template_path = plugin_dir_path( __FILE__ ) . 'templates/';
		$file          = $template_path . $form_type . '.php';

		if ( file_exists( $file ) ) {
			include $file;
		} else {
			echo '<p>' . __( 'Форма не найдена.', 'wpp-auth' ) . '</p>';
		}
	}

	/**
	 * Подключаем стили и скрипты
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'wpp-auth-css', plugins_url( 'assets/css/wpp-auth.css', __FILE__ ), [], '1.7' );
		wp_enqueue_script( 'wpp-auth-js', plugins_url( 'assets/js/wpp-auth.js', __FILE__ ), [ 'jquery' ], '1.7', true );

		wp_localize_script( 'wpp-auth-js', 'wppAuthAjax', [
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'wpp-auth-nonce' ),
			'redirectUrl' => $this->redirect_to,
		] );
	}

	/**
	 * Переопределяем стандартный URL входа
	 */
	public function custom_login_url( $login_url, $redirect ) {
		$login_url = home_url( '/' ) . $this->login_slug;
		if ( $redirect ) {
			$login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url );
		}

		return $login_url;
	}

	/**
	 * AJAX: Подгрузка формы (login, register, forgot-password)
	 */
	public function ajax_load_form() {
		check_ajax_referer( 'wpp-auth-nonce', 'security' );

		$form_type = sanitize_text_field( $_POST['form_type'] );
		$allowed   = [ 'login', 'register', 'forgot-password' ];

		if ( ! in_array( $form_type, $allowed ) ) {
			wp_send_json_error( [ 'message' => __( 'Недопустимая форма.', 'wpp-auth' ) ] );
		}

		ob_start();
		$this->load_form( $form_type );
		$html = ob_get_clean();

		wp_send_json_success( [ 'html' => $html ] );
	}

	/**
	 * AJAX: Вход пользователя
	 */
	public function ajax_login() {
		check_ajax_referer( 'wpp-auth-nonce', 'security' );

		$username = sanitize_text_field( $_POST['username'] );
		$password = sanitize_text_field( $_POST['password'] );

		$user = wp_signon( [ 'user_login' => $username, 'user_password' => $password ], false );

		if ( is_wp_error( $user ) ) {
			wp_send_json_error( [ 'message' => __( 'Неверное имя пользователя или пароль.', 'wpp-auth' ) ] );
		} else {
			wp_send_json_success( [
				'message'  => __( 'Вы успешно вошли.', 'wpp-auth' ),
				'redirect' => $this->redirect_to
			] );
		}
	}

	/**
	 * AJAX: Регистрация пользователя
	 */
	public function ajax_register() {
		check_ajax_referer( 'wpp-auth-nonce', 'security' );

		$username = sanitize_text_field( $_POST['username'] );
		$email    = sanitize_email( $_POST['email'] );
		$password = sanitize_text_field( $_POST['password'] );

		$errors = [];

		if ( empty( $username ) ) {
			$errors[] = __( 'Имя пользователя обязательно.', 'wpp-auth' );
		}

		if ( empty( $email ) || ! is_email( $email ) ) {
			$errors[] = __( 'Введите корректный email.', 'wpp-auth' );
		}

		if ( empty( $password ) ) {
			$errors[] = __( 'Пароль обязателен.', 'wpp-auth' );
		}

		if ( ! empty( $errors ) ) {
			wp_send_json_error( [ 'errors' => $errors ] );
		}

		$user_id = wp_create_user( $username, $password, $email );

		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Ошибка регистрации. Возможно, пользователь уже существует.', 'wpp-auth' ) ] );
		} else {
			wp_send_json_success( [
				'message'  => __( 'Вы успешно зарегистрированы!', 'wpp-auth' ),
				'redirect' => $this->redirect_to
			] );
		}
	}

	/**
	 * AJAX: Восстановление пароля
	 */
	public function ajax_forgot_password() {
		check_ajax_referer( 'wpp-auth-nonce', 'security' );

		$email = sanitize_email( $_POST['email'] );

		if ( empty( $email ) || ! is_email( $email ) ) {
			wp_send_json_error( [ 'message' => __( 'Введите корректный email.', 'wpp-auth' ) ] );
		}

		$user = get_user_by( 'email', $email );

		if ( ! $user ) {
			wp_send_json_error( [ 'message' => __( 'Пользователь с таким email не найден.', 'wpp-auth' ) ] );
		}

		$new_pass = wp_generate_password();
		wp_set_password( $new_pass, $user->ID );

		$subject = __( 'Восстановление пароля', 'wpp-auth' );
		$message = sprintf( __( 'Ваш новый пароль: %s', 'wpp-auth' ), $new_pass );
		wp_mail( $email, $subject, $message );

		wp_send_json_success( [
			'message'  => __( 'Новый пароль отправлен на ваш email.', 'wpp-auth' ),
			'redirect' => home_url( '/' . $this->login_slug )
		] );
	}
}