<?php

function wpp_step_config_7() {
	$form_fields = [
		'signing_date'     => [
			'type'        => 'datepicker',
			'name'        => 'signing_date',
			'label'       => 'Preferred Signing Date',
			'placeholder' => 'MM/DD/YYYY',
			'width'       => 'full'
		],
		'go_back_button'   => [
			'type'         => 'button',
			'element_type' => 'link_button',
			'name'         => 'go_back',
			'label'        => 'Go Back',
			'btn_class'    => 'btn btn-secondary mt-3',
			'href'         => '/property-address', // Ссылка на предыдущий шаг
			'width'        => '1/2'
		],
		'next_step_button' => [
			'type'         => 'button',
			'element_type' => 'submit',
			'classes'      => [ 'text-end' ],
			'name'         => 'next_step',
			'label'        => 'Next Step',
			'btn_class'    => 'btn btn-primary mt-3',
			'width'        => '1/2'
		],
		'step_identifier'  => [
			'type'         => 'text',
			'element_type' => 'hidden',
			'name'         => 'step',
			'default'      => '7',
			'width'        => 'full'
		]
	];

	return $form_fields;
}

function wpp_render_loan_step_7() {
	ob_start();
	?>
    <div class="container">
        <h1>What is your preferred signing date?</h1>
        <p>Most borrowers can close and fund on the same day.</p>
        <p>Borrowers in Alaska, Arizona, California, Hawaii, Idaho, Nevada, New Mexico, Oregon, and Washington may sign
            and fund a minimum of 1 day after signing.</p>
		<?php
		wpp_loan_form_debug_data( 6 );
		wpp_render_form( 'loan-form-step-7', wpp_step_config_7() );
		?>
    </div>
	<?php
	return ob_get_clean();
}

add_shortcode( 'wpp_loan_application_step_7', 'wpp_render_loan_step_7' );