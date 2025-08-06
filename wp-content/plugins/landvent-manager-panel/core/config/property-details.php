<?php
/**
 * Property Details Step Configuration for Loan Management Plugin
 *
 * This file defines the structure and rendering logic for the "Property Details" step
 * in a multi-step loan application form. It organizes property-related data input
 * into accordion sections: Main Property, Existing Mortgages, Other Properties, and Insurance.
 *
 * The form fields are dynamically pre-filled using loan data retrieved via
 * external functions (`wpp_get_loan_data_r`, `wpp_field_value`) defined elsewhere.
 *
 * @package           WPP_Loan_Management
 * @subpackage        Step_Configuration
 * @since             1.0.0
 * @author            WP_Panda <panda@wp-panda.pro>
 * @copyright         2025 WP_Panda
 * @license           GPL-2.0-or-later
 *
 * @link              https://developer.wordpress.org/plugins/
 * @link              https://www.php.net/manual/en/
 */

defined('ABSPATH') || exit;

/**
 * Generates configuration for the main property section.
 *
 * Builds a structured array of form fields for the primary property, including:
 * - Address (street, city, state, zip, country)
 * - Legal description
 * - Property type and occupancy status
 *
 * Uses `wpp_get_loan_data_r()` to retrieve saved values and `wpp_field_value()`
 * to safely extract field data with fallbacks.
 *
 * @since 1.0.0
 *
 * @return array Form configuration compatible with WPP form renderer.
 *
 * @see wpp_get_loan_data_r()        For source of persisted loan data.
 * @see wpp_field_value()            For safe value extraction.
 * @see https://developer.wordpress.org/plugins/settings/settings-api/ For field patterns.
 *
 * @example
 *     $fields = wpp_step_config_main_property();
 *     foreach ($fields as $field) {
 *         render_field($field);
 *     }
 */
function wpp_step_config_main_property() {
	// Retrieve existing loan data from global context or database
	$data = wpp_get_loan_data_r();

	$form_fields = [
		'property_address'   => [
			'type'   => 'fields_block',
			'name'   => 'property_address',
			'label'  => 'Property Address',
			'fields' => [
				'property_street'  => [
					'type'         => 'text',
					'classes'      => ['wpp-top-label'],
					'element_type' => 'text',
					'name'         => 'property_street',
					'label'        => 'Street',
					'width'        => '2/3',
					'default'      => wpp_field_value('property_street', $data),
				],
				'property_city'    => [
					'type'         => 'text',
					'classes'      => ['wpp-top-label'],
					'element_type' => 'text',
					'name'         => 'property_city',
					'label'        => 'City',
					'width'        => '1/3',
					'default'      => wpp_field_value('property_city', $data),
				],
				'property_country' => [
					'type'         => 'text',
					'classes'      => ['wpp-top-label'],
					'element_type' => 'text',
					'name'         => 'property_country',
					'label'        => 'Country',
					'width'        => '1/3',
					'default'      => wpp_field_value('property_country', $data),
				],
				'property_state'   => [
					'type'    => 'select',
					'classes' => ['wpp-top-label'],
					'name'    => 'property_state',
					'label'   => 'State',
					'presets' => 'states', // Preset: US states dropdown
					'width'   => '1/3',
					'default' => wpp_field_value('property_state', $data),
				],
				'property_zip'     => [
					'type'         => 'text',
					'classes'      => ['wpp-top-label'],
					'element_type' => 'text',
					'name'         => 'property_zip',
					'label'        => 'Zip',
					'width'        => '1/3',
					'default'      => wpp_field_value('property_zip', $data),
				],
				'property_note'    => [
					'type'    => 'content',
					'name'    => 'property_note',
					'width'   => 'full',
					'content' => '<i>Note: the borrower\'s address seems different from the property address.</i>',
				],
			],
		],
		'legal_description'  => [
			'type'         => 'textarea',
			'element_type' => 'text',
			'name'         => 'legal_description',
			'label'        => 'Legal Description',
			'width'        => 'full',
			'default'      => wpp_field_value('legal_description', $data),
		],
		'property_type'      => [
			'type'    => 'select',
			'name'    => 'property_type',
			'label'   => 'Property Type',
			'options' => [
				'other'          => 'Other',
				'detached'       => 'Detached',
				'semi_detached'  => 'Semi-Detached',
				'row_town_house' => 'Row/Town House',
				'apartment'      => 'Apartment',
				'mobile'         => 'Mobile',
				'stacked'        => 'Stacked',
				'commercial'     => 'Commercial',
				'land'           => 'Land',
				'condo'          => 'Condo',
			],
			'width'   => 'full',
			'default' => wpp_field_value('property_type', $data),
		],
		'property_occupancy' => [
			'type'    => 'select',
			'name'    => 'property_occupancy',
			'label'   => 'Occupancy',
			'options' => [
				''               => 'Select Occupancy',
				'owner_occupied' => 'Owner-Occupied',
				'rental'         => 'Rental',
				'owner_tenanted' => 'Owner-Occupied/Tenanted',
				'second_home'    => 'Second Home',
				'vacant'         => 'Vacant',
			],
			'width'   => 'full',
			'default' => wpp_field_value('property_occupancy', $data),
		],
	];

	/**
	 * Filters the main property form configuration.
	 *
	 * Allows third-party extensions to modify or enhance the form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_fields The field configuration array.
	 */
	return apply_filters('wpp/form/step_config/main_property', $form_fields);
}

