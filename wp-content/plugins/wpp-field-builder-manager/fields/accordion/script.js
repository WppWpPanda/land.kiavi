jQuery(document).ready(function ($) {
   // console.log('WPP Accordion Field: Логика аккордеона запущена');

    $('.wpp-field-type-WPP_Accordion_Field').each(function () {
        const accordion = this;

        $(accordion).find('.accordion-button').on('click', function () {
            const isOpen = $(this).attr('aria-expanded') === 'true';
           // console.log(`Accordion "${$(this).text().trim()}" ${isOpen ? 'закрыт' : 'открыт'}`);
        });
    });
});