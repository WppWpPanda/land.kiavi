<?php
/**
 * Class WPP_Text_Field
 *
 * Represents text fields:
 * - text
 * - email
 * - tel
 * - hidden
 * - percentage (new type)
 */
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WPP_Text_Field') && class_exists('WPP_Form_Field')) :
	class WPP_Text_Field extends WPP_Form_Field {

		public function __construct($args = []) {
			parent::__construct($args);

			// Подключаем JS только если поле используется
			add_action('wp_footer', [$this, 'enqueue_assets']);
			add_action('admin_footer', [$this, 'enqueue_assets']);
		}

		public function enqueue_assets() {
			wp_enqueue_script(
				'wpp-text',
				WPP_FIELD_BUILDER_URL . 'fields/text/text.js',
				['jquery'],
				file_exists(WPP_FIELD_BUILDER_PATH . 'fields/text/text.js')
					? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/text/text.js')
					: time(),
				true
			);

			wp_enqueue_style(
				'wpp-text',
				WPP_FIELD_BUILDER_URL . 'fields/text/text.css',
				[],
				file_exists(WPP_FIELD_BUILDER_PATH . 'fields/text/text.css')
					? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/text/text.css')
					: time(),
				'all'
			);
		}

		/**
		 * Renders the HTML code for a text field
		 */
		public function render() {
			$this->render_wrapper_start();

			$name = esc_attr($this->get_name());
			$id = sanitize_key($this->args['name']);
			$value = $this->get_value();
			$placeholder = esc_attr($this->args['placeholder'] ?? '');
			$label = esc_html($this->args['label'] ?? '');
			$description = esc_html($this->args['description'] ?? '');
			$required = !empty($this->args['required']) ? 'required' : '';
			$classes = array_map('esc_attr', $this->args['classes'] ?? []);
			$element_type = $this->args['element_type'] ?? 'text';

			// Получаем тип поля (money) и признак наличия копеек
			$is_money = $element_type === 'money';
			$has_cents = isset($this->args['has_cents']) && $this->args['has_cents'] === true;

			// Новый тип: percentage
			$is_percentage = $element_type === 'percentage';


			// Формируем значение для отображения
			$displayValue = $value;

			if ($is_money) {
				$displayValue = $value ? str_replace(['$', ',', "\xc2\xa0", "\t", " "], '', $value) : '';

				if (!$displayValue) {
					$displayValue = '';
				} elseif ($has_cents) {
					$displayValue = number_format((float)$displayValue, 2, '.', ',');
				} else {
					$displayValue = number_format((int)$displayValue, 0, '', ',');
				}
				$displayValue = '$' . $displayValue;

			} elseif ($is_percentage) {
				$displayValue = $value ? str_replace('%', '', $value) : '';
				if (!$displayValue) {
					$displayValue = '';
				}
				$displayValue = number_format((float)$displayValue, 2, '.', '');
			}

			?>
            <!-- Выводим label -->
			<?php if ($label): ?>
                <label for="<?php echo $id; ?>" class="form-label"><?php echo $label; ?></label>
			<?php endif; ?>

            <!-- Само поле -->
            <input type="<?php echo $element_type; ?>"
                   id="<?php echo $id; ?>"
                   name="<?php echo $name; ?>"
                   value="<?php echo $displayValue; ?>"
                   placeholder="<?php echo $is_money ? '$0.00' : ($is_percentage ? '0.00%' : $placeholder); ?>"
                   class="form-control <?php echo esc_attr(implode(' ', $classes)); ?>"
                   data-type="<?php echo $is_money ? 'money' : ($is_percentage ? 'percentage' : $element_type); ?>"
			       <?php if ($is_money): ?>data-has-cents="<?php echo $has_cents ? 'yes' : 'no'; ?>"<?php endif; ?>
				<?php echo $required; ?>>
			<?php

			// Описание под полем
			if ($description) {
				?><small class="form-text text-muted"><?php echo $description; ?></small><?php
			}

			$this->render_wrapper_end();
		}

		/**
		 * Overrides render_wrapper_start()
		 *
		 * @override
		 */
		public function render_wrapper_start() {
			$name = esc_attr($this->get_name());

			// Не выводим обёртку для hidden
			if (!empty($this->args['element_type']) && $this->args['element_type'] === 'hidden') {
				return;
			}

			$classes = array_map('esc_attr', $this->args['classes']);
			$width = strtolower(trim($this->args['width']));

			// Ширина через Bootstrap
			$width_class = match ( $width ) {
				'1/12' => 'col-md-1',
				'1' => 'col-md-1',
				'2' => 'col-md-2',
				'3' => 'col-md-3',
				'4' => 'col-md-4',
				'5' => 'col-md-5',
				'6' => 'col-md-6',
				'7' => 'col-md-7',
				'8' => 'col-md-8',
				'9' => 'col-md-9',
				'10' => 'col-md-10',
				'11' => 'col-md-11',
				'1/6' => 'col-md-2',
				'2/12' => 'col-md-2',
				'1/2' => 'col-md-6',
				'1/3' => 'col-md-4',
				'2/3' => 'col-md-8',
				'1/4' => 'col-md-3',
				default => 'col-12',
			};

			$classes[] = $width_class;

			// Условная логика
			$condition_data = '';
			if (!empty($this->args['conditional']) && is_array($this->args['conditional'])) {
				$condition_data = htmlspecialchars(json_encode($this->args['conditional']), ENT_QUOTES, 'UTF-8');
			}

			$type_class = 'wpp-' . strtolower(preg_replace('/^WPP_|_Field$/', '', get_class($this)));

			?>
        <div class="wpp-field wpp-<?php echo $type_class; ?> <?php echo esc_attr(implode(' ', $classes)); ?>"
             data-name="<?php echo $name; ?>"
		     <?php if ($condition_data): ?>data-condition='<?php echo $condition_data; ?>'<?php endif; ?>>
			<?php
		}
	}
endif;