<?php
/**
 * Beispielkonfiguration.
 *
 * Kopieren nach config/config.php und anpassen:
 *     cp config/config.example.php config/config.php
 *
 * Dieses Verzeichnis liegt außerhalb des Webroots (public/) und ist
 * über den Webserver nicht erreichbar.
 */

return [
    // Datenbank-Zugang (MariaDB, Zeichensatz utf8mb4)
    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'name'     => 'besucheranmeldung',
        'user'     => 'besucheranmeldung',
        'password' => 'BITTE-AENDERN',
        // Optional statt host/port: Unix-Socket, z. B. '/run/mysqld/mysqld.sock'
        'socket'   => '',
    ],

    // Geheimer Token für die Tagesliste der Pforte (/pforte.php?key=...).
    // Mindestens 32 Zeichen, zufällig erzeugt, z. B. mit:
    //     php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'
    'pforte_key' => 'BITTE-DURCH-ZUFAELLIGEN-TOKEN-ERSETZEN',

    // Geheimer Schlüssel, mit dem IP-Adressen für das Rate Limiting gehasht werden (HMAC).
    // Ebenfalls zufällig erzeugen (gleicher Befehl wie oben, aber anderer Wert!).
    'ip_hash_secret' => 'BITTE-DURCH-ZUFAELLIGEN-WERT-ERSETZEN',

    // Öffentliche Basis-URL der Anwendung ohne abschließenden Schrägstrich.
    // Wird für den Bearbeitungslink auf der Bestätigungsseite verwendet.
    'base_url' => 'https://besuch.example.de',

    // Zeitzone
    'timezone' => 'Europe/Berlin',

    // Löschfrist nach dem Besuchstermin (Stunden) – gilt auch für Rate-Limit-Daten
    'loeschfrist_stunden' => 48,

    // Rate Limiting: höchstens so viele Absendungen je IP-Adresse im Zeitfenster
    'rate_limit' => [
        'max_anfragen'        => 20,
        'zeitfenster_minuten' => 60,
    ],

    // Name, der im Seitenkopf angezeigt wird
    'app_name' => 'Besucheranmeldung',
];
