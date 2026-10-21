<?php
/**
 * Bestätigungsseite nach dem Absenden mit persönlichem Bearbeitungslink.
 */

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

sitzungStarten();

$token = $_SESSION['bestaetigung_token'] ?? null;
$anmeldung = anmeldungPerToken($token);

if (!$anmeldung) {
    render('meldung', [
        'titel'       => 'Keine Bestätigung vorhanden',
        'meldung'     => 'Für diese Sitzung liegt keine Anmeldung (mehr) vor.',
        'zurueckLink' => 'index.php',
    ]);
    exit;
}

render('seite_bestaetigung', [
    'titel'         => 'Anmeldung gespeichert',
    'anmeldung'     => $anmeldung,
    'bearbeitenUrl' => basisUrl() . '/bearbeiten.php?token=' . rawurlencode($token),
    'skript'        => 'bestaetigung.js',
]);
