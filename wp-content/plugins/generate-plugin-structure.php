<?php


// Корневая папка плагина
$baseDir = 'wpp-form-builder';

// Структура файлов и папок
$structure = [
    // Основные файлы
    '' => [
        'wpp-form-builder.php',
        'readme.txt'
    ],

    // Дополнительно
    'includes' => [
        'helpers.php'
    ],

    // Ассеты
    'assets/css' => [
        'admin.css'
    ],
    'assets/js' => [
        'admin.js'
    ],

    // Ядро
    'core' => [
        'WPP_Field_Builder.php'
    ],

    // Поля формы
    'core/fields/text' => [
        'text-field.php',
        'style.css',
        'script.js',
        'README.md'
    ],
    'core/fields/textarea' => [
        'textarea-field.php',
        'style.css',
        'script.js',
        'README.md'
    ],
    'core/fields/email' => [
        'email-field.php',
        'style.css',
        'script.js',
        'README.md'
    ],
    'core/fields/tel' => [
        'tel-field.php',
        'style.css',
        'script.js',
        'README.md'
    ],
    'core/fields/number' => [
        'number-field.php',
        'style.css',
        'script.js',
        'README.md'
    ],
    'core/fields/select' => [
        'select-field.php',
        'style.css',
        'script.js',
        'README.md'
    ],
    'core/fields/multiselect' => [
        'multiselect-field.php',
        'style.css',
        'script.js',
        'README.md'
    ],
    'core/fields/select2' => [
        'select2-field.php',
        'style.css',
        'script.js',
        'README.md'
    ],
    'core/fields/checkbox' => [
        'checkbox-field.php',
        'style.css',
        'script.js',
        'README.md'
    ],
    'core/fields/radio' => [
        'radio-field.php',
        'style.css',
        'script.js',
        'README.md'
    ],
    'core/fields/switch' => [
        'switch-field.php',
        'style.css',
        'script.js',
        'README.md'
    ],
    'core/fields/button-group' => [
        'button-group-field.php',
        'style.css',
        'script.js',
        'README.md'
    ],
    'core/fields/google-address' => [
        'google-address-field.php',
        'style.css',
        'script.js',
        'README.md'
    ],
    'core/fields/repeater' => [
        'repeater-field.php',
        'style.css',
        'script.js',
        'README.md'
    ],
    'core/fields/section' => [
        'section-field.php',
        'style.css',
        'script.js',
        'README.md'
    ],
    'core/fields/accordion' => [
        'accordion-field.php',
        'style.css',
        'script.js',
        'README.md'
    ]
];

// Создаем папки и пустые файлы
foreach ($structure as $dir => $files) {
    $fullPath = $baseDir . ($dir ? '/' . $dir : '');

    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0777, true);
        echo "Создана папка: $fullPath\n";
    }

    foreach ($files as $file) {
        $filePath = $fullPath . '/' . $file;
        if (!file_exists($filePath)) {
            touch($filePath);
            echo "Создан файл: $filePath\n";
        } else {
            echo "Файл уже существует: $filePath\n";
        }
    }
}

echo "\n✅ Структура плагина успешно создана!";