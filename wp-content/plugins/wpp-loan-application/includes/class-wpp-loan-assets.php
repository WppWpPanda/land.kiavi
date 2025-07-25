<?php
if (!defined('ABSPATH')) {
	exit;
}

class WPP_Loan_Assets {

	public static function enqueue_frontend() {
		wp_enqueue_style(
			'wpp-loan-css',
			WPP_LOAN_URL . 'assets/css/frontend.css',
			[],
			file_exists(WPP_LOAN_PATH . 'assets/css/frontend.css')
				? filemtime(WPP_LOAN_PATH . 'assets/css/frontend.css')
				: time()
		);

		wp_enqueue_script(
			'wpp-loan',
			WPP_LOAN_URL . 'assets/js/frontend.js',
			['jquery'],
			file_exists(WPP_LOAN_PATH . 'assets/js/frontend.js')
				? filemtime(WPP_LOAN_PATH . 'assets/js/frontend.js')
				: time(),
			true
		);

		$steps = json_decode(WPP_LOAN_STEPS, true);
		$current_path = self::get_current_page_path();

		foreach ($steps as $step_num => $config) {
			if ($config['slug'] === $current_path) {
				$name = $step_num;
				$next_step_num = $step_num + 1;
				$next_step_slug = $steps[$next_step_num]['slug'] ?? 'complete';

				wp_enqueue_script(
					'step-' . $name,
					WPP_LOAN_URL . 'assets/js/step-' . $name . '.js',
					['wpp-loan'],
					file_exists(WPP_LOAN_PATH . 'assets/js/step-' . $name . '.js')
						? filemtime(WPP_LOAN_PATH . 'assets/js/step-' . $name . '.js')
						: time(),
					true
				);

				wp_localize_script('step-' . $name, 'wppLoanData', [
					'ajaxUrl' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('wpp_loan_step_nonce'),
					'currentStep' => $step_num,
					'nextStepSlug' => $next_step_slug,
					'nextStepNum' => $next_step_num
				]);
			}
		}
	}

	private static function get_current_page_path() {
		global $wp;
		return trim($wp->request, '/');
	}
}

add_action('wp_enqueue_scripts', ['WPP_Loan_Assets', 'enqueue_frontend']);