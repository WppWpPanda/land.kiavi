<?php

defined( 'ABSPATH' ) || exit;

add_action( 'template_redirect', 'wpp_process_loan_form' );
function wpp_process_loan_form() {
	// Проверяем что это POST-запрос и есть нужные поля
	if ( $_SERVER['REQUEST_METHOD'] !== 'POST' || ! isset( $_POST['current_loan_id'] ) ) {
		return;
	}

	// Проверяем nonce для безопасности
	if ( ! isset( $_POST['wpp_loan_nonce'] ) || ! wp_verify_nonce( $_POST['wpp_loan_nonce'], 'wpp_save_loan_data' ) ) {
		// Сохраняем ошибку в сессию для отображения в форме
		wpp_set_form_error( 'Ошибка безопасности. Пожалуйста, попробуйте еще раз.' );

		return;
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'wpp_loans_full_data';

	// Получаем и очищаем loan_id
	$loan_id = sanitize_text_field( $_POST['current_loan_id'] );

	if ( empty( $loan_id ) ) {
		wpp_set_form_error( 'ID займа не может быть пустым' );

		return;
	}

	// Фильтруем данные формы
	$exclude_fields = [
		'current_loan_id',
		'wpp_loan_nonce',
		'_wp_http_referer',
		'_wpnonce'
	];

	$clean_data = [];

	//wpp_d_log($_POST);
	foreach ( $_POST as $key => $value ) {
		if ( ! in_array( $key, $exclude_fields ) ) {
			/*$clean_data[$key] = is_array($value)
				? array_map('sanitize_text_field', $value)
				: sanitize_text_field($value);*/

			$clean_data[ $key ] = $value;
		}
	}

	// Сохраняем данные
	$result = $wpdb->replace(
		$table_name,
		[
			'loan_id'   => $loan_id,
			'loan_data' => maybe_serialize( $clean_data )
		],
		[ '%s', '%s' ]
	);

	if ( $result === false ) {
		wpp_set_form_error( 'Ошибка при сохранении данных' );
	} else {
		wpp_set_form_notice( 'Данные успешно сохранены' );
	}
}

// Вспомогательные функции для уведомлений
function wpp_set_form_error( $message ) {
	if ( ! session_id() ) {
		session_start();
	}
	$_SESSION['wpp_form_errors'][] = $message;
}

function wpp_set_form_notice( $message ) {
	if ( ! session_id() ) {
		session_start();
	}
	$_SESSION['wpp_form_notices'][] = $message;
}

function wpp_get_form_messages() {
	if ( ! session_id() ) {
		session_start();
	}

	$output = '';

	// Выводим ошибки
	if ( ! empty( $_SESSION['wpp_form_errors'] ) ) {
		foreach ( $_SESSION['wpp_form_errors'] as $error ) {
			$output .= '<div class="notice notice-error"><p>' . esc_html( $error ) . '</p></div>';
		}
		unset( $_SESSION['wpp_form_errors'] );
	}

	// Выводим уведомления
	if ( ! empty( $_SESSION['wpp_form_notices'] ) ) {
		foreach ( $_SESSION['wpp_form_notices'] as $notice ) {
			$output .= '<div class="notice notice-success"><p>' . esc_html( $notice ) . '</p></div>';
		}
		unset( $_SESSION['wpp_form_notices'] );
	}

	return $output;
}


/*add_action( 'wp_ajax_wpp_get_loan_data', 'wpp_get_loan_data' );
add_action( 'wp_ajax_nopriv_wpp_get_loan_data', 'wpp_get_loan_data' );*/

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