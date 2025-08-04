<?php
/**
 * Example: Display brokers with Edit/Delete actions
 *
 * Fixed version with search hidden and proper data display.
 *
 * @package YourPlugin
 * @since   1.0.0
 */

// 1. Подключаем основной класс
if ( ! class_exists( 'Wpp_List_Table' ) ) {
	$class_file = WP_PLUGIN_DIR . '/wpp-core/wpp_libs/wpp_list_table/Wpp_List_Table.php';

	if ( file_exists( $class_file ) ) {
		require_once $class_file;
	} else {
		echo '<div class="error"><p>Ошибка: Не найден файл Wpp_List_Table.php по пути: ' . esc_html( $class_file ) . '</p></div>';
		return;
	}
}

// 2. Определяем URL к assets
if ( ! defined( 'WPP_TABLE_URL' ) ) {
	define( 'WPP_TABLE_URL', plugin_dir_url( __FILE__ ) . '../wpp_libs/wpp_list_table/' );
	// /wp-content/plugins/wpp-core/wpp_libs/wpp_list_table/
}

/**
 * Класс для отображения брокеров с действиями
 */
class Brokers_Table extends Wpp_List_Table {

	protected $table_name         = 'wpp_brokers';
	protected $primary_key        = 'id';
	protected $per_page           = 15;
	protected $show_search        = false; // 🔒 Скрыть форму поиска
	protected $enqueue_assets     = true;

	protected $columns = array(
		'id'                      => 'ID',
		'brok_brokerage_name'     => 'Brokerage Name',
		'brok_parent_brokerage'   => 'Parent Brokerage',
		'brok_city'               => 'City',
		'brok_state'              => 'State',
		'brok_broker_bdm'         => 'Broker/BDM',
		//'actions'                 => 'Actions'
	);

	protected $sortable_columns = array(
		'id',
		'brok_brokerage_name',
		'brok_city',
		'brok_state',
		'brok_broker_bdm'
	);

	protected $searchable_columns = array(
		'brok_brokerage_name',
		'brok_parent_brokerage',
		'brok_city',
		'brok_state',
		'brok_broker_bdm',
		'brok_address'
	);

	/**
	 * Рендер колонки "Actions"
	 */
	public function column_actions( $item ) {
		$edit_url = add_query_arg(
			array(
				'page' => 'edit-broker',
				'id'   => $item['id']
			),
			admin_url( 'admin.php' )
		);

		$delete_url = add_query_arg(
			array(
				'page'   => 'manage-brokers',
				'action' => 'delete_broker',
				'id'     => $item['id']
			),
			admin_url( 'admin.php' )
		);
		$delete_url = wp_nonce_url( $delete_url, 'delete_broker_' . $item['id'], 'broker_nonce' );

		$actions = sprintf(
			'<a href="%s" class="button button-small" style="font-size:12px;padding:4px 8px;">✏️ Edit</a>',
			esc_url( $edit_url )
		);

		$actions .= ' ' . sprintf(
				'<a href="%s" class="button button-small" style="background:#a00;color:white;font-size:12px;padding:4px 8px;" onclick="return confirm(\'Delete this broker?\nThis cannot be undone.\')">🗑️ Delete</a>',
				esc_url( $delete_url )
			);

		return $actions;
	}

	/**
	 * Дополнительная отладка: вывод SQL-запроса (временно)
	 * Раскомментируй для отладки
	 */
	 protected function get_query() {
	     $query = parent::get_query();
	      error_log( 'Brokers_Table Query: ' . $query ); // смотри в debug.log
	     return $query;
	 }
}

// === Вывод таблицы ===

// Проверка: существует ли таблица в БД?
global $wpdb;
$table_name = $wpdb->prefix . 'wpp_brokers';

if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) !== $table_name ) {
	echo '<div class="error"><p>❌ Ошибка: Таблица <code>' . esc_html( $table_name ) . '</code> не существует в базе данных.</p></div>';
	return;
}

// Проверка: есть ли данные?
$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
if ( $count === null ) {
	echo '<div class="error"><p>❌ Ошибка: Не удалось получить данные из таблицы.</p></div>';
	return;
}

// Создаём и выводим таблицу
$table = new Brokers_Table();

// 🔍 Отладка: проверим, что объект создан
if ( ! $table ) {
	echo '<div class="error"><p>❌ Ошибка: Не удалось создать объект Brokers_Table.</p></div>';
	return;
}
