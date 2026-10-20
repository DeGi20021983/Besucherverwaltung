<?php
/**
 * Deutsche Datumsformate ohne Abhängigkeit von der intl-Erweiterung.
 */

declare(strict_types=1);

const WOCHENTAGE = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
const MONATE = [1 => 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli',
    'August', 'September', 'Oktober', 'November', 'Dezember'];

/** z. B. "Dienstag, 20. Oktober 2026" */
function datum_lang(DateTimeInterface $d): string
{
    return WOCHENTAGE[(int) $d->format('w')] . ', ' . $d->format('j') . '. '
        . MONATE[(int) $d->format('n')] . ' ' . $d->format('Y');
}

/** z. B. "20.10.2026" */
function datum_kurz(DateTimeInterface $d): string
{
    return $d->format('d.m.Y');
}

/** z. B. "Dienstag, 20. Oktober 2026, 09:00 Uhr" */
function termin_lang(DateTimeInterface $d): string
{
    return datum_lang($d) . ', ' . $d->format('H:i') . ' Uhr';
}

/** z. B. "Stadtwerke (Firma)" */
function einrichtung_mit_art(array $a): string
{
    return $a['einrichtung'] . ' (' . $a['einrichtung_art'] . ')';
}
