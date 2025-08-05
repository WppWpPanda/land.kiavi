<?php
// Защита от прямого доступа
if (!defined('ABSPATH')) {
	exit;
}

landvent_manager_header_main();
?>

    <div class="wpp-manager-container">
        <h1>Law Firms & Clerks</h1>

        <div class="wpp-action-panel">
            <a href="#" class="wpp-action-button wpp-add-firms" id="wpp-open-law-firm-modal">
                <i class="fas fa-plus-circle me-2"></i>
				<?php esc_html_e('Add a Law Firm', 'landvent-manager'); ?>
            </a>
        </div>
    </div>

<?php
// Конфигурация формы (все поля типа text)
$law_firm_form_config = [
	'form_title' => 'New Law Firm',
	'form_id' => 'wpp-law-firm-form',
	'fields' => [
		'law_firm_name' => [
			'type' => 'text',
			'label' => 'Law Firm Name:',
			'placeholder' => 'Enter law firm name',
			'width' => 'full',
			'required' => true
		],
		'law_address' => [
			'type' => 'text',
			'label' => 'Address:',
			'placeholder' => 'Street address',
			'width' => 'full'
		],
		'law_city' => [
			'type' => 'text',
			'label' => 'City:',
			'placeholder' => 'City',
			'width' => '1/2'
		],
		'law_county' => [
			'type' => 'text',
			'label' => 'County:',
			'placeholder' => 'County',
			'width' => '1/2'
		],
		'law_state' => [
			'type' => 'text',
			'label' => 'State:',
			'placeholder' => 'State',
			'width' => '1/2'
		],
		'law_zip_code' => [
			'type' => 'text',
			'label' => 'Zip Code:',
			'placeholder' => 'ZIP code',
			'width' => '1/2'
		],
		'law_phone' => [
			'type' => 'text',
			'label' => 'Phone:',
			'placeholder' => '(123) 456-7890',
			'width' => '1/2'
		],
		'law_toll_free' => [
			'type' => 'text',
			'label' => 'Toll Free:',
			'placeholder' => '(800) 123-4567',
			'width' => '1/2'
		],
		'law_fax' => [
			'type' => 'text',
			'label' => 'Fax:',
			'placeholder' => 'Fax number',
			'width' => '1/2'
		],
		'law_website' => [
			'type' => 'text',
			'label' => 'Website:',
			'placeholder' => 'https://example.com',
			'width' => '1/2'
		]
	],
	'submit_button' => [
		'text' => 'Add Law Firm',
		'classes' => ['wpp-button', 'wpp-button-primary']
	]
];
?>

    <!-- Модальное окно -->
    <div class="wpp-modal" id="wpp-law-firm-modal" style="display: none;">
        <div class="wpp-modal-overlay"></div>
        <div class="wpp-modal-content">
            <div class="wpp-modal-header">
                <h3><?php echo esc_html($law_firm_form_config['form_title']); ?></h3>
                <button class="wpp-modal-close">&times;</button>
            </div>

            <div class="wpp-modal-body row">
                <form id="<?php echo esc_attr($law_firm_form_config['form_id']); ?>" method="post">
                    <div class="wpp-form row">
						<?php foreach ($law_firm_form_config['fields'] as $name => $config): ?>

								<?php
								$field_class = 'WPP_' . ucfirst($config['type']) . '_Field';
								if (class_exists($field_class)) {
									$field = new $field_class(array_merge($config, ['name' => $name]));
									$field->render();
								}
								?>

						<?php endforeach; ?>
                    </div>
                    <!-- Security: Nonce field for AJAX verification -->
	                <?php wp_nonce_field('wpp_law_firm_nonce', '_ajax_nonce', false); ?>
                    <div class="wpp-form-actions">
                        <button type="submit" class="<?php echo esc_attr(implode(' ', $law_firm_form_config['submit_button']['classes'])); ?>">
							<?php echo esc_html($law_firm_form_config['submit_button']['text']); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="wpp-data-table">
		<?php


		// Create and render the table
		$table = new LawFirm_Table();

		// Output the table
		echo '<div style="margin: 20px;">';
		echo '<h2>Law Firms</h2>';
		echo $table->display();
		echo '</div>';
		?>
    </div>


<?php
landvent_manager_footer_main();