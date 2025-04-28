document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.accordion-header').forEach(header => {
        header.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            const content = this.nextElementSibling;
            const icon = this.querySelector('.accordion-icon');

            this.setAttribute('aria-expanded', !isExpanded);
            content.hidden = isExpanded;

            // Анимация иконки
            if (icon) {
                icon.style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(90deg)';
            }
        });
    });
});