-- Besucheranmeldung – Datenbankschema für MariaDB (ab 10.6, empfohlen 11.x LTS)
-- Zeichensatz: utf8mb4
--
-- Import:  mariadb -u <benutzer> -p <datenbank> < sql/schema.sql

SET NAMES utf8mb4;

-- Eine Anmeldung: gemeinsame Angaben für alle Personen eines Besuchs.
CREATE TABLE IF NOT EXISTS anmeldungen (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    -- SHA-256-Hash des persönlichen Bearbeitungstokens (der Klartext-Token wird nie gespeichert)
    token_hash        CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    einrichtung_name  VARCHAR(200) NOT NULL,
    einrichtung_art   ENUM('behoerde', 'firma') NOT NULL,
    -- Besuchstermin (Datum + Uhrzeit) in Ortszeit Europe/Berlin
    termin            DATETIME NOT NULL,
    betreff           VARCHAR(255) NOT NULL,
    ansprech_vorname  VARCHAR(100) NOT NULL,
    ansprech_nachname VARCHAR(100) NOT NULL,
    erstellt_am       DATETIME NOT NULL,
    geaendert_am      DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_anmeldungen_token (token_hash),
    -- Tagesliste (Bereichsabfrage auf den heutigen Tag) und Bereinigung (termin < Grenze)
    KEY idx_anmeldungen_termin (termin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Externe Personen einer Anmeldung (beliebig viele pro Anmeldung).
CREATE TABLE IF NOT EXISTS besucher (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    anmeldung_id  INT UNSIGNED NOT NULL,
    position      INT UNSIGNED NOT NULL,
    name          VARCHAR(200) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_besucher_anmeldung (anmeldung_id, position),
    CONSTRAINT fk_besucher_anmeldung
        FOREIGN KEY (anmeldung_id) REFERENCES anmeldungen (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rate Limiting: nur gehashte IP-Adresse (HMAC-SHA-256) mit Zeitstempel, Löschung nach 48 Stunden.
CREATE TABLE IF NOT EXISTS rate_limit (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ip_hash      CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    aktion       VARCHAR(20) CHARACTER SET ascii NOT NULL,
    erstellt_am  DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_rate_limit_ip_zeit (ip_hash, aktion, erstellt_am),
    KEY idx_rate_limit_erstellt (erstellt_am)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
