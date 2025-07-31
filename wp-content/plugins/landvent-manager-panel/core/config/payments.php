<?php
function wpp_step_config_payments() {

	$form_fields = [


		'p_payment_type' => [
			'label' => '',
			'type' => 'select',
			'options' => [
				'ach' => 'ACH',
				'check' => 'Check',
				'debit' => 'Debit',
				'wire_transfer' => 'Wire Transfer',
				'cash' => 'Cash',
				'email_money_transfer' => 'Email Money Transfer',
				'credit_card' => 'Credit Card'
			],
			'required' => true,
            'width' => '1/3'
		],

		'paid_directly' => [
			'label'     => 'Paid directly to Investor',
			'type'      => 'checkbox',
			'show_when' => [ 'payment_type' => 'credit_card' ],
			'toggle'    => [
				'target_section' => 'ach_info',
				'action'         => 'hide'
			],
			'width' => '1/3'
		],

		'account_name_h'   => [
			'type'     => 'content',
			'name' => 'account_name_h',
            'content' => '<h3>Debit ACH Info</h3><hr>',
            'width' => 'full'
		],
		'account_name_dach'   => [
			'label'    => 'Account Name',
			'type'     => 'text',
			'width' => '2/3'
		],
		'routing_number_dach' => [
			'label'    => 'Routing',
			'type'     => 'text',
			'width' => '2/3',
			//'pattern'  => '\d{9}'
            'placeholder' => '#########,#########'
		],
		'account_dach'   => [
			'label'    => 'Account',
			'type'     => 'text',
			'width' => '2/3'
		],
		'account_type_dach'   => [
			'label'    => 'Account Type',
			'type'     => 'select',
			'options'  => [
				'checking' => 'Checking',
				'savings'  => 'Savings'
			],
			'width' => '2/3'
		],
		'deposit_to_dach'     => [
			'label'     => 'Deposit To',
			'type'      => 'text',
			//'show_when' => [ 'payment_type' => 'debit_ach' ],
			'default'   => '',
			'width' => '2/3'
		],


		'account_name_c'   => [
			'type'     => 'content',
			'name' => 'account_name_h',
			'content' => '<h3>Credit ACH Info (for Additional Draws/Advances)</h3><hr>',
			'width' => 'full'
		],
		'account_name_cach'   => [
			'label'    => 'Account Name',
			'type'     => 'text',
			'width' => '2/3'
		],
		'routing_number_cach' => [
			'label'    => 'Routing',
			'type'     => 'text',
			'width' => '2/3',
			//'pattern'  => '\d{9}'
			'placeholder' => '#########,#########'
		],
		'account_cach'   => [
			'label'    => 'Account',
			'type'     => 'text',
			'width' => '2/3'
		],
		'account_type_cach'   => [
			'label'    => 'Account Type',
			'type'     => 'select',
			'options'  => [
				'checking' => 'Checking',
				'savings'  => 'Savings'
			],
			'width' => '2/3'
		]
	];

	return $form_fields;
}


function wpp_step_config_inbound () {

	$form_fields = [


		'loanpminsetup' => [
			'label' => '',
			'type' => 'select',
			'options' => [
				'System Percent' => 'System Percent',
			],
			'required' => true,
			'width' => '1/3'
		]
	];

	return $form_fields;
}

function wpp_step_payment() {

	$applicant = new WPP_Accordion_Field( [
		'type'    => 'accordion',
		'name'    => 'payment_method_1',
		'title'   => 'Payment Method',
		'content' => function () {
			foreach ( wpp_step_config_payments() as $name => $config ) {
				$class_name = 'WPP_' . ucfirst( $config['type'] ) . '_Field';

				if ( class_exists( $class_name ) ) {
					$field = new $class_name( array_merge( $config, [ 'name' => $name ] ) );
					$field->render();
				}
			}
		}

	] );

	$applicant_2 = new WPP_Accordion_Field( [
		'type'    => 'accordion',
		'name'    => 'payment_method_2',
		'title'   => 'Inbound Payment Automation',
		'content' => function () {
			foreach ( wpp_step_config_inbound () as $name => $config ) {
				$class_name = 'WPP_' . ucfirst( $config['type'] ) . '_Field';

				if ( class_exists( $class_name ) ) {
					$field = new $class_name( array_merge( $config, [ 'name' => $name ] ) );
					$field->render();
				}
			}
		}

	] );



	$out = '';
	$out .= $applicant->render();
	$out .= $applicant_2->render();

    return $out;
}

function wpp_term_payments() { ?>
    <div id="payments" class="container">
        <div class="row">
			<?php
			$cont = new WPP_Accordion_Field( [
				'type'    => 'accordion',
				'name'    => 'info_payments',
				'title'   => 'Payments',
				'content' => function () {
					foreach ( wpp_step_payment() as $name => $config ) {
						$class_name = 'WPP_' . ucfirst( $config['type'] ) . '_Field';

						if ( class_exists( $class_name ) ) {
							$field = new $class_name( array_merge( $config, [ 'name' => $name ] ) );
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

add_action( 'wpp_lmp_loan_content', 'wpp_term_payments', 70 );