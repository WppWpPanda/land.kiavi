<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WPP_Address_Field') && class_exists('WPP_Form_Field')) :
    class WPP_Address_Field extends WPP_Form_Field {

        private static $enqueue_google_maps = false;

        public function __construct($args = []) {
            parent::__construct($args);
            self::$enqueue_google_maps = true;

            add_action('wp_footer', [$this, 'enqueue_assets']);
            add_action('admin_footer', [$this, 'enqueue_assets']);
        }

        public function enqueue_assets() {
            if (self::$enqueue_google_maps) {
                // Версия JS по времени изменения
                $script_path = WPP_FIELD_BUILDER_PATH . 'fields/address/script.js';
                $script_version = file_exists($script_path) ? filemtime($script_path) : time();

                // Google Maps Places API
               // $api_key = 'AIzaSyD5ge7CvA3Q0OqFjMj9WugjR4lXa2Z6iUk'; // тестовый ключ
                $api_key = 'AAIzaSyD-2mhWsVLxX3gM-S2gV7Wv5hW9kUy6XJ4'; // тестовый ключ
                wp_enqueue_script(
                    'google-maps-api',
                    "https://maps.googleapis.com/maps/api/js?key={$api_key}&libraries=places&language=ru",
                    [],
                    null,
                    true
                );

                // Скрипт автозаполнения
                wp_enqueue_script(
                    'wpp-address-autocomplete',
                    WPP_FIELD_BUILDER_URL . 'fields/address/script.js',
                    ['jquery'],
                    $script_version,
                    true
                );
            }
        }

        public function render() {
            $this->render_wrapper_start();

            $name = esc_attr($this->get_name());
            $id = sanitize_key($this->args['name']);
            $value = esc_attr($this->get_value());

            ?>
            <input type="text"
                   id="<?php echo $id; ?>"
                   name="<?php echo $name; ?>"
                   value="<?php echo $value; ?>"
                   placeholder="Введите адрес"
                   data-field-name="<?php echo $name; ?>"
                   class="form-control wpp-wpp_address_field <?php echo esc_attr(implode(' ', $this->args['classes'])); ?>">

            <!-- Детали адреса -->
            <div class="wpp-address-details mt-2" id="<?php echo $id; ?>-details" style="display: none;">
                <p><strong>Адрес:</strong> <span class="address-full"></span></p>
                <p><strong>Город:</strong> <span class="address-city"></span></p>
                <p><strong>Индекс:</strong> <span class="address-zip"></span></p>
                <p><strong>Широта:</strong> <span class="address-lat"></span></p>
                <p><strong>Долгота:</strong> <span class="address-lng"></span></p>
            </div>

            <?php

            $this->render_description();
            $this->render_wrapper_end();
        }
    }
endif;