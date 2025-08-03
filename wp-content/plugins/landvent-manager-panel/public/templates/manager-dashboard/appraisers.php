<?php
// Защита от прямого доступа
if (!defined('ABSPATH')) {
	exit;
}

landvent_manager_header_main();
?>

    <div class="wpp-manager-container">
        <h1>Appraisers</h1>

        <div class="wpp-action-panel">
            <a href="#" class="wpp-action-button wpp-add-appraiser" id="wpp-open-appraiser-modal">
                <i class="fas fa-plus-circle me-2"></i>
				<?php esc_html_e('Add a New Appraiser', 'landvent-manager'); ?>
            </a>
        </div>
    </div>

<?php
// Конфигурация формы (все поля типа text)
$appraiser_form_config = [
	'form_title' => 'New Appraiser',
	'form_id' => 'wpp-appraiser-form',
	'fields' => [
		'appr_name' => [
			'type' => 'text',
			'label' => 'Name:',
			'placeholder' => 'Enter appraiser name',
			'width' => 'full',
			'required' => true
		],
		'appr_address' => [
			'type' => 'text',
			'label' => 'Address:',
			'placeholder' => 'Street address',
			'width' => 'full'
		],
		'appr_city' => [
			'type' => 'text',
			'label' => 'City:',
			'placeholder' => 'City',
			'width' => '1/2'
		],
		'appr_county' => [
			'type' => 'text',
			'label' => 'County:',
			'placeholder' => 'County',
			'width' => '1/2'
		],
		'appr_state' => [
			'type' => 'text',
			'label' => 'State:',
			'placeholder' => 'State',
			'width' => '1/2'
		],
		'appr_zip' => [
			'type' => 'text',
			'label' => 'Zip:',
			'placeholder' => 'ZIP code',
			'width' => '1/2'
		],
		'appr_phone' => [
			'type' => 'text',
			'label' => 'Phone:',
			'placeholder' => '(123) 456-7890',
			'width' => '1/2'
		],
		'appr_fax' => [
			'type' => 'text',
			'label' => 'Fax:',
			'placeholder' => 'Fax number',
			'width' => '1/2'
		],
		'appr_email' => [
			'type' => 'text',
			'label' => 'Email:',
			'placeholder' => 'example@example.com',
			'width' => '1/2'
		],
		'appr_title' => [
			'type' => 'text',
			'label' => 'Title:',
			'placeholder' => 'Enter title',
			'width' => '1/2'
		],
		'appr_website' => [
			'type' => 'text',
			'label' => 'Website:',
			'placeholder' => 'https://example.com',
			'width' => '1/2'
		],
		'appr_contact' => [
			'type' => 'textarea',
			'label' => 'Contact:',
			'placeholder' => 'Enter additional contact information',
			'width' => 'full'
		]
	],
	'submit_button' => [
		'text' => 'Add Appraiser',
		'classes' => ['wpp-button', 'wpp-button-primary']
	]
];
?>

    <!-- Модальное окно -->
    <div class="wpp-modal" id="wpp-appraiser-modal" style="display: none;">
        <div class="wpp-modal-overlay"></div>
        <div class="wpp-modal-content">
            <div class="wpp-modal-header">
                <h3><?php echo esc_html($appraiser_form_config['form_title']); ?></h3>
                <button class="wpp-modal-close">&times;</button>
            </div>

            <div class="wpp-modal-body row">
                <form id="<?php echo esc_attr($appraiser_form_config['form_id']); ?>" method="post">
                    <div class="wpp-form row">
						<?php foreach ($appraiser_form_config['fields'] as $name => $config): ?>

							<?php
							$field_class = 'WPP_' . ucfirst($config['type']) . '_Field';
							if (class_exists($field_class)) {
								$field = new $field_class(array_merge($config, ['name' => $name]));
								$field->render();
							}
							?>

						<?php endforeach; ?>
                    </div>

                    <div class="wpp-form-actions">
                        <button type="submit" class="<?php echo esc_attr(implode(' ', $appraiser_form_config['submit_button']['classes'])); ?>">
							<?php echo esc_html($appraiser_form_config['submit_button']['text']); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        jQuery(document).ready(function($) {
            // Открытие модального окна
            $('#wpp-open-appraiser-modal').on('click', function(e) {
                e.preventDefault();
                $('#wpp-appraiser-modal').fadeIn();
            });

            // Закрытие модального окна
            $('.wpp-modal-close, .wpp-modal-overlay').on('click', function() {
                $('#wpp-appraiser-modal').fadeOut();
            });

            // Закрытие при нажатии ESC
            $(document).on('keyup', function(e) {
                if (e.key === "Escape") {
                    $('#wpp-appraiser-modal').fadeOut();
                }
            });
        });
    </script>

<?php
landvent_manager_footer_main();