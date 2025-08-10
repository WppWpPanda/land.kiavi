<?php
/**
 * Обработка и сохранение данных заявки на займ
 *
 * Этот файл содержит функции для:
 * - Обработки POST-запросов от форм заявок
 * - Безопасного сохранения данных в базу данных
 * - Получения сохранённых данных по loan_id
 * - Управления сессионными сообщениями (ошибки/успех)
 *
 * 🔗 Используется хук `template_redirect` для ранней обработки формы
 * 🔗 Поддерживает AJAX и обычные POST-запросы
 *
 * @since 1.0.0
 * @author WP_Panda <panda@wp-panda.pro>
 *
 * @global wpdb $wpdb Объект базы данных WordPress
 * @see https://developer.wordpress.org/reference/hooks/template_redirect/ Хук template_redirect
 * @see https://developer.wordpress.org/reference/functions/wp_verify_nonce/ Проверка nonce
 * @see https://developer.wordpress.org/reference/classes/wpdb/ Класс wpdb
 * @see https://developer.wordpress.org/reference/functions/maybe_serialize/ maybe_serialize()
 * @see https://developer.wordpress.org/reference/functions/sanitize_text_field/ sanitize_text_field()
 *
 * @todo Реализовать валидацию полей формы
 * @todo Добавить логирование изменений (audit log)
 * @todo Поддержка нескольких версий данных (ревизии)
 * @todo Заменить сессии на транзиенты для масштабируемости
 * @todo Добавить ограничение частоты запросов (rate limiting)
 */

defined( 'ABSPATH' ) || exit;

// Регистрируем обработчик формы
add_action( 'template_redirect', 'wpp_process_loan_form' );

/**
 * Обрабатывает POST-запрос с данными заявки на займ
 *
 * Выполняет:
 * 1. Проверку метода запроса (POST)
 * 2. Проверку наличия current_loan_id
 * 3. Проверку безопасности через nonce
 * 4. Очистку и фильтрацию данных
 * 5. Сохранение в базу данных с помощью $wpdb->replace
 *
 * @since 1.0.0
 * @return void
 *
 * @uses wpp_set_form_error() Для установки сообщений об ошибках
 * @uses wpp_set_form_notice() Для установки сообщений об успехе
 * @uses wp_verify_nonce() Для проверки безопасности
 * @uses $wpdb->replace() Для вставки/обновления данных
 *
 * @example
 * POST /form-endpoint
 * {
 *   "current_loan_id": "LN12345",
 *   "wpp_loan_nonce": "abc123...",
 *   "s1_field": "value"
 * }
 */
