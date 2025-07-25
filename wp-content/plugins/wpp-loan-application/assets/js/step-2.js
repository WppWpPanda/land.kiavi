(function ($) {
    $(document).ready(function () {
        const form = $('#loan-form-step-2');

        if (!form.length) return;

        console.log(`Форма шага ${wppLoanData.currentStep} загружена`);

        // Логика кнопок (button_group)
        $('.wpp-button-group .btn').each(function () {
            const $button = $(this);
            const $group = $button.closest('.wpp-button-group');
            const inputId = $group.data('input-id'); // Получаем ID инпута из data-input-id
            const $input = $('#' + inputId);

            if (!$input.length) {
                console.error(`❌ Не найден hidden input с id="${inputId}"`);
                return;
            }

            $button.on('click', function () {
                // Сбрасываем активные классы
                $group.find('.btn').removeClass('active');

                // Добавляем active текущей кнопке
                $button.addClass('active');

                // Устанавливаем значение в hidden input
                $input.val($button.data('value'));

                console.log(`Значение установлено: ${$input.val()}`);

                // Обновляем видимость условных полей
                handleConditionalFields();
            });
        });

        // Отправка формы
        form.on('submit', function (e) {
            e.preventDefault();

            // Показываем прелоадер
            if (typeof showLoader === 'function') {
                showLoader();
            }

            const formData = new FormData(this);
            const data = {};

            formData.forEach((value, key) => {
                data[key] = value;
            });

            console.log('Данные формы:', data); // ← отладка

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
                .then(response => response.json())
                .then(json => {
                    if (json.success && json.data.redirect) {
                        window.location.href = json.data.redirect;
                    } else {
                        if (typeof hideLoader === 'function') {
                            hideLoader();
                        }

                    }
                })
                .catch(error => {
                    console.error('Fetch ошибка:', error);

                    // Скрываем loader
                    if (typeof hideLoader === 'function') {
                        hideLoader();
                    }

                });
        });
    });
})(jQuery);