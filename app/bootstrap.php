<?php
/**
 * Gemeinsamer Einstieg für alle Seiten und für cleanup.php.
 *
 * Lädt die Konfiguration, setzt Zeitzone und Fehlerausgabe und bindet die
 * Bibliotheken ein. Webseiten rufen danach app_web_start() auf.
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

$configFile = APP_ROOT . '/config/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Die Anwendung ist noch nicht eingerichtet: config/config.php fehlt.\n";
    exit;
}

$GLOBALS['APP_CONFIG'] = require $configFile;

date_default_timezone_set(config('timezone', 'Europe/Berlin'));
mb_internal_encoding('UTF-8');

require APP_ROOT . '/app/lib/db.php';
require APP_ROOT . '/app/lib/security.php';
require APP_ROOT . '/app/lib/format.php';
require APP_ROOT . '/app/lib/anmeldung.php';
require APP_ROOT . '/app/lib/cleanup.php';
require APP_ROOT . '/app/lib/view.php';

/**
 * Liest einen Konfigurationswert, verschachtelte Schlüssel mit Punkt ("db.host").
 */
function config(string $key, mixed $default = null): mixed
{
    $value = $GLOBALS['APP_CONFIG'];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

/** Aktueller Zeitpunkt in der Zeitzone der Anwendung. */
function jetzt(): DateTimeImmutable
{
    return new DateTimeImmutable('now', new DateTimeZone(date_default_timezone_get()));
}

/**
 * Start für jede Webseite: Sicherheits-Header, Session (nur für das
 * CSRF-Token) und Bereinigung abgelaufener Daten.
 */
function app_web_start(): void
{
    send_security_headers();
    session_start_secure();

    try {
        cleanup_run(db(), jetzt());
    } catch (Throwable $e) {
        // Die Bereinigung darf die Seite nicht blockieren; cleanup.php per
        // Cronjob holt sie nach. Es wird keine Nutzereingabe protokolliert.
        error_log('Besucheranmeldung: Bereinigung fehlgeschlagen: ' . $e->getMessage());
    }
}
