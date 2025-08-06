<?php
/**
 * Fees Configuration for Loan Management Plugin
 *
 * This file defines the structure and rendering logic for the "Fees" step in a loan
 * application workflow. It includes dynamic fee fields that support both percentage-based
 * and fixed monetary values, with calculations based on the total loan amount.
 *
 * A custom UI component allows users to add arbitrary additional fees dynamically via JavaScript.
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
 * Generates configuration for loan-related fee fields.
 *
 * Builds a list of fee inputs, each supporting dual input (percentage and money).
 * The monetary value is calculated based on a base amount (total loan), enabling
 * real-time preview of fee amounts.
 *
 * Includes:
 * - Deposit
 * - Broker, Origination, Lender, Processing fees
 * - Appraisal and Site Inspection fees
 * - Dynamic container for user-defined fees with "Add" button
 *
 * @since 1.0.0
 *
 * @return array Form configuration array for fee inputs.
 *
 * @see wpp_get_total_loan_amount() For base amount used in calculations.
 * @see https://developer.wordpress.org/plugins/settings/settings-api/ For field patterns.
 * @see https://www.php.net/manual/en/language.types.float.php For float handling.
 *
 * @example
 *     $fees = wpp_step_config_fees();
 *     foreach ($fees as $field) {
 *         render_field($field);
 *     }
 */
function wpp_step_config_fees() {
	// Retrieve the base amount (e.g., total loan) for percentage calculations
	$base_amount = (float) wpp_get_total_loan_amount();

	// Fallback to 0 if no valid amount is returned
	$base_amount = $base_amount > 0 ? $base_amount : 0;

	/**
	 * Filters the base amount used in fee calculations.
	 *
	 * Allows third-party code to modify the base value (e.g., ARV, purchase price).
	 *
	 * @since 1.0.0
	 *
	 * @param float $base_amount The calculated base amount.
	 */
	$base_amount = apply_filters('wpp/fees/base_amount', $base_amount);

	$fields = [
		'fee_deposit'             => [
			'type'        => 'percent_money',
			'name'        => 'fee_deposit',
			'label'       => 'Deposit',
			'width'       => 'full',
			'base_amount' => $base_amount,
		],
		'fee_broker_fee'          => [
			'type'        => 'percent_money',
			'name'        => 'fee_broker_fee',
			'label'       => 'Broker Fee',
			'width'       => 'full',
			'base_amount' => $base_amount,
		],
		'fee_origination_fee'     => [
			'type'        => 'percent_money',
			'name'        => 'fee_origination_fee',
			'label'       => 'Origination Fee',
			'width'       => 'full',
			'base_amount' => $base_amount,
		],
		'fee_lender_fee'          => [
			'type'        => 'percent_money',
			'name'        => 'fee_lender_fee',
			'label'       => 'Lender Fee',
			'width'       => 'full',
			'base_amount' => $base_amount,
		],
		'fee_processing_fee'      => [
			'type'        => 'percent_money',
			'name'        => 'fee_processing_fee',
			'label'       => 'Processing Fee',
			'width'       => 'full',
			'base_amount' => $base_amount,
		],
		'fee_site_inspection_fee' => [
			'type'        => 'percent_money',
			'name'        => 'fee_site_inspection_fee',
			'label'       => 'Site Inspection Fee',
			'width'       => 'full',
			'base_amount' => $base_amount,
		],
		'fee_appraisal_fee'       => [
			'type'        => 'percent_money',
			'name'        => 'fee_appraisal_fee',
			'label'       => 'Appraisal Fee',
			'width'       => 'full',
			'base_amount' => $base_amount,
		],
		'fee_custom'              => [
			'type'    => 'content',
			'name'    => 'fee_custom',
			'label'   => '',
			'width'   => 'full',
			'content' => '
                <!-- Container for dynamically added custom fees -->
                <div id="custom-fees-container">
                    <h4>Additional Fees</h4>
                    <!-- Dynamic fee entries will be injected here by JavaScript -->
                </div>
                
                <!-- Button to add new custom fee -->
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="add-custom-fee">
                    + Add New Fee
                </button>
            ',
		],
	];

	/**
	 * Filters the fees form configuration.
	 *
	 * Allows plugins or themes to add, remove, or modify fee fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields      The fee field configuration.
	 * @param float $base_amount The base amount used for calculations.
	 */
	return apply_filters('wpp/form/step_config/fees', $fields, $base_amount);
}

/**
 * Outputs the Fees tab in the loan management portal.
 *
 * Renders all fee-related inputs inside an accordion UI component. Supports:
 * - Percentage-to-monetary conversion based on loan amount
 * - Dynamic addition of custom fees via JavaScript
 *
 * Hooked to `wpp_lmp_loan_content` with priority 50 to position it after term sheet
 * and before final steps.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML directly.
 *
 * @hooked wpp_lmp_loan_content
 * @priority 50
 *
 * @see wpp_step_config_fees() For field definitions.
 * @see WPP_Accordion_Field For UI component.
 *
 * @example
 *     This function is automatically called by WordPress during page rendering.
 */
function wpp_term_fees() {
	?>
    <div id="fees" class="container">
        <div class="row">
			<?php
			$cont = new WPP_Accordion_Field([
				'type'    => 'accordion',
				'name'    => 'info_fees',
				'title'   => 'Fees',
				'content' => function () {
					foreach (wpp_step_config_fees() as $name => $config) {
						$class_name = 'WPP_' . ucfirst($config['type']) . '_Field';

						if (class_exists($class_name)) {
							$field = new $class_name(array_merge($config, ['name' => $name]));
							$field->render();
						}
					}
				}
			]);

			$cont->render();
			?>
        </div>
    </div>
	<?php
}
add_action('wpp_lmp_loan_content', 'wpp_term_fees', 50);

/*
 * @todo List
 *
 * 1. Implement wpp_get_total_loan_amount() with real data source (e.g., post meta).
 * 2. Add JavaScript to handle dynamic "Add New Fee" functionality.
 * 3. Save custom fees to database on form submission.
 * 4. Validate percentage values (0–100%) and monetary inputs.
 * 5. Add localization support for labels and currency.
 * 6. Encrypt sensitive financial data before storage.
 * 7. Support fee allocation (who pays: borrower, lender, broker).
 * 8. Add tooltips explaining each fee type.
 * 9. Implement real-time total fees calculator.
 * 10. Write unit tests for fee calculation logic.
 */