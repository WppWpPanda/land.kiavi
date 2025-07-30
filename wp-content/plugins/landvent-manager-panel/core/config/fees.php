<?php
function wpp_step_config_fees() {

	// Получаем базовую сумму для расчёта (например, общая сумма кредита)
	$base_amount = (float) wpp_get_total_loan_amount();

// Если сумма не определена, можно поставить fallback (например, 180000), или оставить 0
	$base_amount = $base_amount > 0 ? $base_amount : 0;

	$fields = [
		'fee_deposit' => [
			'type'         => 'percent_money',
			'name'         => 'fee_deposit',
			'label'        => 'Deposit',
			'width'        => 'full',
			'base_amount'  => $base_amount,
			'default'      => [
				'money'   => '0.00',
				'percent' => '0.00'
			],
		],
		'fee_broker_fee' => [
			'type'         => 'percent_money',
			'name'         => 'fee_broker_fee',
			'label'        => 'Broker Fee',
			'width'        => 'full',
			'base_amount'  => $base_amount,
			'default'      => [
				'money'   => '0.00',
				'percent' => '0.00'
			],
		],
		'fee_origination_fee' => [
			'type'         => 'percent_money',
			'name'         => 'fee_origination_fee',
			'label'        => 'Origination Fee',
			'width'        => 'full',
			'base_amount'  => $base_amount,
			'default'      => [
				'money'   => '0.00',
				'percent' => '0.00'
			],
		],
		'fee_lender_fee' => [
			'type'         => 'percent_money',
			'name'         => 'fee_lender_fee',
			'label'        => 'Lender Fee',
			'width'        => 'full',
			'base_amount'  => $base_amount,
			'default'      => [
				'money'   => '0.00',
				'percent' => '0.00'
			],
		],
		'fee_processing_fee' => [
			'type'         => 'percent_money',
			'name'         => 'fee_processing_fee',
			'label'        => 'Processing Fee',
			'width'        => 'full',
			'base_amount'  => $base_amount,
			'default'      => [
				'money'   => '0.00',
				'percent' => '0.00'
			],
		],
		'fee_site_inspection_fee' => [
			'type'         => 'percent_money',
			'name'         => 'fee_site_inspection_fee',
			'label'        => 'Site Inspection Fee',
			'width'        => 'full',
			'base_amount'  => $base_amount,
			'default'      => [
				'money'   => '0.00',
				'percent' => '0.00'
			],
		],
		'fee_appraisal_fee' => [
			'type'         => 'percent_money',
			'name'         => 'fee_appraisal_fee',
			'label'        => 'Appraisal Fee',
			'width'        => 'full',
			'base_amount'  => $base_amount,
			'default'      => [
				'money'   => '0.00',
				'percent' => '0.00'
			],
		],
	];

	return $fields;
}

function wpp_term_fees() { ?>
    <div id="fees" class="container">
        <div class="row">
			<?php
			$cont = new WPP_Accordion_Field( [
				'type'    => 'accordion',
				'name'    => 'info_fees',
				'title'   => 'Fees',
				'content' =>  function () {
					foreach ( wpp_step_config_fees() as $name => $config ) {
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

add_action( 'wpp_lmp_loan_content', 'wpp_term_fees', 50 );