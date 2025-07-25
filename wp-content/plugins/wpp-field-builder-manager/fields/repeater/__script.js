jQuery(document).ready(function ($) {
    'use strict';

   // console.log('WPP Repeater Field: Логика запущена');

    $('.wpp-wpp_repeater_field').each(function () {
        const container = $(this);
        const template = container.find('script[type="text/html"]').first();
        const innerContainer = container.find('.wpp-repeater-inner');
        const addBtn = container.find('.wpp-repeater-add');
        const max = parseInt(addBtn.data('max')) || 999;
        let count = 0;

        if (!template.length) {
         //   console.error('⚠️ Шаблон не найден для repeater');
            return;
        }

        const tmplId = template.attr('id');
        const tmpl = document.getElementById(tmplId);

        if (!tmpl) {
           // console.warn(`⚠️ Шаблон ${tmplId} не найден в DOM`);
            return;
        }

        // Обработчик кнопки "Добавить"
        addBtn.on('click', function () {

            if (count >= max) {
               // console.warn(`❌ Максимум блоков (${max}) достигнут`);
                return;
            }

            const html = tmpl.innerHTML.replace(/__index__/g, count);
            innerContainer.append(html);

           // console.log(`➕ Добавлен новый блок #${count}`);
            count++;

            // Перезапускаем автозаполнение адреса, если есть
            if (typeof initGoogleAutocompleteFields === 'function') {
                initGoogleAutocompleteFields();
            }
        });

        // Удаление блока
        innerContainer.on('click', '.wpp-repeater-remove', function () {
            $(this).closest('.wpp-repeater-block').remove();
           // console.log('❌ Блок удалён из формы');
        });
    });
});