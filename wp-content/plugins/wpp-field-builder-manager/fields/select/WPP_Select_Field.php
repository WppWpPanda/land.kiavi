<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPP_Select_Field' ) && class_exists( 'WPP_Form_Field' ) ) :
	class WPP_Select_Field extends WPP_Form_Field {

		private static $enqueue_select2 = false;

		public function __construct( $args = [] ) {
			parent::__construct( $args );

			// Если поле должно быть Select2 — запоминаем, что нужно подключить ассеты
			if ( ! empty( $this->args['select2'] ) ) {
				self::$enqueue_select2 = true;

				add_action( 'wp_footer', [ $this, 'enqueue_select2_assets' ] );
				add_action( 'admin_footer', [ $this, 'enqueue_select2_assets' ] );
			}

			add_action( 'wp_footer', [ $this, 'enqueue_select_assets' ] );
		}

		public function enqueue_select_assets() {


			wp_enqueue_style(
				'wpp-select',
				WPP_FIELD_BUILDER_URL . 'fields/select/style.css',
				[],
				file_exists( WPP_FIELD_BUILDER_PATH . 'fields/select/style.css' )
					? filemtime( WPP_FIELD_BUILDER_PATH . 'fields/select/style.css' )
					: time(),
				'all'
			);

		}

		public function enqueue_select2_assets() {
			if ( self::$enqueue_select2 ) {
				wp_enqueue_style(
					'select2',
					'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'
				);

				wp_enqueue_script(
					'select2',
					'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
					[ 'jquery' ],
					null,
					true
				);


				wp_enqueue_style(
					'wpp-select',
					WPP_FIELD_BUILDER_URL . 'fields/select/style.css',
					[],
					file_exists( WPP_FIELD_BUILDER_PATH . 'fields/select/style.css' )
						? filemtime( WPP_FIELD_BUILDER_PATH . 'fields/select/style.css' )
						: time(),
					'all'
				);

				?>
                <script>
                    jQuery(document).ready(function ($) {
                        $('.wpp-select2-init').each(function () {
                            const $select = $(this);

                            if (!$select.hasClass('select2-hidden-accessible')) {
                                $select.select2({
                                    width: '100%',
                                    placeholder: "Выберите значение",
                                    allowClear: true,
                                    language: "ru"
                                });
                            }
                        });
                    });
                </script>
				<?php
			}
		}

		public function render() {
			$this->render_wrapper_start();

			$name     = esc_attr( $this->get_name() );
			$id       = sanitize_key( $this->args['name'] );
			$selected = $this->get_value();
			if ( ! empty( $this->args['presets'] ) && $this->args['presets'] === 'states' ) {
				$options = wpp_fb_select_states();
			} else {
				$options = isset( $this->args['options'] ) ? (array) $this->args['options'] : [];
			}
			$is_multiple = ! empty( $this->args['multiple'] );
			$is_select2  = ! empty( $this->args['select2'] );

			// Поддержка множественного выбора
			$field_name = $is_multiple ? $name . '[]' : $name;

			// Класс для JS-инициализации
			$select_class = $is_select2 ? 'form-control wpp-select2-init' : 'form-control';

			// Вывод label, если задан
			$label = ! empty( $this->args['label'] ) ? '<label for="' . $id . '" class="form-label">' . esc_html( $this->args['label'] ) . '</label>' : '';

			echo $label; // ← выводим label

			?>
            <select name="<?php echo $field_name; ?>"
                    id="<?php echo $id; ?>"
                    class="<?php echo esc_attr( $select_class ); ?>"
				<?php echo $is_multiple ? 'multiple' : ''; ?>
                    <?php if($this->args['required']) { echo ' required="required"'; } ?>
            >
				<?php foreach ( $options as $value => $label ):
					$isSelected = $is_multiple
						? in_array( $value, (array) $selected )
						: $value == $selected;
					?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $isSelected ); ?>>
						<?php echo esc_html( $label ); ?>
                    </option>
				<?php endforeach; ?>
            </select>
			<?php

			$this->render_description();
			$this->render_wrapper_end();
		}
	}
endif;

function wpp_fb_select_states() {
	return [
		''   => 'Select State',
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraska',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PA' => 'Pennsylvania',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia',
		'WI' => 'Wisconsin',
		'WY' => 'Wyoming'
	];
}