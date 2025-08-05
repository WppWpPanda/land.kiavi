jQuery(document).ready(function ($) {

    // === Открытие модального окна при клике на "Add Law Firm" ===
    $('#wpp-open-law-firm-modal').on('click', function (e) {
        e.preventDefault();
        $('#wpp-law-firm-modal').fadeIn();
    });

    // === Кэшируем часто используемые элементы с префиксом lf_ (law firm) ===
    const $lf_modal       = $('#wpp-law-firm-modal');
    const $lf_modalHeader = $lf_modal.find('.wpp-modal-header h3');
    const $lf_form        = $('#wpp-law-firm-form');
    const $lf_overlay     = $lf_modal.find('.wpp-modal-overlay');
    const $lf_closeBtn    = $lf_modal.find('.wpp-modal-close');

    /**
     * Открытие модального окна для редактирования юридической фирмы
     */
    $(document).on('click', '.lf-edit', function (e) {
        e.preventDefault();

        const $lf_row = $(this).closest('tr');
        const lf_rowData = $lf_row.data('row_data');

        if (!lf_rowData) {
            alert('Error: Row data not found.');
            return;
        }

        // Сохраняем оригинальный заголовок один раз
        if (!$lf_modalHeader.attr('data-old')) {
            $lf_modalHeader.attr('data-old', $lf_modalHeader.text().trim());
        }

        // Меняем заголовок
        $lf_modalHeader.text('Edit Law Firm');

        // Удаляем старое hidden-поле (если есть)
        $lf_form.find('input[name="law_firm_id"]').remove();

        // Заполняем поля формы
        $.each(lf_rowData, function (key, value) {
            const $lf_input = $lf_form.find(`[name="${key}"]`);
            if ($lf_input.length) {
                $lf_input.val(value);
            }
        });

        // Добавляем ID как hidden поле
        $lf_form.append(`<input type="hidden" name="law_firm_id" value="${lf_rowData.id}" />`);

        // Показываем модальное окно
        $lf_modal.fadeIn();
    });

    /**
     * Закрытие модального окна
     * Сбрасывает заголовок, очищает форму, удаляет law_firm_id и data-old
     */
    function lf_closeModal() {
        // Восстанавливаем заголовок, если есть data-old
        const lf_oldTitle = $lf_modalHeader.attr('data-old');
        if (lf_oldTitle) {
            $lf_form.find('input[name="law_firm_id"]').remove();
            $lf_form[0].reset();
            $lf_modalHeader.text(lf_oldTitle);
            $lf_modalHeader.removeAttr('data-old');
        }

        // Скрываем модальное окно
        $lf_modal.fadeOut();
    }

    // Закрытие по клику на крестик или оверлей
    $lf_closeBtn.add($lf_overlay).on('click', function (e) {
        e.preventDefault();
        lf_closeModal();
    });

    // Закрытие по клавише ESC
    $(document).on('keyup', function (e) {
        if (e.key === "Escape") {
            lf_closeModal();
        }
    });

    // Закрытие при клике вне формы (но не на самой форме)
    $lf_overlay.on('click', function (e) {
        if (e.target === this) {
            lf_closeModal();
        }
    });

    // === AJAX-отправка формы ===
    $('#wpp-law-firm-form').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.text();

        // Собираем данные формы
        const data = form.serializeArray();
        data.push({
            name: 'action',
            value: 'wpp_save_law_firm'
        });

        // Блокируем кнопку
        submitBtn.text('Saving...').prop('disabled', true);

        $.post(trello_vars.ajax_url, data, function (response) {
            if (response.success) {
                alert('✅ ' + response.data.message); // e.g., "Law firm has been successfully added."
                form[0].reset(); // Очищаем форму
                $lf_modal.fadeOut(); // Закрываем модальное окно

                // Перезагружаем страницу для обновления таблицы
                window.location.reload();
            } else {
                alert('❌ Error: ' + response.data.message);
            }
        }, 'json')
            .fail(function () {
                alert('❌ Connection error. Please try again.');
            })
            .always(function () {
                submitBtn.text(originalText).prop('disabled', false);
            });
    });

});