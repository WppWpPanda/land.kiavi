echo $formManager->renderField('repeater-block', [
    'name' => 'team_members',
    'label' => 'Члены команды',
    'min_blocks' => 1,
    'max_blocks' => 5,
    'fields' => [
        [
            'name' => 'name',
            'type' => 'text',
            'args' => [
                'label' => 'Имя',
                'required' => true
            ]
        ],
        [
            'name' => 'position',
            'type' => 'text',
            'args' => [
                'label' => 'Должность'
            ]
        ],
        [
            'name' => 'bio',
            'type' => 'textarea',
            'args' => [
                'label' => 'Биография',
                'rows' => 3
            ]
        ],
        [
            'name' => 'photo',
            'type' => 'file',
            'args' => [
                'label' => 'Фото'
            ]
        ]
    ],
    'values' => [
        [
            'name' => 'Иван Иванов',
            'position' => 'Дизайнер',
            'bio' => 'Опыт работы 5 лет'
        ]
    ]
]);


$out .= $formFieldsManager->renderField('repeater-block', [
        'name' => 'team_members',
        'label' => 'Члены команды',
        'min_blocks' => 1,
        'max_blocks' => 5,
        'fields' => [
            [
                'name' => 'name',
                'type' => 'text',
                'args' => [
                    'label' => 'Имя',
                    'required' => true
                ]
            ],
            [
                'name' => 'position',
                'type' => 'text',
                'args' => [
                    'label' => 'Должность'
                ]
            ],
            [
                'name' => 'bio',
                'type' => 'textarea',
                'args' => [
                    'label' => 'Биография',
                    'rows' => 3
                ]
            ],
            [

                'type' => 'radio-buttons',
                'name' => 'bio2',
                'args' => [
                    'label' => 'Цветовая тема',
                    'options' => [
                        'light' => 'Bridge / Fix and Flip / Fix to Rent',
                        'dark' => 'New Construction *',
                        'system' => 'I\'m not sure yet'
                    ],
                    'button_style' => 'outline',
                    'color' => '#FF5722',
                    'values' => [
                        [
                            'name' => 'Иван Иванов',
                            'position' => 'Дизайнер',
                            'bio' => 'Опыт работы 5 лет'
                        ]
                    ]
                ]
            ],

        ]
    ]);

Данные приходят в виде массива:
[
    'team_members' => [
        ['name' => '...', 'position' => '...'],
        ['name' => '...', 'position' => '...']
    ]
]

//Для обработки данных в форме:

if (isset($_POST['team_members'])) {
    foreach ($_POST['team_members'] as $member) {
        $name = sanitize_text_field($member['name'] ?? '');
        $position = sanitize_text_field($member['position'] ?? '');
        // Обработка остальных полей...
    }
}