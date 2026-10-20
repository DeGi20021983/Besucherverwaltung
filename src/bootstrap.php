<?php
/**
 * Gemeinsamer Einstieg für alle Seiten und Cron-Skripte:
 * Autoloader, Konfiguration, Zeitzone und Fehlerbehandlung.
 */

declare(strict_types=1);

use Besuchermanagement\Config;
use Besuchermanagement\Logger;

const APP_ROOT = __DIR__ . '/..';

// Fehler nie im Browser anzeigen, sondern protokollieren.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

spl_autoload_register(static function (string $klasse): void {
    $praefix = 'Besuchermanagement\\';
    if (!str_starts_with($klasse, $praefix)) {
        return;
    }
    $datei = __DIR__ . '/' . str_replace('\\', '/', substr($klasse, strlen($praefix))) . '.php';
    if (is_file($datei)) {
        require $datei;
    }
});

/**
 * Letzte Rettung bei unerwarteten Fehlern: neutrale Fehlerseite bzw.
 * Meldung auf der Konsole, Details nur im Log.
 */
function fehlerseite(Throwable $e): never
{
    Logger::fehler(sprintf('%s: %s in %s:%d', $e::class, $e->getMessage(), $e->getFile(), $e->getLine()));

    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, 'Fehler: ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!doctype html><html lang="de"><head><meta charset="utf-8"><title>Fehler</title></head><body>'
        . '<h1>Es ist ein Fehler aufgetreten</h1>'
        . '<p>Bitte versuchen Sie es später erneut. Wenn das Problem bleibt, wenden Sie sich an die IT.</p>'
        . '</body></html>';
    exit;
}

set_exception_handler('fehlerseite');
set_error_handler(static function (int $nr, string $text, string $datei, int $zeile): bool {
    if (!(error_reporting() & $nr)) {
        return false;
    }
    throw new ErrorException($text, 0, $nr, $datei, $zeile);
});

$config = Config::ausDatei(APP_ROOT . '/config/config.php');

Logger::setDatei($config->get('log.datei'));
date_default_timezone_set((string) $config->get('app.zeitzone', 'Europe/Berlin'));

if ($config->get('app.debug', false) === true && PHP_SAPI !== 'cli') {
    ini_set('display_errors', '1');
}

return $config;
