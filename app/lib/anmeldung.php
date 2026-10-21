<?php
/**
 * Anmeldungen: Validierung und Datenbankzugriffe.
 */

declare(strict_types=1);

const EINRICHTUNG_ARTEN = ['Behörde', 'Firma'];

/** Höchstlängen der Textfelder (entsprechen den Spalten in schema.sql). */
const FELD_LAENGEN = [
    'einrichtung' => 200,
    'betreff'     => 255,
    'vorname'     => 100,
    'nachname'    => 100,
    'person'      => 200,
];

/** Leere Formularwerte für eine neue Anmeldung. */
function anmeldung_leer(): array
{
    return [
        'einrichtung' => '', 'art' => '', 'datum' => '', 'zeit' => '',
        'betreff' => '', 'vorname' => '', 'nachname' => '', 'personen' => [''],
    ];
}

/** Formularwerte aus einer gespeicherten Anmeldung. */
function anmeldung_als_formular(array $a): array
{
    $am = new DateTimeImmutable($a['besuch_am']);
    return [
        'einrichtung' => $a['einrichtung'],
        'art'         => $a['einrichtung_art'],
        'datum'       => $am->format('Y-m-d'),
        'zeit'        => $am->format('H:i'),
        'betreff'     => $a['betreff'],
        'vorname'     => $a['ansprech_vorname'],
        'nachname'    => $a['ansprech_nachname'],
        'personen'    => array_column($a['personen'], 'name'),
    ];
}

/** Text bereinigen: Steuerzeichen entfernen, Leerraum zusammenfassen. */
function text_bereinigen(mixed $value): string
{
    if (!is_string($value)) {
        return '';
    }
    $value = preg_replace('/[\p{Cc}\p{Cf}]+/u', ' ', $value) ?? '';
    return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
}

/** Formularwerte aus $_POST lesen, ohne sie schon zu prüfen. */
function anmeldung_aus_post(array $post): array
{
    $personen = $post['personen'] ?? [''];
    if (!is_array($personen)) {
        $personen = [$personen];
    }
    $personen = array_values(array_map('text_bereinigen', $personen));
    if ($personen === []) {
        $personen = [''];
    }

    return [
        'einrichtung' => text_bereinigen($post['einrichtung'] ?? ''),
        'art'         => text_bereinigen($post['art'] ?? ''),
        'datum'       => text_bereinigen($post['datum'] ?? ''),
        'zeit'        => text_bereinigen($post['zeit'] ?? ''),
        'betreff'     => text_bereinigen($post['betreff'] ?? ''),
        'vorname'     => text_bereinigen($post['vorname'] ?? ''),
        'nachname'    => text_bereinigen($post['nachname'] ?? ''),
        'personen'    => $personen,
    ];
}

/**
 * Prüft die Formularwerte. Liefert ein Array Feld => Fehlermeldung;
 * Fehler zu Personen stehen unter "personen" => [Index => Meldung].
 */
function anmeldung_validieren(array $v, DateTimeImmutable $now): array
{
    $f = [];

    $pflicht = [
        'einrichtung' => 'Bitte geben Sie den Namen der Einrichtung an.',
        'betreff'     => 'Bitte geben Sie einen Betreff an.',
        'vorname'     => 'Bitte geben Sie den Vornamen an.',
        'nachname'    => 'Bitte geben Sie den Nachnamen an.',
    ];
    foreach ($pflicht as $feld => $meldung) {
        if ($v[$feld] === '') {
            $f[$feld] = $meldung;
        } elseif (mb_strlen($v[$feld]) > FELD_LAENGEN[$feld]) {
            $f[$feld] = 'Bitte höchstens ' . FELD_LAENGEN[$feld] . ' Zeichen eingeben.';
        }
    }

    if ($v['art'] === '') {
        $f['art'] = 'Bitte wählen Sie die Art der Einrichtung.';
    } elseif (!in_array($v['art'], EINRICHTUNG_ARTEN, true)) {
        $f['art'] = 'Bitte wählen Sie „Behörde“ oder „Firma“.';
    }

    $datum = null;
    if ($v['datum'] === '') {
        $f['datum'] = 'Bitte wählen Sie ein Datum.';
    } else {
        $datum = DateTimeImmutable::createFromFormat('!Y-m-d', $v['datum'], $now->getTimezone());
        if ($datum === false || $datum->format('Y-m-d') !== $v['datum']) {
            $f['datum'] = 'Bitte geben Sie ein gültiges Datum an.';
            $datum = null;
        } elseif ($v['datum'] < $now->format('Y-m-d')) {
            $f['datum'] = 'Das Datum darf nicht in der Vergangenheit liegen.';
        }
    }

    if ($v['zeit'] === '') {
        $f['zeit'] = 'Bitte geben Sie die Uhrzeit im Format HH:MM an, z. B. 09:30.';
    } elseif (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $v['zeit'])) {
        $f['zeit'] = 'Bitte nutzen Sie das Format HH:MM, z. B. 09:30.';
    } elseif ($datum !== null && !isset($f['datum'])
        && $v['datum'] === $now->format('Y-m-d') && $v['zeit'] < $now->format('H:i')) {
        $f['zeit'] = 'Die Uhrzeit liegt in der Vergangenheit.';
    }

    foreach ($v['personen'] as $i => $name) {
        if ($name === '') {
            $f['personen'][$i] = 'Bitte geben Sie den Namen der externen Person an.';
        } elseif (mb_strlen($name) > FELD_LAENGEN['person']) {
            $f['personen'][$i] = 'Bitte höchstens ' . FELD_LAENGEN['person'] . ' Zeichen eingeben.';
        }
    }

    return $f;
}

