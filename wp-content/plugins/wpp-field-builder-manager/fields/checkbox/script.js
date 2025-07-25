/**
 * JS for WPP_Checkbox_Field
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
       // console.log('WPP_Checkbox_Field: JS loaded');

        $('.wpp-field-type-WPP_Checkbox_Field input[type="checkbox"]').on('change', function () {
            const isChecked = $(this).is(':checked');
           // console.log('Checkbox изменён:', isChecked ? 'вкл' : 'выкл');
        });
    });

})(jQuery);