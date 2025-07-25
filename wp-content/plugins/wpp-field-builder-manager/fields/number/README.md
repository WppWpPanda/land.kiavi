# Поле: Number — `WPP_Number_Field`

Реализует текстовое поле с кнопками `+` и `–` для числового ввода.

## ⚙️ Параметры

| Параметр     | Тип      | Описание |
|--------------|----------|----------|
| `type`       | string   | `'number'` — указывает на тип поля |
| `name`       | string   | Имя поля (обязательный) |
| `label`      | string   | Подпись к полю |
| `description`| string   | Описание под полем |
| `default`    | integer  | Значение по умолчанию |
| `classes`    | array    | Дополнительные CSS-классы |
| `width`      | string   | full, 1/2, 1/3 и т.д. |
| `conditional`| array    | Условия отображения |
| `min`        | integer  | Минимальное значение |
| `max`        | integer  | Максимальное значение |
| `step`       | integer  | Шаг изменения значения |

## ✅ Пример использования

```php
new WPP_Number_Field([
    'type' => 'number',
    'name' => 'quantity',
    'label' => 'Количество',
    'description' => 'Выберите количество товара',
    'default' => 1,
    'min' => 0,
    'max' => 10,
    'step' => 1,
    'width' => 'full'
]);