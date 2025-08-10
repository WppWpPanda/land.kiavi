<?php
/**
 * Plugin Name: Habr Q&A Custom Login Page
 * Description: Кастомизация страницы входа WordPress - заменяет логотип на название и описание сайта, скрывает ссылку на главную
 * Version: 1.0.0
 * Author: WP_Panda
 * Author URI: https://wp-panda.pro
 * Email: panda@wp-panda.pro
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if (!defined('ABSPATH')) {
	exit; // Запрет прямого доступа
}

function wpp_habr_custom_login_styles() {
	// Убираем стандартный логотип WordPress
	add_filter('login_headerurl', function() { return home_url(); });
	add_filter('login_headertext', function() { return get_bloginfo('name'); });

	// Добавляем свои стили
	echo '<style type="text/css">
        #login h1 a {
            display: none !important;
        }
        .login #backtoblog {
            display: none !important;
        }
    </style>';
}
add_action('login_head', 'wpp_habr_custom_login_styles');

function wpp_habr_custom_login_header() {
	echo '<div id="login">
        <h1>'.esc_html(get_bloginfo('name')).'</h1>
        <p style="text-align:center">'.esc_html(get_bloginfo('description')).'</p>';
}
add_action('login_header', 'wpp_habr_custom_login_header');