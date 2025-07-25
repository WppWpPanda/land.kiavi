<?php
function wpp_step_config_additional_reserve() {

	$form_fields = [];

	return $form_fields;
}

function wpp_term_additional_reserve() { ?>
    <div id="additional-reserves" class="container">
        <div class="row">
			<?php
			$cont = new WPP_Accordion_Field( [
				'type'    => 'accordion',
				'name'    => 'additional_reserve',
				'title'   => 'Additional Reserves',
				'content' => '<p>Доставка осуществляется в течение 3 дней</p>'
			] );

			$cont->render();
			?>
        </div>
    </div>
<?php }

add_action( 'wpp_lmp_loan_content', 'wpp_term_additional_reserve', 40 );