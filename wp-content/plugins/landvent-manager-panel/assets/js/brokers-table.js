jQuery(document).ready(function ($) {

    $('#wpp-open-brokerage-modal').on('click', function (e) {
        e.preventDefault();
        $('#wpp-brokerage-modal').fadeIn();
    });


    // === Кэшируем часто используемые элементы с префиксом br_ ===
    const $br_modal = $('#wpp-brokerage-modal');
    const $br_modalHeader = $br_modal.find('.wpp-modal-header h3');
    const $br_form = $('#wpp-brokerage-form');
    const $br_overlay = $br_modal.find('.wpp-modal-overlay');
    const $br_closeBtn = $br_modal.find('.wpp-modal-close');

    /**
     * Открытие модального окна для редактирования брокера
     */
    $(document).on('click', '.broker-edit', function (e) {
        e.preventDefault();

        const $br_row = $(this).closest('tr');
        const br_rowData = $br_row.data('row_data');

        if (!br_rowData) {
            alert('Ошибка: данные строки не найдены.');
            return;
        }

        // Сохраняем оригинальный заголовок один раз
        if (!$br_modalHeader.attr('data-old')) {
            $br_modalHeader.attr('data-old', $br_modalHeader.text().trim());
        }

        // Меняем заголовок
        $br_modalHeader.text('Edit Broker');

        // Удаляем старое hidden-поле (если есть)
        $br_form.find('input[name="broker_id"]').remove();

        // Заполняем поля формы
        $.each(br_rowData, function (key, value) {
            const $br_input = $br_form.find(`[name="${key}"]`);
            if ($br_input.length) {
                $br_input.val(value);
            }
        });

        // Добавляем ID как hidden поле
        $br_form.append(`<input type="hidden" name="broker_id" value="${br_rowData.id}" />`);

        // Показываем модальное окно
        $br_modal.fadeIn();
    });

    /**
     * Закрытие модального окна
     * Сбрасывает заголовок, очищает форму, удаляет broker_id и data-old
     */
    function br_closeModal() {
        // Восстанавливаем заголовок, если есть data-old
        const br_oldTitle = $br_modalHeader.attr('data-old');
        if (br_oldTitle) {
            // Удаляем только hidden поле с ID
            $br_form.find('input[name="broker_id"]').remove();
            $br_form[0].reset();
            $br_modalHeader.text(br_oldTitle);
            $br_modalHeader.removeAttr('data-old');
        }



        // Скрываем модальное окно
        $br_modal.fadeOut();
    }

    // Закрытие по клику на крестик или оверлей
    $br_closeBtn.add($br_overlay).on('click', function (e) {
        e.preventDefault();
        br_closeModal();
    });

    // Закрытие по клавише ESC
    $(document).on('keyup', function (e) {
        if (e.key === "Escape") {
            br_closeModal();
        }
    });

    // Опционально: закрытие при клике вне формы (но не на самой форме)
    $br_overlay.on('click', function (e) {
        if (e.target === this) { // Клик именно на оверлей, а не на форму
            br_closeModal();
        }
    });


    // AJAX form submission
    $('#wpp-brokerage-form').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.text();

        // Collect form data
        const data = form.serializeArray();
        data.push({
            name: 'action',
            value: 'wpp_save_brokerage'
        });

        // Disable submit button
        submitBtn.text('Saving...').prop('disabled', true);

        $.post(trello_vars.ajax_url, data, function (response) {
            if (response.success) {
                alert('✅ ' + response.data.message); // e.g., "Broker has been successfully added."
                form[0].reset(); // Clear form
                $('#wpp-brokerage-modal').fadeOut(); // Close modal

                // Reload the page after successful save
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