-- Besucheranmeldung – Datenbankschema (MariaDB, utf8mb4)
--
-- Einspielen:  mariadb -u root -p besucher < schema.sql
-- Alle Zeitangaben werden als lokale Zeit (Europe/Berlin) gespeichert;
-- Vergleiche rechnet die Anwendung in PHP aus und übergibt sie als Parameter.

SET NAMES utf8mb4;

-- Eine Anmeldung: gemeinsame Angaben für alle Personen eines Besuchs.
CREATE TABLE IF NOT EXISTS anmeldungen (
    id                 INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    -- SHA-256 des Bearbeitungs-Tokens; der Token selbst wird nicht gespeichert.
    token_hash         CHAR(64)         NOT NULL,
    einrichtung        VARCHAR(200)     NOT NULL,
    einrichtung_art    ENUM('Behörde', 'Firma') NOT NULL,
    besuch_am          DATETIME         NOT NULL,
    betreff            VARCHAR(255)     NOT NULL,
    ansprech_vorname   VARCHAR(100)     NOT NULL,
    ansprech_nachname  VARCHAR(100)     NOT NULL,
    erstellt_am        DATETIME         NOT NULL,
    geaendert_am       DATETIME         NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_anmeldungen_token (token_hash),
    -- Tagesliste (Bereichsabfrage auf heute) und Bereinigung (älter als 48 h)
    KEY idx_anmeldungen_besuch_am (besuch_am)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Externe Personen einer Anmeldung. Werden mit der Anmeldung gelöscht.
CREATE TABLE IF NOT EXISTS besucher (
    id             INT UNSIGNED       NOT NULL AUTO_INCREMENT,
    anmeldung_id   INT UNSIGNED       NOT NULL,
    position       SMALLINT UNSIGNED  NOT NULL,
    name           VARCHAR(200)       NOT NULL,
    PRIMARY KEY (id),
    KEY idx_besucher_anmeldung (anmeldung_id, position),
    CONSTRAINT fk_besucher_anmeldung
        FOREIGN KEY (anmeldung_id) REFERENCES anmeldungen (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rate Limiting: nur ein HMAC-Hash der IP-Adresse mit Zeitstempel.
-- Einträge werden nach 48 Stunden gelöscht.
CREATE TABLE IF NOT EXISTS rate_limit (
    id           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    ip_hash      CHAR(64)         NOT NULL,
    erstellt_am  DATETIME         NOT NULL,
    PRIMARY KEY (id),
    KEY idx_rate_limit_ip_zeit (ip_hash, erstellt_am),
    KEY idx_rate_limit_zeit (erstellt_am)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
