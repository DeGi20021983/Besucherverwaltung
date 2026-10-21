<?php
/**
 * Gemeinsamer Einstieg für alle Seiten und für cleanup.php.
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . '/src/helpers.php';
require APP_ROOT . '/src/security.php';
require APP_ROOT . '/src/anmeldungen.php';
require APP_ROOT . '/src/validierung.php';

mb_internal_encoding('UTF-8');
ini_set('display_errors', '0');
ini_set('log_errors', '1');

$configDatei = APP_ROOT . '/config/config.php';
if (!is_file($configDatei)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Die Anwendung ist noch nicht eingerichtet: config/config.php fehlt.\n";
    exit;
}
$GLOBALS['APP_CONFIG'] = require $configDatei;

date_default_timezone_set((string) config('timezone', 'Europe/Berlin'));

if (PHP_SAPI !== 'cli') {
    set_exception_handler(static function (Throwable $e): void {
        // Nur technische Angaben protokollieren, keine Formularinhalte.
        error_log(sprintf('[besucheranmeldung] %s in %s:%d', get_class($e), $e->getFile(), $e->getLine()));
        if (!headers_sent()) {
            http_response_code(500);
        }
        render('meldung', [
            'titel'   => 'Technischer Fehler',
            'meldung' => 'Leider ist ein technischer Fehler aufgetreten. Bitte versuchen Sie es später erneut.',
        ]);
    });

    sendeSicherheitsHeader();

    // Bereinigung bei jedem Seitenaufruf, damit die Löschfrist auch ohne Cronjob eingehalten wird.
    datenBereinigen();
}
