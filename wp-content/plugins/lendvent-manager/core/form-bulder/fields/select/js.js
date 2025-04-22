document.addEventListener('DOMContentLoaded', function() {
    // Можно добавить кастомную логику для select полей
    console.log('Select fields initialized');

    // Пример: инициализация select2 если он подключен
    if (typeof jQuery !== 'undefined' && jQuery().select2) {
        jQuery('.select-field select').select2();
    }
});