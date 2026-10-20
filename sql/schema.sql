-- Besuchermanagement: Datenbankschema für MariaDB 11.8 LTS
--
-- Einspielen (Datenbank und Benutzer siehe README.md):
--   mariadb -u besuchermanagement -p besuchermanagement < sql/schema.sql
--
-- Die Beispieldaten am Ende beziehen sich auf CURDATE(), also auf heute und
-- morgen zum Zeitpunkt des Einspielens. Für den Produktivbetrieb den Abschnitt
-- "Beispieldaten" weglassen.

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------------
-- Ankündigungen: ein Datensatz pro abgeschicktem Formular
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ankuendigung (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    besuchstag        DATE         NOT NULL,
    uhrzeit           TIME         NOT NULL,
    betreff           VARCHAR(200) NOT NULL,
    ansprech_vorname  VARCHAR(100) NOT NULL,
    ansprech_nachname VARCHAR(100) NOT NULL,
    erstellt_am       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ankuendigung_besuchstag (besuchstag, uhrzeit)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Besucher: eine oder mehrere externe Personen je Ankündigung
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS besucher (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ankuendigung_id INT UNSIGNED NOT NULL,
    einrichtung     VARCHAR(200) NOT NULL,
    vorname         VARCHAR(100) NOT NULL,
    nachname        VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_besucher_ankuendigung (ankuendigung_id),
    CONSTRAINT fk_besucher_ankuendigung
        FOREIGN KEY (ankuendigung_id) REFERENCES ankuendigung (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Rate-Limit: Absendungen je IP-Adresse. Gespeichert wird nur ein
-- gesalzener Hash der IP (kein Klartext), Einträge werden nach Ablauf des
-- Zeitfensters gelöscht.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS rate_limit (
    id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ip_hash   CHAR(64)        NOT NULL,
    zeitpunkt DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_rate_limit_ip_zeit (ip_hash, zeitpunkt)
) ENGINE=InnoDB DEFAULT CHARSET=ascii COLLATE=ascii_bin;

-- ---------------------------------------------------------------------------
-- Beispieldaten für heute und morgen
-- ---------------------------------------------------------------------------
INSERT INTO ankuendigung (besuchstag, uhrzeit, betreff, ansprech_vorname, ansprech_nachname)
VALUES (CURDATE(), '09:00', 'Wartung der Brandmeldeanlage', 'Petra', 'Schulz');
SET @a1 = LAST_INSERT_ID();
INSERT INTO besucher (ankuendigung_id, einrichtung, vorname, nachname) VALUES
    (@a1, 'Brandschutztechnik Müller GmbH', 'Jonas', 'Becker'),
    (@a1, 'Brandschutztechnik Müller GmbH', 'Aylin', 'Yıldız');

INSERT INTO ankuendigung (besuchstag, uhrzeit, betreff, ansprech_vorname, ansprech_nachname)
VALUES (CURDATE(), '13:30', 'Abstimmung Haushaltsplanung 2027', 'Thomas', 'Wagner');
SET @a2 = LAST_INSERT_ID();
INSERT INTO besucher (ankuendigung_id, einrichtung, vorname, nachname) VALUES
    (@a2, 'Landesrechnungshof', 'Sabine', 'Krüger'),
    (@a2, 'Finanzministerium', 'Michael', 'Hoffmann');

INSERT INTO ankuendigung (besuchstag, uhrzeit, betreff, ansprech_vorname, ansprech_nachname)
VALUES (CURDATE() + INTERVAL 1 DAY, '10:15', 'Lieferung Büromöbel 3. OG', 'Anna', 'Fischer');
SET @a3 = LAST_INSERT_ID();
INSERT INTO besucher (ankuendigung_id, einrichtung, vorname, nachname) VALUES
    (@a3, 'Office Interior KG', 'Lukas', 'Weber');

INSERT INTO ankuendigung (besuchstag, uhrzeit, betreff, ansprech_vorname, ansprech_nachname)
VALUES (CURDATE() + INTERVAL 1 DAY, '14:00', 'Datenschutz-Audit', 'Klaus', 'Meyer');
SET @a4 = LAST_INSERT_ID();
INSERT INTO besucher (ankuendigung_id, einrichtung, vorname, nachname) VALUES
    (@a4, 'Landesbeauftragte für den Datenschutz', 'Julia', 'Schneider'),
    (@a4, 'Landesbeauftragte für den Datenschutz', 'Daniel', 'Koch'),
    (@a4, 'IT-Dienstleistungszentrum', 'Fatma', 'Demir');