/**
 * Generates configuration for existing mortgages using a repeater.
 *
 * Allows borrowers to list up to 5 existing liens on the property.
 * Each mortgage includes financial terms, type, and status.
 *
 * @since 1.0.0
 *
 * @return array Repeater-based configuration for mortgage entries.
 *
 * @see https://developer.wordpress.org/reference/functions/apply_filters/ For extensibility.
 *
 * @example
 *     $mortgages = wpp_step_config_existing_mortgages();
 */
function wpp_step_config_existing_mortgages() {
	$data = wpp_get_loan_data_r();

	$form_fields_repeater = [
		'mortgage_holder'           => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'mortgage_holder',
			'label'        => 'Mortgage Holder',
			'width'        => 'full',
		],
		'mortgage_type'             => [
			'type'    => 'select',
			'name'    => 'mortgage_type',
			'label'   => 'Mortgage Type',
			'options' => [
				'first'  => 'First',
				'second' => 'Second',
				'third'  => 'Third',
				'four'   => 'Four',
				'fifth'  => 'Fifth',
				'sixth'  => 'Sixth',
				'seven'  => 'Seventh',
			],
			'width'   => '1/2',
		],
		'postponed_checkbox'        => [
			'type'    => 'checkbox',
			'classes' => ['wpp-no-label-inverse no-left'],
			'name'    => 'postponed',
			'label'   => 'postponed',
			'width'   => '1/4',
		],
		'co_lending_checkbox'       => [
			'type'    => 'checkbox',
			'classes' => ['wpp-no-label-inverse no-left'],
			'name'    => 'co_lending',
			'label'   => 'co-Lending',
			'width'   => '1/4',
		],
		'face_value'                => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'face_value',
			'label'        => 'Face Value',
			'width'        => 'full',
		],
		'balance'                   => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'balance',
			'label'        => 'Balance',
			'width'        => 'full',
		],
		'payment'                   => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'payment',
			'label'        => 'Payment',
			'width'        => 'full',
		],
		'frequency'                 => [
			'type'    => 'select',
			'name'    => 'frequency',
			'label'   => 'Frequency',
			'options' => [
				'monthly'   => 'Monthly',
				'bi-weekly' => 'Bi-Weekly',
				'weekly'    => 'Weekly',
			],
			'width'   => 'full',
		],
		'maturity_date'             => [
			'type'  => 'datepicker',
			'name'  => 'maturity_date',
			'label' => 'Maturity Date',
			'width' => 'full',
		],
		'rate_type'                 => [
			'type'    => 'select',
			'name'    => 'rate_type',
			'label'   => 'Rate Type',
			'options' => [
				''                => 'Select Rate Type',
				'fixed'           => 'Fixed',
				'variable'        => 'Variable',
				'capped_variable' => 'Capped Variable',
				'buydown'         => 'Buydown',
			],
			'width'   => 'full',
		],
		'term_type'                 => [
			'type'    => 'select',
			'name'    => 'term_type',
			'label'   => 'Term Type',
			'options' => [
				''                  => 'Select Term Type',
				'fully_closed'      => 'Fully Closed',
				'closed_variable'   => 'Closed Variable',
				'closed_then_open'  => 'Closed, Then Open',
				'fully_open'        => 'Fully Open',
				'minimum_then_open' => 'Minimum, Then Open',
			],
			'width'   => 'full',
		],
		'interest_rate'             => [
			'type'         => 'text',
			'element_type' => 'percentage',
			'name'         => 'interest_rate',
			'label'        => 'Interest Rate',
			'width'        => 'full',
		],
		'prepayment_penalty'        => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'prepayment_penalty',
			'label'        => 'Prepayment Penalty',
			'width'        => 'full',
		],
		'in_arrears_checkbox_block' => [
			'type'   => 'fields_block',
			'name'   => 'in_arrears_checkbox_block',
			'label'  => 'In arrears?',
			'fields' => [
				'in_arrears_checkbox' => [
					'type'    => 'checkbox',
					'classes' => ['wpp-no-label-inverse no-left'],
					'name'    => 'in_arrears',
					'label'   => 'Yes',
					'width'   => '1/2',
				],
			],
		],
	];

	$form_fields = [
		[
			'type'        => 'repeater',
			'name'        => 'existing_mortgages',
			'title'       => '',
			'button_text' => 'Add a mortgage',
			'min'         => 1,
			'max'         => 5,
			'fields'      => $form_fields_repeater,
			'default'     => !isset($data['existing_mortgages']) || empty(array_filter($data['existing_mortgages'])) ? '' : $data['existing_mortgages'],
		],
	];

	/**
	 * Filters the existing mortgages configuration.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_fields The repeater configuration.
	 */
	return apply_filters('wpp/form/step_config/existing_mortgages', $form_fields);
}

