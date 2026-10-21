<?php
/**
 * Formulardaten einlesen und serverseitig validieren.
 */

declare(strict_types=1);

const MAX_LAENGEN = [
    'einrichtung_name'  => 200,
    'betreff'           => 255,
    'ansprech_vorname'  => 100,
    'ansprech_nachname' => 100,
    'person'            => 200,
];

function leeresFormular(): array
{
    return [
        'einrichtung_name'  => '',
        'einrichtung_art'   => '',
        'datum'             => '',
        'uhrzeit'           => '',
        'betreff'           => '',
        'ansprech_vorname'  => '',
        'ansprech_nachname' => '',
        'personen'          => [''],
    ];
}

function formularAusAnmeldung(array $a): array
{
    $termin = new DateTimeImmutable($a['termin']);

    return [
        'einrichtung_name'  => $a['einrichtung_name'],
        'einrichtung_art'   => $a['einrichtung_art'],
        'datum'             => $termin->format('Y-m-d'),
        'uhrzeit'           => $termin->format('H:i'),
        'betreff'           => $a['betreff'],
        'ansprech_vorname'  => $a['ansprech_vorname'],
        'ansprech_nachname' => $a['ansprech_nachname'],
        'personen'          => $a['personen'] ?: [''],
    ];
}

function textWert(array $quelle, string $schluessel): string
{
    $wert = $quelle[$schluessel] ?? '';
    if (!is_string($wert)) {
        return '';
    }
    // Steuerzeichen entfernen, Leerraum normalisieren
    $wert = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $wert) ?? '';

    return trim(preg_replace('/\s+/u', ' ', $wert) ?? '');
}

function formularAusRequest(array $post): array
{
    $f = leeresFormular();
    foreach (['einrichtung_name', 'einrichtung_art', 'datum', 'uhrzeit', 'betreff', 'ansprech_vorname', 'ansprech_nachname'] as $feld) {
        $f[$feld] = textWert($post, $feld);
    }

    $personen = [];
    if (isset($post['personen']) && is_array($post['personen'])) {
        foreach ($post['personen'] as $name) {
            $personen[] = is_string($name) ? textWert(['n' => $name], 'n') : '';
        }
    }
    $f['personen'] = $personen ?: [''];

    return $f;
}

/**
 * Verarbeitet „Weitere Person hinzufügen“ / „Entfernen“ für Browser ohne JavaScript.
 * Gibt true zurück, wenn die Aktion eine Personen-Aktion war (dann wird nicht gespeichert).
 */
function personenAktionAnwenden(array &$f, string $aktion): bool
{
    if ($aktion === 'person_hinzufuegen') {
        $f['personen'][] = '';
        return true;
    }
    if (preg_match('/^person_entfernen_(\d+)$/', $aktion, $m)) {
        $index = (int) $m[1];
        if (count($f['personen']) > 1 && array_key_exists($index, $f['personen'])) {
            array_splice($f['personen'], $index, 1);
        }
        return true;
    }

    return false;
}

/** Datum + Uhrzeit als DateTimeImmutable oder null bei ungültiger Eingabe. */
function terminParsen(string $datum, string $uhrzeit): ?DateTimeImmutable
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datum) || !uhrzeitFormatGueltig($uhrzeit)) {
        return null;
    }
    $termin = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $datum . ' ' . $uhrzeit);
    if ($termin === false || $termin->format('Y-m-d H:i') !== $datum . ' ' . $uhrzeit) {
        return null;
    }

    return $termin;
}

function uhrzeitFormatGueltig(string $uhrzeit): bool
{
    return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $uhrzeit);
}

function terminAusFormular(array $f): string
{
    return terminParsen($f['datum'], $f['uhrzeit'])->format('Y-m-d H:i:s');
}

/**
 * @return array<string,string> Fehlermeldungen je Feld (Personen: 'personen.N')
 */
function formularValidieren(array $f): array
{
    $fehler = [];

    $pflicht = [
        'einrichtung_name'  => 'Bitte geben Sie den Namen der Einrichtung an.',
        'betreff'           => 'Bitte geben Sie einen Betreff an.',
        'ansprech_vorname'  => 'Bitte geben Sie den Vornamen der Ansprechperson an.',
        'ansprech_nachname' => 'Bitte geben Sie den Nachnamen der Ansprechperson an.',
    ];
    foreach ($pflicht as $feld => $meldung) {
        if ($f[$feld] === '') {
            $fehler[$feld] = $meldung;
        } elseif (mb_strlen($f[$feld]) > MAX_LAENGEN[$feld]) {
            $fehler[$feld] = sprintf('Bitte höchstens %d Zeichen eingeben.', MAX_LAENGEN[$feld]);
        }
    }

    if ($f['einrichtung_art'] === '') {
        $fehler['einrichtung_art'] = 'Bitte wählen Sie die Art der Einrichtung.';
    } elseif (!array_key_exists($f['einrichtung_art'], EINRICHTUNG_ARTEN)) {
        $fehler['einrichtung_art'] = 'Bitte wählen Sie „Behörde“ oder „Firma“.';
    }

    $datumOk = false;
    if ($f['datum'] === '') {
        $fehler['datum'] = 'Bitte geben Sie das Datum des Besuchs an.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $f['datum']) || !terminParsen($f['datum'], '00:00')) {
        $fehler['datum'] = 'Bitte geben Sie ein gültiges Datum an.';
    } elseif ($f['datum'] < heute()->format('Y-m-d')) {
        $fehler['datum'] = 'Das Datum darf nicht in der Vergangenheit liegen.';
    } else {
        $datumOk = true;
    }

    if ($f['uhrzeit'] === '') {
        $fehler['uhrzeit'] = 'Bitte geben Sie die Uhrzeit an.';
    } elseif (!uhrzeitFormatGueltig($f['uhrzeit'])) {
        $fehler['uhrzeit'] = 'Bitte geben Sie die Uhrzeit im Format HH:MM an (z. B. 09:30).';
    } elseif ($datumOk) {
        $termin = terminParsen($f['datum'], $f['uhrzeit']);
        if ($termin < jetzt()->setTime((int) jetzt()->format('H'), (int) jetzt()->format('i'))) {
            $fehler['uhrzeit'] = 'Die Uhrzeit darf nicht in der Vergangenheit liegen.';
        }
    }

    if (!$f['personen']) {
        $fehler['personen.0'] = 'Bitte geben Sie mindestens eine externe Person an.';
    }
    foreach ($f['personen'] as $i => $name) {
        if ($name === '') {
            $fehler['personen.' . $i] = 'Bitte geben Sie den Namen der externen Person an.';
        } elseif (mb_strlen($name) > MAX_LAENGEN['person']) {
            $fehler['personen.' . $i] = sprintf('Bitte höchstens %d Zeichen eingeben.', MAX_LAENGEN['person']);
        }
    }

    return $fehler;
}
