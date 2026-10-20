<?php

declare(strict_types=1);

namespace Besuchermanagement;

use RuntimeException;

/**
 * Lesezugriff auf config/config.php mit Punkt-Notation, z. B. get('db.host').
 */
final class Config
{
    /** @param array<string, mixed> $werte */
    private function __construct(private readonly array $werte)
    {
    }

    public static function ausDatei(string $pfad): self
    {
        if (!is_file($pfad)) {
            throw new RuntimeException('Konfigurationsdatei fehlt: config/config.php (Vorlage: config/config.example.php)');
        }
        $werte = require $pfad;
        if (!is_array($werte)) {
            throw new RuntimeException('config/config.php muss ein Array zurückgeben.');
        }
        return new self($werte);
    }

    public function get(string $schluessel, mixed $standard = null): mixed
    {
        $wert = $this->werte;
        foreach (explode('.', $schluessel) as $teil) {
            if (!is_array($wert) || !array_key_exists($teil, $wert)) {
                return $standard;
            }
            $wert = $wert[$teil];
        }
        return $wert;
    }
}