/**
 * Builds form fields for other properties owned by the borrower.
 *
 * Uses a super accordion to allow multiple property entries with:
 * - Address
 * - Estimated value
 * - Legal description
 * - Income and expenses (including HOA, heating, custom fees)
 *
 * @since 1.0.0
 *
 * @return array Configuration array for "Other Properties" section.
 *
 * @todo Implement conditional logic for partial discharge (currently commented).
 * @todo Add validation for monetary inputs.
 *
 * @example
 *     $props = wpp_step_config_other_properties();
 */
function wpp_step_config_other_properties() {
	$data = wpp_get_loan_data_r();

	$form_fields = [
		'other_info' => [
			'type'   => 'super_accordion',
			'name'   => 'other_properties',
			'title'  => '',
			'header' => '{other_properties_street}',
			'fields' => [
				'other_properties_blanket' => [
					'type'    => 'checkbox',
					'classes' => ['wpp-no-label-inverse no-left'],
					'name'    => 'other_properties_blanket',
					'label'   => 'blanket',
					'width'   => '2',
					'default' => wpp_field_value('other_properties_blanket', $data),
				],
				'other_properties_street'  => [
					'type'         => 'text',
					'classes'      => ['wpp-top-label'],
					'element_type' => 'text',
					'name'         => 'other_properties_street',
					'label'        => 'Street',
					'width'        => '4',
					'default'      => wpp_field_value('other_properties_street', $data),
				],
				'other_properties_city'    => [
					'type'         => 'text',
					'classes'      => ['wpp-top-label'],
					'element_type' => 'text',
					'name'         => 'other_properties_city',
					'label'        => 'City',
					'width'        => '2',
					'default'      => wpp_field_value('other_properties_city', $data),
				],
				'other_properties_country' => [
					'type'         => 'text',
					'classes'      => ['wpp-top-label'],
					'element_type' => 'text',
					'name'         => 'other_properties_country',
					'label'        => 'Country',
					'width'        => '2',
					'default'      => wpp_field_value('other_properties_country', $data),
				],
				'other_properties_state'   => [
					'type'    => 'select',
					'classes' => ['wpp-top-label'],
					'name'    => 'other_properties_state',
					'label'   => 'State',
					'presets' => 'states',
					'width'   => '2',
					'default' => wpp_field_value('other_properties_state', $data),
				],
				'other_properties_sep'     => [
					'type'    => 'content',
					'name'    => 'other_properties_sep',
					'content' => '',
					'width'   => '2',
					'default' => wpp_field_value('other_properties_sep', $data),
				],
				'other_properties_zip'     => [
					'type'         => 'text',
					'classes'      => ['wpp-top-label'],
					'element_type' => 'text',
					'name'         => 'other_properties_zip',
					'label'        => 'Zip',
					'width'        => '2',
					'default'      => wpp_field_value('other_properties_zip', $data),
				],
				'other_est_value'          => [
					'type'         => 'text',
					'classes'      => ['wpp-top-label'],
					'element_type' => 'money',
					'name'         => 'other_est_value',
					'label'        => 'Est. Value',
					'width'        => '4',
					'default'      => wpp_field_value('other_est_value', $data),
				],
				'other_legal_description'  => [
					'type'    => 'text',
					'classes' => ['wpp-top-label'],
					'name'    => 'other_legal_description',
					'label'   => 'Legal Description',
					'width'   => '4',
					'default' => wpp_field_value('other_legal_description', $data),
				],
				'monthly_rt'               => [
					'type'    => 'fields_block',
					'name'    => 'monthly_rt',
					'classes' => ['col-full'],
					'label'   => '',
					'width'   => 'full',
					'fields'  => [
						'monthly_sep'           => [
							'type'    => 'content',
							'name'    => 'monthly_sep',
							'label'   => '',
							'content' => '',
							'width'   => '2',
							'default' => wpp_field_value('monthly_sep', $data),
						],
						'monthly_rental_income' => [
							'type'         => 'text',
							'classes'      => ['wpp-top-label'],
							'element_type' => 'money',
							'name'         => 'monthly_rental_income',
							'label'        => 'Monthly Rental Income',
							'width'        => '5',
							'default'      => wpp_field_value('monthly_rental_income', $data),
						],
						'property_taxes'        => [
							'type'         => 'text',
							'classes'      => ['wpp-top-label'],
							'element_type' => 'money',
							'name'         => 'property_taxes',
							'label'        => 'Property Taxes',
							'width'        => '5',
							'default'      => wpp_field_value('property_taxes', $data),
						],
					],
				],
				'monthly_expenses'         => [
					'type'    => 'fields_block',
					'name'    => 'monthly_expenses',
					'classes' => ['col-full with-padding'],
					'label'   => 'Monthly Expenses<span class="mi-summ">$0.00</span>',
					'fields'  => [
						'hoa_fees' => [
							'classes'      => ['wpp-top-label'],
							'type'         => 'text',
							'element_type' => 'money',
							'name'         => 'hoa_fees',
							'label'        => 'HOA Fees',
							'width'        => '6',
							'default'      => wpp_field_value('hoa_fees', $data),
						],
						'heating'  => [
							'classes'      => ['wpp-top-label'],
							'type'         => 'text',
							'element_type' => 'money',
							'name'         => 'heating',
							'label'        => 'Heating',
							'width'        => '6',
							'default'      => wpp_field_value('heating', $data),
						],
					],
				],
				'op_fees'                  => [
					'type'    => 'repeater',
					'classes' => ['line-group no-border first-del'],
					'name'    => 'op_fees',
					'label'   => 'Custom Monthly Expenses',
					'default' => !isset($data['op_fees']) || empty(array_filter($data['op_fees'])) ? '' : $data['op_fees'],
					'fields'  => [
						'field_sep' => [
							'type'         => 'content',
							'element_type' => 'text',
							'name'         => 'field_sep',
							'content'      => '',
							'width'        => '2',
						],
						'field_1'   => [
							'type'         => 'text',
							'element_type' => 'text',
							'name'         => 'field_1',
							'placeholder'  => 'Field 1',
							'width'        => '5',
						],
						'field_2'   => [
							'type'         => 'text',
							'element_type' => 'money',
							'name'         => 'field_2',
							'placeholder'  => '$',
							'width'        => '5',
						],
					],
				],
			],
		],
	];

	/**
	 * Filters the "Other Properties" configuration.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_fields The configuration array.
	 */
	return apply_filters('wpp/form/step_config/other_properties', $form_fields);
}

