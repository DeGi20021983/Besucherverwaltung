<?php
/**
 * Tagesliste für den Pfortendienst, geschützt durch den geheimen Token
 * aus der Konfiguration: /pforte.php?key=...
 */

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
send_security_headers();

$erwartet = (string) config('pforte_token', '');
$key = $_GET['key'] ?? '';
if (strlen($erwartet) < 32 || !is_string($key) || !hash_equals($erwartet, $key)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "403 – Zugriff verweigert.\n";
    exit;
}

app_web_start();

$heute = jetzt();
$zeilen = tagesliste(db(), $heute);

// Für die Smartphone-Ansicht: eine Karte pro Anmeldung
$besuche = [];
foreach ($zeilen as $z) {
    $besuche[$z['id']] ??= $z + ['personen' => []];
    $besuche[$z['id']]['personen'][] = $z['name'];
}

render_page('pforte', [
    'heute' => $heute, 'zeilen' => $zeilen, 'besuche' => array_values($besuche),
], ['title' => 'Tagesliste', 'bereich' => 'Pforte', 'breit' => true]);
