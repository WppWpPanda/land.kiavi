(function ($) {
    'use strict';

    $(document).ready(function () {
        //console.log('WPP Field Builder: Frontend script loaded.');

        // Инициализация условной логики при загрузке страницы
        handleConditionalFields();

        // Обновление видимости полей при изменении других полей
        $(document).on('input', '.wpp-field input, .wpp-field select', function () {
            handleConditionalFields();
        });

        /**
         * Получаем значение поля по имени
         *
         * @param {string} fieldName - имя поля
         * @returns {string}
         */
        function getFieldValue(fieldName) {
            const $input = $(`[name="${fieldName}"]`);

            if ($input.length === 0) return '';

            // Если поле имеет атрибут disabled → игнорируем его значение
            if ($input.prop('disabled')) {
                return '';
            }

            const value = $input.val();

            // Для money-полей очищаем от лишних символов
            if ($input.closest('.wpp-field').find('[data-type="money"]').length > 0) {
                return value.replace(/[^0-9.]/g, '');
            }

            return value;
        }

        /**
         * Обработчик условного отображения полей
         */
        function handleConditionalFields() {
            const allFields = $('.wpp-field');

            // Проходим по каждому полю и проверяем условия
            allFields.each(function () {
                const field = $(this);
                const conditionData = field.attr('data-condition');
                const compareType = field.attr('data-compare') || '=';

                if (!conditionData) return; // Пропускаем, если нет условия

                try {
                    const conditions = JSON.parse(conditionData);
                    let show = true;

                    $.each(conditions, function (key, expectedValues) {
                        const fieldValue = getFieldValue(key);

                        // Если expectedValues — массив
                        if (Array.isArray(expectedValues)) {
                            const matches = expectedValues.includes(fieldValue);
                            if (compareType === '!=') {
                                show = !matches;
                            } else {
                                show = matches;
                            }
                        } else {
                            // Одиночное значение
                            const matches = String(fieldValue) === String(expectedValues);
                            if (compareType === '!=') {
                                show = !matches;
                            } else {
                                show = matches;
                            }
                        }

                        // Останавливаем цикл, если уже определено, что не показывать
                        if (!show) return false;
                    });

                    // Управляем отображением и состоянием поля
                    if (!show) {
                        field.hide();
                        field.find('input, select').prop('disabled', true); // Блокируем ввод
                    } else {
                        field.show();
                        field.find('input, select').prop('disabled', false); // Разрешаем ввод
                    }

                } catch (e) {
                    console.error('Ошибка условия:', conditionData);
                }
            });
        }

        /**
         * Пример кастомной валидации формы
         */
        $('form.wpp-custom-form').on('submit', function (e) {
            let isValid = true;

            $('.wpp-field [required]').each(function () {
                const value = $(this).val();
                if (!value) {
                    isValid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Пожалуйста, заполните все обязательные поля.');
            }
        });

    });

})(jQuery);