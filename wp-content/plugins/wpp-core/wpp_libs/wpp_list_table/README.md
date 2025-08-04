# Wpp_List_Table — Universal Database Table Display Library

![Version](https://img.shields.io/badge/version-2.2.0-blue)
![License](https://img.shields.io/badge/license-GPL--2.0--or--later-red)

A lightweight, reusable, and standalone PHP library for displaying data from **any WordPress database table** on the **frontend or backend** with full support for:

- ✅ Pagination
- ✅ Column sorting
- ✅ Search (with optional AJAX)
- ✅ Responsive design
- ✅ XSS and SQL injection protection
- ✅ CSS/JS assets with modern styling
- ✅ Localization-ready (text domain: `wpp`)
- ✅ No dependency on `WP_List_Table`

Perfect for plugins, themes, admin panels, or public-facing data displays.

---

## 📁 Directory Structure
wpp_list_table/
├── Wpp_List_Table.php # Main PHP class (OOP, abstract)
├── assets/
│ ├── css/
│ │ └── wpp-table.css # Responsive, modern styling
│ └── js/
│ └── wpp-table.js # AJAX pagination & search
└── readme.md # This file


---

## 🚀 Installation

1. Copy the `wpp_list_table` folder into your project:

your-project/
└── wpp_libs/
    └── wpp_list_table/


2. Include the class in your PHP file:
```php
require_once 'wpp_libs/wpp_list_table/Wpp_List_Table.php';
```
Ensure your database table exists (e.g., wp_your_custom_table).


## 🧩 Basic Usage
Create a child class and configure your table:
```php
class UserDataTable extends Wpp_List_Table {
protected $table_name         = 'users_data';        // → wp_users_data
protected $primary_key        = 'id';
protected $columns            = [
'id'      => 'ID',
'name'    => 'Name',
'email'   => 'Email',
'role'    => 'Role',
'created' => 'Created'
];
protected $sortable_columns   = [ 'id', 'name', 'email', 'created' ];
protected $searchable_columns = [ 'name', 'email', 'role' ];
protected $per_page           = 10;
}

$table = new UserDataTable();
echo $table->display(); // Outputs full HTML table
```

## ⚙️ Configuration Options
| PROPERTY | TYPE | DEFAULT | DESCRIPTION |
|----------|------|---------|-------------|
| **$table_name** | string | — | Name of the DB table (without prefix) |
| **$primary_key** | string | `id` | Primary key field for default sorting |
| **$columns** | array | — | `[ 'db_field' => 'Label' ]` — defines visible columns |
| **$sortable_columns** | array | `[]` | Fields that support click-to-sort |
| **$searchable_columns** | array | `[]` | Fields included in search (`LIKE %query%`) |
| **$per_page** | int | `20` | Number of items per page |
| **$enqueue_assets** | bool | `true` | Auto-load CSS/JS (set `false` to disable) |

## 🌐 Localization (i18n)
All UI strings use the wpp text domain for translation:
```php
esc_html_e( 'No items found.', 'wpp' );
esc_attr_e( 'Search...', 'wpp' );
```
To load translations, ensure your theme/plugin includes:
```php
load_plugin_textdomain( 'wpp', false, 'languages/' );
```
## 💡 Advanced Usage
### Disable Asset Loading
If you want to use your own styles/scripts:
```php
class MinimalTable extends Wpp_List_Table {
    protected $enqueue_assets = false;
    // ... other settings
}
```

### Customize Asset Path
Define a custom URL for assets:
```php
define( 'WPP_TABLE_URL', get_template_directory_uri() . '/wpp_libs/wpp_list_table/' );
require_once 'wpp_libs/wpp_list_table/Wpp_List_Table.php';
```

## 🎨 Features

### ✅ Responsive Design
* Works perfectly on mobile, tablet, and desktop
* Horizontal scroll on small screens
* Clean, modern UI with hover effects
### ✅ AJAX-Powered Interactions
* Smooth search and pagination without page reloads
* Browser history support (pushState)
* Loading states and error handling
### ✅ Security
* SQL injection protection via $wpdb->prepare() and esc_sql()
* XSS protection via esc_html(), esc_attr()
* Input sanitization with filter_input()

### 📦 Dependencies
* WordPress (for $wpdb)
* jQuery (only if AJAX is enabled)

**💡 The class works even outside WordPress if $wpdb is available.** 

## 📄 License
Wpp_List_Table is open-source software released under the GNU General Public License v2.0 or later .

**Copyright (c) 2025 Your Name**

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

***© 2025 WP Panda***