<?php
/**
 * Datenzugriff für Anmeldungen und Besucher (PDO, Prepared Statements).
 */

declare(strict_types=1);

/**
 * Legt eine Anmeldung an und gibt den Klartext-Bearbeitungstoken zurück.
 */
function anmeldungErstellen(array $f): string
{
    $token = zufallsToken();
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO anmeldungen
                (token_hash, einrichtung_name, einrichtung_art, termin, betreff, ansprech_vorname, ansprech_nachname, erstellt_am)
             VALUES
                (:token_hash, :einrichtung_name, :einrichtung_art, :termin, :betreff, :ansprech_vorname, :ansprech_nachname, :erstellt_am)'
        );
        $stmt->execute([
            'token_hash'        => tokenHash($token),
            'einrichtung_name'  => $f['einrichtung_name'],
            'einrichtung_art'   => $f['einrichtung_art'],
            'termin'            => terminAusFormular($f),
            'betreff'           => $f['betreff'],
            'ansprech_vorname'  => $f['ansprech_vorname'],
            'ansprech_nachname' => $f['ansprech_nachname'],
            'erstellt_am'       => jetzt()->format('Y-m-d H:i:s'),
        ]);
        besucherSpeichern((int) $pdo->lastInsertId(), $f['personen']);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    return $token;
}

function anmeldungAktualisieren(int $id, array $f): void
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            'UPDATE anmeldungen SET
                einrichtung_name = :einrichtung_name,
                einrichtung_art = :einrichtung_art,
                termin = :termin,
                betreff = :betreff,
                ansprech_vorname = :ansprech_vorname,
                ansprech_nachname = :ansprech_nachname,
                geaendert_am = :geaendert_am
             WHERE id = :id'
        );
        $stmt->execute([
            'einrichtung_name'  => $f['einrichtung_name'],
            'einrichtung_art'   => $f['einrichtung_art'],
            'termin'            => terminAusFormular($f),
            'betreff'           => $f['betreff'],
            'ansprech_vorname'  => $f['ansprech_vorname'],
            'ansprech_nachname' => $f['ansprech_nachname'],
            'geaendert_am'      => jetzt()->format('Y-m-d H:i:s'),
            'id'                => $id,
        ]);

        $pdo->prepare('DELETE FROM besucher WHERE anmeldung_id = :id')->execute(['id' => $id]);
        besucherSpeichern($id, $f['personen']);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/** Absage = Löschung (Besucher werden per ON DELETE CASCADE mitgelöscht). */
function anmeldungLoeschen(int $id): void
{
    db()->prepare('DELETE FROM anmeldungen WHERE id = :id')->execute(['id' => $id]);
}

function besucherSpeichern(int $anmeldungId, array $personen): void
{
    $stmt = db()->prepare('INSERT INTO besucher (anmeldung_id, position, name) VALUES (:anmeldung_id, :position, :name)');
    foreach (array_values($personen) as $i => $name) {
        $stmt->execute(['anmeldung_id' => $anmeldungId, 'position' => $i + 1, 'name' => $name]);
    }
}

/**
 * Lädt eine Anmeldung anhand des Klartext-Tokens (inkl. Personen) oder null.
 */
function anmeldungPerToken(mixed $token): ?array
{
    if (!tokenFormatGueltig($token)) {
        return null;
    }
    $hash = tokenHash($token);

    $stmt = db()->prepare('SELECT * FROM anmeldungen WHERE token_hash = :hash LIMIT 1');
    $stmt->execute(['hash' => $hash]);
    $anmeldung = $stmt->fetch();
    if (!$anmeldung || !hash_equals($anmeldung['token_hash'], $hash)) {
        return null;
    }

    $stmt = db()->prepare('SELECT name FROM besucher WHERE anmeldung_id = :id ORDER BY position, id');
    $stmt->execute(['id' => $anmeldung['id']]);
    $anmeldung['personen'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return $anmeldung;
}

/**
 * Tagesliste: eine Zeile pro externer Person, sortiert nach Uhrzeit.
 */
function besucheAmTag(DateTimeImmutable $tag): array
{
    $von = $tag->setTime(0, 0);
    $bis = $von->modify('+1 day');

    $stmt = db()->prepare(
        'SELECT a.einrichtung_name, a.einrichtung_art, a.termin, a.betreff,
                a.ansprech_vorname, a.ansprech_nachname, b.name AS besucher_name
           FROM anmeldungen a
           JOIN besucher b ON b.anmeldung_id = a.id
          WHERE a.termin >= :von AND a.termin < :bis
          ORDER BY a.termin, a.einrichtung_name, a.id, b.position, b.id'
    );
    $stmt->execute(['von' => $von->format('Y-m-d H:i:s'), 'bis' => $bis->format('Y-m-d H:i:s')]);

    return $stmt->fetchAll();
}

/**
 * Löscht Anmeldungen (samt Besuchern) und Rate-Limit-Einträge, deren
 * Besuchstermin bzw. Zeitstempel länger als die Löschfrist zurückliegt.
 *
 * @return array{anmeldungen:int, rate_limit:int}
 */
function datenBereinigen(): array
{
    $stunden = (int) config('loeschfrist_stunden', 48);
    $grenze = jetzt()->modify("-{$stunden} hours")->format('Y-m-d H:i:s');

    $a = db()->prepare('DELETE FROM anmeldungen WHERE termin < :grenze');
    $a->execute(['grenze' => $grenze]);

    $r = db()->prepare('DELETE FROM rate_limit WHERE erstellt_am < :grenze');
    $r->execute(['grenze' => $grenze]);

    return ['anmeldungen' => $a->rowCount(), 'rate_limit' => $r->rowCount()];
}
