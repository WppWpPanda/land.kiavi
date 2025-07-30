<?php
defined( 'ABSPATH' ) || exit;
function wpp_step_config_main_property() {

	$data = wpp_get_loan_data_r();

	$form_fields = [
		'property_address'   => [
			'type'   => 'fields_block',
			'name'   => 'property_address',
			'label'  => 'Property Address',
			'fields' => [
				'property_street'  => [
					'type'         => 'text',
					'classes'      => [ 'wpp-top-label' ],
					'element_type' => 'text',
					'name'         => 'property_street',
					'label'        => 'Street',
					'width'        => '2/3',
					'default'      => wpp_field_value( 'property_street', $data ),
				],
				'property_city'    => [
					'type'         => 'text',
					'classes'      => [ 'wpp-top-label' ],
					'element_type' => 'text',
					'name'         => 'property_city',
					'label'        => 'City',
					'width'        => '1/3',
					'default'      => wpp_field_value( 'property_city', $data ),
				],
				'property_country' => [
					'type'         => 'text',
					'classes'      => [ 'wpp-top-label' ],
					'element_type' => 'text',
					'name'         => 'property_country',
					'label'        => 'Country',
					'width'        => '1/3',
					'default'      => wpp_field_value( 'property_country', $data ),
				],
				'property_state'   => [
					'type'    => 'select',
					'classes' => [ 'wpp-top-label' ],
					'name'    => 'property_state',
					'label'   => 'State',
					'presets' => 'states',
					'width'   => '1/3',
					'default' => wpp_field_value( 'property_state', $data ),
				],
				'property_zip'     => [
					'type'         => 'text',
					'classes'      => [ 'wpp-top-label' ],
					'element_type' => 'text',
					'name'         => 'property_zip',
					'label'        => 'Zip',
					'width'        => '1/3',
					'default'      => wpp_field_value( 'property_zip', $data ),
				],
				'property_note'    => [
					'type'    => 'content',
					'name'    => 'property_note',
					'width'   => 'full',
					'content' => '<i>Note: the borrower\'s address seems different from the property address.</i>'
				]
			]
		],
		'legal_description'  => [
			'type'         => 'textarea',
			'element_type' => 'text',
			'name'         => 'legal_description',
			'label'        => 'Legal Description',
			'width'        => 'full',
			'default'      => wpp_field_value( 'legal_description', $data ),
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
				'default'        => wpp_field_value( 'property_type', $data ),
			],
			'width'   => 'full'
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
				'vacant'         => 'Vacant'
			],
			'width'   => 'full',
			'default' => wpp_field_value( 'property_occupancy', $data ),
		]
	];

	return $form_fields;
}

