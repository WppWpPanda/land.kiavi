<?php

/**
 * Получает общую сумму кредита по ID кредита.
 *
 * Ищет сумму в двух источниках:
 * 1. Из кэшированных/справочных данных через wpp_get_loan_data_r()
 * 2. Из сырых данных через get_columns_data()
 *
 * Поддерживает форматы вроде: "$1,000.50", "1 000,50", "1000" и т.д.
 * Возвращает число (float) или 0, если сумма не найдена или не является числом.
 *
 * @since 1.0.0
 *
 * @param string|int|null $loan_id ID кредита. Если null — пытается взять из $_GET['loan'].
 * @return float Найденная сумма кредита или 0, если не найдена.
 *
 * @example
 *   wpp_get_total_loan_amount(123);        // → 10000.0
 *   wpp_get_total_loan_amount();           // Берёт из $_GET['loan']
 *   wpp_get_total_loan_amount('invalid');  // → 0
 */
function wpp_get_total_loan_amount( $loan_id = null ) {
	// === 1. Получение и валидация $loan_id ===
	if ( null === $loan_id ) {
		// Пытаемся взять из GET, но безопасно
		$loan_id = filter_input( INPUT_GET, 'loan', FILTER_SANITIZE_STRING );
		if ( ! $loan_id ) {
			return 0.0;
		}
	}

	// Приводим к строке/числу, убираем возможные пробелы
	$loan_id = trim( (string) $loan_id );
	if ( '' === $loan_id ) {
		return 0.0;
	}

	// === 2. Источник 1: wpp_get_loan_data_r ===
	$default = wpp_get_loan_data_r( $loan_id );

	if ( ! empty( $default['total_loan_amount'] ) ) {
		$raw_amount = $default['total_loan_amount'];

		// Очищаем от типичных "мусорных" символов: $, запятые, пробелы, неразрывные пробелы и табы
		$cleaned = preg_replace( '/[^\d.-]+/', '', $raw_amount );

		if ( is_numeric( $cleaned ) && '' !== $cleaned ) {
			$amount = (float) $cleaned;
			if ( $amount >= 0 ) {
				return $amount;
			}
		}
	}

	// === 3. Источник 2: get_columns_data ===
	$data = get_columns_data( $loan_id );

	if (
		! empty( $data[ $loan_id ] ) &&
		! empty( $data[ $loan_id ]['raw_data'] ) &&
		isset( $data[ $loan_id ]['raw_data']->s4_total_loan_amount_sum )
	) {
		$amount = $data[ $loan_id ]['raw_data']->s4_total_loan_amount_sum;

		if ( is_numeric( $amount ) && ( (float) $amount ) >= 0 ) {
			return (float) $amount;
		}
	}

	// === 4. Ничего не найдено → возвращаем 0.0 ===
	return 0.0;
}


/**
 * Форматирует числовое значение в строку цены по стандартам США (USD).
 *
 * Примеры:
 *   1234.56 → $1,234.56
 *   1000    → $1,000.00
 *   -50.25  → -$50.25
 *
 * Поддерживает округление, кастомное количество знаков после запятой и разделители.
 * По умолчанию использует:
 *   - Символ валюты: $
 *   - Разделитель тысяч: запятая (,)
 *   - Разделитель дробной части: точка (.)
 *   - Два знака после запятой
 *
 * @since 1.0.0
 *
 * @param float|int|string $amount Сумма для форматирования. Допустимы числа, строки с числами.
 * @param array            $args   Массив параметров форматирования:
 *                                 - 'decimals'      (int)    Количество знаков после запятой. По умолчанию 2.
 *                                 - 'thousand_sep'  (string) Разделитель тысяч. По умолчанию ','.
 *                                 - 'decimal_sep'   (string) Разделитель дробной части. По умолчанию '.'.
 *
 * @return string Отформатированная строка с ценой в USD (например: "$1,234.56").
 *                Возвращает "$0.00", если входное значение не является числом.
 *
 * @example
 *   echo format_usd_price(1234.56);        // → $1,234.56
 *   echo format_usd_price(1000, ['decimals' => 0]); // → $1,000
 */
function format_usd_price( $amount, $args = [] ) {
	// Установка параметров по умолчанию
	$defaults = [
		'decimals'     => 2,
		'thousand_sep' => ',',
		'decimal_sep'  => '.',
	];

	$args = wp_parse_args( $args, $defaults );

	// Приводим входное значение к числу
	$amount = filter_var( $amount, FILTER_VALIDATE_FLOAT );
	if ( false === $amount ) {
		$amount = 0.0;
	}

	$negative = $amount < 0;
	$amount   = abs( $amount ); // Работаем с положительным числом

	// Определяем количество знаков после запятой
	$decimals = max(0, (int) $args['decimals']);

	// Форматируем число
	$formatted = number_format( $amount, $decimals, $args['decimal_sep'], $args['thousand_sep'] );

	// Добавляем символ доллара слева
	$formatted = '$' . $formatted;

	// Восстанавливаем знак минуса, если было отрицательное число
	if ( $negative ) {
		$formatted = '-' . $formatted;
	}

	return $formatted;
}