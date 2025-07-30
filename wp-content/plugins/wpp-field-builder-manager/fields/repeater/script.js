jQuery(document).ready(function ($) {
    'use strict';

    $('.wpp-repeater-container').each(function () {
        const container = $(this);
        const template = container.find('script[type="text/html"]').first();
        const innerContainer = container.find('.wpp-repeater-inner');
        const addBtn = container.find('.wpp-repeater-add');
        const max = parseInt(addBtn.data('max')) || 999;
        const min = parseInt(container.data('min')) || 1;

        if (!template.length) {
            return;
        }

        const tmplId = template.attr('id');
        const tmpl = document.getElementById(tmplId);

        if (!tmpl) {
            return;
        }

        // Получаем данные инициализации
        let initData = {};
        const dataScript = container.find('.wpp-repeater-data');
        if (dataScript.length) {
            try {
                initData = JSON.parse(dataScript.text());
            } catch (e) {
                console.error('Error parsing repeater data:', e);
            }
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
            const block = $(this).closest('.wpp-repeater-block');
            const currentCount = innerContainer.children('.wpp-repeater-block').length;

            // Не удаляем, если достигнут минимум
            if (currentCount <= min) {
                return;
            }

            block.remove();
        });

        // Инициализация существующих блоков (если есть)
        // Блоки уже отрендерены PHP, просто добавляем индексы если их нет
        innerContainer.children('.wpp-repeater-block').each(function(index) {
            if (!$(this).data('repeater-index') && $(this).data('repeater-index') !== 0) {
                $(this).attr('data-repeater-index', index);
            }
        });
    });
});