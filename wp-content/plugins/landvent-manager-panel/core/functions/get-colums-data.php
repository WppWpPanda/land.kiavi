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
function get_colums_data( $id = null ) {
	$out = [];
	global $wpdb;
	$table_name = $wpdb->prefix . 'loan_raw_applications'; // Если таблица имеет префикс WordPress

	$fef = ! empty( $id ) ? ' WHERE id=' . $id : '';

	$results = $wpdb->get_results( "SELECT * FROM $table_name" . $fef );

	//debugPanel( $results );
	if ( ! empty( $results ) ) {
		foreach ( $results as $result ) {
			if ( ! empty( json_decode( $result->raw_data ) ) ) {
				$out[ $result->id ] = [
					'id'       => $result->id,
					'date'     => $result->updated_at,
					//'raw_data' => json_decode($result->raw_data)
					'raw_data' => json_decode( $result->raw_data )
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
 * @return array An array of column objects with the following properties:
 *               - id (int): The column's unique identifier
 *               - title (string): The display title of the column
 *               - column_order (int): The position/order of the column
 *               - cards (array): Array of card IDs belonging to this column
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @since 1.0.0
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
	$columns    = $wpdb->get_results(
		"SELECT id, title, column_order, card_ids 
         FROM $table_name 
         ORDER BY column_order ASC"
	);

	if ( ! $columns ) {
		return [];
	}

	// Decode card_ids for each column
	foreach ( $columns as &$column ) {
		$column->cards = json_decode( $column->card_ids, true ) ?: [];
		unset( $column->card_ids ); // Remove the original field
	}

	return $columns;
}

function wpp_field_value( $key, $data = null, $default = null ) {

	if ( empty( $data ) ) {
		$data = wpp_get_loan_data_r();
	}

	$default = ! isset( $default ) ? '' : $default;

	return isset( $data[ $key ] ) ? $data[ $key ] : $default;

}


function wpp_default_value( $default, $args ) {

	if ( ! empty( $_GET['loan'] ) ) {

		$data = wpp_get_loan_data_r();

		if ( ! empty( $data[ $args['name'] ] ) ) {
			return $data[ $args['name'] ];
		}
	}

	return $default;

}

add_filter( 'wpp_form_field_default', 'wpp_default_value', 10, 2 );


function wpp_get_loan_data_purchase_price( $default, $args ) {
	if ( ! empty( $_GET['loan'] ) ) {
		if ( empty( str_replace('$', '', $default) ) ) {
			$data = get_colums_data( $_GET['loan'] );
			return $data[ $_GET['loan'] ]['raw_data']->s4_purchase_price;
		}
	}

	return $default;

}
add_filter( 'wpp_form_field_default_purchase_price', 'wpp_get_loan_data_purchase_price', 10, 2 );


function wpp_get_loan_data_total_loan_amount( $default, $args ) {
	if ( ! empty( $_GET['loan'] ) ) {
		if ( empty( str_replace('$', '', $default) ) ) {
			$data = get_colums_data( $_GET['loan'] );

			return $data[ $_GET['loan'] ]['raw_data']->s4_total_loan_amount_sum;
		}
	}

	return $default;

}
add_filter( 'wpp_form_field_default_total_loan_amount', 'wpp_get_loan_data_total_loan_amount', 10, 2 );

function wpp_get_loan_data_after_repair_value( $default, $args ) {
	if ( ! empty( $_GET['loan'] ) ) {
		if ( empty( str_replace('$', '', $default) ) ) {
			$data = get_colums_data( $_GET['loan'] );

			return $data[ $_GET['loan'] ]['raw_data']->s4_after_repair_value;
		}
	}

	return $default;

}
add_filter( 'wpp_form_field_default_after_repair_value', 'wpp_get_loan_data_after_repair_value', 10, 2 );

function wpp_get_loan_data_total_repair_cost( $default, $args ) {
	if ( ! empty( $_GET['loan'] ) ) {
		if ( empty( str_replace('$', '', $default) ) ) {
			$data = get_colums_data( $_GET['loan'] );

			return $data[ $_GET['loan'] ]['raw_data']->s4_rehab_cost;
		}
	}

	return $default;

}
add_filter( 'wpp_form_field_default_total_repair_cost', 'wpp_get_loan_data_total_repair_cost', 10, 2 );

function wpp_get_loan_data_term( $default, $args ) {
	if ( ! empty( $_GET['loan'] ) ) {
		if ( empty( trim(str_replace('Months', '', $default) )) ) {
			$data = get_colums_data( $_GET['loan'] );

			return trim(str_replace('Months', '', $data[ $_GET['loan'] ]['raw_data']->s4_chosen_rate_type));
		}
	}

	return $default;

}
add_filter( 'wpp_form_field_default_term', 'wpp_get_loan_data_term', 10, 2 );

function wpp_get_loan_data_interest_rate( $default, $args ) {

	if ( ! empty( $_GET['loan'] ) ) {
		if ( empty(  (float)str_replace(['%'], '', $default) ) ) {
			$data = get_colums_data( $_GET['loan'] );
			return $data[ $_GET['loan'] ]['raw_data']->s4_chosen_rate;
		}
	}

	return $default;

}
add_filter( 'wpp_form_field_default_interest_rate', 'wpp_get_loan_data_interest_rate', 10, 2 );


function wpp_get_loan_data_loan_type( $default, $args ) {
	if ( ! empty( $_GET['loan'] ) ) {
		if ( ! empty( $default ) ) {
			$data = get_colums_data( $_GET['loan'] );

			if( !empty( $val = $data[ $_GET['loan'] ]['raw_data']->s4_refinance ) ){
				return 'no' ? 'purchase' : 'refinance';
			}

		}
	}

	return $default;

}

add_filter( 'wpp_form_field_default_loan_type', 'wpp_get_loan_data_loan_type', 10, 2 );