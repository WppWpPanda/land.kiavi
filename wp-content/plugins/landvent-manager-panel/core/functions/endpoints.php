<?php
if (!defined('ABSPATH')) exit;

class LandVent_Manager_Endpoints {
	private static $instance = null;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action('init', array($this, 'add_endpoints'));
		add_filter('query_vars', array($this, 'add_query_vars'));
		add_action('template_redirect', array($this, 'handle_requests'));
	}

	public function add_endpoints() {
		// Главная конечная точка
		add_rewrite_rule(
			'^manager-dashboard/?$',
			'index.php?manager_dashboard=main',
			'top'
		);

		// Дочерние конечные точки
		add_rewrite_rule(
			'^manager-dashboard/(law-firms-clerks|title-companies|brokers|appraisers)/?$',
			'index.php?manager_dashboard=$matches[1]',
			'top'
		);

		// Конечная точка loan с параметром ID
		add_rewrite_rule(
			'^manager-dashboard/loan/([0-9]+)/?$',
			'index.php?manager_dashboard=loan&loan_id=$matches[1]',
			'top'
		);
	}

	public function add_query_vars($vars) {
		$vars[] = 'manager_dashboard';
		$vars[] = 'loan_id';
		return $vars;
	}

	public function handle_requests() {
		global $wp_query;

		if ($dashboard_page = $wp_query->get('manager_dashboard')) {
			// Проверка прав доступа
			if (!current_user_can('manage_options')) {
				wp_die(__('You do not have sufficient permissions to access this page.'));
			}

			// Загружаем соответствующий шаблон
			$this->load_template($dashboard_page);
			exit;
		}
	}
	public function load_template($template_name) {
		global $wp_query;

		$loan_id = $wp_query->get('loan_id', 0);

		// 1. Проверяем тему пользователя
		$theme_template = locate_template(array(
			"landvent-manager/{$template_name}.php",
			"landvent-manager-dashboard/{$template_name}.php"
		));

		if ($theme_template) {
			include $theme_template;
			return;
		}

		// 2. Проверяем шаблоны плагина
		$plugin_template = WPP_LOAN_MANAGER_PATH . "public/templates/manager-dashboard/{$template_name}.php";

		if (file_exists($plugin_template)) {
			// Передаем необходимые переменные
			set_query_var('loan_id', $loan_id);
			set_query_var('dashboard_page', $template_name);

			include $plugin_template;
			return;
		}

		// 3. Фолбэк шаблон
		$this->fallback_template($template_name);
	}


	public function fallback_template($template_name) {
		get_header();

		echo '<div class="landvent-manager-container">';

		switch ($template_name) {
			case 'main':
				echo '<h1>Manager Dashboard</h1>';
				echo '<p>Welcome to the Manager Dashboard</p>';
				break;

			case 'law-firms-clerks':
				echo '<h1>Law Firms & Clerks</h1>';
				break;

			// ... другие случаи ...

			default:
				echo '<h1>Page Not Found</h1>';
				break;
		}

		echo '</div>';

		get_footer();
	}
}

// Инициализация
LandVent_Manager_Endpoints::get_instance();