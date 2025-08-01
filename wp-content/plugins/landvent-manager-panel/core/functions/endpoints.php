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
		add_rewrite_endpoint('manager-dashboard', EP_ROOT);

		// Дочерние конечные точки
		$endpoints = array(
			'law-firms-clerks'    => 'Law Firms & Clerks',
			'title-companies'    => 'Title Companies',
			'brokers'           => 'Brokers',
			'appraisers'        => 'Appraisers'
		);

		foreach ($endpoints as $slug => $title) {
			add_rewrite_rule(
				"^manager-dashboard/{$slug}/?",
				"index.php?manager_dashboard=1&sub_page={$slug}",
				'top'
			);
		}

		// Конечная точка loan с параметром ID
		add_rewrite_rule(
			'^manager-dashboard/loan/([0-9]+)/?',
			'index.php?manager_dashboard=1&sub_page=loan&loan_id=$matches[1]',
			'top'
		);
	}

	public function add_query_vars($vars) {
		$vars[] = 'manager_dashboard';
		$vars[] = 'sub_page';
		$vars[] = 'loan_id';
		return $vars;
	}

	public function handle_requests() {
		global $wp_query;

		if (isset($wp_query->query_vars['manager_dashboard'])) {
			// Проверка прав
			if (!current_user_can('manage_options')) {
				wp_die(__('You do not have sufficient permissions to access this page.'));
			}

			// Определяем какой шаблон загружать
			if (empty($wp_query->query_vars['sub_page'])) {
				$this->render_main_dashboard();
			} else {
				$this->load_template();
			}

			exit;
		}
	}
	public function load_template() {
		global $wp_query;

		$sub_page = isset($wp_query->query_vars['sub_page']) ? $wp_query->query_vars['sub_page'] : '';
		$loan_id = isset($wp_query->query_vars['loan_id']) ? $wp_query->query_vars['loan_id'] : 0;

		// Определяем базовое имя шаблона
		$template_name = empty($sub_page) ? 'main' : $sub_page;

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
		$plugin_templates_dir = WPP_LOAN_MANAGER_PATH . 'public/templates/manager-dashboard/';
		$plugin_template = $plugin_templates_dir . $template_name . '.php';

		if (file_exists($plugin_template)) {
			// Передаем необходимые переменные в шаблон
			set_query_var('loan_id', $loan_id);
			set_query_var('sub_page', $sub_page);

			include $plugin_template;
			return;
		}

		// 3. Фолбэк шаблон
		$this->fallback_template();
	}


	public function render_main_dashboard() {
		// Получаем необходимые данные
		$stats = array(
			'active_loans' => $this->get_active_loans_count(),
			'pending_tasks' => $this->get_pending_tasks_count(),
			'completed_today' => $this->get_completed_today_count()
		);

		// Устанавливаем переменные для шаблона
		set_query_var('dashboard_stats', $stats);
		set_query_var('recent_activities', $this->get_recent_activities());

		// Загружаем шаблон
		$this->load_template();
	}


	public function fallback_template() {
		global $wp_query;

		$sub_page = isset($wp_query->query_vars['sub_page']) ? $wp_query->query_vars['sub_page'] : '';
		$loan_id = isset($wp_query->query_vars['loan_id']) ? $wp_query->query_vars['loan_id'] : 0;

		get_header();

		echo '<div class="landvent-manager-container">';

		switch ($sub_page) {
			case 'law-firms-clerks':
				echo '<h1>Law Firms & Clerks</h1>';
				// Ваш контент здесь
				break;

			case 'title-companies':
				echo '<h1>Title Companies</h1>';
				// Ваш контент здесь
				break;

			case 'brokers':
				echo '<h1>Brokers</h1>';
				// Ваш контент здесь
				break;

			case 'appraisers':
				echo '<h1>Appraisers</h1>';
				// Ваш контент здесь
				break;

			case 'loan':
				echo '<h1>Loan Details</h1>';
				echo '<p>Loan ID: ' . esc_html($loan_id) . '</p>';
				// Ваш контент здесь
				break;

			default:
				echo '<h1>Manager Dashboard</h1>';
				echo '<p>Welcome to the Manager Dashboard</p>';
				break;
		}

		echo '</div>';

		get_footer();
	}
}

// Инициализация
LandVent_Manager_Endpoints::get_instance();