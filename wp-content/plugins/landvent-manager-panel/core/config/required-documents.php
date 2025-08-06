<?php
/**
 * Required Documents Configuration for Loan Management Plugin
 *
 * This file defines the structure and rendering logic for the "Required Documents" step
 * in a loan application workflow. It allows borrowers and administrators to upload
 * essential documents categorized by type, with support for required and optional files.
 *
 * The UI includes a custom table header for consistent presentation and uses a specialized
 * `documents_upload` field type to handle file attachments with metadata (date, status).
 *
 * @package           WPP_Loan_Management
 * @subpackage        Step_Configuration
 * @since             1.0.0
 * @author            WP_Panda <panda@wp-panda.pro>
 * @copyright         2025 WP_Panda
 * @license           GPL-2.0-or-later
 *
 * @link              https://developer.wordpress.org/plugins/
 * @link              https://www.php.net/manual/en/
 * @link              https://developer.wordpress.org/reference/functions/wp_handle_upload/
 */

defined('ABSPATH') || exit;

/**
 * Generates configuration for required document upload fields.
 *
 * Builds a list of document categories that must or may be uploaded during the loan process.
 * Each document has:
 * - A label and description
 * - Required flag (enforced on submission)
 * - Custom field type `documents_upload` for consistent UI/UX
 *
 * The first field renders a custom table header with columns:
 * - Category
 * - Name + "Download All" bulk action
 * - Upload Date
 * - Status
 * - Actions
 *
 * @since 1.0.0
 *
 * @return array Form configuration array for document uploads.
 *
 * @see https://developer.wordpress.org/plugins/settings/media-upload/ For file handling.
 * @see WPP_Documents_Upload_Field For field class implementation.
 *
 * @example
 *     $documents = wpp_step_config_required_documents();
 *     foreach ($documents as $field) {
 *         render_field($field);
 *     }
 */
function wpp_step_config_required_documents() {
	$form_fields = [
		'rd_th_id'                              => [
			'type'        => 'content',
			'content'     => '<div class="th-wrap">
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
			'description' => 'Upload borrower identification documents (e.g., driver’s license, passport)',
			'required'    => false
		],

		// 2. Appraisal Report
		'rd_appraisal_report'                         => [
			'type'        => 'documents_upload',
			'name'        => 'rd_appraisal_report',
			'label'       => 'Appraisal Report',
			'description' => 'Upload official property appraisal report from licensed appraiser',
			'required'    => false
		],

		// 3. Liquidity Statement
		'rd_liquidity_statement'                      => [
			'type'        => 'documents_upload',
			'name'        => 'rd_liquidity_statement',
			'label'       => 'Liquidity Statement',
			'description' => 'Upload bank statements or proof of funds to verify liquidity',
			'required'    => false
		],

		// 4. Experience
		'rd_experience'                               => [
			'type'        => 'documents_upload',
			'name'        => 'rd_experience',
			'label'       => 'Experience',
			'description' => 'Upload documentation of real estate investment or renovation experience',
			'required'    => false
		],

		// 5. Credit Check
		'rd_credit_check'                             => [
			'type'        => 'documents_upload',
			'name'        => 'rd_credit_check',
			'label'       => 'Credit Check',
			'description' => 'Upload credit history report or authorization for credit check',
			'required'    => false
		],

		// 6. Entity Docs - Articles Of Organization
		'rd_entity_docs_articles_of_organization'     => [
			'type'        => 'documents_upload',
			'name'        => 'rd_entity_docs_articles_of_organization',
			'label'       => 'Entity Docs - Articles Of Organization',
			'description' => 'Upload Articles of Organization for LLC or similar entity',
			'required'    => false
		],

		// 7. Entity Docs - EIN Letter
		'rd_entity_docs_ein_letter'                   => [
			'type'        => 'documents_upload',
			'name'        => 'rd_entity_docs_ein_letter',
			'label'       => 'Entity Docs - EIN Letter',
			'description' => 'Upload IRS-issued EIN (Employer Identification Number) confirmation letter',
			'required'    => false
		],

		// 8. Entity Docs - Operating Agreement
		'rd_entity_docs_operating_agreement'          => [
			'type'        => 'documents_upload',
			'name'        => 'rd_entity_docs_operating_agreement',
			'label'       => 'Entity Docs - Operating Agreement',
			'description' => 'Upload operating agreement outlining entity structure and ownership',
			'required'    => false
		],

		// 9. Entity Docs - Certificate Of Good Standing
		'rd_entity_docs_certificate_of_good_standing' => [
			'type'        => 'documents_upload',
			'name'        => 'rd_entity_docs_certificate_of_good_standing',
			'label'       => 'Entity Docs - Certificate Of Good Standing',
			'description' => 'Upload current Certificate of Good Standing from the state of formation',
			'required'    => false
		],

		// 10. Property Insurance
		'rd_property_insurance'                       => [
			'type'        => 'documents_upload',
			'name'        => 'rd_property_insurance',
			'label'       => 'Property Insurance',
			'description' => 'Upload current property insurance policy documentation',
			'required'    => false
		],

		// 11. Repair Budget
		'rd_repair_budget'                            => [
			'type'        => 'documents_upload',
			'name'        => 'rd_repair_budget',
			'label'       => 'Repair Budget',
			'description' => 'Upload detailed repair or renovation budget estimate',
			'required'    => false
		],

		// 12. Background Report
		'rd_background_report'                        => [
			'type'        => 'documents_upload',
			'name'        => 'rd_background_report',
			'label'       => 'Background Report',
			'description' => 'Upload criminal or background check report if required',
			'required'    => false
		],

		// 13. Payoff (Refinance Only)
		'rd_payoff_refinance_only'                    => [
			'type'        => 'documents_upload',
			'name'        => 'rd_payoff_refinance_only',
			'label'       => 'Payoff (Refinance Only)',
			'description' => 'Upload payoff statement from existing lender (refinance applications only)',
			'required'    => false
		],

		// 14. Application
		'rd_application'                              => [
			'type'        => 'documents_upload',
			'name'        => 'rd_application',
			'label'       => 'Application',
			'description' => 'Upload completed loan application form',
			'required'    => true
		],

		// 15. Title Commitment
		'rd_title_commitment'                         => [
			'type'        => 'documents_upload',
			'name'        => 'rd_title_commitment',
			'label'       => 'Title Commitment',
			'description' => 'Upload title commitment or preliminary title report',
			'required'    => true
		],

		// 16. VOM (Refi Only)
		'rd_vom_refi_only'                            => [
			'type'        => 'documents_upload',
			'name'        => 'rd_vom_refi_only',
			'label'       => 'VOM (Refi Only)',
			'description' => 'Upload Verification of Mortgage (VOM) document (refinance only)',
			'required'    => false
		],
	];

	/**
	 * Filters the required documents form configuration.
	 *
	 * Allows third-party code to add, remove, or modify document requirements
	 * based on loan type, region, or other criteria.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_fields The document upload configuration.
	 */
	return apply_filters('wpp/form/step_config/required_documents', $form_fields);
}

