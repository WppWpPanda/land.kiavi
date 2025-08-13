jQuery(document).ready(function ($) {

    // Открытие модального окна "Add Broker"
    $('#wpp-open-brokerage-modal').on('click', function (e) {
        e.preventDefault();
        $('#wpp-brokerage-modal').fadeIn();
    });

    // === Кэшируем часто используемые элементы с префиксом br_ ===
    const $br_modal       = $('#wpp-brokerage-modal');
    const $br_modalHeader = $br_modal.find('.wpp-modal-header h3');
    const $br_form        = $('#wpp-brokerage-form');
    const $br_overlay     = $br_modal.find('.wpp-modal-overlay');
    const $br_closeBtn    = $br_modal.find('.wpp-modal-close');
    const $br_submitBtn   = $br_form.find('button[type="submit"]'); // Кнопка отправки

    /**
     * Открытие модального окна для редактирования брокера
     */
    /**
    $(document).on('click', '.broker-edit', function (e) {
        e.preventDefault();

        const $br_row = $(this).closest('tr');
        const br_rowData = $br_row.data('row_data');

        if (!br_rowData) {
            alert('Ошибка: данные строки не найдены.');
            return;
        }

        // --- Сохраняем оригинальный текст заголовка (если ещё не сохранён) ---
        if (!$br_modalHeader.attr('data-old')) {
            $br_modalHeader.attr('data-old', $br_modalHeader.text().trim());
        }

        // --- Сохраняем оригинальный текст кнопки (если ещё не сохранён) ---
        if (!$br_submitBtn.attr('data-old')) {
            $br_submitBtn.attr('data-old', $br_submitBtn.text().trim());
        }

        // --- Меняем текст заголовка и кнопки ---
        $br_modalHeader.text('Edit Broker');
        $br_submitBtn.text('Save Changes');

        // Удаляем старый ID
        $br_form.find('input[name="broker_id"]').remove();

        // Заполняем форму
        $.each(br_rowData, function (key, value) {
            const $br_input = $br_form.find(`[name="${key}"]`);
            if ($br_input.length) {
                $br_input.val(value);
            }
        });

        // Добавляем ID брокера
        $br_form.append(`<input type="hidden" name="broker_id" value="${br_rowData.id}" />`);

        // Показываем модальное окно
        $br_modal.fadeIn();
    });
    */
    /**
     * Закрытие модального окна
     * Восстанавливает заголовок и текст кнопки, сбрасывает форму
     */
    function br_closeModal() {
        const oldTitle = $br_modalHeader.attr('data-old');
        const oldBtnText = $br_submitBtn.attr('data-old');

        // Восстанавливаем заголовок
        if (oldTitle) {
            $br_modalHeader.text(oldTitle);
            $br_modalHeader.removeAttr('data-old');
        }

        // Восстанавливаем текст кнопки
        if (oldBtnText) {
            $br_submitBtn.text(oldBtnText);
            $br_submitBtn.removeAttr('data-old');
        }

        // Очищаем форму и удаляем ID
        $br_form.find('input[name="broker_id"]').remove();
        $br_form[0].reset();

        // Скрываем модальное окно
        $br_modal.fadeOut();
    }

    // Закрытие по крестику или оверлею
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

    // Закрытие при клике на оверлей (но не на форму)
    $br_overlay.on('click', function (e) {
        if (e.target === this) {
            br_closeModal();
        }
    });

    // === AJAX-отправка формы ===
    $('#wpp-brokerage-form').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = $br_submitBtn;
        const originalText = submitBtn.text(); // "Add Broker" или "Save Changes"

        const data = form.serializeArray();
        data.push({
            name: 'action',
            value: 'wpp_save_brokerage'
        });

        // Показываем лоадер
        submitBtn.text('Saving...').prop('disabled', true);

        $.post(trello_vars.ajax_url, data, function (response) {
            if (response.success) {
                alert('✅ ' + response.data.message);
                $br_modal.fadeOut();
                window.location.reload();
            } else {
                alert('❌ Error: ' + response.data.message);
            }
        }, 'json')
            .fail(function () {
                alert('❌ Connection error. Please try again.');
            })
            .always(function () {
                // После завершения — возвращаем "Save Changes" (или "Add Broker", если форма открыта заново)
                submitBtn.text(originalText).prop('disabled', false);
            });
    });

    $(document).on('click', '.broker-delete', function (e) {
        e.preventDefault(); // Блокируем переход по ссылке

        const $link = $(this);
        const href = $link.attr('href');

        // Извлекаем параметры из URL
        const url = new URL(href);
        const brokerId = url.searchParams.get('id');
        const nonce = url.searchParams.get('broker_nonce');

        if (!brokerId || !nonce) {
            alert('Missing broker ID or security token.');
            return;
        }

        // Показываем лоадер
        const originalText = $link.text();
        $link.text('Deleting...').prop('disabled', true);

        // AJAX-запрос
        $.post(trello_vars.ajax_url, {
            action: 'wpp_delete_broker',
            nonce: nonce,
            broker_id: brokerId
        }, function (response) {
            if (response.success) {
                alert('✅ ' + response.data.message);

                // Удаляем строку из таблицы
                $link.closest('tr').fadeOut(300, function () {
                    $(this).remove();
                });
            } else {
                alert('❌ Error: ' + response.data.message);
            }
        }, 'json')
            .fail(function () {
                alert('❌ Connection error. Please try again.');
            })
            .always(function () {
                $link.text(originalText).prop('disabled', false);
            });
    });

});