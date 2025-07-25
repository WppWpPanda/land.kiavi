jQuery(document).ready(function ($) {
    'use strict';

    $('.wpp-wpp_repeater_field').each(function () {
        const container = $(this);
        const template = container.find('script[type="text/html"]').first();
        const innerContainer = container.find('.wpp-repeater-inner');
        const addBtn = container.find('.wpp-repeater-add');
        const max = parseInt(addBtn.data('max')) || 999;

        if (!template.length) {
            return;
        }

        const tmplId = template.attr('id');
        const tmpl = document.getElementById(tmplId);

        if (!tmpl) {
            return;
        }

        // Функция для получения следующего уникального индекса
        function getNextIndex() {
            let maxIndex = -1;
            innerContainer.find('[data-repeater-index]').each(function() {
                const index = parseInt($(this).data('repeater-index'));
                if (!isNaN(index) && index > maxIndex) {
                    maxIndex = index;
                }
            });
            return maxIndex + 1;
        }

        // Обработчик кнопки "Добавить"
        addBtn.on('click', function () {
            const currentCount = innerContainer.children('.wpp-repeater-block').length;

            if (currentCount >= max) {
                return;
            }

            const newIndex = getNextIndex();
            let html = tmpl.innerHTML.replace(/__index__/g, newIndex);

            // Добавляем data-атрибут для отслеживания индекса
            const tempDiv = $('<div>').html(html);
            tempDiv.find('.wpp-repeater-block').attr('data-repeater-index', newIndex);
            html = tempDiv.html();

            innerContainer.append(html);

            // Перезапускаем автозаполнение адреса, если есть
            if (typeof initGoogleAutocompleteFields === 'function') {
                initGoogleAutocompleteFields();
            }
        });

        // Удаление блока
        innerContainer.on('click', '.wpp-repeater-remove', function () {
            $(this).closest('.wpp-repeater-block').remove();
        });

        // Инициализация существующих блоков (если есть)
        innerContainer.children('.wpp-repeater-block').each(function() {
            const existingIndex = $(this).find('input, select, textarea').first().attr('name');
            if (existingIndex) {
                const match = existingIndex.match(/\[(\d+)\]/);
                if (match && match[1]) {
                    $(this).attr('data-repeater-index', match[1]);
                }
            }
        });
    });
});