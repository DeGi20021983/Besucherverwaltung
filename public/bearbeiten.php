<?php
/**
 * Anmeldung über den persönlichen Bearbeitungslink ändern oder absagen.
 */

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
app_web_start();

if (isset($_GET['abgesagt'])) {
    render_meldung(200, 'Anmeldung abgesagt',
        'Die Anmeldung und alle zugehörigen Daten wurden gelöscht. Der Pfortendienst sieht diesen Besuch nicht mehr.',
        ['href' => 'index.php', 'text' => 'Neuen Besuch anmelden']);
}

$token = token_from_request('token');
$a = $token !== null ? anmeldung_per_token(db(), $token) : null;
if ($a === null) {
    render_meldung(404, 'Anmeldung nicht gefunden',
        'Die Anmeldung wurde abgesagt oder ist bereits automatisch gelöscht worden.',
        ['href' => 'index.php', 'text' => 'Neuen Besuch anmelden']);
}

$now = jetzt();
$selbst = 'bearbeiten.php?token=' . $token;
$v = anmeldung_als_formular($a);
$f = [];
$hinweis = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aktion = $_POST['aktion'] ?? null;

    if ($aktion === 'absagen') {
        if (($hinweis = check_post_request()) === null) {
            anmeldung_loeschen(db(), (int) $a['id']);
            redirect('bearbeiten.php?abgesagt=1');
        }
    } else {
        $v = anmeldung_aus_post($_POST);
        $geaendert = personen_aktion($v, $aktion);

        if ($geaendert !== null) {
            $v = $geaendert;
        } elseif (($hinweis = check_post_request()) === null) {
            $f = anmeldung_validieren($v, $now);
            if ($f === []) {
                anmeldung_aendern(db(), (int) $a['id'], $v, $now);
                redirect($selbst . '&gespeichert=1');
            }
        }
    }
}

render_page('bearbeiten', [
    'v' => $v, 'f' => $f, 'hinweis' => $hinweis, 'heute' => $now->format('Y-m-d'),
    'selbst' => $selbst, 'gespeichert' => isset($_GET['gespeichert']) && $_SERVER['REQUEST_METHOD'] === 'GET',
], ['title' => 'Anmeldung ändern']);
