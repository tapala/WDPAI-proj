(function () {
    'use strict';

    var STORAGE_KEY = 'roomly-theme';
    var root = document.documentElement;
    var media = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
    var toggles = Array.prototype.slice.call(document.querySelectorAll('[data-theme-toggle]'));

    function storedTheme() {
        try {
            var value = window.localStorage.getItem(STORAGE_KEY);

            return value === 'light' || value === 'dark' ? value : null;
        } catch (error) {
            return null;
        }
    }

    function sync() {
        var isDark = root.dataset.theme === 'dark';

        toggles.forEach(function (toggle) {
            toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            toggle.setAttribute('aria-label', isDark ? toggle.dataset.labelLight : toggle.dataset.labelDark);
        });
    }

    function apply(theme) {
        root.dataset.theme = theme;
        sync();
    }

    toggles.forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var next = root.dataset.theme === 'dark' ? 'light' : 'dark';

            try {
                window.localStorage.setItem(STORAGE_KEY, next);
            } catch (error) {
                // Private mode can block storage; the theme still changes for this page.
            }

            apply(next);
        });
    });

    if (media && typeof media.addEventListener === 'function') {
        media.addEventListener('change', function (event) {
            if (storedTheme() === null) {
                apply(event.matches ? 'dark' : 'light');
            }
        });
    }

    sync();
})();
