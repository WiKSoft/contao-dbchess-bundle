<?php

declare(strict_types=1);

namespace Wiksoft\DbChessBundle\Helper;

use Contao\Database;
use Contao\StringUtil;

/**
 * Erzeugt Partie-Aliase nach dem Schema
 * "weiss-schwarz_jahr_ort_veranstaltung_runde". Wird vom PGN-Import, vom
 * Backend-Formular und beim Kopieren von Partien gemeinsam genutzt.
 */
final class GameAlias
{
    /**
     * @param array<string, mixed> $row
     */
    public static function build(array $row): string
    {
        $white = self::value($row, 'white');
        $black = self::value($row, 'black');
        $date = (string) ($row['date'] ?? '');
        $site = self::value($row, 'site');
        $event = self::value($row, 'event');
        $round = self::value($row, 'round');

        $alias = ($white !== '?' ? explode(',', $white)[0] : '_')
            . '-' . ($black !== '?' ? explode(',', $black)[0] : '_')
            . '_' . (!str_starts_with($date, '????') ? explode('.', $date)[0] : '')
            . ($site !== '?' ? '_' . $site : '')
            . ($event !== '?' ? '_' . $event : '')
            . ($round !== '?' ? '_' . $round : '');

        return StringUtil::generateAlias($alias);
    }

    /**
     * Prüft, ob der Alias bereits von einer anderen Partie verwendet wird.
     */
    public static function isTaken(string $alias, int $id): bool
    {
        return Database::getInstance()
            ->prepare('SELECT id FROM tl_dbChess_games WHERE alias=? AND id!=?')
            ->limit(1)
            ->execute($alias, $id)
            ->numRows > 0;
    }

    /**
     * Liefert einen eindeutigen Alias (bei Kollision mit angehängter ID).
     */
    public static function unique(string $alias, int $id): string
    {
        return self::isTaken($alias, $id) ? $alias . '-id-' . $id : $alias;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function value(array $row, string $field): string
    {
        return (string) ($row[$field] ?? '?');
    }
}
