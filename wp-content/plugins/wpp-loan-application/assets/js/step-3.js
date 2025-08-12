(function ($) {
    $(document).ready(function () {
        const form = $('#loan-form-step-3');

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

        // Отправка формы через fetch()
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
                        alert('Ошибка при сохранении данных.');
                    }
                })
                .catch(error => {
                    console.error('Fetch ошибка:', error);
                    // Скрываем loader
                    if (typeof hideLoader === 'function') {
                        hideLoader();
                    }
                    alert('Произошла ошибка сети или сервера.');
                });
        });


        const input = document.getElementById('address_line_1');

        // Инициализируем Autocomplete
        const autocomplete = new google.maps.places.Autocomplete(input, {
            types: ['address'],
            componentRestrictions: { country: 'us' }, // Только США (можно убрать или изменить)
            fields: ['address_components', 'formatted_address']
        });

        // При выборе адреса
        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();

            if (!place.address_components) {
                console.warn('Не удалось получить компоненты адреса.');
                return;
            }

            // Объект для хранения данных
            let streetNumber = '';
            let route = '';
            let city = '';
            let state = '';
            let zip = '';

            // Парсим компоненты адреса
            $.each(place.address_components, function (index, component) {
                const types = component.types;

                if (types.includes('street_number')) {
                    streetNumber = component.long_name;
                } else if (types.includes('route')) {
                    route = component.long_name;
                } else if (types.includes('locality') || types.includes('sublocality') || types.includes('postal_town')) {
                    city = component.long_name;
                } else if (types.includes('administrative_area_level_1')) {
                    state = component.short_name; // Например, "CA"
                } else if (types.includes('postal_code')) {
                    zip = component.long_name;
                }
            });

            // Собираем полный адрес
            const street = [streetNumber, route].filter(Boolean).join(' ');
            $('#address_line_1').val(street);

            // Заполняем остальные поля
            if (city) $('#city').val(city);
            if (state) $('#state').val(state); // select по value
            if (zip) $('#zip').val(zip);

            // Опционально: очистка address_line_2
            // $('#address_line_2').val('');
        });

    });
})(jQuery);