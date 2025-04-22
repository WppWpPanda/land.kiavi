document.addEventListener('DOMContentLoaded', function() {
    // Инициализация радио-кнопок
    const radioGroups = document.querySelectorAll('.radio-button-field');

    radioGroups.forEach(group => {
        const inputs = group.querySelectorAll('.radio-button-input');

        inputs.forEach(input => {
            input.addEventListener('change', function() {
                console.log('Selected:', this.name, this.value);

                // Снимаем активный класс со всех label в группе
                group.querySelectorAll('.radio-button-label').forEach(label => {
                    label.classList.remove('active');
                });

                // Добавляем активный класс к выбранному label
                if (this.checked) {
                    this.nextElementSibling.classList.add('active');
                }
            });

            // Инициализация активного состояния
            if (input.checked) {
                input.nextElementSibling.classList.add('active');
            }
        });
    });
});