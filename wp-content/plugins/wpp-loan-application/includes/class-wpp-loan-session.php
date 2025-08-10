<?php
/**
 * Class: WPP_Loan_Session_Handler
 *
 * Manages multi-step form data persistence across sessions and database.
 *
 * This class provides a hybrid session system:
 * - Uses PHP sessions for fast access during active browsing
 * - Persists all data in a single database row per session
 * - Uses cookies to maintain session continuity across visits
 *
 * Data structure:
 * - One row per `session_id` in `loan_application_data` table
 * - `form_data` column stores all steps as JSON
 * - Example: { "1": { "field": "value" }, "2": { "field": "value" } }
 *
 * 🔗 References:
 * - {@see https://www.php.net/manual/en/function.session-start.php}     `session_start()`
 * - {@see https://www.php.net/manual/en/function.setcookie.php}         `setcookie()`
 * - {@see https://www.php.net/manual/en/function.json-encode.php}       `json_encode()`
 * - {@see https://www.php.net/manual/en/function.json-decode.php}       `json_decode()`
 * - {@see https://developer.wordpress.org/reference/classes/wpdb/}      `$wpdb`
 * - {@see https://developer.wordpress.org/reference/functions/sanitize_text_field/} `sanitize_text_field()`
 * - {@see https://developer.wordpress.org/reference/functions/uniqid/}  `uniqid()`
 * - {@see https://developer.wordpress.org/reference/functions/current_time/} `current_time()`
 *
 * 📁 File Location:
 * - Expected: `/wp-content/plugins/wpp-loan-application/includes/class-wpp-loan-session.php`
 *
 * 💡 Example Usage:
 * ```php
 * WPP_Loan_Session_Handler::save_step_data(1, ['name' => 'John']);
 * $data = WPP_Loan_Session_Handler::get_step_data(1);
 * ```
 *
 * ✅ Best Practices Applied:
 * - Cookie-based session ID persistence
 * - Fallback to database when PHP session is empty
 * - Secure data storage with JSON
 * - Proper cookie path and domain
 * - Error logging for header issues
 *
 * @since 1.0.0
 * @author WP_Panda <panda@wp-panda.pro>
 */
class WPP_Loan_Session_Handler {

	/**
	 * Key used to store data in PHP session superglobal.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private static $session_key = 'wpp_loan_data';

	/**
	 * Name of the cookie used to store session ID.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private const SESSION_COOKIE_NAME = 'wpp_loan_session';

	/**
	 * Retrieves or creates a unique session ID using a persistent cookie.
	 *
	 * If a cookie exists, returns its sanitized value.
	 * If not, generates a new `uniqid` prefixed with 'loan_sess_' and sets the cookie.
	 *
	 * @since 1.0.0
	 * @return string Unique session ID
	 *
	 * @uses sanitize_text_field() To sanitize cookie input
	 * @uses uniqid() To generate unique ID
	 * @uses setcookie() To persist session ID
	 *
	 * @example
	 * $session_id = self::get_session_id(); // e.g., "loan_sess_68950f708b2e75"
	 */
	public static function get_session_id() {
		// If cookie exists, return sanitized value
		if ( ! empty( $_COOKIE[ self::SESSION_COOKIE_NAME ] ) ) {
			return sanitize_text_field( $_COOKIE[ self::SESSION_COOKIE_NAME ] );
		}

		// Generate new session ID
		$new_session_id = uniqid( 'loan_sess_', true );

		// Set cookie: 7 days expiration, secure path/domain
		setcookie(
			self::SESSION_COOKIE_NAME,
			$new_session_id,
			time() + 604800, // 7 days
			COOKIEPATH,
			COOKIE_DOMAIN
		);

		return $new_session_id;
	}

	/**
	 * Starts the PHP session if not already active.
	 *
	 * Checks if headers have already been sent to prevent PHP warnings.
	 * Logs error if session cannot be started due to output.
	 *
	 * @since 1.0.0
	 * @return void
	 *
	 * @uses session_id() To check if session is already started
	 * @uses headers_sent() To prevent "headers already sent" error
	 * @uses error_log() To log session start issues
	 *
	 * @example
	 * self::start_session(); // Safe session start
	 */
	public static function start_session() {
		// Skip if session already started
		if ( session_id() ) {
			return;
		}

		// Prevent session start if output already sent
		if ( headers_sent( $file, $line ) ) {
			error_log( "⚠️ Headers already sent by {$file}:{$line}" );
			return;
		}

		// Start PHP session
		session_start();
	}

