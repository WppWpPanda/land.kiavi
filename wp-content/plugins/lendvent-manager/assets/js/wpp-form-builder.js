jQuery(document).ready(function($) {
    $(document).on('submit', '.wpp-multistep-form form', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $formContainer = $form.closest('.wpp-multistep-form');
        var direction = $(document.activeElement).data('direction');

        // Собираем данные формы
        var formData = {};
        $form.find(':input').each(function() {
            var $input = $(this);
            if ($input.attr('type') === 'checkbox') {
                if ($input.is(':checked')) {
                    if (!formData[$input.attr('name')]) {
                        formData[$input.attr('name')] = [];
                    }
                    formData[$input.attr('name')].push($input.val());
                }
            } else if ($input.attr('type') !== 'submit' && $input.attr('type') !== 'button') {
                formData[$input.attr('name')] = $input.val();
            }
        });

        // AJAX запрос
        $.ajax({
            url: wpp_form_vars.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wpp_form_step',
                nonce: wpp_form_vars.nonce,
                form_data: formData,
                current_step: $form.find('[name="wpp_current_step"]').val(),
                direction: direction
            },
            beforeSend: function() {
                $formContainer.addClass('wpp-loading');
            },
            success: function(response) {
                if (response.success) {
                    $form.html(response.data.content);
                    $form.find('[name="wpp_current_step"]').val(response.data.current_step);

                    // Обновляем прогресс
                    $formContainer.trigger('wpp_step_changed', [response.data]);
                } else {
                    alert('Ошибка: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                alert('Произошла ошибка: ' + error);
            },
            complete: function() {
                $formContainer.removeClass('wpp-loading');
            }
        });
    });

    // Можно добавить обработчики для других событий
    $(document).on('change', '.wpp-multistep-form select, .wpp-multistep-form input[type="radio"]', function() {
        var $form = $(this).closest('form');
        $form.trigger('submit');
    });
});