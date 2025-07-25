<?php
/**
 * WPP Loan Application - Step 5
 *
 * Renders the "Confirm Terms" step of the loan application form.
 * Requires users to confirm citizenship and occupancy status before proceeding.
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

function wpp_step_config_5() {
	/**
	 * Configuration array for all fields in this step
	 *
	 * Fields include:
	 * - terms_confirmation (content)
	 * - us_citizen_checkbox (checkbox)
	 * - not_living_checkbox (checkbox)
	 * - errors_msg (content)
	 * - go_back_button (button)
	 * - next_step_button (button)
	 * - step_identifier (hidden text)
	 */
	$form_fields = [
		'terms_confirmation' => [
			'type'    => 'content',
			'name'    => 'terms_confirmation',
			'content' => '
                    <h1>Please confirm that the following statements are true:</h1>
                ',
			'width'   => 'full'
		],

		'us_citizen_checkbox' => [
			'type'     => 'checkbox',
			'name'     => 'us_citizen',
			'label'    => 'I am a US citizen or a Permanent Resident.',
			'required' => true,
			'width'    => 'full'
		],

		'not_living_checkbox' => [
			'type'     => 'checkbox',
			'name'     => 'not_living',
			'label'    => 'I will not be living in the property.',
			'required' => true,
			'width'    => 'full'
		],

		'errors_msg' => [
			'type'    => 'content',
			'name'    => 'errors_msg',
			'content' => '<div class="wpp-errors-wrap"></div>',
			'width'   => 'full'
		],

		'go_back_button' => [
			'type'         => 'button',
			'element_type' => 'link_button',
			'name'         => 'go_back',
			'label'        => 'Go Back',
			'btn_class'    => 'btn btn-secondary mt-3',
			'href'         => '/estimate-rate/',
			'width'        => '1/2'
		],

		'next_step_button' => [
			'type'         => 'button',
			'element_type' => 'button',
			'name'         => 'next_step',
			'classes'      => [ 'text-end' ],
			'label'        => 'Next Step',
			'btn_class'    => 'btn btn-primary mt-3',
			'width'        => '1/2'
		],

		'step_identifier' => [
			'type'         => 'text',
			'element_type' => 'hidden',
			'name'         => 'step',
			'default'      => '5',
			'width'        => 'full'
		],
	];

	return $form_fields;
}

/**
 * Renders Step 5: Confirm Terms
 *
 * This step includes:
 * - Checkbox for US Citizen or Permanent Resident confirmation
 * - Checkbox for property occupancy (not living in it)
 * - Go Back and Next Step buttons
 * - Hidden step identifier
 *
 * Uses universal field builder classes:
 * - WPP_Text_Field
 * - WPP_Button_Field
 * - WPP_Content_Field
 * - WPP_Checkbox_Field
 *
 * @return string HTML output or error message if dependencies are missing
 * @since 1.0.0
 */
function wpp_render_loan_step_5() {
	ob_start();
	?>
    <div class="container">
		<?php
		wpp_loan_form_debug_data( 4 );
		wpp_render_form( 'loan-form-step-5', wpp_step_config_5() );
		?>
    </div>
	<?php
	return ob_get_clean();
}

add_shortcode( 'wpp_loan_application_step_5', 'wpp_render_loan_step_5' );