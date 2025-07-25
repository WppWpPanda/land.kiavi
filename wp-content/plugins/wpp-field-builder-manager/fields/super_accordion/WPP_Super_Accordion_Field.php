<?php
/**
 * WPP Super Accordion Field
 *
 * Поле аккордеона, которое может содержать другие поля внутри.
 * Поддерживает открытие/закрытие по клику на заголовке
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WPP_Super_Accordion_Field') && class_exists('WPP_Form_Field')) :

	class WPP_Super_Accordion_Field extends WPP_Form_Field {

		public function __construct($args = []) {
			parent::__construct($args);

			// Подключаем JS/CSS только если используется
			add_action('wp_footer', [$this, 'enqueue_assets']);
			add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
		}

		public function enqueue_assets() {
			wp_enqueue_script(
				'wpp-super_accordion',
				WPP_FIELD_BUILDER_URL . 'fields/super_accordion/super_accordion.js',
				['jquery'],
				file_exists(WPP_FIELD_BUILDER_PATH . 'fields/super_accordion/super_accordion.js')
					? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/super_accordion/super_accordion.js')
					: time(),
				true
			);

			wp_enqueue_style(
				'wpp-super_accordion',
				WPP_FIELD_BUILDER_URL . 'fields/super_accordion/super_accordion.css',
				[],
				file_exists(WPP_FIELD_BUILDER_PATH . 'fields/super_accordion/super_accordion.css')
					? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/super_accordion/super_accordion.css')
					: time()
			);
		}

		private function get_form_data() {
			$step = WPP_Loan_Session_Handler::get_step_data(4); // или другой шаг
			return $step['formData'] ?? [];
		}

		/**
		 * Рендерит аккордеон с внутренними полями
		 */
		public function render() {
			$this->render_wrapper_start();

			$id = sanitize_key($this->args['name']);
			$title = esc_html($this->args['title'] ?? 'Details');
			$header_template = $this->args['header'] ?? ''; // Например: "{first_name} {last_name}"
			$content = '';
			$fields = $this->args['fields'] ?? [];
			$is_open = !empty($this->args['open']);

			// Получаем данные формы (из сессии)
			$form_data = []; // должен возвращать formData из шага

			// Парсим заголовок
			$header_text = '';
			if ($header_template && is_array($form_data)) {
				$header_text = preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function ($matches) use ($form_data) {
					$key = $matches[1];
					return esc_html($form_data[$key] ?? $key);
				}, $header_template);
			}

			?>
            <div class="wpp-super-accordion <?php echo $is_open ? 'open' : ''; ?>" id="<?php echo $id; ?>"
                 data-header="<?php echo esc_attr($header_template); ?>">
                <div class="wpp-super-accordion-header">
                    <h5 class="row">
						<?php if ($header_text): ?>
							<?php echo esc_html($header_text); ?>
						<?php else: ?>
							<?php echo $title; ?>
						<?php endif; ?>
                    </h5>
                    <span class="toggle-icon"><?php echo $is_open ? '▼' : '▶'; ?></span>
                </div>

                <div class="wpp-super-accordion-body row" style="display: <?php echo $is_open ? 'block' : 'none'; ?>;">
					<?php foreach ($fields as $name => $config):
						$class_name = 'WPP_' . ucfirst($config['type']) . '_Field';

						if (class_exists($class_name)) {
							$field = new $class_name(array_merge($config, ['name' => $name]));
							$field->render();
						}
					endforeach; ?>
                </div>
            </div>
			<?php

			$this->render_wrapper_end();
		}
	}

endif;