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

/**
 * Fehlerdetails nur anzeigen, wenn 'debug' in config.php eingeschaltet ist
 * UND die Seite auf demselben Rechner aufgerufen wird (localhost).
 */
function diagnoseAnzeigen(): bool
{
    return config('debug', false) === true
        && in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
}

/**
 * Übersetzt typische Fehler in einen verständlichen Hinweis.
 *
 * @return array{fehler:string, hinweis:string}
 */
function fehlerDiagnose(Throwable $e): array
{
    $text = $e->getMessage();
    $hinweise = [
        'could not find driver' => 'Die PHP-Erweiterung „pdo_mysql“ ist nicht aktiviert. Laragon: Rechtsklick ins Laragon-Fenster → PHP → Extensions → „pdo_mysql“ anhaken. XAMPP: in php.ini die Zeile „extension=pdo_mysql“ aktivieren. Danach den Webserver neu starten.',
        '[1049]' => 'Die Datenbank mit dem in config.php eingetragenen Namen („name“) gibt es nicht. Prüfen Sie die Schreibweise oder legen Sie die Datenbank an (z. B. in HeidiSQL oder phpMyAdmin).',
        '[1045]' => 'Benutzername oder Passwort der Datenbank sind falsch. Bei Laragon und XAMPP lautet der Benutzer meist „root“ mit leerem Passwort (\'\').',
        '[2002]' => 'Der Datenbank-Server ist nicht erreichbar. Läuft er? (Laragon: „Alle starten“, XAMPP: MySQL starten.) Tragen Sie als „host“ \'127.0.0.1\' und als „port“ 3306 ein.',
        '[2006]' => 'Der Datenbank-Server ist nicht erreichbar. Prüfen Sie, ob er läuft und ob „host“ und „port“ in config.php stimmen.',
        '[1046]' => 'In config.php ist kein Datenbankname („name“) eingetragen.',
        '[1146]' => 'Die Tabellen fehlen. Importieren Sie sql/schema.sql in die Datenbank aus config.php. Wichtig: vorher links genau diese Datenbank anklicken (sonst erscheint „No database selected“).',
        '42S02' => 'Die Tabellen fehlen. Importieren Sie sql/schema.sql in die Datenbank aus config.php. Wichtig: vorher links genau diese Datenbank anklicken (sonst erscheint „No database selected“).',
        '[1044]' => 'Der Datenbank-Benutzer darf auf diese Datenbank nicht zugreifen. Häufigste Ursache: Der Datenbankname („name“) in config.php ist falsch geschrieben oder die Datenbank wurde noch nicht angelegt. Sonst dem Benutzer Rechte geben oder lokal den Benutzer „root“ verwenden.',
    ];
    foreach ($hinweise as $muster => $hinweis) {
        if (stripos($text, $muster) !== false) {
            return ['fehler' => $text, 'hinweis' => $hinweis];
        }
    }

    return [
        'fehler'  => sprintf('%s: %s (%s, Zeile %d)', get_class($e), $text, basename($e->getFile()), $e->getLine()),
        'hinweis' => 'Bitte die Fehlermeldung oben prüfen oder an die technische Betreuung weitergeben.',
    ];
}
