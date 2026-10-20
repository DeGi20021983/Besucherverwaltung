<?php

declare(strict_types=1);

namespace Besuchermanagement;

use DateTimeImmutable;
use PDO;

/**
 * Stellt die PDO-Verbindung zur MariaDB her (eine Verbindung pro Request).
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function verbindung(Config $config): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            (string) $config->get('db.host', 'localhost'),
            (int) $config->get('db.port', 3306),
            (string) $config->get('db.name'),
            (string) $config->get('db.charset', 'utf8mb4'),
        );

        $pdo = new PDO($dsn, (string) $config->get('db.user'), (string) $config->get('db.password'), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // echte Prepared Statements auf dem Server, keine Emulation
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
        ]);

        // Sitzungs-Zeitzone der Datenbank an PHP angleichen (Europe/Berlin inkl.
        // Sommerzeit), damit NOW()/CURDATE() zu den PHP-Zeiten passen.
        $offset = (new DateTimeImmutable('now'))->format('P');
        $pdo->prepare('SET time_zone = ?')->execute([$offset]);

        return self::$pdo = $pdo;
    }
}
