<?php
/**
 * Class WPP_Radio_Field
 *
 * Представляет группу радио-кнопок.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WPP_Radio_Field') && class_exists('WPP_Form_Field')) :
    class WPP_Radio_Field extends WPP_Form_Field {

        public function __construct($args = []) {
            parent::__construct($args);

            // Подключаем JS только если поле используется
            add_action('wp_enqueue_scripts', [$this, 'enqueue_assets'],100);
            add_action('admin_footer', [$this, 'enqueue_assets']);
        }

        public function enqueue_assets() {
            wp_enqueue_script(
                'wpp-radio',
                WPP_FIELD_BUILDER_URL . 'fields/radio/script.js',
                ['jquery'],
                file_exists(WPP_FIELD_BUILDER_PATH . 'fields/radio/script.js')
                    ? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/radio/script.js')
                    : time(),
                true
            );

            wp_enqueue_style(
                'wpp-radio',
                WPP_FIELD_BUILDER_URL . 'fields/radio/style.css',
                [],
                file_exists(WPP_FIELD_BUILDER_PATH . 'fields/radio/style.css')
                    ? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/radio/style.css')
                    : time(),
                'all'
            );
        }

        /**
         * Рендерит HTML-код радио-поля
         */
        public function render() {
            $this->render_wrapper_start();

            $name = esc_attr($this->get_name());
            $selected_value = esc_attr($this->get_value());
            $options = isset($this->args['options']) && is_array($this->args['options']) ? $this->args['options'] : [];

            if (empty($options)) {
                echo '<p class="text-danger">Не переданы опции для радио-поля</p>';
                return;
            }

            foreach ($options as $value => $label) {
                $id = sanitize_key($name . '_' . $value);
                $checked = ($value == $selected_value) ? 'checked' : '';
                ?>
                <div class="form-check">
                    <input type="radio"
                           id="<?php echo $id; ?>"
                           name="<?php echo $name; ?>"
                           value="<?php echo esc_attr($value); ?>"
                           class="form-check-input <?php echo esc_attr(implode(' ', $this->args['classes'])); ?>"
                        <?php echo $checked; ?>
                        <?php echo $this->args['required'] ? 'required' : ''; ?>>
                    <label class="form-check-label" for="<?php echo $id; ?>">
                        <?php echo esc_html($label); ?>
                    </label>
                </div>
                <?php
            }

            $this->render_description();
            $this->render_wrapper_end();
        }
    }
endif;