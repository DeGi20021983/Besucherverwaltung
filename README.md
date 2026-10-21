# Besucheranmeldung

Schlanke Web-Anwendung zur Anmeldung externer Besucherinnen und Besucher – reines PHP ohne Framework und ohne Composer, MariaDB, Oberfläche auf Deutsch, Zeitzone Europe/Berlin.

- **Beschäftigte** kündigen Besuche über ein öffentliches Formular an (ohne Login) und erhalten einen persönlichen Bearbeitungslink zum Ändern oder Absagen.
- **Pfortendienst** sieht über einen geheimen Link eine druckbare Tagesliste (A4 quer) aller Besuche des heutigen Tages.
- Alle Besucherdaten und Rate-Limit-Daten werden **48 Stunden nach dem Besuchstermin automatisch gelöscht**.

## Ordnerstruktur

```
├── public/                     ← einziges öffentlich erreichbares Verzeichnis (DocumentRoot)
│   ├── index.php               Anmeldeformular (Link für Beschäftigte)
│   ├── bestaetigung.php        Bestätigung mit persönlichem Bearbeitungslink
│   ├── bearbeiten.php          Ändern / Absagen über ?token=…
│   ├── pforte.php              Tagesliste für die Pforte über ?key=…
│   ├── .htaccess               Apache-Regeln (Header, Zugriffsschutz, HTTPS)
│   └── assets/
│       ├── css/style.css       Bildschirm-Layout (responsiv)
│       ├── css/print.css       Druck-Stylesheet (A4 quer, nur Tabelle)
│       └── js/                 formular.js, bestaetigung.js, pforte.js
├── src/                        Anwendungslogik (nicht öffentlich)
│   ├── bootstrap.php           Konfiguration, Header, Fehlerbehandlung, Bereinigung je Aufruf
│   ├── helpers.php             DB-Verbindung (PDO), Ausgabe, Datum, Templates
│   ├── security.php            CSRF, Tokens, Honeypot, Rate Limiting, Sicherheits-Header
│   ├── anmeldungen.php         Datenbankzugriffe (Prepared Statements), Bereinigung
│   └── validierung.php         Serverseitige Validierung
├── templates/                  HTML-Vorlagen
├── config/
│   ├── config.example.php      Vorlage: DB-Zugang, Pforten-Token, Zeitzone …
│   └── config.php              echte Konfiguration (nicht im Git!)
├── content/
│   └── datenschutzhinweis.txt  Datenschutzhinweis (Platzhalter, frei editierbar)
├── sql/schema.sql              Datenbankschema
├── cleanup.php                 Bereinigung für den Cronjob (nur CLI)
├── deploy/
│   ├── nginx.conf.example      Beispielkonfiguration nginx + PHP-FPM
│   └── apache-vhost.conf.example
└── .htaccess                   sperrt alles, falls der DocumentRoot falsch gesetzt ist
```

## Voraussetzungen

- PHP 8.2 oder neuer (empfohlen: aktuelle stabile Version, z. B. 8.4) mit den Erweiterungen `pdo_mysql` und `mbstring`
- MariaDB 10.6 oder neuer (empfohlen: aktuelle LTS, z. B. 11.4/11.8)
- Apache 2.4 (mit `mod_headers`, `mod_rewrite`, `AllowOverride All`) **oder** nginx mit PHP-FPM
- **HTTPS ist Pflicht.** Die Anwendung ist für den Betrieb ausschließlich über HTTPS vorgesehen, da Bearbeitungs- und Pforten-Token in der URL stehen. Ein kostenloses Zertifikat gibt es z. B. über Let's Encrypt (`certbot`).

## Installation

1. **Dateien hochladen**, z. B. nach `/var/www/besucheranmeldung`.
   Der Webserver darf **nur** auf `public/` zeigen. `config/`, `src/`, `templates/`, `sql/` usw. liegen außerhalb des Webroots.

2. **Datenbank anlegen**

   ```sql
   CREATE DATABASE besucheranmeldung CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'besucheranmeldung'@'localhost' IDENTIFIED BY 'ein-sicheres-passwort';
   GRANT SELECT, INSERT, UPDATE, DELETE ON besucheranmeldung.* TO 'besucheranmeldung'@'localhost';
   FLUSH PRIVILEGES;
   ```

   Schema importieren (mit einem Benutzer, der Tabellen anlegen darf):

   ```bash
   mariadb -u root -p besucheranmeldung < sql/schema.sql
   ```

3. **Konfiguration anlegen**

   ```bash
   cp config/config.example.php config/config.php
   chmod 640 config/config.php        # nur Eigentümer + Webserver-Gruppe
   ```

   In `config/config.php` eintragen: Datenbank-Zugang, `pforte_key`, `ip_hash_secret`, `base_url` (z. B. `https://besuch.example.de`).

