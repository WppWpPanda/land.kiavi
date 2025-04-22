document.addEventListener('DOMContentLoaded', function() {
    // Можно добавить кастомную логику для radio полей
    console.log('Radio fields initialized');

    // Пример: обработка изменения значения
    document.querySelectorAll('.radio-field input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            console.log('Selected value:', this.value);
        });
    });
});