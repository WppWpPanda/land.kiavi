<?php
/**
 * WPP_Field_Builder - class-wpp-form-field.php
 *
 * Абстрактный родительский класс для всех полей формы.
 * Обеспечивает единое API для всех типов полей (text, select, checkbox и т.д.)
 *
 * @package WPP_Field_Builder
 * @subpackage Fields
 * @since 1.0.0
 * @author Your Name <your@email.com>
 * @license GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Абстрактный класс WPP_Form_Field
 *
 * Является базовым классом для всех кастомных полей плагина.
 * Реализует общую логику:
 * - Установка параметров поля
 * - Рендеринг обёртки, метки, описания
 * - Валидация
 * - Условная логика отображения
 */
abstract class WPP_Form_Field {

	/**
	 * Массив аргументов и настроек поля
	 *
	 * @var array
	 */
	protected $args;

	/**
	 * Конструктор
	 *
	 * Принимает массив параметров и устанавливает значения по умолчанию.
	 *
	 * @param array $args Пользовательские параметры поля
	 *
	 * @since 1.0.0
	 */
	public function __construct( $args = [] ) {
		$defaults = [
			'name'        => '',           // Имя поля (name="...")
			'label'       => '',           // Подпись поля
			'description' => '',           // Описание под полем
			'default'     => '',           // Значение по умолчанию
			'placeholder' => '',           // Placeholder
			'classes'     => [],           // Дополнительные CSS-классы
			'width'       => 'full',       // Ширина: full, 1/2, 1/3 и т.д.
			'required'    => false,        // Обязательное ли поле
			'conditional' => [],           // Условие отображения ['field_name' => ['value']]
			'validation'  => null,         // Callback для валидации
		];

		$this->args = wp_parse_args( $args, $defaults );
	}

	/**
	 * Рендерит HTML-код поля.
	 *
	 * Если метод не переопределён в дочернем классе — выводит предупреждение.
	 * Это позволяет избежать фатальных ошибок и помогает при разработке.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function render() {
		$called_class = get_called_class();
		$parent_class = __CLASS__;

		trigger_error(
			"Метод render() не реализован в классе {$called_class}. Используется базовая реализация из {$parent_class}.",
			E_USER_WARNING
		);

		echo '<div class="wpp-field-error alert alert-danger">';
		echo '<strong>Ошибка:</strong> Метод render() должен быть реализован в дочернем классе.';
		echo '</div>';
	}

	// ┌────────────────────────────────────────────┐
	// │           Base Rendering Methods           │
	// └────────────────────────────────────────────┘

	/**
	 * Рендерит начальную обёртку поля (div)
	 *
	 * @return void
	 * @since 1.0.0
	 */
	/**
	 * Рендерит начальную обёртку поля (div)
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function render_wrapper_start() {



		$name    = esc_attr( $this->args['name'] );

		var_dump($name);
		$classes = array_map( 'esc_attr', $this->args['classes'] );
		$width   = strtolower( trim( $this->args['width'] ) );

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
		if ( ! empty( $this->args['conditional'] ) && is_array( $this->args['conditional'] ) ) {
			$condition_data = htmlspecialchars( json_encode( $this->args['conditional'] ), ENT_QUOTES, 'UTF-8' );
		}

		$compare = ! empty( $this->args['compare'] ) ? esc_attr( $this->args['compare'] ) : '=';

		?>
        <div class="wpp-field wpp-<?php echo sanitize_key( get_class( $this ) ); ?> <?php echo esc_attr( implode( ' ', $classes ) ); ?>"
        data-name="<?php echo $name; ?>"
		<?php if ( ! $this->should_render() ) {
			echo ' style="display:none"';
		} ?>

		<?php if ( $condition_data ): ?>data-compare="<?php echo $compare; ?>" data-condition='<?php echo $condition_data; ?>'<?php endif; ?>>
		<?php
	}

	/**
	 * Рендерит закрывающий тег обёртки поля
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function render_wrapper_end() {
		?>
        </div>
		<?php
	}

	/**
	 * Рендерит подпись (label)
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function render_label() {
		if ( ! empty( $this->args['label'] ) ) {
			$id = sanitize_key( $this->args['name'] );
			echo '<label for="' . $id . '">' . esc_html( $this->args['label'] ) . '</label>';
		}
	}

	/**
	 * Рендерит описание (description)
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function render_description() {
		if ( ! empty( $this->args['description'] ) ) {
			echo '<small class="description">' . esc_html( $this->args['description'] ) . '</small>';
		}
	}

	// ┌────────────────────────────────────────────┐
	// │             Validation & Logic             │
	// └────────────────────────────────────────────┘

	/**
	 * Возвращает значение поля
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function get_value() {
		return $this->args['default'];
	}

	/**
	 * Возвращает имя поля
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_name() {
		return $this->args['name'];
	}

	/**
	 * Возвращает правила условного отображения
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_conditional_rules() {
		return $this->args['conditional'];
	}

	/**
	 * Выполняет валидацию значения поля
	 *
	 * Если задан пользовательский callback — вызывает его.
	 * Иначе использует стандартную фильтрацию.
	 *
	 * @param mixed $value Введённое значение
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function validate( $value ) {
		if ( $this->args['validation'] && is_callable( $this->args['validation'] ) ) {
			return call_user_func( $this->args['validation'], $value );
		}

		return sanitize_text_field( $value );
	}


	public function should_render() {
		if ( empty( $this->args['conditional'] ) ) {
			return true; // Если нет условий — рендерим всегда
		}

		$conditions = $this->args['conditional'];

		foreach ( $conditions as $condition_field => $condition_values ) {
			$expected_value = WPP_Loan_Session_Handler::get_field_value( 1, $condition_field );

			// Поддержка массива в conditional
			if ( is_array( $condition_values ) ) {
				if ( ! in_array( $expected_value, $condition_values ) ) {
					return false;
				}
			} else {
				if ( $expected_value !== $condition_values ) {
					return false;
				}
			}
		}

		return true;
	}
}