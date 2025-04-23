<?php
/*
Plugin Name: Lendvent Manager
*/

require_once 'core/init.php';

function wpp_loan_form()
{
    $formFieldsManager = FormFieldsManager::getInstance();
    $formFieldsManager->init();

    $out = $formFieldsManager->renderField('text', [
        'name' => 'username',
        'label' => 'Ваше имя',
        'placeholder' => 'Введите имя',
        'required' => true
    ]);

    $out .= $formFieldsManager->renderField('select', [
        'name' => 'country',
        'label' => 'Страна',
        'options' => [
            'ru' => 'Россия',
            'us' => 'США',
            'de' => 'Германия',
            'fr' => 'Франция'
        ],
        'placeholder' => 'Выберите страну',
        'required' => true
    ]);

// Множественный выбор
    $out .= $formFieldsManager->renderField('select', [
        'name' => 'interests',
        'label' => 'Интересы',
        'options' => [
            'sports' => 'Спорт',
            'music' => 'Музыка',
            'books' => 'Книги',
            'travel' => 'Путешествия'
        ],
        'multiple' => true,
        'value' => ['music', 'travel'] // предвыбранные значения
    ]);

    $out .= $formFieldsManager->renderField('radio', [
        'name' => 'gender',
        'label' => 'Пол',
        'options' => [
            'male' => 'Мужской',
            'female' => 'Женский'
        ],
        'value' => 'male',
        'required' => true
    ]);

// Горизонтальное расположение
    $out .= $formFieldsManager->renderField('radio', [
        'name' => 'payment_method',
        'label' => 'Способ оплаты',
        'options' => [
            'card' => 'Кредитная карта',
            'cash' => 'Наличные',
            'transfer' => 'Банковский перевод'
        ],
        'inline' => true,
        'wrap_class' => 'payment-methods'
    ]);

// С дополнительным классом для обертки
    $out .= $formFieldsManager->renderField('radio', [
        'name' => 'subscription',
        'label' => 'Тип подписки',
        'options' => [
            'free' => 'Бесплатная',
            'monthly' => 'Месячная ($10)',
            'yearly' => 'Годовая ($100)'
        ],
        'wrap_class' => 'subscription-options'
    ]);

    $out .= $formFieldsManager->renderField('checkbox', [
        'name' => 'agree_terms',
        'option_label' => 'Я согласен с условиями',
        'value' => true,
        'required' => true,
        'single' => true
    ]);

// Группа чекбоксов (вертикальное расположение)
    $out .= $formFieldsManager->renderField('checkbox', [
        'name' => 'interests',
        'label' => 'Ваши интересы',
        'options' => [
            'sports' => 'Спорт',
            'music' => 'Музыка',
            'books' => 'Книги',
            'travel' => 'Путешествия'
        ],
        'value' => ['music', 'travel'],
        'required' => true
    ]);

// Группа чекбоксов (горизонтальное расположение)
    $out .= $formFieldsManager->renderField('checkbox', [
        'name' => 'preferred_contact',
        'label' => 'Предпочитаемый способ связи',
        'options' => [
            'email' => 'Email',
            'phone' => 'Телефон',
            'sms' => 'SMS',
            'whatsapp' => 'WhatsApp'
        ],
        'inline' => true,
        'wrap_class' => 'contact-methods'
    ]);

    $out .= $formFieldsManager->renderField('radio-buttons', [
        'name' => 'delivery_method',
        'label' => 'Способ доставки',
        'options' => [
            'courier' => 'Курьер',
            'pickup' => 'Самовывоз',
            'post' => 'Почта'
        ],
        'value' => 'courier'
    ]);

// Стиль "pill" и кастомный цвет
    $out .= $formFieldsManager->renderField('radio-buttons', [
        'name' => 'payment_method',
        'label' => 'Способ оплаты',
        'options' => [
            'card' => 'Карта',
            'cash' => 'Наличные',
            'online' => 'Онлайн'
        ],
        'button_style' => 'pill',
        'color' => '#4CAF50'
    ]);

// Outline стиль
    $out .= $formFieldsManager->renderField('radio-buttons', [
        'name' => 'theme',
        'label' => 'Цветовая тема',
        'options' => [
            'light' => 'Светлая',
            'dark' => 'Темная',
            'system' => 'Как в системе'
        ],
        'button_style' => 'outline',
        'color' => '#FF5722'
    ]);

    // Простой заголовок
$out .= $formFieldsManager->renderField('heading', [
    'text' => 'Контактная информация',
    'type' => 'h2'
]);

// Заголовок с кастомными стилями
    $out .= $formFieldsManager->renderField('heading', [
    'text' => 'Специальное предложение!',
    'type' => 'h3',
    'color' => '#ff5722',
    'align' => 'center',
    'margin_bottom' => '30px',
    'extra_classes' => 'special-offer-heading'
]);

// Заголовок без обертки
    $out .= $formFieldsManager->renderField('heading', [
    'text' => 'Дополнительные опции',
    'type' => 'h4',
    'wrapper' => false
]);


// Выводим резуль_

    return $out;
}

add_shortcode('_wpp_loan_form', 'wpp_loan_form');


function _wpp_loan_form()
{
    $formFieldsManager = FormFieldsManager::getInstance();
    $formFieldsManager->init();

    $out = $formFieldsManager->renderField('heading', [
        'type' => 'h2',
        'text' => 'What kind of real estate investment are you considering?',
        'wrapper' => false
    ]);

// Outline стиль
    $out .= $formFieldsManager->renderField('radio-buttons', [
        'name' => 'theme',
        'label' => 'Цветовая тема',
        'options' => [
            'light' => 'Bridge / Fix and Flip / Fix to Rent',
            'dark' => 'New Construction *',
            'system' => 'I\'m not sure yet'
        ],
        'button_style' => 'outline',
        'color' => '#FF5722'
    ]);

    $out .= $formFieldsManager->renderField('repeater', [
        'name' => 'education',
        'label' => 'Образование',
        'add_button_text' => 'Добавить место учебы',
        'min_rows' => 1,
        'max_rows' => 5,
        'template' => [
            [
                'name' => 'institution',
                'label' => 'Учебное заведение',
                'type' => 'text'
            ],
            [
                'name' => 'year',
                'label' => 'Год окончания',
                'type' => 'text'
            ],
            [
                'name' => 'description',
                'label' => 'Описание',
                'type' => 'textarea'
            ]
        ],
        'value' => [
            [
                'institution' => 'МГУ',
                'year' => '2010',
                'description' => 'Факультет вычислительной математики'
            ]
        ]
    ]);

    // Простое текстовое поле
    $out .=$formFieldsManager->renderField('textarea', [
        'name' => 'description',
        'label' => 'Описание',
        'rows' => 6
    ]);

// Поле с дополнительными параметрами
    $out .=$formFieldsManager->renderField('textarea', [
        'name' => 'bio',
        'label' => 'Биография',
        'value' => 'Расскажите о себе...',
        'placeholder' => 'Введите информацию о себе',
        'required' => true,
        'rows' => 8,
        'wrapper_class' => 'bio-field',
        'input_class' => 'large-text'
    ]);



// Выводим резуль_

    return $out;
}
add_shortcode('wpp_loan_form', '_wpp_loan_form');