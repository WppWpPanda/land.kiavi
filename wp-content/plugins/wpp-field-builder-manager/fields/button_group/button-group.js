(function ($) {
    $(document).ready(function () {
        $('.wpp-button-group').each(function () {
            const group = $(this);
            const input = group.find('input[type="hidden"]');
            const selectedValue = input.val();

            if (selectedValue) {
                group.find('.btn').removeClass('active');
                group.find(`[data-value="${selectedValue}"]`).addClass('active');
            }
        });
    });
})(jQuery);