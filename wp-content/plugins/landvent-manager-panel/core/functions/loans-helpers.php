<?php
defined( 'ABSPATH' ) || exit;


/**
 * Get a human-readable "time ago" string for the loan's closing date, or a dash if not set.
 *
 * This function retrieves loan data using the helper function `wpp_get_loan_data_r()`,
 * checks for the existence of the 'closing_date' field, and formats it using `wpp_time_ago()`
 * to display a friendly relative time (e.g., "3 days ago", "1 month ago").
 *
 * If the loan has no closing date, or the date is invalid, it returns a dash ('—') as a placeholder.
 *
 * @param int $loan_id The unique identifier of the loan.
 *
 * @return string Formatted "time ago" string if closing_date exists and valid; otherwise, '—'.
 *
 * @package     WP_Panda
 * @subpackage  Loans
 * @author      WP_Panda <panda@wp-panda.pro>
 * @copyright   2025 WP_Panda. All rights reserved.
 * @license     GNU/GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @version     1.0.0
 *
 * @link https://www.php.net/manual/en/function.is-array.php              PHP: is_array()
 * @link https://www.php.net/manual/en/function.is-string.php             PHP: is_string()
 * @link https://www.php.net/manual/en/function.empty.php                 PHP: empty()
 * @link https://www.php.net/manual/en/language.types.string.php          PHP: String type
 * @link https://developer.wordpress.org/reference/functions/mysql2date/  mysql2date() (if used internally by wpp_time_ago)
 *
 * @uses wpp_get_loan_data_r( int $loan_id ) array Returns full loan data as an associative array.
 *       Must be defined in your plugin or theme.
 *
 * @uses wpp_time_ago( string $date ) string Formats a date into a "time ago" string.
 *       Example: "5 minutes ago", "2 weeks ago". Expected to accept MySQL datetime format.
 *
 * @example
 *     echo get_closing_date_display( 123 );
 *     // Output: "3 days ago" or "—"
 *
 * @example (In admin table or frontend list)
 *     $output = sprintf(
 *         'Closing: %s',
 *         get_closing_date_display( $loan_id )
 *     );
 *     echo esc_html( $output );
 */
function get_closing_date_display( $loan_id ) {
	// Ensure $loan_id is a valid integer
	if ( ! is_numeric( $loan_id ) || $loan_id < 1 ) {
		error_log( 'Invalid loan_id provided to get_closing_date_display: ' . $loan_id );

		return '—';
	}

	$loan_id = (int) $loan_id;

	// Step 1: Fetch loan data using the external function
	// This function should return an associative array with loan details
	$loan_data = wpp_get_loan_data_r( $loan_id );

	// Step 2: Validate that we received valid data
	if ( ! is_array( $loan_data ) || empty( $loan_data ) ) {
		error_log( "No loan data returned for loan ID: {$loan_id}" );

		return '—';
	}

	// Step 3: Check if 'closing_date' key exists and has a non-empty value
	if ( ! isset( $loan_data['closing_date'] ) || empty( $loan_data['closing_date'] ) ) {
		// No closing date set — return dash as placeholder
		return '—';
	}

	$closing_date = $loan_data['closing_date'];

	// Optional: Validate format (basic check for non-empty string that looks like a date)
	if ( ! is_string( $closing_date ) && ! ( is_object( $closing_date ) && method_exists( $closing_date, '__toString' ) ) ) {
		error_log( "Invalid closing_date format for loan ID: {$loan_id} (type: " . gettype( $closing_date ) . ")" );

		return '—';
	}

	$closing_date = (string) $closing_date;

	// Validate that it's a valid MySQL-style datetime: YYYY-MM-DD HH:MM:SS
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $closing_date ) ) {
		error_log( "Invalid date format for closing_date (loan ID: {$loan_id}): {$closing_date}" );

		return '—';
	}

	// Step 4: Format the date using the helper function wpp_time_ago()
	// This function is expected to accept a MySQL datetime string and return a human-readable string


	$formatted_time = date_i18n( $closing_date );

	// Ensure the result is a non-empty string
	return ! empty( $formatted_time ) ? $formatted_time : '—';
}