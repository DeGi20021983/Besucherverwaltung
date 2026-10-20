<?php

declare(strict_types=1);

namespace Besuchermanagement;

/**
 * Einfaches Datei-Log. Es dürfen KEINE personenbezogenen Daten (Namen,
 * Einrichtungen, Betreff, IP-Adressen) übergeben werden, nur technische
 * Meldungen und IDs.
 */
final class Logger
{
    private static ?string $datei = null;

    public static function setDatei(?string $datei): void
    {
        self::$datei = $datei;
    }

    public static function fehler(string $meldung): void
    {
        self::schreiben('FEHLER', $meldung);
    }

    public static function info(string $meldung): void
    {
        self::schreiben('INFO', $meldung);
    }

    private static function schreiben(string $stufe, string $meldung): void
    {
        $zeile = sprintf("[%s] %s %s\n", date('Y-m-d H:i:s'), $stufe, str_replace(["\r", "\n"], ' ', $meldung));
        if (self::$datei !== null && @file_put_contents(self::$datei, $zeile, FILE_APPEND | LOCK_EX) !== false) {
            return;
        }
        error_log(rtrim($zeile));
    }
}