/**
 * Returns insurance policy details form fields.
 *
 * @since 1.0.0
 *
 * @return array Field definitions for policy information.
 */
function wpp_form_fields_policy() {
	return [
		'policy_policy_issue_date'         => [
			'type'  => 'datepicker',
			'name'  => 'policy_policy_issue_date',
			'label' => 'Policy Issue Date',
			'width' => 'full',
		],
		'policy_property_address'          => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'policy_property_address',
			'label'        => 'Property Address',
			'value'        => '402 N George Street Millersville PA',
			'width'        => 'full',
		],
		'policy_insured_name'              => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'policy_insured_name',
			'label'        => 'Insured Name',
			'width'        => 'full',
		],
		'policy_policy_type'               => [
			'type'    => 'select',
			'name'    => 'policy_policy_type',
			'label'   => 'Policy Type',
			'options' => [
				''           => 'Select Policy Type',
				'none'       => 'None',
				'homeowners' => 'Homeowners Insurance',
				'renters'    => 'Renters Insurance',
				'umbrella'   => 'Umbrella Insurance',
			],
			'default' => 'none',
			'width'   => 'full',
		],
		'policy_policy_number'             => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'policy_policy_number',
			'label'        => 'Policy #',
			'width'        => 'full',
		],
		'policy_insurance_company'         => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'policy_insurance_company',
			'label'        => 'Insurance Company',
			'width'        => 'full',
		],
		'policy_eff_date'                  => [
			'type'  => 'datepicker',
			'name'  => 'policy_eff_date',
			'label' => 'Eff Date',
			'width' => 'full',
		],
		'policy_expiry_date'               => [
			'type'  => 'datepicker',
			'name'  => 'policy_expiry_date',
			'label' => 'Expiry Date',
			'width' => 'full',
		],
		'policy_coverage'                  => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'policy_coverage',
			'label'        => 'Coverage',
			'placeholder'  => '$0.00',
			'width'        => 'full',
		],
		'policy_deductible'                => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'policy_deductible',
			'label'        => 'Deductible',
			'placeholder'  => '$0.00',
			'width'        => 'full',
		],
		'policy_insurance_limit'           => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'policy_insurance_limit',
			'label'        => 'Insurance Limit',
			'placeholder'  => '$0.00',
			'width'        => 'full',
		],
		'policy_annual_cost'               => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'policy_annual_cost',
			'label'        => 'Annual Cost',
			'placeholder'  => '$0.00',
			'width'        => 'full',
		],
		'policy_stated_amount'             => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'policy_stated_amount',
			'label'        => 'Stated Amount',
			'placeholder'  => '$0.00',
			'width'        => 'full',
		],
		'policy_payment_date'              => [
			'type'  => 'datepicker',
			'name'  => 'policy_payment_date',
			'label' => 'Payment Date',
			'width' => 'full',
		],
		'policy_pay_on_behalf_of_borrower' => [
			'type'  => 'checkbox',
			'name'  => 'policy_pay_on_behalf_of_borrower',
			'label' => 'Pay on behalf of borrower',
			'width' => 'full',
		],
		'policy_loss_payee'                => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'policy_loss_payee',
			'label'        => 'Loss Payee',
			'width'        => 'full',
		],
		'policy_mortgagee'                 => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'policy_mortgagee',
			'label'        => 'Mortgagee',
			'width'        => 'full',
		],
	];
}

