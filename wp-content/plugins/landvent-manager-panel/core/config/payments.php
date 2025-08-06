<?php
/**
 * Payments Step Configuration for Loan Management Plugin
 *
 * This file defines the form structure and rendering logic for the "Payments" step
 * in a loan management workflow. It includes configuration for:
 * - Payment methods (ACH, Check, Debit, Credit Card, etc.)
 * - Debit ACH and Credit ACH banking details
 * - Inbound payment automation settings
 *
 * The UI is organized using accordions and supports conditional logic (e.g., hiding
 * fields based on payment type). All components are rendered via dynamic field classes.
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
 * Generates configuration for payment method selection and ACH details.
 *
 * Builds a form array that allows borrowers to select their preferred payment method
 * and provide corresponding banking information. Includes:
 * - Payment type dropdown
 * - Direct payment checkbox (conditionally hides ACH info)
 * - Debit ACH info section (account name, routing, account number, type, deposit)
 * - Credit ACH info (for future advances)
 *
 * Uses conditional rendering (`show_when`, `toggle`) to improve UX.
 *
 * @since 1.0.0
 *
 * @return array Form configuration compatible with WPP form renderer.
 *
 * @see https://developer.wordpress.org/plugins/settings/settings-api/ For field patterns.
 * @see https://www.php.net/manual/en/language.types.array.php For array syntax.
 *
 * @example
 *     $fields = wpp_step_config_payments();
 *     foreach ($fields as $field) {
 *         render_field($field);
 *     }
 */
function wpp_step_config_payments() {
	$form_fields = [
		'p_payment_type' => [
			'label'    => '',
			'type'     => 'select',
			'options'  => [
				'ach'                  => 'ACH',
				'check'                => 'Check',
				'debit'                => 'Debit',
				'wire_transfer'        => 'Wire Transfer',
				'cash'                 => 'Cash',
				'email_money_transfer' => 'Email Money Transfer',
				'credit_card'          => 'Credit Card'
			],
			'required' => true,
			'width'    => '1/3'
		],

		'paid_directly' => [
			'label'     => 'Paid directly to Investor',
			'type'      => 'checkbox',
			'show_when' => [ 'payment_type' => 'credit_card' ],
			'toggle'    => [
				'target_section' => 'ach_info',
				'action'         => 'hide'
			],
			'width'     => '1/3'
		],

		'account_name_h'      => [
			'type'    => 'content',
			'name'    => 'account_name_h',
			'content' => '<h3>Debit ACH Info</h3><hr>',
			'width'   => 'full'
		],
		'account_name_dach'   => [
			'label' => 'Account Name',
			'type'  => 'text',
			'width' => '2/3'
		],
		'routing_number_dach' => [
			'label'       => 'Routing',
			'type'        => 'text',
			'width'       => '2/3',
			'placeholder' => '#########,#########'
		],
		'account_dach'        => [
			'label' => 'Account',
			'type'  => 'text',
			'width' => '2/3'
		],
		'account_type_dach'   => [
			'label'   => 'Account Type',
			'type'    => 'select',
			'options' => [
				'checking' => 'Checking',
				'savings'  => 'Savings'
			],
			'width'   => '2/3'
		],
		'deposit_to_dach'     => [
			'label'   => 'Deposit To',
			'type'    => 'text',
			'default' => '',
			'width'   => '2/3'
		],

		'account_name_c'      => [
			'type'    => 'content',
			'name'    => 'account_name_h',
			'content' => '<h3>Credit ACH Info (for Additional Draws/Advances)</h3><hr>',
			'width'   => 'full'
		],
		'account_name_cach'   => [
			'label' => 'Account Name',
			'type'  => 'text',
			'width' => '2/3'
		],
		'routing_number_cach' => [
			'label'       => 'Routing',
			'type'        => 'text',
			'width'       => '2/3',
			'placeholder' => '#########,#########'
		],
		'account_cach'        => [
			'label' => 'Account',
			'type'  => 'text',
			'width' => '2/3'
		],
		'account_type_cach'   => [
			'label'   => 'Account Type',
			'type'    => 'select',
			'options' => [
				'checking' => 'Checking',
				'savings'  => 'Savings'
			],
			'width'   => '2/3'
		]
	];

	/**
	 * Filters the payment method form configuration.
	 *
	 * Allows third-party code to modify or extend the payment fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_fields The form field configuration.
	 */
	return apply_filters('wpp/form/step_config/payments', $form_fields);
}

