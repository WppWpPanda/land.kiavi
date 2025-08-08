<?php
/**
 * WPP Loan Application - Helpers
 *
 * Универсальные вспомогательные функции для всех шагов.
 * Включает:
 * - Рендеринг формы через $form_fields
 * - Расчёт LTC (Loan to Cost)
 * - Расчёт ARV LTV
 * - Форматирование чисел и денег
 * - Получение данных из предыдущих шагов
 *
 * @package WPP_Loan_Application
 * @subpackage Helpers
 * @since 1.0.0
 * @author WP Panda <panda@wp-panda.pro>
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


// ----------------------
// 🔧 Форма и рендеринг
// ----------------------

/**
 * Рендерит форму по массиву $form_fields
 *
 * @param string $form_id ID формы (например 'loan-form-step-4')
 * @param array $form_fields Массив с конфигурацией полей
 * @param string $method Метод формы (GET/POST)
 * @param array $attributes Дополнительные атрибуты формы (class, style и т.д.)
 *
 * @return void
 */
function wpp_render_form( string $form_id, array $form_fields, string $method = 'post', array $attributes = [ 'classes' => 'wpp-custom-form container-fluid row' ] ) {
	$classes = [ 'wpp-custom-form', 'row' ];
	$attrs   = '';

	if ( ! empty( $attributes['class'] ) ) {
		$classes = array_merge( $classes, explode( ' ', $attributes['class'] ) );
		unset( $attributes['class'] );
	}

	echo '<form id="' . esc_attr( $form_id ) . '" method="' . esc_attr( $method ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '"';

	foreach ( $attributes as $attr => $value ) {
		echo ' ' . esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
	}

	echo '>';

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
 * Displays debug data for loan form steps in development mode.
 *
 * This function checks if development mode is enabled and outputs formatted
 * session data for the specified step. The data is displayed in a styled
 * <pre> tag for easy debugging.
 *
 * @since 1.0.0
 *
 * @param int $step The step number to retrieve debug data for.
 * @return void Outputs HTML directly when in development mode.
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
 * Преобразует данные формы в одномерный массив для сохранения
 *
 * @param array $all_data Все данные из $_SESSION
 * @return array Одномерный массив данных
 */
function wpp_flatten_session_data($all_data) {
	$flattened = [];

	foreach ($all_data as $step => $step_data) {
		// Пропускаем шаги без formData
		if (!isset($step_data) || !is_array($step_data)) {
			continue;
		}

		foreach ($step_data as $key => $value) {
			// Пропускаем step_identifier
			if ($key === 'step_identifier') {
				continue;
			}

			$new_key = 's' . $step . '_' . $key;

			// Добавляем значение
			if (!is_array($value)) {
				$flattened[$new_key] = $value;
			} else {
				// Если значение массив — можно сериализовать или превратить в строку
				$flattened[$new_key] = maybe_serialize($value);
			}
		}
	}

	return $flattened;
}


/**
 * Сохраняет всю сессию в таблицу `wpp_loan_raw_applications`
 * И очищает PHP-сессию
 *
 * @param string $session_id ID сессии
 * @param array $all_data Все данные заявки
 * @param int $user_id WP User ID (опционально)
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


function wpp_has_shortcode_on_page() {
	global $post;

	// 1. Get the value of the constant (which is a JSON string)
	$steps_json = defined('WPP_LOAN_STEPS') ? WPP_LOAN_STEPS: null;

	// 2. Check if the constant is defined
	if (!$steps_json) {
		error_log('WPP_LOAN_STEPS is not defined.');
		return false;
	}

	// 3. Decode the JSON string into a PHP array
	$steps = json_decode($steps_json, true); // Second argument true for associative array

	// 4. Check if decoding was successful
	if (!is_array($steps)) {
		error_log('WPP_LOAN_STEP is not a valid JSON string or could not be decoded.');
		return false;
	}

	// 5. Now we can safely use foreach
	foreach ( $steps as $key => $one ) {

		// Check if the shortcode exists in the post/page content
		// IMPORTANT: has_shortcode looks for the shortcode name WITHOUT square brackets.
		// Therefore, the 'short' value in your array should contain only the name, e.g., 'wpp_loan_application_step_1'
		$shortcode_name = trim($one['short'], '[]'); // Remove square brackets if they exist

		if ( is_singular() && has_shortcode( $post->post_content, $shortcode_name ) ) {

			$step_data = WPP_Loan_Session_Handler::get_step_data($key);

			/*if(empty($step_data)) {
				wp_safe_redirect(get_home_url()); /////////////////////////////////////////////////
				exit; // Always use exit after wp_safe_redirect
			}*/
		}
	}

	return false;
}

add_action('wp', 'wpp_has_shortcode_on_page');