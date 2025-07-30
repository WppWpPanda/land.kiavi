/**
 * JS для WPP_Text_Field
 */

(function ($) {
    'use strict';

    $(document).ready(function () {

        // Email
        $('.wpp-field-type-WPP_Text_Field input[type="email"]').on('input', function () {
            const val = $(this).val();
            // console.log('Email изменён:', val);
        });

        // Телефон
        $('.wpp-field-type-WPP_Text_Field input[type="tel"]').on('input', function () {
            const val = $(this).val();
            // console.log('Телефон изменён:', val);
        });

        // При фокусе на поле money — удаляем $
        $(document).on('focus', '.wpp-field input[data-type="money"]', function () {
            const $input = $(this);
            const val = $input.val().replace(/^\$/, '');
            $input.val(val);
        });

        // При потере фокуса — форматируем
        $(document).on('blur', '.wpp-field input[data-type="money"]', function () {
            const $input = $(this);
            const hasCents = $input.attr('data-has-cents') === 'yes';
            let value = $input.val().replace(/[^0-9.]/g, '');

            if (!value) {
                $input.val('$');
                return;
            }

            let formattedValue;

            if (hasCents) {
                // С центами → $150,000.00
                const num = parseFloat(value);
                if (!isNaN(num)) {
                    formattedValue = new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD'
                    }).format(num / 100); // Делим на 100, если передаем как cents
                }
            } else {
                // Без центов → $150,000
                const num = parseInt(value.replace(/\D/g, ''), 10);
                if (!isNaN(num)) {
                    formattedValue = '$' + num.toLocaleString();
                }
            }

            $input.val(formattedValue || '$');
        });

        // При вводе в money-поле — чистим и форматируем
        $(document).on('input', '.wpp-field input[data-type="money"]', function (e) {
            const $input = $(this);
            const hasCents = $input.attr('data-has-cents') === 'yes';
            let value = $input.val();

            // Если пустое значение — устанавливаем $
            if (!value.trim() && e.type === 'input') {
                $input.val('$');
                return;
            }

            // Чистим от всего, кроме цифр и точки (если есть копейки)
            let cleaned = value.replace(/[^0-9.]/g, '');

            if (hasCents) {
                // С копейками
                if (cleaned.includes('.')) {
                    const [before, after] = cleaned.split('.');
                    cleaned = before + '.' + after.slice(0, 2);
                } else {
                    cleaned += '.00';
                }

                const num = parseFloat(cleaned);
                if (!isNaN(num)) {
                    const formatted = new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD'
                    }).format(num / 100); // делим на 100, если храним в центах
                    $input.val(formatted);
                }
            } else {
                // Без копеек
                cleaned = cleaned.replace(/\D/g, '');
                const num = parseInt(cleaned, 10);
                if (!isNaN(num)) {
                    const formatted = '$' + num.toLocaleString();
                    $input.val(formatted);
                }
            }
        });

        // Инициализация: форматируем начальное значение
        $('.wpp-field[data-type="money"] input').each(function () {
            const $input = $(this);
            const hasCents = $input.attr('data-has-cents') === 'yes';
            let value = $input.val().trim();

            // Если поле пустое - устанавливаем $
            if (!value || value === '$') {
                $input.val('$');
                return;
            }

            // Удаляем все нечисловые символы, кроме точки (для центов)
            let cleanedValue = value.replace(/[^\d.]/g, '');

            // Если значение содержит пробелы (как в "200 000")
            if (value.includes(' ')) {
                cleanedValue = value.replace(/\s/g, ''); // Просто удаляем все пробелы
            }

            // Для полей с центами
            if (hasCents) {
                const num = parseFloat(cleanedValue);
                if (!isNaN(num)) {
                    $input.val(new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD'
                    }).format(num / 100));
                } else {
                    $input.val('$');
                }
            }
            // Для полей без центов
            else {
                const num = parseInt(cleanedValue, 10);
                if (!isNaN(num)) {
                    $input.val('$' + num.toLocaleString('en-US'));
                } else {
                    $input.val('$');
                }
            }
        });

        // Обработка поля percentage
        $(document).on('input', '[data-type="percentage"]', function(e) {
            let $input = $(this);
            let value = $input.val();

            // Удаляем все символы, кроме цифр и точки
            value = value.replace(/[^\d.]/g, '');

            // Удаляем лишние точки (оставляем только первую)
            let dotCount = (value.match(/\./g) || []).length;
            if (dotCount > 1) {
                value = value.substring(0, value.lastIndexOf('.'));
            }

            // Ограничиваем количество знаков после запятой (2 для процентов)
            let parts = value.split('.');
            if (parts.length > 1) {
                parts[1] = parts[1].substring(0, 2);
                value = parts.join('.');
            }

            // Обновляем значение в поле
            $input.val(value);

            // Добавляем символ % при потере фокуса
        }).on('blur', '[data-type="percentage"]', function() {
            let $input = $(this);
            let value = $input.val().trim();

            if (value !== '') {
                // Добавляем % только если его нет
                if (!value.endsWith('%')) {
                    $input.val(value + '%');
                }
            }

            // Удаляем % при получении фокуса для удобства редактирования
        }).on('focus', '[data-type="percentage"]', function() {
            let $input = $(this);
            let value = $input.val().replace('%', '');
            $input.val(value);
        });
    });

})(jQuery);