<?php
/**
 * Datenlöschung: Besucherdaten und Rate-Limit-Einträge 48 Stunden nach
 * dem Besuchstermin bzw. nach ihrer Erfassung.
 */

declare(strict_types=1);

const AUFBEWAHRUNG_STUNDEN = 48;

/**
 * Löscht alle abgelaufenen Daten und liefert die Anzahl gelöschter
 * Anmeldungen und Rate-Limit-Einträge.
 */
function cleanup_run(PDO $db, DateTimeImmutable $now): array
{
    // Absolute 48 Stunden, auch über eine Zeitumstellung hinweg.
    $grenze = $now->setTimezone(new DateTimeZone('UTC'))
        ->modify('-' . AUFBEWAHRUNG_STUNDEN . ' hours')
        ->setTimezone($now->getTimezone())
        ->format('Y-m-d H:i:s');

    // Die Personen werden über ON DELETE CASCADE mitgelöscht.
    $stmt = $db->prepare('DELETE FROM anmeldungen WHERE besuch_am < :grenze');
    $stmt->execute([':grenze' => $grenze]);
    $anmeldungen = $stmt->rowCount();

    $stmt = $db->prepare('DELETE FROM rate_limit WHERE erstellt_am < :grenze');
    $stmt->execute([':grenze' => $grenze]);

    return ['anmeldungen' => $anmeldungen, 'rate_limit' => $stmt->rowCount()];
}
