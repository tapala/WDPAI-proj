(function () {
    'use strict';

    var STORAGE_KEY = 'roomly-theme';
    var root = document.documentElement;
    var stored = null;

    try {
        stored = window.localStorage.getItem(STORAGE_KEY);
    } catch (error) {
        stored = null;
    }

    if (stored !== 'light' && stored !== 'dark') {
        stored = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'dark'
            : 'light';
    }

    root.dataset.theme = stored;
    root.classList.add('js');
})();