function wpp_step_config_existing_mortgages() {

	$data = wpp_get_loan_data_r();


	$form_fields_repeater = [
		'mortgage_holder'           => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'mortgage_holder',
			'label'        => 'Mortgage Holder',
			'width'        => 'full',
			//'default'      => isset( $data['mortgage_holder'] ) ? $data['mortgage_holder'] : '',
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
				//'default' => isset( $data['mortgage_type'] ) ? $data['mortgage_type'] : '',
			],
			'width'   => '1/2'
		],
		'postponed_checkbox'        => [
			'type'    => 'checkbox',
			'classes' => [ 'wpp-no-label-inverse no-left' ],
			'name'    => 'postponed',
			'label'   => 'postponed',
			'width'   => '1/4',
			//'default' => isset( $data['postponed'] ) ? $data['postponed'] : '',
		],
		'co_lending_checkbox'       => [
			'type'    => 'checkbox',
			'classes' => [ 'wpp-no-label-inverse no-left' ],
			'name'    => 'co_lending',
			'label'   => 'co-Lending',
			'width'   => '1/4',
			//'default' => isset( $data['co_lending'] ) ? $data['co_lending'] : '',
		],
		'face_value'                => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'face_value',
			'label'        => 'Face Value',
			'width'        => 'full',
			//'default'      => isset( $data['face_value'] ) ? $data['face_value'] : '',
		],
		'balance'                   => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'balance',
			'label'        => 'Balance',
			'width'        => 'full',
			//'default'      => isset( $data['balance'] ) ? $data['balance'] : '',
		],
		'payment'                   => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'payment',
			'label'        => 'Payment',
			'width'        => 'full',
			//'default'      => isset( $data['payment'] ) ? $data['payment'] : '',
		],
		'frequency'                 => [
			'type'    => 'select',
			'name'    => 'frequency',
			'label'   => 'Frequency',
			'options' => [
				'monthly'   => 'Monthly',
				'bi-weekly' => 'Bi-Weekly',
				'weekly'    => 'Weekly'
			],
			'width'   => 'full',
			//'default' => isset( $data['frequency'] ) ? $data['frequency'] : '',
		],
		'maturity_date'             => [
			'type'  => 'datepicker',
			'name'  => 'maturity_date',
			'label' => 'Maturity Date',
			'width' => 'full',
			//'default' => isset( $data['maturity_date'] ) ? $data['maturity_date'] : '',
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
				'buydown'         => 'Buydown'
			],
			'width'   => 'full',
			//'default' => isset( $data['rate_type'] ) ? $data['rate_type'] : '',
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
				'minimum_then_open' => 'Minimum, Then Open'
			],
			'width'   => 'full',
			//'default' => isset( $data['term_type'] ) ? $data['term_type'] : '',
		],
		'interest_rate'             => [
			'type'         => 'text',
			'element_type' => 'percentage',
			'name'         => 'interest_rate',
			'label'        => 'Interest Rate',
			'width'        => 'full',
			//'default'      => isset( $data['interest_rate'] ) ? $data['interest_rate'] : '',
		],
		'prepayment_penalty'        => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'prepayment_penalty',
			'label'        => 'Prepayment Penalty',
			'width'        => 'full',
			//'default'      => isset( $data['prepayment_penalty'] ) ? $data['prepayment_penalty'] : '',
		],
		'in_arrears_checkbox_block' => [
			'type'   => 'fields_block',
			'name'   => 'in_arrears_checkbox_block',
			'label'  => 'In arrears?',
			'fields' => [
				'in_arrears_checkbox' => [
					'type'    => 'checkbox',
					'classes' => [ 'wpp-no-label-inverse no-left' ],
					'name'    => 'in_arrears',
					'label'   => 'Yes',
					'width'   => '1/2'
				]
			],
			//'default' => isset( $data['in_arrears_checkbox_block'] ) ? $data['in_arrears_checkbox_block'] : '',
		]

	];


	$form_fields = [
		[
			'type'        => 'repeater',
			'name'        => 'user_contacts',
			'title'       => '',
			'button_text' => 'Add a mortgage',
			'min'         => 1,
			'max'         => 5,
			'fields'      => $form_fields_repeater,
			'default'     => ( ! isset( $data['user_contacts'] ) || empty( array_filter( $data['user_contacts'] ) ) ? '' : $data['user_contacts'] )
		]
	];


	return $form_fields;
}

