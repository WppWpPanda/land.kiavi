<?php
/**
 * WPP_Field_Builder - class-wpp-field-loader.php
 *
 * Этот файл отвечает за автоматическую загрузку классов полей из папки `/fields`.
 * Каждая директория в `/fields` представляет собой тип поля (например, `text`, `select`),
 * где должен быть файл `[Field_Type]_Field.php`.
 *
 * @package WPP_Field_Builder
 * @subpackage Loader
 * @since 1.0.0
 * @author Your Name <your@email.com>
 * @license GPL-2.0-or-later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class WPP_Field_Loader
 *
 * Отвечает за автоматическое подключение классов полей,
 * находящихся в папке `/fields`. Каждое поле должно иметь:
 * - Папку с названием типа поля (например, `/text`)
 * - Файл класса вида `WPP_[Type]_Field.php`
 *
 * Пример: `/fields/text/WPP_Text_Field.php`
 */
class WPP_Field_Loader {

    private static $instance = null;

    public static function init() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_fields();
    }

    private function load_fields() {
        $fields_dir = WPP_FIELD_BUILDER_PATH . 'fields/';

        if (!is_dir($fields_dir)) {
            error_log("❌ WPP_Field_Builder: Папка fields/ не найдена");
            return;
        }

        $field_dirs = array_filter(glob($fields_dir . '*'), 'is_dir');

        if (empty($field_dirs)) {
            error_log("❌ WPP_Field_Builder: Нет папок в fields/");
            return;
        }

        foreach ($field_dirs as $dir) {
            $folder_name = basename($dir);
            $class_name = 'WPP_' . ucfirst($folder_name) . '_Field';
            $file_path = $dir . '/' . $class_name . '.php';

            if (file_exists($file_path)) {
                require_once $file_path;
                //error_log("✅ Загружено поле: {$class_name}");
            } else {
                error_log("❌ Файл не найден: {$file_path}");
            }
        }
    }
}