<?php
/**
 * Plugin Name: WPP Field Builder Manager
 * Description: Универсальный менеджер форм для WordPress. Поддерживает кастомные поля, условную логику, валидацию и адаптивный дизайн на Bootstrap.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: wpp-field-builder
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Определяем константы плагина
if (!defined('WPP_FIELD_BUILDER_PATH')) {
    define('WPP_FIELD_BUILDER_PATH', plugin_dir_path(__FILE__));
}
if (!defined('WPP_FIELD_BUILDER_URL')) {
    define('WPP_FIELD_BUILDER_URL', plugin_dir_url(__FILE__));
}

/**
 * Подключаем основные классы плагина
 */
require_once WPP_FIELD_BUILDER_PATH . 'includes/class-wpp-form-field.php';

// Подключаем загрузчик полей
if (file_exists(WPP_FIELD_BUILDER_PATH . 'includes/class-wpp-field-loader.php')) {
    require_once WPP_FIELD_BUILDER_PATH . 'includes/class-wpp-field-loader.php';
} else {
    error_log('WPP_Field_Builder: class-wpp-field-loader.php не найден!');
}

// Подключаем ассеты (Bootstrap, jQuery)
if (file_exists(WPP_FIELD_BUILDER_PATH . 'includes/class-wpp-assets.php')) {
    require_once WPP_FIELD_BUILDER_PATH . 'includes/class-wpp-assets.php';
} else {
    error_log('WPP_Field_Builder: class-wpp-assets.php не найден!');
}

/**
 * Инициализируем систему ассетов (Bootstrap, jQuery)
 */
add_action('plugins_loaded', ['WPP_Assets', 'init']);

/**
 * Загружаем все поля при активации плагина
 */
add_action('plugins_loaded', function () {
    if (class_exists('WPP_Field_Loader')) {
        WPP_Field_Loader::init();
    } else {
        error_log('WPP_Field_Builder: Не удалось запустить WPP_Field_Loader — класс не найден');
    }
});

/**
 * Подключаем тестовую форму после загрузки всех полей
 */
add_action('plugins_loaded', function () {
    $test_form_path = WPP_FIELD_BUILDER_PATH . 'test/test-form.php';
    if (file_exists($test_form_path)) {
        require_once $test_form_path;
    } else {
        error_log('WPP_Field_Builder: test/test-form.php не найден!');
    }
});