function wpp_step_config_other_properties() {

	$data = wpp_get_loan_data_r();

	$form_fields = [
		'other_info' => [
			'type'   => 'super_accordion',
			'name'   => 'other_info',
			'title'  => '',
			'header' => '{other_properties_street}',
			'fields' => [
				'other_properties_blanket' => [
					'type'    => 'checkbox',
					'classes' => [ 'wpp-no-label-inverse no-left' ],
					'name'    => 'other_properties_blanket',
					'label'   => 'blanket',
					'width'   => '2',
					'default'      => wpp_field_value( 'other_properties_blanket', $data ),
				],
				'other_properties_street'  => [
					'type'         => 'text',
					'classes'      => [ 'wpp-top-label' ],
					'element_type' => 'text',
					'name'         => 'other_properties_street',
					'label'        => 'Street',
					'width'        => '4',
					'default'      => wpp_field_value( 'other_properties_street', $data ),
				],
				'other_properties_city'    => [
					'type'         => 'text',
					'classes'      => [ 'wpp-top-label' ],
					'element_type' => 'text',
					'name'         => 'other_properties_city',
					'label'        => 'City',
					'width'        => '2',
					'default'      => wpp_field_value( 'other_properties_city', $data ),
				],
				'other_properties_country' => [
					'type'         => 'text',
					'classes'      => [ 'wpp-top-label' ],
					'element_type' => 'text',
					'name'         => 'other_properties_country',
					'label'        => 'Country',
					'width'        => '2',
					'default'      => wpp_field_value( 'other_properties_country', $data ),
				],
				'other_properties_state'   => [
					'type'    => 'select',
					'classes' => [ 'wpp-top-label' ],
					'name'    => 'other_properties_state',
					'label'   => 'State',
					'presets' => 'states',
					'width'   => '2',
					'default'      => wpp_field_value( 'other_properties_state', $data ),
				],
				'other_properties_sep'     => [
					'type'    => 'content',
					'name'    => 'other_properties_sep',
					'content' => '',
					'width'   => '2',
					'default'      => wpp_field_value( 'other_properties_sep', $data ),
				],

				'other_properties_zip'    => [
					'type'         => 'text',
					'classes'      => [ 'wpp-top-label' ],
					'element_type' => 'text',
					'name'         => 'other_properties_zip',
					'label'        => 'Zip',
					'width'        => '2',
					'default'      => wpp_field_value( 'other_properties_zip', $data ),
				],
				'other_est_value'         => [
					'type'         => 'text',
					'classes'      => [ 'wpp-top-label' ],
					'element_type' => 'money',
					'name'         => 'other_est_value',
					'label'        => 'Est. Value',
					'width'        => '4',
					'default'      => wpp_field_value( 'other_est_value', $data ),
				],
				'other_legal_description' => [
					'type'    => 'text',
					'classes' => [ 'wpp-top-label' ],
					'name'    => 'other_legal_description',
					'label'   => 'Legal Description',
					'width'   => '4',
					'default'      => wpp_field_value( 'other_legal_description', $data ),
				],
				'monthly_rt'              => [
					'type'    => 'fields_block',
					'name'    => 'monthly_rt',
					'classes' => [ 'col-full' ],
					'label'   => '',
					'width'   => 'full',
					'fields'  => [
						'monthly_sep'           => [
							'type'    => 'content',
							'name'    => 'monthly_sep',
							'label'   => '',
							'content' => '',
							'width'   => '2',
							'default'      => wpp_field_value( 'monthly_sep', $data ),
						],
						'monthly_rental_income' => [
							'type'         => 'text',
							'classes'      => [ 'wpp-top-label' ],
							'element_type' => 'money',
							'name'         => 'monthly_rental_income',
							'label'        => 'Monthly Rental Income',
							'width'        => '5',
							'default'      => wpp_field_value( 'monthly_rental_income', $data ),
						],
						'property_taxes'        => [
							'type'         => 'text',
							'classes'      => [ 'wpp-top-label' ],
							'element_type' => 'money',
							'name'         => 'property_taxes',
							'label'        => 'Property Taxes',
							'width'        => '5',
							'default'      => wpp_field_value( 'property_taxes', $data ),
						],
					]
				],
				'monthly_expenses'        => [
					'type'    => 'fields_block',
					'name'    => 'monthly_expenses',
					'classes' => [ 'col-full with-padding' ],
					'label'   => 'Monthly Expenses<span class="mi-summ">$0.00</span>',
					'fields'  => [
						'hoa_fees' => [
							'classes'      => [ 'wpp-top-label' ],
							'type'         => 'text',
							'element_type' => 'money',
							'name'         => 'hoa_fees',
							'label'        => 'HOA Fees',
							'width'        => '6',
							'default'      => wpp_field_value( 'hoa_fees', $data ),
						],
						'heating'  => [
							'classes'      => [ 'wpp-top-label' ],
							'type'         => 'text',
							'element_type' => 'money',
							'name'         => 'heating',
							'label'        => 'Heating',
							'width'        => '6',
							'default'      => wpp_field_value( 'heating', $data ),
						]
					]
				],
				'op_fees'         => [
					'type'    => 'repeater',
					'classes' => [ 'line-group no-border first-del' ],
					'name'    => 'op_fees',
					'label'   => 'Custom Monthly Expenses',
                    'default'     => ( ! isset( $data['op_fees'] ) || empty( array_filter( $data['op_fees'] ) ) ? '' : $data['op_fees'] ),
					'fields'  => [
						'field_sep' => [
							'type'         => 'content',
							'element_type' => 'text',
							'name'         => 'field_sep',
							'content'      => '',
							'width'        => '2'
						],
						'field_1'   => [
							'type'         => 'text',
							'element_type' => 'text',
							'name'         => 'field_1',
							'placeholder'  => 'Field 1',
							'width'        => '5'
						],
						'field_2'   => [
							'type'         => 'text',
							'element_type' => 'money',
							'name'         => 'field_2',
							'placeholder'  => '$',
							'width'        => '5'
						]
					]
				],
			]
			/**'partial_discharge_condition' => [
			 * 'type'         => 'checkbox',
			 * 'name'         => 'partial_discharge_condition',
			 * 'classes' => [ 'wpp-no-label-inverse no-left' ],
			 * 'label'        => 'Partial Discharge Condition',
			 * 'width'        => 'full'
			 * ],
			 *
			 * 'partial_discharge_condition_txt' => [
			 * 'type'         => 'content',
			 * 'name'         => 'partial_discharge_condition_txt',
			 * 'content'      => '<p>Minimum amount required to discharge this property:</p>',
			 * 'width'        => 'full',
			 * 'conditional' => ['partial_discharge_condition'=>['yes', '1']]
			 * ],
			 * 'partial_discharge_condition_minimum_amount' => [
			 * 'type'         => 'text',
			 * 'classes'      => [ 'label-min' ],
			 * 'element_type' => 'money',
			 * 'name'         => 'partial_discharge_condition_minimum_amount',
			 * 'label'        => 'The greater of',
			 * 'placeholder'  => '',
			 * 'width'        => '4',
			 * 'conditional' => ['partial_discharge_condition'=>['1']]
			 * ],
			 * 'partial_discharge_condition_percentage_of_net_proceeds' => [
			 * 'type'         => 'text',
			 * 'element_type' => 'percentage',
			 * 'classes'      => [ 'label-min' ],
			 * 'name'         => 'partial_discharge_condition_percentage_of_net_proceeds',
			 * 'label'        => 'or',
			 * 'placeholder'  => '',
			 * 'width'        => '4',
			 * 'conditional' => ['partial_discharge_condition'=>['yes']]
			 * ]
			 **/
		]
	];

	/*$form_fields = [[
		'type' => 'repeater',
		'name' => 'user_contacts',
		'title' => '',
		'button_text' => 'Add a mortgage',
		'min' => 1,
		'max' => 5,
		'fields' => $form_fields_repeater
	]
	];*/

	return $form_fields;
}


