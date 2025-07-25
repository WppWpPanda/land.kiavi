<?php
/**
 * WPP Loan Application - Step 3
 *
 * Renders the "Property Address" step of the loan application form.
 * Allows users to enter property address details including:
 * - Address Line 1 & 2
 * - City
 * - State
 * - ZIP Code
 *
 * @package WPP_Loan_Application
 * @subpackage Shortcodes
 * @since 1.0.0
 * @author WP Panda <panda@wp-panda.pro>
 * @license GPL-2.0-or-later
 * @link https://wp-panda.pro
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

function wpp_step_config_3() {
	/**
	 * Configuration array for all fields in this step
	 *
	 * Fields include:
	 * - Address Header (content)
	 * - Address Line 1 (text)
	 * - Address Line 2 (text)
	 * - City (text)
	 * - State (select)
	 * - Zip Code (text)
	 * - Skip Button
	 * - Next Step Button
	 * - Hidden Step Identifier
	 */
	$form_fields = [
		'address_head' => [
			'type' => 'content',
			'element_type' => 'text',
			'content' => '<h1>What is the address of the property you would like to purchase?</h1>',
			'name' => 'address_head',
			'label' => '',
			'width' => 'full'
		],
		'address_line_1' => [
			'type' => 'text',
			'element_type' => 'text',
			'name' => 'address_line_1',
			'label' => 'Address Line 1',
			'width' => '1/2'
		],
		'address_line_2' => [
			'type' => 'text',
			'element_type' => 'text',
			'name' => 'address_line_2',
			'label' => 'Address Line 2',
			'width' => '1/2'
		],
		'city' => [
			'type' => 'text',
			'element_type' => 'text',
			'name' => 'city',
			'label' => 'City',
			'width' => '1/3'
		],
		'state' => [
			'type' => 'select',
			'name' => 'state',
			'label' => 'State',
			'options' => [
				'' => 'Select State',
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
			],
			'width' => '1/3'
		],
		'zip' => [
			'type' => 'text',
			'element_type' => 'text',
			'name' => 'zip',
			'label' => 'Zip',
			'width' => '1/3'
		],
		'skip_button' => [
			'type' => 'button',
			'element_type' => 'link_button',
			'name' => 'skip_step',
			'label' => 'Go Back',
			'btn_class' => 'btn btn-secondary mt-3',
			'href' => '/property', // или другой шаг
			'width' => '1/2'
		],
		'next_step_button' => [
			'type' => 'button',
			'element_type' => 'button',
			'classes' => ['text-end'],
			'name' => 'next_step',
			'label' => 'Next Step',
			'btn_class' => 'btn btn-primary mt-3',
			'width' => '1/2'
		],
		'step_identifier' => [
			'type' => 'text',
			'element_type' => 'hidden',
			'name' => 'step',
			'default' => '3',
			'width' => 'full'
		]
	];

    return $form_fields;
}
/**
 * Renders the third step of the loan application: Property Address.
 *
 * This step asks for:
 * - Full property address
 * - City, State, ZIP
 * - Includes Skip and Next Step buttons
 *
 * Uses universal form builder classes: WPP_Text_Field, WPP_Select_Field, etc.
 *
 * @since 1.0.0
 * @return string HTML output or error message if dependencies are missing
 */
function wpp_render_loan_step_3() {
	ob_start();

	?>
    <div class="container">

	    <?php
	    wpp_loan_form_debug_data( 2 );
		wpp_render_form('loan-form-step-3',wpp_step_config_3());
		?>
    </div>
	<?php

	return ob_get_clean();
}

add_shortcode('wpp_loan_application_step_3', 'wpp_render_loan_step_3');