/**
 * Outputs the Required Documents tab in the loan management portal.
 *
 * Renders all document upload fields inside an accordion UI component.
 * Includes a "Download All" button for bulk retrieval of submitted documents.
 *
 * Hooked to `wpp_lmp_loan_content` with priority 120 to position it near the end
 * of the application flow, after financial and property details.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML directly.
 *
 * @hooked wpp_lmp_loan_content
 * @priority 120
 *
 * @see wpp_step_config_required_documents() For field definitions.
 * @see WPP_Accordion_Field For UI component.
 * @see https://developer.wordpress.org/plugins/media/add-media/
 *
 * @example
 *     This function is automatically called by WordPress during page rendering.
 */
function wpp_term_required_documents() {
	?>
    <div id="required-documents" class="container">
        <div class="row">
			<?php
			$cont = new WPP_Accordion_Field([
				'type'    => 'accordion',
				'name'    => 'info_required_documents',
				'title'   => 'Required Documents',
				'content' => function () {
					foreach (wpp_step_config_required_documents() as $name => $config) {
						$class_name = 'WPP_' . ucfirst($config['type']) . '_Field';

						if (class_exists($class_name)) {
							$field = new $class_name(array_merge($config, ['name' => $name]));
							$field->render();
						}
					}
				}
			]);

			$cont->render();
			?>
        </div>
    </div>
	<?php
}
add_action('wpp_lmp_loan_content', 'wpp_term_required_documents', 120);

/*
 * @todo List
 *
 * 1. Implement JavaScript for "Download All" functionality (zip打包 and stream).
 * 2. Add file type validation (PDF, JPG, PNG, DOCX) and size limits.
 * 3. Store uploaded files securely outside public HTML directory.
 * 4. Add status tracking (Uploaded, Verified, Rejected, Pending).
 * 5. Send notifications when new documents are uploaded.
 * 6. Support multiple file uploads per field.
 * 7. Add drag-and-drop upload interface.
 * 8. Encrypt sensitive documents at rest.
 * 9. Integrate with e-signature service for required forms.
 * 10. Write unit tests for document upload and validation logic.
 */