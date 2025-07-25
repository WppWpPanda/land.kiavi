<?php
defined( 'ABSPATH' ) || exit;

/**
 * Retrieves data from the 'loan_raw_applications' table and returns it as an associative array.
 *
 * Извлекает данные из таблицы 'loan_raw_applications' и возвращает их в виде ассоциативного массива.
 *
 *
 *  The function uses the global $wpdb variable to interact with the WordPress database.
 *  It fetches all records from the table with the WordPress prefix (e.g., wp_loan_raw_applications).
 *  For each row, if the 'raw_data' field is not empty, it will be unserialized using unserialize()
 *  and stored along with the record's ID.
 *
 * Функция использует глобальную переменную $wpdb для работы с базой данных WordPress.
 * Получает все записи из таблицы, которая имеет префикс WordPress (например, wp_loan_raw_applications).
 * Для каждой строки, если поле raw_data не пустое, оно десериализуется с помощью unserialize()
 * и сохраняется вместе с id записи.
 *
 * @return array Ассоциативный массив, где ключ — это ID записи, а значение — массив с полями:
 *               - 'id'        => (int) ID записи
 *               - 'raw_data'  => (mixed) Десериализованные данные из поля raw_data
 *               An associative array where the key is the record ID and the value is an array containing:
 *               - 'id'        => (int) Record ID
 *               - 'raw_data'  => (mixed) Unserialized data from the raw_data field
 */
function get_colums_data() {
	$out = [];
	global $wpdb;
	$table_name = $wpdb->prefix . 'loan_raw_applications'; // Если таблица имеет префикс WordPress

	$results = $wpdb->get_results( "SELECT * FROM $table_name" );

	//debugPanel( $results );
	if ( ! empty( $results ) ) {
		foreach ( $results as $result ) {
			if ( ! empty( json_decode( $result->raw_data ) ) ) {
				$out[ $result->id ] = [
					'id'       => $result->id,
					'date'     => $result->updated_at,
					//'raw_data' => json_decode($result->raw_data)
					'raw_data' => json_decode($result->raw_data)
				];
			}
		}
	}

	return $out;

}



/**
 * Retrieves all Trello-style columns with their associated cards from the database.
 *
 * This function queries the loan_raw_applications table to fetch all columns,
 * their metadata, and the card IDs associated with each column. The card IDs
 * are decoded from JSON format into an array and attached to each column object.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return array An array of column objects with the following properties:
 *               - id (int): The column's unique identifier
 *               - title (string): The display title of the column
 *               - column_order (int): The position/order of the column
 *               - cards (array): Array of card IDs belonging to this column
 *
 * @example
 * $columns = get_all_columns_with_cards();
 * foreach ($columns as $column) {
 *     echo "Column {$column->title} has " . count($column->cards) . " cards";
 * }
 */
function get_all_columns_with_cards() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'loan_raw_applications';
	$columns = $wpdb->get_results(
		"SELECT id, title, column_order, card_ids 
         FROM $table_name 
         ORDER BY column_order ASC"
	);

	if (!$columns) {
		return [];
	}

	// Decode card_ids for each column
	foreach ($columns as &$column) {
		$column->cards = json_decode($column->card_ids, true) ?: [];
		unset($column->card_ids); // Remove the original field
	}

	return $columns;
}

function wpp_field_value( $key, $data = null , $default = null ) {

	if( empty($data) ) {
		$data = wpp_get_loan_data_r();
	}

	$default = !isset( $default ) ? '' : $default;

	return isset( $data[ $key ] ) ? $data[ $key ] : $default;

}