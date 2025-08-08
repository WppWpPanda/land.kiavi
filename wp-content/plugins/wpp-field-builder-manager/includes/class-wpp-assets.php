<?php
/**
 * WPP_Field_Builder - class-wpp-assets.php
 *
 * Этот файл отвечает за подключение всех необходимых ассетов (CSS и JS),
 * включая Bootstrap 5 и jQuery как в админке WordPress, так и на фронтенде.
 *
 * @package WPP_Field_Builder
 * @subpackage Assets
 * @since 1.0.0
 * @author Your Name <your@email.com>
 * @license GPL-2.0-or-later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class WPP_Assets
 *
 * Отвечает за подключение CSS и JS ресурсов плагина:
 * - Bootstrap 5 (через CDN)
 * - Кастомные стили и скрипты
 * - jQuery (входит в ядро WordPress)
 *
 * Используется как в административной части WordPress, так и на фронтенде.
 */
class WPP_Assets {

    /**
     * Регистрирует хуки для подключения ассетов.
     *
     * @since 1.0.0
     * @return void
     */
    public static function init() {
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_frontend']);
    }

    /**
     * Подключает ассеты для административной панели WordPress.
     *
     * @since 1.0.0
     * @param string $hook Текущий хук страницы админки.
     * @return void
     */
    public static function enqueue_admin($hook) {
        self::enqueue_bootstrap();
        wp_enqueue_script(
            'wpp-admin-js',
            WPP_FIELD_BUILDER_URL . 'assets/js/admin.js',
            ['jquery'],
            null,
            true
        );
        wp_enqueue_style(
            'wpp-admin-css',
            WPP_FIELD_BUILDER_URL . 'assets/css/admin.css'
        );
    }

    /**
     * Подключает ассеты для фронтенда сайта.
     *
     * @since 1.0.0
     * @return void
     */
    public static function enqueue_frontend() {
        self::enqueue_bootstrap();
        // JS
        wp_enqueue_script(
            'wpp-frontend-js',
            WPP_FIELD_BUILDER_URL . 'assets/js/frontend.js',
            ['jquery'],
            filemtime(WPP_FIELD_BUILDER_PATH . 'assets/js/frontend.js'),
            true
        );

// CSS
        wp_enqueue_style(
            'wpp-frontend-css',
            WPP_FIELD_BUILDER_URL . 'assets/css/frontend.css',
            [],
            filemtime(WPP_FIELD_BUILDER_PATH . 'assets/css/frontend.css')
        );
    }

    /**
     * Подключает стили и скрипты Bootstrap 5 через CDN.
     *
     * @since 1.0.0
     * @return void
     */
    private static function enqueue_bootstrap() {
        // Подключаем стили Bootstrap 5 через CDN
        wp_enqueue_style(
            'bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
            [],
            '5.3.0'
        );

        // Подключаем JavaScript Bootstrap 5 (с Popper)
        wp_enqueue_script(
            'bootstrap-js',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
            [],
            '5.3.0',
            true
        );
    }
}