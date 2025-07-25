/**
 * Textarea Field Script
 *
 * Кастомная логика для текстовой области (при необходимости).
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
       // console.log('WPP Textarea Field: JS loaded.');

        // Пример: автоматическое увеличение высоты textarea
        $('.wpp-field-type-WPP_Textarea_Field textarea').each(function () {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        $('.wpp-field-type-WPP_Textarea_Field textarea').on('input', function () {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });

})(jQuery);