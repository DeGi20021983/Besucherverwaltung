/*
 * Anmeldeformular: Personen hinzufügen/entfernen und clientseitige Validierung.
 * Ohne JavaScript funktioniert das Formular ebenfalls (Server übernimmt beides).
 */
(function () {
    'use strict';

    var form = document.getElementById('anmeldeformular');
    if (!form) {
        return;
    }

    var liste = document.getElementById('personen-liste');
    var hinzufuegen = document.getElementById('person-hinzufuegen');
    var zusammenfassung = document.getElementById('fehler-zusammenfassung');

    /* ---------- Personen ---------- */

    function personen() {
        return Array.prototype.slice.call(liste.querySelectorAll('li.person'));
    }

    function nummerieren() {
        var eintraege = personen();
        eintraege.forEach(function (li, i) {
            var input = li.querySelector('input');
            var label = li.querySelector('label');
            var nr = li.querySelector('.person-nr');
            var fehler = li.querySelector('.feld-fehler');
            var button = li.querySelector('.button-entfernen');

            input.id = 'person-' + i;
            label.htmlFor = input.id;
            nr.textContent = String(i + 1);
            fehler.id = 'fehler-personen-' + i;
            input.setAttribute('aria-describedby', fehler.id);
            button.value = 'person_entfernen_' + i;
            button.setAttribute('aria-label', 'Person ' + (i + 1) + ' entfernen');
            button.hidden = eintraege.length < 2;
        });
    }

    function personHinzufuegen() {
        var vorlage = personen()[0];
        var neu = vorlage.cloneNode(true);
        var input = neu.querySelector('input');
        input.value = '';
        input.removeAttribute('aria-invalid');
        neu.classList.remove('hat-fehler');
        var fehler = neu.querySelector('.feld-fehler');
        fehler.textContent = '';
        fehler.hidden = true;
        liste.appendChild(neu);
        nummerieren();
        input.focus();
    }

    function personEntfernen(li) {
        if (personen().length < 2) {
            return;
        }
        var index = personen().indexOf(li);
        li.parentNode.removeChild(li);
        nummerieren();
        var rest = personen();
        rest[Math.min(index, rest.length - 1)].querySelector('input').focus();
    }

    hinzufuegen.addEventListener('click', function (ev) {
        ev.preventDefault();
        personHinzufuegen();
    });

    liste.addEventListener('click', function (ev) {
        var button = ev.target.closest('.button-entfernen');
        if (button) {
            ev.preventDefault();
            personEntfernen(button.closest('li.person'));
        }
    });

    /* ---------- Validierung ---------- */

    function zweistellig(n) {
        return (n < 10 ? '0' : '') + n;
    }

    function heuteIso() {
        var d = new Date();
        return d.getFullYear() + '-' + zweistellig(d.getMonth() + 1) + '-' + zweistellig(d.getDate());
    }

    function jetztUhrzeit() {
        var d = new Date();
        return zweistellig(d.getHours()) + ':' + zweistellig(d.getMinutes());
    }

    function setzeFehler(input, text) {
        var feld = input.closest('.feld');
        var ausgabe = document.getElementById(input.getAttribute('aria-describedby'));
        if (text) {
            input.setAttribute('aria-invalid', 'true');
            feld.classList.add('hat-fehler');
        } else {
            input.removeAttribute('aria-invalid');
            feld.classList.remove('hat-fehler');
        }
        if (ausgabe) {
            ausgabe.textContent = text || '';
            ausgabe.hidden = !text;
        }
    }

    var pflichtMeldungen = {
        einrichtung_name: 'Bitte geben Sie den Namen der Einrichtung an.',
        einrichtung_art: 'Bitte wählen Sie die Art der Einrichtung.',
        datum: 'Bitte geben Sie das Datum des Besuchs an.',
        uhrzeit: 'Bitte geben Sie die Uhrzeit an.',
        betreff: 'Bitte geben Sie einen Betreff an.',
        ansprech_vorname: 'Bitte geben Sie den Vornamen der Ansprechperson an.',
        ansprech_nachname: 'Bitte geben Sie den Nachnamen der Ansprechperson an.'
    };

    function pruefen() {
        var fehlerFelder = [];

        Object.keys(pflichtMeldungen).forEach(function (name) {
            var input = form.elements[name];
            var meldung = input.value.trim() === '' ? pflichtMeldungen[name] : '';
            setzeFehler(input, meldung);
            if (meldung) {
                fehlerFelder.push(input);
            }
        });

        var datum = form.elements.datum;
        var uhrzeit = form.elements.uhrzeit;
        if (datum.value && !/^\d{4}-\d{2}-\d{2}$/.test(datum.value)) {
            setzeFehler(datum, 'Bitte geben Sie ein gültiges Datum an.');
            fehlerFelder.push(datum);
        } else if (datum.value && datum.value < heuteIso()) {
            setzeFehler(datum, 'Das Datum darf nicht in der Vergangenheit liegen.');
            fehlerFelder.push(datum);
        }
        if (uhrzeit.value && !/^([01]\d|2[0-3]):[0-5]\d$/.test(uhrzeit.value)) {
            setzeFehler(uhrzeit, 'Bitte geben Sie die Uhrzeit im Format HH:MM an (z. B. 09:30).');
            fehlerFelder.push(uhrzeit);
        } else if (uhrzeit.value && datum.value === heuteIso() && uhrzeit.value < jetztUhrzeit()) {
            setzeFehler(uhrzeit, 'Die Uhrzeit darf nicht in der Vergangenheit liegen.');
            fehlerFelder.push(uhrzeit);
        }

        personen().forEach(function (li) {
            var input = li.querySelector('input');
            var meldung = input.value.trim() === '' ? 'Bitte geben Sie den Namen der externen Person an.' : '';
            setzeFehler(input, meldung);
            if (meldung) {
                fehlerFelder.push(input);
            }
        });

        return fehlerFelder;
    }

    form.addEventListener('submit', function (ev) {
        var submitter = ev.submitter;
        if (submitter && submitter.value !== 'speichern') {
            return;
        }
        var fehlerFelder = pruefen();
        if (fehlerFelder.length) {
            ev.preventDefault();
            if (zusammenfassung) {
                zusammenfassung.textContent = 'Bitte prüfen Sie Ihre Angaben: ' +
                    (fehlerFelder.length === 1 ? 'Ein Feld ist' : fehlerFelder.length + ' Felder sind') +
                    ' nicht korrekt ausgefüllt.';
                zusammenfassung.hidden = false;
            }
            fehlerFelder[0].focus();
        }
    });

    // Fehlermeldung eines Feldes entfernen, sobald es korrigiert wird
    form.addEventListener('input', function (ev) {
        var input = ev.target;
        if (input.getAttribute('aria-invalid') === 'true' && input.value.trim() !== '') {
            setzeFehler(input, '');
        }
    });

    form.elements.datum.min = heuteIso();
    nummerieren();
})();
