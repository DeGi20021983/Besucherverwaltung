<?php
/**
 * Löscht Besucherdaten und Rate-Limit-Einträge 48 Stunden nach dem Besuchstermin.
 *
 * Für den Cronjob (nur über die Kommandozeile aufrufbar), z. B. stündlich:
 *     0 * * * * /usr/bin/php /var/www/besucheranmeldung/cleanup.php >/dev/null 2>&1
 *
 * Die Anwendung führt dieselbe Bereinigung zusätzlich bei jedem Seitenaufruf aus.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

require __DIR__ . '/src/bootstrap.php';

$ergebnis = datenBereinigen();

printf(
    "[%s] Bereinigung: %d Anmeldung(en), %d Rate-Limit-Eintrag/-Einträge gelöscht.\n",
    jetzt()->format('Y-m-d H:i:s'),
    $ergebnis['anmeldungen'],
    $ergebnis['rate_limit']
);
