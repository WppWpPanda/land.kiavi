<?php
/**
 * WPP_Field_Builder - test/test-form.php
 *
 * Тестовая форма с поддержкой всех типов:
 * - text (в т.ч. email, tel, hidden)
 * - textarea
 * - checkbox
 * - radio
 * - select / select2
 * - button (submit, link_button, link)
 * - address
 * - accordion
 * - repeater
 *
 * @package WPP_Field_Builder
 * @since 1.0.0
 * @author Your Name <your@email.com>
 * @license GPL-2.0-or-later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Регистрация шорткода
add_shortcode('wpp_custom_form', 'wpp_render_custom_form');
function wpp_render_custom_form() {
    ob_start();

    // Массив всех полей формы
    $form_fields = [
        'user_name' => [
            'type'        => 'text',
            'element_type' => 'text',
            'label'       => 'Имя пользователя',
            'description' => 'Введите ваше имя',
            'placeholder' => 'Например: Иван',
            'default'     => '',
            'width'       => 'full',
        ],
        'user_email' => [
            'type'        => 'text',
            'element_type' => 'email',
            'label'       => 'Email',
            'description' => 'Введите ваш email',
            'placeholder' => 'example@domain.com',
            'default'     => '',
            'width'       => 'full',
            'required'    => true,
        ],
        'phone' => [
            'type'        => 'text',
            'element_type' => 'tel',
            'label'       => 'Телефон',
            'description' => 'Формат: +7 (___) ___-__-__',
            'placeholder' => '+7 (___) ___-__-__',
            'default'     => '',
            'width'       => 'full',
        ],
        'security_token' => [
            'type'         => 'text',
            'element_type' => 'hidden',
            'name'         => 'security_token',
            'default'      => wp_create_nonce('wpp_form_submit'),
        ],
        'user_bio' => [
            'type'        => 'textarea',
            'label'       => 'О себе',
            'description' => 'Расскажите немного о себе',
            'placeholder' => 'Например: Я люблю WordPress',
            'default'     => '',
            'width'       => 'full',
            'rows'        => 5,
            'conditional' => ['user_name' => 'John'], // показывается только если имя — John
        ],
        'subscribe_newsletter' => [
            'type'        => 'checkbox',
            'label'       => 'Подписаться на рассылку',
            'description' => 'Получать обновления по email',
            'default'     => true,
            'width'       => 'full'
        ],
        'gender' => [
            'type'        => 'radio',
            'label'       => 'Ваш пол',
            'description' => 'Выберите один из вариантов',
            'default'     => 'male',
            'width'       => 'full',
            'options'     => [
                'male'   => 'Мужской',
                'female' => 'Женский',
                'other'  => 'Другой'
            ],
            'conditional' => ['subscribe_newsletter' => '1']
        ],
        'country' => [
            'type'        => 'select',
            'label'       => 'Страна проживания',
            'default'     => 'ru',
            'width'       => '1/2',
            'options'     => [
                'ru' => 'Россия',
                'ua' => 'Украина',
                'kz' => 'Казахстан'
            ]
        ],
        'favorite_colors' => [
            'type'     => 'select',
            'label'    => 'Любимые цвета',
            'default'  => ['red', 'blue'],
            'width'    => '1/2',
            'multiple' => true,
            'select2'  => true,
            'options'  => [
                'red'   => 'Красный',
                'green' => 'Зелёный',
                'blue'  => 'Синий'
            ]
        ],
        'advanced_settings_accordion' => [
            'type'    => 'accordion',
            'name'    => 'advanced_settings',
            'title'   => 'Расширенные настройки',
            'content' => function () {
                $field = new WPP_Checkbox_Field([
                    'type'  => 'checkbox',
                    'name'  => 'enable_debug_mode',
                    'label' => 'Включить режим отладки',
                ]);
                $field->render();
            },
            'open' => false,
        ],
        'contact_repeater' => [
            'type'    => 'repeater',
            'name'    => 'user_contacts',
            'title'   => 'Контактные данные',
            'min'     => 1,
            'max'     => 5,
            'fields'  => [
                'phone' => [
                    'type' => 'text',
                    'element_type' => 'tel',
                    'label' => 'Телефон',
                    'placeholder' => '+7 (___) ___-__-__'
                ],
                'email' => [
                    'type' => 'text',
                    'element_type' => 'email',
                    'label' => 'Email',
                    'placeholder' => 'example@domain.com'
                ],
                'type' => [
                    'type' => 'select',
                    'label' => 'Тип контакта',
                    'options' => [
                        'work' => 'Рабочий',
                        'home' => 'Домашний',
                        'other' => 'Другой'
                    ]
                ]
            ]
        ],
        'address_field' => [
            'type' => 'address',
            'label' => 'Адрес доставки',
            'placeholder' => 'Введите адрес',
            'width' => 'full'
        ],
        'product_quantity' => [
            'type'        => 'number',
            'name'        => 'product_quantity',
            'label'       => 'Количество товара',
            'description' => 'Укажите, сколько штук нужно',
            'default'     => 1,
            'min'         => 0,
            'max'         => 10,
            'step'        => 1,
            'width'       => '1/2'
        ],
        'submit_button' => [
            'type' => 'button',
            'element_type' => 'button',
            'label' => 'Отправить форму',
            'btn_type' => 'submit',
            'btn_class' => 'btn-primary',
            'width' => 'full'
        ]
    ];

    ?>
    <form method="post" class="wpp-custom-form">
        <?php foreach ($form_fields as $name => $config):
            $class_name = 'WPP_' . ucfirst($config['type']) . '_Field';

            if (class_exists($class_name)) {
                $field = new $class_name(array_merge($config, ['name' => $name]));
                $field->render();
            }
        endforeach; ?>

        <input type="hidden" name="wpp_form_submitted" value="1">
    </form>
    <?php

    return ob_get_clean();
}