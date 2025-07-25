# Поле: Кнопка / Ссылка — `WPP_Button_Field`

Реализует элементы:
- Обычная кнопка (`<button>`)
- Ссылка в виде кнопки (`<a class="btn ...">`)
- Простая гиперссылка (`<a>`)

## ⚙️ Поддерживаемые параметры

| Параметр       | Тип         | Описание |
|----------------|-------------|----------|
| `type`         | string      | Должен быть `'button'` (указывает на тип поля) |
| `element_type` | string      | `'button'`, `'link_button'`, `'link'` — какой элемент выводить |
| `label`        | string      | Текст внутри кнопки или ссылки |
| `btn_type`     | string      | Для кнопки: `submit`, `button`, `reset` |
| `btn_class`    | string      | Bootstrap класс: `btn-primary`, `btn-success` и т.д. |
| `href`         | string      | URL для ссылок |
| `description`  | string      | Описание под элементом |
| `conditional`  | array       | Условия отображения: `['field_name' => 'value']` |
| `width`        | string      | full, 1/2, 1/3 и т.д. |
| `classes`      | array       | Дополнительные классы обёртки |

## ✅ Примеры

### 1. Обычная кнопка "Отправить"

```php
new WPP_Button_Field([
    'type'      => 'button',
    'label'     => 'Отправить форму',
    'btn_type'  => 'submit',
    'btn_class' => 'btn-primary',
    'width'     => 'full'
]);
```

### 2. Кнопка "Сброс"

```php
new WPP_Button_Field([
    'type'      => 'button',
    'label'     => 'Сбросить',
    'btn_type'  => 'reset',
    'btn_class' => 'btn-secondary',
    'width'     => '1/2'
]);
```

### 3.Кнопка с условной логикой

```php
new WPP_Button_Field([
    'type'        => 'button',
    'label'       => 'Продолжить',
    'btn_class'   => 'btn-success',
    'conditional' => ['subscribe_newsletter' => '1'],
    'width'       => '1/2'
]);
```

### 4. Ссылка в виде кнопки

```php
new WPP_Button_Field([
    'type'         => 'button',
    'element_type' => 'link_button',
    'label'        => 'Зарегистрироваться',
    'href'         => 'https://example.com/register',
    'btn_class'    => 'btn-success',
    'width'        => '1/2'
]);
```

### 5. Ссылка без стилей кнопки

```php
new WPP_Button_Field([
    'type'         => 'button',
    'element_type' => 'link',
    'label'        => 'Подробнее',
    'href'         => 'https://example.com/about',
    'width'        => '1/2',
    'classes'      => ['text-decoration-none', 'fs-5']
]);
```

### 6. Кнопка с описанием

```php
new WPP_Button_Field([
    'type'        => 'button',
    'label'       => 'Нажми меня',
    'btn_class'   => 'btn-outline-dark',
    'description' => 'Это основная кнопка формы',
    'width'       => 'full'
]);
```

### 7. Кнопка с кастомными классами обертки.

```php
new WPP_Button_Field([
    'type'      => 'button',
    'label'     => 'Оформить заказ',
    'btn_class' => 'btn-lg btn-warning',
    'classes'   => ['form-control', 'mb-4'],
    'width'     => 'full'
]);
```

### 8. Кнопка с динамическим JS-обработчиком

```php
new WPP_Button_Field([
    'type'      => 'button',
    'element_type' => 'button',
    'label'     => 'Показать уведомление',
    'btn_class' => 'btn-info',
    'script'    => '
        document.querySelector("button[name=\'custom_action\']").addEventListener("click", function(e) {
            e.preventDefault();
            alert("Кнопка нажата!");
        });
    ',
    'width'     => 'full'
]);
```
Вы можете добавить собственный JS-код через параметр script (выводится в футере).
