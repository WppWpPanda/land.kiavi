<?php
// Добавляем обработчик AJAX для авторизованных пользователей
add_action('wp_ajax_add_trello_column', 'handle_add_trello_column');
add_action('wp_ajax_nopriv_add_trello_column', 'handle_add_trello_column');

function handle_add_trello_column() {
	// Проверяем nonce для безопасности
	///check_ajax_referer('trello_ajax_nonc_e', 'nonce');

	global $wpdb;
	$table_name = $wpdb->prefix . 'wpp_trello_columns';

	// Получаем данные из запроса
	$title = sanitize_text_field($_POST['title']);
	$column_order = intval($_POST['column_order']);

	// Вставляем новую колонку в базу данных
	$result = $wpdb->insert(
		$table_name,
		array(
			'title' => $title,
			'column_order' => $column_order,
			'card_ids' => '[]'
		),
		array(
			'%s', // title
			'%d', // column_order
			'%s'  // card_ids
		)
	);

	if ($result) {
		// Получаем данные только что добавленной колонки
		$column_id = $wpdb->insert_id;
		$column = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $column_id)
		);

		wp_send_json_success(array(
			'column' => $column
		));
	} else {
		wp_send_json_error(array(
			'message' => 'Failed to add column to database'
		));
	}
}


function wpp_get_all_trello_columns() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'wpp_trello_columns';

	$sql = $wpdb->prepare("SELECT * FROM $table_name ORDER BY column_order ASC");
	$results = $wpdb->get_results($sql);

	if ($wpdb->last_error) {
		error_log('Trello Columns DB Error: ' . $wpdb->last_error);
		return [];
	}

	return $results ?: [];
}


add_action('wp_ajax_delete_trello_column', 'handle_delete_trello_column');
function handle_delete_trello_column() {
	//check_ajax_referer('delete-column-nonce', 'security');

	/*if (!current_user_can('manage_options')) {
		wp_send_json_error('Доступ запрещен');
	}*/

	global $wpdb;
	$table_name = $wpdb->prefix . 'wpp_trello_columns';
	$column_id = intval($_POST['column_id']);

	$result = $wpdb->delete($table_name, ['id' => $column_id]);

	if ($result) {
		wp_send_json_success();
	} else {
		wp_send_json_error('Ошибка базы данных');
	}
}

add_action('wp_ajax_update_card_position', 'update_card_position_callback');

/**
 * AJAX handler for updating card position with transactions
 */
function update_card_position_callback() {
	global $wpdb;

	$column_name = $wpdb->prefix . 'wpp_trello_columns' ;

	// Verify nonce first
	if (!check_ajax_referer('trello_nonce', 'nonce', false)) {
		wp_send_json_error([
			'message' => 'Invalid security token. Please refresh the page and try again.',
			'error_code' => 'invalid_nonce'
		], 403);
		return;
	}

	// Validate required parameters
	if (!isset($_POST['card_id'], $_POST['column_id'], $_POST['position'])) {
		wp_send_json_error([
			'message' => 'Missing required parameters.',
			'error_code' => 'missing_parameters'
		], 400);
		return;
	}

	$card_id = intval($_POST['card_id']);
	$column_id_param = $_POST['column_id'];
	$position = intval($_POST['position']);

	// Проверяем, является ли column_id "new" или "New"
	$is_new_column = (strtolower($column_id_param) === 'new');

	// Если это не "new", то валидируем как число
	if (!$is_new_column) {
		$new_column_id = intval($column_id_param);
		if ($new_column_id <= 0) {
			wp_send_json_error([
				'message' => 'Invalid column ID.',
				'error_code' => 'invalid_column_id'
			], 400);
			return;
		}
	}

	// Validate card_id
	if ($card_id <= 0) {
		wp_send_json_error([
			'message' => 'Invalid card ID.',
			'error_code' => 'invalid_card_id'
		], 400);
		return;
	}

	// Start transaction
	$wpdb->query('START TRANSACTION');

	// Find old column
	$old_column = $wpdb->get_row($wpdb->prepare(
		"SELECT * FROM $column_name 
         WHERE JSON_CONTAINS(card_ids, JSON_ARRAY(%d)) 
         LIMIT 1 FOR UPDATE",
		$card_id
	));

	/*if (!$old_column) {
		$wpdb->query('ROLLBACK');
		wp_send_json_error([
			'message' => "Card not found in any column.",
			'error_code' => 'card_not_found'
		], 404);
		return;
	}*/

	// Если column_id = "new", просто удаляем карточку из старой колонки
	if ($is_new_column) {
		$old_cards = json_decode($old_column->card_ids, true);
		$old_cards = array_values(array_filter($old_cards, function($id) use ($card_id) {
			return $id != $card_id;
		}));

		$result = $wpdb->update(
			$column_name,
			['card_ids' => json_encode($old_cards)],
			['id' => $old_column->id],
			['%s'],
			['%d']
		);

		if ($result === false) {
			$wpdb->query('ROLLBACK');
			wp_send_json_error([
				'message' => 'Failed to remove card from column.',
				'error_code' => 'remove_card_failed'
			], 500);
			return;
		}

		// Commit transaction
		$wpdb->query('COMMIT');

		// Return success response
		wp_send_json_success([
			'message' => 'Card removed from column successfully',
			'action' => 'removed',
			'old_column_id' => $old_column->id,
			'card_count' => count($old_cards)
		]);
		return;
	}

	// Если это обычная колонка (не "new"), выполняем обычную логику перемещения

	// Get new column with lock
	$new_column = $wpdb->get_row($wpdb->prepare(
		"SELECT * FROM $column_name WHERE id = %d FOR UPDATE",
		$new_column_id
	));

	if (!$new_column) {
		$wpdb->query('ROLLBACK');
		wp_send_json_error([
			'message' => "Target column not found.",
			'error_code' => 'column_not_found'
		], 404);
		return;
	}

	// Process old column (if moving between columns)
	if ($old_column && $old_column->id != $new_column_id) {
		$old_cards = json_decode($old_column->card_ids, true);
		$old_cards = array_values(array_filter($old_cards, function($id) use ($card_id) {
			return $id != $card_id;
		}));

		$result = $wpdb->update(
			$column_name,
			['card_ids' => json_encode($old_cards)],
			['id' => $old_column->id],
			['%s'],
			['%d']
		);

		if ($result === false) {
			$wpdb->query('ROLLBACK');
			wp_send_json_error([
				'message' => 'Failed to update source column.',
				'error_code' => 'source_column_update_failed'
			], 500);
			return;
		}
	}

	// Process new column
	$new_cards = json_decode($new_column->card_ids, true);
	$new_cards = array_values(array_filter($new_cards, function($id) use ($card_id) {
		return $id != $card_id;
	}));
	array_splice($new_cards, $position, 0, [$card_id]);

	$result = $wpdb->update(
		$column_name,
		['card_ids' => json_encode($new_cards)],
		['id' => $new_column_id],
		['%s'],
		['%d']
	);

	if ($result === false) {
		$wpdb->query('ROLLBACK');
		wp_send_json_error([
			'message' => 'Failed to update target column.',
			'error_code' => 'target_column_update_failed'
		], 500);
		return;
	}

	// Commit transaction
	$wpdb->query('COMMIT');

	// Return success response
	wp_send_json_success([
		'message' => 'Card position updated successfully',
		'action' => 'moved',
		'old_column_id' => $old_column ? $old_column->id : null,
		'new_column_id' => $new_column_id,
		'position' => $position,
		'card_count' => count($new_cards)
	]);
}