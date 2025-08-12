<?php
/**
 * Term Sheet Details Configuration for Loan Management Plugin
 *
 * This file defines the form structure and rendering logic for the "Term Sheet Details" step
 * in a loan application workflow. It includes critical financial and structural terms such as:
 * - Loan type, purpose, and position
 * - Interest rate, repayment type, and term
 * - Advance details, closing dates, and broker fees
 *
 * The UI is rendered using a single accordion component for advanced settings, with support
 * for dynamic fields like interest reserves and conditional rendering (currently commented).
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

defined( 'ABSPATH' ) || exit;

/**
 * Generates configuration for loan term sheet fields.
 *
 * Builds a comprehensive array of form fields that define the financial and structural
 * parameters of a loan. These include:
 * - Loan type and application type
 * - Financial values (purchase price, ARV, total loan, repair costs)
 * - Interest settings (rate, schedule, reserve, penalties)
 * - Closing and deadline dates
 * - Broker information and fees
 *
 * This configuration is designed to be rendered within an accordion UI component.
 *
 * @return array Form configuration compatible with WPP form renderer.
 *
 * @since 1.0.0
 *
 * @see https://developer.wordpress.org/plugins/settings/settings-api/ For field patterns.
 * @see https://www.php.net/manual/en/function.array.php For array handling.
 *
 * @example
 *     $fields = wpp_step_config_u();
 *     foreach ($fields as $field) {
 *         render_field($field);
 *     }
 */
function wpp_step_config_u() {
	$form_fields = [
		'loan_type'                         => [
			'type'    => 'select',
			'name'    => 'loan_type',
			'label'   => 'Loan Type',
			'options' => [
				'purchase'  => 'Purchase',
				'refinance' => 'Refinance/ETO',
			],
			'width'   => 'full'
		],
		'application_type'                  => [
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
		'loan_purpose'                      => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'loan_purpose',
			'label'        => 'Loan Purpose',
			'width'        => 'full'
		],
		'mers_loan'                         => [
			'type'  => 'checkbox',
			'name'  => 'mers_loan',
			'label' => 'MERS Loan',
			'width' => 'full'
		],
		'purchase_price'                    => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'purchase_price',
			'label'        => 'Purchase Price',
			'placeholder'  => '$0.00',
			'data-type'    => 'money',
			'width'        => 'full'
		],
		'current_value'                     => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'current_value',
			'label'        => 'Current Value',
			'placeholder'  => '$0.00',
			'data-type'    => 'money',
			'width'        => 'full'
		],
		'after_repair_value'                => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'after_repair_value',
			'label'        => 'ARV',
			'placeholder'  => '$0.00',
			'data-type'    => 'money',
			'width'        => 'full'
		],
		'total_loan_amount'                 => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'total_loan_amount',
			'label'        => 'Total Loan',
			'placeholder'  => '$0.00',
			'data-type'    => 'money',
			'width'        => 'full'
		],
		'total_repair_cost'                 => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'total_repair_cost',
			'label'        => 'Total Repair Cost',
			'placeholder'  => '$0.00',
			'data-type'    => 'money',
			'width'        => 'full'
		],
		'advance_at_closing'                => [
			'type'         => 'text',
			'element_type' => 'money',
			'name'         => 'advance_at_closing',
			'label'        => 'Advance at Closing',
			'placeholder'  => '$0.00',
			'data-type'    => 'money',
			'width'        => 'full'
		],
		'loan_position'                     => [
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
		'interest_rate'                     => [
			'type'         => 'text',
			'element_type' => 'percentage',
			'name'         => 'interest_rate',
			'label'        => 'Interest Rate',
			'placeholder'  => '0.00%',
			'width'        => '1/2'
		],
		'enable_variable_rate'              => [
			'classes' => [ 'wpp-no-label-inverse no-left' ],
			'type'    => 'checkbox',
			'name'    => 'enable_variable_rate',
			'label'   => 'enable variable rate',
			'width'   => '1/2'
		],
		'standby_interest'                  => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'standby_interest',
			'label'        => 'Standby Interest (%)',
			'placeholder'  => 'e.g., 1.00',
			'width'        => 'full'
		],
		'repayment_type'                    => [
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
		'_monthly_payment'                  => [
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
		'interest_adjustment'               => [
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
		'term'                              => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'term',
			'label'        => 'Term',
			'placeholder'  => 'e.g., 12 months',
			'width'        => 'full'
		],
		'interest_schedule'                 => [
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
		],
		'interest_reserve_months'           => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'interest_reserve_months',
			'label'        => 'or',
			'placeholder'  => 'e.g., 6',
			'width'        => '1/2',
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
		],
		'interest_reserve_deduction_months' => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'interest_reserve_deduction_months',
			'label'        => 'or',
			'placeholder'  => 'e.g., 6',
			'width'        => '1/2',
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
			'type'    => 'select',
			'name'    => 'broker',
			'label'   => 'Broker',
			'options' => get_all_brokers_as_array(),
			'width'   => 'full'
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
		'closing_date'                      => [
			'type'        => 'datepicker',
			'name'        => 'closing_date',
			'label'       => 'Closing Date',
			'placeholder' => 'MM/DD/YYYY',
			'width'       => 'full'
		],
		'deadline_to_accept'                => [
			'type'        => 'datepicker',
			'name'        => 'deadline_to_accept',
			'label'       => 'Deadline to Accept',
			'placeholder' => 'MM/DD/YYYY',
			'width'       => 'full'
		],
	];

	/**
	 * Filters the term sheet form configuration.
	 *
	 * Allows third-party code to modify or extend the loan terms form.
	 *
	 * @param array $form_fields The form field configuration.
	 *
	 * @since 1.0.0
	 *
	 */
	return apply_filters( 'wpp/form/step_config/term_sheet', $form_fields );
}

/**
 * Outputs the Term Sheet Details tab in the loan management portal.
 *
 * Displays all loan terms in a single, open accordion for easy review and editing.
 * Hooked to `wpp_lmp_loan_content` with priority 40 to position it logically
 * between other steps like property details and payments.
 *
 * @return void Outputs HTML directly.
 *
 * @hooked wpp_lmp_loan_content
 * @priority 40
 *
 * @since 1.0.0
 *
 * @see wpp_step_config_u() For form field definitions.
 * @see WPP_Accordion_Field For UI component.
 *
 * @example
 *     This function is automatically called by WordPress during page rendering.
 */
function wpp_term_sheet_details() {
	?>
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
	<?php
}

add_action( 'wpp_lmp_loan_content', 'wpp_term_sheet_details', 40 );

/*
 * @todo List
 *
 * 1. Implement conditional logic for `interest_reserve_*` fields (currently commented).
 * 2. Calculate and update `_monthly_payment` dynamically via JavaScript or PHP.
 * 3. Validate `closing_date` and `deadline_to_accept` (ensure closing <= deadline).
 * 4. Add field sanitization and validation on save.
 * 5. Encrypt sensitive data like broker email and fees.
 * 6. Localize datepicker and field labels.
 * 7. Add support for multiple brokers or co-brokers.
 * 8. Implement real-time calculation of interest reserve based on term and rate.
 * 9. Add ARIA labels and improve accessibility.
 * 10. Write unit tests for form configuration and rendering logic.
 */