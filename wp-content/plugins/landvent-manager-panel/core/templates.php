<?php
defined('ABSPATH') || exit;
// Регистрация шаблонов в WordPress
function lmp_register_dashboard_template($templates) {
	$templates['dashboard-template.php'] = 'LandVent — Dashboard Template';
	$templates['single-loan.php'] = 'LandVent — Single Loan';
	return $templates;
}

add_filter('theme_page_templates', 'lmp_register_dashboard_template');

// Загрузка кастомных шаблонов
function lmp_load_custom_template($template) {
	$current_template = get_post_meta(get_the_ID(), '_wp_page_template', true);

	if ($current_template === 'dashboard-template.php') {
		$plugin_template = WPP_LOAN_MANAGER_PATH . 'public/templates/dashboard-template.php';
	} elseif ($current_template === 'single-loan.php') {
		$plugin_template = WPP_LOAN_MANAGER_PATH . 'public/templates/single-loan.php';
	} else {
		return $template;
	}

	if (file_exists($plugin_template)) {
		return $plugin_template;
	}

	return $template;
}

add_filter('template_include', 'lmp_load_custom_template', 99);