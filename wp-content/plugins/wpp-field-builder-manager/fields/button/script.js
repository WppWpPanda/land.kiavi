(function ($) {
    $(document).ready(function () {
        //console.log('Button Group JS загружен');

        // Инициализация всех групп кнопок
        $('.wpp-button-group').each(function () {
            const group = $(this);
            const input = group.find('input[type="hidden"]');
            const buttons = group.find('.btn');
            const currentValue = input.val();

            // Устанавливаем активную кнопку при загрузке
            if (currentValue) {
                buttons.removeClass('active');
                buttons.filter('[data-value="' + currentValue + '"]').addClass('active');
            }

            // Обработчик клика на кнопки
            buttons.on('click', function () {
                buttons.removeClass('active');
                $(this).addClass('active');
                input.val($(this).data('value'));

                //console.log('Выбрано значение:', $(this).data('value'));
            });
        });
    });
})(jQuery);