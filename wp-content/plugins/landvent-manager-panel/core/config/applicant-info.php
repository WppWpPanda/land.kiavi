<?php

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
					'classes'      => [ 'vertical-orient' ],
					'type'         => 'text',
					'element_type' => 'text',
					'name'         => 'bower_name',
					'label'        => 'Full Name',
					'width'        => '1/3',
					'default'      => wpp_field_value( 'bower_name', $data ),
				],
				'bower_email' => [
					'classes'      => [ 'vertical-orient' ],
					'type'         => 'text',
					'element_type' => 'text',
					'name'         => 'bower_email',
					'label'        => 'Email',
					'width'        => '1/3',
					'default'      => wpp_field_value( 'bower_email', $data ),
				],
				'bower_phone' => [
					'classes' => [ 'vertical-orient' ],
					'type'    => 'text',
					'name'    => 'bower_phone',
					'label'   => 'Phone',
					'width'   => '1/3',
					'default' => wpp_field_value( 'bower_phone', $data ),
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
					'default'      => wpp_field_value( 'bower_ach_routing', $data ),
				],
				'bower_ach_account'           => [
					'type'         => 'text',
					'element_type' => 'text',
					'name'         => 'bower_ach_account',
					'label'        => 'Account',
					'width'        => 'full',
					'default'      => wpp_field_value( 'bower_ach_account', $data ),
				],
				'bower_use_for_loan_payments' => [
					'classes' => [ 'wpp-no-label-inverse' ],
					'type'    => 'checkbox',
					'name'    => 'bower_use_for_loan_payments',
					'label'   => 'use for loan payments',
					'width'   => '1/2',
					'default' => wpp_field_value( 'bower_use_for_loan_payments', $data, 'yes' ),
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
					'default' => wpp_field_value( 'bower_ach_account_type', $data, 'checking' ),
				],

			],
			'width'  => 'full'
		]
	];

	return $form_fields;
}

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
					'classes'      => [ 'vertical-orient' ],
					'type'         => 'text',
					'element_type' => 'text',
					'name'         => 'Guarantor_name',
					'label'        => 'Full Name',
					'width'        => '1/3',
					'default'      => wpp_field_value( 'guarantor_name', $data ),
				],
				'guarantor_email' => [
					'classes'      => [ 'vertical-orient' ],
					'type'         => 'text',
					'element_type' => 'text',
					'name'         => 'guarantor_email',
					'label'        => 'Email',
					'width'        => '1/3',
					'default'      => wpp_field_value( 'guarantor_email', $data ),
				],
				'guarantor_phone' => [
					'classes' => [ 'vertical-orient' ],
					'type'    => 'text',
					'name'    => 'guarantor_phone',
					'label'   => 'Phone',
					'width'   => '1/3',
					'default' => wpp_field_value( 'guarantor_phone', $data ),
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
					'default'      => wpp_field_value( 'guarantor_ach_account_name', $data )
				],
				'guarantor_ach_routing'           => [
					'type'         => 'text',
					'element_type' => 'text',
					'name'         => 'guarantor_ach_routing',
					'label'        => 'Routing',
					'placeholder'  => '#########,#########',
					'width'        => 'full',
					'default'      => wpp_field_value( 'guarantor_ach_routing', $data )

				],
				'guarantor_ach_account'           => [
					'type'         => 'text',
					'element_type' => 'text',
					'name'         => 'guarantor_ach_account',
					'label'        => 'Account',
					'width'        => 'full',
					'default'      => wpp_field_value( 'guarantor_ach_account', $data )
				],
				'guarantor_use_for_loan_payments' => [
					'classes' => [ 'wpp-no-label-inverse' ],
					'type'    => 'checkbox',
					'name'    => 'guarantor_use_for_loan_payments',
					'label'   => 'use for loan payments',
					'width'   => '1/2',
					'default' => wpp_field_value( 'guarantor_use_for_loan_payments', $data, 'yes' )
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
					'default' => wpp_field_value( 'guarantor_ach_account_typ', $data, 'checking' )
				],

			],
			'width'  => 'full'
		]
	];

	return $form_fields;
}

function wpp_step_config_applicant() {

	$applicant = new WPP_Accordion_Field( [
		'type'    => 'accordion',
		'name'    => 'applicant_block',
		'title'   => 'Borrowers',
		'content' => function () {
			foreach ( wpp_step_config_borrowers_info() as $name => $config ) {
				$class_name = 'WPP_' . ucfirst( $config['type'] ) . '_Field';

				if ( class_exists( $class_name ) ) {
					$field = new $class_name( array_merge( $config, [ 'name' => $name ] ) );
					$field->render();
				}
			}
		}

	] );

	$guarantor = new WPP_Accordion_Field( [
		'type'    => 'accordion',
		'name'    => 'guarantor_block',
		'title'   => 'Guarantors',
		'content' => function () {
			foreach ( wpp_step_config_guarantor_info() as $name => $config ) {
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
	$out .= $guarantor->render();


	return $out;

}

function wpp_term_applicant() { ?>

    <div id="applicant-info" class="container">
        <div class="row">
			<?php
			$cont = new WPP_Accordion_Field( [
				'type'    => 'accordion',
				'name'    => 'info_block',
				'title'   => 'Applicants',
				'content' => function () {
					foreach ( wpp_step_config_applicant() as $name => $config ) {
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

add_action( 'wpp_lmp_loan_content', 'wpp_term_applicant', 10 );