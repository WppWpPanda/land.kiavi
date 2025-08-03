<?php
/**
 * Brokers Management Page Template
 *
 * Displays the admin interface for managing brokerages in the LandVent Manager Panel.
 * Includes:
 * - A header and container layout.
 * - An "Add Brokerage" button that opens a modal form.
 * - A modal form for adding new brokerages via AJAX.
 * - Form configuration array with field definitions.
 * - Security nonce for AJAX submission.
 *
 * This file is typically included within an admin menu page.
 *
 * @package LandVent_Manager_Panel
 * @subpackage Admin/UI
 * @since 1.0.0
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

// -------------------------------
// 1. Render Page Header
// -------------------------------
// Outputs the main admin header (e.g., page title, navigation)
// Defined in another file (likely core/templates.php)
landvent_manager_header_main();
?>

    <!-- Main Container -->
    <div class="wpp-manager-container">
        <h1><?php esc_html_e('Brokers', 'landvent-manager'); ?></h1>

        <!-- Action Panel -->
        <div class="wpp-action-panel">
            <!-- Button to Open Modal -->
            <a href="#" class="wpp-action-button wpp-add-brokerage" id="wpp-open-brokerage-modal">
                <i class="fas fa-plus-circle me-2"></i>
				<?php esc_html_e('Add a Brokerage', 'landvent-manager'); ?>
            </a>
        </div>
    </div>

<?php
// -------------------------------
// 2. Form Configuration
// -------------------------------
// Defines the structure of the "Add Brokerage" form.
// Used to dynamically render fields and support future reuse or JSON export.
//
// @var array $brokerage_form_config
$brokerage_form_config = [
	'form_title' => 'New Brokerage',
	'form_id'    => 'wpp-brokerage-form',
	'fields'     => [
		'brok_brokerage_name' => [
			'type'        => 'text',
			'label'       => 'Brokerage:',
			'placeholder' => 'Enter brokerage name',
			'width'       => 'full',
			'required'    => true,
		],
		'brok_parent_brokerage' => [
			'type'        => 'text',
			'label'       => 'Parent Brokerage:',
			'placeholder' => 'Enter parent brokerage name',
			'width'       => 'full',
		],
		'brok_address' => [
			'type'        => 'text',
			'label'       => 'Address:',
			'placeholder' => 'Street address',
			'width'       => 'full',
		],
		'brok_city' => [
			'type'        => 'text',
			'label'       => 'City:',
			'placeholder' => 'City',
			'width'       => '1/2',
		],
		'brok_county' => [
			'type'        => 'text',
			'label'       => 'County:',
			'placeholder' => 'County',
			'width'       => '1/2',
		],
		'brok_state' => [
			'type'        => 'text',
			'label'       => 'State:',
			'placeholder' => 'State',
			'width'       => '1/2',
		],
		'brok_zip_code' => [
			'type'        => 'text',
			'label'       => 'Zip Code:',
			'placeholder' => 'ZIP code',
			'width'       => '1/2',
		],
		'brok_broker_bdm' => [
			'type'         => 'text',
			'label'        => 'Broker BDM:',
			'placeholder'  => 'Enter broker BDM',
			'width'        => 'full',
			'edit_button'  => true, // Optional: indicates a future "Edit" UI element
		],
	],
	'submit_button' => [
		'text'    => 'Add Broker',
		'classes' => ['wpp-button', 'wpp-button-primary'],
	],
];

// -------------------------------
// 3. Modal Form: Add/Edit Brokerage
// -------------------------------
// Hidden modal that appears when "Add Brokerage" is clicked.
// Contains a form with dynamic field rendering based on $brokerage_form_config.
// Uses AJAX for submission (handled by wpp_save_brokerage_callback).
?>
    <div class="wpp-modal" id="wpp-brokerage-modal" style="display: none;">
        <div class="wpp-modal-overlay"></div>
        <div class="wpp-modal-content">
            <div class="wpp-modal-header">
                <h3><?php echo esc_html($brokerage_form_config['form_title']); ?></h3>
                <button class="wpp-modal-close">&times;</button>
            </div>

            <div class="wpp-modal-body row">
                <form id="<?php echo esc_attr($brokerage_form_config['form_id']); ?>" method="post">
                    <div class="wpp-form row">
						<?php foreach ($brokerage_form_config['fields'] as $name => $config): ?>
							<?php
							// Dynamically instantiate field class (e.g., WPP_Text_Field)
							$field_class = 'WPP_' . ucfirst($config['type']) . '_Field';

							if (class_exists($field_class)) {
								$field = new $field_class(array_merge($config, ['name' => $name]));
								$field->render();
							} else {
								// Fallback: render simple input if class not found
								printf(
									'<div class="wpp-form-field wpp-width-%s">
                                    <label>%s</label>
                                    <input type="text" name="%s" placeholder="%s" %s />
                                </div>',
									esc_attr($config['width']),
									esc_html($config['label']),
									esc_attr($name),
									esc_attr($config['placeholder']),
									!empty($config['required']) ? 'required' : ''
								);
							}
							?>
						<?php endforeach; ?>
                    </div>

                    <!-- Security: Nonce field for AJAX verification -->
					<?php wp_nonce_field('wpp_brokerage_nonce', '_ajax_nonce', false); ?>

                    <!-- Submit Button -->
                    <div class="wpp-form-actions">
                        <button type="submit"
                                class="<?php echo esc_attr(implode(' ', $brokerage_form_config['submit_button']['classes'])); ?>">
							<?php echo esc_html($brokerage_form_config['submit_button']['text']); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php
// -------------------------------
// 4. Render Page Footer
// -------------------------------
// Outputs the main admin footer (e.g., scripts, closing tags)
// Defined in another file (likely core/templates.php)
landvent_manager_footer_main();