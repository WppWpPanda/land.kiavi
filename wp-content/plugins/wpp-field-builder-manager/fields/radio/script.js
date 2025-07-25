/**
 * JS for WPP_Radio_Field
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
       // console.log('WPP_Radio_Field: JS loaded');

        $('.wpp-field-type-WPP_Radio_Field input[type="radio"]').on('change', function () {
            const selected = $(this).val();
            //console.log('Выбрано значение:', selected);
        });
    });

})(jQuery);