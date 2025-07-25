<?php
/**
 * Class WPP_Accordion_Field
 *
 * Представляет аккордеон (раскрывающийся блок) для группировки полей или контента.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WPP_Accordion_Field') && class_exists('WPP_Form_Field')) :
    class WPP_Accordion_Field extends WPP_Form_Field {

        public function __construct($args = []) {
            parent::__construct($args);

            // Подключаем JS только если поле используется
            add_action('wp_enqueue_scripts', [$this, 'enqueue_assets'],100);
            add_action('admin_footer', [$this, 'enqueue_assets']);
        }

        public function enqueue_assets() {
            wp_enqueue_script(
                'wpp-accordion',
                WPP_FIELD_BUILDER_URL . 'fields/accordion/script.js',
                ['jquery'],
                file_exists(WPP_FIELD_BUILDER_PATH . 'fields/accordion/script.js')
                    ? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/accordion/script.js')
                    : time(),
                true
            );

            wp_enqueue_style(
                'wpp-accordion',
                WPP_FIELD_BUILDER_URL . 'fields/accordion/style.css',
                [],
                file_exists(WPP_FIELD_BUILDER_PATH . 'fields/accordion/style.css')
                    ? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/accordion/style.css')
                    : time(),
                'all'
            );
        }

        /**
         * Рендерит HTML-код аккордеона
         */
        public function render() {
            $this->render_wrapper_start();

            $id = sanitize_key($this->get_name());
            $title = !empty($this->args['title']) ? esc_html($this->args['title']) : 'Раскрыть';
            $content = !empty($this->args['content']) ? $this->args['content'] : '';
            $open = !empty($this->args['open']); // по умолчанию закрыт

            ?>
            <div class="accordion" id="<?php echo $id; ?>-accordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="<?php echo $id; ?>-header">
                        <button class="accordion-button <?php echo $open ? '' : 'collapsed'; ?>"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#<?php echo $id; ?>-collapse"
                                aria-expanded="<?php echo $open ? 'true' : 'false'; ?>"
                                aria-controls="<?php echo $id; ?>-collapse">
                            <?php echo $title; ?>
                        </button>
                    </h2>
                    <div id="<?php echo $id; ?>-collapse"
                         class="accordion-collapse collapse <?php echo $open ? 'show' : ''; ?>"
                         aria-labelledby="<?php echo $id; ?>-header"
                         data-bs-parent="#<?php echo $id; ?>-accordion">
                        <div class="accordion-body row">
                            <?php if (is_callable($content)): ?>
                                <?php call_user_func($content); ?>
                            <?php elseif (is_string($content)): ?>
                                <?php echo wp_kses_post($content); ?>
                            <?php else: ?>
                                <?php echo 'Контент не задан или неверного формата'; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php

            $this->render_wrapper_end();
        }
    }
endif;