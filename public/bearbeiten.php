<?php
/**
 * Anmeldung über den persönlichen Bearbeitungslink ändern oder absagen.
 */

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

sitzungStarten();

$token = $_GET['token'] ?? '';
$anmeldung = anmeldungPerToken($token);

if (!$anmeldung) {
    http_response_code(404);
    render('meldung', [
        'titel'       => 'Anmeldung nicht gefunden',
        'meldung'     => 'Zu diesem Link gibt es keine Anmeldung. Möglicherweise wurde sie abgesagt oder nach Ablauf der Löschfrist automatisch gelöscht.',
        'zurueckLink' => 'index.php',
    ]);
    exit;
}

$eigeneUrl = 'bearbeiten.php?token=' . rawurlencode($token);
$formular = formularAusAnmeldung($anmeldung);
$fehler = [];
$absageFehler = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    postAnfragePruefen();
    $aktion = is_string($_POST['aktion'] ?? null) ? $_POST['aktion'] : 'speichern';

    if ($aktion === 'absagen') {
        if (($_POST['absage_bestaetigt'] ?? '') !== '1') {
            $absageFehler = 'Bitte bestätigen Sie die Absage mit dem Häkchen.';
        } else {
            anmeldungLoeschen((int) $anmeldung['id']);
            render('meldung', [
                'titel'       => 'Anmeldung abgesagt',
                'meldung'     => 'Die Anmeldung wurde abgesagt und alle zugehörigen Daten wurden gelöscht.',
                'zurueckLink' => 'index.php',
            ]);
            exit;
        }
    } else {
        $formular = formularAusRequest($_POST);
        if (!personenAktionAnwenden($formular, $aktion)) {
            if (rateLimitUeberschritten('aendern')) {
                http_response_code(429);
                $fehler['_allgemein'] = 'Es wurden zu viele Änderungen in kurzer Zeit gesendet. Bitte versuchen Sie es später erneut.';
            } else {
                rateLimitErfassen('aendern');
                $fehler = formularValidieren($formular);
                if (!$fehler) {
                    anmeldungAktualisieren((int) $anmeldung['id'], $formular);
                    weiterleiten($eigeneUrl . '&gespeichert=1');
                }
            }
        }
    }
}

render('seite_bearbeiten', [
    'titel'        => 'Anmeldung ändern',
    'formular'     => $formular,
    'fehler'       => $fehler,
    'absageFehler' => $absageFehler,
    'eigeneUrl'    => $eigeneUrl,
    'gespeichert'  => isset($_GET['gespeichert']) && $_SERVER['REQUEST_METHOD'] === 'GET',
    'skript'       => 'formular.js',
]);
