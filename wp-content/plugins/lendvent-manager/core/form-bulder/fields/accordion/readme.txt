// Простое использование
echo $formManager->renderField('accordion', [
    'title' => 'Часто задаваемые вопросы',
    'content' => '<p>Ответ на частый вопрос...</p>',
    'is_open' => true
]);

// С вложенными полями
$content = $formManager->renderField('text', [
    'name' => 'question',
    'label' => 'Ваш вопрос'
]);

echo $formManager->renderField('accordion', [
    'title' => 'Форма вопроса',
    'content' => $content,
    'icon' => '➕',
    'icon_open' => '➖'
]);