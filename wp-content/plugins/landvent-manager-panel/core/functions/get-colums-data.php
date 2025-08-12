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
 *               - 'loan_id'        => (int) ID записи
 *               - 'raw_data'  => (mixed) Десериализованные данные из поля raw_data
 *               An associative array where the key is the record ID and the value is an array containing:
 *               - 'id'        => (int) Record ID
 *               - 'raw_data'  => (mixed) Unserialized data from the raw_data field
 */
function get_colums_data( $loan_id = null ) {
	$out = [];

	global $wpdb;

	if ( empty( $loan_id ) ) {
		global $loan_id;
	}

	$table_name = $wpdb->prefix . 'loan_raw_applications'; // Если таблица имеет префикс WordPress

	$fef = ! empty( $loan_id ) ? ' WHERE id=' . $loan_id : '';

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

/**
 * Retrieves a specific field value from loan data, with fallback to default.
 *
 * This function safely fetches a value by key from provided data or from
 * the current loan's data (via `wpp_get_loan_data_r()`). If the key is not found,
 * it returns a specified default value.
 *
 * @param string $key The field key to retrieve (e.g., 'loan_amount', 'status').
 * @param array|null $data Optional. Associative array of data. If null, loads loan data.
 * @param mixed $default Optional. Default value to return if key is not found.
 *
 * @return mixed The value if found; otherwise, the default.
 *
 * @since 1.0.0
 *
 * @author WP_Panda <panda@wp-panda.pro>
 *
 * @link https://developer.wordpress.org/reference/functions/apply_filters/ Consider adding filter hooks for extensibility
 * @link https://www.php.net/manual/en/function.isset.php On usage of `isset()` vs `array_key_exists()`
 *
 * @example
 * $amount = wpp_field_value( 'loan_amount', null, 0 ); // Uses current loan data
 * $status = wpp_field_value( 'status', $custom_data, 'pending' );
 */
function wpp_field_value( $key, $data = null, $default = null ) {
	// Ensure key is a non-empty string
	if ( ! is_string( $key ) || '' === trim( $key ) ) {
		return $default;
	}

	// Use provided data or fallback to loan data
	if ( null === $data ) {
		$data = wpp_get_loan_data_r();
	}

	// Ensure $data is an array to prevent warnings
	if ( ! is_array( $data ) ) {
		$data = array();
	}

	// Return the value if exists, otherwise return default
	// Using array_key_exists() allows 'null', 'false', '0', '' to be valid values
	return array_key_exists( $key, $data ) ? $data[ $key ] : $default;
}

/**
 * Filters the default value for a form field, using loan data if available.
 *
 * If a loan ID is set and the requested field exists in the loan data,
 * this function returns the saved value instead of the default.
 *
 * @param mixed $default Default value for the form field.
 * @param array $args Field arguments, expected to contain 'name' key.
 *
 * @return mixed The value from loan data if available; otherwise, the original default.
 * @link https://developer.wordpress.org/reference/functions/apply_filters/ About `wpp_form_field_default`
 *
 * @since 1.0.0
 *
 * @author WP_Panda <panda@wp-panda.pro>
 *
 * @link https://developer.wordpress.org/reference/functions/add_filter/ For info on filters
 */
function wpp_default_value( $default, $args ) {
	// Ensure required parameter 'name' is present and valid
	if ( ! isset( $args['name'] ) || ! is_string( $args['name'] ) ) {
		return $default;
	}

	// Access global loan ID (set by the dashboard/router logic)
	global $loan_id;

	// Return early if no loan ID is set
	if ( empty( $loan_id ) ) {
		return $default;
	}

	/**
	 * Retrieve loan data from storage or cache.
	 *
	 * Assumed to return an array of key-value pairs for the current loan.
	 * Consider caching this result (e.g., using wp_cache_set/get) if called multiple times.
	 *
	 * @see wpp_get_loan_data_r() - Should be defined elsewhere in the plugin
	 */
	$loan_data = wpp_get_loan_data_r();

	// Return early if data is not an array or field doesn't exist
	if ( ! is_array( $loan_data ) || ! array_key_exists( $args['name'], $loan_data ) ) {
		return $default;
	}

	$value = $loan_data[ $args['name'] ];

	// Avoid returning empty string, null, or false if default should prevail
	// Optional: Uncomment next lines if you want to fallback on "empty" values
	// if ( empty( $value ) && ! is_numeric( $value ) ) {
	//     return $default;
	// }

	return $value;
}

// Hook into the form field default filter
add_filter( 'wpp_form_field_default', 'wpp_default_value', 10, 2 );


function wpp_get_loan_data_purchase_price( $default, $args ) {
	global $loan_id;
	if ( ! empty( $loan_id ) ) {
		if ( empty( str_replace( '$', '', $default ) ) ) {
			$data = get_colums_data( $loan_id );

			return $data[ $loan_id ]['raw_data']->s4_purchase_price;
		}
	}

	return $default;

}

add_filter( 'wpp_form_field_default_purchase_price', 'wpp_get_loan_data_purchase_price', 10, 2 );


function wpp_get_loan_data_total_loan_amount( $default, $args ) {
	global $loan_id;
	if ( ! empty( $loan_id ) ) {
		if ( empty( str_replace( '$', '', $default ) ) ) {
			$data = get_colums_data( $loan_id );

			return $data[ $loan_id ]['raw_data']->s4_total_loan_amount_sum;
		}
	}

	return $default;

}

add_filter( 'wpp_form_field_default_total_loan_amount', 'wpp_get_loan_data_total_loan_amount', 10, 2 );

function wpp_get_loan_data_after_repair_value( $default, $args ) {
	global $loan_id;
	if ( ! empty( $loan_id ) ) {
		if ( empty( str_replace( '$', '', $default ) ) ) {
			$data = get_colums_data( $loan_id );

			return $data[ $loan_id ]['raw_data']->s4_after_repair_value;
		}
	}

	return $default;

}

add_filter( 'wpp_form_field_default_after_repair_value', 'wpp_get_loan_data_after_repair_value', 10, 2 );

function wpp_get_loan_data_total_repair_cost( $default, $args ) {
	global $loan_id;
	if ( ! empty( $loan_id ) ) {
		if ( empty( str_replace( '$', '', $default ) ) ) {
			$data = get_colums_data( $loan_id );

			return $data[ $loan_id ]['raw_data']->s4_rehab_cost;
		}
	}

	return $default;

}

add_filter( 'wpp_form_field_default_total_repair_cost', 'wpp_get_loan_data_total_repair_cost', 10, 2 );

function wpp_get_loan_data_term( $default, $args ) {
	global $loan_id;
	if ( ! empty( $loan_id ) ) {
		if ( empty( trim( str_replace( 'Months', '', $default ) ) ) ) {
			$data = get_colums_data( $loan_id );

			return trim( str_replace( 'Months', '', $data[ $loan_id ]['raw_data']->s4_chosen_rate_type ) );
		}
	}

	return $default;

}

add_filter( 'wpp_form_field_default_term', 'wpp_get_loan_data_term', 10, 2 );

function wpp_get_loan_data_interest_rate( $default, $args ) {

	global $loan_id;
	if ( ! empty( $loan_id ) ) {
		if ( empty( (float) str_replace( [ '%' ], '', $default ) ) ) {
			$data = get_colums_data( $loan_id );

			return $data[ $loan_id ]['raw_data']->s4_chosen_rate;
		}
	}

	return $default;

}

add_filter( 'wpp_form_field_default_interest_rate', 'wpp_get_loan_data_interest_rate', 10, 2 );


function wpp_get_loan_data_loan_type( $default, $args ) {
	global $loan_id;
	if ( ! empty( $loan_id ) ) {
		if ( ! empty( $default ) ) {
			$data = get_colums_data( $loan_id );

			if ( ! empty( $val = $data[ $loan_id ]['raw_data']->s4_refinance ) ) {
				return 'no' ? 'purchase' : 'refinance';
			}

		}
	}

	return $default;

}

add_filter( 'wpp_form_field_default_loan_type', 'wpp_get_loan_data_loan_type', 10, 2 );