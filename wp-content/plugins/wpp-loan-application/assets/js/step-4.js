(function ($) {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('loan-form-step-4');

        if (!form) {
            console.warn('❌ Form #loan-form-step-4 not found');
            return;
        }

        console.log(`Step ${wppLoanData.currentStep} form loaded`);

        // Form elements
        const monthlyPaymentsTable = document.querySelector('#monthly-payments-table tbody');
        const loanToCostEl = document.querySelector('[data-name="loan_to_cost_ratio"] .wpp-content-body');
        const afterRepairLtvEl = document.querySelector('[data-name="after_repair_ltv"] .wpp-content-body');
        const qualificationMsgEl = document.querySelector('[data-name="loan_qualification_message"] .wpp-content-body');
        const totalLoanAmountEl = document.querySelector('[data-name="total_loan_amount"] .wpp-content-body');
        const totalLoanAmountSum = document.querySelector('[name="total_loan_amount_sum"]');

        // Error display block
        const errorsWrapEl = document.querySelector('.wpp-errors-wrap');

        // "No Loans Available" message
        let noLoansMessageEl = document.querySelector('.no-loans-available-message');

        /**
         * Parses value from money field
         * Removes all characters except digits and dot
         *
         * @param {string|number} value - Field value
         * @returns {number}
         */
        function parseMoneyValue(value) {
            if (!value || typeof value !== 'string') return 0;

            const cleaned = value.replace(/[^0-9.]/g, '');
            const floatValue = parseFloat(cleaned);

            return isNaN(floatValue) ? 0 : floatValue;
        }

        /**
         * Formats number as USD
         * Example: 150000 → $150,000.00
         *
         * @param {number} value - Amount
         * @returns {string}
         */
        function formatDollar(value) {
            return value.toLocaleString('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        /**
         * Calculates monthly payment (Interest Only)
         *
         * @param {number} principal - Principal amount
         * @param {number} annualRatePercent - Annual rate (%)
         * @returns {number}
         */
        function calculateMonthlyPayment(principal, annualRatePercent) {
            if (!principal || !annualRatePercent) return 0;

            const monthlyRate = annualRatePercent / 100 / 12;
            return principal * monthlyRate;
        }

        /**
         * Returns array of rates based on FICO Score
         *
         * @param {number} ficoScore - FICO score
         * @param {boolean} isRefinanceWithPropOwned - Refinance and property owned >6 months
         * @returns {Array<{months: number, rate: number}>}
         */
        function getRatesByFico(ficoScore, isRefinanceWithPropOwned) {
            ficoScore = parseInt(ficoScore);

            if (isRefinanceWithPropOwned) {
                if (ficoScore < 659) {
                    return [
                        { months: 12, rate: 11.25 },
                        { months: 18, rate: 12.00 },
                        { months: 24, rate: 12.25 }
                    ];
                } else if (ficoScore >= 660 && ficoScore <= 719) {
                    return [
                        { months: 12, rate: 10.75 },
                        { months: 18, rate: 11.50 },
                        { months: 24, rate: 11.75 }
                    ];
                } else if (ficoScore >= 720) {
                    return [
                        { months: 12, rate: 10.25 },
                        { months: 18, rate: 11.00 },
                        { months: 24, rate: 11.25 }
                    ];
                }
            } else {
                if (ficoScore < 659) {
                    return [
                        { months: 12, rate: 10.75 },
                        { months: 18, rate: 11.50 },
                        { months: 24, rate: 11.75 }
                    ];
                } else if (ficoScore >= 660 && ficoScore <= 719) {
                    return [
                        { months: 12, rate: 9.50 },
                        { months: 18, rate: 10.00 },
                        { months: 24, rate: 10.50 }
                    ];
                } else if (ficoScore >= 720) {
                    return [
                        { months: 12, rate: 8.25 },
                        { months: 18, rate: 9.00 },
                        { months: 24, rate: 9.25 }
                    ];
                }
            }

            return [
                { months: 12, rate: 8.25 },
                { months: 18, rate: 9.00 },
                { months: 24, rate: 9.25 }
            ];
        }

        /**
         * Updates rates table or shows errors
         */
        function updateCalculations() {
            // Get form values
            const refinanceSelect = form.querySelector('[name="refinance"]');
            const propOwnedSelect = form.querySelector('[name="prop_owned_6_months"]');
            const rehabCostInput = form.querySelector('[name="rehab_cost"]');
            const purchasePriceInput = form.querySelector('[name="purchase_price"]');
            const afterRepairValueInput = form.querySelector('[name="after_repair_value"]');
            const ficoSelect = form.querySelector('[name="estimated_fico_score"]');

            const refinance = refinanceSelect ? refinanceSelect.value : 'no';
            const propOwned6Months = propOwnedSelect ? propOwnedSelect.value : 'no';
            const rehabCost = rehabCostInput ? parseMoneyValue(rehabCostInput.value) : 0;
            const purchasePrice = purchasePriceInput ? parseMoneyValue(purchasePriceInput.value) : 0;
            const afterRepairValue = afterRepairValueInput ? parseMoneyValue(afterRepairValueInput.value) : 0;
            const selectedFico = ficoSelect ? ficoSelect.value : 'over_780';

            // Calculate FICO
            let ficoScore = 0;
            switch (selectedFico) {
                case 'below_600': ficoScore = 599; break;
                case '600-619': ficoScore = 610; break;
                case '620-639': ficoScore = 630; break;
                case '640-659': ficoScore = 650; break;
                case '660-679': ficoScore = 670; break;
                case '680-699': ficoScore = 690; break;
                case '700-719': ficoScore = 710; break;
                case '720-739': ficoScore = 730; break;
                case '740-759': ficoScore = 750; break;
                case '760-779': ficoScore = 770; break;
                case 'over_780': ficoScore = 780; break;
                default: ficoScore = 780;
            }

            const hasLowFico = ficoScore < 640;

            // Calculate LTC and ARV LTV
            const purchaseLoanAmount = parseMoneyValue(form.querySelector('[name="purchase_loan_amount"]').value);
            const refinanceLoanAmount = parseMoneyValue(form.querySelector('[name="refinance_loan_amount"]').value);

            let totalLoanAmountForPayment = 0;
            let totalLoanAmount = 0;

            if (refinance === 'no' || (refinance === 'yes' && propOwned6Months === 'no')) {
                totalLoanAmountForPayment = purchaseLoanAmount;
                totalLoanAmount = purchaseLoanAmount + rehabCost;
            } else if (refinance === 'yes' && propOwned6Months === 'yes') {
                totalLoanAmountForPayment = refinanceLoanAmount + rehabCost;
                totalLoanAmount = refinanceLoanAmount + rehabCost;
            }

            // Validate conditions
            const minTotalLoanRequired = 100000;
            const hasLowLoanError = totalLoanAmount < minTotalLoanRequired;

            const hasLowRehabError = rehabCost > 0 && rehabCost < 1000;

            const ltc = purchasePrice > 0
                ? ((totalLoanAmount / purchasePrice) * 100).toFixed(2)
                : 0;

            const maxLTC = 90;
            const hasHighLTCError = ltc > maxLTC;

            const arvLtv = afterRepairValue > 0
                ? ((totalLoanAmount / afterRepairValue) * 100).toFixed(2)
                : 0;

            const maxARVLTV = 75;
            const hasHighARVLTVError = arvLtv > maxARVLTV;

            // Update displayed data
            if (loanToCostEl) {
                loanToCostEl.innerHTML = `<p>Loan-to-cost is ${ltc}%.</p>`;
            }

            if (afterRepairLtvEl) {
                afterRepairLtvEl.innerHTML = `<p>After-repair loan-to-value is ${arvLtv}%.</p>`;
            }

            if (totalLoanAmountEl) {
                totalLoanAmountEl.innerHTML = `<p>Total Loan Amount: ${formatDollar(totalLoanAmount)}</p>`;
            }

            if (totalLoanAmountSum && typeof totalLoanAmountSum === 'object') {
                totalLoanAmountSum.value = totalLoanAmount;
            }

            const minLoan = Math.max(20000, purchasePrice * 0.05);
            const maxLoan = Math.min(purchasePrice * 0.75, afterRepairValue * 0.65, 2920000);

            if (qualificationMsgEl) {
                qualificationMsgEl.innerHTML = `<p>You qualify for a loan between ${formatDollar(minLoan)} and ${formatDollar(maxLoan)}.</p>`;
            }

            // Check for errors
            if (errorsWrapEl) {
                let errorHTML = '';
                let hasError = false;

                if (hasLowFico) {
                    errorHTML += `<li>Applicant's FICO score is too low</li>`;
                    hasError = true;
                }

                if (hasLowRehabError) {
                    errorHTML += `<li>Adjust your Rehab Amount. Rehab Cost is ${formatDollar(rehabCost)}, but must be at least $1,000.00.</li>`;
                    hasError = true;
                }

                if (hasLowLoanError) {
                    errorHTML += `<li>Adjust your Initial Loan Amount. Full Loan Amount is ${formatDollar(totalLoanAmount)}, but must be at least $100,000.</li>`;
                    hasError = true;
                }

                if (hasHighLTCError) {
                    errorHTML += `<li>Reduce either Purchase Price or Initial Loan Amount. Loan-To-Cost (LTC) is ${ltc}%, but must be no more than 90%</li>`;
                    hasError = true;
                }

                if (hasHighARVLTVError) {
                    errorHTML += `<li>Either decrease Loan Amount Requested or increase the After Repair Value. After-Repair Loan-to-Value (ARV LTV) is ${arvLtv}%, but must be no more than 75%</li>`;
                    hasError = true;
                }

                if (hasError) {
                    // Show error messages
                    errorsWrapEl.style.display = 'block';
                    errorsWrapEl.innerHTML = `
                        <div class="alert alert-danger">
                            <strong>Please fix the following:</strong>
                            <ul>${errorHTML}</ul>
                        </div>
                    `;

                    // Show "No Loans Available" message if not already shown
                    if (!noLoansMessageEl) {
                        noLoansMessageEl = document.createElement('div');
                        noLoansMessageEl.className = 'alert alert-light bg-white border text-center no-loans-available-message';
                        noLoansMessageEl.innerHTML = `
                            <h3>No Loans Available</h3>
                            <p>Adjust your information to see more</p>
                        `;
                        const tableContainer = monthlyPaymentsTable?.closest('.wpp-field');
                        if (tableContainer) {
                            tableContainer.insertAdjacentElement('beforebegin', noLoansMessageEl);
                            tableContainer.style.display = 'none';
                        }
                    } else {
                        noLoansMessageEl.style.display = 'block';
                        const tableContainer = monthlyPaymentsTable?.closest('.wpp-field');
                        if (tableContainer) {
                            tableContainer.style.display = 'none';
                        }
                    }

                    // Hide rates table
                    if (monthlyPaymentsTable) {
                        monthlyPaymentsTable.closest('.wpp-field').style.display = 'none';
                    }

                } else {
                    // No errors - clear blocks
                    errorsWrapEl.style.display = 'none';
                    errorsWrapEl.innerHTML = '';

                    if (noLoansMessageEl) {
                        noLoansMessageEl.style.display = 'none';
                    }

                    if (monthlyPaymentsTable) {
                        // Clear old table
                        monthlyPaymentsTable.innerHTML = '';

                        // Get rates
                        const rates = getRatesByFico(ficoScore, refinance === 'yes' && propOwned6Months === 'yes');

                        // Add rows
                        rates.forEach(rate => {
                            const monthlyPayment = calculateMonthlyPayment(
                                totalLoanAmountForPayment,
                                rate.rate
                            );

                            const formattedPayment = formatDollar(monthlyPayment.toFixed(2));

                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${rate.months} Months</td>
                                <td>${rate.rate}%</td>
                                <td>${formattedPayment}</td>
                                <td><button type="button" class="btn btn-success choose-button">Choose</button></td>
                            `;
                            monthlyPaymentsTable.appendChild(row);
                        });

                        // Show table
                        const tableContainer = monthlyPaymentsTable.closest('.wpp-field');
                        if (tableContainer) {
                            tableContainer.style.display = 'block';
                        }

                        // Choose button handlers
                        form.querySelectorAll('.choose-button').forEach(button => {

                            button.addEventListener('click', function () {
                                const chosenRow = this.closest('tr');
                                const rateType = chosenRow.cells[0].textContent.trim();
                                const rate = parseFloat(chosenRow.cells[1].textContent);
                                const payments = parseFloat(chosenRow.cells[2].textContent);

                                //monthly_payment
                                // Remove previous hidden fields
                                document.querySelectorAll('input[name="chosen_monthly_payment"], input[name="chosen_rate_type"], input[name="chosen_rate"]').forEach(el => el.remove());

                                // Create new ones
                                const hiddenRateType = document.createElement('input');
                                hiddenRateType.type = 'hidden';
                                hiddenRateType.type = 'text';
                                hiddenRateType.name = 'chosen_rate_type';
                                hiddenRateType.value = rateType;

                                const hiddenRate = document.createElement('input');
                                hiddenRate.type = 'hidden';
                                hiddenRate.type = 'text';
                                hiddenRate.name = 'chosen_rate';
                                hiddenRate.value = rate;

                                const hiddenPayment = document.createElement('input');
                                hiddenRate.type = 'hidden';
                                hiddenPayment.type = 'text';
                                hiddenPayment.name = 'chosen_monthly_payment';
                                hiddenPayment.value = payments;

                                form.appendChild(hiddenRateType);
                                form.appendChild(hiddenRate);
                                form.appendChild(hiddenPayment);

                                // Submit form via AJAX
                                submitForm();
                            });
                        });
                    }
                }
            }
        }

        /**
         * Submits form data via AJAX
         */
        function submitForm() {
            if (typeof showLoader === 'function') {
                showLoader();
            }

            const formData = new FormData(form);
            const data = {};

            formData.forEach((value, key) => {
                // Parse numeric fields
                data[key] = form.querySelector(`[name="${key}"][data-type="money"]`)
                    ? parseMoneyValue(value)
                    : value;
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
                        alert('Error saving data.');

                        if (typeof hideLoader === 'function') {
                            hideLoader();
                        }
                    }
                })
                .catch(error => {
                    if (typeof hideLoader === 'function') {
                        hideLoader();
                    }

                    console.error('🔴 Fetch error:', error);
                    alert('A network or server error occurred.');
                });
        }

        // Form submit handler
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            submitForm();
        });

        // Auto-update on input
        form.querySelectorAll('input[data-type="money"], select').forEach(input => {
            input.addEventListener('input', updateCalculations);
        });

        // Log changes
        form.querySelectorAll('input[data-type="money"], select').forEach(input => {
            input.addEventListener('change', function () {
                const valueInDollars = parseMoneyValue(this.value);
                console.log(`✏️ Field "${this.name}" changed`, {
                    raw: this.value,
                    parsed_dollars: valueInDollars.toFixed(2)
                });
            });
        });

        // Initialize on load
        updateCalculations();
    });
})(jQuery);