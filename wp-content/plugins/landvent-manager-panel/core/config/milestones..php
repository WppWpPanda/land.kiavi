<?php

function wpp_term_milestonesconfig_documents() {


	$form_fields = [
		'project_milestones' => [
			'type' => 'super_accordion',
			'name' => 'project_milestones',
			'title' => 'Project Milestones',
			'open' => false,
			'fields' => [
				'milestone_1' => [
					'type' => 'text',
					'element_type' => 'text',
					'name' => 'milestone_1',
					'label' => 'Milestone 1',
					'placeholder' => 'e.g., Foundation Pour',
					'width' => 'full'
				],
				'milestone_2' => [
					'type' => 'text',
					'element_type' => 'text',
					'name' => 'milestone_2',
					'label' => 'Milestone 2',
					'placeholder' => 'e.g., Framing Complete',
					'width' => 'full'
				],
				'milestone_3' => [
					'type' => 'text',
					'element_type' => 'text',
					'name' => 'milestone_3',
					'label' => 'Milestone 3',
					'placeholder' => 'e.g., Final Inspection',
					'width' => 'full'
				]
			],
			'width' => 'full'
		]
	];

	return $form_fields;
}

function wpp_term_milestones() { ?>
    <div id="milestones" class="container">
        <div class="row">
			<?php
			$cont = new WPP_Accordion_Field( [
				'type'    => 'accordion',
				'name'    => 'info_milestones',
				'title'   => 'milestones',
				'content' =>   function () {
					foreach ( wpp_term_milestonesconfig_documents() as $name => $config ) {
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

add_action( 'wpp_lmp_loan_content', 'wpp_term_milestones', 60 );