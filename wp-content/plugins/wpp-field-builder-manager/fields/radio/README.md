# Радио-кнопка — `WPP_Radio_Field`

Реализует группу переключателей `<input type="radio">`.

## Параметры

| Параметр       | Описание |
|----------------|----------|
| `name`         | Имя группы радио-кнопок (обязательный) |
| `label`        | Подпись к полю (не используется в радио, но можно добавить заголовок) |
| `description`  | Описание под полем |
| `default`      | Значение по умолчанию (ключ из `options`) |
| `options`      | Массив вариантов выбора: `['value' => 'Подпись']` |
| `classes`      | Дополнительные CSS-классы |
| `width`        | Ширина: full, 1/2, 1/3 и т.д. |
| `conditional`  | Условия отображения, например: `['subscribe_newsletter' => 'yes']` |
| `validation`   | Callback-функция для валидации |

## Пример использования

```php
$field = new WPP_Radio_Field([
    'name'        => 'gender',
    'label'       => 'Пол',
    'description' => 'Выберите ваш пол',
    'default'     => 'male',
    'classes'     => ['custom-control-input'],
    'width'       => 'full',
    'options'     => [
        'male'   => 'Мужской',
        'female' => 'Женский',
        'other'  => 'Другой'
    ],
    'required'    => true,
    'validation'  => function ($value) {
        $valid = ['male', 'female', 'other'];
        return in_array($value, $valid) ? $value : 'Неверное значение';
    }
]);

$field->render();