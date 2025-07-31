<?php
/**
 * WPP_Field_Builder - WPP_Documents_Upload_Field.php
 *
 * Класс для поля загрузки документов с сохранением в uploads/documents/
 *
 * @package WPP_Field_Builder
 * @subpackage Fields
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * Класс поля для загрузки документов
 */
class WPP_Documents_Upload_Field extends WPP_Form_Field {

	public function __construct($args = []) {
		parent::__construct($args);
		// Подключаем JS только если поле используется
		add_action('wp_footer', [$this, 'enqueue_assets']);
		add_action('admin_footer', [$this, 'enqueue_assets']);
	}

	public function enqueue_assets() {
		// Убедимся, что Font Awesome загружен (или подключите его в основном плагине)
		wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

		wp_enqueue_script(
			'wpp-documents-upload',
			WPP_FIELD_BUILDER_URL . 'fields/documents_upload/documents-upload.js', // Убедитесь, что путь правильный
			['jquery'],
			file_exists(WPP_FIELD_BUILDER_PATH . 'fields/documents_upload/documents-upload.js')
				? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/documents_upload/documents-upload.js')
				: time(),
			true
		);
		// Локализация скрипта (передача данных в JavaScript)
		wp_localize_script('wpp-documents-upload', 'wpp_ajax', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('wpp_upload_nonce')
		));
		wp_enqueue_style(
			'wpp-documents-upload',
			WPP_FIELD_BUILDER_URL . 'fields/documents_upload/documents-upload.css', // Убедитесь, что путь правильный
			[],
			file_exists(WPP_FIELD_BUILDER_PATH . 'fields/documents_upload/documents-upload.css')
				? filemtime(WPP_FIELD_BUILDER_PATH . 'fields/documents_upload/documents-upload.css')
				: time(),
			'all'
		);
	}

	/**
	 * Рендерит HTML-код поля для загрузки документов
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function render() {
		// Начало обёртки поля
		$this->render_wrapper_start();

		echo '<div class="wpp-documents-upload-wrap">';
		// Подпись (label)
		$this->render_label();
		$this->render_description();
		echo '</div>';

		// Получаем атрибуты из аргументов
		$name = esc_attr($this->args['name']);
		$id = sanitize_key($name);
		$placeholder = esc_attr($this->args['placeholder']);
		$required = $this->args['required'] ? 'required' : '';
		$multiple = !empty($this->args['multiple']) ? 'multiple' : '';

		// Формируем список разрешенных типов для JS
		$allowed_types_attr = '';
		if (!empty($this->args['allowed_types']) && is_array($this->args['allowed_types'])) {
			$allowed_types_attr = 'data-allowed-types="' . esc_attr(implode(',', $this->args['allowed_types'])) . '"';
		}

		// Получаем ID займа из GET-параметра
		$loan_id = isset($_GET['loan']) ? intval($_GET['loan']) : 0;

		// Получаем уже загруженные файлы ТОЛЬКО для ЭТОГО поля
		$uploaded_files = $this->get_uploaded_files($loan_id);

		// Рендерим поле загрузки документов
		echo '<div class="wpp-documents-upload-field"
                  data-loan-id="' . $loan_id . '"
                  data-field-name="' . $name . '"
                  data-field-id="' . $id . '"
                  ' . $allowed_types_attr . '>';

		// Отображаем загруженные документы
		if (!empty($uploaded_files)) {
			foreach ($uploaded_files as $file) {
				$file_url = $file['url'];
				$file_name = $file['name'];

				// Отображаем имя файла с ссылкой
				echo '<a href="' . esc_url($file_url) . '" target="_blank">' . esc_html($file_name) . '</a>';

				// Дата загрузки файла
				$upload_date = date('M j, Y', filemtime($file['path'])); // Получаем дату модификации файла
				echo ' <span class="date">' . $upload_date . '</span>';

				// Селект со статусами
				echo ' <select class="status-select">';
				echo '<option value="waiting_for_review">Waiting for Review</option>';
				echo '<option value="reviewing">Reviewing</option>';
				echo '<option value="changes_required">Changes Required</option>';
				echo '<option value="rejected">Rejected</option>';
				echo '<option value="accepted">Accepted</option>';
				echo '</select>';

				// Кнопка удаления
				echo ' <button type="button" class="remove-file" data-file="' . esc_attr($file_name) . '" data-field="' . $name . '">✕</button>';
			}
		} else {
			// Если файл не загружен
			echo '<span class="upload-link"><i class="fas fa-cloud-upload-alt"></i> upload file</span>';

			echo '<div class="clearfix-bate"></div>';
			// Сообщение "missing documents"
			echo ' <span class="missing-documents"><i class="fas fa-exclamation-triangle"></i> missing documents</span>';
			echo '<div class="clearfix-btn"></div>';
		}

		echo '</div>';

		// Конец обёртки поля
		$this->render_wrapper_end();
	}

	/**
	 * Возвращает массив загруженных документов ТОЛЬКО для ЭТОГО поля
	 *
	 * @param int $loan_id ID займа
	 * @return array
	 * @since 1.0.0
	 */
	private function get_uploaded_files($loan_id) {
		if (!$loan_id || empty($this->args['name'])) {
			return [];
		}

		// Получаем путь к папке документов
		$upload_dir = wp_upload_dir();
		$documents_dir = $upload_dir['basedir'] . '/documents/' . $loan_id . '/';

		// Проверяем существование директории
		if (!is_dir($documents_dir)) {
			return [];
		}

		// Формируем паттерн для файлов этого конкретного поля
		// Файлы будут называться: {field_name}_{original_filename}
		$field_name_sanitized = sanitize_file_name($this->args['name']);
		$field_files_pattern = $documents_dir . $field_name_sanitized . '_*';

		$files = [];
		$file_list = glob($field_files_pattern);

		foreach ($file_list as $file_path) {
			if (is_file($file_path)) {
				$file_name = basename($file_path);
				$file_url = $upload_dir['baseurl'] . '/documents/' . $loan_id . '/' . $file_name;
				$files[] = [
					'name' => $file_name,
					'path' => $file_path,
					'url' => $file_url
				];
			}
		}

		return $files;
	}


	/**
	 * Обрабатывает загрузку документов, сохраняя файл с префиксом имени поля
	 *
	 * @param array $uploaded_file Загруженный файл из $_FILES
	 * @param int $loan_id ID займа
	 * @return array|WP_Error
	 */
	public function handle_file_upload($uploaded_file, $loan_id) {
		if (!$loan_id) {
			return new WP_Error('invalid_loan_id', 'Invalid loan ID');
		}

		// Проверяем, был ли файл загружен
		if (empty($uploaded_file) || $uploaded_file['error'] !== UPLOAD_ERR_OK) {
			return new WP_Error('upload_error', 'File upload error');
		}

		// Проверяем тип файла
		$allowed_types = !empty($this->args['allowed_types']) ? $this->args['allowed_types'] : [
			'application/pdf',
			'image/jpeg',
			'image/png',
			'image/gif',
			'application/msword',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
		];

		$file_type = wp_check_filetype_and_ext($uploaded_file['tmp_name'], $uploaded_file['name']);

		if (!in_array($file_type['type'], $allowed_types)) {
			return new WP_Error('invalid_file_type', 'Invalid file type. Allowed: ' . implode(', ', $allowed_types));
		}

		// Создаем директорию для документов
		$upload_dir = wp_upload_dir();
		$documents_dir = $upload_dir['basedir'] . '/documents/' . $loan_id . '/';

		if (!wp_mkdir_p($documents_dir)) {
			return new WP_Error('mkdir_error', 'Could not create documents directory');
		}

		// Генерируем уникальное имя файла с префиксом имени поля
		$field_name_sanitized = sanitize_file_name($this->args['name']);
		$filename = $field_name_sanitized . '_' . wp_unique_filename($documents_dir, $uploaded_file['name']);
		$file_path = $documents_dir . $filename;

		// Перемещаем файл
		if (!move_uploaded_file($uploaded_file['tmp_name'], $file_path)) {
			return new WP_Error('move_error', 'Could not move uploaded file');
		}

		// Возвращаем информацию о файле
		$file_url = $upload_dir['baseurl'] . '/documents/' . $loan_id . '/' . $filename;

		// Добавляем дату загрузки
		$upload_date = date('M j, Y'); // Текущая дата

		return [
			'name' => $filename, // Возвращаем имя с префиксом
			'path' => $file_path,
			'url' => $file_url,
			'upload_date' => $upload_date
		];
	}

	/**
	 * Удаляет документ конкретного поля
	 *
	 * @param string $filename Имя файла (с префиксом поля)
	 * @param int $loan_id ID займа
	 * @return bool
	 */
	public function delete_file($filename, $loan_id) {
		if (!$loan_id || empty($filename)) {
			return false;
		}

		// Проверяем, принадлежит ли файл этому полю (по префиксу)
		$field_name_sanitized = sanitize_file_name($this->args['name']);
		if (strpos($filename, $field_name_sanitized . '_') !== 0) {
			// Файл не принадлежит этому полю
			return false;
		}

		$upload_dir = wp_upload_dir();
		$file_path = $upload_dir['basedir'] . '/documents/' . $loan_id . '/' . $filename;

		if (file_exists($file_path)) {
			return unlink($file_path);
		}

		return false;
	}

	/**
	 * Переопределяем метод валидации для документов
	 *
	 * @param mixed $value Загруженный файл
	 * @return mixed
	 * @since 1.0.0
	 */
	public function validate($value) {
		// Если задан пользовательский callback валидации - используем его
		if ($this->args['validation'] && is_callable($this->args['validation'])) {
			return call_user_func($this->args['validation'], $value);
		}

		// Получаем ID займа
		$loan_id = isset($_GET['loan']) ? intval($_GET['loan']) : 0;

		if (!$loan_id) {
			return new WP_Error('invalid_loan_id', 'Loan ID is required');
		}

		// Если файл не загружен, но поле обязательно - возвращаем ошибку
		if (empty($value) && $this->args['required']) {
			// Проверяем, есть ли уже загруженные документы
			$existing_files = $this->get_uploaded_files($loan_id);
			if (empty($existing_files)) {
				return new WP_Error('upload_required', 'File upload is required');
			}
		}

		// Если файл загружен - обрабатываем его
		if (!empty($value) && is_array($value) && isset($value['tmp_name'])) {
			$result = $this->handle_file_upload($value, $loan_id);
			if (is_wp_error($result)) {
				return $result;
			}
			return $result;
		}

		return $value;
	}

	/**
	 * Получает URL для загрузки документов через AJAX
	 *
	 * @return string
	 */
	public function get_ajax_url() {
		return admin_url('admin-ajax.php');
	}
}

