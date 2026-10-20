# Besuchermanagement

Schlanke Web-Anwendung für ein Behördenhaus: Beschäftigte kündigen externe
Besucher an, der Pfortendienst erhält pro Tag eine druckbare Excel-Liste.
Reines PHP ohne Framework und ohne Composer.

> Stand: **Schritt 1 von 6** (Ordnerstruktur, Konfiguration, Datenbankschema,
> Datenbankverbindung). Formular, Pfortenansicht, Export, E-Mail und Cron
> folgen in den nächsten Schritten; die vollständige Installationsanleitung
> und Testliste kommen in Schritt 6.

## Voraussetzungen

- PHP 8.5 mit den Erweiterungen `pdo_mysql`, `zip`, `mbstring`
- MariaDB 11.8 LTS
- Apache 2.4 mit `mod_rewrite` und `AllowOverride All` (für `.htaccess`)

## Ordnerstruktur

```
public/        Web-Root (DocumentRoot): index.php, pforte.php, export.php, assets/
  assets/bootstrap/  Bootstrap 5.3.8, lokal eingebunden (kein CDN)
src/           PHP-Klassen (Namespace Besuchermanagement) und bootstrap.php
lib/           manuell eingebundene Bibliotheken (z. B. PHPMailer, ohne Composer)
config/        config.php (nicht im Git) und config.example.php
sql/           schema.sql
cron/          Cron-Skripte (Tagesliste, Löschung)
storage/logs/  Log-Datei ohne personenbezogene Daten
```

Nur `public/` darf vom Webserver ausgeliefert werden. Alle anderen
Verzeichnisse enthalten zusätzlich eine `.htaccess` mit `Require all denied`.

## Installation (bisheriger Stand)

1. Dateien auf den Server kopieren, z. B. nach `/var/www/besuchermanagement`.
2. Apache-VirtualHost mit `DocumentRoot /var/www/besuchermanagement/public`
   und `AllowOverride All` für dieses Verzeichnis.
3. Datenbank und Benutzer anlegen:
   ```sql
   CREATE DATABASE besuchermanagement CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'besuchermanagement'@'localhost' IDENTIFIED BY 'sicheres-passwort';
   GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, REFERENCES
       ON besuchermanagement.* TO 'besuchermanagement'@'localhost';
   ```
4. Schema (mit Beispieldaten für heute und morgen) einspielen:
   ```sh
   mariadb -u besuchermanagement -p besuchermanagement < sql/schema.sql
   ```
5. Konfiguration anlegen und Zugangsdaten eintragen:
   ```sh
   cp config/config.example.php config/config.php
   ```
6. `storage/logs/` für den Webserver-Benutzer beschreibbar machen:
   ```sh
   chown www-data storage/logs
   ```
7. Startseite aufrufen. Sie meldet, ob Konfiguration und Datenbankverbindung
   in Ordnung sind; Fehlerdetails stehen nur in `storage/logs/app.log`.
