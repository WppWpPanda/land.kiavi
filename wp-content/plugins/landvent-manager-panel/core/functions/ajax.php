<?php

defined( 'ABSPATH' ) || exit;
// Обработчик для скачивания всех документов в виде ZIP
add_action('wp_ajax_wpp_download_all_documents', 'wpp_handle_download_all_documents');
add_action('wp_ajax_nopriv_wpp_download_all_documents', 'wpp_handle_download_all_documents');

function wpp_handle_download_all_documents() {
	// Проверяем nonce для безопасности
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpp_upload_nonce')) {
		wp_die('Security check failed');
	}

	// Проверяем loan_id
	if (!isset($_POST['loan_id'])) {
		wp_die('Invalid request: Missing loan ID');
	}

	$loan_id = intval($_POST['loan_id']);

	// Определяем путь к директории с документами
	$upload_dir = wp_upload_dir();
	$documents_dir = $upload_dir['basedir'] . '/documents/' . $loan_id . '/';

	// Проверяем, существует ли директория
	if (!is_dir($documents_dir)) {
		wp_die('No documents found for this loan.');
	}

	// Получаем список всех файлов
	$files = array();
	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($documents_dir, RecursiveDirectoryIterator::SKIP_DOTS));
	foreach ($iterator as $file) {
		if ($file->isFile()) {
			$files[] = $file->getPathname();
		}
	}

	// Проверяем, есть ли файлы
	if (empty($files)) {
		wp_die('No documents found for this loan.');
	}

	// Создаем имя для ZIP-файла
	$zip_filename = 'loan_' . $loan_id . '_documents_' . date('Y-m-d_H-i-s') . '.zip';
	$zip_filepath = $upload_dir['basedir'] . '/documents/' . $loan_id . '/' . $zip_filename;

	// Создаем ZIP-архив
	$zip = new ZipArchive();
	if ($zip->open($zip_filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
		foreach ($files as $file) {
			// Добавляем файл в архив, используя относительный путь
			$relative_path = str_replace($documents_dir, '', $file);
			$zip->addFile($file, $relative_path);
		}
		$zip->close();

		// Отправляем заголовки для скачивания файла
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename="' . basename($zip_filepath) . '"');
		header('Content-Length: ' . filesize($zip_filepath));

		// Читаем и выводим содержимое файла
		readfile($zip_filepath);

		// Удаляем временный ZIP-файл после скачивания
		unlink($zip_filepath);

		// Завершаем выполнение скрипта
		exit;
	} else {
		wp_die('Could not create ZIP file.');
	}
}