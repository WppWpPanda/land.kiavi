<?php
defined( 'ABSPATH' ) || exit;

/**
 * Retrieves broker data by ID from the custom brokers table.
 *
 * This function queries the `wpp_brokers` database table to fetch a single broker's
 * information based on the provided ID. It uses WordPress's $wpdb class for safe,
 * prepared SQL queries to prevent SQL injection.
 *
 * Returns the broker as an associative array on success, or `false` if no broker
 * is found or the ID is invalid.
 *
 *
 * @since 1.0.0
 * @author WP_Panda <panda@wp-panda.pro>
 *
 * @param int $broker_id The unique ID of the broker to retrieve.
 * @return array|false Associative array of broker data if found, otherwise false.
 *
 * @example
 *   $data = get_broker_by_id(5);
 *   if ($data) {
 *       echo $data['brok_brokerage_name']; // e.g., "ABC Realty"
 *   }
 *
 */
function get_broker_by_id( $broker_id ) {
	// Access the global $wpdb object for database operations
	global $wpdb;

	// Construct full table name using WordPress table prefix (e.g., wp_wpp_brokers)
	$table_name = $wpdb->prefix . 'wpp_brokers';

	// Prepare a safe SQL query using %d placeholder for integer $broker_id
	// This prevents SQL injection attacks
	$query = $wpdb->prepare(
		"SELECT * FROM $table_name WHERE id = %d",
		$broker_id
	);

	// Execute the query and fetch a single row as an associative array
	// ARRAY_A ensures we get keys like 'brok_brokerage_name' instead of numeric indices
	$broker = $wpdb->get_row( $query, ARRAY_A );

	// Return the broker data if found, otherwise return false
	return $broker ? $broker : false;
}


/**
 * Get all brokers from the 'wpp_brokers' table and return as an associative array.
 *
 * This function retrieves all records from the custom WordPress database table
 * `{$wpdb->prefix}wpp_brokers`, specifically selecting the `id` and `brok_brokerage_name`
 * fields. It returns a simple associative array where the key is the broker ID and
 * the value is the brokerage name.
 *
 * @package     WP_Panda
 * @subpackage  Brokers
 * @author      WP_Panda <panda@wp-panda.pro>
 * @copyright   2025 WP_Panda. All rights reserved.
 * @license     GNU/GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @version     1.0.0
 *
 * @link https://developer.wordpress.org/reference/classes/wpdb/         Official $wpdb documentation
 * @link https://developer.wordpress.org/reference/functions/wpdb-prepare/ $wpdb::prepare() reference
 * @link https://developer.wordpress.org/reference/classes/wpdb/get_results/ $wpdb::get_results() reference
 * @link https://www.php.net/manual/en/language.types.array.php           PHP: Arrays
 * @link https://www.php.net/manual/en/control-structures.foreach.php     PHP: foreach
 *
 * @return array<string|int, string> Associative array in the format [ ID => 'Brokerage Name' ].
 *                                   Returns empty array if no brokers found or on error.
 *
 * @example
 *     $brokers = get_all_brokers_as_array();
 *     foreach ( $brokers as $id => $name ) {
 *         echo "ID: $id => Name: $name\n";
 *     }
 *
 * @example (Check if empty)
 *     $brokers = get_all_brokers_as_array();
 *     if ( empty( $brokers ) ) {
 *         echo 'No brokers found.';
 *     } else {
 *         echo 'Found ' . count( $brokers ) . ' brokers.';
 *     }
 *
 * @example (Use in a dropdown select)
 *     $brokers = get_all_brokers_as_array();
 *     echo '<select name="broker_id">';
 *     foreach ( $brokers as $id => $name ) {
 *         printf( '<option value="%d">%s</option>', esc_attr( $id ), esc_html( $name ) );
 *     }
 *     echo '</select>';
 */
function get_all_brokers_as_array() {
	// Access the global $wpdb object to interact with the WordPress database.
	// @see https://developer.wordpress.org/reference/classes/wpdb/
	global $wpdb;

	// Construct the full table name using WordPress's dynamic prefix.
	// This ensures compatibility even if the site uses a custom table prefix (e.g., 'wp2_', 'my_', etc.).
	// The original table name is 'wpp_brokers', so with prefix 'etgfp_' it becomes 'etgfp_wpp_brokers'.
	// $wpdb->prefix is defined in wp-config.php and defaults to 'wp_' unless changed.
	// @see https://developer.wordpress.org/dbase/wpdb/#table-names
	$table_name = $wpdb->prefix . 'wpp_brokers';

	// Prepare the SQL query to fetch only the required fields: `id` and `brok_brokerage_name`.
	// We use $wpdb->prepare() even though there's no dynamic input to follow best practices
	// and ensure the query is properly formatted. It also helps prevent potential injection
	// if the function is later extended.
	// Since no user input is involved, we could use a raw query, but prepare() is safer by convention.
	// @see https://developer.wordpress.org/reference/functions/wpdb-prepare/
	$query = $wpdb->prepare( "SELECT id, brok_brokerage_name FROM {$table_name}" );

	// Execute the query and retrieve results as an associative array (ARRAY_A).
	// Each row will be an associative array with keys 'id' and 'brok_brokerage_name'.
	// If no rows are found, $results will be empty (null or empty array).
	// @see https://developer.wordpress.org/reference/classes/wpdb/get_results/
	$results = $wpdb->get_results( $query, ARRAY_A );

	// Initialize an empty array to store the final key-value pairs.
	$brokers = array();

	// Check if any results were returned before attempting to loop.
	// This prevents PHP warnings when trying to iterate over null or non-array values.
	if ( ! empty( $results ) && is_array( $results ) ) {
		// Loop through each result row and map 'id' to 'brok_brokerage_name'.
		// This creates a clean associative array: [ 1 => 'Broker One', 2 => 'Broker Two', ... ]
		foreach ( $results as $row ) {
			// Ensure both keys exist in the row to avoid PHP notices.
			// This is defensive programming in case schema changes or malformed data exists.
			if ( isset( $row['id'], $row['brok_brokerage_name'] ) ) {
				$brokers[ $row['id'] ] = $row['brok_brokerage_name'];
			}
		}
	}

	// Return the formatted array.
	// If no brokers were found, this returns an empty array, which is safe and expected.
	return $brokers;
}
