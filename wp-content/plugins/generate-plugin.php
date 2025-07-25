<?php

$structure =  [
    'wpp-loan-application.php', // Главный файл плагина
    'README.md',

    'assets' => [
        'css' => ['frontend.css'],
        'js' => ['frontend.js']
    ],

    'fields' => [
        'loan' => [
            'WPP_Loan_Application_Field.php',
            'WPP_Loan_Step_Personal.php',
            'WPP_Loan_Step_Property.php',
            'WPP_Loan_Step_Income.php',
            'WPP_Loan_Step_Submit.php',
            'style.css',
            'script.js'
        ]
    ],

    'includes' => [
        'class-wpp-loan-assets.php',
        'class-wpp-loan-form-handler.php',
        'class-wpp-loan-session.php',
        'class-wpp-loan-application-handler.php'
    ],

    'shortcodes' => ['wpp-loan-shortcode.php'],

    'templates' => [
        'step-personal.php',
        'step-property.php',
        'step-income.php',
        'step-review.php'
    ],

    'pages' => [
        'wpp-loan-step-personal.php',
        'wpp-loan-step-property.php',
        'wpp-loan-step-income.php',
        'wpp-loan-step-review.php'
    ],

    'applications' => [
        'class-wpp-loan-application-handler.php'
    ],

    'steps' => [
        'config' => [
            'personal.php',
            'property.php',
            'income.php',
            'review.php'
        ]
    ]
];

function create_structure($base_path, $structure) {
    $root = $base_path . '/' . $structure['plugin_root'];

    if (!file_exists($root)) {
        mkdir($root, 0777, true);
    }

    foreach ($structure['files'] as $file) {
        $path = "$root/$file";
        if (!file_exists($path)) file_put_contents($path, '');
    }

    foreach ($structure['directories'] as $dir => $content) {
        $dir_path = "$root/$dir";
        if (!file_exists($dir_path)) mkdir($dir_path, 0777, true);

        if (isset($content['files'])) {
            foreach ($content['files'] as $file) {
                $file_path = "$dir_path/$file";
                if (!file_exists($file_path)) file_put_contents($file_path, '');
            }
        }

        if (isset($content['directories'])) {
            foreach ($content['directories'] as $subdir => $subcontent) {
                $subdir_path = "$dir_path/$subdir";
                if (!file_exists($subdir_path)) mkdir($subdir_path, 0777, true);

                if (isset($subcontent['files'])) {
                    foreach ($subcontent['files'] as $file) {
                        $file_path = "$subdir_path/$file";
                        if (!file_exists($file_path)) file_put_contents($file_path, '');
                    }
                }
            }
        }
    }
}

// Укажи путь к директории, где должен быть создан плагин
$project_path = __DIR__; // текущая директория

create_structure($project_path, $structure);

echo "Структура плагина успешно создана в: " . $project_path . '/' . $structure['plugin_root'];