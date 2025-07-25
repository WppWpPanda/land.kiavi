<?php
/**
 * WPP Loan Application - Step 1
 *
 * @package WPP_Loan_Application
 * @subpackage Shortcodes
 * @version 1.0.0
 * @author WP Panda <panda@wp-panda.pro>
 * @license GPL-2.0-or-later
 * @link https://wp-panda.pro
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the first step of the loan application form.
 *
 * This step asks the user about their real estate investment type:
 * - Bridge / Fix and Flip / Fix to Rent
 * - New Construction (with additional details in an accordion)
 *
 * Uses field builder classes:
 * - WPP_Button_Group_Field
 * - WPP_Accordion_Field
 * - WPP_Text_Field
 * - WPP_Button_Field
 *
 * @return string HTML output or error message if dependencies are missing
 * @since 1.0.0
 */

function wpp_step_config_1(){
	/**
	 * Form fields configuration array
	 *
	 * Each key represents a unique field name.
	 * Fields are rendered using WPP_Form_Builder via their class names.
	 *
	 * @var array
	 */
	$form_fields = [
		'investment_header'        => [
			'type'    => 'content',
			'name'    => 'investment_header',
			'label'   => '',
			'content' => '
                <h1>What kind of real estate investment are you considering?</h1>
            ',
			'width'   => 'full'
		],
		'investment_type'          => [
			'type'        => 'button_group',
			'name'        => 'investment_type',
			'label'       => '',
			'options'     => [
				'bridge_fix_flip_rent' => 'Bridge / Fix and Flip / Fix to Rent',
				'new_construction'     => 'New Construction *'
			],
			'orientation' => 'vertical',
			'width'       => 'full'
		],
		'new_construction_details' => [
			'type'    => 'accordion',
			'name'    => 'new_construction_details',
			'title'   => '* What is a New Construction deal at Lendvent?',
			'content' => '
                <ul>
                    <li>A vacant lot with ground-up development</li>
                    <li>An addition to existing improvements with an increase of gross living area greater than 100%</li>
                    <li>Structural replacement of the roof and more than one exterior wall while retaining foundation</li>
                    <li>Structural replacement of the roof and all interior framing</li>
                </ul>
            ',
			'open'    => false,
		],
		'step_identifier'          => [
			'type'         => 'text',
			'element_type' => 'hidden',
			'name'         => 'step',
			'default'      => '1'
		]
	];
    return $form_fields;
}
function wpp_render_loan_step_1() {
	ob_start();

	$form_fields = wpp_step_config_1();

	?>
    <div class="container">
		<?php wpp_render_form( 'loan-form-step-1', $form_fields ); ?>
    </div>
	<?php

	return ob_get_clean();
}

// Register shortcode for step 1
add_shortcode( 'wpp_loan_application_step_1', 'wpp_render_loan_step_1' );