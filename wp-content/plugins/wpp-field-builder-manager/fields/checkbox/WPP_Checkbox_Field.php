<?php
/**
 * Class WPP_Checkbox_Field
 *
 * Представляет чекбокс формы.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WPP_Checkbox_Field') && class_exists('WPP_Form_Field')) :
    class WPP_Checkbox_Field extends WPP_Form_Field {

        public function __construct($args = []) {
            parent::__construct($args);

            // Подключаем JS только если поле используется
            add_action('wp_enqueue_scripts', [$this, 'enqueue_assets'],100);
            add_action('admin_footer', [$this, 'enqueue_assets']);
        }

        public function enqueue_assets() {
            wp_enqueue_script(
                'wpp-checkbox',
                WPP_FIELD_BUILDER_URL . 'fields/checkbox/script.js',
                ['jquery'],
                file_exists(WPP_FIELD_BUILDER_PATH . 'fields/checkbox/script.js')
                    ? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/checkbox/script.js')
                    : time(),
                true
            );

            wp_enqueue_style(
                'wpp-checkbox',
                WPP_FIELD_BUILDER_URL . 'fields/checkbox/style.css',
                [],
                file_exists(WPP_FIELD_BUILDER_PATH . 'fields/checkbox/style.css')
                    ? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/checkbox/style.css')
                    : time(),
                'all'
            );
        }

        /**
         * Рендерит HTML-код чекбокса
         */
        public function render() {
            $this->render_wrapper_start();

            $id = sanitize_key($this->get_name());
            $name = esc_attr($this->get_name());
            $checked = !empty($this->get_value()) ? 'checked' : '';
            $required = $this->args['required'] ? 'required' : '';

            ?>
            <div class="form-check">
                <input type="checkbox"
                       id="<?php echo $id; ?>"
                       name="<?php echo $name; ?>"
                       value="1"
                       class="form-check-input <?php echo esc_attr(implode(' ', $this->args['classes'])); ?>"
                    <?php echo $checked; ?>
                    <?php echo $required; ?>>
                <?php if (!empty($this->args['label'])): ?>
                    <label class="form-check-label" for="<?php echo $id; ?>">
                        <?php echo esc_html($this->args['label']); ?>
                    </label>
                <?php endif; ?>
                <?php if (!empty($this->args['description'])): ?>
                    <small class="form-text text-muted"><?php echo esc_html($this->args['description']); ?></small>
                <?php endif; ?>
            </div>
            <?php

            $this->render_wrapper_end();
        }
    }
endif;