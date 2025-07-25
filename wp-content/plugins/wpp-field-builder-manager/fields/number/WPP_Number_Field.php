<?php
/**
 * Class WPP_Number_Field
 *
 * Текстовое поле типа number с кнопками +/- для изменения значения
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WPP_Number_Field') && class_exists('WPP_Form_Field')) :
    class WPP_Number_Field extends WPP_Form_Field {

        public function __construct($args = []) {
            parent::__construct($args);

            // Подключаем JS только если поле используется
            add_action('wp_enqueue_scripts', [$this, 'enqueue_assets'],100);
            add_action('admin_footer', [$this, 'enqueue_assets']);
        }

        public function enqueue_assets() {
            wp_enqueue_script(
                'wpp-number',
                WPP_FIELD_BUILDER_URL . 'fields/number/script.js',
                ['jquery'],
                file_exists(WPP_FIELD_BUILDER_PATH . 'fields/number/script.js')
                    ? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/number/script.js')
                    : time(),
                true
            );

            wp_enqueue_style(
                'wpp-number',
                WPP_FIELD_BUILDER_URL . 'fields/number/style.css',
                [],
                file_exists(WPP_FIELD_BUILDER_PATH . 'fields/number/style.css')
                    ? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/number/style.css')
                    : time(),
                 'all'
            );
        }

        public function render() {
            $this->render_wrapper_start();

            $name = esc_attr($this->get_name());
            $id = sanitize_key($this->args['name']);
            $value = intval($this->get_value()) ?: 0;
            $label = esc_html($this->args['label'] ?? '');
            $description = esc_html($this->args['description'] ?? '');
            $min = isset($this->args['min']) ? intval($this->args['min']) : 0;
            $max = isset($this->args['max']) ? intval($this->args['max']) : 9999;
            $step = isset($this->args['step']) ? intval($this->args['step']) : 1;

            ?>
            <div class="wpp-number-field">
                <?php if ($label): ?>
                    <label for="<?php echo $id; ?>" class="form-label"><?php echo $label; ?></label>
                <?php endif; ?>

                <div class="input-group wpp-number-input">
                    <button type="button" class="btn btn-outline-secondary wpp-number-decrement">–</button>
                    <input type="text"
                           id="<?php echo $id; ?>"
                           name="<?php echo $name; ?>"
                           value="<?php echo $value; ?>"
                           data-min="<?php echo $min; ?>"
                           data-max="<?php echo $max; ?>"
                           data-step="<?php echo $step; ?>"
                           class="form-control text-center"
                           readonly>
                    <button type="button" class="btn btn-outline-secondary wpp-number-increment">+</button>
                </div>

                <?php if ($description): ?>
                    <small class="form-text text-muted"><?php echo $description; ?></small>
                <?php endif; ?>
            </div>
            <?php

            $this->render_description();
            $this->render_wrapper_end();
        }
    }
endif;