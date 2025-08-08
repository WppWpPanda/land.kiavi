<?php
/**
 * WPP Loan Application - Step 9 (Thank You)
 *
 * Renders a thank you page after form submission.
 * Displays all submitted data from previous steps using field labels and formatted values.
 * Does not display hidden fields like 'step' or other internal identifiers.
 *
 * @package WPP_Loan_Application
 * @subpackage Shortcodes
 * @since 1.0.0
 * @author WP Panda <panda@wp-panda.pro>
 * @license GPL-2.0-or-later
 * @link https://wp-panda.pro
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the final step of the loan application with summary of all previous data
 *
 * @return string HTML output
 */
function wpp_render_loan_thanks() {

	ob_start();

	// Check required field classes
	if (
		! class_exists( 'WPP_Content_Field' ) ||
		! class_exists( 'WPP_Button_Field' )
	) {
		return '<div class="alert alert-danger">Required field classes are not available.</div>';
	}

	// Get data from all previous steps (steps 1–8)
	/*$steps_data = array();
	for ( $i = 1; $i <= 8; $i ++ ) {
		$step_data = WPP_Loan_Session_Handler::get_step_data( $i );
		if ( ! empty( $step_data ) ) {
			$steps_data[ $i ] = $step_data;
		}
	}*/

	$steps_data  = $GLOBALS['wpp_raw_data'];


/*	if ( empty( $steps_data ) ) {

		*/?><!--
        <script>
            window.location.href = " <?php /*echo get_home_url(); */?>";
        </script>
		--><?php
/*        return false;
	}*/



	?>
    <div class="container mt-4">

        <!-- Thank You Message -->
        <div class="row">
            <div class="col-md-12 text-center">
                <h1 class="mb-3"><?php echo 'Thank You!'; ?></h1>
                <p class="lead mb-4"><?php echo 'Your loan application has been successfully submitted and will be reviewed shortly.'; ?></p>
                <p><?php echo 'A member of our team will contact you soon to discuss next steps.'; ?></p>
                <p><strong><?php echo 'If you have any questions, feel free to reach out at any time.'; ?></strong></p>
            </div>
        </div>

        <!-- Debug Mode -->
		<?php if ( defined( 'WPP_LOAN_DEV_MODE' ) && WPP_LOAN_DEV_MODE ): ?>
            <pre style="background:#f9f9f9; padding:1rem; border:1px solid #ccc;">
                <strong><?php echo 'All Data from Steps 1–8:'; ?></strong>
                <?php echo htmlspecialchars( json_encode( $steps_data, JSON_PRETTY_PRINT ) ); ?>
            </pre>
		<?php endif; ?>

        <!-- Application Summary -->
        <div class="row mt-5">
           <!-- <div class="col-md-12">
                <h2 class="mb-4"><?php /*echo 'Application Summary'; */?></h2>
            </div>-->

			<?php foreach ( $steps_data as $step_number => $step_data ):

				if ( ! empty( $step_data ) ): ?>
                    <div class="col-md-12">
                        <h3 class="mt-4 mb-3">
							<?php echo 'Step ' . esc_html( $step_number ); ?>
                        </h3>
                        <div class="card p-3 mb-4">
                            <div class="row">
								<?php
								// Получаем конфигурацию полей этого шага
								$step_form_fields = isset( $step_data['formFields'] ) ? $step_data['formFields'] : [];

								foreach ( $step_data as $key => $value ):

									// ❌ Пропускаем все поля типа "hidden"
									$field_config = isset( $step_form_fields[ $key ] ) ? $step_form_fields[ $key ] : [];
									if ( ! empty( $field_config['element_type'] ) && $field_config['element_type'] === 'hidden' ) {
										continue;
									}

									// Также пропускаем ключ 'step', если он есть
									if ( $key === 'step_identifier' ) {
										continue;
									}

									// Пропускаем пустые значения
									if ( empty( $value ) ) {
										continue;
									}

									// Формируем label из 'label' или делаем из ключа
									$label = ! empty( $field_config['label'] ) ?
										esc_html( $field_config['label'] ) :
										ucwords( str_replace( '_', ' ', esc_html( $key ) ) );

									// Форматируем значение
									$display_value = $value;


									$functionName = "wpp_step_config_$step_number";
									if ( function_exists( $functionName ) ) {
										$step_config = $functionName(); // Вызов функции
									}


									if ( ! empty( $step_config[ $key ]['element_type'] ) && $step_config[ $key ]['element_type'] === 'money' ) {
										$cleaned       = preg_replace( '/[^0-9.]/', '', $value );
										$display_value = is_numeric( $cleaned ) ? format_dollar( (float) $cleaned ) : esc_html( $value );
									} elseif ( $step_config[ $key ]['type'] === 'select' || $step_config[ $key ]['type'] === 'button_group' ) {
										$display_value = $step_config[ $key ]['options'][ $value ];
									} elseif ( $value === 'yes' || ( (int) $value === 1 && ( $step_config[ $key ]['type'] === 'checkbox' || $step_config[ $key ]['type'] === 'radio' ) ) ) {
										$display_value = 'Yes';
									} elseif ( $value === 'no' || ( (int) $value === 0 && ( $step_config[ $key ]['type'] === 'checkbox' || $step_config[ $key ]['type'] === 'radio' ) ) ) {
										$display_value = 'No';
									} elseif ( is_array( $value ) ) {
										$display_value = implode( ', ', array_filter( $value ) );
									}

									?>
                                    <div class="col-md-6 mb-2">
                                        <strong><?php echo esc_html( $label ); ?>:</strong>
                                        <span class="ms-2"><?php echo esc_html( $display_value ); ?></span>
                                    </div>
								<?php endforeach; ?>
                            </div>
                        </div>
                    </div>
				<?php endif; ?>
			<?php endforeach; ?>
        </div>

        <!-- Go Back Button -->
        <div class="row mt-4 mb-5">
            <div class="col-md-12 text-center">
				<?php
				$form_fields = [];

				foreach ( $form_fields as $name => $config ) {
					$class_name = 'WPP_' . ucfirst( $config['type'] ) . '_Field';

					if ( class_exists( $class_name ) ) {
						$field = new $class_name( array_merge( $config, array( 'name' => $name ) ) );
						$field->render();
					}
				}
				?>
            </div>
        </div>

    </div>
	<?php
	$content = ob_get_clean();



	return $content;
}

add_shortcode( 'wpp_render_loan_thanks', 'wpp_render_loan_thanks' );


/**
 * Formats a number into dollar format
 *
 * @param float|int $amount
 *
 * @return string
 */
function format_dollar( $amount ) {
	return '$' . number_format( (float) $amount, 2, '.', ',' );
}


/**
 * Запись В ьтаблцу и очистка сессии
 * @return void
 */
function wpp_thanks_init() {
	if( is_page('completed')) {

		// Get data from all previous steps (steps 1–8)
		$steps_data = array();
		for ( $i = 1; $i <= 8; $i ++ ) {
			$step_data = WPP_Loan_Session_Handler::get_step_data( $i );
			if ( ! empty( $step_data ) ) {
				$steps_data[ $i ] = $step_data;
			}
		}

       /* if(empty($steps_data)) {
            wp_safe_redirect(get_home_url());
	        exit;
        }*/

        $GLOBALS['wpp_raw_data'] = $steps_data;

        /**
		 * Сохраняем данные в сырую сессию
		 */
		wpp_save_session_to_database_raw();
		// Очищаем сессию пользователя
		WPP_Loan_Session_Handler::clear_all();
    }
}

add_action('wp', 'wpp_thanks_init');