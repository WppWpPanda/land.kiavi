<?php
/**
 * WPP Loan Application - Helpers
 *
 * Универсальные вспомогательные функции для многошаговой формы заявки на займ.
 * Обеспечивает:
 * - Рендеринг динамических форм через классы полей
 * - Работу с сессионными данными
 * - Отладку в режиме разработки
 * - Форматирование и сохранение данных
 *
 *  Подключается через: `require_once WPP_LOAN_PATH . 'includes/functions/helpers.php';`
 *
 * @package WPP_Loan_Application
 * @subpackage Helpers
 * @since 1.0.0
 * @author WP_Panda <panda@wp-panda.pro>
 * @license GPL-2.0-or-later
 *
 * @global WP_Post $post WordPress post object
 * @global wpdb $wpdb WordPress database abstraction object
 *
 * @link https://developer.wordpress.org/reference/functions/has_shortcode/ has_shortcode()
 * @link https://developer.wordpress.org/reference/functions/sanitize_text_field/ sanitize_text_field()
 * @link https://developer.wordpress.org/reference/functions/json_encode/ json_encode()
 * @link https://developer.wordpress.org/reference/functions/current_time/ current_time()
 * @link https://developer.wordpress.org/reference/functions/wp_safe_redirect/ wp_safe_redirect()
 * @link https://developer.wordpress.org/reference/functions/esc_attr/ esc_attr()
 *
 * @todo Добавить поддержку мультиформ (нескольких параллельных сессий)
 * @todo Реализовать валидацию данных перед сохранением
 * @todo Добавить хуки (actions/filters) для расширения
 * @todo Поддержка импорта/экспорта данных
 * @todo Добавить логирование ошибок в файл
 * @todo Реализовать резервное копирование сессий
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Рендерит форму на основе массива конфигурации полей.
 *
 * Динамически создаёт экземпляры классов полей (например, `WPP_Text_Field`, `WPP_Select_Field`)
 * и вызывает их метод `render()`. Поддерживает кастомные CSS-классы и атрибуты.
 *
 * @since 1.0.0
 * @param string $form_id Уникальный ID формы (например, 'loan-form-step-1')
 * @param array $form_fields Массив конфигурации полей в формате [ 'field_name' => [ 'type' => 'text', ... ] ]
 * @param string $method HTTP-метод формы (по умолчанию 'post')
 * @param array $attributes Дополнительные HTML-атрибуты формы (например, 'class', 'data-*')
 * @return void Выводит HTML напрямую
 *
 * @uses normalizeClassName() Преобразует тип поля в имя класса
 * @uses class_exists() Проверяет, определён ли класс поля
 * @uses esc_attr() Экранирует атрибуты для безопасности
 *
 * @example
 * wpp_render_form(
 *     'loan-form-step-1',
 *     [
 *         'name' => [ 'type' => 'text', 'label' => 'Full Name' ],
 *         'email' => [ 'type' => 'email', 'label' => 'Email' ]
 *     ],
 *     'post',
 *     [ 'class' => 'custom-form', 'data-step' => '1' ]
 * );
 */
