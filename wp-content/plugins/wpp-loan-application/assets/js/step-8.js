document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loan-form-step-8');

    if (!form) {
        console.warn('❌ Форма #loan-form-step-8 не найдена');
        return;
    }

    console.log(`Форма шага ${wppLoanData.currentStep} загружена`);

    // Элементы формы
    const errorsWrapEl = document.querySelector('.wpp-errors-wrap');

    /**
     * Парсит денежное значение, убирая все символы, кроме чисел и точки
     * @param {string|number} value - Значение из поля
     * @returns {number}
     */
    function parseMoneyValue(value) {
        if (!value || typeof value !== 'string') return 0;

        const cleaned = value.replace(/[^0-9.]/g, '');
        const floatValue = parseFloat(cleaned);
        return isNaN(floatValue) ? 0 : floatValue;
    }

    /**
     * Обработчик отправки формы
     */
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Показываем лоадер, если он определён
        if (typeof showLoader === 'function') {
            showLoader();
        }

        // Собираем данные формы
        const formData = new FormData(form);
        const data = {};

        formData.forEach(function (value, key) {
            data[key] = parseMoneyValue(value);
        });

        console.group('📩 Отправляемые данные:');
        console.table(data);
        console.groupEnd();

        // Отправляем данные через AJAX
        fetch(wppLoanData.ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                action: 'wpp_save_step_data',
                security: wppLoanData.nonce,
                step: wppLoanData.currentStep,
                formData: JSON.stringify(data)
            })
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(function (json) {
                if (typeof hideLoader === 'function') {
                    hideLoader();
                }

                if (json.success && json.data.redirect) {
                    window.location.href = json.data.redirect;
                } else {
                    alert('Ошибка при сохранении данных.');
                }
            })
            .catch(function (error) {
                if (typeof hideLoader === 'function') {
                    hideLoader();
                }

                console.error('🔴 Fetch ошибка:', error);

                if (errorsWrapEl) {
                    errorsWrapEl.style.display = 'block';
                    errorsWrapEl.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Произошла ошибка:</strong>
                        <ul><li>Не удалось сохранить данные заявки.</li></ul>
                    </div>
                `;
                } else {
                    alert('Произошла ошибка сети или сервера.');
                }
            });
    });

    /**
     * Автоматическое обновление полей при вводе
     * Для отладки и мониторинга изменений
     */
    form.querySelectorAll('input[data-type="money"], select').forEach(function (input) {
        input.addEventListener('change', function () {
            const valueInDollars = parseMoneyValue(this.value);
            console.log(`✏️ Изменено поле "${this.name}"`, {
                raw: this.value,
                parsed_dollars: valueInDollars.toFixed(2)
            });
        });
    });

    /**
     * Добавляем обработчики для кнопок "Choose"
     * При клике добавляются hidden поля с выбранным типом и ставкой
     */
    function setupRateSelection() {
        const chooseButtons = document.querySelectorAll('.choose-button');

        chooseButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const chosenRow = this.closest('tr');
                const rateType = chosenRow.cells[0].textContent.trim();
                const rate = parseFloat(chosenRow.cells[1].textContent);

                // Удаляем старые hidden поля
                document.querySelectorAll('input[name="chosen_rate_type"], input[name="chosen_rate"]').forEach(function (el) {
                    el.remove();
                });

                // Создаём новые
                const hiddenRateType = document.createElement('input');
                hiddenRateType.type = 'hidden';
                hiddenRateType.name = 'chosen_rate_type';
                hiddenRateType.value = rateType;

                const hiddenRate = document.createElement('input');
                hiddenRate.type = 'hidden';
                hiddenRate.name = 'chosen_rate';
                hiddenRate.value = rate;

                form.appendChild(hiddenRateType);
                form.appendChild(hiddenRate);

                // Отправляем форму
                form.submit();
            });
        });
    }

    // Инициализация выбора ставки
    setupRateSelection();

    // Перезапуск после динамической загрузки таблицы
    form.addEventListener('DOMNodeInserted', function () {
        setupRateSelection();
    });

})();