jQuery(document).ready(function ($) {

    // === Открытие модального окна при клике на "Add Company" ===
    $('#wpp-open-title-company-modal').on('click', function (e) {
        e.preventDefault();
        $('#wpp-title-company-modal').fadeIn();
    });

    // === Кэшируем элементы с префиксом comp_ ===
    const $comp_modal       = $('#wpp-title-company-modal');
    const $comp_modalHeader = $comp_modal.find('.wpp-modal-header h3');
    const $comp_form        = $('#wpp-title-company-form');
    const $comp_overlay     = $comp_modal.find('.wpp-modal-overlay');
    const $comp_closeBtn    = $comp_modal.find('.wpp-modal-close');
    const $comp_submitBtn   = $comp_form.find('button[type="submit"]'); // Кнопка отправки

    /**
     * Открытие модального окна для редактирования компании
     */
    $(document).on('click', '.comp-edit', function (e) {
        e.preventDefault();

        const $comp_row = $(this).closest('tr');
        const comp_rowData = $comp_row.data('row_data');

        if (!comp_rowData) {
            alert('Error: Row data not found.');
            return;
        }

        // --- Сохраняем оригинальный текст заголовка (если ещё не сохранён) ---
        if (!$comp_modalHeader.attr('data-old')) {
            $comp_modalHeader.attr('data-old', $comp_modalHeader.text().trim());
        }

        // --- Сохраняем оригинальный текст кнопки (если ещё не сохранён) ---
        if (!$comp_submitBtn.attr('data-old')) {
            $comp_submitBtn.attr('data-old', $comp_submitBtn.text().trim());
        }

        // --- Меняем заголовок и текст кнопки ---
        $comp_modalHeader.text('Edit Company');
        $comp_submitBtn.text('Save Changes');

        // Удаляем старый ID
        $comp_form.find('input[name="company_id"]').remove();

        // Заполняем форму
        $.each(comp_rowData, function (key, value) {
            const $comp_input = $comp_form.find(`[name="${key}"]`);
            if ($comp_input.length) {
                $comp_input.val(value);
            }
        });

        // Добавляем ID компании
        $comp_form.append(`<input type="hidden" name="company_id" value="${comp_rowData.id}" />`);

        // Показываем модальное окно
        $comp_modal.fadeIn();
    });

    /**
     * Закрытие модального окна
     * Восстанавливает заголовок и текст кнопки, сбрасывает форму
     */
    function comp_closeModal() {
        const oldTitle = $comp_modalHeader.attr('data-old');
        const oldBtnText = $comp_submitBtn.attr('data-old');

        // Восстанавливаем заголовок
        if (oldTitle) {
            $comp_modalHeader.text(oldTitle);
            $comp_modalHeader.removeAttr('data-old');
        }

        // Восстанавливаем текст кнопки
        if (oldBtnText) {
            $comp_submitBtn.text(oldBtnText);
            $comp_submitBtn.removeAttr('data-old');
        }

        // Очищаем форму и удаляем ID
        $comp_form.find('input[name="company_id"]').remove();
        $comp_form[0].reset();

        // Скрываем модальное окно
        $comp_modal.fadeOut();
    }

    // Закрытие по крестику или оверлею
    $comp_closeBtn.add($comp_overlay).on('click', function (e) {
        e.preventDefault();
        comp_closeModal();
    });

    // Закрытие по клавише ESC
    $(document).on('keyup', function (e) {
        if (e.key === "Escape") {
            comp_closeModal();
        }
    });

    // Закрытие при клике на оверлей (но не на форму)
    $comp_overlay.on('click', function (e) {
        if (e.target === this) {
            comp_closeModal();
        }
    });

    // === AJAX-отправка формы ===
    $('#wpp-title-company-form').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = $comp_submitBtn;
        const originalText = submitBtn.text(); // "Add Company" или "Save Changes"

        const data = form.serializeArray();
        data.push({
            name: 'action',
            value: 'wpp_save_company'
        });

        // Показываем лоадер
        submitBtn.text('Saving...').prop('disabled', true);

        $.post(trello_vars.ajax_url, data, function (response) {
            if (response.success) {
                alert('✅ ' + response.data.message);
                $comp_modal.fadeOut();
                window.location.reload();
            } else {
                alert('❌ Error: ' + response.data.message);
            }
        }, 'json')
            .fail(function () {
                alert('❌ Connection error. Please try again.');
            })
            .always(function () {
                // После завершения — возвращаем "Save Changes" или "Add Company"
                submitBtn.text(originalText).prop('disabled', false);
            });
    });

});