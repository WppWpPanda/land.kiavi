<?php
defined( 'ABSPATH' ) || exit;
function wpp_step_config_8( $step = false ) {
	$steps_data = [];
	for ( $i = 1; $i <= 7; $i ++ ) {
		$steps_data[ $i ] = WPP_Loan_Session_Handler::get_step_data( $i );
	}
	$content_t = <<<CON
                <h4>About This Application</h4>
                <ul>
                    <li>If you've recently applied for another loan with us, we will use the credit decision on file.</li>
                    <li>To ensure the most accurate pricing, we recommend the guarantor submit the loan application. This guarantor must have owned at least 25% of this entity for at least 180 days or since inception.</li>
                    <li>If you need to correct the guarantor for this loan, please go back and make that change.</li>
                    <li>If you need to change entities for this loan application, please contact <a href="mailto:support@hello.kiavi.com">support@hello.kiavi.com</a>.</li>
                </ul>
CON;
	$content_n = <<<CON
                <h4>About This Application</h4>
                <ul>
                    <li>If you've recently applied for another loan with us, we will use the credit decision on file.</li>
                    <li>To ensure the most accurate pricing, we recommend the guarantor submit the loan application. This guarantor must have owned at least 25% of this entity for at least 180 days or since inception.</li>
                    <li>If you need to correct the guarantor for this loan, please go back and make that change.</li>
                    <li>If you need to change entities for this loan application, please contact <a href="mailto:support@hello.kiavi.com">support@hello.kiavi.com</a>.</li>
                </ul>
CON;

	$form_fields_1 = [
		'entity_name'    => [
			'type'    => 'content',
			'name'    => 'entity_name',
			'label'   => 'Entity Name',
			'content' => '<p><strong>' . esc_html( $steps_data[2]['entity_name'] ?? 'N/A' ) . '</strong></p>',
			'width'   => 'full'
		],
		'first_name'     => [
			'type'    => 'content',
			'name'    => 'first_name',
			'label'   => 'First Name',
			'content' => '<p><strong>' . esc_html( $steps_data[2]['first_name'] ?? 'N/A' ) . '</strong></p>',
			'width'   => '1/3'
		],
		'last_name'      => [
			'type'    => 'content',
			'name'    => 'last_name',
			'label'   => 'Last Name',
			'content' => '<p><strong>' . esc_html( $steps_data[2]['last_name'] ?? 'N/A' ) . '</strong></p>',
			'width'   => '1/3'
		],
		'suffix'         => [
			'type'    => 'content',
			'name'    => 'suffix',
			'label'   => 'Suffix',
			'content' => '<p><strong>' . esc_html( $steps_data[2]['suffix'] ?? 'N/A' ) . '</strong></p>',
			'width'   => '1/3'
		],

		// Редактируемые поля из шага 7
		'date_of_birth'  => [
			'type'         => 'datepicker',
			'element_type' => 'text',
			'name'         => 'date_of_birth',
			'label'        => 'Date of Birth',
			'value'        => '',
			'placeholder'  => 'MM/DD/YYYY',
			'required'     => true,
			'width'        => 'full'
		],
		'address_line_1' => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'address_line_1',
			'label'        => 'Current Borrower Address Line 1',
			'value'        => '',
			'width'        => 'full'
		],
		'address_line_2' => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'address_line_2',
			'label'        => 'Current Borrower Address Line 2',
			'value'        => '',
			'width'        => 'full'
		],
		'city'           => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'city',
			'label'        => 'City',
			'value'        => '',
			'width'        => 'full'
		],
		'state'          => [
			'type'    => 'select',
			'name'    => 'state',
			'label'   => 'State',
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
			'default' => '',
			'width'   => '1/2'
		],
		'zip_code'       => [
			'type'         => 'text',
			'element_type' => 'text',
			'name'         => 'zip_code',
			'label'        => 'ZIP Code',
			'value'        => '',
			'width'        => '1/2'
		],

		// Блок с ошибками
		'errors_msg'     => [
			'type'    => 'content',
			'name'    => 'errors_msg',
			'content' => '<div class="wpp-errors-wrap"></div>',
			'width'   => 'full'
		],

	];

	if ( ! empty( $step ) && $step === 2 ) {
		$pdf_url = wpp_generate_loan_summary_pdf( $steps_data );
	} else {
		$pdf_url = '';
	}

	$form_fields_2 = [
		'sub_detail'               => [
			'type'    => 'content',
			'name'    => 'sub_detail',
			'label'   => '',
			'content' => $content_t,
			'width'   => 'full'
		],
		'download_terms_button'    => [
			'type'         => 'button',
			'element_type' => 'link_button',
			'name'         => 'download_terms',
			'label'        => 'Download Loan Terms',
			'href'         => esc_url( $pdf_url ),
			'btn_class'    => 'btn btn-secondary mt-3',
			'width'        => 'full'
		],
		'sub_detail2'              => [
			'type'    => 'content',
			'name'    => 'sub_detail2',
			'label'   => '',
			'content' => $content_n,
			'width'   => 'full'
		],
		// Чекбоксы согласия
		'credit_report_consent'    => [
			'type'  => 'checkbox',
			'name'  => 'credit_report_consent',
			'label' => 'Order a consumer credit report (soft pull) in connection with this loan application.',
			'width' => 'full'
		],
		'background_check_consent' => [
			'type'  => 'checkbox',
			'name'  => 'background_check_consent',
			'label' => 'Obtain a background report in connection with this loan application.',
			'width' => 'full'
		],// Кнопки

		'go_back_button'   => [
			'type'         => 'button',
			'element_type' => 'link_button',
			'name'         => 'go_back',
			'label'        => 'Go Back',
			'href'         => '/preferred-signing-date/',
			'btn_class'    => 'btn btn-secondary mt-3',
			'width'        => '1/2'
		],
		'next_step_button' => [
			'type'         => 'button',
			'class'        => 'text-end',
			'element_type' => 'submit',
			'name'         => 'next_step',
			'label'        => 'Submit Application',
			'btn_class'    => 'btn btn-primary mt-3',
			'width'        => '1/2'
		],
		// Скрытый инпут для текущего шага
		'step_identifier'  => [
			'type'         => 'text',
			'element_type' => 'hidden',
			'name'         => 'step',
			'default'      => '8',
			'width'        => 'full'
		]

	];

	if ( empty( $step ) ) {
		return array_merge(
			$form_fields_1,
			$form_fields_2
		);
	} elseif ( 1 === $step ) {
		return $form_fields_1;
	} else {
		return $form_fields_2;
	}

}

