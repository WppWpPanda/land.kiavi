<?php
/*
Plugin Name: LandVent Manager Panel
Plugin URI: https://example.com
Description: Простая панель управления для менеджеров с шаблоном дашборда.
Version: 1.0
Author: WP Panda
Author URI: https://wppanda.com
License: GPL2
*/

// Защита от прямого доступа
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Определяем константы плагина
if ( ! defined( 'WPP_LOAN_MANAGER_PATH' ) ) {
	define( 'WPP_LOAN_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'WPP_LOAN_MANAGER_URL' ) ) {
	define( 'WPP_LOAN_MANAGER_URL', plugin_dir_url( __FILE__ ) );
}

require_once 'core/functions/navs.php';
require_once 'core/functions/trello-columns.php';
require_once 'core/functions/init.php';

function wpp_enqueue_Loan_styles() {

	wp_enqueue_style(
		'landvent-manager-panel',
		WPP_LOAN_MANAGER_URL . 'assets/css/landvent-manager-panel.css',
		[],
		file_exists( WPP_LOAN_MANAGER_PATH . 'assets/css/landvent-manager-panel.css' )
			? filemtime( WPP_LOAN_MANAGER_PATH . 'assets/css/landvent-manager-panel.css' )
			: time()
	);

	wp_enqueue_style(
		'trello-style',
		WPP_LOAN_MANAGER_URL . 'assets/css/trello-style.css',
	[],
		file_exists( WPP_LOAN_MANAGER_PATH . 'assets/css/trello-style.css' )
			? filemtime( WPP_LOAN_MANAGER_PATH . 'assets/css/trello-style.css' )
			: time()

	);

	wp_enqueue_script(
		'trello-script',
		WPP_LOAN_MANAGER_URL . 'assets/js/trello-script.js',
		array('jquery', 'jquery-ui-sortable'),
		file_exists( WPP_LOAN_MANAGER_PATH . 'assets/js/trello-script.js' )
			? filemtime( WPP_LOAN_MANAGER_PATH . 'assets/js/trello-script.js' )
			: time(),
		true
	);

	wp_enqueue_script(
		'trello-script-ft',
		WPP_LOAN_MANAGER_URL . 'assets/js/frontend.js',
		array('jquery'),
		file_exists( WPP_LOAN_MANAGER_PATH . 'assets/js/frontend.js' )
			? filemtime( WPP_LOAN_MANAGER_PATH . 'assets/js/frontend.js' )
			: time(),
		true
	);


    // Локализация для AJAX
    wp_localize_script('trello-script', 'trello_vars', array(
	    'ajax_url' => admin_url('admin-ajax.php'),
	    'nonce' => wp_create_nonce('trello_nonce')
    ));


}

add_action( 'wp_enqueue_scripts', 'wpp_enqueue_Loan_styles' );

// Подключаем шаблон
//require_once plugin_dir_path(__FILE__) . 'templates/dashboard-template.php';

// Регистрация шаблона в WordPress (чтобы он появился в списке выбора)
function lmp_register_dashboard_template( $templates ) {
	$templates['dashboard-template.php'] = 'LandVent — Dashboard Template';
	$templates['single-loan.php']        = 'LandVent — Single Loan';

	return $templates;
}

add_filter( 'theme_page_templates', 'lmp_register_dashboard_template' );

// Указываем WordPress, где искать шаблон
function lmp_load_custom_template( $template ) {

	$current_template = get_post_meta( get_the_ID(), '_wp_page_template', true );
	$plugin_template  = plugin_dir_path( __FILE__ ) . 'xxx.php';

	if ( $current_template === 'dashboard-template.php' ) {
		$plugin_template = plugin_dir_path( __FILE__ ) . 'public/templates/dashboard-template.php';


	} elseif ( $current_template === 'single-loan.php' ) {
		$plugin_template = plugin_dir_path( __FILE__ ) . 'public/templates/single-loan.php';
	}

	if ( file_exists( $plugin_template ) ) {
		return $plugin_template;
	}

	return $template;
}

add_filter( 'template_include', 'lmp_load_custom_template', 99 );

require_once 'core/config/term-sheet-details.php';
require_once 'core/config/property-details.php';
require_once 'core/config/applicant-info.php';
require_once 'core/config/additional-reserve.php';
require_once 'core/config/attorney.php';
require_once 'core/config/conditions.php';
require_once 'core/config/documents.php';
require_once 'core/config/fees.php';
require_once 'core/config/investors.php';
require_once 'core/config/payments.php';
require_once 'core/config/required-documents.php';
require_once 'core/config/title-company.php';
require_once 'core/functions/endpoints.php';



function trello_create_db_tables() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	// Текущая версия базы данных
	$current_trello_version = '1.0';
	$current_loans_version = '1.0';

	// Получаем сохранённые версии
	$installed_trello_version = get_option('trello_db_version', '0');
	$installed_loans_version = get_option('loans_db_version', '0');

	// SQL для таблицы Trello
	$table_trello = $wpdb->prefix . 'wpp_trello_columns';
	$sql_trello = "CREATE TABLE $table_trello (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        column_order int(11) NOT NULL DEFAULT 0,
        card_ids text NOT NULL DEFAULT '[]',
        PRIMARY KEY  (id)
    ) $charset_collate;";

	// SQL для таблицы Loans
	$table_loans = $wpdb->prefix . 'wpp_loans_full_data';
	$sql_loans = "CREATE TABLE $table_loans (
        loan_id VARCHAR(100) NOT NULL,
        change_time DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        loan_data LONGTEXT NOT NULL,
        PRIMARY KEY (loan_id)
    ) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	// Создаём/обновляем таблицу Trello если нужно
	if (version_compare($installed_trello_version, $current_trello_version, '<')) {
		dbDelta($sql_trello);
		update_option('trello_db_version', $current_trello_version);
	}

	// Создаём/обновляем таблицу Loans если нужно
	if (version_compare($installed_loans_version, $current_loans_version, '<')) {
		dbDelta($sql_loans);
		update_option('loans_db_version', $current_loans_version);
	}

	LandVent_Manager_Endpoints::get_instance()->add_endpoints();
	flush_rewrite_rules();
}

// Хук активации
register_activation_hook(__FILE__, 'trello_create_db_tables');

// Хук для проверки обновлений при каждом запуске (опционально)
add_action('plugins_loaded', 'check_db_updates');
function check_db_updates() {
	$current_trello_version = '1.0';
	$current_loans_version = '1.0';

	if (version_compare(get_option('trello_db_version', '0'), $current_trello_version, '<') ||
	    version_compare(get_option('loans_db_version', '0'), $current_loans_version, '<')) {
		trello_create_db_tables();
	}


}
//register_activation_hook(__FILE__, 'trello_create_db_tables');