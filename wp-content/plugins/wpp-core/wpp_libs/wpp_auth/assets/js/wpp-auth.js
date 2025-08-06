jQuery(document).ready(function ($) {
    // Открытие модального окна
    $('.open-auth-modal').on('click', function (e) {
        e.preventDefault();
        $('.wpp-auth-modal').fadeIn();
    });

    // Закрытие модального окна
    $(document).on('click', '.close-modal', function () {
        $('.wpp-auth-modal').fadeOut();
    });

   // Закрытие модального окна при клике вне окна
  /*  $(document).on('click', function (e) {
        e.preventDefault();
        if ($(e.target).closest('.wpp-auth-modal-content').length === 0) {
            $('.wpp-auth-modal').fadeOut();
        }
    })*/

    // Переключение форм через AJAX
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

    // Логин
    $('#wpp-login-form').on('submit', function (e) {
        e.preventDefault();
        const data = {
            action: 'wpp_auth_login',
            security: wppAuthAjax.nonce,
            username: $('#wpp-username').val(),
            password: $('#wpp-password').val()
        };

        $.post(wppAuthAjax.ajaxUrl, data, function (res) {
            if (res.success) {
                alert(res.data.message);
                window.location.href = res.data.redirect;
            } else {
                $('.wpp-error-message').html('<p>' + res.data.message + '</p>');
            }
        });
    });

    // Регистрация
    $('#wpp-register-form').on('submit', function (e) {
        e.preventDefault();
        const data = {
            action: 'wpp_auth_register',
            security: wppAuthAjax.nonce,
            username: $('#wpp-reg-username').val(),
            email: $('#wpp-reg-email').val(),
            password: $('#wpp-reg-password').val()
        };

        $.post(wppAuthAjax.ajaxUrl, data, function (res) {
            if (res.success) {
                alert(res.data.message);
                window.location.href = res.data.redirect;
            } else {
                let errors = res.data.errors ? res.data.errors.join('<br>') : res.data.message;
                $('.wpp-error-message').html('<p>' + errors + '</p>');
            }
        });
    });

    // Восстановление пароля
    $('#wpp-forgot-password-form').on('submit', function (e) {
        e.preventDefault();
        const data = {
            action: 'wpp_auth_forgot_password',
            security: wppAuthAjax.nonce,
            email: $('#wpp-forgot-email').val()
        };

        $.post(wppAuthAjax.ajaxUrl, data, function (res) {
            if (res.success) {
                alert(res.data.message);
                window.location.href = res.data.redirect;
            } else {
                $('.wpp-error-message').html('<p>' + res.data.message + '</p>');
            }
        });
    });
});