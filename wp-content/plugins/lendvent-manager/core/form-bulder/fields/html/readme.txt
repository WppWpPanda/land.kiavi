echo $formManager->renderField('html', [
    'content' => '<div class="notice"><p>Произвольный HTML-контент</p></div>'
]);

С оберткой и классами:
echo $formManager->renderField('html', [
    'content' => '<button type="button" class="custom-button">Нажми меня</button>',
    'wrapper_class' => 'custom-html-wrapper',
    'before' => '<h3>Дополнительные элементы:</h3>',
    'after' => '<p class="hint">Можно добавить больше кнопок</p>'
]);

Вставка шорткодов:
echo $formManager->renderField('html', [
    'content' => do_shortcode('[my_custom_shortcode param="value"]')
]);

Комбинирование с другими полями:
$fields = [
    $formManager->renderField('text', ['name' => 'name', 'label' => 'Имя']),
    $formManager->renderField('html', [
        'content' => '<hr><p class="divider">Или войдите через соцсети:</p>'
    ]),
    $formManager->renderField('html', [
        'content' => '<div class="social-login">' .
                     '<button class="social-btn fb">Facebook</button>' .
                     '<button class="social-btn google">Google</button>' .
                     '</div>'
    ])
];

echo implode('', $fields);