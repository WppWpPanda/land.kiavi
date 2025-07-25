/**
 * WPP Field Builder Manager — Admin JavaScript
 *
 * Этот файл содержит логику работы с полями формы в админке WordPress.
 * Поддерживает условное отображение, интерактивность и базовую валидацию.
 *
 * @package WPP_Field_Builder
 * @since 1.0.0
 * @author Your Name <your@email.com>
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        console.log('WPP Field Builder: Admin script loaded.');

        // Инициализация условной логики при загрузке страницы
        handleConditionalFields();

        // Обновление видимости полей при изменении других полей
        $(document).on('change', '.wpp-field input, .wpp-field select, .wpp-field textarea', function () {
            handleConditionalFields();
        });

        /**
         * Обработчик условного отображения полей
         *
         * Поля могут зависеть от значений других полей.
         * Условия передаются через data-condition (JSON-строка).
         */
        function handleConditionalFields() {
            $('.wpp-field').each(function () {
                const field = $(this);
                const conditionData = field.attr('data-condition');

                if (conditionData) {
                    try {
                        const conditions = JSON.parse(conditionData);
                        let show = true;

                        $.each(conditions, function (key, value) {
                            const fieldValue = $(`[name="${key}"]`).val();
                            if (fieldValue !== value) {
                                show = false;
                                return false; // break
                            }
                        });

                        field.toggle(show);
                    } catch (e) {
                        console.error('Ошибка разбора условия:', conditionData);
                    }
                }
            });
        }

        /**
         * Добавляет поддержку Tooltips из Bootstrap
         */
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        /**
         * Пример кастомной валидации в админке
         */
        $('#post').on('submit', function (e) {
            let isValid = true;

            $('.wpp-field [required]').each(function () {
                const value = $(this).val();
                if (!value) {
                    isValid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Пожалуйста, заполните все обязательные поля.');
            }
        });

    });

})(jQuery);