# Поле: Аккордеон — `WPP_Accordion_Field`

Поле для создания раскрывающегося блока с произвольным содержимым.

## ⚙️ Параметры

| Параметр       | Тип          | Описание |
|----------------|---------------|----------|
| `name`         | string        | Имя аккордеона |
| `title`        | string        | Заголовок аккордеона |
| `content`      | string / callable | Контент внутри (HTML или callback) |
| `open`         | boolean       | Открыт ли по умолчанию |
| `classes`      | array         | Дополнительные CSS-классы |
| `width`        | string        | Ширина: full, 1/2 и т.д. |

## ✅ Примеры

### 1. Простой аккордеон с текстом

```php
new WPP_Accordion_Field([
    'type'    => 'accordion',
    'name'    => 'info_block',
    'title'   => 'Информация о доставке',
    'content' => '<p>Доставка осуществляется в течение 3 дней</p>'
]);
```
```php
new WPP_Accordion_Field([
    'type'    => 'accordion',
    'name'    => 'advanced_settings',
    'title'   => 'Расширенные настройки',
    'open'    => true,
    'content' => function () {
        $checkbox = new WPP_Checkbox_Field([
            'name' => 'enable_debug',
            'label' => 'Включить режим отладки'
        ]);
        $checkbox->render();

        $radio = new WPP_Radio_Field([
            'name' => 'delivery_type',
            'label' => 'Тип доставки',
            'options' => [
                'standard' => 'Стандартная',
                'express' => 'Экспресс'
            ]
        ]);
        $radio->render();
    }
]);
```