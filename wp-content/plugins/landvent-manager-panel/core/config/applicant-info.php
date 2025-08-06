<?php
/**
 * Applicant and Guarantor Information Configuration for Loan Management Plugin
 *
 * This file defines the form structure and rendering logic for collecting borrower and
 * guarantor information in a loan application, including personal details and ACH
 * (Automated Clearing House) banking information.
 *
 * The UI uses nested `super_accordion` components to group related data and improve
 * user experience. All fields are dynamically pre-filled using saved loan data.
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
 * Generates configuration for borrower information fields.
 *
 * Includes:
 * - Full name, email, phone (displayed in a compact header)
 * - ACH details: account name, routing number, account number
 * - Option to use ACH for loan payments (checkbox)
 * - Account type selection (Checking, Savings, Loan Account)
 *
 * Uses `wpp_get_loan_data_r()` to retrieve existing data and `wpp_field_value()`
 * to safely extract field values with fallbacks.
 *
 * @since 1.0.0
 *
 * @return array Form configuration array for borrower details.
 *
 * @see wpp_get_loan_data_r()        For source of persisted loan data.
 * @see wpp_field_value()            For safe value extraction with defaults.
 * @see https://developer.wordpress.org/plugins/settings/settings-api/ For field patterns.
 *
 * @example
 *     $fields = wpp_step_config_borrowers_info();
 *     foreach ($fields as $field) {
 *         render_field($field);
 *     }
 */
function wpp_step_config_borrowers_info() {
	$data = wpp_get_loan_data_r();

	$form_fields = [
		'bower_info' => [
			'type'   => 'super_accordion',
			'name'   => 'bower_info',
			'title'  => '',
			'header' => '{bower_name}{bower_email}{bower_phone}',
			'fields' => [
				'bower_name'  => [
					'classes'      => ['vertical-orient'],
					'type'         => 'text',
					'element_type' => 'text',
					'name'         => 'bower_name',
					'label'        => 'Full Name',
					'width'        => '1/3',
					'default'      => wpp_field_value('bower_name', $data),
				],
				'bower_email' => [
					'classes'      => ['vertical-orient'],
					'type'         => 'text',
					'element_type' => 'text',
					'name'         => 'bower_email',
					'label'        => 'Email',
					'width'        => '1/3',
					'default'      => wpp_field_value('bower_email', $data),
				],
				'bower_phone' => [
					'classes' => ['vertical-orient'],
					'type'    => 'text',
					'name'    => 'bower_phone',
					'label'   => 'Phone',
					'width'   => '1/3',
					'default' => wpp_field_value('bower_phone', $data),
				]
			],
			'width'  => 'full'
		],
		'bower_ach'  => [
			'type'   => 'super_accordion',
			'name'   => 'bower_ach',
			'title'  => '',
			'header' => 'ACH Details',
			'fields' => [
				'bower_ach_account_name'      => [
					'type'         => 'text',
					'element_type' => 'text',
					'name'         => 'bower_ach_account_name',
					'label'        => 'Account Name',
					'width'        => 'full'
				],
				'bower_ach_routing'           => [
					'type'         => 'text',
					'element_type' => 'text',
					'name'         => 'bower_ach_routing',
					'label'        => 'Routing',
					'placeholder'  => '#########,#########',
					'width'        => 'full',
					'default'      => wpp_field_value('bower_ach_routing', $data),
				],
				'bower_ach_account'           => [
					'type'         => 'text',
					'element_type' => 'text',
					'name'         => 'bower_ach_account',
					'label'        => 'Account',
					'width'        => 'full',
					'default'      => wpp_field_value('bower_ach_account', $data),
				],
				'bower_use_for_loan_payments' => [
					'classes' => ['wpp-no-label-inverse'],
					'type'    => 'checkbox',
					'name'    => 'bower_use_for_loan_payments',
					'label'   => 'use for loan payments',
					'width'   => '1/2',
					'default' => wpp_field_value('bower_use_for_loan_payments', $data, 'yes'),
				],
				'bower_ach_account_type'      => [
					'type'    => 'radio',
					'name'    => 'bower_ach_account_type',
					'label'   => 'Account Type',
					'options' => [
						'checking'     => 'Checking',
						'savings'      => 'Savings',
						'loan_account' => 'Loan Account'
					],
					'width'   => 'full',
					'default' => wpp_field_value('bower_ach_account_type', $data, 'checking'),
				],
			],
			'width'  => 'full'
		]
	];

	/**
	 * Filters the borrower information form configuration.
	 *
	 * Allows third-party code to modify or extend the form fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_fields The form field configuration.
	 */
	return apply_filters('wpp/form/step_config/borrowers_info', $form_fields);
}

