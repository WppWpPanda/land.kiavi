<?php
// Защита от прямого доступа
if (!defined('ABSPATH')) {
	exit;
}

landvent_manager_header_main();
?>

    <div class="wpp-manager-container">
        <h1>Title Companies</h1>

        <div class="wpp-action-panel">
            <a href="#" class="wpp-action-button wpp-add-title-company" id="wpp-open-title-company-modal">
                <i class="fas fa-plus-circle me-2"></i>
				<?php esc_html_e('Add a Title Company', 'landvent-manager'); ?>
            </a>
        </div>
    </div>

<?php
// Конфигурация формы (все поля типа text)
$title_company_form_config = [
	'form_title' => 'New Title Company',
	'form_id' => 'wpp-title-company-form',
	'fields' => [
		'comp_title_company_name' => [
			'type' => 'text',
			'label' => 'Title Company Name:',
			'placeholder' => 'Enter title company name',
			'width' => 'full',
			'required' => true
		],
		'comp_address' => [
			'type' => 'text',
			'label' => 'Address:',
			'placeholder' => 'Street address',
			'width' => 'full'
		],
		'comp_city' => [
			'type' => 'text',
			'label' => 'City:',
			'placeholder' => 'City',
			'width' => '1/2'
		],
		'comp_county' => [
			'type' => 'text',
			'label' => 'County:',
			'placeholder' => 'County',
			'width' => '1/2'
		],
		'comp_state' => [
			'type' => 'text',
			'label' => 'State:',
			'placeholder' => 'State',
			'width' => '1/2'
		],
		'comp_zip_code' => [
			'type' => 'text',
			'label' => 'Zip Code:',
			'placeholder' => 'ZIP code',
			'width' => '1/2'
		],
		'comp_phone' => [
			'type' => 'text',
			'label' => 'Phone:',
			'placeholder' => '(123) 456-7890',
			'width' => '1/2'
		],
		'comp_toll_free' => [
			'type' => 'text',
			'label' => 'Toll Free:',
			'placeholder' => '(800) 123-4567',
			'width' => '1/2'
		],
		'comp_fax' => [
			'type' => 'text',
			'label' => 'Fax:',
			'placeholder' => 'Fax number',
			'width' => '1/2'
		]
	],
	'submit_button' => [
		'text' => 'Add Title Company',
		'classes' => ['wpp-button', 'wpp-button-primary']
	]
];
?>

    <!-- Модальное окно -->
    <div class="wpp-modal" id="wpp-title-company-modal" style="display: none;">
        <div class="wpp-modal-overlay"></div>
        <div class="wpp-modal-content">
            <div class="wpp-modal-header">
                <h3><?php echo esc_html($title_company_form_config['form_title']); ?></h3>
                <button class="wpp-modal-close">&times;</button>
            </div>

            <div class="wpp-modal-body row">
                <form id="<?php echo esc_attr($title_company_form_config['form_id']); ?>" method="post">
                    <div class="wpp-form row">
						<?php foreach ($title_company_form_config['fields'] as $name => $config): ?>

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
                        <button type="submit" class="<?php echo esc_attr(implode(' ', $title_company_form_config['submit_button']['classes'])); ?>">
							<?php echo esc_html($title_company_form_config['submit_button']['text']); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        jQuery(document).ready(function($) {
            // Открытие модального окна
            $('#wpp-open-title-company-modal').on('click', function(e) {
                e.preventDefault();
                $('#wpp-title-company-modal').fadeIn();
            });

            // Закрытие модального окна
            $('.wpp-modal-close, .wpp-modal-overlay').on('click', function() {
                $('#wpp-title-company-modal').fadeOut();
            });

            // Закрытие при нажатии ESC
            $(document).on('keyup', function(e) {
                if (e.key === "Escape") {
                    $('#wpp-title-company-modal').fadeOut();
                }
            });
        });
    </script>

<?php
landvent_manager_footer_main();
?>