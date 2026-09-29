<?php

declare(strict_types=1);

namespace Wiksoft\DbChessBundle\Helper;

use Contao\System;

/**
 * ECO-Codes mit Eröffnungsnamen aus der Sprachdatei "dbChess_eco"
 * (contao/languages/en/dbChess_eco.php). Englisch wird von Contao immer als
 * Rückfallsprache geladen, einzelne Namen lassen sich im Projekt
 * überschreiben oder übersetzen.
 */
final class EcoCodes
{
    /**
     * Alle Codes mit vollständigem Namen inkl. Zugfolge, z.B.
     * "B90" => "Najdorf Sicilian: 1.e4 c5 ...".
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        System::loadLanguageFile('dbChess_eco');

        $codes = $GLOBALS['TL_LANG']['dbChess_eco'] ?? [];
        ksort($codes);

        return $codes;
    }

    public static function name(string $code): string
    {
        return self::all()[$code] ?? '';
    }

    /**
     * Optionen für ein Auswahlfeld: Code und Eröffnungsname ohne Zugfolge.
     * Ein gespeicherter Code, der nicht (mehr) in der Sprachdatei steht, wird
     * trotzdem angeboten, damit er beim Speichern nicht verloren geht.
     *
     * @return array<string, string>
     */
    public static function options(?string $current = null): array
    {
        $options = [];

        foreach (self::all() as $code => $name) {
            $options[$code] = $code . ' – ' . explode(': 1.', $name, 2)[0];
        }

        if ($current !== null && $current !== '' && !isset($options[$current])) {
            $options[$current] = $current;
            ksort($options);
        }

        return $options;
    }
}
