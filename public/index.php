<?php
/**
 * Anmeldeformular für Beschäftigte (öffentlicher Link, ohne Anmeldung).
 */

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
app_web_start();

$now = jetzt();
$v = anmeldung_leer();
$f = [];
$hinweis = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v = anmeldung_aus_post($_POST);
    $geaendert = personen_aktion($v, $_POST['aktion'] ?? null);

    if ($geaendert !== null) {
        $v = $geaendert;
    } elseif (($hinweis = check_post_request()) === null) {
        $f = anmeldung_validieren($v, $now);
        if ($f === []) {
            $token = anmeldung_anlegen(db(), $v, $now);
            redirect('bestaetigung.php?token=' . $token);
        }
    }
}

render_page('anmelden', [
    'v' => $v, 'f' => $f, 'hinweis' => $hinweis, 'heute' => $now->format('Y-m-d'),
], ['title' => 'Besuch anmelden']);
