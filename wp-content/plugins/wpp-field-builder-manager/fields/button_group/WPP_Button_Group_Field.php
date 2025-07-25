<?php
/**
 * Class WPP_Button_Group_Field
 *
 * Группа кнопок, работающих как радио-поля
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPP_Button_Group_Field' ) && class_exists( 'WPP_Form_Field' ) ) :
	class WPP_Button_Group_Field extends WPP_Form_Field {


		public function __construct( $args = [] ) {

			parent::__construct( $args );
			// Подключаем JS только если поле используется
			add_action( 'wp_footer',  [$this, 'enqueue_assets' ], 10 );
			//add_action('admin_footer', [$this, 'enqueue_assets']);
		}

		public function enqueue_assets() {

			wp_enqueue_script(
				'wpp-button-group',
				WPP_FIELD_BUILDER_URL . 'fields/button_group/button-group.js',
				[ 'jquery' ],
				file_exists( WPP_FIELD_BUILDER_PATH . 'fields/button_group/button-group.js' )
					? filemtime( WPP_FIELD_BUILDER_PATH . 'fields/button_group/button-group.js' )
					: time(),
				true
			);

			wp_enqueue_style(
				'wp-button-group',
				WPP_FIELD_BUILDER_URL . 'fields/button_group/button-group.css',
				[],
				file_exists( WPP_FIELD_BUILDER_PATH . 'fields/button_group/button-group.css' )
					? filemtime( WPP_FIELD_BUILDER_PATH . 'fields/button_group/button-group.css' )
					: time(),
				'all'
			);

		}

		public function render() {
			$this->render_wrapper_start();

			$name        = esc_attr( $this->get_name() );
			$id          = sanitize_key( $this->args['name'] );
			$value       = esc_attr( $this->get_value() );
			$label       = esc_html( $this->args['label'] ?? '' );
			$description = esc_html( $this->args['description'] ?? '' );
			$options     = $this->args['options'] ?? [];
			$orientation = ! empty( $this->args['orientation'] ) && $this->args['orientation'] === 'vertical'
				? 'btn-group-vertical' : 'btn-group';

			if ( $label ) {
				echo "<label for='{$id}' class='form-label'>{$label}</label>";
			}

			?>
            <div class="<?php echo $orientation; ?> wpp-button-group" role="group" aria-label="<?php echo $label; ?>">
				<?php foreach ( $options as $option_value => $option_label ):
					$option_id = $id . '_' . sanitize_key( $option_value );
					$active = ( $value === (string) $option_value ) ? 'active' : '';
					?>
                    <button type="button"
                            class="btn btn-outline-primary <?php echo $active; ?>"
                            data-value="<?php echo esc_attr( $option_value ); ?>"
                            aria-pressed="<?php echo $active ? 'true' : 'false'; ?>">
						<?php echo esc_html( $option_label ); ?>
                    </button>
				<?php endforeach; ?>
            </div>

            <!-- Скрытое поле для отправки формы -->
            <input type="hidden"
                   id="<?php echo $id; ?>"
                   name="<?php echo $name; ?>"
                   value="<?php echo $value; ?>">

			<?php if ( $description ): ?>
                <small class="form-text text-muted"><?php echo $description; ?></small>
			<?php endif; ?>

			<?php
			$this->render_wrapper_end();
		}
	}
endif;