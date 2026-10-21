<?php
/**
 * Tagesliste für den Pfortendienst – geschützt durch den geheimen Token aus der Konfiguration.
 * Aufruf: /pforte.php?key=<pforte_key>
 */

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$schluessel = $_GET['key'] ?? '';

if (!pforteSchluesselGueltig($schluessel)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "403 – Zugriff verweigert\n";
    exit;
}

$heute = heute();

render('seite_pforte', [
    'titel'        => 'Tagesliste Pforte',
    'seitenKlasse' => 'seite-pforte',
    'heute'        => $heute,
    'besuche'      => besucheAmTag($heute),
    'stand'        => jetzt(),
    'eigeneUrl'    => 'pforte.php?key=' . rawurlencode($schluessel),
    'skript'       => 'pforte.js',
]);