	/**
	 * Saves step data to both PHP session and database.
	 *
	 * Merges new data with existing form data in the database.
	 * Stores all steps in a single JSON column for atomicity.
	 *
	 * @since 1.0.0
	 * @param int|string $step Step identifier (e.g., 1, 'personal-info')
	 * @param array $data Form data to save
	 * @return bool|int Returns number of affected rows or false on failure
	 *
	 * @uses self::start_session() To ensure session is active
	 * @uses self::get_session_id() To get session identifier
	 * @uses $wpdb->get_row() To read existing data
	 * @uses $wpdb->update() / $wpdb->insert() To persist data
	 * @uses json_encode() To serialize data
	 *
	 * @example
	 * $result = self::save_step_data(1, ['name' => 'John', 'email' => 'john@example.com']);
	 * if ($result) { ... }
	 */
	public static function save_step_data( $step, $data ) {
		self::start_session();

		// Initialize session storage
		if ( ! isset( $_SESSION[ self::$session_key ] ) ) {
			$_SESSION[ self::$session_key ] = [];
		}

		// Save to PHP session
		$_SESSION[ self::$session_key ][ $step ] = $data;

		// Prepare database persistence
		global $wpdb;
		$table_name = $wpdb->prefix . 'loan_application_data';
		$session_id = self::get_session_id();

		// Retrieve existing form data
		$existing = $wpdb->get_row(
			$wpdb->prepare( "SELECT form_data FROM $table_name WHERE session_id = %s", $session_id ),
			ARRAY_A
		);

		// Decode existing data or initialize empty array
		$all_data = [];
		if ( $existing && ! empty( $existing['form_data'] ) ) {
			$all_data = json_decode( $existing['form_data'], true );
		}

		// Merge new step data
		$all_data[ $step ] = $data;

		// Build database arguments
		$args = [
			'session_id' => $session_id,
			'form_data'  => json_encode( $all_data ),
			'updated_at' => current_time( 'mysql' ),
			'user_id'    => 0, // Anonymous by default
		];

		// Insert or update
		if ( $existing ) {
			$result = $wpdb->update(
				$table_name,
				$args,
				[ 'session_id' => $session_id ]
			);
		} else {
			$args['created_at'] = current_time( 'mysql' );
			$result             = $wpdb->insert( $table_name, $args );
		}

		return ! empty( $result ) ? $result : false;
	}

	/**
	 * Retrieves data for a specific step.
	 *
	 * Checks PHP session first, then falls back to database.
	 *
	 * @since 1.0.0
	 * @param int|string $step Step identifier
	 * @return array|false Step data or false if not found
	 *
	 * @uses self::start_session() To ensure session is active
	 * @uses $wpdb->get_row() To query database
	 * @uses json_decode() To unserialize data
	 *
	 * @example
	 * $data = self::get_step_data(1);
	 * if ($data) { echo $data['name']; }
	 */
	public static function get_step_data( $step ) {
		self::start_session();

		// Check PHP session first
		if ( isset( $_SESSION[ self::$session_key ][ $step ] ) ) {
			return $_SESSION[ self::$session_key ][ $step ];
		}

		// Fallback to database
		global $wpdb;
		$table_name = $wpdb->prefix . 'loan_application_data';
		$session_id = self::get_session_id();

		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT form_data FROM $table_name WHERE session_id = %s", $session_id ),
			ARRAY_A
		);

		if ( ! $row || empty( $row['form_data'] ) ) {
			return false;
		}

		$all_data = json_decode( $row['form_data'], true );

