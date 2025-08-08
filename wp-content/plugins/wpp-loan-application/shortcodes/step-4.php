<?php

function wpp_step_config_4() {
	$form_fields = [
		'purchase_title' => [
			'type'    => 'content',
			'name'    => 'purchase_title',
			'label'   => '',
			'width'   => 'full',
			'content' => sprintf( '<h1>%s</h1>', 'Estimate Your Bridge Rate' )
		],
		'property_state' => [
			'type'    => 'select',
			'name'    => 'property_state',
			'label'   => 'Property State',
			'options' => [
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
			],
			'width'   => '1/4'
		],

		'property_type' => [
			'type'    => 'select',
			'name'    => 'property_type',
			'label'   => 'Property Type',
			'options' => [
				''                  => 'Select Property Type',
				'single_family'     => 'Single Family',
				'detached_pud'      => 'Detached-PUD',
				'attached_pud'      => 'Attached-PUD',
				'condo'             => 'Condo',
				'2_4_unit'          => '2-4 Unit',
				'manufactured_home' => 'Manufactured Home'
			],
			'default' => 'single_family',
			'width'   => '1/4'
		],

		'estimated_fico_score' => [
			'type'    => 'select',
			'name'    => 'estimated_fico_score',
			'label'   => 'Est. FICO Score',
			'options' => [
				''          => 'Select FICO Score',
				'below_600' => 'Below 600',
				'600-619'   => '600-619',
				'620-639'   => '620-639',
				'640-659'   => '640-659',
				'660-679'   => '660-679',
				'680-699'   => '680-699',
				'700-719'   => '700-719',
				'720-739'   => '720-739',
				'740-759'   => '740-759',
				'760-779'   => '760-779',
				'over_780'  => 'Over 780'
			],
			'default' => '720-739',
			'width'   => '1/4'
		],

		'personally_guaranteed' => [
			'type'    => 'select',
			'name'    => 'personally_guaranteed',
			'label'   => 'Personally Guaranteed',
			'options' => [
				''    => 'Select Guarantee Status',
				'yes' => 'Yes',
				'no'  => 'No'
			],
			'default' => 'yes',
			'width'   => '1/4'
		],

		'refinance'           => [
			'type'    => 'select',
			'name'    => 'refinance',
			'label'   => 'Refinance',
			'options' => [
				''    => 'Select Refinance Option',
				'yes' => 'Yes',
				'no'  => 'No'
			],
			'default' => 'no',
			'width'   => '1/4'
		],

		// refinance_amount будет вставлен динамически после refinance
		'prop_owned_6_months' => [
			'type'        => 'select',
			'name'        => 'prop_owned_6_months',
			'label'       => 'Prop. Owned ≥ 6 Months',
			'options'     => [
				''    => 'Select Option',
				'yes' => 'Yes',
				'no'  => 'No'
			],
			'default'     => 'no',
			'compare'     => '=', // Показать, если refinance ≠ yes
			'conditional' => [ 'refinance' => [ 'yes' ] ],
			'width'       => '1/4'
		],
		'purchase_price'      => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'purchase_price',
			'label'        => 'Purchase Price',
			'width'        => '1/4',
			'default'      => '200000',
			'compare'      => '!=', // Показать, если refinance ≠ yes
			'conditional'  => [ 'prop_owned_6_months' => [ 'no', '' ] ],
			'has_cents'    => false
		],

		'estimated_home_value' => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'estimated_home_value',
			'label'        => 'Estimated Home Value',
			'width'        => '1/4',
			'default'      => '225000',
			'compare'      => '=', // Показать, если refinance ≠ yes
			'conditional'  => [ 'prop_owned_6_months' => [ 'yes' ] ],
			'has_cents'    => false
		],

		'remaining_mortgage' => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'remaining_mortgage',
			'label'        => 'Remaining Mortgage',
			'width'        => '1/4',
			'default'      => '0',
			'compare'      => '=', // Показать, если refinance ≠ yes
			'conditional'  => [ 'prop_owned_6_months' => [ 'yes' ] ],
			'has_cents'    => false
		],

		'purchase_loan_amount' => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'purchase_loan_amount',
			'label'        => 'Purchase Loan Amount',
			'width'        => '1/4',
			'default'      => '150000',
			'has_cents'    => false,
			'conditional'  => [ 'refinance' => [ 'no' ] ],
		],

		'refinance_loan_amount' => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'refinance_loan_amount',
			'label'        => 'Refinance Loan Amount',
			'width'        => '1/4',
			'default'      => '150000',
			'has_cents'    => false,
			'conditional'  => [ 'refinance' => [ 'yes' ] ]
		],

		'loan_qualification_message' => [
			'type'    => 'content',
			'name'    => 'loan_qualification_message',
			'label'   => '',
			'content' => '<p>You qualify for a loan between $0 to $900,000.</p>',
			'width'   => '1/4',
			'classes' => [ 'mb-4' ]
		],

		'property_rehab' => [
			'type'    => 'select',
			'name'    => 'property_rehab',
			'label'   => 'Property Rehab',
			'options' => [
				''    => 'Select Rehab Option',
				'yes' => 'Yes',
				'no'  => 'No'
			],
			'default' => 'yes',
			'width'   => '1/4'
		],

		'rehab_cost' => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'rehab_cost',
			'label'        => 'Estimated Cost of Rehab',
			'width'        => '1/4',
			'default'      => '25000',
			'has_cents'    => false,
			'conditional'  => [ 'property_rehab' => [ 'yes' ] ]

		],

		'rehab_funds' => [
			'type'        => 'select',
			'name'        => 'rehab_funds',
			'label'       => 'Rehab Funds',
			'options'     => [
				''    => 'Select Rehab Funds',
				'yes' => 'Yes',
				'no'  => 'No'
			],
			'default'     => 'yes',
			'width'       => '1/4',
			'conditional' => [ 'property_rehab' => [ 'yes' ] ]
		],

		'after_repair_value' => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'after_repair_value',
			'label'        => 'After Repair Value (ARV)',
			'width'        => '1/4',
			'default'      => '300000',
			'has_cents'    => false,
			'conditional'  => [ 'property_rehab' => [ 'yes' ] ]
		],

		'total_loan_amount' => [
			'type'    => 'content',
			'name'    => 'total_loan_amount',
			'label'   => '',
			'content' => '<p>Total Loan Amount: $750,000</p>',
			'width'   => 'full',
			'classes' => [ 'mb-4' ],

		],

		'total_loan_amount_sum' => [
			'type'    => 'text',
			'name'    => 'total_loan_amount_sum',
			'label'   => '',
			'width'   => 'full',
            'default' => '175000',
			'classes' => [ 'mb-4 wpp-hidden hidden' ],

		],

       /* 'rate_type' => [
			'type'    => 'text',
			'name'    => 'rate_type',
			'label'   => '',
			'width'   => 'full',
			'classes' => [ 'mb-4' ],

		],

        'rate' => [
			'type'    => 'text',
			'name'    => 'rate',
			'label'   => '',
			'width'   => 'full',
			'classes' => [ 'mb-4' ],
		],

		'monthly_payment' => [
			'type'    => 'text',
			'name'    => 'monthly_payment',
			'label'   => '',
			'width'   => 'full',
			'classes' => [ 'mb-4' ],
		],*/

		'loan_to_cost_ratio' => [
			'type'    => 'content',
			'name'    => 'loan_to_cost_ratio',
			'label'   => '',
			'content' => '<p>Loan-to-cost is 50%.</p>',
			'width'   => '1/2'
		],

		'after_repair_ltv' => [
			'type'    => 'content',
			'name'    => 'after_repair_ltv',
			'label'   => '',
			'content' => '<p>After-repair loan-to-value is 15%.</p>',
			'width'   => '1/2'
		],


		'errors_msg' => [
			'type'    => 'content',
			'name'    => 'errors_msg',
			'content' => '<div class="wpp-errors-wrap"></div>',
			'width'   => 'full'
		],


		'rates_table'     => [
			'type'    => 'content',
			'name'    => 'rates_table',
			'content' => '
                <table id="monthly-payments-table" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Rate Type</th>
                            <th>Rate</th>
                            <th>Est. Monthly Payment</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            ',
			'width'   => 'full'
		],
		'step_identifier' => [
			'type'         => 'text',
			'element_type' => 'hidden',
			'name'         => 'step',
			'default'      => '4',
			'width'        => 'full'
		],
	];

	return $form_fields;
}

function wpp_render_loan_step_4() {
	ob_start();
	?>
    <div class="container">
		<?php
		wpp_loan_form_debug_data( 3 );
		wpp_render_form( 'loan-form-step-4', wpp_step_config_4() );
		?>
    </div>

	<?php return ob_get_clean();
}

add_shortcode( 'wpp_loan_application_step_4', 'wpp_render_loan_step_4' );