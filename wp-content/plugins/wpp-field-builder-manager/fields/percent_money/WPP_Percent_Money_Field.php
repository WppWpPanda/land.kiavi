<?php
/**
 * Class WPP_Percent_Money_Field
 *
 * Представляет поле с двумя значениями: сумму и проценты.
 * При изменении одного значения автоматически рассчитывается другое.
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WPP_Percent_Money_Field') && class_exists('WPP_Form_Field')) :

	class WPP_Percent_Money_Field extends WPP_Form_Field {

		/**
		 * Конструктор
		 *
		 * @param array $args Параметры поля
		 */
		public function __construct($args = []) {
			parent::__construct($args);

			// Подключаем JS только если поле используется
			add_action('wp_footer', [$this, 'enqueue_assets']);
			add_action('admin_footer', [$this, 'enqueue_assets']);
		}

		/**
		 * Подключение ресурсов
		 */
		public function enqueue_assets() {
			wp_enqueue_script(
				'wpp-percent-money',
				WPP_FIELD_BUILDER_URL . 'fields/percent_money/percent_money.js',
				['jquery'],
				filemtime(WPP_FIELD_BUILDER_PATH . 'fields/percent_money/percent_money.js'),
				true
			);

			wp_enqueue_style(
				'wpp-percent-money',
				WPP_FIELD_BUILDER_URL . 'fields/percent_money/percent_money.css',
				[],
				filemtime(WPP_FIELD_BUILDER_PATH . 'fields/percent_money/percent_money.css'),
				'all'
			);
		}

		/**
		 * Рендеринг HTML-кода поля
		 */
		/**
		 * Рендеринг HTML-кода поля
		 */
		public function render() {
			$this->render_wrapper_start();

			$name = esc_attr($this->get_name());
			$id = sanitize_key($this->args['name']);
			$label = esc_html($this->args['label'] ?? '');
			$description = esc_html($this->args['description'] ?? '');

			// Получаем значения по умолчанию
			$money_value = !empty($this->args['default']) && !empty($this->args['default']['money']) ? $this->args['default']['money'] : 0;
			$percent_value = !empty($this->args['default']) && !empty($this->args['default']['percent']) ? $this->args['default']['percent'] : 0;

			// Базовая сумма для расчёта
			$base_amount = isset($this->args['base_amount']) ? (float)$this->args['base_amount'] : 0;

			?>
            <!-- Label -->
			<?php if ($label): ?>
                <label for="<?php echo $id; ?>_money" class="form-label"><?php echo $label; ?></label>
			<?php endif; ?>

            <!-- Двойное поле: $ [input] и [input] % -->
            <div class="wpp-percent-money-field">

                <!-- Поле для суммы с префиксом $ -->
                <div class="wpp-money-input input-group">
                    <span class="input-group-text wpp-prefix">$</span>
                    <input
                            type="text"
                            id="<?php echo $id; ?>_money"
                            name="<?php echo $name; ?>[money]"
                            value="<?php echo esc_attr($money_value); ?>"
                            placeholder="0.00"
                            class="form-control money"
                            data-base-amount="<?php echo esc_attr($base_amount); ?>"
                            data-linked-field="#<?php echo $id; ?>_percent"
                            inputmode="decimal"
                    />
                </div>

                <!-- Разделитель (опционально) -->
                <div class="wpp-percent-separator">or</div>

                <!-- Поле для процента с суффиксом % -->
                <div class="wpp-percent-input input-group">
                    <input
                            type="number"
                            id="<?php echo $id; ?>_percent"
                            name="<?php echo $name; ?>[percent]"
                            value="<?php echo esc_attr($percent_value); ?>"
                            placeholder="0.00"
                            class="form-control percent"
                            data-base-amount="<?php echo esc_attr($base_amount); ?>"
                            data-linked-field="#<?php echo $id; ?>_money"
                            inputmode="decimal"
                            step="any"
                            min="0"
                            max="100"
                    />
                    <span class="input-group-text wpp-suffix">%</span>
                </div>

            </div>

            <!-- Описание под полем -->
			<?php if ($description): ?>
                <small class="form-text text-muted"><?php echo $description; ?></small>
			<?php endif; ?>

			<?php

			$this->render_wrapper_end();
		}

		/**
		 * Получение значения поля
		 *
		 * @param string $type Тип значения ('money' или 'percent')
		 * @return mixed
		 */
		public function get_value($type = 'money') {
			$value = null;

			switch ($type) {
				case 'money':
					$value = WPP_Loan_Session_Handler::get_field_value(1, $this->args['name'] . '[money]');
					break;
				case 'percent':
					$value = WPP_Loan_Session_Handler::get_field_value(1, $this->args['name'] . '[percent]');
					break;
			}

			return $value !== null ? $value : '';
		}
	}
endif;