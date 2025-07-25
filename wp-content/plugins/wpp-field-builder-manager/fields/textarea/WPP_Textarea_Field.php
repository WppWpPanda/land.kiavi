<?php
/**
 * WPP_Field_Builder - WPP_Textarea_Field.php
 *
 * Реализация текстовой области формы.
 * Расширяет базовый класс WPP_Form_Field.
 *
 * @package WPP_Field_Builder
 * @subpackage Fields
 * @since 1.0.0
 * @author Your Name <your@email.com>
 * @license GPL-2.0-or-later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WPP_Textarea_Field')) :

    /**
     * Class WPP_Textarea_Field
     *
     * Представляет текстовую область (textarea).
     */
    class WPP_Textarea_Field extends WPP_Form_Field {

        public function __construct($args = []) {
            parent::__construct($args);

            // Подключаем JS только если поле используется
            add_action('wp_enqueue_scripts', [$this, 'enqueue_assets'],100);
            add_action('admin_footer', [$this, 'enqueue_assets']);
        }

        public function enqueue_assets() {
            wp_enqueue_script(
                'wpp-textarea',
                WPP_FIELD_BUILDER_URL . 'fields/textarea/script.js',
                ['jquery'],
                file_exists(WPP_FIELD_BUILDER_PATH . 'fields/textarea/script.js')
                    ? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/textarea/script.js')
                    : time(),
                true
            );

            wp_enqueue_style(
                'wpp-textarea',
                WPP_FIELD_BUILDER_URL . 'fields/textarea/style.css',
                [],
                file_exists(WPP_FIELD_BUILDER_PATH . 'fields/textarea/style.css')
                    ? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/textarea/style.css')
                    : time(),
                'all'
            );
        }

        /**
         * Рендерит HTML-код текстовой области.
         *
         * @since 1.0.0
         * @return void
         */
        public function render() {
            $this->render_wrapper_start();
            $this->render_label();

            $id = sanitize_key($this->get_name());
            $name = esc_attr($this->get_name());
            $value = esc_textarea($this->get_value());
            $placeholder = esc_attr($this->args['placeholder']);
            $required = $this->args['required'] ? 'required' : '';
            $rows = isset($this->args['rows']) ? intval($this->args['rows']) : 4;

            ?>
            <textarea id="<?php echo $id; ?>"
                      name="<?php echo $name; ?>"
                      rows="<?php echo $rows; ?>"
                      placeholder="<?php echo $placeholder; ?>"
                      <?php echo $required; ?>><?php echo $value; ?></textarea>
            <?php

            $this->render_description();
            $this->render_wrapper_end();
        }
    }

endif;