/*
 * Besucheranmeldung – statische Demo für GitHub Pages.
 *
 * Emuliert den PHP-Server im Browser: Anmeldungen liegen im localStorage,
 * Seiten werden per Hash-Routing gerendert. Aufbau, Texte und Prüfregeln
 * entsprechen der echten Anwendung (public/, templates/, src/validierung.php).
 *
 *   #/                     Anmeldeformular (index.php)
 *   #/bestaetigung/<token> Bestätigung mit Bearbeitungslink (bestaetigung.php)
 *   #/bearbeiten/<token>   Ändern oder absagen (bearbeiten.php?token=…)
 *   #/pforte               Tagesliste für die Pforte (pforte.php?key=…)
 */
(function () {
    'use strict';

    var SPEICHER_SCHLUESSEL = 'besucheranmeldung-demo-v1';

    var EINRICHTUNG_ARTEN = { behoerde: 'Behörde', firma: 'Firma' };

    var MAX_LAENGEN = {
        einrichtung_name: 200,
        betreff: 255,
        ansprech_vorname: 100,
        ansprech_nachname: 100,
        person: 200
    };

    var DATENSCHUTZ = [
        '[PLATZHALTER – bitte durch den abgestimmten Datenschutzhinweis Ihrer Einrichtung ersetzen. Absätze werden durch eine Leerzeile getrennt.]',
        'Mit diesem Formular kündigen Sie externe Besucherinnen und Besucher beim Pfortendienst an. Verarbeitet werden die Angaben zur Einrichtung, der Besuchstermin, der Betreff, der Name der Ansprechperson sowie die Namen der externen Personen. Zweck ist ausschließlich die Organisation des Empfangs und die Zutrittskontrolle am Besuchstag.',
        'Die Daten werden 48 Stunden nach dem Besuchstermin automatisch gelöscht. Eine Absage löscht die Anmeldung sofort.',
        'Zum Schutz vor massenhaftem automatisiertem Absenden wird ein nicht umkehrbarer Hashwert Ihrer IP-Adresse mit Zeitstempel gespeichert und ebenfalls nach 48 Stunden gelöscht. Es werden keine Tracking- oder externen Dienste eingesetzt. Ein technisch notwendiges Sitzungs-Cookie schützt das Formular vor Missbrauch.',
        'Verantwortlich: [Name und Anschrift der Einrichtung]. Datenschutzbeauftragte/r: [Kontakt]. Informationen zu Ihren Rechten (Auskunft, Berichtigung, Löschung, Einschränkung, Widerspruch, Beschwerde bei der Aufsichtsbehörde) finden Sie unter [Link oder Ort].'
    ];

    var inhalt = document.getElementById('inhalt');

    /* ================================================================
     * Hilfsfunktionen (entsprechen src/helpers.php)
     * ================================================================ */

    function e(wert) {
        return String(wert == null ? '' : wert)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function zweistellig(n) {
        return (n < 10 ? '0' : '') + n;
    }

    function isoDatum(d) {
        return d.getFullYear() + '-' + zweistellig(d.getMonth() + 1) + '-' + zweistellig(d.getDate());
    }

    function uhrzeit(d) {
        return zweistellig(d.getHours()) + ':' + zweistellig(d.getMinutes());
    }

    function heute() {
        var d = new Date();
        d.setHours(0, 0, 0, 0);
        return d;
    }

    function tagPlus(tage) {
        var d = heute();
        d.setDate(d.getDate() + tage);
        return isoDatum(d);
    }

    /** „2026-10-21 09:30“ → Date */
    function terminDatum(termin) {
        var t = termin.split(/[ T]/);
        var d = t[0].split('-');
        var z = t[1].split(':');
        return new Date(+d[0], +d[1] - 1, +d[2], +z[0], +z[1]);
    }

    function datumKurz(d) {
        return zweistellig(d.getDate()) + '.' + zweistellig(d.getMonth() + 1) + '.' + d.getFullYear();
    }

    function datumLang(d) {
        var tage = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
        var monate = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
        return tage[d.getDay()] + ', ' + d.getDate() + '. ' + monate[d.getMonth()] + ' ' + d.getFullYear();
    }

    function einrichtungArtLabel(art) {
        return EINRICHTUNG_ARTEN[art] || art;
    }

    function zufallsToken() {
        var bytes = new Uint8Array(16);
        (window.crypto || window.msCrypto).getRandomValues(bytes);
        return Array.prototype.map.call(bytes, function (b) {
            return (b < 16 ? '0' : '') + b.toString(16);
        }).join('');
    }

    function textWert(wert) {
        return String(wert == null ? '' : wert).replace(/[\x00-\x1F\x7F]+/g, ' ').replace(/\s+/g, ' ').trim();
    }

    /* ================================================================
     * „Datenbank“ im localStorage (ersetzt MariaDB + src/anmeldungen.php)
     * ================================================================ */

    var fluechtigerSpeicher = null; // Fallback, falls localStorage gesperrt ist

    function demoDaten() {
        // Beispielbesuche relativ zum heutigen Tag, damit die Pforte immer etwas zeigt
        var h = tagPlus(0);
        var m = tagPlus(1);
        var liste = [
            ['Stadtverwaltung Musterstadt', 'behoerde', h + ' 08:30', 'Abstimmung Bauantrag Erweiterung Nordflügel', 'Petra', 'Schmidt', ['Jonas Becker', 'Aylin Demir']],
            ['Elektro Weber GmbH', 'firma', h + ' 09:00', 'Wartung der Brandmeldeanlage', 'Thomas', 'Klein', ['Markus Weber']],
            ['Landesamt für Statistik', 'behoerde', h + ' 10:15', 'Erhebung Beschäftigtenzahlen 2026', 'Sabine', 'Hoffmann', ['Dr. Claudia Neumann']],
            ['IT-Service Nord GmbH', 'firma', h + ' 11:00', 'Austausch der Netzwerk-Switches, Gebäude B', 'Michael', 'Wagner', ['Lukas Schulz', 'Mia Fischer', 'Ben Richter']],
            ['Kreisverwaltung Beispielkreis', 'behoerde', h + ' 13:30', 'Arbeitsschutz-Begehung', 'Anna', 'Krüger', ['Frank Wolf']],
            ['Druckerei Sonnenschein KG', 'firma', h + ' 15:00', 'Abholung Druckvorlagen Jahresbericht', 'Jan', 'Hartmann', ['Sophie Lange', 'Emre Yilmaz']],
            ['Gebäudereinigung Blitzblank', 'firma', m + ' 07:45', 'Angebotsbesichtigung Sonderreinigung', 'Petra', 'Schmidt', ['Olga Nowak']]
        ];
        return liste.map(function (z, i) {
            return {
                id: i + 1,
                token: zufallsToken(),
                einrichtung_name: z[0],
                einrichtung_art: z[1],
                termin: z[2],
                betreff: z[3],
                ansprech_vorname: z[4],
                ansprech_nachname: z[5],
                personen: z[6],
                demo: true
            };
        });
    }

    function neueDatenbank() {
        var anmeldungen = demoDaten();
        return { stand: tagPlus(0), naechsteId: anmeldungen.length + 1, anmeldungen: anmeldungen };
    }

    function datenbankLaden() {
        var db = null;
        try {
            db = JSON.parse(window.localStorage.getItem(SPEICHER_SCHLUESSEL));
        } catch (err) {
            db = fluechtigerSpeicher;
        }
        // An einem neuen Tag die Beispieldaten neu erzeugen, damit die Tagesliste aktuell ist
        if (!db || !db.anmeldungen || db.stand !== tagPlus(0)) {
            db = neueDatenbank();
            datenbankSpeichern(db);
        }
        return db;
    }

    function datenbankSpeichern(db) {
        fluechtigerSpeicher = db;
        try {
            window.localStorage.setItem(SPEICHER_SCHLUESSEL, JSON.stringify(db));
        } catch (err) {
            // Privater Modus o. Ä.: Daten bleiben nur bis zum Neuladen erhalten
        }
    }

    function anmeldungErstellen(f) {
        var db = datenbankLaden();
        var a = formularZuAnmeldung(f);
        a.id = db.naechsteId++;
        a.token = zufallsToken();
        db.anmeldungen.push(a);
        datenbankSpeichern(db);
        return a.token;
    }

    function anmeldungPerToken(token) {
        if (!/^[0-9a-f]{32}$/.test(token || '')) {
            return null;
        }
        var treffer = datenbankLaden().anmeldungen.filter(function (a) {
            return a.token === token;
        });
        return treffer[0] || null;
    }

    function anmeldungAktualisieren(token, f) {
        var db = datenbankLaden();
        db.anmeldungen.forEach(function (a, i) {
            if (a.token === token) {
                var neu = formularZuAnmeldung(f);
                neu.id = a.id;
                neu.token = a.token;
                neu.demo = a.demo;
                db.anmeldungen[i] = neu;
            }
        });
        datenbankSpeichern(db);
    }

    function anmeldungLoeschen(token) {
        var db = datenbankLaden();
        db.anmeldungen = db.anmeldungen.filter(function (a) {
            return a.token !== token;
        });
        datenbankSpeichern(db);
    }

    /** Tagesliste: eine Zeile pro externer Person, sortiert wie die SQL-Abfrage. */
    function besucheAmTag(tagIso) {
        var zeilen = [];
        datenbankLaden().anmeldungen
            .filter(function (a) {
                return a.termin.slice(0, 10) === tagIso;
            })
            .sort(function (x, y) {
                return x.termin.localeCompare(y.termin) ||
                    x.einrichtung_name.localeCompare(y.einrichtung_name, 'de') ||
                    x.id - y.id;
            })
            .forEach(function (a) {
                a.personen.forEach(function (name) {
                    zeilen.push({ a: a, besucher_name: name });
                });
            });
        return zeilen;
    }

    function formularZuAnmeldung(f) {
        return {
            einrichtung_name: f.einrichtung_name,
            einrichtung_art: f.einrichtung_art,
            termin: f.datum + ' ' + f.uhrzeit,
            betreff: f.betreff,
            ansprech_vorname: f.ansprech_vorname,
            ansprech_nachname: f.ansprech_nachname,
            personen: f.personen.slice()
        };
    }

    /* ================================================================
     * Formular einlesen und prüfen (entspricht src/validierung.php)
     * ================================================================ */

    function leeresFormular() {
        return {
            einrichtung_name: '', einrichtung_art: '', datum: '', uhrzeit: '', betreff: '',
            ansprech_vorname: '', ansprech_nachname: '', personen: ['']
        };
    }

    function formularAusAnmeldung(a) {
        return {
            einrichtung_name: a.einrichtung_name,
            einrichtung_art: a.einrichtung_art,
            datum: a.termin.slice(0, 10),
            uhrzeit: a.termin.slice(11, 16),
            betreff: a.betreff,
            ansprech_vorname: a.ansprech_vorname,
            ansprech_nachname: a.ansprech_nachname,
            personen: a.personen.length ? a.personen.slice() : ['']
        };
    }

    function formularAusDom(form) {
        var f = leeresFormular();
        ['einrichtung_name', 'einrichtung_art', 'datum', 'uhrzeit', 'betreff', 'ansprech_vorname', 'ansprech_nachname']
            .forEach(function (feld) {
                f[feld] = textWert(form.elements[feld].value);
            });
        f.personen = Array.prototype.map.call(form.querySelectorAll('input[name="personen[]"]'), function (input) {
            return textWert(input.value);
        });
        if (!f.personen.length) {
            f.personen = [''];
        }
        return f;
    }

    function datumGueltig(datum) {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(datum)) {
            return false;
        }
        var t = datum.split('-');
        var d = new Date(+t[0], +t[1] - 1, +t[2]);
        return isoDatum(d) === datum;
    }

    function uhrzeitFormatGueltig(wert) {
        return /^([01]\d|2[0-3]):[0-5]\d$/.test(wert);
    }

    function laenge(text) {
        return Array.from ? Array.from(text).length : text.length;
    }

    /** @return Object Fehlermeldungen je Feld (Personen: 'personen.N') */
    function formularValidieren(f) {
        var fehler = {};
        var pflicht = {
            einrichtung_name: 'Bitte geben Sie den Namen der Einrichtung an.',
            betreff: 'Bitte geben Sie einen Betreff an.',
            ansprech_vorname: 'Bitte geben Sie den Vornamen der Ansprechperson an.',
            ansprech_nachname: 'Bitte geben Sie den Nachnamen der Ansprechperson an.'
        };
        Object.keys(pflicht).forEach(function (feld) {
            if (f[feld] === '') {
                fehler[feld] = pflicht[feld];
            } else if (laenge(f[feld]) > MAX_LAENGEN[feld]) {
                fehler[feld] = 'Bitte höchstens ' + MAX_LAENGEN[feld] + ' Zeichen eingeben.';
            }
        });

        if (f.einrichtung_art === '') {
            fehler.einrichtung_art = 'Bitte wählen Sie die Art der Einrichtung.';
        } else if (!EINRICHTUNG_ARTEN.hasOwnProperty(f.einrichtung_art)) {
            fehler.einrichtung_art = 'Bitte wählen Sie „Behörde“ oder „Firma“.';
        }

        var datumOk = false;
        if (f.datum === '') {
            fehler.datum = 'Bitte geben Sie das Datum des Besuchs an.';
        } else if (!datumGueltig(f.datum)) {
            fehler.datum = 'Bitte geben Sie ein gültiges Datum an.';
        } else if (f.datum < tagPlus(0)) {
            fehler.datum = 'Das Datum darf nicht in der Vergangenheit liegen.';
        } else {
            datumOk = true;
        }

        if (f.uhrzeit === '') {
            fehler.uhrzeit = 'Bitte geben Sie die Uhrzeit an.';
        } else if (!uhrzeitFormatGueltig(f.uhrzeit)) {
            fehler.uhrzeit = 'Bitte geben Sie die Uhrzeit im Format HH:MM an (z. B. 09:30).';
        } else if (datumOk && f.datum === tagPlus(0) && f.uhrzeit < uhrzeit(new Date())) {
            fehler.uhrzeit = 'Die Uhrzeit darf nicht in der Vergangenheit liegen.';
        }

        f.personen.forEach(function (name, i) {
            if (name === '') {
                fehler['personen.' + i] = 'Bitte geben Sie den Namen der externen Person an.';
            } else if (laenge(name) > MAX_LAENGEN.person) {
                fehler['personen.' + i] = 'Bitte höchstens ' + MAX_LAENGEN.person + ' Zeichen eingeben.';
            }
        });

        return fehler;
    }

    /* ================================================================
     * Templates (entsprechen templates/*.php)
     * ================================================================ */

    function fehlerZusammenfassung() {
        return '<div class="meldung meldung-fehler" role="alert" id="fehler-zusammenfassung" hidden></div>';
    }

    function feld(name, label, eingabe) {
        var id = 'fehler-' + name.replace('.', '-');
        return '<div class="feld">' +
            '<label for="' + e(name) + '">' + label + '</label>' +
            eingabe.replace('>', ' aria-describedby="' + id + '">') +
            '<p class="feld-fehler" id="' + id + '" hidden></p>' +
            '</div>';
    }

    function textfeld(f, name, label, max, autocomplete) {
        return feld(name, label,
            '<input type="text" id="' + name + '" name="' + name + '" required maxlength="' + max + '"' +
            (autocomplete ? ' autocomplete="' + autocomplete + '"' : '') +
            ' value="' + e(f[name]) + '">');
    }

    function personZeile(name, i, anzahl) {
        return '<li class="person feld">' +
            '<label for="person-' + i + '">Name der externen Person <span class="person-nr">' + (i + 1) + '</span></label>' +
            '<div class="person-zeile">' +
            '<input type="text" id="person-' + i + '" name="personen[]" required maxlength="200" value="' + e(name) + '" aria-describedby="fehler-personen-' + i + '">' +
            '<button type="button" class="button button-entfernen"' + (anzahl < 2 ? ' hidden' : '') +
            ' aria-label="Person ' + (i + 1) + ' entfernen">Entfernen</button>' +
            '</div>' +
            '<p class="feld-fehler" id="fehler-personen-' + i + '" hidden></p>' +
            '</li>';
    }

    function formularHtml(f, absendenText) {
        var artOptionen = '<option value=""' + (f.einrichtung_art === '' ? ' selected' : '') + '>Bitte wählen …</option>' +
            Object.keys(EINRICHTUNG_ARTEN).map(function (wert) {
                return '<option value="' + wert + '"' + (f.einrichtung_art === wert ? ' selected' : '') + '>' + EINRICHTUNG_ARTEN[wert] + '</option>';
            }).join('');

        return '<form class="anmeldeformular" id="anmeldeformular" novalidate>' +
            '<fieldset class="karte"><legend>Einrichtung</legend><div class="raster raster-2">' +
            textfeld(f, 'einrichtung_name', 'Name der Einrichtung', 200, 'organization') +
            feld('einrichtung_art', 'Art der Einrichtung', '<select id="einrichtung_art" name="einrichtung_art" required>' + artOptionen + '</select>') +
            '</div></fieldset>' +

            '<fieldset class="karte"><legend>Termin und Anlass</legend><div class="raster raster-2">' +
            feld('datum', 'Tag', '<input type="date" id="datum" name="datum" required min="' + tagPlus(0) + '" value="' + e(f.datum) + '">') +
            feld('uhrzeit', 'Uhrzeit <span class="hinweis-inline">(HH:MM)</span>',
                '<input type="time" id="uhrzeit" name="uhrzeit" required pattern="([01][0-9]|2[0-3]):[0-5][0-9]" placeholder="HH:MM" value="' + e(f.uhrzeit) + '">') +
            '</div>' +
            textfeld(f, 'betreff', 'Betreff', 255) +
            '</fieldset>' +

            '<fieldset class="karte"><legend>Ansprechperson</legend><div class="raster raster-2">' +
            textfeld(f, 'ansprech_vorname', 'Vorname', 100, 'given-name') +
            textfeld(f, 'ansprech_nachname', 'Nachname', 100, 'family-name') +
            '</div></fieldset>' +

            '<fieldset class="karte"><legend>Externe Personen</legend>' +
            '<p class="hinweis">Einrichtung, Termin, Betreff und Ansprechperson gelten für alle Personen dieser Anmeldung.</p>' +
            '<ol class="personen-liste" id="personen-liste">' +
            f.personen.map(function (name, i) {
                return personZeile(name, i, f.personen.length);
            }).join('') +
            '</ol>' +
            '<button type="button" class="button button-sekundaer" id="person-hinzufuegen">+ Weitere Person hinzufügen</button>' +
            '</fieldset>' +

            '<section class="karte datenschutz" aria-labelledby="datenschutz-titel">' +
            '<h2 id="datenschutz-titel">Datenschutzhinweis</h2>' +
            DATENSCHUTZ.map(function (absatz) {
                return '<p>' + e(absatz) + '</p>';
            }).join('') +
            '</section>' +

            '<div class="aktionen"><button type="submit" class="button button-primaer">' + e(absendenText) + '</button></div>' +
            '</form>';
    }

    function zusammenfassungHtml(a) {
        var termin = terminDatum(a.termin);
        return '<section class="karte"><h2>Ihre Angaben</h2><dl class="zusammenfassung">' +
            '<dt>Einrichtung</dt><dd>' + e(a.einrichtung_name) + ' (' + e(einrichtungArtLabel(a.einrichtung_art)) + ')</dd>' +
            '<dt>Termin</dt><dd>' + e(datumLang(termin)) + ', ' + e(uhrzeit(termin)) + ' Uhr</dd>' +
            '<dt>Betreff</dt><dd>' + e(a.betreff) + '</dd>' +
            '<dt>Ansprechperson</dt><dd>' + e(a.ansprech_vorname + ' ' + a.ansprech_nachname) + '</dd>' +
            '<dt>Externe Person' + (a.personen.length > 1 ? 'en' : '') + '</dt><dd><ul class="liste-schlicht">' +
            a.personen.map(function (n) {
                return '<li>' + e(n) + '</li>';
            }).join('') +
            '</ul></dd></dl></section>';
    }

    function meldungHtml(titel, text) {
        return '<section class="karte"><h1>' + e(titel) + '</h1><p>' + e(text) + '</p>' +
            '<p><a class="button button-sekundaer" href="#/">Zur Besucheranmeldung</a></p></section>';
    }

    function bearbeitenUrl(token) {
        return location.href.split('#')[0] + '#/bearbeiten/' + token;
    }

    /* ================================================================
     * Formular-Verhalten (entspricht public/assets/js/formular.js)
     * ================================================================ */

    function formularVerdrahten(beimSpeichern) {
        var form = document.getElementById('anmeldeformular');
        var liste = document.getElementById('personen-liste');
        var zusammenfassung = document.getElementById('fehler-zusammenfassung');

        function personen() {
            return Array.prototype.slice.call(liste.querySelectorAll('li.person'));
        }

        function nummerieren() {
            var eintraege = personen();
            eintraege.forEach(function (li, i) {
                var input = li.querySelector('input');
                var fehler = li.querySelector('.feld-fehler');
                var button = li.querySelector('.button-entfernen');
                input.id = 'person-' + i;
                li.querySelector('label').htmlFor = input.id;
                li.querySelector('.person-nr').textContent = String(i + 1);
                fehler.id = 'fehler-personen-' + i;
                input.setAttribute('aria-describedby', fehler.id);
                button.setAttribute('aria-label', 'Person ' + (i + 1) + ' entfernen');
                button.hidden = eintraege.length < 2;
            });
        }

        document.getElementById('person-hinzufuegen').addEventListener('click', function () {
            var neu = personen()[0].cloneNode(true);
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
        });

        liste.addEventListener('click', function (ev) {
            var button = ev.target.closest('.button-entfernen');
            if (!button || personen().length < 2) {
                return;
            }
            var li = button.closest('li.person');
            var index = personen().indexOf(li);
            li.parentNode.removeChild(li);
            nummerieren();
            var rest = personen();
            rest[Math.min(index, rest.length - 1)].querySelector('input').focus();
        });

        function setzeFehler(input, text) {
            var feldEl = input.closest('.feld');
            var ausgabe = document.getElementById(input.getAttribute('aria-describedby'));
            if (text) {
                input.setAttribute('aria-invalid', 'true');
                feldEl.classList.add('hat-fehler');
            } else {
                input.removeAttribute('aria-invalid');
                feldEl.classList.remove('hat-fehler');
            }
            if (ausgabe) {
                ausgabe.textContent = text || '';
                ausgabe.hidden = !text;
            }
        }

        form.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var f = formularAusDom(form);
            var fehler = formularValidieren(f);
            var fehlerFelder = [];

            ['einrichtung_name', 'einrichtung_art', 'datum', 'uhrzeit', 'betreff', 'ansprech_vorname', 'ansprech_nachname']
                .forEach(function (name) {
                    var input = form.elements[name];
                    setzeFehler(input, fehler[name]);
                    if (fehler[name]) {
                        fehlerFelder.push(input);
                    }
                });
            personen().forEach(function (li, i) {
                var input = li.querySelector('input');
                setzeFehler(input, fehler['personen.' + i]);
                if (fehler['personen.' + i]) {
                    fehlerFelder.push(input);
                }
            });

            if (fehlerFelder.length) {
                zusammenfassung.textContent = 'Bitte prüfen Sie Ihre Angaben: ' +
                    (fehlerFelder.length === 1 ? 'Ein Feld ist' : fehlerFelder.length + ' Felder sind') +
                    ' nicht korrekt ausgefüllt.';
                zusammenfassung.hidden = false;
                fehlerFelder[0].focus();
                return;
            }
            beimSpeichern(f);
        });

        // Fehlermeldung eines Feldes entfernen, sobald es korrigiert wird
        form.addEventListener('input', function (ev) {
            var input = ev.target;
            if (input.getAttribute('aria-invalid') === 'true' && input.value.trim() !== '') {
                setzeFehler(input, '');
            }
        });

        nummerieren();
    }

    /* ================================================================
     * Seiten (entsprechen public/*.php)
     * ================================================================ */

    var seiten = {};

    seiten.anmelden = function () {
        var beispiel = datenbankLaden().anmeldungen.filter(function (a) {
            return a.demo;
        })[0];
        inhalt.innerHTML =
            '<h1>Besuch anmelden</h1>' +
            '<p class="einleitung">Kündigen Sie hier externe Besucherinnen und Besucher beim Pfortendienst an. Alle Felder sind Pflichtfelder.</p>' +
            '<div class="demo-hinweis"><strong>So probieren Sie die Demo aus:</strong><ul>' +
            '<li>Formular ausfüllen und absenden, danach erscheint der persönliche Bearbeitungslink.</li>' +
            '<li>Oben auf <a href="#/pforte">Pforte</a> klicken, um die druckbare Tagesliste zu sehen.</li>' +
            (beispiel ? '<li>Oder eine <a href="#/bearbeiten/' + beispiel.token + '">Beispiel-Anmeldung ändern oder absagen</a>.</li>' : '') +
            '</ul></div>' +
            fehlerZusammenfassung() +
            formularHtml(leeresFormular(), 'Besuch anmelden');
        formularVerdrahten(function (f) {
            var token = anmeldungErstellen(f);
            location.hash = '#/bestaetigung/' + token;
        });
        return 'Besuch anmelden';
    };

    seiten.bestaetigung = function (token) {
        var a = anmeldungPerToken(token);
        if (!a) {
            inhalt.innerHTML = meldungHtml('Keine Bestätigung vorhanden', 'Für diese Sitzung liegt keine Anmeldung (mehr) vor.');
            return 'Keine Bestätigung vorhanden';
        }
        var url = bearbeitenUrl(token);
        inhalt.innerHTML =
            '<div class="meldung meldung-erfolg" role="status">Vielen Dank! Der Besuch wurde beim Pfortendienst angemeldet.</div>' +
            '<section class="karte"><h1>Ihr persönlicher Bearbeitungslink</h1>' +
            '<p><strong>Bitte speichern Sie diesen Link</strong> (z. B. als Lesezeichen oder in Ihren Notizen). Nur über diesen Link können Sie die Anmeldung später ändern oder absagen. Aus Datenschutzgründen wird der Link nirgends sonst gespeichert und kann nach dem Schließen des Browsers nicht wiederhergestellt werden.</p>' +
            '<div class="link-box"><input type="text" id="bearbeiten-link" value="' + e(url) + '" readonly aria-label="Bearbeitungslink">' +
            '<button type="button" class="button button-sekundaer" id="link-kopieren">Link kopieren</button></div>' +
            '<p class="hinweis" id="kopier-status" role="status"></p>' +
            '<p><a href="#/bearbeiten/' + e(token) + '">Anmeldung jetzt ansehen oder ändern</a></p></section>' +
            zusammenfassungHtml(a) +
            '<p><a class="button button-sekundaer" href="#/">Weiteren Besuch anmelden</a></p>';

        var feldEl = document.getElementById('bearbeiten-link');
        var status = document.getElementById('kopier-status');
        feldEl.addEventListener('focus', function () {
            feldEl.select();
        });
        document.getElementById('link-kopieren').addEventListener('click', function () {
            function manuell() {
                feldEl.select();
                status.textContent = 'Bitte kopieren Sie den markierten Link manuell (Strg+C).';
            }
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(feldEl.value).then(function () {
                    status.textContent = 'Der Link wurde in die Zwischenablage kopiert.';
                }, manuell);
            } else {
                manuell();
            }
        });
        return 'Anmeldung gespeichert';
    };

    seiten.bearbeiten = function (token, gespeichert) {
        var a = anmeldungPerToken(token);
        if (!a) {
            inhalt.innerHTML = meldungHtml('Anmeldung nicht gefunden', 'Zu diesem Link gibt es keine Anmeldung. Möglicherweise wurde sie abgesagt oder nach Ablauf der Löschfrist automatisch gelöscht.');
            return 'Anmeldung nicht gefunden';
        }
        inhalt.innerHTML =
            '<h1>Anmeldung ändern oder absagen</h1>' +
            (gespeichert ? '<div class="meldung meldung-erfolg" role="status">Die Änderungen wurden gespeichert. Der Bearbeitungslink bleibt gültig.</div>' : '') +
            '<p class="einleitung">Sie können alle Angaben ändern, Personen hinzufügen oder entfernen oder die Anmeldung komplett absagen.</p>' +
            fehlerZusammenfassung() +
            formularHtml(formularAusAnmeldung(a), 'Änderungen speichern') +
            '<form class="karte absage" id="absage" novalidate>' +
            '<h2>Anmeldung absagen</h2>' +
            '<p>Bei einer Absage wird die Anmeldung mit allen Personen sofort und endgültig gelöscht.</p>' +
            '<div class="feld feld-checkbox"><label>' +
            '<input type="checkbox" name="absage_bestaetigt" value="1" required aria-describedby="fehler-absage"> Ja, ich möchte diese Anmeldung absagen.' +
            '</label><p class="feld-fehler" id="fehler-absage" hidden></p></div>' +
            '<button type="submit" class="button button-gefahr">Anmeldung absagen</button>' +
            '</form>';

        formularVerdrahten(function (f) {
            anmeldungAktualisieren(token, f);
            zeigen('#/bearbeiten/' + token, true);
            window.scrollTo(0, 0);
        });

        document.getElementById('absage').addEventListener('submit', function (ev) {
            ev.preventDefault();
            var box = this.elements.absage_bestaetigt;
            var fehler = document.getElementById('fehler-absage');
            if (!box.checked) {
                box.setAttribute('aria-invalid', 'true');
                box.closest('.feld').classList.add('hat-fehler');
                fehler.textContent = 'Bitte bestätigen Sie die Absage mit dem Häkchen.';
                fehler.hidden = false;
                box.focus();
                return;
            }
            anmeldungLoeschen(token);
            inhalt.innerHTML = meldungHtml('Anmeldung abgesagt', 'Die Anmeldung wurde abgesagt und alle zugehörigen Daten wurden gelöscht.');
            document.title = 'Anmeldung abgesagt – Besucheranmeldung';
            window.scrollTo(0, 0);
        });
        return 'Anmeldung ändern';
    };

    seiten.pforte = function () {
        var tag = heute();
        var jetzt = new Date();
        var besuche = besucheAmTag(isoDatum(tag));

        var html =
            '<div class="pforte-kopf"><div>' +
            '<h1>Besuche am ' + e(datumLang(tag)) + '</h1>' +
            '<p class="stand">Stand: ' + datumKurz(jetzt) + ', ' + uhrzeit(jetzt) + ' Uhr · ' + besuche.length + ' ' + (besuche.length === 1 ? 'Person' : 'Personen') + '</p>' +
            '</div><div class="pforte-aktionen keine-druckausgabe">' +
            '<button type="button" class="button button-sekundaer" id="aktualisieren">Aktualisieren</button>' +
            '<button type="button" class="button button-primaer" id="drucken">Drucken</button>' +
            '</div></div>' +
            '<p class="demo-hinweis">In der echten Anwendung ist diese Liste nur über einen geheimen Link (<code>pforte.php?key=…</code>) erreichbar. „Drucken“ erzeugt eine A4-Seite im Querformat ohne Buttons.</p>';

        if (!besuche.length) {
            html += '<div class="meldung meldung-info">Für heute sind keine Besuche angemeldet.</div>';
        } else {
            html += '<div class="tabelle-wrapper"><table class="tagesliste"><thead><tr>' +
                '<th scope="col">Einrichtung</th><th scope="col">Tag</th><th scope="col">Uhrzeit</th>' +
                '<th scope="col">Name der externen Person</th><th scope="col">Betreff</th><th scope="col">Ansprechperson</th>' +
                '</tr></thead><tbody>' +
                besuche.map(function (b) {
                    var termin = terminDatum(b.a.termin);
                    return '<tr>' +
                        '<td data-label="Einrichtung">' + e(b.a.einrichtung_name) + ' (' + e(einrichtungArtLabel(b.a.einrichtung_art)) + ')</td>' +
                        '<td data-label="Tag">' + datumKurz(termin) + '</td>' +
                        '<td data-label="Uhrzeit">' + uhrzeit(termin) + '</td>' +
                        '<td data-label="Name" class="spalte-name">' + e(b.besucher_name) + '</td>' +
                        '<td data-label="Betreff">' + e(b.a.betreff) + '</td>' +
                        '<td data-label="Ansprechperson">' + e(b.a.ansprech_vorname + ' ' + b.a.ansprech_nachname) + '</td>' +
                        '</tr>';
                }).join('') +
                '</tbody></table></div>';
        }
        inhalt.innerHTML = html;

        document.getElementById('drucken').addEventListener('click', function () {
            window.print();
        });
        document.getElementById('aktualisieren').addEventListener('click', function () {
            seiten.pforte();
        });
        return 'Tagesliste Pforte';
    };

    /* ================================================================
     * Routing
     * ================================================================ */

    function zeigen(hash, gespeichert) {
        var teile = (hash || '').replace(/^#\/?/, '').split('/');
        var titel;
        var ansicht = 'anmelden';

        document.body.className = '';
        if (teile[0] === 'pforte') {
            ansicht = 'pforte';
            document.body.className = 'seite-pforte';
            titel = seiten.pforte();
        } else if (teile[0] === 'bestaetigung') {
            titel = seiten.bestaetigung(teile[1]);
        } else if (teile[0] === 'bearbeiten') {
            titel = seiten.bearbeiten(teile[1], gespeichert);
        } else {
            titel = seiten.anmelden();
        }

        document.title = titel + ' – Besucheranmeldung (Demo)';
        Array.prototype.forEach.call(document.querySelectorAll('.demo-nav a'), function (a) {
            if (a.getAttribute('data-ansicht') === ansicht) {
                a.setAttribute('aria-current', 'page');
            } else {
                a.removeAttribute('aria-current');
            }
        });
    }

    window.addEventListener('hashchange', function () {
        zeigen(location.hash);
        window.scrollTo(0, 0);
    });

    document.getElementById('demo-zuruecksetzen').addEventListener('click', function () {
        if (window.confirm('Alle in dieser Demo eingegebenen Anmeldungen löschen und die Beispieldaten neu laden?')) {
            datenbankSpeichern(neueDatenbank());
            if (location.hash === '' || location.hash === '#/') {
                zeigen('#/');
            } else {
                location.hash = '#/';
            }
        }
    });

    zeigen(location.hash);
})();
