document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.accordion-header').forEach(header => {
        header.addEventListener('click', function() {
            const accordion = this.parentElement;
            const content = this.nextElementSibling;
            const icon = this.querySelector('.accordion-icon');
            const transition = parseInt(accordion.dataset.transition) || 300;
            const iconClosed = accordion.querySelector('.accordion-icon').textContent;
            const iconOpen = accordion.dataset.iconOpen || '▾';

            // Если аккордеон уже анимируется, ничего не делаем
            if (content.classList.contains('is-transitioning')) return;

            // Переключаем состояние
            const isOpening = content.style.display === 'none';

            // Устанавливаем высоту перед анимацией
            if (isOpening) {
                content.style.display = 'block';
                const startHeight = content.offsetHeight;
                content.style.height = '0px';
                content.classList.add('is-transitioning');

                setTimeout(() => {
                    content.style.height = startHeight + 'px';
                }, 10);

                // Меняем иконку
                icon.textContent = iconOpen;
            } else {
                const startHeight = content.offsetHeight;
                content.style.height = startHeight + 'px';
                content.classList.add('is-transitioning');

                setTimeout(() => {
                    content.style.height = '0px';
                }, 10);

                // Меняем иконку
                icon.textContent = iconClosed;
            }

            // Завершение анимации
            setTimeout(() => {
                content.classList.remove('is-transitioning');
                content.style.height = '';
                if (!isOpening) {
                    content.style.display = 'none';
                }
            }, transition);
        });
    });
});