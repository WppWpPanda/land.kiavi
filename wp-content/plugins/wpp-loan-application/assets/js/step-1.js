(function ($) {
    'use strict';

    $(document).ready(function () {
        const form = $('#loan-form-step-1');
        if (!form.length) return;

        const investmentTypeGroup = $('.wpp-button-group');
        const investmentTypeInput = $('#investment_type');

        // Handle investment type button clicks
        investmentTypeGroup.on('click', '.btn', function () {
            const $button = $(this);

            // Remove active class from all buttons
            investmentTypeGroup.find('.btn').removeClass('active');

            // Add active class to clicked button
            $button.addClass('active');

            // Update hidden input value
            const selectedValue = $button.data('value');

            investmentTypeInput.val(selectedValue);
            form.submit();
        });

        // Handle form submission via AJAX
        form.on('submit', function (e) {
            e.preventDefault();

            // Show loader if function exists
            if (typeof showLoader === 'function') {
                showLoader();
            }

            const formData = new FormData(this);
            const data = {};

            // Convert FormData to plain object
            formData.forEach((value, key) => {
                data[key] = value;
            });

            // Send data via AJAX
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
                        console.error('Error saving data. Please try again.');
                    }
                })
                .catch(error => {
                    if (typeof hideLoader === 'function') {
                        hideLoader();
                    }

                    console.error('A network or server error occurred.');
                });
        });
    });
})(jQuery);