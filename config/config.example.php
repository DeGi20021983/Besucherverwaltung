<?php
/**
 * Beispielkonfiguration.
 *
 * Kopieren nach config/config.php und anpassen:
 *   cp config/config.example.php config/config.php
 *
 * Dieses Verzeichnis liegt außerhalb des öffentlichen Verzeichnisses (public/)
 * und darf vom Webserver nicht ausgeliefert werden.
 */

return [
    // Datenbank-Zugang (MariaDB)
    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'name'     => 'besucher',
        'user'     => 'besucher',
        'password' => 'BITTE-AENDERN',
    ],

    // Öffentliche Adresse der Anwendung, ohne abschließenden Schrägstrich.
    // Wird für den Bearbeitungslink auf der Bestätigungsseite verwendet.
    'base_url' => 'https://besuch.example.de',

    // Geheimer Token für die Tagesliste der Pforte (mindestens 32 Zeichen).
    // Erzeugen mit:  php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'
    'pforte_token' => '',

    // Geheimer Schlüssel für den Hash der IP-Adressen (Rate Limiting).
    // Erzeugen wie oben, aber einen anderen Wert verwenden.
    'ip_hash_key' => '',

    // Zeitzone der Anwendung
    'timezone' => 'Europe/Berlin',

    // Rate Limiting: höchstens so viele Absendungen (Anmelden, Ändern, Absagen)
    // pro IP-Adresse innerhalb des Zeitfensters (Minuten).
    'rate_limit' => [
        'max'     => 20,
        'minuten' => 60,
    ],

    // Name des Hauses, oben rechts im Seitenkopf
    'hausname' => 'Behördenhaus',
];
