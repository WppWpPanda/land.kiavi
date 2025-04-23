<?php
// Добавляем аккордеон как зарегистрированный тип поля
add_action('init', function() {
    $formManager = FormFieldsManager::getInstance();

    // Проверяем, есть ли уже метод addField
    if (method_exists($formManager, 'addField')) {
        $formManager->addField(
            'accordion',
            'Field_Accordion',
            plugin_dir_path(__FILE__) . 'fields/accordion/field-accordion.php'
        );
    }
});