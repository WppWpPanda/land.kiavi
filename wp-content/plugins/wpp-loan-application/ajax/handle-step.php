<?php
/**
 * AJAX-обработчик шагов формы заявки
 *
 * Обрабатывает данные формы, сохраняет их в сессии и перенаправляет на следующий шаг.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Подключаем класс сессии, если он ещё не загружен
if ( ! class_exists( 'WPP_Loan_Session_Handler' ) ) {
	require_once WPP_LOAN_PATH . 'includes/class-wpp-loan-session.php';
}

// Регистрируем хуки для сохранения данных
add_action( 'wp_ajax_wpp_save_step_data', 'wpp_save_step_data' );
add_action( 'wp_ajax_nopriv_wpp_save_step_data', 'wpp_save_step_data' );

/**
 * Обработчик AJAX-запроса для сохранения данных формы
 */
function wpp_save_step_data() {

	// Проверяем nonce
	//check_ajax_referer('wpp_loan_nonce', 'security');

	// Получаем номер текущего шага
	$step = sanitize_text_field( $_POST['step'] );

	// Парсим formData
	$form_data = json_decode( stripslashes( $_POST['formData'] ), true );

	if ( ! is_array( $form_data ) ) {
		wp_send_json_error( [
			'message' => 'Ошибка парсинга данных формы',
			'raw'     => $_POST['formData']
		] );
		exit;
	}

	// Сохраняем в БД (в одну строку) через статический метод
	$saved = WPP_Loan_Session_Handler::save_step_data( $step, $form_data );

	//сохраняем гаранта
	if ( 2 === (int) $step ) {
		$guarantor_id = wpp_save_guarantor( $form_data );

		if ( $guarantor_id ) {
			error_log( 'Гарант сохранён под ID: ' . $guarantor_id );
			$saved = WPP_Loan_Session_Handler::save_step_data( 'guarantor', ['id'=>$guarantor_id] );
		} else {
			error_log( 'Ошибка при сохранении гаранта' );
		}

	}

	if ( ! $saved ) {
		wp_send_json_error( [
			'message'    => 'Не удалось сохранить данные в базу',
			'step'       => $step,
			'session_id' => WPP_Loan_Session_Handler::get_session_id()
		] );
	}

	// Определяем следующий шаг
	$steps = defined( 'WPP_LOAN_STEPS' ) ? json_decode( WPP_LOAN_STEPS, true ) : [];

	$next_step_num = $step + 1;

	$next_step_slug = 'complete'; // значение по умолчанию

	if ( ! empty( $steps[ $next_step_num ] ) ) {
		$next_step_slug = $steps[ $next_step_num ]['slug'];
	}

	// Формируем URL редиректа
	$redirect_url = '/' . ltrim( $next_step_slug, '/' );

	// Отправляем успешный ответ
	wp_send_json_success( [
		'redirect'     => esc_url_raw( $redirect_url ),
		'currentStep'  => $step,
		'nextStepNum'  => $next_step_num,
		'nextStepSlug' => $next_step_slug,
		'data'         => $form_data
	] );
}