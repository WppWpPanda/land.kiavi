<?php
/**
 * Class: WPP_Loan_Assets
 *
 * Manages the enqueuing of frontend CSS and JavaScript assets for the loan application process.
 *
 * This class:
 * - Enqueues a shared frontend stylesheet and script
 * - Dynamically enqueues step-specific JavaScript based on the current page URL
 * - Localizes data (AJAX URL, nonce, step navigation) for use in JavaScript
 * - Uses file modification time for cache busting
 *
 *  References:
 * - {@see https://developer.wordpress.org/reference/functions/wp_enqueue_style/}   `wp_enqueue_style()`
 * - {@see https://developer.wordpress.org/reference/functions/wp_enqueue_script/}  `wp_enqueue_script()`
 * - {@see https://developer.wordpress.org/reference/functions/wp_localize_script/} `wp_localize_script()`
 * - {@see https://developer.wordpress.org/reference/functions/admin_url/}          `admin_url()`
 * - {@see https://developer.wordpress.org/reference/functions/wp_create_nonce/}    `wp_create_nonce()`
 * - {@see https://developer.wordpress.org/reference/functions/file_exists/}       `file_exists()`
 * - {@see https://www.php.net/manual/en/function.filemtime.php}                   `filemtime()`
 *
 *  File Location:
 * - Expected: `/wp-content/plugins/wpp-loan-application/includes/class-wpp-loan-assets.php`
 *
 *  Example Usage:
 * ```php
 * add_action('wp_enqueue_scripts', ['WPP_Loan_Assets', 'enqueue_frontend']);
 * ```
 *
 *  Best Practices Applied:
 * - Cache-busting via `filemtime()` fallback to `time()`
 * - Footer loading for performance
 * - Secure localization with nonce
 * - Modular step-based JS loading
 * - Graceful fallback for missing files
 *
 * @since 1.0.0
 * @author WP_Panda <panda@wp-panda.pro>
 */

defined( 'ABSPATH' ) || exit;

class WPP_Loan_Assets {

