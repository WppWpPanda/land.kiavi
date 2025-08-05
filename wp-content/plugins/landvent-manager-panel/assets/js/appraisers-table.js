jQuery(document).ready(function ($) {

    // === Открытие модального окна при клике на "Add Appraiser" ===
    $('#wpp-open-appraiser-modal').on('click', function (e) {
        e.preventDefault();
        $('#wpp-appraiser-modal').fadeIn();
    });

    // === Кэшируем элементы с префиксом appr_ ===
    const $appr_modal       = $('#wpp-appraiser-modal');
    const $appr_modalHeader = $appr_modal.find('.wpp-modal-header h3');
    const $appr_form        = $('#wpp-appraiser-form');
    const $appr_overlay     = $appr_modal.find('.wpp-modal-overlay');
    const $appr_closeBtn    = $appr_modal.find('.wpp-modal-close');

    /**
     * Открытие модального окна для редактирования оценщика
     */
    $(document).on('click', '.appr-edit', function (e) {
        e.preventDefault();

        const $appr_row = $(this).closest('tr');
        const appr_rowData = $appr_row.data('row_data');

        if (!appr_rowData) {
            alert('Error: Row data not found.');
            return;
        }

        if (!$appr_modalHeader.attr('data-old')) {
            $appr_modalHeader.attr('data-old', $appr_modalHeader.text().trim());
        }

        $appr_modalHeader.text('Edit Appraiser');
        $appr_form.find('input[name="appraiser_id"]').remove();

        $.each(appr_rowData, function (key, value) {
            const $appr_input = $appr_form.find(`[name="${key}"]`);
            if ($appr_input.length) {
                $appr_input.val(value);
            }
        });

        $appr_form.append(`<input type="hidden" name="appraiser_id" value="${appr_rowData.id}" />`);
        $appr_modal.fadeIn();
    });

    /**
     * Закрытие модального окна
     */
    function appr_closeModal() {
        const oldTitle = $appr_modalHeader.attr('data-old');
        if (oldTitle) {
            $appr_form.find('input[name="appraiser_id"]').remove();
            $appr_form[0].reset();
            $appr_modalHeader.text(oldTitle);
            $appr_modalHeader.removeAttr('data-old');
        }
        $appr_modal.fadeOut();
    }

    $appr_closeBtn.add($appr_overlay).on('click', function (e) {
        e.preventDefault();
        appr_closeModal();
    });

    $(document).on('keyup', function (e) {
        if (e.key === "Escape") {
            appr_closeModal();
        }
    });

    $appr_overlay.on('click', function (e) {
        if (e.target === this) {
            appr_closeModal();
        }
    });

    // === AJAX-отправка формы ===
    $('#wpp-appraiser-form').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.text();

        const data = form.serializeArray();
        data.push({
            name: 'action',
            value: 'wpp_save_appraiser'
        });

        submitBtn.text('Saving...').prop('disabled', true);

        $.post(trello_vars.ajax_url, data, function (response) {
            if (response.success) {
                alert('✅ ' + response.data.message);
                form[0].reset();
                $appr_modal.fadeOut();
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