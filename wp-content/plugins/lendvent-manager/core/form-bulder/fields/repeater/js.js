document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.repeater-field').forEach(repeater => {
        const rowsContainer = repeater.querySelector('.repeater-rows');
        const template = repeater.querySelector('.repeater-template').innerHTML;
        const addButton = repeater.querySelector('.repeater-add-button');
        const minRows = parseInt(repeater.dataset.minRows) || 0;
        const maxRows = parseInt(repeater.dataset.maxRows) || 0;

        // Добавление строки
        addButton.addEventListener('click', function() {
            if (maxRows > 0 && rowsContainer.children.length >= maxRows) return;

            const newIndex = rowsContainer.children.length;
            const newRow = template.replace(/{{row_index}}/g, newIndex);
            rowsContainer.insertAdjacentHTML('beforeend', newRow);

            updateButtonsState();
        });

        // Удаление строки (делегирование событий)
        rowsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('repeater-remove-button')) {
                if (rowsContainer.children.length <= minRows) return;

                e.target.closest('.repeater-row').remove();
                updateButtonsState();
            }
        });

        // Обновление состояния кнопок
        function updateButtonsState() {
            if (maxRows > 0) {
                addButton.disabled = rowsContainer.children.length >= maxRows;
            }

            const removeButtons = rowsContainer.querySelectorAll('.repeater-remove-button');
            removeButtons.forEach(btn => {
                btn.disabled = rowsContainer.children.length <= minRows;
            });
        }

        updateButtonsState();
    });
});