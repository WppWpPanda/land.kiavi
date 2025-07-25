# Поле: Repeater — `WPP_Repeater_Field`

Поле-повторитель, позволяющий добавлять динамические блоки.

## ⚙️ Параметры

| Параметр       | Тип         | Описание |
|----------------|-------------|----------|
| `type`         | string      | `'repeater'` |
| `name`         | string      | Имя поля |
| `title`        | string      | Заголовок группы |
| `fields`       | array       | Поля внутри блока |
| `min`          | integer     | Минимальное количество блоков |
| `max`          | integer     | Максимальное количество блоков |
| `description`  | string      | Описание под полем |
| `conditional`  | array       | Условия отображения |
| `width`        | string      | full, 1/2, 1/3 и т.д. |
| `classes`      | array       | Дополнительные CSS-классы |

## ✅ Примеры

### 1. Контакты пользователя

```php
new WPP_Repeater_Field([
    'type' => 'repeater',
    'name' => 'user_contacts',
    'title' => 'Контакт',
    'min' => 1,
    'max' => 5,
    'fields' => [
        'phone' => [
            'type' => 'text',
            'label' => 'Телефон',
            'placeholder' => '+7 (___) ___-__-__'
        ],
        'email' => [
            'type' => 'text',
            'label' => 'Email',
            'placeholder' => 'example@domain.com'
        ],
        'type' => [
            'type' => 'select',
            'label' => 'Тип',
            'options' => [
                'work' => 'Рабочий',
                'home' => 'Домашний',
                'other' => 'Другой'
            ]
        ]
    ]
]);
```
```php
new WPP_Repeater_Field([
    'type' => 'repeater',
    'name' => 'delivery_addresses',
    'title' => 'Адрес доставки',
    'min' => 1,
    'max' => 3,
    'fields' => [
        'address' => [
            'type' => 'address',
            'label' => 'Адрес доставки',
            'placeholder' => 'Введите адрес'
        ],
        'default' => [
            'type' => 'checkbox',
            'label' => 'Сделать основным'
        ]
    ]
]);
```