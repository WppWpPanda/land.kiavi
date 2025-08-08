jQuery(document).ready(function ($) {
    /**
     * Helper: Показать лоадер на кнопке
     * Заменяет текст кнопки на иконку загрузки
     *
     * @param {jQuery} $button - Кнопка, на которой нужно показать лоадер
     * @param {string} originalText - Исходный текст кнопки (сохраняется)
     * @returns {string} - Оригинальный текст кнопки
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
     * Helper: Скрыть лоадер и вернуть текст кнопки
     * @param {jQuery} $button - Кнопка, с которой нужно убрать лоадер
     */
    function hideLoader($button) {
        const originalText = $button.data('original-text') || 'Submit';
        $button
            .prop('disabled', false)
            .html(originalText);
    }

    // --- Открытие модального окна ---
    $('.open-auth-modal').on('click', function (e) {
        if (!$(this).hasClass('logout')) {
            e.preventDefault();
            $('.wpp-auth-modal').fadeIn();
        }
    });

    // --- Закрытие модального окна ---
    $(document).on('click', '.close-modal', function () {
        $('.wpp-auth-modal').fadeOut();
    });

    // 🔥 Закрытие по клику на оверлей (вне контента)
    $(document).on('click', '.wpp-auth-modal', function (e) {
        if ($(e.target).is('.wpp-auth-modal')) {
            $('.wpp-auth-modal').fadeOut();
        }
    });


    // --- Переключение форм через AJAX ---
    $(document).on('click', '[data-form]', function (e) {
        e.preventDefault();
        const formType = $(this).data('form');
        $.post(wppAuthAjax.ajaxUrl, {
            action: 'wpp_auth_load_form',
            security: wppAuthAjax.nonce,
            form_type: formType
        }, function (res) {
            if (res.success) {
                $('.wpp-auth-modal-content').html(res.data.html);
            } else {
                alert(res.data.message);
            }
        });
    });

    // --- Логин ---
    $(document).on('submit', '#wpp-login-form', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"], input[type="submit"]');

        const data = {
            action: 'wpp_auth_login',
            security: wppAuthAjax.nonce,
            username: $('#wpp-username').val(),
            password: $('#wpp-password').val()
        };

        // Показываем лоадер
        showLoader($submitBtn);

        $.post(wppAuthAjax.ajaxUrl, data, function (res) {
            if (res.success) {
                alert(res.data.message);
                window.location.href = res.data.redirect;
            } else {
                $('.wpp-error-message').html('<p>' + res.data.message + '</p>');
            }
        })
            .fail(function () {
                $('.wpp-error-message').html('<p>Network error. Please try again.</p>');
            })
            .always(function () {
                // Всегда убираем лоадер
                hideLoader($submitBtn);
            });
    });

    // --- Регистрация ---
    $(document).on('submit', '#wpp-register-form', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"], input[type="submit"]');

        const data = {
            action: 'wpp_auth_register',
            security: wppAuthAjax.nonce,
            username: $('#wpp-reg-username').val(),
            email: $('#wpp-reg-email').val(),
            password: $('#wpp-reg-password').val()
        };

        showLoader($submitBtn);

        $.post(wppAuthAjax.ajaxUrl, data, function (res) {
            if (res.success) {
                alert(res.data.message);
                window.location.href = res.data.redirect;
            } else {
                let errors = res.data.errors ? res.data.errors.join('<br>') : res.data.message;
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

    // --- Восстановление пароля ---
    $(document).on('submit', '#wpp-forgot-password-form', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"], input[type="submit"]');

        const data = {
            action: 'wpp_auth_forgot_password',
            security: wppAuthAjax.nonce,
            email: $('#wpp-forgot-email').val()
        };

        showLoader($submitBtn);

        $.post(wppAuthAjax.ajaxUrl, data, function (res) {
            if (res.success) {
                alert(res.data.message);
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