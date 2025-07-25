# Текстовая Область — `WPP_Textarea_Field`

Реализует стандартное поле `<textarea>`.

## Параметры

| Параметр       | Описание |
|----------------|----------|
| `name`         | Имя поля (обязательный) |
| `label`        | Подпись к полю |
| `description`  | Описание под полем |
| `default`      | Значение по умолчанию |
| `placeholder`  | Текст внутри поля до ввода |
| `classes`      | Массив дополнительных CSS-классов |
| `width`        | Ширина: full, 1/2, 1/3 и т.д. |
| `conditional`  | Условия отображения, например: `['country' => 'Russia']` |
| `validation`   | Callback-функция для валидации |
| `rows`         | Количество строк в textarea (по умолчанию 4) |

## Пример использования

```php
$field = new WPP_Textarea_Field([
    'name'        => 'user_bio',
    'label'       => 'О себе',
    'description' => 'Введите краткую информацию о себе',
    'placeholder' => 'Например: Я люблю WordPress',
    'default'     => 'Пример текста',
    'classes'     => ['form-control'],
    'width'       => 'full',
    'conditional' => ['subscribe_newsletter' => 'yes'],
    'required'    => false,
    'rows'        => 6,
    'validation'  => function ($value) {
        return empty($value) ? 'Поле обязательно!' : sanitize_textarea_field($value);
    }
]);

$field->render();