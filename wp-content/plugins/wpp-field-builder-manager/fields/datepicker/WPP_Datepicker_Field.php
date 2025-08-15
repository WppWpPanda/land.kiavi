<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPP_Datepicker_Field' ) && class_exists( 'WPP_Form_Field' ) ) :

	/**
	 * Class WPP_Datepicker_Field
	 *
	 * Представляет поле ввода даты.
	 */
	class WPP_Datepicker_Field extends WPP_Form_Field {

		public function __construct( $args = [] ) {
			parent::__construct( $args );

			// Подключаем стили и скрипты только если поле используется
			add_action( 'wp_footer', [ $this, 'enqueue_assets' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		}

		public function enqueue_assets() {
			// jQuery UI Datepicker (встроенный WordPress)
			wp_enqueue_script(
				'jquery-ui-datepicker',
				false, // URL не нужен, используем встроенный в WP
				[ 'jquery', 'jquery-ui-core' ] // Указываем зависимости
			);


			// Стили jQuery UI
			// На:
			wp_enqueue_style(
				'jquery-ui-css',
				'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css'
			);

			// Инициализация datepicker
			$script = '
                jQuery(document).ready(function($) {
                    $("input[data-type=\'date\']").datepicker({
                        dateFormat : "yy-mm-dd",
                        changeMonth: true,
                        changeYear: true,
                        yearRange: "-10:+10"
                    });
                });
            ';
			wp_add_inline_script( 'jquery-ui-datepicker', $script );

			wp_enqueue_script(
				'wpp-datepicker',
				WPP_FIELD_BUILDER_URL . 'fields/datepicker/script.js',
				[ 'jquery' ],
				file_exists( WPP_FIELD_BUILDER_PATH . 'fields/datepicker/script.js' )
					? filemtime( WPP_FIELD_BUILDER_PATH . 'fields/datepicker/script.js' )
					: time(),
				true
			);
		}

		/**
		 * Рендерит HTML-код поля ввода даты
		 */
		public function render() {
			$this->render_wrapper_start();

			$id          = sanitize_key( $this->args['name'] );
			$name        = esc_attr( $this->args['name'] );
			$value       = esc_attr( $this->get_value() );
			$placeholder = esc_attr( $this->args['placeholder'] ?: 'YYYY-MM-DD' );
			$required    = $this->args['required'] ? 'required' : '';

			?>
            <label for="<?php echo $id; ?>">
				<?php echo esc_html( $this->args['label'] ); ?>
				<?php if ( $this->args['required'] ) : ?>
                    <span class="text-danger">*</span>
				<?php endif; ?>
            </label>
            <input type="text"
                   id="<?php echo $id; ?>"
                   name="<?php echo $name; ?>"
                   value="<?php echo $value; ?>"
                   placeholder="<?php echo $placeholder; ?>"
                   data-type="date"
                   class="form-control <?php echo esc_attr( implode( ' ', $this->args['classes'] ) ); ?>"
				<?php echo $required; ?>>

			<?php if ( ! empty( $this->args['description'] ) ) : ?>
                <small class="form-text text-muted"><?php echo esc_html( $this->args['description'] ); ?></small>
			<?php endif; ?>

			<?php
			$this->render_wrapper_end();
		}
	}

endif;