/**
 * Generates configuration for guarantor information fields.
 *
 * Mirrors the borrower structure but for guarantors. Includes:
 * - Personal contact info (name, email, phone)
 * - ACH details
 * - Usage flag and account type
 *
 * @since 1.0.0
 *
 * @return array Form configuration array for guarantor details.
 *
 * @example
 *     $fields = wpp_step_config_guarantor_info();
 */
function wpp_step_config_guarantor_info() {
	$data = wpp_get_loan_data_r();

	$form_fields = [
		'guarantor_info' => [
			'type'   => 'super_accordion',
			'name'   => 'guarantor_info',
			'title'  => '',
			'header' => '{guarantor_name}{guarantor_email}{guarantor_phone}',
			'fields' => [
				'guarantor_name'  => [
					'classes'      => ['vertical-orient'],
					'type'         => 'text',
					'element_type' => 'text',
					'name'         => 'Guarantor_name',
					'label'        => 'Full Name',
					'width'        => '1/3',
					'default'      => wpp_field_value('guarantor_name', $data),
				],
				'guarantor_email' => [
					'classes'      => ['vertical-orient'],
					'type'         => 'text',
					'element_type' => 'text',
					'name'         => 'guarantor_email',
					'label'        => 'Email',
					'width'        => '1/3',
					'default'      => wpp_field_value('guarantor_email', $data),
				],
				'guarantor_phone' => [
					'classes' => ['vertical-orient'],
					'type'    => 'text',
					'name'    => 'guarantor_phone',
					'label'   => 'Phone',
					'width'   => '1/3',
					'default' => wpp_field_value('guarantor_phone', $data),
				]
			],
			'width'  => 'full'
		],
		'guarantor_ach'  => [
			'type'   => 'super_accordion',
			'name'   => 'guarantor_ach',
			'title'  => '',
			'header' => 'ACH Details',
			'fields' => [
				'guarantor_ach_account_name'      => [
					'type'         => 'text',
					'element_type' => 'text',
					'name'         => 'guarantor_ach_account_name',
					'label'        => 'Account Name',
					'width'        => 'full',
					'default'      => wpp_field_value('guarantor_ach_account_name', $data)
				],
				'guarantor_ach_routing'           => [
					'type'         => 'text',
					'element_type' => 'text',
					'name'         => 'guarantor_ach_routing',
					'label'        => 'Routing',
					'placeholder'  => '#########,#########',
					'width'        => 'full',
					'default'      => wpp_field_value('guarantor_ach_routing', $data)
				],
				'guarantor_ach_account'           => [
					'type'         => 'text',
					'element_type' => 'text',
					'name'         => 'guarantor_ach_account',
					'label'        => 'Account',
					'width'        => 'full',
					'default'      => wpp_field_value('guarantor_ach_account', $data)
				],
				'guarantor_use_for_loan_payments' => [
					'classes' => ['wpp-no-label-inverse'],
					'type'    => 'checkbox',
					'name'    => 'guarantor_use_for_loan_payments',
					'label'   => 'use for loan payments',
					'width'   => '1/2',
					'default' => wpp_field_value('guarantor_use_for_loan_payments', $data, 'yes')
				],
				'guarantor_ach_account_type'      => [
					'type'    => 'radio',
					'name'    => 'guarantor_ach_account_type',
					'label'   => 'Account Type',
					'options' => [
						'checking'     => 'Checking',
						'savings'      => 'Savings',
						'loan_account' => 'Loan Account'
					],
					'width'   => 'full',
					'default' => wpp_field_value('guarantor_ach_account_type', $data, 'checking')
				],
			],
			'width'  => 'full'
		]
	];

	/**
	 * Filters the guarantor information form configuration.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_fields The configuration array.
	 */
	return apply_filters('wpp/form/step_config/guarantor_info', $form_fields);
}