4. **Pforten-Token erzeugen** (und ebenso einen zweiten, anderen Wert für `ip_hash_secret`):

   ```bash
   php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'
   # alternativ: openssl rand -hex 32
   ```

   Den Wert als `pforte_key` in `config/config.php` eintragen. Der Token muss mindestens 32 Zeichen lang sein, sonst liefert die Tagesliste immer HTTP 403. Zum Sperren eines bekannt gewordenen Links einfach einen neuen Token eintragen.

5. **Webserver einrichten**
   - **Apache:** DocumentRoot auf `public/` setzen, `AllowOverride All`. Die Regeln stehen in `public/.htaccess`. Beispiel-VirtualHost: `deploy/apache-vhost.conf.example`.
   - **nginx:** siehe `deploy/nginx.conf.example` (Pfade, Domain und PHP-FPM-Socket anpassen).

6. **Datenschutzhinweis anpassen:** `content/datenschutzhinweis.txt` bearbeiten (Absätze durch eine Leerzeile trennen).

7. **Cronjob einrichten** (siehe unten).

## Die beiden Links

| Für | Link |
|---|---|
| **Beschäftigte** (Anmeldeformular) | `https://besuch.example.de/` |
| **Pforte** (Tagesliste) | `https://besuch.example.de/pforte.php?key=<pforte_key aus config.php>` |

Ohne gültigen `key` antwortet `pforte.php` mit **HTTP 403** und zeigt nichts an. Den Pforten-Link nur an den Pfortendienst geben (z. B. als Lesezeichen am Pforten-PC).

Nach dem Absenden erhalten Beschäftigte zusätzlich ihren persönlichen Bearbeitungslink `https://besuch.example.de/bearbeiten.php?token=…` zum Ändern oder Absagen.

## Cronjob für die Datenlöschung

`cleanup.php` löscht alle Anmeldungen (inkl. Personen) und Rate-Limit-Einträge, die älter als 48 Stunden nach dem Besuchstermin sind. Es ist nur über die Kommandozeile aufrufbar. Beispiel für einen stündlichen Lauf (`crontab -e` des Webserver-Benutzers):

```cron
0 * * * * /usr/bin/php /var/www/besucheranmeldung/cleanup.php >/dev/null 2>&1
```

Zusätzlich führt die Anwendung die Bereinigung bei **jedem Seitenaufruf** aus, damit die Löschfrist auch ohne Cronjob eingehalten wird.

## Lokal ausprobieren (ohne Webserver)

```bash
cp config/config.example.php config/config.php   # DB-Daten eintragen, base_url = http://localhost:8080
php -S localhost:8080 -t public
```

Dann <http://localhost:8080/> (Formular) bzw. `http://localhost:8080/pforte.php?key=<pforte_key>` öffnen. Der eingebaute PHP-Server ist nur zum Testen gedacht, nicht für den Betrieb.

## Sicherheit und Datenschutz

- PDO mit Prepared Statements für alle Datenbankzugriffe, echte Prepared Statements (`ATTR_EMULATE_PREPARES = false`).
- Alle Ausgaben von Nutzereingaben über `htmlspecialchars` (XSS-Schutz).
- CSRF-Token in allen Formularen, Honeypot-Feld gegen Spam-Bots.
- Tokens werden mit `random_bytes` erzeugt und mit `hash_equals` verglichen. Bearbeitungstokens werden nur als SHA-256-Hash gespeichert.
- Sicherheits-Header: Content-Security-Policy (keine Inline-Skripte, keine externen Quellen), X-Frame-Options, X-Content-Type-Options, `Referrer-Policy: no-referrer` (Tokens in URLs werden nicht weitergegeben), Permissions-Policy, HSTS (bei HTTPS), `noindex`.
- Rate Limiting: Es wird nur ein HMAC-SHA-256-Hash der IP-Adresse mit Zeitstempel gespeichert (Standard: max. 20 Absendungen pro Stunde), Löschung nach 48 Stunden. Keine weiteren personenbezogenen Logs.
- Keine Tracking- oder externen Dienste, keine CDNs, keine Google Fonts (Systemschriften).
- Nur ein technisch notwendiges Sitzungs-Cookie (für den CSRF-Schutz).
- **Hinweis zu Webserver-Logs:** Apache/nginx protokollieren standardmäßig IP-Adressen und vollständige URLs (inkl. Tokens). Die Beispielkonfigurationen schalten das Zugriffs-Log ab; bei Bedarf ein Log-Format ohne IP und Query-String verwenden.
- **Reverse-Proxy:** Das Rate Limiting nutzt `REMOTE_ADDR`. Steht die Anwendung hinter einem Proxy/Load-Balancer, muss dieser die echte Client-IP setzen (z. B. `mod_remoteip` bzw. `set_real_ip_from` in nginx), sonst teilen sich alle Nutzer ein Limit.
- Bei sehr vielen Personen pro Anmeldung (> ca. 900) greift die PHP-Einstellung `max_input_vars` (Standard 1000) – bei Bedarf erhöhen.
