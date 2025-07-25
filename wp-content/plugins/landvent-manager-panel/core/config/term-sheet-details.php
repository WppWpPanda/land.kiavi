<?php
function wpp_step_config_u() {

	$form_fields = [
		'loan_type'              => [
			'type'    => 'select',
			'name'    => 'loan_type',
			'label'   => 'Loan Type',
			'options' => [
				'purchase'  => 'Purchase',
				'refinance' => 'Refinance/ETO',
			],
			'width'   => 'full'
		],
		'application_type'       => [
			'type'    => 'select',
			'name'    => 'application_type',
			'label'   => 'Application Type',
			'options' => [
				'mortgage'        => 'Mortgage',
				'loc'             => 'LOC',
				'deed_of_trust'   => 'Deed of Trust',
				'promissory_note' => 'Promissory Note',
			],
			'width'   => 'full'
		],
		'loan_purpose'           => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'loan_purpose',
			'label'        => 'Loan Purpose',
			'width'        => 'full'
		],
		'mers_loan'              => [
			'type'  => 'checkbox',
			'name'  => 'mers_loan',
			'label' => 'MERS Loan',
			'width' => 'full'
		],
		'purchase_price'         => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'purchase_price',
			'label'        => 'Purchase Price',
			'placeholder'  => '$0.00',
			'data-type'    => 'money',
			'width'        => 'full'
		],

