# Поле: Текстовое поле — `WPP_Text_Field`

Поддерживает разные типы HTML-полей:
- `text` — обычное текстовое поле
- `email` — поле для email с валидацией браузера
- `tel` — телефонное поле
- `hidden` — скрытое поле (без обёртки)

---

## ⚙️ Параметры

| Параметр       | Тип         | Описание |
|----------------|-------------|----------|
| `type`         | string      | Должен быть `'text'` (тип поля) |
| `element_type` | string      | HTML-тип: `'text'`, `'email'`, `'tel'`, `'hidden'` |
| `name`         | string      | Имя поля (обязательно) |
| `label`        | string      | Подпись к полю |
| `description`  | string      | Описание под полем |
| `default`      | string      | Значение по умолчанию |
| `classes`      | array       | Дополнительные CSS-классы |
| `width`        | string      | full, 1/2, 1/3 и т.д. |
| `conditional`  | array       | Условия отображения |
| `validation`   | callable    | Callback-функция валидации |

---

## ✅ Примеры использования

### 1. Обычный текст

```php
new WPP_Text_Field([
    'type' => 'text',
    'name' => 'username',
    'label' => 'Имя пользователя',
    'placeholder' => 'Например: Иван',
    'default' => '',
    'classes' => ['form-control'],
    'width' => 'full',
    'required' => true,
    'validation' => function ($value) {
        return empty($value) ? 'Поле обязательно!' : sanitize_text_field($value);
    }
]);

new WPP_Text_Field([
    'type' => 'text',
    'element_type' => 'email',
    'name' => 'user_email',
    'label' => 'Email',
    'placeholder' => 'example@domain.com',
    'default' => '',
    'required' => true,
    'validation' => function ($value) {
        return is_email($value) ? $value : 'Введите корректный email';
    }
]);

new WPP_Text_Field([
    'type' => 'text',
    'element_type' => 'tel',
    'name' => 'phone',
    'label' => 'Телефон',
    'placeholder' => '+7 (___) ___-__-__',
    'default' => '',
    'classes' => ['form-control'],
    'width' => 'full'
]);

new WPP_Text_Field([
    'type' => 'text',
    'element_type' => 'hidden',
    'name' => 'security_token',
    'default' => wp_create_nonce('wpp_form_submit')
]);

'interest_rate' => [
        'type' => 'text',
        'element_type' => 'percentage',
        'name' => 'interest_rate',
        'label' => 'Interest Rate',
        'placeholder' => '12.00%',
        'width' => '1/2'
    ],