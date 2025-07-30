(function ($) {
    'use strict';

    $(document).ready(function () {

        // Обработка изменения поля суммы
        $(document).on('input', '.wpp-percent-money-field .money', function () {
            const $moneyInput = $(this);
            const $percentInput = $($moneyInput.data('linked-field'));
            const baseAmount = parseFloat($moneyInput.data('base-amount'));

            let moneyValue = parseFloat($moneyInput.val().replace(/[^\d.]/g, ''));

            if (isNaN(moneyValue) || moneyValue < 0) {
                moneyValue = 0;
            }

            // Округляем до 2 знаков после запятой
            moneyValue = parseFloat(moneyValue.toFixed(2));
            $moneyInput.val(moneyValue);

            // Расчёт процента
            const percentValue = baseAmount > 0 ? (moneyValue / baseAmount) * 100 : 0;
            console.log('Calculated Percent:', percentValue.toFixed(2)); // Логируем результат
            $percentInput.val(percentValue.toFixed(2));
        });

        // Обработка изменения поля процента
        $(document).on('input', '.wpp-percent-money-field .percent', function () {
            const $percentInput = $(this);
            const $moneyInput = $($percentInput.data('linked-field'));
            const baseAmount = parseFloat($percentInput.data('base-amount'));

            let percentValue = parseFloat($percentInput.val().replace(/[^\d.]/g, ''));

            if (isNaN(percentValue) || percentValue < 0) {
                percentValue = 0;
            }

            // Ограничиваем 100%
            if (percentValue > 100) {
                percentValue = 100;
                $percentInput.val('100.00');
            }

            // Округляем до 2 знаков после запятой
            percentValue = parseFloat(percentValue.toFixed(2));
            $percentInput.val(percentValue);

            // Расчёт суммы
            const moneyValue = baseAmount > 0 ? (percentValue / 100) * baseAmount : 0;
            $moneyInput.val(parseFloat(moneyValue.toFixed(2)));
        });

        // Проверка на допустимые символы
       /* $(document).on('input', '.wpp-percent-money-field input', function () {
            const $input = $(this);
            const value = $input.val();

            // Разрешаем только цифры и точку
            const cleanedValue = value.replace(/[^0-9.]/g, '');
            if (value !== cleanedValue) {
                $input.val(cleanedValue);
            }
        });*/

        // При фокусе — убираем лишние нули (опционально)
        $(document).on('focus', '.wpp-percent-money-field input', function () {
            const $input = $(this);
            if ($input.val() === '0.00') {
                $input.val('');
            }
        });

        // При потере фокуса — устанавливаем 0.00, если пусто
        $(document).on('blur', '.wpp-percent-money-field input', function () {
            const $input = $(this);
            if ($input.val() === '') {
                $input.val('0.00');
            }
        });

        // Обновите обработчик ввода для всех полей
        $(document).on('input', '.wpp-percent-money-field input', function () {
            const $input = $(this);
            const value = $input.val();

            // Разрешаем только цифры и точку
            let cleanedValue = value.replace(/[^0-9.]/g, '');

            // Убедимся, что точка введена только один раз
            if ((cleanedValue.match(/\./g) || []).length > 1) {
                cleanedValue = value.replace(/\.+/, '.'); // Оставляем только одну точку
            }

            if (value !== cleanedValue) {
                $input.val(cleanedValue);
            }
        });
    });

})(jQuery);