function wpp_render_form( string $form_id, array $form_fields, string $method = 'post', array $attributes = [ 'classes' => 'wpp-custom-form container-fluid row' ] ) {
	$classes = [ 'wpp-custom-form', 'row' ];
	$attrs   = '';

	// Добавляем кастомные CSS-классы
	if ( ! empty( $attributes['class'] ) ) {
		$classes = array_merge( $classes, explode( ' ', $attributes['class'] ) );
		unset( $attributes['class'] );
	}

	// Выводим открывающий тег формы
	echo '<form id="' . esc_attr( $form_id ) . '" method="' . esc_attr( $method ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '"';

	// Добавляем дополнительные атрибуты
	foreach ( $attributes as $attr => $value ) {
		echo ' ' . esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
	}

	echo '>';

	// Рендерим каждое поле
	foreach ( $form_fields as $name => $config ) {
		$class_name = 'WPP_' . normalizeClassName( $config['type'] ) . '_Field';

		if ( class_exists( $class_name ) ) {
			$field = new $class_name( array_merge( $config, [ 'name' => $name ] ) );
			$field->render();
		}
	}

	echo '</form>';
}

/**
 * Отображает отладочные данные формы в режиме разработки.
 *
 * Выводит отформатированные данные указанного шага в виде JSON.
 * Активируется только при определённой константе `WPP_LOAN_DEV_MODE`.
 *
 * @since 1.0.0
 * @param int $step Номер шага формы
 * @return void Выводит HTML `<pre>` блок
 *
 * @uses WPP_Loan_Session_Handler::get_step_data() Получает данные шага
 * @uses json_encode() Сериализует данные с форматированием
 * @uses htmlspecialchars() Экранирует вывод
 *
 * @example
 * wpp_loan_form_debug_data(2); // Покажет данные шага 2
 */
function wpp_loan_form_debug_data(int $step): void {
	if (!defined('WPP_LOAN_DEV_MODE') || !WPP_LOAN_DEV_MODE) {
		return;
	}

	$step_data = WPP_Loan_Session_Handler::get_step_data($step);

	printf(
		'<pre style="%s"><strong>%s</strong>%s</pre>',
		'background:#f9f9f9;padding:1rem;border:1px solid #ccc;border-radius:4px;',
		sprintf('Data from Step %d:', $step),
		htmlspecialchars(json_encode($step_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
	);
}

/**
 * Преобразует вложенные данные сессии в одномерный массив.
 *
 * Конвертирует структуру:
 * [
 *   1 => [ 'name' => 'John', 'email' => 'john@example.com' ],
 *   2 => [ 'address' => '123 Main St' ]
 * ]
 * в:
 * [
 *   's1_name' => 'John',
 *   's1_email' => 'john@example.com',
 *   's2_address' => '123 Main St'
 * ]
 *
 * @since 1.0.0
 * @param array $all_data Вложенные данные всех шагов
 * @return array Одномерный массив с префиксами `s{step}_`
 *
 * @uses maybe_serialize() Сериализует массивы и объекты
 *
 * @example
 * $flat = wpp_flatten_session_data($session_data);
 * print_r($flat);
 */
function wpp_flatten_session_data($all_data) {
	$flattened = [];

	foreach ($all_data as $step => $step_data) {
		if (!isset($step_data) || !is_array($step_data)) {
			continue;
		}

		foreach ($step_data as $key => $value) {
			if ($key === 'step_identifier') {
				continue;
			}

			$new_key = 's' . $step . '_' . $key;

			if (!is_array($value)) {
				$flattened[$new_key] = $value;
			} else {
				$flattened[$new_key] = maybe_serialize($value);
			}
		}
	}

	return $flattened;
}

/**
 * Сохраняет всю сессию в таблицу `loan_raw_applications`
 * и очищает PHP-сессию.
 *
 * Используется при завершении заявки.
 *
 * @since 1.0.0
 * @param int $user_id ID пользователя WordPress (0 для анонимных)
 * @return void
 *
 * @uses WPP_Loan_Session_Handler::get_all_data_from_session() Получает все данные
 * @uses WPP_Loan_Session_Handler::get_session_id() Получает ID сессии
 * @uses $wpdb->insert() / $wpdb->update() Сохраняет в БД
 * @uses wpp_link_guarantor_to_loans() Связывает поручителя с заявкой
 *
 * @example
 * wpp_save_session_to_database_raw(get_current_user_id());
 */
function wpp_save_session_to_database_raw($user_id = 0) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'loan_raw_applications';
	$all_data = WPP_Loan_Session_Handler::get_all_data_from_session();
	$session_id = WPP_Loan_Session_Handler::get_session_id();

	// Преобразуем данные в одномерный массив
	$flat_data = wpp_flatten_session_data($all_data);

	// Подготавливаем данные для записи
	$data_to_save = [
		'session_id' => sanitize_text_field($session_id),
		'raw_data'   => json_encode($flat_data),
		'updated_at' => current_time('mysql'),
	];

	if ($user_id) {
		$data_to_save['user_id'] = intval($user_id);
	}

	// Проверяем, есть ли уже такая сессия
	$existing = $wpdb->get_row(
		$wpdb->prepare("SELECT id FROM $table_name WHERE session_id = %s", $session_id),
		ARRAY_A
	);

	if ($existing) {
		// Обновляем существующую запись
		$wpdb->update(
			$table_name,
			$data_to_save,
			['session_id' => $session_id]
		);
		wpp_link_guarantor_to_loans( WPP_Loan_Session_Handler::get_step_data('guarantor')['id'], $existing['id'] );
	} else {
		// Вставляем новую запись
		$data_to_save['created_at'] = current_time('mysql');
		$wpdb->insert($table_name, $data_to_save);
		wpp_link_guarantor_to_loans( WPP_Loan_Session_Handler::get_step_data('guarantor')['id'], $wpdb->insert_id );
	}
}

/**
 * Проверяет наличие шорткода формы на текущей странице.
 *
 * Используется для:
 * - Перенаправления с пустых шагов
 * - Определения текущего шага
 * - Интеграции с редактором Gutenberg/классическим
 *
 * @since 1.0.0
 * @return bool Всегда возвращает false (результат обработки)
 *
 * @global WP_Post $post Текущий объект поста
 * @uses WPP_Loan_Session_Handler::get_step_data() Проверяет, есть ли данные
 * @uses has_shortcode() Проверяет наличие шорткода в контенте
 * @uses wp_safe_redirect() Перенаправляет на главную при отсутствии данных
 * @uses error_log() Логирует ошибки конфигурации
 *
 * @example
 * // Выполняется автоматически на хуке 'wp'
 * add_action('wp', 'wpp_has_shortcode_on_page');
 */
function wpp_has_shortcode_on_page() {
	global $post;

	// 1. Получаем JSON-конфигурацию шагов
	$steps_json = defined('WPP_LOAN_STEPS') ? WPP_LOAN_STEPS : null;

	if (!$steps_json) {
		error_log('WPP_LOAN_STEPS is not defined.');
		return false;
	}

	// 2. Декодируем JSON
	$steps = json_decode($steps_json, true);

	if (!is_array($steps)) {
		error_log('WPP_LOAN_STEPS is not a valid JSON string or could not be decoded.');
		return false;
	}

	// 3. Проверяем каждый шаг
	foreach ( $steps as $key => $one ) {
		$shortcode_name = trim($one['short'], '[]');

		if ( is_singular() && has_shortcode( $post->post_content, $shortcode_name ) ) {
			$step_data = WPP_Loan_Session_Handler::get_step_data($key);

			/*if(empty($step_data)) {
				wp_safe_redirect(get_home_url());
				exit;
			}*/
		}
	}

	return false;
}

// Запускаем проверку на раннем этапе загрузки страницы
add_action('wp', 'wpp_has_shortcode_on_page');