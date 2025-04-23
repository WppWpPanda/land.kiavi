document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.repeater-block-field').forEach(repeater => {
        const rowsContainer = repeater.querySelector('.repeater-block-rows');
        const template = repeater.querySelector('.repeater-block-template').innerHTML;
        const addButton = repeater.querySelector('.repeater-block-add');
        const fieldName = repeater.dataset.name;
        const minBlocks = parseInt(repeater.dataset.min) || 0;
        const maxBlocks = parseInt(repeater.dataset.max) || 0;

        // Добавление блока
        addButton.addEventListener('click', function() {
            if (maxBlocks > 0 && rowsContainer.children.length >= maxBlocks) return;

            const newIndex = rowsContainer.children.length;
            const newRow = template.replace(/{{index}}/g, newIndex)
                .replace(/{{name}}/g, fieldName);

            rowsContainer.insertAdjacentHTML('beforeend', newRow);
            updateButtons();
        });

        // Удаление блока
        rowsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('repeater-block-remove')) {
                if (rowsContainer.children.length <= minBlocks) return;

                e.target.closest('.repeater-block-row').remove();
                updateButtons();
            }
        });

        function updateButtons() {
            if (maxBlocks > 0) {
                addButton.disabled = rowsContainer.children.length >= maxBlocks;
            }

            const removeButtons = rowsContainer.querySelectorAll('.repeater-block-remove');
            removeButtons.forEach(btn => {
                btn.disabled = rowsContainer.children.length <= minBlocks;
            });
        }

        updateButtons();
    });
});