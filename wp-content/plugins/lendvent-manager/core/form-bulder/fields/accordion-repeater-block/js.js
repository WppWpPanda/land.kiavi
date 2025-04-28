document.addEventListener('DOMContentLoaded', function() {
    // Обработка аккордеонов
    document.querySelectorAll('.accordion-block-header').forEach(header => {
        header.addEventListener('click', function() {
            const row = this.closest('.accordion-block-row');
            const content = this.nextElementSibling;
            const icon = this.querySelector('.accordion-block-icon');

            row.classList.toggle('is-open');
            content.style.display = row.classList.contains('is-open') ? 'block' : 'none';
        });
    });

    // Обработка добавления/удаления блоков
    document.querySelectorAll('.accordion-block-field').forEach(container => {
        const rowsContainer = container.querySelector('.accordion-block-rows');
        const template = container.querySelector('.accordion-block-template').innerHTML;
        const addButton = container.querySelector('.accordion-block-add');
        const minBlocks = parseInt(container.dataset.min) || 0;
        const maxBlocks = parseInt(container.dataset.max) || 0;

        addButton.addEventListener('click', function() {
            if (maxBlocks > 0 && rowsContainer.children.length >= maxBlocks) return;

            const newIndex = rowsContainer.children.length;
            const newRow = template.replace(/{{index}}/g, newIndex);

            rowsContainer.insertAdjacentHTML('beforeend', newRow);
            updateButtons();

            // Инициализация нового аккордеона
            const newHeader = rowsContainer.lastElementChild.querySelector('.accordion-block-header');
            newHeader.addEventListener('click', function() {
                const row = this.closest('.accordion-block-row');
                const content = this.nextElementSibling;
                row.classList.toggle('is-open');
                content.style.display = row.classList.contains('is-open') ? 'block' : 'none';
            });
        });

        rowsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('accordion-block-remove')) {
                if (rowsContainer.children.length <= minBlocks) return;

                e.target.closest('.accordion-block-row').remove();
                updateButtons();
            }
        });

        function updateButtons() {
            if (maxBlocks > 0) {
                addButton.disabled = rowsContainer.children.length >= maxBlocks;
            }

            const removeButtons = rowsContainer.querySelectorAll('.accordion-block-remove');
            removeButtons.forEach(btn => {
                btn.disabled = rowsContainer.children.length <= minBlocks;
            });
        }

        updateButtons();
    });
});