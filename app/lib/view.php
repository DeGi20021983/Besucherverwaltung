<?php
/**
 * Einfaches Rendern der Vorlagen aus app/views.
 */

declare(strict_types=1);

/**
 * Gibt eine Seite im gemeinsamen Layout aus.
 *
 * @param string $view   Vorlage in app/views (ohne .php)
 * @param array  $vars   Variablen für die Vorlage
 * @param array  $layout title, bereich (Text rechts im Kopf), breit (bool)
 */
function render_page(string $view, array $vars, array $layout): void
{
    $inhalt = render($view, $vars);
    echo render('layout', $layout + ['inhalt' => $inhalt]);
}

function render(string $view, array $vars = []): string
{
    extract($vars, EXTR_SKIP);
    ob_start();
    require APP_ROOT . '/app/views/' . $view . '.php';
    return (string) ob_get_clean();
}

/** Schlichte Fehlerseite (z. B. 403, 404, 429). */
function render_meldung(int $status, string $titel, string $text, ?array $link = null, bool $breit = false): void
{
    http_response_code($status);
    render_page('meldung', ['titel' => $titel, 'text' => $text, 'link' => $link], [
        'title' => $titel, 'breit' => $breit,
    ]);
    exit;
}

function redirect(string $url): never
{
    header('Location: ' . $url, true, 303);
    exit;
}

/** Absolute URL zu einer Seite der Anwendung. */
function app_url(string $pfad): string
{
    return rtrim((string) config('base_url', ''), '/') . '/' . ltrim($pfad, '/');
}

/** Kreis-Icon für Fehlermeldungen (rein dekorativ). */
function icon_fehler(int $size = 16): string
{
    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
        . ' stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/>'
        . '<path d="M12 7v6M12 16.5v.5"/></svg>';
}

/** Fehlermeldung unter einem Feld, sofern vorhanden. */
function feld_fehler(?string $meldung, string $id): string
{
    if ($meldung === null || $meldung === '') {
        return '';
    }
    return '<p class="err" id="' . e($id) . '">' . icon_fehler() . '<span>' . e($meldung) . '</span></p>';
}

/** Attribute für ein Eingabefeld mit möglichem Fehler. */
function feld_attr(?string $meldung, string $fehlerId): string
{
    return $meldung ? ' aria-invalid="true" aria-describedby="' . e($fehlerId) . '"' : '';
}

/** Datenschutzhinweis aus der editierbaren Datei (HTML-Kommentare werden entfernt). */
function datenschutzhinweis(): string
{
    $datei = APP_ROOT . '/app/texte/datenschutzhinweis.html';
    $html = is_file($datei) ? (string) file_get_contents($datei) : '';
    return trim(preg_replace('/<!--.*?-->/s', '', $html) ?? '');
}