	/**
	 * Enqueues the main and step-specific frontend assets.
	 *
	 * This method:
	 * 1. Enqueues the shared frontend CSS and JS
	 * 2. Determines the current page path
	 * 3. Matches it to a step in `WPP_LOAN_STEPS`
	 * 4. Enqueues the corresponding step JS file
	 * 5. Localizes AJAX and navigation data for the step
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 * @static
	 * @uses wp_enqueue_style()        To load CSS
	 * @uses wp_enqueue_script()       To load JS
	 * @uses wp_localize_script()      To pass PHP data to JS
	 * @uses admin_url()               To get AJAX endpoint
	 * @uses wp_create_nonce()         To generate security token
	 * @uses file_exists()             To check asset existence
	 * @uses filemtime()               To get file version
	 * @uses self::get_current_page_path() To get current URL path
	 *
	 * @example
	 * If current URL is `/apply/step-1`, and WPP_LOAN_STEPS defines:
	 * {
	 *   "1": { "slug": "step-1" },
	 *   "2": { "slug": "step-2" }
	 * }
	 * Then:
	 * - Loads: step-1.js
	 * - Localizes: currentStep=1, nextStepSlug="step-2"
	 */
	public static function enqueue_frontend() {
		$key = 'AIzaSyDJVaxW0jTvEuwO5kyql7XSEUxYH-whH8c';
		// === 1. Enqueue Shared Frontend Stylesheet ===
		wp_enqueue_style(
			'wpp-loan-css', // Handle
			WPP_LOAN_URL . 'assets/css/frontend.css', // Source
			[], // No dependencies
			file_exists( WPP_LOAN_PATH . 'assets/css/frontend.css' )
				? filemtime( WPP_LOAN_PATH . 'assets/css/frontend.css' ) // Version
				: time() // Fallback
		);

		// === 2. Enqueue Shared Frontend Script ===
		wp_enqueue_script(
			'places', // Handle
			'https://maps.googleapis.com/maps/api/js?key=' . $key . '&libraries=places', // Source
			[ 'jquery' ], // Depends on jQuery
			null, // Fallback
			array(
				'strategy' => 'async', // асинхронная загрузка
				'in_footer' => true // загрузка в подвале
			)

		);

		wp_enqueue_script(
			'wpp-loan', // Handle
			WPP_LOAN_URL . 'assets/js/frontend.js', // Source
			[ 'jquery' ], // Depends on jQuery
			file_exists( WPP_LOAN_PATH . 'assets/js/frontend.js' )
				? filemtime( WPP_LOAN_PATH . 'assets/js/frontend.js' ) // Version
				: time(), // Fallback
			true // Load in footer
		);

		// === 3. Parse Loan Steps Configuration ===
		$steps = json_decode( WPP_LOAN_STEPS, true );
		if ( ! is_array( $steps ) ) {
			return; // Invalid steps config
		}

		// === 4. Get Current Page Path (e.g., "step-1") ===
		$current_path = self::get_current_page_path();

		// === 5. Find Matching Step and Enqueue Step Script ===
		foreach ( $steps as $step_num => $config ) {
			if ( ! isset( $config['slug'] ) || $config['slug'] !== $current_path ) {
				continue;
			}

			// Construct step script name (e.g., "step-1")
			$name = $step_num;

			// Determine next step
			$next_step_num  = $step_num + 1;
			$next_step_slug = $steps[ $next_step_num ]['slug'] ?? 'complete';

			// === 6. Enqueue Step-Specific JavaScript ===
			wp_enqueue_script(
				'step-' . $name, // Handle
				WPP_LOAN_URL . 'assets/js/step-' . $name . '.js', // Source
				[ 'wpp-loan', 'places' ], // Depends on shared script
				file_exists( WPP_LOAN_PATH . 'assets/js/step-' . $name . '.js' )
					? filemtime( WPP_LOAN_PATH . 'assets/js/step-' . $name . '.js' ) // Version
					: time(), // Fallback
				true // Load in footer
			);

			// === 7. Localize Data for JavaScript ===
			// Makes PHP variables available in JS via `wppLoanData`
			wp_localize_script( 'step-' . $name, 'wppLoanData', [
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ), // AJAX endpoint
				'nonce'        => wp_create_nonce( 'wpp_loan_step_nonce' ), // Security
				'currentStep'  => (int) $step_num, // Current step number
				'nextStepSlug' => $next_step_slug, // Next step URL
				'nextStepNum'  => $next_step_num, // Next step number
			] );

			// Only enqueue one step script per page
			break;
		}
	}

	/**
	 * Get the current page path from the URL.
	 *
	 * Strips leading/trailing slashes and returns the clean path.
	 * Example: "/apply/step-1" → "step-1"
	 *
	 * @return string Cleaned page path
	 *
	 * @global WP $wp WordPress main object
	 * @since 1.0.0
	 * @static
	 * @access private
	 *
	 * @example
	 * http://example.com/apply/step-1 → returns "step-1"
	 * http://example.com/ → returns ""
	 */
	private static function get_current_page_path() {
		global $wp;

		return trim( $wp->request, '/' );
	}
}

// Hook into WordPress frontend asset loading
// 🔗 https://developer.wordpress.org/reference/hooks/wp_enqueue_scripts/
add_action( 'wp_enqueue_scripts', [ 'WPP_Loan_Assets', 'enqueue_frontend' ] );


// Then add filter
function modify_script_loading($tag, $handle) {
	if ('places' === $handle) {
		//return str_replace(' src', ' defer src', $tag);
		// Or for async:
		 return str_replace(' src', 'defer src', $tag);
	}
	return $tag;
}
//add_filter('script_loader_tag', 'modify_script_loading', 10, 2);