(function ($) {
    'use strict';

    let customFeeIndex = 0;

    $(document).ready(function () {

        //*********************************************//
        // FEE
        //*********************************************//

        // === 1. Восстановление custom fees из loanData ===
        if (typeof loanData !== 'undefined' && Array.isArray(loanData.custom_fees)) {
            loanData.custom_fees.forEach(function (fee) {
                addCustomFee(fee.label, fee.money, fee.percent);
            });
        }

        // === 2. Кнопка "Add New Fee" ===
        $('#add-custom-fee').on('click', function () {
            addCustomFee('', '0.00', '0.00');
        });

        // === 3. Функция добавления нового сбора ===
        function addCustomFee(label = '', money = '0.00', percent = '0.00') {
            const baseAmount = parseFloat(loanData.baseAmount) || 0;
            const index = customFeeIndex++;

            const $container = $('#custom-fees-container');

            const feeHtml = `
            <div class="custom-fee-item mb-2" data-index="${index}">
                <div class="input-group input-group-sm">
                    <!-- Название -->
                    <input type="text"
                           name="custom_fees[${index}][label]"
                           class="form-control fee-label"
                           placeholder="Fee Name"
                           value="${$.escapeHtml(label)}"
                           style="max-width: 150px;"
                           data-index="${index}">

                    <div class="wpp-percent-money-field d-flex align-items-center gap-2 flex-grow-1">
                        <!-- Сумма -->
                        <div class="wpp-money-input input-group input-group-sm">
                            <span class="input-group-text wpp-prefix">$</span>
                            <input type="number"
                                   step="any"
                                   name="custom_fees[${index}][money]"
                                   class="form-control money"
                                   data-base-amount="${baseAmount}"
                                   data-linked-field="#custom-fee-${index}-percent"
                                   value="${$.escapeHtml(money)}"
                                   placeholder="0.00"
                                   data-index="${index}">
                        </div>

                        <span class="mx-1">or</span>

                        <!-- Процент -->
                        <div class="wpp-percent-input input-group input-group-sm">
                            <input type="number"
                                   step="any"
                                   name="custom_fees[${index}][percent]"
                                   id="custom-fee-${index}-percent"
                                   class="form-control percent"
                                   data-base-amount="${baseAmount}"
                                   data-linked-field="[name='custom_fees[${index}][money]']"
                                   value="${$.escapeHtml(percent)}"
                                   placeholder="0.00"
                                   data-index="${index}">
                            <span class="input-group-text wpp-suffix">%</span>
                        </div>

                        <!-- Удалить -->
                        <button type="button"
                                class="btn btn-sm btn-outline-danger remove-custom-fee"
                                data-index="${index}">
                            ×
                        </button>
                    </div>
                </div>
            </div>`;

            $container.append(feeHtml);

            // Запускаем пересчёт, если есть значения
            const $moneyInput = $(`.custom-fee-item[data-index="${index}"] .money`);
            const $percentInput = $(`.custom-fee-item[data-index="${index}"] .percent`);

            // Принудительно обновляем значения
            if (money !== '0.00' && !isNaN(parseFloat(money))) {
                $moneyInput.trigger('input');
            } else if (percent !== '0.00' && !isNaN(parseFloat(percent))) {
                $percentInput.trigger('input');
            }
        }

        // === 4. Удаление сбора ===
        $(document).on('click', '.remove-custom-fee', function () {
            $(this).closest('.custom-fee-item').remove();
        });

        // === 5. Расчёт: money → percent ===
        $(document).on('input', '.custom-fee-item .money', function () {
            const $input = $(this);
            const index = $input.data('index');
            const $percentInput = $(`.custom-fee-item[data-index="${index}"] .percent`);
            const baseAmount = parseFloat($input.data('base-amount'));
            const moneyValue = Math.max(0, parseFloat($input.val()) || 0);

            const percentValue = baseAmount > 0 ? (moneyValue / baseAmount) * 100 : 0;
            $percentInput.val(percentValue.toFixed(2));
        });

        // === 6. Расчёт: percent → money ===
        $(document).on('input', '.custom-fee-item .percent', function () {
            const $input = $(this);
            const index = $input.data('index');
            const $moneyInput = $(`.custom-fee-item[data-index="${index}"] .money`);
            const baseAmount = parseFloat($input.data('base-amount'));
            const percentValue = Math.max(0, Math.min(100, parseFloat($input.val()) || 0));

            const moneyValue = baseAmount > 0 ? (percentValue / 100) * baseAmount : 0;
            $moneyInput.val(parseFloat(moneyValue.toFixed(2)));
        });

        // === 7. Защита от пустых значений ===
        $(document).on('blur', '.custom-fee-item input', function () {
            if ($(this).val() === '' || isNaN($(this).val())) {
                $(this).val('0.00');
                $(this).trigger('input'); // Пересчитываем
            }
        });


        // === Утилита: Экранирование HTML ===
        $.escapeHtml = function (text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };
        //*********************************************//
        // Скачивание
        //*********************************************//

        // Обработка клика на ссылку для скачивания всех документов
        $(document).on('click', '.wpp-download-docs', function (e) {
            e.preventDefault(); // Предотвращаем стандартное поведение ссылки

            // Получаем loan_id из параметра GET в URL
            let urlParams = new URLSearchParams(window.location.search);
            let loanId = urlParams.get('loan');

            // Проверяем, есть ли loan_id
            if (!loanId) {
                alert('Loan ID not found in the URL (e.g., ?loan=123).');
                console.error('wpp-download-docs: Loan ID is missing from the URL parameters.');
                return;
            }

            // Очищаем loanId от недопустимых символов (на всякий случай, хотя intval в PHP тоже поможет)
            loanId = parseInt(loanId, 10);
            if (isNaN(loanId) || loanId <= 0) {
                alert('Invalid Loan ID in the URL.');
                console.error('wpp-download-docs: Invalid Loan ID found in URL:', urlParams.get('loan'));
                return;
            }

            // Создаем временную форму для отправки запроса
            var $form = $('<form>', {
                'action': wpp_ajax.ajax_url, // URL для обработки запроса
                'method': 'POST'
            }).hide(); // Скрываем форму

            // Добавляем скрытые поля с данными
            $form.append($('<input>', {
                'type': 'hidden',
                'name': 'action',
                'value': 'wpp_download_all_documents'
            }));

            $form.append($('<input>', {
                'type': 'hidden',
                'name': 'loan_id',
                'value': loanId
            }));

            $form.append($('<input>', {
                'type': 'hidden',
                'name': 'nonce',
                'value': wpp_ajax.nonce
            }));

            // Добавляем форму в DOM, отправляем и удаляем
            $('body').append($form);
            $form.submit();
            $form.remove();
        });


        //*********************************************//
        // БРОКЕРЫ
        //*********************************************//

        // Открытие модального окна



        //*********************************************//
        // ФИРМЫ
        //*********************************************//

        // Открытие модального окна
        $('#wpp-open-law-firm-modal').on('click', function(e) {
            e.preventDefault();
            $('#wpp-law-firm-modal').fadeIn();
        });


    })


})(jQuery);

