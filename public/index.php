<?php
/**
 * Anmeldeformular für Beschäftigte (öffentlicher Link, ohne Login).
 */

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

sitzungStarten();

$formular = leeresFormular();
$fehler = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    postAnfragePruefen();
    $formular = formularAusRequest($_POST);
    $aktion = is_string($_POST['aktion'] ?? null) ? $_POST['aktion'] : 'speichern';

    if (!personenAktionAnwenden($formular, $aktion)) {
        if (rateLimitUeberschritten('anmelden')) {
            http_response_code(429);
            $fehler['_allgemein'] = 'Es wurden zu viele Anmeldungen in kurzer Zeit gesendet. Bitte versuchen Sie es später erneut.';
        } else {
            rateLimitErfassen('anmelden');
            $fehler = formularValidieren($formular);
            if (!$fehler) {
                $token = anmeldungErstellen($formular);
                session_regenerate_id(true);
                $_SESSION['bestaetigung_token'] = $token;
                weiterleiten('bestaetigung.php');
            }
        }
    }
}

render('seite_anmelden', [
    'titel'    => 'Besuch anmelden',
    'formular' => $formular,
    'fehler'   => $fehler,
    'skript'   => 'formular.js',
]);