function wpp_render_loan_step_8() {
	ob_start();

	?>
    <h2>Confirm Your Information</h2>
    <p>Please review your application before submitting it.</p>

    <div class="container">
        <form id="loan-form-step-8" method="post" class="wpp-custom-form mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="row">
                        <h4>Entity and Individual Details</h4>
                        <p>Here is the most up-to-date information we have. Please enter or confirm any additional
                            details.</p>

						<?php
						$form_fields = wpp_step_config_8( 1 );
						foreach ( $form_fields as $name => $config ):
							$class_name = 'WPP_' . ucfirst( $config['type'] ) . '_Field';
							if ( class_exists( $class_name ) ) {
								$field = new $class_name( array_merge( $config, [ 'name' => $name ] ) );
								$field->render();
							}
						endforeach;
						?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="row">
						<?php $form_fields = wpp_step_config_8( 2 );
						foreach ( $form_fields as $name => $config ):
							$class_name = 'WPP_' . ucfirst( $config['type'] ) . '_Field';
							if ( class_exists( $class_name ) ) {
								$field = new $class_name( array_merge( $config, [ 'name' => $name ] ) );
								$field->render();
							}
						endforeach;
						?>
                    </div>
                </div>
            </div>
        </form>
    </div>

	<?php
	$content =  ob_get_clean();


    return $content;
}

add_shortcode( 'wpp_loan_application_step_8', 'wpp_render_loan_step_8' );