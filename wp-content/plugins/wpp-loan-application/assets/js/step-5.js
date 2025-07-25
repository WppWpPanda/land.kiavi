/**
 * Loan Application Form - Step 5
 * Handles form submission and validation for the loan application process
 */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loan-form-step-5');

    // Exit if form doesn't exist
    if (!form) {
        console.error('Loan form element not found');
        return;
    }

    console.log(`Step ${wppLoanData.currentStep} form loaded`);

    // Error display container
    const errorsWrapEl = document.querySelector('.wpp-errors-wrap');

    /**
     * Validates form inputs before submission
     * @returns {boolean} True if validation passes, false otherwise
     */
    function validateForm() {
        // Add your validation logic here
        // Return false to prevent submission if validation fails
        return true;
    }

    /**
     * Handles form submission via AJAX
     * @param {Event} e - The submit event
     */
    function handleFormSubmit(e) {
        e.preventDefault();

        // Validate form before submission
        if (!validateForm()) {
            return false;
        }

        // Show loading indicator if available
        if (typeof showLoader === 'function') {
            showLoader();
        }

        // Prepare form data
        const formData = new FormData(form);
        const data = {};

        // Convert FormData to plain object
        formData.forEach((value, key) => {
            data[key] = value;
        });

        // Submit form data via AJAX
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
            .then(handleResponse)
            .catch(handleError);
    }

    /**
     * Handles the AJAX response
     * @param {Response} response - The fetch response
     */
    function handleResponse(response) {
        return response.json().then(json => {
            if (json.success && json.data.redirect) {
                window.location.href = json.data.redirect;
            } else {
                displayError('Error saving form data');
            }
        });
    }

    /**
     * Handles submission errors
     * @param {Error} error - The error object
     */
    function handleError(error) {
        console.error('Form submission error:', error);
        displayError('A network error occurred. Please try again.');
    }

    /**
     * Displays error message to user
     * @param {string} message - The error message
     */
    function displayError(message) {
        // Hide loader if available
        if (typeof hideLoader === 'function') {
            hideLoader();
        }

        // Display error in alert (can be enhanced to show in errorsWrapEl)
        alert(message);
    }

    // Attach submit handler
    form.addEventListener('submit', handleFormSubmit);
});