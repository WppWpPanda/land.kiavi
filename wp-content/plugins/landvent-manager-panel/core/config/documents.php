<?php
function wpp_step_config_documents() {


	$form_fields = [];

	return $form_fields;
}

function wpp_term_documents() { ?>
    <div id="documents" class="container">
        <div class="row">
			<?php
			$cont = new WPP_Accordion_Field( [
				'type'    => 'accordion',
				'name'    => 'info_documents',
				'title'   => 'Documents',
				'content' =>  function () {
					foreach ( wpp_step_config_documents() as $name => $config ) {
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

add_action( 'wpp_lmp_loan_content', 'wpp_term_documents', 130 );