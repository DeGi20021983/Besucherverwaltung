<?php
/**
 * Löscht Besucherdaten und Rate-Limit-Einträge, die älter als 48 Stunden sind.
 *
 * Für den Cronjob gedacht (z. B. stündlich):
 *   0 * * * * php /var/www/besucherverwaltung/cleanup.php
 *
 * Liegt außerhalb von public/ und ist daher nicht über den Browser erreichbar.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

require __DIR__ . '/app/bootstrap.php';

try {
    $ergebnis = cleanup_run(db(), jetzt());
} catch (Throwable $e) {
    fwrite(STDERR, 'Bereinigung fehlgeschlagen: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

if (in_array('-v', $argv ?? [], true)) {
    printf("Gelöscht: %d Anmeldung(en), %d Rate-Limit-Einträge\n",
        $ergebnis['anmeldungen'], $ergebnis['rate_limit']);
}