function wpp_form_fields_policy() {
	return [
		'policy_policy_issue_date'         => [
			'type'  => 'datepicker',
			'name'  => 'policy_policy_issue_date',
			'label' => 'Policy Issue Date',
			'width' => 'full'
		],
		'policy_property_address'          => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'policy_property_address',
			'label'        => 'Property Address',
			'value'        => '402 N George Street Millersville PA',
			'width'        => 'full'
		],
		'policy_insured_name'              => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'policy_insured_name',
			'label'        => 'Insured Name',
			'width'        => 'full'
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
				'umbrella'   => 'Umbrella Insurance'
			],
			'default' => 'none',
			'width'   => 'full'
		],
		'policy_policy_number'             => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'policy_policy_number',
			'label'        => 'Policy #',
			'width'        => 'full'
		],
		'policy_insurance_company'         => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'policy_insurance_company',
			'label'        => 'Insurance Company',
			'width'        => 'full'
		],
		'policy_eff_date'                  => [
			'type'  => 'datepicker',
			'name'  => 'policy_eff_date',
			'label' => 'Eff Date',
			'width' => 'full'
		],
		'policy_expiry_date'               => [
			'type'  => 'datepicker',
			'name'  => 'policy_expiry_date',
			'label' => 'Expiry Date',
			'width' => 'full'
		],
		'policy_coverage'                  => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'policy_coverage',
			'label'        => 'Coverage',
			'placeholder'  => '$0.00',
			'width'        => 'full'
		],
		'policy_deductible'                => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'policy_deductible',
			'label'        => 'Deductible',
			'placeholder'  => '$0.00',
			'width'        => 'full'
		],
		'policy_insurance_limit'           => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'policy_insurance_limit',
			'label'        => 'Insurance Limit',
			'placeholder'  => '$0.00',
			'width'        => 'full'
		],
		'policy_annual_cost'               => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'policy_annual_cost',
			'label'        => 'Annual Cost',
			'placeholder'  => '$0.00',
			'width'        => 'full'
		],
		'policy_stated_amount'             => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'policy_stated_amount',
			'label'        => 'Stated Amount',
			'placeholder'  => '$0.00',
			'width'        => 'full'
		],
		'policy_payment_date'              => [
			'type'  => 'datepicker',
			'name'  => 'policy_payment_date',
			'label' => 'Payment Date',
			'width' => 'full'
		],
		'policy_pay_on_behalf_of_borrower' => [
			'type'  => 'checkbox',
			'name'  => 'policy_pay_on_behalf_of_borrower',
			'label' => 'Pay on behalf of borrower',
			'width' => 'full'
		],
		'policy_loss_payee'                => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'policy_loss_payee',
			'label'        => 'Loss Payee',
			'width'        => 'full'
		],
		'policy_mortgagee'                 => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'policy_mortgagee',
			'label'        => 'Mortgagee',
			'width'        => 'full'
		]
	];
}