/**
 * Returns insurance agent details form fields.
 *
 * @since 1.0.0
 *
 * @return array Field definitions for agent information.
 */
function wpp_form_fields_agent() {
	return [
		'agent_company'      => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'agent_company',
			'label'        => 'Company',
			'width'        => 'full',
		],
		'agent_agent_name'   => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'agent_agent_name',
			'label'        => 'Agent Name',
			'width'        => 'full',
		],
		'agent_agent_number' => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'agent_agent_number',
			'label'        => 'Agent #',
			'width'        => 'full',
		],
		'agent_address'      => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'agent_address',
			'label'        => 'Address',
			'width'        => 'full',
		],
		'agent_email'        => [
			'type'         => 'text',
			'element_type' => 'email',
			'name'         => 'agent_email',
			'label'        => 'Email',
			'width'        => 'full',
		],
		'agent_phone_number' => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'agent_phone_number',
			'label'        => 'Phone #',
			'width'        => 'full',
		],
		'agent_fax_number'   => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'agent_fax_number',
			'label'        => 'Fax #',
			'width'        => 'full',
		],
	];
}

/**
 * Renders the insurance section with accordions.
 *
 * @since 1.0.0
 *
 * @return string HTML output of insurance accordions.
 *
 * @todo Refactor to return structured data instead of direct output.
 */
