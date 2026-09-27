<?php

declare(strict_types=1);

namespace Wiksoft\DbChessBundle\Pgn;

/**
 * Zerlegt den Inhalt einer PGN-Datei in einzelne Partien.
 *
 * Jede Partie wird als Array geliefert: die Tag-Namen (klein geschrieben)
 * mit ihren Werten sowie unter dem Schlüssel "pgn" die Zugnotation in einer
 * Zeile. Maskierte Zeichen in Tag-Werten (\" und \\) werden gemäß
 * PGN-Spezifikation zurückgewandelt, damit ein Export/Import-Durchlauf die
 * Werte nicht verändert.
 */
final class PgnReader
{
    private const TAG_PATTERN = '/^\s*\[([A-Za-z0-9_]+)\s+"((?:[^"\\\\]|\\\\.)*)"\s*\]\s*$/';

    /**
     * @return list<array<string, string>>
     */
    public static function parse(string $content): array
    {
        // UTF-8-BOM entfernen
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        $lines = preg_split('/\r\n|\r|\n/', $content) ?: [];

        $games = [];
        $tags = [];
        $moves = [];
        $inMoves = false;
        $blankAfterTags = false;

        foreach ($lines as $line) {
            if (preg_match(self::TAG_PATTERN, $line, $matches)) {
                // Ein Tag nach der Zugnotation (oder nach einer Leerzeile hinter
                // einem Kopf ohne Züge) beginnt eine neue Partie.
                if ($inMoves || $blankAfterTags) {
                    $games[] = self::buildGame($tags, $moves);
                    $tags = [];
                    $moves = [];
                    $inMoves = false;
                    $blankAfterTags = false;
                }

                $tags[strtolower($matches[1])] = self::unescape($matches[2]);
                continue;
            }

            $trimmed = trim($line);

            if ($trimmed === '') {
                if ($tags && !$inMoves) {
                    $blankAfterTags = true;
                }
                continue;
            }

            // Escape-Zeilen gemäß PGN-Spezifikation ignorieren
            if (str_starts_with($trimmed, '%')) {
                continue;
            }

            $inMoves = true;
            $moves[] = $trimmed;
        }

        if ($tags || $moves) {
            $games[] = self::buildGame($tags, $moves);
        }

        return $games;
    }

    /**
     * @param array<string, string> $tags
     * @param list<string> $moves
     * @return array<string, string>
     */
    private static function buildGame(array $tags, array $moves): array
    {
        $tags['pgn'] = preg_replace('/\s{2,}/', ' ', implode(' ', $moves)) ?? '';

        return $tags;
    }

    private static function unescape(string $value): string
    {
        return preg_replace('/\\\\(.)/', '$1', $value) ?? $value;
    }
}
