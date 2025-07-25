<?php
/**
 * Plugin Name: WPP Loan Application - Install Script
 *
 * @package WPP_Loan_Application
 * @subpackage Install
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * Создаёт таблицу wpp_loan_application_data при активации плагина
 */
/**
 * Создаёт таблицы для плагина при активации
 */
function wpp_create_loan_application_table() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$charset_collate = $wpdb->get_charset_collate();

	// Таблица заявок
	$table_name = $wpdb->prefix . 'loan_application_data';
	if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            session_id VARCHAR(255) NOT NULL,
            user_id BIGINT UNSIGNED DEFAULT 0,
            form_data LONGTEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY session_id (session_id)
        ) $charset_collate;";
		dbDelta($sql);
	}

	// Таблица гарантов
	$table_name = $wpdb->prefix . 'loan_guarantors';

	if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        guarantor_key VARCHAR(255) NOT NULL,
        first_name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL,
        suffix VARCHAR(50),
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY guarantor_key (guarantor_key)
    ) $charset_collate;";

		dbDelta($sql);
	}

	// Таблица связей гарантов и заявок
	$table_name = $wpdb->prefix . 'loan_guarantor_relations';
	if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            loan_id BIGINT UNSIGNED NOT NULL,
            guarantor_id BIGINT UNSIGNED NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY loan_guarantor_unique (loan_id, guarantor_id)
        ) $charset_collate;";
		dbDelta($sql);
	}

	// Сырые данные заявки
	$table_name = $wpdb->prefix . 'loan_raw_applications';
	if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE $table_name (
           	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		    session_id VARCHAR(255) NOT NULL,
		    user_id BIGINT UNSIGNED DEFAULT 0,
		    raw_data LONGTEXT NOT NULL,
		    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		    PRIMARY KEY (id),
		    KEY idx_session_id (session_id)
        ) $charset_collate;";
		dbDelta($sql);
	}

	// Обновляем версию БД
	if (!get_option('wpp_loan_db_version')) {
		update_option('wpp_loan_db_version', '1.0.0');
	}
}
//register_activation_hook(__FILE__, 'wpp_create_loan_application_table');

// Создание страниц при активации
//register_activation_hook( __FILE__, 'wpp_loan_create_pages' );

function wpp_loan_create_pages() {
	// Список шагов и их шорткодов
	$steps = json_decode( WPP_LOAN_STEPS, true );

	$steps['home']  =  [
		'slug'  => 'main',
		'title' => 'home',
		'short' => '[loan_start_button link="/personal" text="Start Application"]'
	];

	foreach ( $steps as $step_number => $config ) {

		// Проверяем, существует ли уже страница
		if ( get_page_by_path( $config['slug'], OBJECT, 'page' ) ) {
			continue;
		}

		$page_id = wp_insert_post( [
			'post_title'     => $config['title'],
			'post_name'      => $config['slug'],
			'post_content'   => $config['short'],
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'comment_status' => 'closed',
			'ping_status'    => 'closed'
		] );

		if($config['slug'] === 'main') {
			// Назначаем созданную страницу как домашнюю
			update_option('show_on_front', 'page');
			update_option('page_on_front', $page_id);
		}
	}
}