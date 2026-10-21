<?php
/**
 * Sicherheit: Header, Ausgabe-Escaping, CSRF, Tokens, Honeypot, Rate Limiting.
 */

declare(strict_types=1);

function send_security_headers(): void
{
    header("Content-Security-Policy: default-src 'none'; script-src 'self'; style-src 'self'; "
        . "img-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), interest-cohort=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    header('X-Robots-Tag: noindex, nofollow');
    // Seiten enthalten Tokens oder Besucherdaten und werden nicht zwischengespeichert.
    header('Cache-Control: no-store, max-age=0');
    if (is_https()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

/** HTML-Escaping für jede Ausgabe von Nutzereingaben. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

function session_start_secure(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', '7200');
    session_name('besuch_sid');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

/** Zufälliger, nicht erratbarer Token (256 Bit, hexadezimal). */
function new_token(): string
{
    return bin2hex(random_bytes(32));
}

/** In der Datenbank wird nur der Hash eines Bearbeitungs-Tokens gespeichert. */
function token_hash(string $token): string
{
    return hash('sha256', $token);
}

/** Ein Token aus der URL ist nur gültig, wenn er genau 64 Hex-Zeichen hat. */
function token_from_request(string $name): ?string
{
    $token = $_GET[$name] ?? '';
    return is_string($token) && preg_match('/^[a-f0-9]{64}$/', $token) ? $token : null;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf']) || !is_string($_SESSION['csrf'])) {
        $_SESSION['csrf'] = new_token();
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $sent = $_POST['csrf'] ?? '';
    return is_string($sent) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $sent);
}

/**
 * Honeypot: ein für Menschen unsichtbares Feld. Ist es ausgefüllt, stammt
 * die Anfrage sehr wahrscheinlich von einem Bot.
 */
function honeypot_field(string $id = 'website'): string
{
    return '<div class="hp" aria-hidden="true"><label for="' . e($id) . '">Website (bitte leer lassen)</label>'
        . '<input type="text" id="' . e($id) . '" name="website" value="" tabindex="-1" autocomplete="off"></div>';
}

function honeypot_triggered(): bool
{
    return trim((string) ($_POST['website'] ?? '')) !== '';
}

/**
 * HMAC der IP-Adresse. Die IP selbst wird nirgends gespeichert; ohne den
 * geheimen Schlüssel aus der Konfiguration lässt sich der Hash nicht
 * zurückrechnen.
 */
function ip_hash(): string
{
    $key = (string) config('ip_hash_key', '');
    if (strlen($key) < 32) {
        throw new RuntimeException('ip_hash_key in config/config.php fehlt oder ist zu kurz.');
    }
    return hash_hmac('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? ''), $key);
}

/**
 * Zählt eine Absendung und prüft das Limit. Liefert false, wenn die
 * IP-Adresse im Zeitfenster schon zu viele Absendungen hatte.
 */
function rate_limit_allow(PDO $db, DateTimeImmutable $now): bool
{
    $hash = ip_hash();
    $max = max(1, (int) config('rate_limit.max', 20));
    $since = $now->modify('-' . max(1, (int) config('rate_limit.minuten', 60)) . ' minutes');

    $stmt = $db->prepare('SELECT COUNT(*) FROM rate_limit WHERE ip_hash = :h AND erstellt_am > :seit');
    $stmt->execute([':h' => $hash, ':seit' => $since->format('Y-m-d H:i:s')]);
    if ((int) $stmt->fetchColumn() >= $max) {
        return false;
    }

    $stmt = $db->prepare('INSERT INTO rate_limit (ip_hash, erstellt_am) VALUES (:h, :t)');
    $stmt->execute([':h' => $hash, ':t' => $now->format('Y-m-d H:i:s')]);
    return true;
}

/**
 * Gemeinsame Prüfung jeder POST-Anfrage. Liefert eine allgemeine
 * Fehlermeldung oder null, wenn die Anfrage verarbeitet werden darf.
 */
function check_post_request(): ?string
{
    if (!csrf_valid()) {
        return 'Das Formular ist abgelaufen. Bitte senden Sie es erneut ab.';
    }
    if (honeypot_triggered()) {
        return 'Die Anfrage konnte nicht verarbeitet werden.';
    }
    if (!rate_limit_allow(db(), jetzt())) {
        http_response_code(429);
        return 'Es wurden zu viele Anfragen gesendet. Bitte versuchen Sie es später erneut.';
    }
    return null;
}