/** Datum und Uhrzeit des Besuchs als DATETIME-Wert. */
function besuch_am(array $v): string
{
    return $v['datum'] . ' ' . $v['zeit'] . ':00';
}

/** Speichert eine neue Anmeldung und liefert den Bearbeitungs-Token. */
function anmeldung_anlegen(PDO $db, array $v, DateTimeImmutable $now): string
{
    $token = new_token();
    $zeit = $now->format('Y-m-d H:i:s');

    $db->beginTransaction();
    try {
        $stmt = $db->prepare(
            'INSERT INTO anmeldungen (token_hash, einrichtung, einrichtung_art, besuch_am, betreff,
                ansprech_vorname, ansprech_nachname, erstellt_am, geaendert_am)
             VALUES (:token, :einrichtung, :art, :am, :betreff, :vorname, :nachname, :erstellt, :geaendert)'
        );
        $stmt->execute([
            ':token' => token_hash($token), ':einrichtung' => $v['einrichtung'], ':art' => $v['art'],
            ':am' => besuch_am($v), ':betreff' => $v['betreff'], ':vorname' => $v['vorname'],
            ':nachname' => $v['nachname'], ':erstellt' => $zeit, ':geaendert' => $zeit,
        ]);
        personen_speichern($db, (int) $db->lastInsertId(), $v['personen']);
        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }

    return $token;
}

/** Ändert eine Anmeldung; die Personenliste wird vollständig ersetzt. */
function anmeldung_aendern(PDO $db, int $id, array $v, DateTimeImmutable $now): void
{
    $db->beginTransaction();
    try {
        $stmt = $db->prepare(
            'UPDATE anmeldungen SET einrichtung = :einrichtung, einrichtung_art = :art, besuch_am = :am,
                betreff = :betreff, ansprech_vorname = :vorname, ansprech_nachname = :nachname,
                geaendert_am = :geaendert
             WHERE id = :id'
        );
        $stmt->execute([
            ':einrichtung' => $v['einrichtung'], ':art' => $v['art'], ':am' => besuch_am($v),
            ':betreff' => $v['betreff'], ':vorname' => $v['vorname'], ':nachname' => $v['nachname'],
            ':geaendert' => $now->format('Y-m-d H:i:s'), ':id' => $id,
        ]);
        $db->prepare('DELETE FROM besucher WHERE anmeldung_id = :id')->execute([':id' => $id]);
        personen_speichern($db, $id, $v['personen']);
        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

function personen_speichern(PDO $db, int $anmeldungId, array $personen): void
{
    $stmt = $db->prepare('INSERT INTO besucher (anmeldung_id, position, name) VALUES (:id, :pos, :name)');
    foreach (array_values($personen) as $pos => $name) {
        $stmt->execute([':id' => $anmeldungId, ':pos' => $pos + 1, ':name' => $name]);
    }
}

/** Absage = Löschung. Die Personen werden per ON DELETE CASCADE entfernt. */
function anmeldung_loeschen(PDO $db, int $id): void
{
    $db->prepare('DELETE FROM anmeldungen WHERE id = :id')->execute([':id' => $id]);
}

/** Sucht eine Anmeldung über den Bearbeitungs-Token (inklusive Personen). */
function anmeldung_per_token(PDO $db, string $token): ?array
{
    $hash = token_hash($token);
    $stmt = $db->prepare('SELECT * FROM anmeldungen WHERE token_hash = :h');
    $stmt->execute([':h' => $hash]);
    $a = $stmt->fetch();
    if (!$a || !hash_equals($a['token_hash'], $hash)) {
        return null;
    }

    $stmt = $db->prepare('SELECT name FROM besucher WHERE anmeldung_id = :id ORDER BY position, id');
    $stmt->execute([':id' => $a['id']]);
    $a['personen'] = $stmt->fetchAll();
    return $a;
}

/**
 * Alle Besuche eines Tages, eine Zeile pro externer Person,
 * sortiert nach Uhrzeit.
 */
function tagesliste(PDO $db, DateTimeImmutable $tag): array
{
    $beginn = $tag->setTime(0, 0);
    $stmt = $db->prepare(
        'SELECT a.id, a.einrichtung, a.einrichtung_art, a.besuch_am, a.betreff,
                a.ansprech_vorname, a.ansprech_nachname, b.name
         FROM anmeldungen a
         JOIN besucher b ON b.anmeldung_id = a.id
         WHERE a.besuch_am >= :beginn AND a.besuch_am < :ende
         ORDER BY a.besuch_am, a.id, b.position, b.id'
    );
    $stmt->execute([
        ':beginn' => $beginn->format('Y-m-d H:i:s'),
        ':ende'   => $beginn->modify('+1 day')->format('Y-m-d H:i:s'),
    ]);
    return $stmt->fetchAll();
}

/**
 * Personen hinzufügen/entfernen ohne JavaScript: Die Buttons senden das
 * Formular mit aktion=person_hinzufuegen bzw. person_entfernen_<Index>.
 * Liefert die geänderten Werte oder null, wenn keine solche Aktion vorliegt.
 */
function personen_aktion(array $v, mixed $aktion): ?array
{
    if (!is_string($aktion)) {
        return null;
    }
    if ($aktion === 'person_hinzufuegen') {
        $v['personen'][] = '';
        return $v;
    }
    if (preg_match('/^person_entfernen_(\d+)$/', $aktion, $m)) {
        unset($v['personen'][(int) $m[1]]);
        $v['personen'] = array_values($v['personen']) ?: [''];
        return $v;
    }
    return null;
}
