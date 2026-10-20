/* Besucheranmeldung – Komfortfunktionen im Browser.
   Alles funktioniert auch ohne JavaScript; die Prüfung auf dem Server ist maßgeblich. */
(function () {
  'use strict';

  var FEHLER_ICON = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v6M12 16.5v.5"/></svg>';

  function pad(n) { return (n < 10 ? '0' : '') + n; }
  function heute() {
    var d = new Date();
    return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
  }
  function jetztZeit() {
    var d = new Date();
    return pad(d.getHours()) + ':' + pad(d.getMinutes());
  }

  /* ---------- Fehleranzeige am Feld ---------- */

  function fehlerSetzen(feld, ziel, meldung) {
    var vorhanden = feld.querySelector(':scope > .err, :scope .person-eingabe > .err');
    if (vorhanden) { vorhanden.remove(); }
    if (!meldung) {
      ziel.removeAttribute('aria-invalid');
      ziel.removeAttribute('aria-describedby');
      return;
    }
    var id = 'e-' + (ziel.id || ziel.name);
    var p = document.createElement('p');
    p.className = 'err';
    p.id = id;
    p.innerHTML = FEHLER_ICON;
    var span = document.createElement('span');
    span.textContent = meldung;
    p.appendChild(span);
    var nach = feld.querySelector('.person-eingabe') || feld;
    if (feld.classList.contains('feld-gruppe')) {
      feld.appendChild(p);
    } else {
      nach.appendChild(p);
    }
    ziel.setAttribute('aria-invalid', 'true');
    ziel.setAttribute('aria-describedby', id);
  }

  /* ---------- Personen hinzufügen / entfernen ---------- */

  function personenAktualisieren(liste) {
    var personen = liste.querySelectorAll('[data-person]');
    var mehrere = personen.length > 1;
    personen.forEach(function (el, i) {
      var nr = i + 1;
      var input = el.querySelector('input');
      var label = el.querySelector('label');
      var btn = el.querySelector('[data-person-entfernen]');
      var err = el.querySelector('.err');
      input.id = 'person-' + nr;
      label.htmlFor = input.id;
      label.textContent = mehrere ? 'Name der externen Person ' + nr : 'Name der externen Person';
      if (err) {
        err.id = 'e-person-' + nr;
        input.setAttribute('aria-describedby', err.id);
      }
      btn.hidden = !mehrere;
      btn.setAttribute('aria-label', 'Person ' + nr + ' entfernen');
      btn.value = 'person_entfernen_' + i;
    });
  }

  function personenEinrichten(form) {
    var liste = form.querySelector('[data-personen]');
    var vorlage = document.getElementById('person-vorlage');
    var hinzu = form.querySelector('[data-person-hinzufuegen]');
    if (!liste || !vorlage || !hinzu) { return; }

    hinzu.addEventListener('click', function (ev) {
      ev.preventDefault();
      var neu = vorlage.content.firstElementChild.cloneNode(true);
      liste.appendChild(neu);
      personenAktualisieren(liste);
      neu.querySelector('input').focus();
    });

    liste.addEventListener('click', function (ev) {
      var btn = ev.target.closest('[data-person-entfernen]');
      if (!btn) { return; }
      ev.preventDefault();
      var person = btn.closest('[data-person]');
      var index = Array.prototype.indexOf.call(liste.querySelectorAll('[data-person]'), person);
      person.remove();
      personenAktualisieren(liste);
      var inputs = liste.querySelectorAll('[data-person] input');
      var ziel = inputs[Math.min(index, inputs.length - 1)];
      if (ziel) { ziel.focus(); }
    });
  }

  /* ---------- Clientseitige Prüfung ---------- */

  var PFLICHT = {
    einrichtung: 'Bitte geben Sie den Namen der Einrichtung an.',
    betreff: 'Bitte geben Sie einen Betreff an.',
    vorname: 'Bitte geben Sie den Vornamen an.',
    nachname: 'Bitte geben Sie den Nachnamen an.'
  };

  function pruefen(form) {
    var fehler = [];
    function melde(feld, ziel, text) {
      fehlerSetzen(feld, ziel, text);
      if (text) { fehler.push(ziel); }
    }

    Object.keys(PFLICHT).forEach(function (name) {
      var input = form.elements[name];
      melde(input.closest('.feld'), input, input.value.trim() === '' ? PFLICHT[name] : '');
    });

    var gruppe = form.querySelector('#art');
    var gewaehlt = form.querySelector('input[name="art"]:checked');
    if (gewaehlt) {
      gruppe.removeAttribute('aria-invalid');
      fehlerSetzen(gruppe, gruppe, '');
    } else {
      melde(gruppe, gruppe, 'Bitte wählen Sie die Art der Einrichtung.');
      fehler[fehler.length - 1] = gruppe.querySelector('input');
    }

    var datum = form.elements.datum;
    var zeit = form.elements.zeit;
    var d = datum.value.trim();
    var z = zeit.value.trim();
    var datumFehler = '';
    if (d === '') {
      datumFehler = 'Bitte wählen Sie ein Datum.';
    } else if (!/^\d{4}-\d{2}-\d{2}$/.test(d)) {
      datumFehler = 'Bitte geben Sie ein gültiges Datum an.';
    } else if (d < heute()) {
      datumFehler = 'Das Datum darf nicht in der Vergangenheit liegen.';
    }
    melde(datum.closest('.feld'), datum, datumFehler);

    var zeitFehler = '';
    if (z === '') {
      zeitFehler = 'Bitte geben Sie die Uhrzeit im Format HH:MM an, z. B. 09:30.';
    } else if (!/^([01]\d|2[0-3]):[0-5]\d$/.test(z)) {
      zeitFehler = 'Bitte nutzen Sie das Format HH:MM, z. B. 09:30.';
    } else if (!datumFehler && d === heute() && z < jetztZeit()) {
      zeitFehler = 'Die Uhrzeit liegt in der Vergangenheit.';
    }
    melde(zeit.closest('.feld'), zeit, zeitFehler);

    form.querySelectorAll('[data-personen] [data-person]').forEach(function (el) {
      var input = el.querySelector('input');
      melde(el, input, input.value.trim() === '' ? 'Bitte geben Sie den Namen der externen Person an.' : '');
    });

    return fehler;
  }

  function formularEinrichten(form) {
    personenEinrichten(form);
    var hinweis = form.querySelector('[data-fehler-hinweis]');

    form.addEventListener('submit', function (ev) {
      var btn = ev.submitter;
      // Nur die eigentliche Absendung prüfen
      if (btn && btn.value && btn.value !== 'speichern') { return; }
      var fehler = pruefen(form);
      if (fehler.length) {
        ev.preventDefault();
        if (hinweis) {
          var titel = form.querySelector('[data-fehler-hinweis] .alert-titel');
          if (titel && !hinweis.querySelector('.alert-text')) {
            titel.textContent = 'Die Angaben wurden noch nicht gespeichert.';
            var t = document.createElement('div');
            t.className = 'alert-text';
            t.textContent = 'Bitte korrigieren Sie die markierten Felder. Ihre bisherigen Eingaben bleiben erhalten.';
            titel.parentNode.appendChild(t);
          }
          hinweis.hidden = false;
        }
        fehler[0].focus();
      }
    });

    // Fehler am Feld verschwinden, sobald es korrigiert wird
    form.addEventListener('input', function (ev) {
      var t = ev.target;
      if (t.getAttribute('aria-invalid') === 'true' && t.value.trim() !== '') {
        var feld = t.closest('[data-person]') || t.closest('.feld');
        if (feld) { fehlerSetzen(feld, t, ''); }
      }
    });
    form.addEventListener('change', function (ev) {
      if (ev.target.name === 'art') {
        var gruppe = form.querySelector('#art');
        fehlerSetzen(gruppe, gruppe, '');
      }
    });
  }

  /* ---------- Weitere Seiten ---------- */

  function kopierenEinrichten(btn) {
    var input = document.getElementById(btn.getAttribute('data-kopieren'));
    if (!input) { return; }
    btn.hidden = false;
    btn.addEventListener('click', function () {
      function fertig() { btn.textContent = 'Kopiert'; }
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(input.value).then(fertig, function () {
          input.select();
        });
      } else {
        input.select();
        try { document.execCommand('copy'); fertig(); } catch (e) { /* markiert lassen */ }
      }
    });
    input.addEventListener('focus', function () { input.select(); });
  }

  function absageEinrichten() {
    var frage = document.querySelector('[data-absage-frage]');
    var box = document.querySelector('[data-absage-box]');
    var zurueck = document.querySelector('[data-absage-zurueck]');
    if (!frage || !box || !zurueck) { return; }
    frage.hidden = false;
    zurueck.hidden = false;
    box.hidden = true;
    frage.addEventListener('click', function () {
      frage.hidden = true;
      box.hidden = false;
      zurueck.focus();
    });
    zurueck.addEventListener('click', function () {
      box.hidden = true;
      frage.hidden = false;
      frage.focus();
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-formular]').forEach(formularEinrichten);
    document.querySelectorAll('[data-kopieren]').forEach(kopierenEinrichten);
    absageEinrichten();
    var drucken = document.querySelector('[data-drucken]');
    if (drucken) {
      drucken.addEventListener('click', function () { window.print(); });
    }
  });
})();
