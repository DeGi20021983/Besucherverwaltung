/* Tagesliste: Drucken-Button (Inline-Skripte sind per Content-Security-Policy gesperrt). */
(function () {
    'use strict';

    var button = document.getElementById('drucken');
    if (button) {
        button.addEventListener('click', function () {
            window.print();
        });
    }
})();