/**
 * Renders the full applicant UI using accordions.
 *
 * Combines:
 * - Borrower information section
 * - Guarantor information section
 *
 * Dynamically instantiates field classes based on type (e.g., WPP_SuperAccordion_Field).
 *
 * @since 1.0.0
 *
 * @return string HTML output of the complete applicant UI.
 *
 * @see wpp_step_config_borrowers_info() For borrower fields.
 * @see wpp_step_config_guarantor_info() For guarantor fields.
 * @see WPP_Accordion_Field For UI component.
 *
 * @todo Fix typo in default value key: 'guarantor_ach_account_typ' → 'guarantor_ach_account_type'.
 * @todo Add validation for ACH routing/account numbers.
 * @todo Support multiple borrowers and guarantors via repeater.
 * @todo Encrypt sensitive banking data before storage.
 */
function wpp_step_config_applicant() {
	$applicant = new WPP_Accordion_Field([
		'type'    => 'accordion',
		'name'    => 'applicant_block',
		'title'   => 'Borrowers',
		'content' => function () {
			foreach (wpp_step_config_borrowers_info() as $name => $config) {
				$class_name = 'WPP_' . ucfirst($config['type']) . '_Field';

				if (class_exists($class_name)) {
					$field = new $class_name(array_merge($config, ['name' => $name]));
					$field->render();
				}
			}
		}
	]);

	$guarantor = new WPP_Accordion_Field([
		'type'    => 'accordion',
		'name'    => 'guarantor_block',
		'title'   => 'Guarantors',
		'content' => function () {
			foreach (wpp_step_config_guarantor_info() as $name => $config) {
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
	$out .= $guarantor->render();

	return $out;
}

/**
 * Outputs the Applicant tab in the loan management portal.
 *
 * Hooked to `wpp_lmp_loan_content` with priority 10 to position it early in the flow.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML directly.
 *
 * @hooked wpp_lmp_loan_content
 * @priority 10
 *
 * @example
 *     This function is automatically called by WordPress during page rendering.
 */
function wpp_term_applicant() {
	?>
    <div id="applicant-info" class="container">
        <div class="row">
			<?php
			$cont = new WPP_Accordion_Field([
				'type'    => 'accordion',
				'name'    => 'info_block',
				'title'   => 'Applicants',
				'content' => function () {
					wpp_step_config_applicant();
				}
			]);

			$cont->render();
			?>
        </div>
    </div>
	<?php
}
add_action('wpp_lmp_loan_content', 'wpp_term_applicant', 10);

/*
 * @todo List
 *
 * 1. Fix typo: 'guarantor_ach_account_typ' → 'guarantor_ach_account_type' in default value.
 * 2. Validate ACH routing number (must be 9 digits).
 * 3. Support multiple borrowers and guarantors using a repeater field.
 * 4. Encrypt ACH account and routing numbers before saving to database.
 * 5. Add conditional logic to show/hide ACH section based on payment method.
 * 6. Localize all field labels and messages.
 * 7. Implement accessibility improvements (ARIA labels, keyboard nav).
 * 8. Add email validation for email fields.
 * 9. Write unit tests for form configurations.
 * 10. Refactor to use a factory pattern for field instantiation.
 */