<?php
/**
 * Vorlage für die Konfiguration des Besuchermanagements.
 *
 * Kopieren nach config/config.php und anpassen:
 *   cp config/config.example.php config/config.php
 *
 * config/config.php liegt außerhalb des Web-Roots (/public) und wird nicht
 * ins Git-Repository eingecheckt.
 */

declare(strict_types=1);

return [
    'app' => [
        // Name in Titelzeile und E-Mails
        'name'     => 'Besuchermanagement',
        'zeitzone' => 'Europe/Berlin',
        // true nur zur Fehlersuche auf einem Testsystem: zeigt PHP-Fehler im Browser
        'debug'    => false,
    ],

    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'name'     => 'besuchermanagement',
        'user'     => 'besuchermanagement',
        'password' => 'BITTE-AENDERN',
        'charset'  => 'utf8mb4',
    ],

    // Pfortenansicht (pforte.php) und Excel-Export (export.php) nur von diesen
    // IP-Adressen bzw. Netzen (CIDR) erreichbar. Leeres Array = keine Einschränkung.
    'pforte_ip_allowlist' => [
        // '10.0.5.21',
        // '10.0.5.0/28',
    ],

    'mail' => [
        // 'smtp' (PHPMailer in /lib) oder 'mail' (PHP-Funktion mail() als Fallback)
        'transport'  => 'smtp',
        'smtp_host'  => 'smtp.example.local',
        'smtp_port'  => 587,
        // 'tls' (STARTTLS), 'ssl' (SMTPS) oder '' (unverschlüsselt)
        'smtp_secure' => 'tls',
        'smtp_user'  => '',
        'smtp_password' => '',
        'absender'      => 'besuchermanagement@example.local',
        'absender_name' => 'Besuchermanagement',
        // Empfänger der Pforte (eine oder mehrere Adressen)
        'pforte_empfaenger' => [
            'pforte@example.local',
        ],
        // Uhrzeit (HH:MM) für den Versand der Tagesliste per Cron
        'tagesliste_uhrzeit' => '06:30',
        // Sofort-Mail an die Pforte bei Ankündigungen für den laufenden Tag
        'sofort_benachrichtigung' => true,
    ],

    'datenschutz' => [
        // Löschung: Stunden nach dem angekündigten Besuchszeitpunkt (Tag + Uhrzeit)
        'aufbewahrung_stunden' => 24,
        // Hinweistext unter dem Formular
        'hinweis' => 'Die hier eingegebenen personenbezogenen Daten werden ausschließlich '
            . 'zur Anmeldung des Besuchs beim Pfortendienst verwendet und 24 Stunden nach '
            . 'dem angekündigten Besuchszeitpunkt automatisch gelöscht.',
    ],

    'rate_limit' => [
        // Maximal so viele Ankündigungen je IP-Adresse innerhalb des Zeitfensters
        'max_anfragen'     => 20,
        'fenster_sekunden' => 3600,
        // Geheimer Zufallswert, mit dem IP-Adressen vor dem Speichern gehasht
        // werden. Erzeugen z. B. mit: php -r "echo bin2hex(random_bytes(32));"
        'ip_salt' => 'BITTE-ZUFALLSWERT-EINTRAGEN',
    ],

    'log' => [
        // Log-Datei (ohne personenbezogene Daten). Verzeichnis muss für den
        // Webserver-Benutzer beschreibbar sein.
        'datei' => __DIR__ . '/../storage/logs/app.log',
    ],
];
