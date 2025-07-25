<?php
function wpp_step_config_title_company() {

	$form_fields = [];

	return $form_fields;
}

function wpp_term_title_company() { ?>
    <div id="title-company" class="container">
        <div class="row">
			<?php
			$cont = new WPP_Accordion_Field( [
				'type'    => 'accordion',
				'name'    => 'info_title_company',
				'title'   => 'Title Company',
				'content' => '<p>Доставка осуществляется в течение 3 дней</p>'
			] );

			$cont->render();
			?>
        </div>
    </div>
<?php }

add_action( 'wpp_lmp_loan_content', 'wpp_term_title_company', 110 );