function wpp_step_config_insurance() {
	$out = '';

	$out_1 = new WPP_Accordion_Field([
		'type'    => 'accordion',
		'name'    => 'wpp_form_fields_accordion_v',
		'title'   => 'Policy Details  ',
		'content' => function () {
			foreach (wpp_form_fields_policy() as $name => $config) {
				$class_name = 'WPP_' . ucfirst($config['type']) . '_Field';
				if (class_exists($class_name)) {
					$field = new $class_name(array_merge($config, ['name' => $config['name']]));
					$field->render();
				}
			}
		},
	]);

	$out_2 = new WPP_Accordion_Field([
		'type'    => 'accordion',
		'name'    => 'wpp_form_fields_accordion_d',
		'title'   => 'Agent Details',
		'content' => function () {
			foreach (wpp_form_fields_agent() as $name => $config) {
				$class_name = 'WPP_' . ucfirst($config['type']) . '_Field';
				if (class_exists($class_name)) {
					$field = new $class_name(array_merge($config, ['name' => $config['name']]));
					$field->render();
				}
			}
		},
	]);

	$out .= $out_1->render();
	$out .= $out_2->render();

	return $out;
}

/**
 * Builds the full Property Details UI using nested accordions.
 *
 * Combines all sub-sections into a single interface.
 *
 * @since 1.0.0
 *
 * @return string Rendered HTML of all sections.
 */
function wpp_step_config_property_details() {
	$main_property = new WPP_Accordion_Field([
		'type'    => 'accordion',
		'name'    => 'main_property',
		'title'   => 'Main Property',
		'content' => function () {
			foreach (wpp_step_config_main_property() as $name => $config) {
				$class_name = 'WPP_' . ucfirst($config['type']) . '_Field';
				if (class_exists($class_name)) {
					$field = new $class_name(array_merge($config, ['name' => $config['name']]));
					$field->render();
				}
			}
		},
	]);

	$existing_mortgages = new WPP_Accordion_Field([
		'type'    => 'accordion',
		'name'    => 'existing_mortgages',
		'title'   => 'Existing Mortgages',
		'content' => function () {
			foreach (wpp_step_config_existing_mortgages() as $name => $config) {
				$class_name = 'WPP_' . ucfirst($config['type']) . '_Field';
				if (class_exists($class_name)) {
					$field = new $class_name(array_merge($config, ['name' => $config['name']]));
					$field->render();
				}
			}
		},
	]);

	$other_properties = new WPP_Accordion_Field([
		'type'    => 'accordion',
		'name'    => 'other_properties',
		'title'   => 'Other Properties',
		'content' => function () {
			foreach (wpp_step_config_other_properties() as $name => $config) {
				$class_name = 'WPP_' . ucfirst($config['type']) . '_Field';
				if (class_exists($class_name)) {
					$field = new $class_name(array_merge($config, ['name' => $config['name']]));
					$field->render();
				}
			}
		},
	]);

	$insurance = new WPP_Accordion_Field([
		'type'    => 'accordion',
		'name'    => 'insurance',
		'title'   => 'Insurance',
		'content' => function () {
			wpp_step_config_insurance();
		},
	]);

	$out = '';
	$out .= $main_property->render();
	$out .= $existing_mortgages->render();
	$out .= $other_properties->render();
	$out .= $insurance->render();

	return $out;
}

/**
 * Outputs the Property Details tab in the loan management portal.
 *
 * Hooked to `wpp_lmp_loan_content` to dynamically render content.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML directly.
 *
 * @hooked wpp_lmp_loan_content
 * @priority 20
 */
function wpp_term_property_details() {
	?>
    <div id="property-details" class="container">
        <div class="property-details">
			<?php
			$cont = new WPP_Accordion_Field([
				'type'    => 'accordion',
				'name'    => 'info_property',
				'title'   => 'Property Details',
				'content' => function () {
					wpp_step_config_property_details();
				},
			]);
			$cont->render();
			?>
        </div>
    </div>
	<?php
}
add_action('wpp_lmp_loan_content', 'wpp_term_property_details', 20);

/*
 * @todo List
 *
 * 1. Implement partial discharge condition fields (currently commented).
 * 2. Add field validation and sanitization on save.
 * 3. Localize datepicker fields (i18n).
 * 4. Add nonce verification for form submissions.
 * 5. Ensure accessibility (ARIA, keyboard navigation).
 * 6. Write unit tests for form configurations.
 * 7. Document WPP_Accordion_Field and field classes.
 * 8. Optimize performance for large repeater sets.
 */