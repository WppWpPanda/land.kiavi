jQuery(document).ready(function($) {


    function updateColumnStats(columnElement, callback) {
        // Находим все карточки в этой колонке
        const $cards = $(columnElement).find('.card');
        const loanCount = $cards.length;

        // Инициализируем суммы
        let totalAmount = 0;
        let totalPayment = 0;

        // Перебираем каждую карточку и суммируем значения
        $cards.each(function() {
            // Суммируем amount-info st-1 (удаляем "$" и "," перед парсингом)
            const amountText = $(this).find('.amount-info .st-1 b').text().replace(/[$,]/g, '');
            const amount = parseFloat(amountText) || 0;
            totalAmount += amount;

            // Суммируем payment-info st-1 (аналогично обрабатываем)
            const paymentText = $(this).find('.payment-info .st-1').text().replace(/[$,]/g, '');
            const payment = parseFloat(paymentText) || 0;
            totalPayment += payment;
        });

        // Обновляем данные в колонке
        $(columnElement).find('.wpp-loan-count').find('b').html(loanCount);
        $(columnElement).find('.wpp-nit b').text('$' + totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2 }));
        $(columnElement).find('.wpp-total b').text('$' + totalPayment.toLocaleString('en-US', { minimumFractionDigits: 2 }));

        // Вызываем callback, если он передан
        if (typeof callback === 'function') {
            callback({
                loanCount: loanCount,
                totalAmount: totalAmount,
                totalPayment: totalPayment
            });
        }
    }

    function totalSum() {
        let sumNitAdv = 0;
        let sumTotal = 0;
        let sumLoanCount = 0;

        $('.colum-header-wrap').each(function () {
            const $column = $(this);

            // Извлекаем текст из тегов <b> и преобразуем в число
            const nitAdvText = $column.find('.wpp-nit b').text().trim();
            const totalText = $column.find('.wpp-total b').text().trim();
            const loanCountText = $column.find('.wpp-loan-count b').text().trim();

            // Парсим числа, удаляя символы "$" и "," (если есть)
            const nitAdv = parseFloat(nitAdvText.replace(/[^0-9.-]+/g, '')) || 0;
            const total = parseFloat(totalText.replace(/[^0-9.-]+/g, '')) || 0;
            const loanCount = parseInt(loanCountText) || 0;

            // Суммируем
            sumNitAdv += nitAdv;
            sumTotal += total;
            sumLoanCount += loanCount;
        });

        // Вставляем суммы в нужные span'ы
        $('.int-adv-tp span').text('$' + sumNitAdv.toLocaleString());
        $('.int-tot-tp span').text('$' + sumTotal.toLocaleString());
        $('.int-loan-count span').text(sumLoanCount);
    }


    $('.column').each(function() {
        updateColumnStats(this);
    });

    totalSum();

    // Инициализация сортируемых элементов
    function initSortables() {
        // Делаем карточки перетаскиваемыми между колонками
        $(".cards-container").sortable({
            connectWith: ".cards-container",
            placeholder: "card-placeholder",
            receive: function(event, ui) {
               // updateCardPosition(ui.item);
            },
            update: function(event, ui) {
                if(ui.item.hasClass('ui-sortable-helper')) return;
                updateCardPosition(ui.item);
                $('.column').each(function() {
                    updateColumnStats(this);
                });
            }
        }).disableSelection();

        // Делаем колонки перетаскиваемыми
        $(".columns-container").sortable({
            items: ".column",
            handle: ".column-header",
            placeholder: "column-placeholder",
            tolerance: "pointer"
        });
    }

    // Первоначальная инициализация
    initSortables();

    // Функция обновления позиции карточки через AJAX
    function updateCardPosition(card) {
        var card_id = card.data('card-id');
        var column_id = card.closest('.column').data('column-id');
        var position = card.index();

        $.post(trello_vars.ajax_url, {
            action: 'update_card_position',
            card_id: card_id,
            column_id: column_id,
            position: position,
            nonce: trello_vars.nonce
        }, function(response) {
            if(!response.success) {
               // alert('Ы');
            }
        });
    }

    // Обработчик добавления новой карточки
   /** $('.trello-board').on('click', '.add-card-btn', function() {
        var column = $(this).closest('.column');
        var card_id = 'new_' + Date.now();
        var card_html = `
            <div class="card" data-card-id="${card_id}">
                <div class="card-content" contenteditable="true">New</div>
            </div>
        `;

        column.find('.cards-container').append(card_html);
        $(".cards-container").sortable("refresh");
    });*/

    // Обработчик добавления новой колонки с диалоговым окном
    $('#add-column-btn').on('click', function() {
        // Создаем модальное окно
        var modalHtml = `
            <div id="trello-column-modal" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;">
                <div style="background:white;padding:20px;border-radius:5px;width:300px;">
                    <input type="text" id="column-name-input" style="width:100%;padding:8px;margin:10px 0;" placeholder="Enter column name">
                    <div class="add-column-footer">
                        <button id="cancel-column-btn">Cancel</button>
                        <button id="confirm-column-btn">Add</button>
                    </div>
                </div>
            </div>
        `;

        $('body').append(modalHtml);

        // Фокусируемся на поле ввода
        $('#column-name-input').focus();

        // Обработчик отмены
        $('#cancel-column-btn').on('click', function() {
            $('#trello-column-modal').remove();
        });

        // Обработчик подтверждения
        $('#confirm-column-btn').on('click', function() {
            var columnName = $('#column-name-input').val().trim();
            if (columnName === '') {
                columnName = 'New Column';
            }

            //createNewColumn(columnName);
            addColumnViaAjax(columnName);
            $('#trello-column-modal').remove();
        });

        // Закрытие по нажатию Enter
        $('#column-name-input').on('keypress', function(e) {
            if (e.which === 13) {
                var columnName = $(this).val().trim();
                if (columnName === '') {
                    columnName = 'New Column';
                }

                //createNewColumn(columnName);
                addColumnViaAjax(columnName);
                $('#trello-column-modal').remove();
            }
        });
    });

    // Функция для добавления колонки через AJAX
    function addColumnViaAjax(columnName) {
        // Получаем текущее количество колонок для определения порядка
        var columnCount = $('.trello-column').length;

        // AJAX запрос
        $.ajax({
            url: trello_vars.ajax_url, // ajaxurl предоставляется WordPress
            type: 'POST',
            data: {
                action: 'add_trello_column',
                title: columnName,
                column_order: columnCount,
                nonce: trello_vars._nonce // Нужно создать и передать nonce
            },
            beforeSend: function() {
                // Показываем индикатор загрузки
                $('#add-column-btn').prop('disabled', true).text('Adding...');
            },
            success: function(response) {
                if (response.success) {
                    // Создаем колонку с полученными данными
                    createNewColumn(response.data.column);
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function(xhr, status, error) {
                alert('AJAX Error: ' + error);
            },
            complete: function() {
                $('#add-column-btn').prop('disabled', false).text('+ Add Column');
            }
        });
    }


    $('.delete-column').on('click', function(e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this column?')) return;

        const column = $(this).closest('.column');
        const columnId = column.data('column-id');

        column.hide();

        // AJAX запрос для удаления
        $.post(trello_vars.ajax_url, {
            action: 'delete_trello_column',
            column_id: columnId,
            security: trello_vars._nonce
        }, function(response) {
            if (response.success) {
                column.remove();
            } else {
                alert('Ошибка: ' + response.data);
            }
        });
    });


    // Функция создания новой колонки
    function createNewColumn(data) {
        var column_id = 'new_col_' + Date.now();
        var column_html = ` 
            <div class="column" data-column-id="${data.id}">
                <div class="column-header">
                    <h3 contenteditable="true">${data.title}</h3>
                    <button class="delete-column" title="Remove Column"></button>
                </div>
                <div class="cards-container"></div>
            </div>
        `;

        var $newColumn = $(column_html);
        $('.trello-board .columns-container').append($newColumn);

        // Инициализируем sortable только для новой колонки
        $newColumn.find('.cards-container').sortable({
            connectWith: ".cards-container",
            placeholder: "card-placeholder",
            receive: function(event, ui) {// updateCardPosition(ui.item);
                 },
            update: function(event, ui) {
                if(ui.item.hasClass('ui-sortable-helper')) return;
                updateCardPosition(ui.item);
            }
        }).disableSelection();

        // Обновляем connectWith для всех существующих контейнеров
        $(".cards-container").sortable("option", "connectWith", ".cards-container");
    }

    // Обработчик удаления карточки
    $('.trello-board').on('click', '.delete-card', function() {
        if(confirm('Удалить эту карточку?')) {
            var card = $(this).closest('.card');
            var card_id = card.data('card-id');

            $.post(trello_vars.ajax_url, {
                action: 'delete_card',
                card_id: card_id,
                nonce: trello_vars.nonce
            }, function() {
                card.remove();
            });
        }
    });
});