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


(function ($) {
    'use strict';

    let customFeeIndex = 0;

    $(document).ready(function () {

        // Кнопка "Add New Fee"
        $('#add-custom-fee').on('click', function () {
            const baseAmount = 176000; // Должно браться динамически, как wpp_get_total_loan_amount()
            const index = customFeeIndex++;

            const feeHtml = `
            <div class="custom-fee-item mb-2" data-index="${index}">
                <div class="input-group input-group-sm">
                    <input type="text"
                           class="form-control fee-label"
                           placeholder="Fee Name"
                           style="max-width: 150px;"
                           data-index="${index}">

                    <div class="wpp-percent-money-field d-flex align-items-center gap-2">
                        <!-- Поле суммы -->
                        <div class="wpp-money-input input-group input-group-sm">
                            <span class="input-group-text wpp-prefix">$</span>
                            <input type="number"
                                   step="any"
                                   class="form-control money"
                                   data-base-amount="${baseAmount}"
                                   data-linked-field="#custom-fee-${index}-percent"
                                   placeholder="0.00"
                                   data-index="${index}">
                        </div>

                        <span class="mx-1">or</span>

                        <!-- Поле процента -->
                        <div class="wpp-percent-input input-group input-group-sm">
                            <input type="number"
                                   step="any"
                                   class="form-control percent"
                                   id="custom-fee-${index}-percent"
                                   data-base-amount="${baseAmount}"
                                   data-linked-field=".custom-fee-item[data-index='${index}'] .money"
                                   placeholder="0.00"
                                   data-index="${index}">
                            <span class="input-group-text wpp-suffix">%</span>
                        </div>

                        <!-- Кнопка удаления -->
                        <button type="button"
                                class="btn btn-sm btn-outline-danger remove-custom-fee"
                                data-index="${index}">
                            ×
                        </button>
                    </div>
                </div>
            </div>`;

            $('#custom-fees-container').append(feeHtml);
        });

        // Удаление сбора
        $(document).on('click', '.remove-custom-fee', function () {
            $(this).closest('.custom-fee-item').remove();
        });

        // Расчёт: сумма → процент
        $(document).on('input', '.custom-fee-item .money', function () {
            const $input = $(this);
            const index = $input.data('index');
            const $percentInput = $(`.custom-fee-item[data-index="${index}"] .percent`);
            const baseAmount = parseFloat($input.data('base-amount'));
            const moneyValue = parseFloat($input.val()) || 0;

            const percentValue = baseAmount > 0 ? (moneyValue / baseAmount) * 100 : 0;
            $percentInput.val(percentValue.toFixed(2));
        });

        // Расчёт: процент → сумма
        $(document).on('input', '.custom-fee-item .percent', function () {
            const $input = $(this);
            const index = $input.data('index');
            const $moneyInput = $(`.custom-fee-item[data-index="${index}"] .money`);
            const baseAmount = parseFloat($input.data('base-amount'));
            const percentValue = Math.max(0, Math.min(100, parseFloat($input.val()) || 0));

            const moneyValue = baseAmount > 0 ? (percentValue / 100) * baseAmount : 0;
            $moneyInput.val(parseFloat(moneyValue.toFixed(2)));
        });

        // При потере фокуса — подставляем 0.00, если пусто
        $(document).on('blur', '.custom-fee-item input', function () {
            if ($(this).val() === '') {
                $(this).val('0.00');
            }
        });
    });

})(jQuery);