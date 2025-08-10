jQuery(document).ready(function ($) {
    /**
     * Helper: Show loader on button
     * Replaces button text with a loading spinner and disables it.
     *
     * @param {jQuery} $button - The button element to show the loader on.
     * @returns {string} - The original button text (for restoring later).
     */
    function showLoader($button) {
        const originalText = $button.html();
        $button
            .prop('disabled', true)
            .data('original-text', originalText)
            .html('<span class="wpp-spinner"></span> Loading...');
        return originalText;
    }

    /**
     * Helper: Hide loader and restore original button text
     * Re-enables the button and restores its initial text.
     *
     * @param {jQuery} $button - The button to remove the loader from.
     */
    function hideLoader($button) {
        const originalText = $button.data('original-text') || 'Submit';
        $button
            .prop('disabled', false)
            .html(originalText);
    }

    // --- Open Authentication Modal ---
    // Prevent default if not logout link, and show modal
    $('.open-auth-modal').on('click', function (e) {
        if (!$(this).hasClass('logout')) {
            e.preventDefault();
            $('.wpp-auth-modal').fadeIn();
        }
    });

    // --- Close Modal (by close button) ---
    $(document).on('click', '.close-modal', function () {
        $('.wpp-auth-modal').fadeOut();
    });

    // --- Close Modal (by clicking on overlay) ---
    // Only close if the click target is the modal background (not inner content)
    $(document).on('click', '.wpp-auth-modal', function (e) {
        if ($(e.target).is('.wpp-auth-modal')) {
            $('.wpp-auth-modal').fadeOut();
        }
    });

    // --- Switch Between Forms (Login / Register / Forgot) via AJAX ---
    // Load the requested form dynamically without page reload
    $(document).on('click', '[data-form]', function (e) {
        e.preventDefault();
        const formType = $(this).data('form'); // 'login', 'register', 'forgot'

        $.post(wppAuthAjax.ajaxUrl, {
            action: 'wpp_auth_load_form',
            security: wppAuthAjax.nonce,
            form_type: formType
        }, function (res) {
            if (res.success) {
                $('.wpp-auth-modal-content').html(res.data.html);
            } else {
                alert(res.data.message || 'Failed to load form.');
            }
        }).fail(function () {
            alert('Network error. Could not load the form.');
        });
    });

    // --- Handle Login Form Submission ---
    $(document).on('submit', '#wpp-login-form', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"], input[type="submit"]');

        const username = $('#wpp-username').val().trim();
        const password = $('#wpp-password').val().trim();

        // Clear previous error messages
        $('.wpp-error-message').empty();

        // Validate required fields
        if (!username || !password) {
            $('.wpp-error-message').html('<p>Please fill in all fields.</p>');
            return;
        }

        const data = {
            action: 'wpp_auth_login',
            security: wppAuthAjax.nonce,
            username: username,
            password: password
        };

        // Show loader and disable button
        showLoader($submitBtn);

        $.post(wppAuthAjax.ajaxUrl, data, function (res) {
            if (res.success) {
                console.info(res.data.message);
                window.location.href = res.data.redirect || '/';
            } else {
                const message = res.data?.message || 'Login failed. Please try again.';
                $('.wpp-error-message').html('<p>' + message + '</p>');
            }
        })
            .fail(function () {
                $('.wpp-error-message').html('<p>Network error. Please try again.</p>');
            })
            .always(function () {
                hideLoader($submitBtn);
            });
    });

    // --- Handle Registration Form Submission ---
    $(document).on('submit', '#wpp-register-form', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"], input[type="submit"]');

        const data = {
            action: 'wpp_auth_register',
            security: wppAuthAjax.nonce,
            username: $('#wpp-reg-username').val().trim(),
            email: $('#wpp-reg-email').val().trim(),
            password: $('#wpp-reg-password').val()
        };

        // Clear previous errors
        $('.wpp-error-message').empty();

        // Validate input
        if (!data.username || !data.email || !data.password) {
            $('.wpp-error-message').html('<p>All fields are required.</p>');
            return;
        }

        showLoader($submitBtn);

        $.post(wppAuthAjax.ajaxUrl, data, function (res) {
            if (res.success) {
                console.info(res.data.message);
                window.location.href = res.data.redirect;
            } else {
                // Handle multiple errors or single message
                const errors = res.data.errors ? res.data.errors.join('<br>') : res.data.message;
                $('.wpp-error-message').html('<p>' + errors + '</p>');
            }
        })
            .fail(function () {
                $('.wpp-error-message').html('<p>Network error. Please try again.</p>');
            })
            .always(function () {
                hideLoader($submitBtn);
            });
    });

    // --- Handle Password Recovery (Forgot Password) ---
    $(document).on('submit', '#wpp-forgot-password-form', function (e) {
        e.preventDefault();
        const $submitBtn = $(this).find('button[type="submit"], input[type="submit"]');
        const email = $('#wpp-forgot-email').val().trim();

        // Clear previous messages
        $('.wpp-error-message').empty();

        if (!email) {
            $('.wpp-error-message').html('<p>Please enter your email address.</p>');
            return;
        }

        const data = {
            action: 'wpp_auth_forgot_password',
            security: wppAuthAjax.nonce,
            email: email
        };

        showLoader($submitBtn);

        $.post(wppAuthAjax.ajaxUrl, data, function (res) {
            if (res.success) {
                //alert(res.data.message);
                window.location.href = res.data.redirect;
            } else {
                $('.wpp-error-message').html('<p>' + res.data.message + '</p>');
            }
        })
            .fail(function () {
                $('.wpp-error-message').html('<p>Network error. Please try again.</p>');
            })
            .always(function () {
                hideLoader($submitBtn);
            });
    });
});