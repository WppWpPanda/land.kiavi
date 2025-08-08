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
    const $lf_submitBtn   = $lf_form.find('button[type="submit"]'); // Кнопка отправки

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

        // --- Сохраняем оригинальный текст заголовка (если ещё не сохранён) ---
        if (!$lf_modalHeader.attr('data-old')) {
            $lf_modalHeader.attr('data-old', $lf_modalHeader.text().trim());
        }

        // --- Сохраняем оригинальный текст кнопки (если ещё не сохранён) ---
        if (!$lf_submitBtn.attr('data-old')) {
            $lf_submitBtn.attr('data-old', $lf_submitBtn.text().trim());
        }

        // --- Меняем заголовок и текст кнопки ---
        $lf_modalHeader.text('Edit Law Firm');
        $lf_submitBtn.text('Save Changes');

        // Удаляем старый ID
        $lf_form.find('input[name="law_firm_id"]').remove();

        // Заполняем поля формы
        $.each(lf_rowData, function (key, value) {
            const $lf_input = $lf_form.find(`[name="${key}"]`);
            if ($lf_input.length) {
                $lf_input.val(value);
            }
        });

        // Добавляем ID юридической фирмы
        $lf_form.append(`<input type="hidden" name="law_firm_id" value="${lf_rowData.id}" />`);

        // Показываем модальное окно
        $lf_modal.fadeIn();
    });

    /**
     * Закрытие модального окна
     * Восстанавливает заголовок и текст кнопки, сбрасывает форму
     */
    function lf_closeModal() {
        const oldTitle = $lf_modalHeader.attr('data-old');
        const oldBtnText = $lf_submitBtn.attr('data-old');

        // Восстанавливаем заголовок
        if (oldTitle) {
            $lf_modalHeader.text(oldTitle);
            $lf_modalHeader.removeAttr('data-old');
        }

        // Восстанавливаем текст кнопки
        if (oldBtnText) {
            $lf_submitBtn.text(oldBtnText);
            $lf_submitBtn.removeAttr('data-old');
        }

        // Очищаем форму и удаляем ID
        $lf_form.find('input[name="law_firm_id"]').remove();
        $lf_form[0].reset();

        // Скрываем модальное окно
        $lf_modal.fadeOut();
    }

    // Закрытие по крестику или оверлею
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

    // Закрытие при клике на оверлей (но не на форму)
    $lf_overlay.on('click', function (e) {
        if (e.target === this) {
            lf_closeModal();
        }
    });

    // === AJAX-отправка формы ===
    $('#wpp-law-firm-form').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = $lf_submitBtn;
        const originalText = submitBtn.text(); // "Add Law Firm" или "Save Changes"

        const data = form.serializeArray();
        data.push({
            name: 'action',
            value: 'wpp_save_law_firm'
        });

        // Показываем лоадер
        submitBtn.text('Saving...').prop('disabled', true);

        $.post(trello_vars.ajax_url, data, function (response) {
            if (response.success) {
                alert('✅ ' + response.data.message);
                $lf_modal.fadeOut();
                window.location.reload();
            } else {
                alert('❌ Error: ' + response.data.message);
            }
        }, 'json')
            .fail(function () {
                alert('❌ Connection error. Please try again.');
            })
            .always(function () {
                // После завершения — возвращаем "Save Changes" или "Add Law Firm"
                submitBtn.text(originalText).prop('disabled', false);
            });
    });

});