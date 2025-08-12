/**
 * Complete and corrected script for form #loan-form-step-8
 * Connects Google Places Autocomplete to the address_line_1 field
 * Automatically fills: city, state, zip_code
 */

// === 1. DOMContentLoaded handler for form (data saving, buttons, etc.) ===
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loan-form-step-8');

    if (!form) {
        console.warn('❌ Form #loan-form-step-8 not found');
        return;
    }

    console.log(`✅ Step ${wppLoanData.currentStep} form loaded`);

    const errorsWrapEl = document.querySelector('.wpp-errors-wrap');

    /**
     * Parses monetary value, removing all characters except digits and dot
     * @param {string|number} value
     * @returns {number}
     */
    function parseMoneyValue(value) {
        if (!value || typeof value !== 'string') return 0;
        const cleaned = value.replace(/[^0-9.]/g, '');
        const floatValue = parseFloat(cleaned);
        return isNaN(floatValue) ? 0 : floatValue;
    }

    /**
     * Form submission handler
     */
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (typeof showLoader === 'function') {
            showLoader();
        }

        const formData = new FormData(form);
        const data = {};

        formData.forEach(function (value, key) {
            data[key] = parseMoneyValue(value);
        });

        console.group('📩 Sending data:');
        console.table(data);
        console.groupEnd();

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
                    alert('Error saving data.');
                }
            })
            .catch(function (error) {
                if (typeof hideLoader === 'function') {
                    hideLoader();
                }

                console.error('🔴 Fetch error:', error);

                if (errorsWrapEl) {
                    errorsWrapEl.style.display = 'block';
                    errorsWrapEl.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>An error occurred:</strong>
                        <ul><li>Failed to save application data.</li></ul>
                    </div>
                `;
                } else {
                    alert('A network or server error occurred.');
                }
            });
    });

    /**
     * Log field changes (for debugging)
     */
    form.querySelectorAll('input[data-type="money"], select').forEach(function (input) {
        input.addEventListener('change', function () {
            const valueInDollars = parseMoneyValue(this.value);
            console.log(`✏️ Field changed "${this.name}"`, {
                raw: this.value,
                parsed_dollars: valueInDollars.toFixed(2)
            });
        });
    });

    /**
     * Handlers for "Choose" buttons
     */
    function setupRateSelection() {
        const chooseButtons = document.querySelectorAll('.choose-button');

        chooseButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const chosenRow = this.closest('tr');
                const rateType = chosenRow.cells[0].textContent.trim();
                const rate = parseFloat(chosenRow.cells[1].textContent);

                // Remove old hidden fields
                document.querySelectorAll('input[name="chosen_rate_type"], input[name="chosen_rate"]').forEach(function (el) {
                    el.remove();
                });

                // Create new hidden fields
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

                // Submit the form
                form.submit();
            });
        });
    }

    // Initialize on load
    setupRateSelection();

    // Re-initialize if table is inserted dynamically
    form.addEventListener('DOMNodeInserted', function () {
        setupRateSelection();
    });

}); // <-- End of addEventListener
; // ✅ CRITICAL: Semicolon to prevent "is not a function" error

// === 2. Google Places Autocomplete (only after API is loaded) ===
jQuery(function ($) {
    // Wait until Google Maps API is fully loaded
    waitForGoogle(function () {
        const form = document.getElementById('loan-form-step-8');
        if (!form) {
            // console.warn('❌ Form #loan-form-step-8 not found (Google Places)');
            return;
        }

        const $input = $('#loan-form-step-8 #address_line_1');
        if (!$input.length) {
            // console.warn('❌ Field #address_line_1 not found');
            return;
        }

        const input = $input[0];

        try {
            const autocomplete = new google.maps.places.Autocomplete(input, {
                types: ['address'],
                componentRestrictions: { country: 'us' },
                fields: ['address_components', 'formatted_address']
            });

            autocomplete.addListener('place_changed', function () {
                const place = autocomplete.getPlace();

                if (!place.address_components) {
                    // console.warn('⚠️ Failed to get address components:', place);
                    return;
                }

                let streetNumber = '';
                let route = '';
                let city = '';
                let state = '';
                let zip = '';

                // Parse address components
                $.each(place.address_components, function (index, component) {
                    const types = component.types;

                    if (types.includes('street_number')) streetNumber = component.long_name;
                    if (types.includes('route')) route = component.long_name;
                    if (
                        types.includes('locality') ||
                        types.includes('postal_town') ||
                        types.includes('sublocality') ||
                        types.includes('sublocality_level_1')
                    ) city = component.long_name;
                    if (types.includes('administrative_area_level_1')) state = component.short_name;
                    if (types.includes('postal_code')) zip = component.long_name;
                });

                // Assemble street address
                const street = [streetNumber, route].filter(Boolean).join(' ').trim();
                $input.val(street);

                // Fill fields within this form
                const $form = $input.closest('form');
                if (city) $form.find('#city').val(city);
                if (state) $form.find('#state').val(state);
                if (zip) $form.find('#zip_code').val(zip); // ⚠️ zip_code, not zip

                console.log('✅ Google Places: Address successfully parsed', { street, city, state, zip });
            });
        } catch (error) {
            // console.error('❌ Error initializing Google Places:', error);
        }
    });
});

/**
 * Waits for Google Maps API to load
 * @param {Function} callback
 */
function waitForGoogle(callback) {
    if (typeof google !== 'undefined' && google.maps && google.maps.places) {
        callback();
    } else {
        setTimeout(function () {
            waitForGoogle(callback);
        }, 200);
    }
}