function wpp_form_fields_agent() {
	return [
		'agent_company'      => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'agent_company',
			'label'        => 'Company',
			'width'        => 'full'
		],
		'agent_agent_name'   => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'agent_agent_name',
			'label'        => 'Agent Name',
			'width'        => 'full'
		],
		'agent_agent_number' => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'agent_agent_number',
			'label'        => 'Agent #',
			'width'        => 'full'
		],
		'agent_address'      => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'agent_address',
			'label'        => 'Address',
			'width'        => 'full'
		],
		'agent_email'        => [
			'type'         => 'text',
			'element_type' => 'email',
			'name'         => 'agent_email',
			'label'        => 'Email',
			'width'        => 'full'
		],
		'agent_phone_number' => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'agent_phone_number',
			'label'        => 'Phone #',
			'width'        => 'full'
		],
		'agent_fax_number'   => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'agent_fax_number',
			'label'        => 'Fax #',
			'width'        => 'full'
		]
	];
}

function wpp_step_config_insurance() {

	$out = '';


	$out_1 = new WPP_Accordion_Field( [
		'type'    => 'accordion',
		'name'    => 'wpp_form_fields_accordion_v',
		'title'   => 'Policy Details  ',
		'content' => function () {
			foreach ( wpp_form_fields_policy() as $name => $config ) {
				$class_name = 'WPP_' . ucfirst( $config['type'] ) . '_Field';

				if ( class_exists( $class_name ) ) {
					$field = new $class_name( array_merge( $config, [ 'name' => $config['name'] ] ) );
					$field->render();
				}
			}
		}
	] );

	$out_2 = new WPP_Accordion_Field( [
		'type'    => 'accordion',
		'name'    => 'wpp_form_fields_accordion_d',
		'title'   => 'Agent Details',
		'content' => function () {
			foreach ( wpp_form_fields_agent() as $name => $config ) {
				$class_name = 'WPP_' . ucfirst( $config['type'] ) . '_Field';

				if ( class_exists( $class_name ) ) {
					$field = new $class_name( array_merge( $config, [ 'name' => $config['name'] ] ) );
					$field->render();
				}
			}
		}
	] );

	$out .= $out_1->render();
	$out .= $out_2->render();

	return $out;
}

