<?php
/**
 * Allgemeine Hilfsfunktionen: Konfiguration, Datenbank, Ausgabe, Datum.
 */

declare(strict_types=1);

const EINRICHTUNG_ARTEN = [
    'behoerde' => 'Behörde',
    'firma'    => 'Firma',
];

function config(string $schluessel, mixed $standard = null): mixed
{
    return $GLOBALS['APP_CONFIG'][$schluessel] ?? $standard;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $c = config('db', []);
    if (!empty($c['socket'])) {
        $dsn = sprintf('mysql:unix_socket=%s;dbname=%s;charset=utf8mb4', $c['socket'], $c['name']);
    } else {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'] ?? 'localhost', (int) ($c['port'] ?? 3306), $c['name']);
    }

    $pdo = new PDO($dsn, (string) $c['user'], (string) $c['password'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}

/** HTML-Ausgabe mit XSS-Schutz. */
function e(mixed $wert): string
{
    return htmlspecialchars((string) $wert, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

function jetzt(): DateTimeImmutable
{
    return new DateTimeImmutable('now');
}

function heute(): DateTimeImmutable
{
    return new DateTimeImmutable('today');
}

/** z. B. „Mittwoch, 21. Oktober 2026“ */
function datumLang(DateTimeInterface $datum): string
{
    static $tage = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
    static $monate = [1 => 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];

    return sprintf(
        '%s, %d. %s %s',
        $tage[(int) $datum->format('w')],
        (int) $datum->format('j'),
        $monate[(int) $datum->format('n')],
        $datum->format('Y')
    );
}

function einrichtungArtLabel(string $art): string
{
    return EINRICHTUNG_ARTEN[$art] ?? $art;
}

/** Basis-URL für absolute Links (aus der Konfiguration, nicht aus dem Host-Header). */
function basisUrl(): string
{
    return rtrim((string) config('base_url', ''), '/');
}

function weiterleiten(string $pfad): never
{
    header('Location: ' . $pfad, true, 303);
    exit;
}

/**
 * Rendert ein Template aus templates/ innerhalb des Layouts.
 * Variablen: 'titel' (Seitentitel), 'layout' (false für Seiten ohne Layout).
 */
function render(string $template, array $daten = []): void
{
    $daten['titel'] ??= (string) config('app_name', 'Besucheranmeldung');
    extract($daten, EXTR_SKIP);

    ob_start();
    require APP_ROOT . '/templates/' . $template . '.php';
    $inhalt = ob_get_clean();

    require APP_ROOT . '/templates/layout.php';
}

/** Datenschutzhinweis aus content/datenschutzhinweis.txt als Absätze. */
function datenschutzAbsaetze(): array
{
    $datei = APP_ROOT . '/content/datenschutzhinweis.txt';
    if (!is_readable($datei)) {
        return [];
    }
    $text = str_replace("\r\n", "\n", (string) file_get_contents($datei));
    $absaetze = preg_split('/\n\s*\n/', trim($text)) ?: [];

    return array_values(array_filter(array_map('trim', $absaetze), 'strlen'));
}