function wpp_process_loan_form() {
	// Проверяем, что это POST-запрос и есть идентификатор займа
	if ( $_SERVER['REQUEST_METHOD'] !== 'POST' || ! isset( $_POST['current_loan_id'] ) ) {
		return;
	}

	// Проверяем nonce для защиты от CSRF-атак
	// Несанкционированные запросы будут отклонены
	if ( ! isset( $_POST['wpp_loan_nonce'] ) || ! wp_verify_nonce( $_POST['wpp_loan_nonce'], 'wpp_save_loan_data' ) ) {
		wpp_set_form_error( 'Ошибка безопасности. Пожалуйста, попробуйте еще раз.' );
		return;
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'wpp_loans_full_data';

	// Получаем и очищаем ID займа
	$loan_id = sanitize_text_field( $_POST['current_loan_id'] );

	if ( empty( $loan_id ) ) {
		wpp_set_form_error( 'ID займа не может быть пустым' );
		return;
	}

	// Список полей, которые нужно исключить из сохранения
	$exclude_fields = [
		'current_loan_id',
		'wpp_loan_nonce',
		'_wp_http_referer',
		'_wpnonce'
	];

	$clean_data = [];

	//error_log( 'Это то, что происходит при сохранении');
	//error_log( print_r($_POST, true) );

	// Обрабатываем все остальные поля формы
	foreach ( $_POST as $key => $value ) {
		if ( ! in_array( $key, $exclude_fields, true ) ) {
			// Сохраняем данные "как есть" (предполагается, что очистка будет в другом месте)
			$clean_data[ $key ] = $value;
		}
	}

	// Сохраняем данные в базу данных
	// $wpdb->replace() вставит новую запись или заменит существующую
	$result = $wpdb->replace(
		$table_name,
		[
			'loan_id'   => $loan_id,
			'loan_data' => maybe_serialize( $clean_data )
		],
		[ '%s', '%s' ] // Формат значений для экранирования
	);

	if ( $result === false ) {
		// Ошибка при работе с базой данных
		wpp_set_form_error( 'Ошибка при сохранении данных' );
	} else {
		// Успешное сохранение
		wpp_set_form_notice( 'Данные успешно сохранены' );
	}
}

/**
 * Устанавливает сообщение об ошибке в сессию
 *
 * Сообщение будет отображено при следующей загрузке страницы
 *
 * @since 1.0.0
 * @param string $message Текст сообщения об ошибке
 * @return void
 *
 * @uses session_start() Если сессия еще не запущена
 * @see wpp_get_form_messages() Для получения и отображения сообщений
 *
 * @example
 * wpp_set_form_error('Поле "Имя" обязательно для заполнения');
 */
function wpp_set_form_error( $message ) {
	if ( ! session_id() ) {
		session_start();
	}
	$_SESSION['wpp_form_errors'][] = $message;
}

/**
 * Устанавливает уведомление об успешном действии в сессию
 *
 * Сообщение будет отображено при следующей загрузке страницы
 *
 * @since 1.0.0
 * @param string $message Текст уведомления
 * @return void
 *
 * @uses session_start() Если сессия еще не запущена
 * @see wpp_get_form_messages() Для получения и отображения сообщений
 *
 * @example
 * wpp_set_form_notice('Заявка успешно сохранена');
 */
function wpp_set_form_notice( $message ) {
	if ( ! session_id() ) {
		session_start();
	}
	$_SESSION['wpp_form_notices'][] = $message;
}

/**
 * Получает и возвращает HTML-код сообщений формы
 *
 * Выводит сообщения об ошибках и успехе из сессии
 * Автоматически очищает сообщения после вывода
 *
 * @since 1.0.0
 * @return string HTML-код сообщений или пустая строка
 *
 * @uses session_start() Если сессия еще не запущена
 * @uses esc_html() Для экранирования вывода
 *
 * @example
 * echo wpp_get_form_messages(); // <div class="notice notice-error">...</div>
 */
function wpp_get_form_messages() {
	if ( ! session_id() ) {
		session_start();
	}

	$output = '';

	// Выводим все сообщения об ошибках
	if ( ! empty( $_SESSION['wpp_form_errors'] ) ) {
		foreach ( $_SESSION['wpp_form_errors'] as $error ) {
			$output .= '<div class="notice notice-error"><p>' . esc_html( $error ) . '</p></div>';
		}
		unset( $_SESSION['wpp_form_errors'] );
	}

	// Выводим все уведомления об успехе
	if ( ! empty( $_SESSION['wpp_form_notices'] ) ) {
		foreach ( $_SESSION['wpp_form_notices'] as $notice ) {
			$output .= '<div class="notice notice-success"><p>' . esc_html( $notice ) . '</p></div>';
		}
		unset( $_SESSION['wpp_form_notices'] );
	}

	return $output;
}

/**
 * Получает данные займа и возвращает их как JSON (для AJAX)
 *
 * @since 1.0.0
 * @param string|null $loan_id Идентификатор займа
 * @return void|false Возвращает JSON или false
 *
 * @uses wp_doing_ajax() Проверяет, выполняется ли AJAX-запрос
 * @uses wp_send_json_success() Отправляет успешный JSON-ответ
 * @uses wp_send_json_error() Отправляет JSON-ответ с ошибкой
 * @uses maybe_unserialize() Десериализует данные
 *
 * @example
 * AJAX: /wp-admin/admin-ajax.php?action=wpp_get_loan_data&loan_id=LN12345
 * Response: { "success": true, "data": { "s1_field": "value" } }
 */
function wpp_get_loan_data( $loan_id = null ) {
	if ( empty( $loan_id ) ) {
		global $loan_id;

		if ( ! isset( $loan_id ) ) {
			if ( wp_doing_ajax() ) {
				wp_send_json_error( [ 'message' => 'Loan ID is required' ] );
			} else {
				return false;
			}
		}
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'wpp_loans_full_data';

	// Получаем данные из базы данных
	$data = $wpdb->get_row(
		$wpdb->prepare( "SELECT loan_data FROM $table_name WHERE loan_id = %s", $loan_id ),
		ARRAY_A
	);

	if ( ! $data ) {
		if ( wp_doing_ajax() ) {
			wp_send_json_error( [ 'message' => 'Data not found' ] );
		} else {
			return false;
		}
	}

	$loan_data = maybe_unserialize( $data['loan_data'] );
	wp_send_json_success( $loan_data );
}

/**
 * Получает данные займа и возвращает их как массив PHP
 *
 * Отличается от wpp_get_loan_data() тем, что:
 * - Не отправляет JSON
 * - Возвращает данные напрямую
 * - Подходит для использования в PHP-коде
 *
 * @since 1.0.0
 * @param string|null $loan_ID Идентификатор займа
 * @return array|false Массив с данными или false при ошибке
 *
 * @uses maybe_unserialize() Десериализует данные
 *
 * @example
 * $data = wpp_get_loan_data_r('LN12345');
 * if ($data) {
 *     echo $data['s1_field'];
 * }
 */
function wpp_get_loan_data_r( $loan_ID = null ) {
	if ( empty( $loan_ID ) ) {
		global $loan_id;

		if ( ! isset( $loan_id ) ) {
			if ( wp_doing_ajax() ) {
				wp_send_json_error( [ 'message' => 'Loan ID is required' ] );
			} else {
				return false;
			}
		}

		$loan_ID = $loan_id;
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'wpp_loans_full_data';

	$data = $wpdb->get_row(
		$wpdb->prepare( "SELECT loan_data FROM $table_name WHERE loan_id = %s", $loan_ID ),
		ARRAY_A
	);

	if ( ! $data ) {
		if ( wp_doing_ajax() ) {
			wp_send_json_error( [ 'message' => 'Data not found 2' ] );
		} else {
			return false;
		}
	}

	$loan_data = maybe_unserialize( $data['loan_data'] );

	return $loan_data;
}