function wpp_step_config_property_details() {

	$main_property = new WPP_Accordion_Field( [
		'type'    => 'accordion',
		'name'    => 'main_property',
		'title'   => 'Main Property',
		'content' => function () {
			foreach ( wpp_step_config_main_property() as $name => $config ) {
				$class_name = 'WPP_' . ucfirst( $config['type'] ) . '_Field';

				if ( class_exists( $class_name ) ) {
					$field = new $class_name( array_merge( $config, [ 'name' => $config['name'] ] ) );
					$field->render();
				}
			}
		}

	] );


	$existing_mortgages = new WPP_Accordion_Field( [
		'type'    => 'accordion',
		'name'    => 'existing_mortgages',
		'title'   => 'Existing Mortgages',
		'content' => function () {
			foreach ( wpp_step_config_existing_mortgages() as $name => $config ) {

				$class_name = 'WPP_' . ucfirst( $config['type'] ) . '_Field';

				if ( class_exists( $class_name ) ) {
					$field = new $class_name( array_merge( $config, [ 'name' => $config['name'] ] ) );
					$field->render();
				}
			}
		}

	] );

	$other_properties = new WPP_Accordion_Field( [
		'type'    => 'accordion',
		'name'    => 'other_properties',
		'title'   => 'Other Properties',
		'content' => function () {
			foreach ( wpp_step_config_other_properties() as $name => $config ) {
				$class_name = 'WPP_' . ucfirst( $config['type'] ) . '_Field';

				if ( class_exists( $class_name ) ) {
					$field = new $class_name( array_merge( $config, [ 'name' => $config['name'] ] ) );
					$field->render();
				}
			}
		}

	] );

	$insurance = new WPP_Accordion_Field( [
		'type'    => 'accordion',
		'name'    => 'insurance',
		'title'   => 'Insurance',
		'content' => function () {
			foreach ( wpp_step_config_insurance() as $name => $config ) {
				$class_name = 'WPP_' . ucfirst( $config['type'] ) . '_Field';

				if ( class_exists( $class_name ) ) {
					$field = new $class_name( array_merge( $config, [ 'name' => $config['name'] ] ) );
					$field->render();
				}
			}
		}

	] );

	$out = '';
	$out .= $main_property->render();
	$out .= $existing_mortgages->render();
	$out .= $other_properties->render();
	$out .= $insurance->render();

	return $out;
}

function wpp_term_property_details() { ?>
    <style>
        .page-template-single-loan .wpp-field.wpp-top-label {
            display: inline-block;
        }

        .page-template-single-loan .wpp-field.wpp-top-label label.form-label {
            width: 100%;
            min-width: 0;
            margin-bottom: 1px;
        }

        .page-template-single-loan label.wpp-fields-block-label,
        .page-template-single-loan .wpp-wpp_textarea_field label {
            width: 200px;
            min-width: 200px;
            font-size: .8rem;
            font-weight: 300;
        }

        .page-template-single-loan .wpp-field i {
            font-size: 13px;
            color: #737373;
        }

        .col-full > .wpp-fields-block.row {
            width: 100%;
        }

        .wpp-field.wpp-wpp_fields_block_field.col-full.col-12 {
            padding: 0;
            width: 100%;
            min-width: 100%;
            display: flex;
            justify-content: center;
        }

        .wpp-field.wpp-wpp_fields_block_field.col-full.with-padding.col-12 {
            padding-right: calc(var(--bs-gutter-x) * .5);
            padding-left: calc(var(--bs-gutter-x) * .5);
        }

        span.mi-summ {
            display: block;
            width: 100%;
            font-weight: 600;
        }


        .wpp-field.wpp-wpp_repeater_field.line-group.no-border.first-del .wpp-repeater-block.border.mb-3.position-relative.row {
            border: none !important;
            padding: 0;
        }

        .wpp-field.wpp-wpp_repeater_field.line-group.no-border.first-del .wpp-repeater-inner {
            border: none;
        }

        .wpp-field.wpp-wpp_repeater_field.line-group.no-border.first-del button.btn.btn-sm.btn-danger.position-absolute.top-0.end-0.m-2.wpp-repeater-remove {
            left: 0;
            margin: 0 .8rem !important;
            padding: .05rem;
        }

        .page-template-single-loan .wpp-field.label-min label.form-label {
            min-width: auto;
            width: auto;
        }

        @media (min-width: 768px) {
            .col-full .wpp-fields-block-label {
                flex: 0 0 auto;
                width: 16.66666667% !important;
                min-width: 16.66666667% !important;
            }
        }
    </style>
    <div id="property-details" class="container">
        <div class="property-details">
			<?php
			$cont = new WPP_Accordion_Field( [
				'type'    => 'accordion',
				'name'    => 'info_property',
				'title'   => 'Property Details',
				'content' => function () {
					foreach ( wpp_step_config_property_details() as $name => $config ) {
						$class_name = 'WPP_' . ucfirst( $config['type'] ) . '_Field';

						if ( class_exists( $class_name ) ) {
							$field = new $class_name( array_merge( $config, [ 'name' => $config['name'] ] ) );
							$field->render();
						}
					}
				}
			] );

			$cont->render();
			?>
        </div>
    </div>
<?php }

add_action( 'wpp_lmp_loan_content', 'wpp_term_property_details', 20 );