		return isset( $all_data[ $step ] ) ? $all_data[ $step ] : false;
	}

	/**
	 * Retrieves all form data from the current session.
	 *
	 * Tries PHP session first, then database. Does not cache to session.
	 *
	 * @since 1.0.0
	 * @return array|false All step data or false if empty
	 *
	 * @uses self::start_session() To ensure session is active
	 * @uses $wpdb->get_row() To query database
	 * @uses json_decode() To unserialize data
	 *
	 * @example
	 * $all = self::get_all_data_from_session();
	 * foreach ($all as $step => $data) { ... }
	 */
	public static function get_all_data_from_session() {
		self::start_session();

		// Try PHP session first
		if ( ! empty( $_SESSION[ self::$session_key ] ) ) {
			return $_SESSION[ self::$session_key ];
		}

		// Fallback to database
		global $wpdb;
		$table_name = $wpdb->prefix . 'loan_application_data';
		$session_id = self::get_session_id();

		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT form_data FROM $table_name WHERE session_id = %s", $session_id ),
			ARRAY_A
		);

		if ( ! $row || empty( $row['form_data'] ) ) {
			return false;
		}

		$all_data = json_decode( $row['form_data'], true );

		if ( ! is_array( $all_data ) ) {
			return false;
		}

		return $all_data;
	}

	/**
	 * Retrieves a specific field value from a step.
	 *
	 * Assumes data is stored in `formData` sub-array.
	 *
	 * @since 1.0.0
	 * @param int|string $step Step identifier
	 * @param string $field_name Field key
	 * @return mixed|null Field value or null if not set
	 *
	 * @uses self::get_step_data() To get step data
	 *
	 * @example
	 * $email = self::get_field_value(1, 'email');
	 */
	public static function get_field_value( $step, $field_name ) {
		$step_data = self::get_step_data( $step );

		return $step_data && isset( $step_data['formData'][ $field_name ] )
			? $step_data['formData'][ $field_name ]
			: null;
	}

	/**
	 * Retrieves all form data (alias of get_all_data_from_session).
	 *
	 * @since 1.0.0
	 * @return array|false All step data
	 *
	 * @uses self::get_all_data_from_session() Delegates logic
	 */
	public static function get_all_data() {
		self::start_session();

		if ( ! empty( $_SESSION[ self::$session_key ] ) ) {
			return $_SESSION[ self::$session_key ];
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'loan_application_data';
		$session_id = self::get_session_id();

		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT form_data FROM $table_name WHERE session_id = %s", $session_id ),
			ARRAY_A
		);

		if ( ! $row || empty( $row['form_data'] ) ) {
			return false;
		}

		$all_data = json_decode( $row['form_data'], true );

		// Cache in PHP session for performance
		$_SESSION[ self::$session_key ] = $all_data;

		return $all_data;
	}

	/**
	 * Clears all session data: removes from database and deletes cookie.
	 *
	 * Does not call start_session() to avoid unnecessary session start.
	 *
	 * @since 1.0.0
	 * @return void
	 *
	 * @uses $wpdb->delete() To remove from database
	 * @uses setcookie() To expire session cookie
	 *
	 * @example
	 * self::clear_all(); // Reset entire loan session
	 */
	public static function clear_all() {
		// Clear PHP session data
		unset( $_SESSION[ self::$session_key ] );

		// Remove from database
		global $wpdb;
		$table_name = $wpdb->prefix . 'loan_application_data';
		$session_id = self::get_session_id();

		$wpdb->delete( $table_name, [ 'session_id' => $session_id ] );

		// Expire cookie
		if ( isset( $_COOKIE[ self::SESSION_COOKIE_NAME ] ) ) {
			setcookie( self::SESSION_COOKIE_NAME, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
			unset( $_COOKIE[ self::SESSION_COOKIE_NAME ] );
		}
	}

	/**
	 * Checks if data exists for a given step.
	 *
	 * @since 1.0.0
	 * @param int|string $step Step identifier
	 * @return bool True if step data exists
	 *
	 * @uses self::has_step_data() To check session first
	 * @uses self::get_all_data() As fallback
	 */
	public static function has_step_data( $step ) {
		self::start_session();

		if ( isset( $_SESSION[ self::$session_key ][ $step ] ) ) {
			return true;
		}

		$all_data = self::get_all_data();

		return is_array( $all_data ) && isset( $all_data[ $step ] );
	}
}