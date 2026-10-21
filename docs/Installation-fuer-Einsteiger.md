# Installation für Einsteiger

Diese Anleitung erklärt Schritt für Schritt, wie die Besucheranmeldung ins Internet kommt. Sie richtet sich an Personen **ohne IT-Vorkenntnisse**. Die technische Kurzfassung steht in der [README](../README.md).

> **Tipp vorab:** Wenn Ihre Einrichtung eine IT-Abteilung oder einen IT-Dienstleister hat, geben Sie diese Anleitung am besten dorthin weiter. Der Aufwand für eine Fachperson liegt bei etwa einer Stunde. Wenn Sie es selbst machen, planen Sie einen halben Tag ein.

---

## Die wichtigsten Begriffe

| Begriff | Was ist das? |
|---|---|
| **Webspace / Hosting** | Gemieteter Speicherplatz auf einem Computer (Server), der rund um die Uhr mit dem Internet verbunden ist. Dort liegen die Dateien der Anwendung. Anbieter sind z. B. IONOS, Strato, all-inkl, netcup oder Hetzner. Kosten: meist wenige Euro im Monat. |
| **Domain** | Die Internetadresse, z. B. `besuch.meine-einrichtung.de`. Sie wird beim Hosting-Anbieter mitgebucht oder auf den Webspace „gezeigt“. |
| **Kundenmenü** | Die Verwaltungsoberfläche Ihres Hosting-Anbieters im Browser. Dort stellen Sie fast alles per Mausklick ein. Es heißt je nach Anbieter z. B. „Control Panel“, „KAS“ oder „Kundencenter“. |
| **PHP** | Die Programmiersprache, in der die Anwendung geschrieben ist. Der Webspace muss sie können. Das ist bei fast allen Angeboten der Fall. |
| **Datenbank (MariaDB/MySQL)** | Ein geordneter Speicher, in dem die Anmeldungen abgelegt werden, ähnlich einer Tabelle in Excel. MariaDB und MySQL sind praktisch gleichwertig. |
| **phpMyAdmin** | Eine Seite im Kundenmenü, mit der man die Datenbank im Browser bedienen kann. |
| **FTP / FileZilla** | FTP ist ein Weg, Dateien vom eigenen Computer auf den Webspace zu kopieren. FileZilla ist ein kostenloses Programm dafür (filezilla-project.org). Viele Anbieter haben auch einen „Dateimanager“ im Browser. |
| **HTTPS / SSL-Zertifikat** | Sorgt für eine verschlüsselte Verbindung (Schloss-Symbol im Browser). Für diese Anwendung **Pflicht**. Ein kostenloses Zertifikat („Let's Encrypt“) lässt sich im Kundenmenü meist per Klick aktivieren. |
| **Token** | Eine lange, zufällige Zeichenfolge, die wie ein Passwort wirkt. Wer den Link mit dem Token kennt, darf die Seite sehen. |
| **Cronjob** | Ein automatischer Auftrag, den der Server regelmäßig ausführt, z. B. „jede Stunde alte Daten löschen“. |
| **XAMPP** | Ein kostenloses Komplettpaket (Webserver, Datenbank, PHP), mit dem man die Anwendung auf dem eigenen PC ausprobieren kann. Nur für Tests, nicht für den echten Betrieb. |
| **localhost** | Die Adresse „dieser Rechner selbst“. Was unter `http://localhost` läuft, ist nur auf dem eigenen PC sichtbar. |
| **Webroot / Zielverzeichnis** | Der Ordner auf dem Webspace, den Besucher der Domain sehen dürfen. Alles außerhalb davon bleibt verborgen. |

---

## Vorab (optional): Kostenlos auf dem eigenen Rechner ausprobieren mit XAMPP

Bevor Sie Hosting buchen, können Sie die Anwendung **kostenlos auf Ihrem eigenen Windows-PC** ausprobieren. Dafür gibt es **XAMPP**: ein Gratis-Paket, das Webserver (Apache), Datenbank (MariaDB), PHP und phpMyAdmin mit einem Klick installiert.

> **Wichtig:** Eine XAMPP-Installation ist **nur auf diesem einen Rechner** erreichbar (Adresse `http://localhost`). Kolleginnen, Kollegen und die Pforte können sie nicht aufrufen, und es gibt kein HTTPS. XAMPP eignet sich also nur zum **Ausprobieren**. Für den echten Betrieb brauchen Sie Hosting mit HTTPS (ab [Schritt 1](#schritt-1-passendes-hosting-besorgen)).

**Passt die Version?** Die Anwendung braucht **PHP 8.2 oder neuer**. Bei XAMPP steht die PHP-Version direkt im Namen des Downloads, z. B. „XAMPP 8.2.12“ = PHP 8.2.12. Nehmen Sie also eine Version **ab 8.2**. Die enthaltene Datenbank (MariaDB 10.4 oder neuer) ist für den Test geeignet. Ältere Pakete (8.1, 8.0, 7.x) funktionieren **nicht**.

### X1: XAMPP installieren

1. Laden Sie XAMPP für Windows von der offiziellen Seite <https://www.apachefriends.org/de/> herunter (Version ab 8.2, siehe oben).
2. Starten Sie das Installationsprogramm. Eine Warnung zur Benutzerkontensteuerung können Sie mit „OK“ bestätigen.
3. Bei der Auswahl der Komponenten genügen **Apache**, **MySQL**, **PHP** und **phpMyAdmin**.
4. Installationsordner: **`C:\xampp`** (Vorschlag übernehmen).
5. Fragt die Windows-Firewall nach, erlauben Sie den Zugriff nur für **private Netzwerke**.

### X2: Projektdateien ablegen

1. Laden Sie den Code wie in [Schritt 2](#schritt-2-dateien-von-github-herunterladen) beschrieben als ZIP herunter und entpacken Sie ihn.
2. Benennen Sie den entpackten Ordner in **`besucheranmeldung`** um und verschieben Sie ihn nach **`C:\xampp\`**.
   Danach muss es den Ordner **`C:\xampp\besucheranmeldung\public`** geben.

> Legen Sie die Dateien **nicht** in den Ordner `C:\xampp\htdocs`. Sonst wären auch `config` und `src` über den Browser abrufbar.

### X3: Apache auf den Ordner `public` einstellen

Damit wie beim echten Betrieb nur der Ordner `public` sichtbar ist, ändern Sie einmalig zwei Zeilen:

1. Öffnen Sie das **XAMPP Control Panel** (Startmenü → „XAMPP Control Panel“).
2. Klicken Sie in der Zeile **Apache** auf **„Config“** → **„Apache (httpd.conf)“**. Die Datei öffnet sich im Editor.
3. Suchen Sie mit **Strg + F** nach `htdocs`. Sie finden diese beiden Zeilen:

   ```
   DocumentRoot "C:/xampp/htdocs"
   <Directory "C:/xampp/htdocs">
   ```

4. Ändern Sie **beide** Zeilen so (Schrägstriche `/` wie abgebildet):

   ```
   DocumentRoot "C:/xampp/besucheranmeldung/public"
   <Directory "C:/xampp/besucheranmeldung/public">
   ```

5. Speichern Sie die Datei (Strg + S).

Die XAMPP-Startseite erscheint danach nicht mehr. phpMyAdmin bleibt unter `http://localhost/phpmyadmin` erreichbar. Zum Rückgängigmachen tragen Sie wieder `C:/xampp/htdocs` ein.

### X4: Apache und Datenbank starten

1. Klicken Sie im XAMPP Control Panel bei **Apache** und bei **MySQL** jeweils auf **„Start“**.
2. Beide Namen werden **grün** hinterlegt.

Bleibt Apache rot, belegt meist ein anderes Programm (z. B. Skype oder IIS) den Anschluss. Beenden Sie dieses Programm und starten Sie Apache erneut.

### X5: Datenbank einrichten

1. Öffnen Sie im Browser **<http://localhost/phpmyadmin>**.
2. Klicken Sie links auf **„Neu“**.
3. Datenbankname: **`besucheranmeldung`**, Kollation: **`utf8mb4_unicode_ci`** → **„Anlegen“**.
4. Mit der neuen Datenbank ausgewählt: oben **„Importieren“** → Datei **`C:\xampp\besucheranmeldung\sql\schema.sql`** wählen → unten **„Importieren“**.
5. Links erscheinen die drei Tabellen `anmeldungen`, `besucher` und `rate_limit`.

### X6: Konfigurationsdatei ausfüllen

Gehen Sie vor wie in [Schritt 5](#schritt-5-konfigurationsdatei-ausfüllen) (also `config\config.example.php` kopieren und die Kopie in `config.php` umbenennen). Für XAMPP tragen Sie diese Werte ein:

```php
'db' => [
    'host'     => 'localhost',
    'port'     => 3306,
    'name'     => 'besucheranmeldung',
    'user'     => 'root',
    'password' => '',
    'socket'   => '',
],
'pforte_key' => 'HIER-IHR-TOKEN-MIT-MINDESTENS-40-ZEICHEN',
'ip_hash_secret' => 'HIER-EIN-ZWEITER-ZUFALLSWERT',
'base_url' => 'http://localhost',
```

- Bei XAMPP heißt der Datenbank-Benutzer **`root`** und hat **kein Passwort** (zwei Hochkommas ohne Inhalt: `''`). Das ist nur für einen Test auf dem eigenen Rechner in Ordnung, **niemals** auf einem echten Server.
- Die beiden Token erzeugen Sie wie in [Schritt 4](#schritt-4-zwei-geheime-zeichenfolgen-token-erzeugen) beschrieben.

### X7: Ausprobieren

| Was? | Adresse im Browser |
|---|---|
| Anmeldeformular | <http://localhost/> |
| Tagesliste Pforte | `http://localhost/pforte.php?key=IHR-PFORTEN-TOKEN` |

Testen Sie wie in [Schritt 10](#schritt-10-testen) beschrieben: Anmeldung für heute anlegen, Tagesliste ansehen und drucken, Pforte ohne Token aufrufen (muss „403 – Zugriff verweigert“ zeigen), Anmeldung über den Bearbeitungslink ändern und absagen.

Wenn Sie fertig sind, klicken Sie im XAMPP Control Panel bei Apache und MySQL auf **„Stop“**. Testdaten werden 48 Stunden nach dem eingetragenen Termin automatisch gelöscht.

> **Gut zu wissen:** Lokal über `http://localhost` funktioniert die Anwendung ohne HTTPS. Unter jeder anderen Adresse leitet sie automatisch auf HTTPS um. Auf dem echten Server muss HTTPS deshalb eingerichtet sein ([Schritt 7](#schritt-7-https-einschalten)).

---

## Schritt 1: Passendes Hosting besorgen

Prüfen Sie beim Hosting-Angebot (oder fragen Sie den Anbieter), ob diese Punkte erfüllt sind:

- [ ] **PHP ab Version 8.2**
- [ ] **MariaDB- oder MySQL-Datenbank** (mindestens eine)
- [ ] **Kostenloses SSL-Zertifikat** (HTTPS)
- [ ] Das **Zielverzeichnis der Domain lässt sich frei wählen** (wichtig für Schritt 6)
- [ ] Optional: **Cronjobs** einrichtbar

Ein einfaches Webhosting-Paket („Webspace“, „Hosting Basic“ o. Ä.) reicht aus. Ein eigener Server ist nicht nötig.

> **Datenschutz:** Wählen Sie einen Anbieter mit Rechenzentrum in Deutschland oder der EU und schließen Sie im Kundenmenü den **Auftragsverarbeitungsvertrag (AVV)** ab. Den bieten alle großen Anbieter zum Download oder per Klick an.

---

## Schritt 2: Dateien von GitHub herunterladen

1. Öffnen Sie <https://github.com/DeGi20021983/Besucherverwaltung>.
2. Klicken Sie auf den grünen Button **„Code“** und dann auf **„Download ZIP“**.
3. Entpacken Sie die ZIP-Datei auf Ihrem Computer (Rechtsklick → „Alle extrahieren“).

Sie haben jetzt einen Ordner mit u. a. diesen Unterordnern: `public`, `src`, `templates`, `config`, `content`, `sql`.

---

## Schritt 3: Datenbank anlegen

1. Melden Sie sich im **Kundenmenü** Ihres Hosting-Anbieters an.
2. Suchen Sie den Bereich **„Datenbanken“** bzw. **„MySQL“** und legen Sie eine **neue Datenbank** an.
3. Notieren Sie sich diese vier Angaben (Sie brauchen sie in Schritt 5):
   - **Datenbank-Server** (Host), z. B. `localhost` oder `db12345.hosting-anbieter.de`
   - **Name der Datenbank**
   - **Benutzername**
   - **Passwort**
4. Öffnen Sie **phpMyAdmin** (meist ein Link direkt neben der Datenbank).
5. Wählen Sie links Ihre neue Datenbank aus und klicken Sie oben auf **„Importieren“**.
6. Wählen Sie die Datei **`sql/schema.sql`** aus dem entpackten Ordner und klicken Sie auf **„OK“** bzw. **„Importieren“**.
7. Links erscheinen nun drei Tabellen: `anmeldungen`, `besucher` und `rate_limit`. Damit ist die Datenbank fertig.

---

## Schritt 4: Zwei geheime Zeichenfolgen (Token) erzeugen

Sie brauchen **zwei verschiedene**, lange Zufallswerte:

- den **Pforten-Token**: er schützt die Tagesliste der Pforte;
- den **IP-Hash-Schlüssel**: er dient intern dem Spamschutz.

So erzeugen Sie sie ohne Fachwissen:

- Nutzen Sie den **Passwort-Generator** eines Passwort-Managers (z. B. KeePass, Bitwarden oder den Passwort-Vorschlag Ihres Browsers).
- Einstellungen: **mindestens 40 Zeichen**, nur **Buchstaben und Ziffern** (keine Sonderzeichen, damit der Link später sauber funktioniert).
- Erzeugen Sie zwei verschiedene Werte und bewahren Sie beide sicher auf, z. B. im Passwort-Manager.

Beispiel, wie so ein Wert aussieht (**bitte nicht übernehmen**):
`k7Qm2Rx9VtB4nLp8ZsW3eHc6YaJ1dGf5UuN0oPiT`

> Wer Zugang zu einer Kommandozeile hat, kann alternativ `php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'` verwenden.

---

## Schritt 5: Konfigurationsdatei ausfüllen

Die Konfigurationsdatei enthält alle Einstellungen und Zugangsdaten an einer Stelle.

1. Öffnen Sie im entpackten Ordner den Unterordner **`config`**.
2. Kopieren Sie die Datei **`config.example.php`** und benennen Sie die Kopie in **`config.php`** um.
   *(Unter Windows: Damit Dateiendungen sichtbar sind, im Explorer unter „Ansicht“ → „Dateinamenerweiterungen“ einschalten.)*
3. Öffnen Sie `config.php` mit einem einfachen Texteditor (Windows: Rechtsklick → „Öffnen mit“ → „Editor“; besser noch das kostenlose Notepad++). **Nicht** mit Word öffnen.
4. Ersetzen Sie die Werte zwischen den Anführungszeichen `'…'`. Lassen Sie die Anführungszeichen, Kommas und alles andere unverändert:

| Zeile | Was eintragen? |
|---|---|
| `'host' => 'localhost',` | Datenbank-Server aus Schritt 3 |
| `'name' => 'besucheranmeldung',` | Name der Datenbank |
| `'user' => 'besucheranmeldung',` | Benutzername der Datenbank |
| `'password' => 'BITTE-AENDERN',` | Passwort der Datenbank |
| `'pforte_key' => '…',` | Pforten-Token aus Schritt 4 |
| `'ip_hash_secret' => '…',` | zweiter Wert aus Schritt 4 |
| `'base_url' => 'https://besuch.example.de',` | Ihre Internetadresse, **mit** `https://`, **ohne** Schrägstrich am Ende |
| `'app_name' => 'Besucheranmeldung',` | optional: Name im Seitenkopf |

Der fertige Abschnitt sieht dann etwa so aus:

```php
'db' => [
    'host'     => 'db12345.hosting-anbieter.de',
    'port'     => 3306,
    'name'     => 'db12345_besuch',
    'user'     => 'db12345_besuch',
    'password' => 'MeinDatenbankPasswort',
    'socket'   => '',
],
'pforte_key' => 'k7Qm2Rx9VtB4nLp8ZsW3eHc6YaJ1dGf5UuN0oPiT',
'ip_hash_secret' => 'Zr4Hb8Nw2Kq6Tx1Mv9Lc3Pj7Fs5Gd0Ye8Ua2Wn6R',
'base_url' => 'https://besuch.meine-einrichtung.de',
```

5. Speichern Sie die Datei.

> **Wichtig:** `config.php` enthält Passwörter. Laden Sie sie **niemals** auf GitHub hoch und schicken Sie sie nicht unverschlüsselt per E-Mail.

---

## Schritt 6: Dateien hochladen und Zielverzeichnis einstellen

Dieser Schritt ist der wichtigste für die Sicherheit.

1. Verbinden Sie sich mit **FileZilla** (oder dem Dateimanager im Kundenmenü) mit Ihrem Webspace. Die Zugangsdaten (Server, Benutzer, Passwort) finden Sie im Kundenmenü unter „FTP“ oder „SFTP“.
2. Legen Sie auf dem Webspace einen neuen Ordner an, z. B. **`besucheranmeldung`**.
3. Laden Sie den **gesamten Inhalt** des entpackten Ordners dort hinein, also `public`, `src`, `templates`, `config` (mit Ihrer `config.php`), `content`, `sql` sowie die Dateien `cleanup.php` und `.htaccess`.
   *(Dateien, deren Name mit einem Punkt beginnt, wie `.htaccess`, sind manchmal versteckt. In FileZilla: Menü „Server“ → „Anzeige versteckter Dateien erzwingen“.)*
4. Gehen Sie im Kundenmenü zu **„Domains“**, wählen Sie Ihre Domain und stellen Sie das **Zielverzeichnis** (auch „Document Root“ oder „Verzeichnis“ genannt) auf:

   **`/besucheranmeldung/public`**

**Warum `public`?** Nur der Ordner `public` darf aus dem Internet erreichbar sein. Die Ordner `config` (Passwörter) und `src` (Programmcode) liegen dadurch außer Reichweite. Würde die Domain auf den Hauptordner zeigen, wäre das unsicher. Die Anwendung sperrt sich in diesem Fall absichtlich selbst, und es erscheint nur „Forbidden“ / „403“.

> Falls Ihr Anbieter kein frei wählbares Zielverzeichnis erlaubt, fragen Sie den Support danach oder wählen Sie ein anderes Paket. Legen Sie die Dateien **nicht** einfach so ab, dass `config` öffentlich erreichbar ist.

---

## Schritt 7: HTTPS einschalten

1. Im Kundenmenü unter **„SSL“** bzw. **„Zertifikate“** für Ihre Domain ein **kostenloses Let's-Encrypt-Zertifikat** aktivieren.
2. Wenn angeboten, die Option **„HTTPS erzwingen“** bzw. „Weiterleitung auf HTTPS“ einschalten.
3. Nach einigen Minuten sollte Ihre Adresse mit `https://` und einem Schloss-Symbol im Browser erscheinen.

Ohne HTTPS könnten die geheimen Links mitgelesen werden. Deshalb ist HTTPS hier Pflicht.

---

## Schritt 8: Datenschutzhinweis anpassen

1. Öffnen Sie die Datei **`content/datenschutzhinweis.txt`** mit dem Texteditor.
2. Ersetzen Sie den Platzhaltertext durch den Text, den Sie mit Ihrer bzw. Ihrem **Datenschutzbeauftragten** abgestimmt haben. Absätze trennen Sie durch eine Leerzeile.
3. Laden Sie die Datei erneut hoch, sie ersetzt dann die alte.

Der Text erscheint automatisch im Anmeldeformular.

---

## Schritt 9: Automatische Löschung (Cronjob), optional

Die Anwendung löscht alte Daten bei **jedem Seitenaufruf** selbst. Ein Cronjob ist deshalb **nicht zwingend**, sorgt aber dafür, dass auch an ruhigen Tagen (z. B. am Wochenende) pünktlich gelöscht wird.

1. Im Kundenmenü den Bereich **„Cronjobs“** bzw. „Geplante Aufgaben“ öffnen.
2. Neuen Cronjob anlegen:
   - **Typ:** „PHP-Skript“ (falls wählbar)
   - **Datei:** `/besucheranmeldung/cleanup.php`
   - **Häufigkeit:** stündlich
3. Speichern.

Wenn der Anbieter einen Befehl verlangt, lautet er sinngemäß:
`php /pfad/zum/webspace/besucheranmeldung/cleanup.php`
(den genauen Pfad nennt das Kundenmenü oder der Support).

Bietet Ihr Anbieter nur Cronjobs an, die eine **Internetadresse (URL)** aufrufen, lassen Sie diesen Schritt weg. `cleanup.php` ist aus Sicherheitsgründen nicht über das Internet erreichbar, und die Löschung bei jedem Seitenaufruf genügt.

---

## Schritt 10: Testen

1. **Formular:** Öffnen Sie `https://ihre-domain.de/`. Das Anmeldeformular erscheint.
2. Legen Sie eine **Test-Anmeldung für heute** an (Uhrzeit etwas später als jetzt). Sie erhalten eine Bestätigungsseite mit einem **Bearbeitungslink**.
3. **Pforte:** Öffnen Sie `https://ihre-domain.de/pforte.php?key=IHR-PFORTEN-TOKEN`. Ihre Test-Anmeldung steht in der Tabelle. Probieren Sie den Button **„Drucken“** aus.
4. **Schutz prüfen:** Öffnen Sie `https://ihre-domain.de/pforte.php` **ohne** Token. Es muss „403 – Zugriff verweigert“ erscheinen.
5. Öffnen Sie den Bearbeitungslink aus Schritt 2 und **sagen Sie die Test-Anmeldung ab**.

Wenn alles klappt, ist die Installation abgeschlossen. 🎉

---

## Schritt 11: Links verteilen

| Für wen? | Link | Wie weitergeben? |
|---|---|---|
| **Alle Beschäftigten** | `https://ihre-domain.de/` | Frei, z. B. im Intranet, per Rundmail oder als Lesezeichen |
| **Nur Pfortendienst** | `https://ihre-domain.de/pforte.php?key=IHR-PFORTEN-TOKEN` | Vertraulich, am besten direkt als Lesezeichen am Pforten-PC einrichten |

Gerät der Pforten-Link in falsche Hände, tragen Sie in `config/config.php` einfach einen **neuen Pforten-Token** ein und laden die Datei erneut hoch. Der alte Link funktioniert dann sofort nicht mehr.

---

## Hilfe bei Problemen

| Was sehen Sie? | Was bedeutet das? | Was tun? |
|---|---|---|
| „Die Anwendung ist noch nicht eingerichtet: config/config.php fehlt.“ | Die Datei `config.php` fehlt oder liegt im falschen Ordner. | Schritt 5 prüfen: Die Datei muss `config.php` heißen (nicht `config.php.txt`) und im Ordner `config` liegen. |
| „Technischer Fehler“ | Meist sind die Datenbank-Zugangsdaten falsch, oder die Tabellen fehlen. | Angaben in `config.php` mit dem Kundenmenü vergleichen; in phpMyAdmin prüfen, ob die drei Tabellen vorhanden sind (Schritt 3). |
| „Forbidden“ / „403“ auf **allen** Seiten | Das Zielverzeichnis zeigt nicht auf `public`. | Schritt 6, Punkt 4 prüfen. |
| „403 – Zugriff verweigert“ nur bei der **Pforte** | Der Token im Link stimmt nicht mit `pforte_key` überein oder ist kürzer als 32 Zeichen. | Token in `config.php` und im Link vergleichen (Groß-/Kleinschreibung beachten). |
| **XAMPP:** „Diese Website ist nicht erreichbar“ | Apache läuft nicht. | Im XAMPP Control Panel Apache starten (muss grün sein), siehe X4. |
| **XAMPP:** „Object not found“ / „403“ | Die Pfade in `httpd.conf` stimmen nicht, oder der Ordner liegt woanders. | X2 und X3 prüfen: Gibt es `C:\xampp\besucheranmeldung\public`? Danach Apache neu starten. |
| Eine Liste von Dateien oder Programmcode wird angezeigt | Die Domain zeigt auf den falschen Ordner, oder PHP ist nicht aktiv. | Zielverzeichnis prüfen; im Kundenmenü PHP 8.2 oder neuer einstellen. |
| Seite lädt ohne Farben/Layout | Der Ordner `public/assets` wurde nicht vollständig hochgeladen. | Ordner erneut hochladen. |
| Der Bearbeitungslink beginnt mit `https://besuch.example.de` | `base_url` wurde nicht angepasst. | In `config.php` Ihre eigene Adresse eintragen (Schritt 5). |
| Meldung „zu viele Anmeldungen in kurzer Zeit“ | Spamschutz: mehr als 20 Absendungen pro Stunde vom selben Anschluss. | Eine Stunde warten. Der Wert lässt sich in `config.php` unter `rate_limit` erhöhen. |

Kommen Sie nicht weiter, hilft in der Regel der **Support Ihres Hosting-Anbieters**. Nennen Sie dort: „PHP-Anwendung mit MariaDB-Datenbank, Document Root soll auf den Unterordner `public` zeigen.“