// AJAX-обработчики для загрузки документов
add_action('wp_ajax_wpp_upload_document', 'wpp_handle_document_upload');
add_action('wp_ajax_nopriv_wpp_upload_document', 'wpp_handle_document_upload');

function wpp_handle_document_upload() {
	// Проверяем nonce для безопасности
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpp_upload_nonce')) {
		wp_send_json_error('Security check failed');
	}

	if (!isset($_POST['loan_id']) || !isset($_FILES['file']) || !isset($_POST['field_name'])) {
		wp_send_json_error('Invalid request: Missing required data');
	}

	$loan_id = intval($_POST['loan_id']);
	$field_name = sanitize_text_field($_POST['field_name']);

	// Создаем временное поле для обработки загрузки
	// Передаем имя поля, чтобы методы handle_file_upload и get_uploaded_files работали корректно
	$temp_args = ['name' => $field_name];
	if (isset($_POST['allowed_types'])) {
		$temp_args['allowed_types'] = explode(',', sanitize_text_field($_POST['allowed_types']));
	}

	$field = new WPP_Documents_Upload_Field($temp_args);
	$result = $field->handle_file_upload($_FILES['file'], $loan_id);

	if (is_wp_error($result)) {
		wp_send_json_error($result->get_error_message());
	}

	wp_send_json_success($result);
}

// Обработчики удаления документов
add_action('wp_ajax_wpp_delete_document', 'wpp_handle_document_delete');
add_action('wp_ajax_nopriv_wpp_delete_document', 'wpp_handle_document_delete');

function wpp_handle_document_delete() {
	// Проверяем nonce для безопасности
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpp_upload_nonce')) {
		wp_send_json_error('Security check failed');
	}

	if (!isset($_POST['loan_id']) || !isset($_POST['filename']) || !isset($_POST['field_name'])) {
		wp_send_json_error('Invalid request: Missing required data');
	}

	$loan_id = intval($_POST['loan_id']);
	$filename = sanitize_file_name($_POST['filename']);
	$field_name = sanitize_text_field($_POST['field_name']);

	// Создаем временное поле для обработки удаления
	// Передаем имя поля, чтобы метод delete_file знал, какой файл удалять
	$field = new WPP_Documents_Upload_Field(['name' => $field_name]);
	$result = $field->delete_file($filename, $loan_id);

	if (!$result) {
		wp_send_json_error('Could not delete file or file does not belong to this field');
	}

	wp_send_json_success();
}
