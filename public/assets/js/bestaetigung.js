/* Bestätigungsseite: Bearbeitungslink in die Zwischenablage kopieren. */
(function () {
    'use strict';

    var feld = document.getElementById('bearbeiten-link');
    var button = document.getElementById('link-kopieren');
    var status = document.getElementById('kopier-status');
    if (!feld || !button) {
        return;
    }

    button.hidden = false;
    feld.addEventListener('focus', function () {
        feld.select();
    });

    button.addEventListener('click', function () {
        function erfolg() {
            status.textContent = 'Der Link wurde in die Zwischenablage kopiert.';
        }
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(feld.value).then(erfolg, function () {
                feld.select();
                status.textContent = 'Bitte kopieren Sie den markierten Link manuell (Strg+C).';
            });
        } else {
            feld.select();
            try {
                document.execCommand('copy');
                erfolg();
            } catch (e) {
                status.textContent = 'Bitte kopieren Sie den markierten Link manuell (Strg+C).';
            }
        }
    });
})();
