# Prompt: Besucherverwaltung (PHP + MariaDB)

Entwickle eine schlanke Web-Anwendung zur Besucheranmeldung in reinem PHP (ohne Framework) mit MariaDB. Die Oberfläche ist komplett auf Deutsch, Zeitzone Europe/Berlin.

## Ziel
Beschäftigte kündigen externe Besucherinnen und Besucher über ein Formular an. Der Pfortendienst sieht eine druckbare Tagesliste aller Besuche des heutigen Tages.

## Rollen und Zugang (kein Login)
- **Beschäftigte:** Erreichen das Anmeldeformular über einen öffentlichen Link, ohne Anmeldung.
- **Pfortendienst:** Erreicht die Tagesliste über einen eigenen Link. Da die Anwendung aus dem Internet erreichbar ist, wird dieser Link durch einen langen, zufälligen geheimen Token in der URL geschützt (z. B. `/pforte.php?key=...`). Der Token steht in der Konfigurationsdatei. Ohne gültigen Token wird nichts angezeigt (HTTP 403). *[Festgelegt]*

## Anmeldeformular
Eine Anmeldung enthält folgende Pflichtfelder:
- **Einrichtung:** Name der Einrichtung (Textfeld) und Art der Einrichtung (Auswahl: „Behörde“ oder „Firma“)
- **Tag:** Datum
- **Uhrzeit:** Format HH:MM
- **Betreff**
- **Ansprechperson:** Vorname und Nachname (zwei Felder)
- **Name der externen Person:** Standardmäßig ein Feld. Über einen Button „Weitere Person hinzufügen“ können beliebig viele weitere Personen ergänzt (und wieder entfernt) werden. Es gibt keine Höchstzahl.

Einrichtung, Tag, Uhrzeit, Betreff und Ansprechperson gelten gemeinsam für alle Personen einer Anmeldung.

Validierung (serverseitig, zusätzlich clientseitig):
- Alle Felder sind Pflichtfelder.
- Datum und Uhrzeit dürfen nicht in der Vergangenheit liegen.
- Wochenenden und Uhrzeiten außerhalb der Öffnungszeiten sind erlaubt.
- Uhrzeit muss dem Format HH:MM entsprechen.
- Fehler werden verständlich am jeweiligen Feld angezeigt, bereits eingegebene Werte bleiben erhalten.

Im Formular steht ein **Datenschutzhinweis** (Text als Platzhalter in einer separaten, leicht editierbaren Datei), der u. a. erklärt, dass die Daten 48 Stunden nach dem Besuchstermin automatisch gelöscht werden.

## Ändern und Absagen
Beschäftigte dürfen eine Anmeldung nachträglich ändern oder absagen. Da es kein Login gibt, erhält die Person nach dem Absenden eine Bestätigungsseite mit einem **persönlichen Bearbeitungslink** (enthält einen zufälligen, nicht erratbaren Token). Die Seite weist darauf hin, den Link zu speichern. Über diesen Link können alle Angaben geändert (inkl. Personen hinzufügen/entfernen) oder die Anmeldung komplett abgesagt werden (Absage = Löschung). Für Änderungen gelten dieselben Validierungsregeln. *[Festgelegt]*

## Tagesliste für den Pfortendienst
- Zeigt nur die Besuche des **heutigen Tages**, sortiert nach Uhrzeit.
- Eine Tabellenzeile pro externer Person mit den Spalten: **Einrichtung** (Name und Art, z. B. „Stadtwerke (Firma)“), **Tag**, **Uhrzeit**, **Name der externen Person**, **Betreff**, **Ansprechperson** (Vor- und Nachname).
- Überschrift mit dem heutigen Datum, Hinweis wenn keine Besuche angemeldet sind.
- Button „Drucken“ (`window.print()`) und ein eigenes Druck-Stylesheet: A4, nur die Tabelle mit Überschrift und Datum, keine Buttons/Navigation, Tabellenkopf wiederholt sich auf Folgeseiten. *[Festgelegt: A4 Querformat]*

## Datenlöschung
- Besucherdaten werden **48 Stunden nach dem Besuchstermin** (Datum + Uhrzeit) gelöscht.
- Umsetzung: ein Skript `cleanup.php`, das per Cronjob (z. B. stündlich) aufgerufen werden kann. Zusätzlich führt die Anwendung die Bereinigung bei jedem Seitenaufruf aus, damit die Löschung auch ohne Cronjob zuverlässig erfolgt. *[Festgelegt]*
- Logdaten (siehe unten) werden ebenfalls nach 48 Stunden gelöscht.

## Protokollierung und Datenschutz
- Speichere IP-Adressen nur, wenn es technisch nötig ist. Nötig ist es hier nur für einen einfachen Schutz gegen massenhaftes automatisiertes Absenden (Rate Limiting). Speichere dafür nur einen gehashten Wert der IP mit Zeitstempel; Löschung nach 48 Stunden.
- Keine weiteren personenbezogenen Logs, keine Tracking- oder externen Dienste (keine CDNs, keine Google Fonts).

## Sicherheit
- PDO mit Prepared Statements für alle Datenbankzugriffe.
- Ausgabe aller Nutzereingaben mit `htmlspecialchars` (XSS-Schutz).
- CSRF-Token in allen Formularen.
- Tokens mit `random_bytes` erzeugen; Vergleich mit `hash_equals`.
- Honeypot-Feld gegen Spam-Bots.
- Betrieb nur über HTTPS vorgesehen (Hinweis in der README).
- Sicherheits-Header setzen (u. a. Content-Security-Policy, X-Frame-Options, Referrer-Policy: no-referrer, damit Tokens in URLs nicht weitergegeben werden).

## Technik
- Aktuelle stabile Versionen von PHP und MariaDB, Zeichensatz utf8mb4.
- Reines PHP ohne Framework und ohne Composer-Abhängigkeiten.
- Webserver steht noch nicht fest: Die Anwendung muss unter Apache und nginx laufen. Liefere eine `.htaccess` für Apache und eine Beispielkonfiguration für nginx. Konfiguration, Datenbank-Zugangsdaten und interne Dateien liegen außerhalb des öffentlich erreichbaren Verzeichnisses.
- Schlichtes, sauberes und responsives Design ohne Vorgaben, gut nutzbar am PC und am Smartphone.

## Lieferumfang
1. Vollständiger Quellcode mit übersichtlicher Ordnerstruktur
2. `schema.sql` mit allen Tabellen (Anmeldungen, Besucher, Rate-Limit), Fremdschlüsseln mit `ON DELETE CASCADE` und passenden Indizes
3. `config.example.php` (Datenbank-Zugang, Pforten-Token, Zeitzone)
4. `cleanup.php` für den Cronjob
5. `README.md` mit Installationsanleitung, Beispiel-Cronjob, Erzeugung des Pforten-Tokens und den beiden Links (Beschäftigte und Pforte)

Erkläre kurz die Struktur und gehe danach Schritt für Schritt vor.
