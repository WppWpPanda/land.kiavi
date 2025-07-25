(function ($) {
    'use strict';

    function updateAccordionHeaders() {
        $('.wpp-super-accordion').each(function () {
            const $accordion = $(this);
            const headerTemplate = $accordion.attr('data-header');

            if (!headerTemplate) return;

            const fields = $accordion.find('input, select');
            const data = {};

            fields.each(function () {
                const name = $(this).attr('name');
                const value = $(this).val();
                if (name) {
                    data[name] = '<span>' + value + '</span>';
                }
            });

            const $titleEl = $accordion.find('.wpp-super-accordion-header h5');

            const parsedHeader = headerTemplate.replace(/\{([a-zA-Z0-9_]+)\}/g, function (_, key) {
                return data[key] || key;
            });

            if ($titleEl.length && parsedHeader) {
                $titleEl.html( parsedHeader);
            }
        });
    }


// Вызываем при изменении полей внутри аккордеона
    $(document).on('input', '.wpp-super-accordion input, .wpp-super-accordion select', function () {
        updateAccordionHeaders();
    });

    $(document).ready(function () {

        updateAccordionHeaders();

        $('.wpp-super-accordion').on('click', '.wpp-super-accordion-header', function () {

            const accordion = $(this).closest('.wpp-super-accordion');
            const body = accordion.find('.wpp-super-accordion-body');
            const icon = accordion.find('.toggle-icon');

            if (body.is(':visible')) {
                body.slideUp(200);
                icon.html('▶');
                accordion.removeClass('open');
            } else {
                body.slideDown(200);
                icon.html('▼');
                accordion.addClass('open');
            }
        });
        
    });

})(jQuery);