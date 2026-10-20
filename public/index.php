<?php
/**
 * Startseite. In Schritt 1 nur ein Platzhalter, der prüft, ob Konfiguration
 * und Datenbankverbindung funktionieren. Das Formular "Besuch ankündigen"
 * folgt in Schritt 2.
 */

declare(strict_types=1);

use Besuchermanagement\Database;
use Besuchermanagement\Logger;

$config = require __DIR__ . '/../src/bootstrap.php';

$verbindungOk = false;
try {
    Database::verbindung($config)->query('SELECT 1 FROM ankuendigung LIMIT 1');
    $verbindungOk = true;
} catch (PDOException $e) {
    // Details nur ins Log, nie in den Browser
    Logger::fehler('Datenbankprüfung fehlgeschlagen: ' . $e->getMessage());
}

$appName = htmlspecialchars((string) $config->get('app.name', 'Besuchermanagement'), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $appName ?></title>
    <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">
</head>
<body>
<main class="container py-4">
    <h1 class="h3 mb-4"><?= $appName ?></h1>
    <?php if ($verbindungOk): ?>
        <div class="alert alert-success" role="status">
            Konfiguration und Datenbankverbindung sind in Ordnung.
            Das Formular „Besuch ankündigen“ folgt im nächsten Schritt.
        </div>
    <?php else: ?>
        <div class="alert alert-danger" role="alert">
            Die Datenbank ist nicht erreichbar oder das Schema fehlt.
            Details stehen in der Log-Datei.
        </div>
    <?php endif; ?>
</main>
</body>
</html>
