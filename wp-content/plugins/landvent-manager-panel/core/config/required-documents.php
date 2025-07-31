<?php
function wpp_step_config_required_documents() {

	$form_fields = [

		'rd_th_id'                              => [
			'type'        => 'content',
			//'name'        => 'rd_borrower_id',
			//'label'       => 'Borrower ID',
			'content' => '<div class="th-wrap">
                            <div class="th-wrap-1">	CATEGORY</div>
                            <div class="th-wrap-d">
                            <div class="th-wrap-2">NAME <a href="javascript:void(0);" class="wpp-download-docs">download all</a></div>
                            <div class="th-wrap-3">DATE</div>
                            <div class="th-wrap-4">STATUS</div>
                            <div class="th-wrap-5"></div>
                            </div>
                          </div>',
			'required'    => false,
		],

		// 1. Borrower ID
		'rd_borrower_id'                              => [
			'type'        => 'documents_upload',
			'name'        => 'rd_borrower_id',
			'label'       => 'Borrower ID',
			'description' => 'Upload borrower identification documents',
			'required'    => false,
			////'required' => false, => ['application/pdf', 'image/jpeg', 'image/png'],
		],

		// 2. Appraisal Report
		'rd_appraisal_report'                         => [
			'type'        => 'documents_upload',
			'name'        => 'rd_appraisal_report',
			'label'       => 'Appraisal Report',
			'description' => 'Upload property appraisal report',
			'required'    => false,
			////'required' => false, => ['application/pdf'],
		],

		// 3. Liquidity Statement
		'rd_liquidity_statement'                      => [
			'type'        => 'documents_upload',
			'name'        => 'rd_liquidity_statement',
			'label'       => 'Liquidity Statement',
			'description' => 'Upload liquidity statement',
			'required'    => false,
			//'required' => false, => ['application/pdf'],
		],

		// 4. Experience
		'rd_experience'                               => [
			'type'        => 'documents_upload',
			'name'        => 'rd_experience',
			'label'       => 'Experience',
			'description' => 'Upload experience documentation',
			'required'    => false,
			//'required' => false, => ['application/pdf'],
		],

		// 5. Credit Check
		'rd_credit_check'                             => [
			'type'        => 'documents_upload',
			'name'        => 'rd_credit_check',
			'label'       => 'Credit Check',
			'description' => 'Upload credit history report',
			'required'    => false,
			//'required' => false, => ['application/pdf'],
		],

		// 6. Entity Docs - Articles Of Organization
		'rd_entity_docs_articles_of_organization'     => [
			'type'        => 'documents_upload',
			'name'        => 'rd_entity_docs_articles_of_organization',
			'label'       => 'Entity Docs - Articles Of Organization',
			'description' => 'Upload articles of organization',
			'required'    => false,
			//'required' => false, => ['application/pdf'],
		],

		// 7. Entity Docs - EIN Letter
		'rd_entity_docs_ein_letter'                   => [
			'type'        => 'documents_upload',
			'name'        => 'rd_entity_docs_ein_letter',
			'label'       => 'Entity Docs - EIN Letter',
			'description' => 'Upload EIN (Employer Identification Number) letter',
			'required'    => false,
			//'required' => false, => ['application/pdf'],
		],

		// 8. Entity Docs - Operating Agreement
		'rd_entity_docs_operating_agreement'          => [
			'type'        => 'documents_upload',
			'name'        => 'rd_entity_docs_operating_agreement',
			'label'       => 'Entity Docs - Operating Agreement',
			'description' => 'Upload operating agreement',
			'required'    => false,
			//'required' => false, => ['application/pdf'],
		],

		// 9. Entity Docs - Certificate Of Good Standing
		'rd_entity_docs_certificate_of_good_standing' => [
			'type'        => 'documents_upload',
			'name'        => 'rd_entity_docs_certificate_of_good_standing',
			'label'       => 'Entity Docs - Certificate Of Good Standing',
			'description' => 'Upload certificate of good standing',
			'required'    => false,
			//'required' => false, => ['application/pdf'],
		],

		// 10. Property Insurance
		'rd_property_insurance'                       => [
			'type'        => 'documents_upload',
			'name'        => 'rd_property_insurance',
			'label'       => 'Property Insurance',
			'description' => 'Upload property insurance policy',
			'required'    => false,
			//'required' => false, => ['application/pdf'],
		],

		// 11. Repair Budget
		'rd_repair_budget'                            => [
			'type'        => 'documents_upload',
			'name'        => 'rd_repair_budget',
			'label'       => 'Repair Budget',
			'description' => 'Upload repair budget',
			'required'    => false,
			//'required' => false, => ['application/pdf'],
		],

		// 12. Background Report
		'rd_background_report'                        => [
			'type'        => 'documents_upload',
			'name'        => 'rd_background_report',
			'label'       => 'Background Report',
			'description' => 'Upload background check report',
			'required'    => false,
			//'required' => false, => ['application/pdf'],
		],

		// 13. Payoff (Refinance Only)
		'rd_payoff_refinance_only'                    => [
			'type'        => 'documents_upload',
			'name'        => 'rd_payoff_refinance_only',
			'label'       => 'Payoff (Refinance Only)',
			'description' => 'Upload payoff documents (refinance only)',
			'required'    => false,
			//'required' => false, => ['application/pdf'],
		],

		// 14. Application
		'rd_application'                              => [
			'type'        => 'documents_upload',
			'name'        => 'rd_application',
			'label'       => 'Application',
			'description' => 'Upload loan application',
			'required'    => true,
			//'required' => false, => ['application/pdf'],
		],

		// 15. Title Commitment
		'rd_title_commitment'                         => [
			'type'        => 'documents_upload',
			'name'        => 'rd_title_commitment',
			'label'       => 'Title Commitment',
			'description' => 'Upload title commitment',
			'required'    => true,
			//'required' => false, => ['application/pdf'],
		],

		// 16. VOM (Refi Only)
		'rd_vom_refi_only'                            => [
			'type'        => 'documents_upload',
			'name'        => 'rd_vom_refi_only',
			'label'       => 'VOM (Refi Only)',
			'description' => 'Upload VOM document (refinance only)',
			'required'    => false,
			//'required' => false, => ['application/pdf'],
		],
	];

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
				'content' => function () {
					foreach ( wpp_step_config_required_documents() as $name => $config ) {
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

add_action( 'wpp_lmp_loan_content', 'wpp_term_required_documents', 120 );