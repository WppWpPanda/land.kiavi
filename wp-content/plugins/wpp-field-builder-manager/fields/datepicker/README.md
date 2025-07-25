# Чекбокс — `WPP_Checkbox_Field`

Реализует стандартный чекбокс `<input type="checkbox">`.

## Параметры

| Параметр       | Описание |
|----------------|----------|
| `name`         | Имя поля (обязательный) |
| `label`        | Подпись к чекбоксу |
| `description`  | Описание под чекбоксом |
| `default`      | Значение по умолчанию (`true` или `false`) |
| `classes`      | Массив дополнительных CSS-классов |
| `width`        | Ширина: full, 1/2, 1/3 и т.д. |
| `conditional`  | Условия отображения, например: `['subscribe_newsletter' => 'yes']` |
| `validation`   | Callback-функция для валидации |

## Пример использования

```php
$field = new WPP_Checkbox_Field([
    'name'        => 'subscribe_newsletter',
    'label'       => 'Подписаться на рассылку',
    'description' => 'Получать новости на email',
    'default'     => true,
    'classes'     => ['custom-control-input'],
    'width'       => 'full',
    'conditional' => ['country' => 'Russia'],
    'required'    => false,
    'validation'  => function ($value) {
        return is_bool($value) || $value === '1' || $value === 'on'
            ? (bool)$value
            : 'Неверное значение чекбокса';
    }
]);

$field->render();