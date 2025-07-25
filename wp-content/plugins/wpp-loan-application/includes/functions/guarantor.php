<?php


/**
 * Сохраняет или обновляет данные гаранта в таблице `wpp_loan_guarantors`
 *
 * @param array $form_data Данные формы с ключами:
 * - first_name
 * - last_name
 * - suffix (опционально)
 *
 * @return bool|int ID гаранта или false при ошибке
 */
function wpp_save_guarantor( $form_data ) {
	global $wpdb;

	// Проверяем обязательные поля
	if (
		empty( $form_data['first_name'] ) ||
		empty( $form_data['last_name'] )
	) {
		error_log( '❌ Не все обязательные поля заполнены' );

		return false;
	}

	$guarantor_table = $wpdb->prefix . 'loan_guarantors';

	// Подготавливаем данные
	$data_to_save = [
		'first_name' => sanitize_text_field( $form_data['first_name'] ),
		'last_name'  => sanitize_text_field( $form_data['last_name'] ),
		'suffix'     => ! empty( $form_data['suffix'] ) ? sanitize_text_field( $form_data['suffix'] ) : null,
		'updated_at' => current_time( 'mysql' )
	];

	// Создаём уникальный ключ на основе имени и фамилии
	$data_to_save['guarantor_key'] = md5(
		$data_to_save['first_name'] .
		$data_to_save['last_name'] .
		( $data_to_save['suffix'] ?? '' )
	);

	// Ищем существующего гаранта по guarantor_key
	$existing = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT id FROM $guarantor_table WHERE guarantor_key = %s",
			$data_to_save['guarantor_key']
		),
		ARRAY_A
	);

	$guarantor_id = false;

	if ( $existing ) {
		// Обновляем существующего гаранта
		$wpdb->update(
			$guarantor_table,
			$data_to_save,
			[ 'id' => $existing['id'] ]
		);
		$guarantor_id = $existing['id'];
	} else {
		// Вставляем нового гаранта
		$data_to_save['created_at'] = current_time( 'mysql' );
		$wpdb->insert( $guarantor_table, $data_to_save );
		$guarantor_id = $wpdb->insert_id;
	}

	if ( ! $guarantor_id ) {
		error_log( '❌ Не удалось сохранить данные гаранта' );

		return false;
	}

	return $guarantor_id;
}


/**
 * Привязывает гаранта к одной или нескольким заявкам
 *
 * @param int $guarantor_id ID гаранта
 * @param array|int $loan_ids Массив или одиночный loan_id
 *
 * @return bool
 */
function wpp_link_guarantor_to_loans( $guarantor_id, $loan_ids ) {
	global $wpdb;

	$relations_table = $wpdb->prefix . 'loan_guarantor_relations';
	$guarantor_id    = intval( $guarantor_id );


	if ( $guarantor_id <= 0 ) {
		error_log( '❌ Некорректный guarantor_id' );

		return false;
	}

	// Приводим к массиву
	if ( ! is_array( $loan_ids ) ) {
		$loan_ids = [ $loan_ids ];
	}

	$loan_ids = array_filter( array_map( 'intval', $loan_ids ) );


	if ( empty( $loan_ids ) ) {
		error_log( '⚠️ Нет заявок для привязки' );

		return true; // считаем успехом, если нет заявок
	}

	foreach ( $loan_ids as $loan_id ) {

		if ( $loan_id <= 0 ) {
			continue;
		}

		// Проверяем, есть ли уже такая связь
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $relations_table WHERE loan_id = %d AND guarantor_id = %d",
				$loan_id,
				$guarantor_id
			)
		);

		if ( empty( $exists ) ) {

			// Добавляем новую связь
			$result = $wpdb->insert( $relations_table, [
				'loan_id'      => $loan_id,
				'guarantor_id' => $guarantor_id,
			] );

		}
	}

	return true;
}

/**
 * Получает всех гарантов из базы данных
 *
 * @return array Массив в формате [id => 'John Doe', ...]
 */
function wpp_get_all_guarantors_for_dropdown() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'loan_guarantors';
	$results = $wpdb->get_results("SELECT id, first_name, last_name, suffix FROM $table_name ORDER BY last_name ASC", ARRAY_A);

	$dropdown_options = [];

	foreach ($results as $row) {
		$id = intval($row['id']);
		$first_name = esc_html($row['first_name']);
		$last_name = esc_html($row['last_name']);
		$suffix = !empty($row['suffix']) ? esc_html($row['suffix']) : '';

		// Формируем отображаемое имя
		if ($suffix) {
			$display_name = "$first_name $last_name $suffix";
		} else {
			$display_name = "$first_name $last_name";
		}

		$dropdown_options[$id] = $display_name;
	}

	return $dropdown_options;
}