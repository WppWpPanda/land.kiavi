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

        if (!$comp_modalHeader.attr('data-old')) {
            $comp_modalHeader.attr('data-old', $comp_modalHeader.text().trim());
        }

        $comp_modalHeader.text('Edit Company');
        $comp_form.find('input[name="company_id"]').remove();

        $.each(comp_rowData, function (key, value) {
            const $comp_input = $comp_form.find(`[name="${key}"]`);
            if ($comp_input.length) {
                $comp_input.val(value);
            }
        });

        $comp_form.append(`<input type="hidden" name="company_id" value="${comp_rowData.id}" />`);
        $comp_modal.fadeIn();
    });

    /**
     * Закрытие модального окна
     */
    function comp_closeModal() {
        const oldTitle = $comp_modalHeader.attr('data-old');
        if (oldTitle) {
            $comp_form.find('input[name="company_id"]').remove();
            $comp_form[0].reset();
            $comp_modalHeader.text(oldTitle);
            $comp_modalHeader.removeAttr('data-old');
        }
        $comp_modal.fadeOut();
    }

    $comp_closeBtn.add($comp_overlay).on('click', function (e) {
        e.preventDefault();
        comp_closeModal();
    });

    $(document).on('keyup', function (e) {
        if (e.key === "Escape") {
            comp_closeModal();
        }
    });

    $comp_overlay.on('click', function (e) {
        if (e.target === this) {
            comp_closeModal();
        }
    });

    // === AJAX-отправка формы ===
    $('#wpp-title-company-form').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.text();

        const data = form.serializeArray();
        data.push({
            name: 'action',
            value: 'wpp_save_company'
        });

        submitBtn.text('Saving...').prop('disabled', true);

        $.post(trello_vars.ajax_url, data, function (response) {
            if (response.success) {
                alert('✅ ' + response.data.message);
                form[0].reset();
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
                submitBtn.text(originalText).prop('disabled', false);
            });
    });

});