/**
 * Generates configuration for inbound payment automation settings.
 *
 * Currently includes a single select field for system-based percentage setup.
 * Designed to be extended with automation rules in future versions.
 *
 * @since 1.0.0
 *
 * @return array Form configuration for inbound automation.
 *
 * @example
 *     $inbound = wpp_step_config_inbound();
 */
function wpp_step_config_inbound() {
	$form_fields = [
		'loanpminsetup' => [
			'label'    => '',
			'type'     => 'select',
			'options'  => [
				'System Percent' => 'System Percent',
			],
			'required' => true,
			'width'    => '1/3'
		]
	];

	/**
	 * Filters the inbound automation form configuration.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_fields The configuration array.
	 */
	return apply_filters('wpp/form/step_config/inbound_automation', $form_fields);
}

/**
 * Renders the full payment configuration UI using accordions.
 *
 * Combines:
 * - Payment Method section
 * - Inbound Payment Automation section
 *
 * Dynamically instantiates field classes based on type (e.g., WPP_Select_Field).
 *
 * @since 1.0.0
 *
 * @return string HTML output of the complete payment UI.
 *
 * @see wpp_step_config_payments() For payment method fields.
 * @see wpp_step_config_inbound() For automation fields.
 * @see WPP_Accordion_Field For UI component.
 *
 * @todo Implement validation for ACH routing/account numbers.
 * @todo Add support for multiple investors in "Deposit To".
 * @todo Localize field labels.
 */
function wpp_step_payment() {
	$applicant = new WPP_Accordion_Field([
		'type'    => 'accordion',
		'name'    => 'payment_method_1',
		'title'   => 'Payment Method',
		'content' => function () {
			foreach (wpp_step_config_payments() as $name => $config) {
				$class_name = 'WPP_' . ucfirst($config['type']) . '_Field';

				if (class_exists($class_name)) {
					$field = new $class_name(array_merge($config, ['name' => $name]));
					$field->render();
				}
			}
		}
	]);

	$applicant_2 = new WPP_Accordion_Field([
		'type'    => 'accordion',
		'name'    => 'payment_method_2',
		'title'   => 'Inbound Payment Automation',
		'content' => function () {
			foreach (wpp_step_config_inbound() as $name => $config) {
				$class_name = 'WPP_' . ucfirst($config['type']) . '_Field';

				if (class_exists($class_name)) {
					$field = new $class_name(array_merge($config, ['name' => $name]));
					$field->render();
				}
			}
		}
	]);

	$out = '';
	$out .= $applicant->render();
	$out .= $applicant_2->render();

	return $out;
}

/**
 * Outputs the Payments tab in the loan management portal.
 *
 * Hooked to `wpp_lmp_loan_content` with priority 70 to position it after other steps.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML directly.
 *
 * @hooked wpp_lmp_loan_content
 * @priority 70
 *
 * @example
 *     This function is automatically called by WordPress during page rendering.
 */
function wpp_term_payments() {
	?>
    <div id="payments" class="container">
        <div class="row">
			<?php
			$cont = new WPP_Accordion_Field([
				'type'    => 'accordion',
				'name'    => 'info_payments',
				'title'   => 'Payments',
				'content' => function () {
					wpp_step_payment();
				}
			]);

			$cont->render();
			?>
        </div>
    </div>
	<?php
}
add_action('wpp_lmp_loan_content', 'wpp_term_payments', 70);

/*
 * @todo List
 *
 * 1. Validate ACH routing number (9 digits) and account number length.
 * 2. Add conditional logic to show/hide Debit ACH section based on payment type.
 * 3. Implement encryption for sensitive bank data before saving.
 * 4. Add support for multiple payment methods per loan.
 * 5. Integrate with payment gateway APIs (Stripe, Plaid, etc.).
 * 6. Localize all field labels and messages.
 * 7. Write unit tests for form configurations.
 * 8. Add accessibility attributes (ARIA) to dynamic fields.
 * 9. Support file uploads for voided check or bank verification.
 * 10. Refactor to use a factory pattern for field instantiation.
 */