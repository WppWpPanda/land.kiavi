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