<?php
function wpp_step_config_attorney() {

	$form_fields = [];

	return $form_fields;
}

function wpp_term_attorney() { ?>
    <div id="attorney" class="container">
        <div class="row">
			<?php
			$cont = new WPP_Accordion_Field( [
				'type'    => 'accordion',
				'name'    => 'info_attorney',
				'title'   => 'Attorney',
				'content' => '<p>Доставка осуществляется в течение 3 дней</p>'
			] );

			$cont->render();
			?>
        </div>
    </div>
<?php }

add_action( 'wpp_lmp_loan_content', 'wpp_term_attorney', 100 );