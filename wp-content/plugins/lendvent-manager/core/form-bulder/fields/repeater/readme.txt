echo $formManager->renderField('repeater', [
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