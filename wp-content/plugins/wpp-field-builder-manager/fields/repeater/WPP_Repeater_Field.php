<?php
/**
 * Class WPP_Repeater_Field
 *
 * Повторитель (Repeater) с поддержкой любых кастомных полей внутри
 */
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WPP_Repeater_Field') && class_exists('WPP_Form_Field')) :
	class WPP_Repeater_Field extends WPP_Form_Field {

		public function __construct($args = []) {
			parent::__construct($args);

			// Подключаем JS только если поле используется
			add_action('wp_footer', [$this, 'enqueue_assets']);
			add_action('admin_footer', [$this, 'enqueue_assets']);
		}

		public function enqueue_assets() {
			wp_enqueue_script(
				'wpp-repeater',
				WPP_FIELD_BUILDER_URL . 'fields/repeater/script.js',
				['jquery'],
				file_exists(WPP_FIELD_BUILDER_PATH . 'fields/repeater/script.js')
					? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/repeater/script.js')
					: time(),
				true
			);

			wp_enqueue_style(
				'wpp-repeater',
				WPP_FIELD_BUILDER_URL . 'fields/repeater/style.css',
				[],
				file_exists(WPP_FIELD_BUILDER_PATH . 'fields/repeater/style.css')
					? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/repeater/style.css')
					: time(),
				'all'
			);
		}

		/**
		 * Рендерит HTML-код repeater
		 */
		public function render() {

         //   wpp_dump($this->args);
			$this->render_wrapper_start();
			$name = esc_attr($this->get_name());
			$id = sanitize_key($name);
			$title = !empty($this->args['title']) ? esc_html($this->args['title']) : '';
			$fields = !empty($this->args['fields']) && is_array($this->args['fields']) ? $this->args['fields'] : [];
			$min = isset($this->args['min']) ? intval($this->args['min']) : 1;
			$max = isset($this->args['max']) ? intval($this->args['max']) : 999;
			$button_text = !empty($this->args['button_text']) ? esc_html($this->args['button_text']) : '+ ADD';
			$value = $this->get_value();

			// Получаем данные из default или value
			$items = !empty($value) ? $value : (!empty($this->args['default']) ? $this->args['default'] : []);

			// Если items пустой и min > 0, создаем минимальное количество блоков
			if (empty($items) && $min > 0) {
				$items = array_fill(0, $min, []);
			}

			?>
            <div class="wpp-repeater-container" data-name="<?php echo $name; ?>">
                <div class="wpp-repeater-header d-flex justify-content-between align-items-center mb-3">
                    <h5><?php echo esc_html($title); ?></h5>
                    <button type="button" class="btn btn-sm btn-success wpp-repeater-add" data-max="<?php echo $max; ?>"><?php echo $button_text; ?></button>
                </div>

                <div class="wpp-repeater-inner">
					<?php
					if (!empty($items)) {
						foreach ($items as $index => $item) {

							$this->render_repeater_block($id, $index, $fields, $item);
						}
					} else {
						// Рендерим пустой блок, если нет данных
						$this->render_repeater_block($id, 0, $fields, []);
					}
					?>
                </div>

                <!-- Шаблон блока -->
                <script type="text/html" id="tmpl-<?php echo $id; ?>">
					<?php $this->render_repeater_block($id, '__index__', $fields, []); ?>
                </script>
            </div>
			<?php

			$this->render_description();
			$this->render_wrapper_end();
		}

		/**
		 * Рендер одного блока repeater
		 *
		 * @param string $id
		 * @param int|string $index
		 * @param array $fields
		 * @param array $values Значения для полей
		 */
		private function render_repeater_block($id, $index, $fields, $values = []) {
			?>
            <div class="wpp-repeater-block border mb-3 position-relative row">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 wpp-repeater-remove">&times;</button>

				<?php foreach ($fields as $key => $config):
					// Генерируем имя поля: field_name[__index__][key]
					$fieldName = $id . '[' . $index . '][' . $key . ']';

					// Устанавливаем значение из переданных данных
					$fieldValue = isset($values[$key]) ? $values[$key] : '';

                   // wpp_dump($fieldValue);

					// Для полей типа checkbox нужно особое обращение
					if ($config['type'] === 'checkbox') {
						$fieldValue = isset($values[$key]) ? $values[$key] : false;
					}

					// Создаем новый массив аргументов для поля
					$fieldArgs = array_merge($config, [
						'name' => $fieldName,
						'default' => $fieldValue
					]);

					// Определяем класс поля
					$class_name = 'WPP_' . ucfirst($config['type']) . '_Field';

					if (class_exists($class_name)) {
						$field = new $class_name($fieldArgs);
						$field->render();
					}
				endforeach; ?>
            </div>
			<?php
		}
	}
endif;