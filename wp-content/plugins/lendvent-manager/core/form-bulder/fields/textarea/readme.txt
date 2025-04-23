// Простое текстовое поле
echo $formManager->renderField('textarea', [
    'name' => 'description',
    'label' => 'Описание',
    'rows' => 6
]);

// Поле с дополнительными параметрами
echo $formManager->renderField('textarea', [
    'name' => 'bio',
    'label' => 'Биография',
    'value' => 'Расскажите о себе...',
    'placeholder' => 'Введите информацию о себе',
    'required' => true,
    'rows' => 8,
    'wrapper_class' => 'bio-field',
    'input_class' => 'large-text'
]);