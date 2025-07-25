<?php
function wpp_step_config_required_documents() {

	$form_fields = [];

	return $form_fields;
}

function wpp_term_required_documents() { ?>
    <div id="required-documents" class="container">
        <div class="row">
			<?php
			$cont = new WPP_Accordion_Field( [
				'type'    => 'accordion',
				'name'    => 'info_required_documents',
				'title'   => 'Required Documents',
				'content' => '<p>Доставка осуществляется в течение 3 дней</p>'
			] );

			$cont->render();
			?>
        </div>
    </div>
<?php }

add_action( 'wpp_lmp_loan_content', 'wpp_term_required_documents', 120 );