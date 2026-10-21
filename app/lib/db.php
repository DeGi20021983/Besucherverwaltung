<?php
/**
 * Datenbankverbindung (PDO, MariaDB, utf8mb4).
 * Alle Abfragen der Anwendung laufen über Prepared Statements.
 */

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        config('db.host', 'localhost'),
        (int) config('db.port', 3306),
        config('db.name', 'besucher')
    );

    $pdo = new PDO($dsn, (string) config('db.user'), (string) config('db.password'), [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

    return $pdo;
}
