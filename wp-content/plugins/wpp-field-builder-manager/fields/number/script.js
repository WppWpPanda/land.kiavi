jQuery(document).ready(function ($) {
    'use strict';

   // console.log('WPP Number Field: Логика запущена');

    $('.wpp-number-input').on('click', '.wpp-number-increment', function () {
        const input = $(this).siblings('input[type="text"]');
        let val = parseInt(input.val()) || 0;
        const step = parseInt(input.data('step')) || 1;
        const max = parseInt(input.data('max'));

        if (isNaN(val)) val = 0;

        if (max && val + step > max) {
           // console.warn(`❌ Максимум (${max}) достигнут`);
            return;
        }

        val += step;
        input.val(val);
       // console.log(`➕ Увеличено до ${val}`);
    });

    $('.wpp-number-input').on('click', '.wpp-number-decrement', function () {
        const input = $(this).siblings('input[type="text"]');
        let val = parseInt(input.val()) || 0;
        const step = parseInt(input.data('step')) || 1;
        const min = parseInt(input.data('min'));

        if (isNaN(val)) val = 0;

        if (min && val - step < min) {
          //  console.warn(`❌ Минимум (${min}) достигнут`);
            return;
        }

        val -= step;
        input.val(val);
       // console.log(`➖ Уменьшено до ${val}`);
    });
});