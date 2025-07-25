wpp-field-builder-manager/
│
├── wpp-field-builder-manager.php     ← Основной файл плагина
├── README.md                         ← Этот файл
│
├── includes/                         ← Ядро плагина
│   ├── class-wpp-form-field.php      ← Абстрактный класс всех полей
│   ├── class-wpp-field-loader.php    ← Автозагрузчик полей
│   └── class-wpp-assets.php          ← Подключение ассетов (Bootstrap, jQuery)
│
├── fields/                           ← Поля формы
│   └── text/                         ← Пример поля "text"
│       ├── WPP_Text_Field.php        ← Реализация поля
│       ├── style.css                 ← Стили
│       ├── script.js                 ← JS-логика
│       └── README.md                 ← Документация по полю
│
├── assets/                           ← Общие ресурсы
│   ├── css/
│   │   ├── admin.css                 ← Стили админки
│   │   └── frontend.css              ← Стили фронтенда
│   └── js/
│       ├── admin.js                  ← JS для админки
│       └── frontend.js               ← JS для фронтенда
│
└── test/                             ← Тесты
└── field-tests.php               ← Юнит-тесты (по желанию)