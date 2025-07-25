(function () {
    function showLoader() {
        const loader = document.getElementById('wpp-loader-screen');
        if (loader) {
            loader.style.display = 'flex';
        }
    }

    function hideLoader() {
        const loader = document.getElementById('wpp-loader-screen');
        if (loader) {
            loader.style.display = 'none';
        }
    }

    // Делаем функции глобальными
    window.showLoader = showLoader;
    window.hideLoader = hideLoader;

    console.log('Прелоадер загружен');
})();