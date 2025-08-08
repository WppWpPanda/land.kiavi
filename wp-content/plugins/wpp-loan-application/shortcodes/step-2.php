<?php
/**
 * WPP Loan Application - Step 2
 *
 * Renders the second step of the loan application form.
 * Allows users to create a new entity or select from existing profiles.
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


function wpp_step_config_2() {
	/**
	 * Configuration array for all fields in this step
	 *
	 * Fields include:
	 * - Entity Selector (select)
	 * - Entity Name (text)
	 * - Entity Type (select)
	 * - Experience (radio)
	 * - Guarantor Header (content)
	 * - First Name (text)
	 * - Last Name (text)
	 * - Suffix (select)
	 * - Go Back Button
	 * - Next Step Button
	 * - Hidden Step Identifier
	 *
	 * Conditional fields only appear when 'entity_selector' is set to 'new_entry'
	 */
	$form_fields = [
		'entity_selector' => [
			'type'    => 'select',
			'name'    => 'entity_selector',
			'label'   => 'Entity',
			'options' => [
				''           => 'Search for an Entity',
				'new_entry'  => '-- Create New Entity --',
				'existing_1' => 'Existing Entity #1',
				'existing_2' => 'Existing Entity #2'
			],
			'width'   => 'full',
            'required' => true,
		],

		'entity_name' => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'entity_name',
			'label'        => 'Entity Name',
			'width'        => '1/2',
			'conditional'  => [ 'entity_selector' => [ 'new_entry' ] ]
		],

		'entity_type' => [
			'type'        => 'select',
			'name'        => 'entity_type',
			'label'       => 'Entity Type',
			'options'     => [
				''            => 'Select Entity Type',
				'individual'  => 'Individual',
				'corporation' => 'Corporation',
				'llc'         => 'LLC',
				'partnership' => 'Partnership',
				'trust'       => 'Trust'
			],
			'width'       => '1/2',
			'conditional' => [ 'entity_selector' => [ 'new_entry' ] ]
		],

		'experience_header' => [
			'type'        => 'content',
			'name'        => 'experience_header',
			'label'       => 'Experience',
			'content'     => '<p>How many flips have been completed under this entity in the last 2 years?</p>',
			'width'       => 'full',
			'conditional' => [ 'entity_selector' => [ 'new_entry' ] ]
		],

		'experience' => [
			'type'        => 'radio',
			'name'        => 'experience',
			'label'       => 'How many flips have been completed under this entity in the last 2 years?',
			'options'     => [
				'none' => 'None',
				'1-4'  => '1-4 properties',
				'5+'   => '5 or more properties'
			],
			'width'       => 'full',
			'conditional' => [ 'entity_selector' => [ 'new_entry' ] ]
		],

		'guarantor_header' => [
			'type'        => 'content',
			'name'        => 'guarantor_header',
			'label'       => 'Guarantor',
			'content'     => '
                        <p>If you plan to have no guarantor for the loan, please select a credit qualifying individual from this selected entity.</p>
                        <p>You can choose your personal guarantee status for this loan on the rate calculator.</p>
                    ',
			'width'       => 'full',
			'conditional' => [ 'entity_selector' => [ 'new_entry' ] ]
		],

		/*'search_all_profiles' => [
			'type' => 'checkbox',
			'name' => 'search_all_profiles',
			'label' => 'Search All Profiles',
			'width' => 'full',
			'conditional' => ['entity_selector' => ['new_entry']]
		],*/

		'first_name' => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'first_name',
			'label'        => 'First Name',
			'width'        => '1/3',
			'conditional'  => [ 'entity_selector' => [ 'new_entry' ] ]
		],

		'last_name' => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'last_name',
			'label'        => 'Last Name',
			'width'        => '1/3',
			'conditional'  => [ 'entity_selector' => [ 'new_entry' ] ]
		],

		'suffix' => [
			'type'        => 'select',
			'name'        => 'suffix',
			'label'       => 'Suffix',
			'options'     => [
				''    => 'Select Suffix',
				'Jr.' => 'Jr.',
				'Sr.' => 'Sr.',
				'II'  => 'II',
				'III' => 'III',
				'IV'  => 'IV',
				'V'   => 'V'
			],
			'width'       => '1/3',
			'conditional' => [ 'entity_selector' => [ 'new_entry' ] ]
		],

		'go_back_button' => [
			'type'         => 'button',
			'element_type' => 'link_button',
			'name'         => 'go_back',
			'label'        => 'Go Back',
			'btn_class'    => 'btn-secondary',
			'href'         => '/step/personal',
			'width'        => '1/2'
		],

		'next_step_button' => [
			'type'         => 'button',
			'classes'      => [ 'text-end' ],
			'element_type' => 'button',
			'name'         => 'next_step',
			'label'        => 'Next Step',
			'btn_class'    => 'btn-primary',
			'width'        => '1/2'
		],

		'step_identifier' => [
			'type'         => 'text',
			'element_type' => 'hidden',
			'name'         => 'step',
			'default'      => '2',
			'width'        => 'full',
			'conditional'  => [ 'entity_selector' => [ 'new_entry' ] ]
		]
	];

	return $form_fields;
}

/**
 * Renders the "Start a New Loan" step of the loan application.
 *
 * This step includes:
 * - Entity selection (new or existing)
 * - Conditional fields for creating a new entity
 * - Guarantor information (conditionally shown)
 * - First Name, Last Name, Suffix, etc.
 *
 * Uses universal form builder classes: WPP_Select_Field, WPP_Text_Field, etc.
 *
 * @return string HTML output or error message if dependencies are missing
 * @since 1.0.0
 */
function wpp_render_loan_step_2() {
	ob_start();
	?>
    <div class="container">
        <div class="row">
            <h2>Start a New Loan</h2>
            <p>Get started by creating a new entity and guarantor or selecting from existing profiles.</p>
        </div>

		<?php
		wpp_loan_form_debug_data( 1 );
		wpp_render_form( 'loan-form-step-2', wpp_step_config_2() );
		?>
    </div>
	<?php

	return ob_get_clean();
}

add_shortcode( 'wpp_loan_application_step_2', 'wpp_render_loan_step_2' );