////////////////////////////Down Payment: [+]
		'current_value'          => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'current_value',
			'label'        => 'Current Value',
			'placeholder'  => '$0.00',
			'data-type'    => 'money',
			'width'        => 'full'
		],
		'after_repair_value'     => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'after_repair_value',
			'label'        => 'ARV',
			'placeholder'  => '$0.00',
			'data-type'    => 'money',
			'width'        => 'full'
		],
		// Total Repair Cost
		'total_loan_amount'      => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'total_loan_amount',
			'label'        => 'Total Loan',
			'placeholder'  => '$0.00',
			'data-type'    => 'money',
			'width'        => 'full'
		],
		/*'total_loan_amount'                  => [
			'type'    => 'content',
			'name'    => 'total_loan_amount',
			'label'   => '',
			'width'   => 'full',
			'content' => '
<div data-name="total_loan_amount" class="wpp-content-body">
<p>Total Loan Amount: $175,000</p>
</div>
',
		],*/
		'total_repair_cost'      => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'total_repair_cost',
			'label'        => 'Total Repair Cost',
			'placeholder'  => '$0.00',
			'data-type'    => 'money',
			'width'        => 'full'
		],
		'advance_at_closing'     => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'advance_at_closing',
			'label'        => 'Advance at Closing',
			'placeholder'  => '$0.00',
			'data-type'    => 'money',
			'width'        => 'full'
		],
		'loan_position'          => [
			'type'    => 'select',
			'name'    => 'loan_position',
			'label'   => 'Loan Position',
			'options' => [
				1 => 'First',
				2 => 'Second',
				3 => 'Third',
				4 => 'Fourth',
				5 => 'Fifth',
				6 => 'Sixth',
				7 => 'Seventh'
			],
			'width'   => 'full'
		],
		'interest_rate'          => [
			'type'         => 'text',
			'element_type' => 'percentage',
			'name'         => 'interest_rate',
			'label'        => 'Interest Rate',
			'placeholder'  => '0.00%',
			'width'        => '1/2'
		],
		'enable_variable_rate'   => [
			'classes' => [ 'wpp-no-label-inverse no-left' ],
			'type'    => 'checkbox',
			'name'    => 'enable_variable_rate',
			'label'   => 'enable variable rate',
			'width'   => '1/2'
		],
		'standby_interest'       => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'standby_interest',
			'label'        => 'Standby Interest (%)',
			'placeholder'  => 'e.g., 1.00',
			'width'        => 'full'
		],
		'repayment_type'         => [
			'type'    => 'select',
			'name'    => 'repayment_type',
			'label'   => 'Repayment Type',
			'options' => [
				'interest_only'          => 'Interest Only',
				'principal_and_interest' => 'Principal + Interest',
				'capitalizing_interest'  => 'Capitalizing Interest'
			],
			'width'   => 'full'
		],
		'_monthly_payment'       => [
			'type'    => 'content',
			'name'    => '_monthly_payment',
			'label'   => 'Monthly Payment',
			'width'   => 'full',
			'content' => '
				<span>
					$1,207.50
				</span>
			',
		],
		'interest_adjustment'    => [
			'type'    => 'content',
			'name'    => 'interest_adjustment',
			'label'   => 'Interest Adjustment',
			'width'   => '1/2',
			'content' => '
				<span>
					no closing date entered
				</span>
			',
		],
		'term'                   => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'term',
			'label'        => 'Term',
			'placeholder'  => 'e.g., 12 months',
			'width'        => 'full'
		],


		// Interest Schedule
		'interest_schedule'      => [
			'type'    => 'radio',
			'name'    => 'interest_schedule',
			'label'   => 'Interest Schedule',
			'options' => [
				'charge_interest_at_monthly_payment'             => 'charge interest at monthly payment',
				'interest_reserve'                               => 'interest reserve',
				'defer_interest_payments_until_the_next_advance' => 'defer interest payments until the next advance'
			],
			'width'   => 'full',
			'default' => 'interest_reserve'
		],

		'interest_reserve_amount'           => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'interest_reserve_amount',
			'label'        => 'Interest Reserve',
			'placeholder'  => '$0.00',
			'data-type'    => 'money',
			'width'        => '1/2',
			//'conditional'  => [ 'interest_schedule' => [ 'interest_reserve' ] ]
		],
		'interest_reserve_months'           => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'interest_reserve_months',
			'label'        => 'or',
			'placeholder'  => 'e.g., 6',
			'width'        => '1/2',
			//'conditional'  => [ 'interest_schedule' => [ 'interest_reserve' ] ]
		],
		'interest_reserve_deduction_amount' => [
			'type'         => 'select',
			'element_type' => 'money',
			'name'         => 'interest_reserve_deduction_amount',
			'label'        => 'Interest Reserve Deduction',
			'options'      => [
				'spread_evenly'    => 'Spread evenly',
				'apply_upfront'    => 'Apply upfront (deferred payments)',
				'apply_at_the_end' => 'Apply at the end'
			],
			'width'        => '1/2',
			//'conditional'  => [ 'interest_schedule' => [ 'interest_reserve' ] ]
		],
		'interest_reserve_deduction_months' => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'interest_reserve_deduction_months',
			'label'        => 'or',
			'placeholder'  => 'e.g., 6',
			'width'        => '1/2',
			//'conditional'  => [ 'interest_schedule' => [ 'interest_reserve' ] ]
		],
		'early_term_type'                   => [
			'type'    => 'select',
			'name'    => 'early_term_type',
			'label'   => 'Early Term. Type',
			'width'   => 'full',
			'options' => [
				'fully_closed'      => 'Fully Closed',
				'closed_variable'   => 'Closed Variable',
				'closed_then_open'  => 'Closed, Then Open',
				'fully_open'        => 'Fully Open',
				'minimum_then_open' => 'Minimum, Then Open'
			],
			'default' => 'fully_closed'
		],
		'early_term_penalty'                => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'early_term_penalty',
			'label'        => 'Early Term Penalty',
			'placeholder'  => '$0.00',
			'data-type'    => 'money',
			'width'        => '1/2'
		],
		'broker'                            => [
			'type'         => 'text',
			'element_type' => 'email',
			'name'         => 'broker',
			'label'        => 'Broker',
			'placeholder'  => 'broker@example.com',
			'width'        => 'full'
		],
		'broker_fee'                        => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'broker_fee',
			'label'        => 'Broker Fee',
			'placeholder'  => '$0.00',
			'data-type'    => 'money',
			'width'        => '1/2'
		],
		'broker_fee_percent'                => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'broker_fee_percent',
			'label'        => 'or',
			'placeholder'  => '',
			'width'        => '1/2'
		],

		// Closing Date
		'closing_date'                      => [
			'type'        => 'datepicker',
			'name'        => 'closing_date',
			'label'       => 'Closing Date',
			'placeholder' => 'MM/DD/YYYY',
			'width'       => 'full'
		],

		// Deadline to Accept
		'deadline_to_accept'                => [
			'type'        => 'datepicker',
			'name'        => 'deadline_to_accept',
			'label'       => 'Deadline to Accept',
			'placeholder' => 'MM/DD/YYYY',
			'width'       => 'full'
		],

	];

	return $form_fields;
}

function wpp_term_sheet_details() { ?>
    <div id="term-sheet-details" class="container">
        <div class="row">
			<?php
			$ACC = new WPP_Accordion_Field( [
				'type'    => 'accordion',
				'name'    => 'advanced_settings',
				'title'   => 'Term Sheet Details',
				'open'    => true,
				'content' => function () {
					foreach ( wpp_step_config_u() as $name => $config ) {
						$class_name = 'WPP_' . ucfirst( $config['type'] ) . '_Field';

						if ( class_exists( $class_name ) ) {
							$field = new $class_name( array_merge( $config, [ 'name' => $name ] ) );
							$field->render();
						}
					}
				}
			] );

			$ACC->render();
			?>
        </div>
    </div>
<?php }

add_action( 'wpp_lmp_loan_content', 'wpp_term_sheet_details', 40 );