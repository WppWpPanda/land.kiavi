document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loan-form-step-6');
    if (!form) return;

    console.log(`Форма шага ${wppLoanData.currentStep} загружена`);

    // Обработчик отправки формы
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (typeof showLoader === 'function') {
            showLoader();
        }

        const formData = new FormData(form);
        const data = {};

        // Собираем все значения из формы
        formData.forEach((value, key) => {
            data[key] = value;
        });

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
                if (typeof hideLoader === 'function') {
                    hideLoader();
                }

                console.error('Fetch ошибка:', error);

            });
    });
});

jQuery(function ($){
    $(document).ready(function () {
        // Проверяем, существует ли нужное поле в нужной форме
        const $input = $('#loan-form-step-6 #address_line_1');
        if (!$input.length) {
            console.warn('Поле address_line_1 в форме шага 6 не найдено');
            return;
        }

        const input = $input[0];

        // Инициализируем Google Places Autocomplete
        const autocomplete = new google.maps.places.Autocomplete(input, {
            types: ['address'],                    // Только адреса
            componentRestrictions: { country: 'us' }, // Только США (можно убрать)
            fields: ['address_components', 'formatted_address']
        });

        // При выборе адреса
        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();

            if (!place.address_components) {
                console.warn('Не удалось получить компоненты адреса:', place);
                return;
            }

            // Переменные для хранения данных
            let streetNumber = '';
            let route = '';
            let city = '';
            let state = '';
            let zip = '';

            // Парсим компоненты
            $.each(place.address_components, function (index, component) {
                const types = component.types;

                if (types.includes('street_number')) streetNumber = component.long_name;
                if (types.includes('route')) route = component.long_name;
                if (
                    types.includes('locality') ||
                    types.includes('postal_town') ||
                    types.includes('sublocality') ||
                    types.includes('sublocality_level_1')
                ) {
                    city = component.long_name;
                }
                if (types.includes('administrative_area_level_1')) {
                    state = component.short_name; // Например, "CA"
                }
                if (types.includes('postal_code')) {
                    zip = component.long_name;
                }
            });

            // Собираем улицу
            const street = [streetNumber, route].filter(Boolean).join(' ').trim();
            $input.val(street);

            // Находим поля в рамках текущей формы
            const $form = $input.closest('form');

            if (city) $form.find('#city').val(city);
            if (state) $form.find('#state').val(state); // select по value
            if (zip) $form.find('#zip').val(zip);

            // Для отладки (можно убрать)
            console.log('Автозаполнение (шаг 6):', { street, city, state, zip });
        });
    });
})