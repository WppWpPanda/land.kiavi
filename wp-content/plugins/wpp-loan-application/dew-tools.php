<?php
/**
 * Простая функция для логирования любых данных в error_log
 * без лишних проверок и преобразований
 *
 * @param mixed $data Данные для логирования
 * @param string $prefix Префикс сообщения (необязательно)
 */
function wpp_log($data, $prefix = '') {
	// Добавляем временную метку
	$message = '[' . current_time('mysql') . ']';

	// Добавляем префикс если указан
	if (!empty($prefix)) {
		$message .= ' [' . $prefix . ']';
	}

	$message .= ' ';

	// Преобразуем данные в читаемый формат
	if (is_null($data)) {
		$message .= 'NULL';
	} elseif (is_bool($data)) {
		$message .= $data ? 'true' : 'false';
	} elseif (is_scalar($data)) {
		$message .= (string)$data;
	} else {
		// Для массивов и объектов используем print_r
		$message .= print_r($data, true);
	}

	// Записываем в лог
	error_log($message);
}