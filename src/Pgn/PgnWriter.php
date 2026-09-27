<?php

declare(strict_types=1);

namespace Wiksoft\DbChessBundle\Pgn;

/**
 * Erzeugt PGN-Text aus Datensätzen der Tabelle tl_dbChess_games.
 * Wird vom Backend-Export und vom Download-Inhaltselement gemeinsam genutzt.
 */
final class PgnWriter
{
    /** PGN-Tag => Datenbankfeld */
    private const TAGS = [
        'Event' => 'event',
        'Site' => 'site',
        'Date' => 'date',
        'Round' => 'round',
        'White' => 'white',
        'Black' => 'black',
        'Result' => 'result',
        'ECO' => 'eco',
        'WhiteElo' => 'whiteelo',
        'BlackElo' => 'blackelo',
        'Source' => 'source',
        'Annotator' => 'annotator',
    ];

    /**
     * @param iterable<array<string, mixed>> $rows
     */
    public static function games(iterable $rows): string
    {
        $pgnText = '';

        foreach ($rows as $row) {
            $pgnText .= self::game($row);
        }

        return $pgnText;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function game(array $row): string
    {
        $header = '';

        foreach (self::TAGS as $tag => $field) {
            if (!empty($row[$field])) {
                $header .= self::tag($tag, (string) $row[$field]);
            }
        }

        if (!empty($row['fen'])) {
            $header .= self::tag('FEN', (string) $row['fen']);
            $header .= self::tag('SetUp', '1');
        }

        $moves = wordwrap(html_entity_decode((string) ($row['pgn'] ?? ''), ENT_QUOTES), 80, "\n", false);

        return $header . "\n" . $moves . "\n\n";
    }

    /**
     * Maskiert Anführungszeichen und Backslashes gemäß PGN-Spezifikation,
     * damit Feldwerte mit '"' (z.B. Spielernamen) kein kaputtes PGN-Tag
     * erzeugen.
     */
    private static function tag(string $name, string $value): string
    {
        return '[' . $name . ' "' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"]' . "\n";
    }
}
