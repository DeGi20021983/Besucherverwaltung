<?php
/**
 * Sicherheitsfunktionen: Header, Sitzung/CSRF, Tokens, Honeypot, Rate Limiting.
 */

declare(strict_types=1);

const HONEYPOT_FELD = 'webseite';

function istHttps(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

function sendeSicherheitsHeader(): void
{
    header("Content-Security-Policy: default-src 'none'; script-src 'self'; style-src 'self'; img-src 'self'; "
        . "connect-src 'none'; font-src 'none'; form-action 'self'; base-uri 'none'; frame-ancestors 'none'");
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    header('Cross-Origin-Resource-Policy: same-origin');
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store, max-age=0');
    if (istHttps()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
    header_remove('X-Powered-By');
}

function sitzungStarten(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    session_name('besuch_sid');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => istHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** Zufälliger, nicht erratbarer Token (hex). */
function zufallsToken(int $bytes = 32): string
{
    return bin2hex(random_bytes($bytes));
}

function csrfToken(): string
{
    sitzungStarten();
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = zufallsToken();
    }

    return $_SESSION['csrf_token'];
}

function csrfFeld(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function csrfGueltig(): bool
{
    sitzungStarten();
    $gesendet = $_POST['csrf_token'] ?? '';
    $erwartet = $_SESSION['csrf_token'] ?? '';

    return is_string($gesendet) && is_string($erwartet) && $erwartet !== ''
        && hash_equals($erwartet, $gesendet);
}

function honeypotFeld(): string
{
    return '<div class="hp" aria-hidden="true"><label>Bitte leer lassen '
        . '<input type="text" name="' . HONEYPOT_FELD . '" value="" tabindex="-1" autocomplete="off"></label></div>';
}

function honeypotAusgeloest(): bool
{
    return isset($_POST[HONEYPOT_FELD]) && $_POST[HONEYPOT_FELD] !== '';
}

/**
 * Prüft CSRF-Token und Honeypot eines POST-Requests. Bricht bei Fehler mit einer Meldung ab.
 */
function postAnfragePruefen(): void
{
    if (!csrfGueltig()) {
        http_response_code(400);
        render('meldung', [
            'titel'   => 'Sitzung abgelaufen',
            'meldung' => 'Ihre Sitzung ist abgelaufen oder das Formular ist ungültig. Bitte laden Sie die Seite neu und senden Sie das Formular erneut.',
        ]);
        exit;
    }
    if (honeypotAusgeloest()) {
        http_response_code(400);
        render('meldung', [
            'titel'   => 'Anfrage abgelehnt',
            'meldung' => 'Die Anfrage konnte nicht verarbeitet werden.',
        ]);
        exit;
    }
}

/** SHA-256 eines Bearbeitungstokens – nur dieser Hash wird gespeichert. */
function tokenHash(string $token): string
{
    return hash('sha256', $token);
}

function tokenFormatGueltig(mixed $token): bool
{
    return is_string($token) && strlen($token) === 64 && ctype_xdigit($token);
}

function pforteSchluesselGueltig(mixed $schluessel): bool
{
    $erwartet = (string) config('pforte_key', '');
    if (strlen($erwartet) < 32) {
        error_log('[besucheranmeldung] pforte_key in config.php fehlt oder ist kürzer als 32 Zeichen.');
        return false;
    }

    return is_string($schluessel) && hash_equals($erwartet, $schluessel);
}

/** HMAC der IP-Adresse – die IP selbst wird nie gespeichert. */
function ipHash(): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $geheimnis = (string) config('ip_hash_secret', '');

    return hash_hmac('sha256', $ip, $geheimnis);
}

function rateLimitUeberschritten(string $aktion): bool
{
    $rl = config('rate_limit', []);
    $max = (int) ($rl['max_anfragen'] ?? 20);
    $minuten = (int) ($rl['zeitfenster_minuten'] ?? 60);

    $stmt = db()->prepare('SELECT COUNT(*) FROM rate_limit WHERE ip_hash = :ip AND aktion = :aktion AND erstellt_am >= :seit');
    $stmt->execute([
        'ip'     => ipHash(),
        'aktion' => $aktion,
        'seit'   => jetzt()->modify("-{$minuten} minutes")->format('Y-m-d H:i:s'),
    ]);

    return (int) $stmt->fetchColumn() >= $max;
}

function rateLimitErfassen(string $aktion): void
{
    $stmt = db()->prepare('INSERT INTO rate_limit (ip_hash, aktion, erstellt_am) VALUES (:ip, :aktion, :zeit)');
    $stmt->execute([
        'ip'     => ipHash(),
        'aktion' => $aktion,
        'zeit'   => jetzt()->format('Y-m-d H:i:s'),
    ]);
}
