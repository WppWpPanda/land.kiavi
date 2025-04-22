document.addEventListener('DOMContentLoaded', function() {
    console.log('Checkbox fields initialized');

    // Обработка изменения состояния чекбоксов
    document.querySelectorAll('.checkbox-field input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.name.endsWith('[]')) {
                console.log('Checkbox group changed:', this.name, this.value, this.checked);
            } else {
                console.log('Checkbox changed:', this.name, this.checked);
            }
        });
    });

    // Можно добавить кастомную логику, например:
    // - Валидацию обязательных чекбоксов
    // - Ограничение количества выбранных чекбоксов
});