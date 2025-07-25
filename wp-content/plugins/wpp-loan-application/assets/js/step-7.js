document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loan-form-step-7');
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

        formData.forEach((value, key) => {
            data[key] = value;
        });

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
                alert('Произошла ошибка сети или сервера.');
            });
    });
});