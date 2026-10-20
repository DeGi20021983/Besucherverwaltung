<?php
/**
 * Bestätigungsseite nach dem Absenden mit dem persönlichen Bearbeitungslink.
 */

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
app_web_start();

$token = token_from_request('token');
$a = $token !== null ? anmeldung_per_token(db(), $token) : null;
if ($a === null) {
    render_meldung(404, 'Anmeldung nicht gefunden',
        'Die Anmeldung wurde abgesagt oder ist bereits automatisch gelöscht worden.',
        ['href' => 'index.php', 'text' => 'Neuen Besuch anmelden']);
}

render_page('bestaetigung', [
    'a' => $a,
    'link' => app_url('bearbeiten.php?token=' . $token),
    'bearbeiten' => 'bearbeiten.php?token=' . $token,
], ['title' => 'Anmeldung gespeichert']);
