<?php
/**
 * Class WPP_Button_Field
 *
 * Представляет:
 * - <button>
 * - <a class="btn ...">
 * - <a>
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WPP_Button_Field') && class_exists('WPP_Form_Field')) :
    class WPP_Button_Field extends WPP_Form_Field {

        public function __construct($args = []) {
            parent::__construct($args);

            // Подключаем JS только если поле используется
            add_action('wp_enqueue_scripts', [$this, 'enqueue_assets'],100);
            add_action('admin_footer', [$this, 'enqueue_assets']);
        }

        public function enqueue_assets() {
            wp_enqueue_script(
                'wpp-button',
                WPP_FIELD_BUILDER_URL . 'fields/button/script.js',
                ['jquery'],
                file_exists(WPP_FIELD_BUILDER_PATH . 'fields/button/script.js')
                    ? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/button/script.js')
                    : time(),
                true
            );

            wp_enqueue_style(
                'wpp-button',
                WPP_FIELD_BUILDER_URL . 'fields/number/style.css',
                [],
                file_exists(WPP_FIELD_BUILDER_PATH . 'fields/button/style.css')
                    ? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/button/style.css')
                    : time(),
                'all'
            );
        }

        public function render() {
            $this->render_wrapper_start();

            $name = esc_attr($this->get_name());
            $id = sanitize_key($this->args['name']);
            $label = !empty($this->args['label']) ? $this->args['label'] : 'Кнопка';
            $type = !empty($this->args['btn_type']) ? esc_attr($this->args['btn_class']) : 'submit';
            $btn_class = !empty($this->args['btn_class']) ? esc_attr($this->args['btn_class']) : 'btn-primary';
            $href = !empty($this->args['href']) ? esc_url($this->args['href']) : '#';
            $element_type = !empty($this->args['element_type']) ? $this->args['element_type'] : 'button';

            // Общие классы
            $classes = ['btn'];
            if ($element_type === 'link_button') {
                $classes[] = $btn_class;
            } elseif ($element_type === 'link') {
                $classes = [];
            } else {
                $classes[] = $btn_class;
            }

            $class_string = implode(' ', $classes);
            ?>
            <?php if ($element_type === 'button' || $element_type === 'submit'  ): ?>
                <button type="<?php echo $type; ?>"
                        id="<?php echo $id; ?>"
                        name="<?php echo $name; ?>"
                        class="<?php echo $class_string; ?>">
                    <?php echo esc_html($label); ?>
                </button>

            <?php elseif ($element_type === 'link_button'): ?>
                <a href="<?php echo $href; ?>"
                   id="<?php echo $id; ?>"
                   class="<?php echo $class_string; ?>">
                    <?php echo esc_html($label); ?>
                </a>

            <?php elseif ($element_type === 'link'): ?>
                <a href="<?php echo $href; ?>"
                   id="<?php echo $id; ?>"
                   class="<?php echo $class_string; ?>">
                    <?php echo esc_html($label); ?>
                </a>

            <?php endif; ?>

            <?php
            $this->render_description();
            $this->render_wrapper_end();
        }
    }
endif;