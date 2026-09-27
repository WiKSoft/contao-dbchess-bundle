<?php

declare(strict_types=1);

namespace Wiksoft\DbChessBundle\Helper;

use Contao\StringUtil;
use Contao\System;

/**
 * Gemeinsame Bausteine für die Partie-Abfragen der Frontend-Elemente
 * (Liste, Download, Index).
 */
final class GameQuery
{
    /**
     * Spaltennamen, die als Sortierfeld in eine ORDER-BY-Klausel eingesetzt
     * werden dürfen (identisch mit den 'options' von dbChess_list_sortfields
     * in tl_content.php). Da Spaltennamen sich nicht per Platzhalter
     * parametrisieren lassen, werden die Werte aus dem Backend-Auswahlfeld
     * gegen diese feste Liste geprüft.
     */
    private const ALLOWED_SORT_FIELDS = ['event', 'site', 'date', 'round', 'result', 'white', 'black', 'eco', 'whiteelo', 'blackelo', 'annotator', 'source'];

    /**
     * Baut die WHERE-Bedingung für die ausgewählten Partiesammlungen als
     * parametrisierte Query.
     *
     * @return array{0: string, 1: list<int>}
     */
    public static function collectionWhere(mixed $collections): array
    {
        $ids = array_values(array_filter(array_map('intval', StringUtil::deserialize($collections, true))));

        if (!$ids) {
            return ['pid IS NULL', []];
        }

        return ['pid IN (' . implode(',', array_fill(0, \count($ids), '?')) . ')', $ids];
    }

    /**
     * Baut die ORDER-BY-Klausel anhand der im Backend gewählten Sortierfelder.
     */
    public static function sortingClause(mixed $sortFields, ?string $byOrder): string
    {
        $direction = $byOrder === 'a' ? 'ASC' : 'DESC';
        $fields = [];

        foreach (StringUtil::deserialize($sortFields, true) as $field) {
            if (!\in_array($field, self::ALLOWED_SORT_FIELDS, true)) {
                continue;
            }

            $fields[] = ($field === 'round' ? 'CAST(round AS UNSIGNED)' : $field) . ' ' . $direction;
        }

        $fields[] = 'gameFeatured DESC';

        return 'ORDER BY ' . implode(', ', $fields);
    }

    /**
     * Liefert die optionale, im Backend konfigurierte Filterbedingung.
     *
     * Der Wert ist ein freier SQL-Ausdruck (wie list_where im
     * Contao-Auflistungsmodul) und wird unverändert in die Query übernommen.
     * Er kann daher nur von Administratoren bearbeitet werden (siehe
     * tl_content.php). Insert-Tags werden vorher ersetzt.
     */
    public static function filterClause(?string $filter): string
    {
        $filter = trim(StringUtil::decodeEntities((string) $filter));

        if ($filter === '') {
            return '';
        }

        $filter = System::getContainer()->get('contao.insert_tag.parser')->replaceInline($filter);

        return 'AND (' . $filter . ')';
    }

    /**
     * Entfernt aus einer Liste von Partien alle über "sid" verknüpften
     * Duplikate, sodass jede Partiengruppe nur einmal auftaucht.
     *
     * @param array<int|string, array<string, mixed>> $list
     * @param bool $replaceSidWithCount "sid" durch die Anzahl verknüpfter Partien ersetzen
     * @param (callable(array<string, mixed>, array<string, mixed>): array<string, mixed>)|null $merge
     *        Wird für jede entfernte Partie aufgerufen, um Daten in die verbleibende zu übernehmen
     * @return array<int|string, array<string, mixed>>
     */
    public static function removeLinkedDuplicates(array $list, bool $replaceSidWithCount = false, ?callable $merge = null): array
    {
        $keysById = [];

        foreach ($list as $key => $entry) {
            $keysById[(string) $entry['id']] = $key;
        }

        foreach (array_keys($list) as $key) {
            if (!isset($list[$key]) || empty($list[$key]['sid'])) {
                continue;
            }

            $sidList = StringUtil::deserialize($list[$key]['sid'], true);

            if ($replaceSidWithCount) {
                $list[$key]['sid'] = \count($sidList);
            }

            foreach ($sidList as $sid) {
                $otherKey = $keysById[(string) $sid] ?? null;

                if ($otherKey === null || $otherKey === $key || !isset($list[$otherKey])) {
                    continue;
                }

                if ($merge !== null) {
                    $list[$key] = $merge($list[$key], $list[$otherKey]);
                }

                unset($list[$otherKey]);
            }
        }

        return $list;
    }
}
