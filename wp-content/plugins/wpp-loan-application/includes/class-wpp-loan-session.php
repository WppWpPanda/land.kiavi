<?php
/**
 * Class WPP_Loan_Session_Handler
 *
 * Управляет данными между шагами формы.
 * Все данные хранятся в одной строке базы данных, связанной с session_id.
 *
 * @package WPP_Loan_Application
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WPP_Loan_Session_Handler {

	/**
	 * Ключ для хранения данных в $_SESSION
	 * @var string
	 */
	private static $session_key = 'wpp_loan_data';

	/**
	 * Имя куки с session_id
	 * @var string
	 */
	private const SESSION_COOKIE_NAME = 'wpp_loan_session';

	/**
	 * Получает или создаёт уникальный session_id через куки
	 *
	 * @return string
	 */
	public static function get_session_id() {
		if ( ! empty( $_COOKIE[ self::SESSION_COOKIE_NAME ] ) ) {
			return sanitize_text_field( $_COOKIE[ self::SESSION_COOKIE_NAME ] );
		}

		$new_session_id = uniqid( 'loan_sess_', true );

		setcookie(
			self::SESSION_COOKIE_NAME,
			$new_session_id,
			time() + 604800,
			COOKIEPATH,
			COOKIE_DOMAIN
		);

		return $new_session_id;
	}

	/**
	 * Запускает сессию, если она ещё не запущена
	 */
	public static function start_session() {
		if ( session_id() ) {
			return;
		}

		if ( headers_sent( $file, $line ) ) {
			error_log( "⚠️ Headers already sent by {$file}:{$line}" );

			return;
		}

		session_start();
	}

	/**
	 * Сохраняет данные текущего шага в сессию и в БД (в одной строке)
	 *
	 * @param int|string $step Номер шага
	 * @param array $data Данные формы
	 * @param int $user_id ID пользователя (опционально)
	 *
	 * @return bool|int Возвращает ID записи или false при ошибке
	 */


	public static function save_step_data( $step, $data ) {
		self::start_session();

		// Сохраняем в PHP-сессию
		if ( ! isset( $_SESSION[ self::$session_key ] ) ) {
			$_SESSION[ self::$session_key ] = [];
		}

		$_SESSION[ self::$session_key ][ $step ] = $data;

		// Читаем существующие данные из сессии
		global $wpdb;
		$table_name = $wpdb->prefix . 'loan_application_data';
		$session_id = self::get_session_id();

		// Получаем существующую запись
		$existing = $wpdb->get_row(
			$wpdb->prepare( "SELECT form_data FROM $table_name WHERE session_id = %s", $session_id ),
			ARRAY_A
		);

		// Парсим старые данные, если они есть
		$all_data = [];

		if ( $existing && ! empty( $existing['form_data'] ) ) {
			$all_data = json_decode( $existing['form_data'], true );
		}

		// Обновляем данные по текущему шагу
		$all_data[ $step ] = $data;

		$args = [
			'session_id' => $session_id,
			'form_data'  => json_encode( $all_data ),
			'updated_at' => current_time( 'mysql' ),
		];


		$args['user_id'] = 0;


		if ( $existing ) {
			// Обновляем существующую запись
			$result = $wpdb->update(
				$table_name,
				$args,
				[ 'session_id' => $session_id ]
			);
		} else {
			// Вставляем новую запись
			$args['created_at'] = current_time( 'mysql' );
			$result             = $wpdb->insert( $table_name, $args );
		}


		return ! empty( $result ) ? $result : false;
	}

	/**
	 * Получает данные определённого шага
	 *
	 * @param int|string $step Номер шага
	 *
	 * @return array|false
	 */
	public static function get_step_data( $step ) {
		self::start_session();

		// Сначала ищем в PHP-сессии
		if ( isset( $_SESSION[ self::$session_key ][ $step ] ) ) {
			return $_SESSION[ self::$session_key ][ $step ];
		}

		// Если нет — читаем из БД
		global $wpdb;
		$table_name = $wpdb->prefix . 'loan_application_data';
		$session_id = self::get_session_id();

		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT form_data FROM $table_name WHERE session_id = %s", $session_id ),
			ARRAY_A
		);


		if ( ! $row || empty( $row['form_data'] ) ) {
			return false;
		}

		$all_data = json_decode( $row['form_data'], true );

		return isset( $all_data[ $step ] ) ? $all_data[ $step ] : false;
	}

	/**
	 * Получает все данные из текущей сессии (из $_SESSION или из БД)
	 *
	 * @return array|false Массив всех данных или false, если нет данных
	 */
	public static function get_all_data_from_session() {
		self::start_session();

		// Сначала пытаемся получить из PHP-сессии
		if (!empty($_SESSION[self::$session_key])) {
			return $_SESSION[self::$session_key];
		}

		// Если в сессии ничего нет — читаем из БД
		global $wpdb;
		$table_name = $wpdb->prefix . 'loan_application_data';
		$session_id = self::get_session_id();

		$row = $wpdb->get_row(
			$wpdb->prepare("SELECT form_data FROM $table_name WHERE session_id = %s", $session_id),
			ARRAY_A
		);

		if (!$row || empty($row['form_data'])) {
			return false;
		}

		$all_data = json_decode($row['form_data'], true);

		if (!is_array($all_data)) {
			return false;
		}

		// Сохраняем в сессию для последующего доступа без запросов к БД

		return $all_data;
	}

	/**
	 * Получает значение поля из указанного шага
	 *
	 * @param int|string $step Номер шага
	 * @param string $field_name Имя поля
	 *
	 * @return mixed|null
	 */
	public static function get_field_value( $step, $field_name ) {
		$step_data = self::get_step_data( $step );

		return $step_data && isset( $step_data['formData'][ $field_name ] )
			? $step_data['formData'][ $field_name ]
			: null;
	}

	/**
	 * Получает все данные из сессии (все шаги)
	 *
	 * @return array|false
	 */
	public static function get_all_data() {
		self::start_session();

		// Сначала пробуем из PHP-сессии
		if ( ! empty( $_SESSION[ self::$session_key ] ) ) {
			return $_SESSION[ self::$session_key ];
		}

		// Если нет — читаем из БД
		global $wpdb;
		$table_name = $wpdb->prefix . 'loan_application_data';
		$session_id = self::get_session_id();

		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT form_data FROM $table_name WHERE session_id = %s", $session_id ),
			ARRAY_A
		);

		if ( ! $row || empty( $row['form_data'] ) ) {
			return false;
		}

		$all_data = json_decode( $row['form_data'], true );

		// Также сохраняем в PHP-сессию, чтобы не читать лишний раз из БД
		$_SESSION[ self::$session_key ] = $all_data;

		return $all_data;
	}

	/**
	 * Очищает всю сессию: удаляет из БД и сбрасывает куку
	 */
	public static function clear_all() {
		//self::start_session();
		unset( $_SESSION[ self::$session_key ] );


		global $wpdb;
		$table_name = $wpdb->prefix . 'loan_application_data';
		$session_id = self::get_session_id();

		$wpdb->delete( $table_name, [ 'session_id' => $session_id ] );

		// Очищаем cookie
		if ( isset( $_COOKIE[ self::SESSION_COOKIE_NAME ] ) ) {
			setcookie( self::SESSION_COOKIE_NAME, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
			unset( $_COOKIE[ self::SESSION_COOKIE_NAME ] );
		}
	}

	/**
	 * Проверяет, есть ли данные по шагу
	 *
	 * @param int|string $step Номер шага
	 *
	 * @return bool
	 */
	public static function has_step_data( $step ) {
		self::start_session();

		if ( isset( $_SESSION[ self::$session_key ][ $step ] ) ) {
			return true;
		}

		$all_data = self::get_all_data();

		return is_array( $all_data ) && isset( $all_data[ $step ] );
	}
}