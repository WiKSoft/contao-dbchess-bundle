<?php

declare(strict_types=1);

namespace Wiksoft\DbChessBundle\Module;

use Contao\BackendTemplate;
use Contao\Database;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\Module;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Wiksoft\DbChessBundle\Helper\EcoCodes;
use Wiksoft\DbChessBundle\Helper\GameQuery;

class ModuleDbChessIndex extends Module
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_dbChess_index';

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();
        $scopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');

        if ($request && $scopeMatcher->isBackendRequest($request)) {
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . mb_strtoupper($GLOBALS['TL_LANG']['FMD']['dbChess_index'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate module
     */
    protected function compile()
    {
        $this->import(Database::class, 'Database');
        System::loadLanguageFile('tl_module');

        // Parameter für Detailliste vorhanden? Input::get() wandelt Zeichen wie
        // ( ) ' " = # in HTML-Entities um; diese werden hier zurückgewandelt,
        // damit der Wert (als Query-Parameter) wieder dem Datenbankwert
        // entspricht.
        $index = StringUtil::decodeEntities($this->fullyUrlDecode((string) Input::get('index')));  // Datenfeld
        $ceId = Input::get('ce_id');  // Modul-ID

        [$where, $whereParams] = GameQuery::collectionWhere($this->dbChess_index_collection);
        [$field, $whiteblack] = $this->resolveIndexField();

        // Datenfelder der Detailliste um id und sid ergänzen
        $arrDetailFields = StringUtil::deserialize($this->dbChess_index_detailFields, true);
        $arrDetailFields[] = 'id';
        $arrDetailFields[] = 'sid';
        $sortingDetail = $this->sanitizeGameField((string) $arrDetailFields[0]);

        // Ausnahmewerte
        $arrException = StringUtil::deserialize($this->dbChess_index_exception, true);

        // Detailliste zusammenstellen
        $gameslist = [];
        if ($index && $ceId == $this->id) {
            $gameslist = $this->fetchDetailList($where, $whereParams, $field, $whiteblack, $index, $arrDetailFields, $sortingDetail);
            // Verknüpfte Partien nur einmalig anzeigen
            $gameslist = GameQuery::removeLinkedDuplicates($gameslist, true);
        }

        // Index erstellen
        $rows = $this->fetchFieldIndexRows($where, $whereParams, $field, $whiteblack);

        if ($field !== 'annotator' && $field !== 'source') {
            $rows = GameQuery::removeLinkedDuplicates($rows);
        }

        $fieldindex = $this->buildFieldIndex($rows, $arrException, $field);
        $fieldindex = $this->sortFieldIndex($fieldindex, $this->dbChess_index_sort, $this->dbChess_index_byorder);
        $currentMax = $this->resolveMaxCount($fieldindex);

        // Use a custom template
        if ($this->dbChess_index_template != '') {
            $this->Template = new FrontendTemplate($this->dbChess_index_template);
        }

        // Tag-Liste/Cloud vorbereiten (Twig hat keinen Zugriff auf $GLOBALS, die
        // Datenbank oder Controller-Hilfsmethoden wie addToUrl(), daher hier
        // vollständig aufbereiten)
        $tags = $this->buildTagCloud($fieldindex, $field, $currentMax);

        // Insert-Tag-Klammern im (aus der URL stammenden) Wert maskieren, da
        // Contao Insert-Tags in der fertigen Seitenausgabe ersetzt.
        $this->Template->index = str_replace(['{{', '}}'], ['&#123;&#123;', '&#125;&#125;'], $index);
        $this->Template->tags = $tags;
        $this->Template->gameslist = array_values($gameslist);
        $this->Template->lblPlay = $GLOBALS['TL_LANG']['tl_module']['dbChess_play'] ?? '';
    }

    /**
     * Dekodiert einen URL-Parameter vollständig, unabhängig davon, wie oft
     * er kodiert wurde. Contaos addToUrl()/urlEncode() sowie die
     * Folder-URL-Erzeugung führen bei Werten mit Sonderzeichen (Komma,
     * Leerzeichen) zu mehrfacher Prozent-Kodierung; ein einzelnes
     * urldecode() reicht dann nicht aus, um wieder den Originalwert zu
     * erhalten. Hier wird so lange dekodiert, bis sich der Wert nicht mehr
     * ändert.
     */
    private function fullyUrlDecode(string $value): string
    {
        do {
            $previous = $value;
            $value = rawurldecode($value);
        } while ($value !== $previous);

        return $value;
    }

    /**
     * Spaltennamen, die als Index-/Sortierfeld in eine SQL-Query eingesetzt
     * werden dürfen (identisch mit den 'options' von dbChess_index_fields
     * bzw. dbChess_index_detailFields in tl_module.php, ohne den virtuellen
     * Wert "whiteblack"). Da Spaltennamen sich nicht per Platzhalter
     * parametrisieren lassen, werden die Werte aus den Backend-Auswahlfeldern
     * gegen diese feste Liste geprüft, bevor sie in SQL-Queries interpoliert
     * werden.
     *
     * @var array<int, string>
     */
    private const ALLOWED_GAME_FIELDS = ['date', 'site', 'event', 'round', 'white', 'black', 'result', 'whiteelo', 'blackelo', 'eco', 'source', 'annotator'];

    /**
     * Löst das konfigurierte Index-Feld auf. "whiteblack" ist ein
     * kombinierter virtueller Feldname für weiß+schwarz gemeinsam.
     *
     * @return array{0: string, 1: bool}
     */
    private function resolveIndexField(): array
    {
        if ($this->dbChess_index_fields === 'whiteblack') {
            return ['white', true];
        }

        return [$this->sanitizeGameField((string) $this->dbChess_index_fields), false];
    }

    /**
     * Prüft ein aus einem Backend-Auswahlfeld stammendes Feld gegen die
     * Liste der bekannten Spaltennamen, bevor es als SQL-Identifier (Spalte
     * in SELECT/ORDER BY) verwendet wird.
     */
    private function sanitizeGameField(string $field, string $default = 'date'): string
    {
        return in_array($field, self::ALLOWED_GAME_FIELDS, true) ? $field : $default;
    }

    /**
     * Holt die Partien für die Detailliste zu einem gewählten Index-Wert.
     *
     * @param array<int, int|string> $whereParams
     * @param array<int, string> $arrDetailFields
     * @return array<int, array<string, mixed>>
     */
    private function fetchDetailList(string $where, array $whereParams, string $field, bool $whiteblack, string $index, array $arrDetailFields, string $sortingDetail): array
    {
        if ($whiteblack) {
            $params = [...$whereParams, $index, $index];
            $result = $this->Database
                ->prepare("SELECT * FROM tl_dbChess_games WHERE ($where) AND (white=? OR black=?) ORDER BY $sortingDetail")
                ->execute(...$params);
        } elseif ($field === 'source') {
            $params = [...$whereParams, $index, addcslashes($index, '%_\\') . ',%'];
            $result = $this->Database
                ->prepare("SELECT * FROM tl_dbChess_games WHERE ($where) AND ($field=? OR $field LIKE ?) ORDER BY $sortingDetail")
                ->execute(...$params);
        } else {
            $params = [...$whereParams, $index];
            $result = $this->Database
                ->prepare("SELECT * FROM tl_dbChess_games WHERE ($where) AND $field=? ORDER BY $sortingDetail")
                ->execute(...$params);
        }

        $gameslist = [];
        $objJumpTo = $this->dbChess_index_jumpTo ? PageModel::findById($this->dbChess_index_jumpTo) : null;

        while ($result->next()) {
            $game = [];

            if ($objJumpTo !== null) {
                $game['href'] = $objJumpTo->getFrontendUrl('/items/' . $result->alias);
            }

            foreach ($arrDetailFields as $valueFields) {
                $game[$valueFields] = $result->$valueFields;
            }

            $gameslist[] = $game;
        }

        return $gameslist;
    }

    /**
     * Holt die Rohdaten (id, sid, Feld[er]) für den Aufbau des Index.
     *
     * @param array<int, int|string> $whereParams
     * @return array<int, array<string, mixed>>
     */
    private function fetchFieldIndexRows(string $where, array $whereParams, string $field, bool $whiteblack): array
    {
        if ($whiteblack) {
            $result = $this->Database
                ->prepare("SELECT id, sid, white, black FROM tl_dbChess_games WHERE $where")
                ->execute(...$whereParams);
        } else {
            $result = $this->Database
                ->prepare("SELECT id, sid, $field FROM tl_dbChess_games WHERE $where")
                ->execute(...$whereParams);
        }

        return $result->fetchAllAssoc();
    }

    /**
     * Zählt die Häufigkeit jedes Feldwerts (unter Berücksichtigung der
     * konfigurierten Ausnahmewerte). Die Ausnahmeprüfung läuft über eine
     * Lookup-Tabelle (O(1) statt einer verschachtelten Schleife).
     *
     * @param array<int, array<string, mixed>> $rows
     * @param array<int, mixed> $arrException
     * @return array<int|string, int>
     */
    private function buildFieldIndex(array $rows, array $arrException, string $field): array
    {
        $exceptions = array_fill_keys(array_map('strval', $arrException), true);

        $fieldindex = [];

        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                if ($key === 'id' || $key === 'sid' || isset($exceptions[(string) $value])) {
                    continue;
                }

                if ($field === 'source') {
                    [$value] = explode(',', (string) $value);
                }

                // Leere Werte nicht als eigenen Index-Eintrag aufnehmen
                if ($value === null || $value === '') {
                    continue;
                }

                $fieldindex[$value] = ($fieldindex[$value] ?? 0) + 1;
            }
        }

        return $fieldindex;
    }

    /**
     * Sortiert den Index nach Häufigkeit oder alphabetisch, je nach
     * Backend-Konfiguration.
     *
     * @param array<int|string, int> $fieldindex
     * @return array<int|string, int>
     */
    private function sortFieldIndex(array $fieldindex, string $sort, string $byOrder): array
    {
        if ($sort === 'c' && $byOrder === 'a') {
            asort($fieldindex);
        } elseif ($sort === 'c' && $byOrder === 'd') {
            arsort($fieldindex);
        } elseif ($sort === 'f' && $byOrder === 'a') {
            ksort($fieldindex);
        } elseif ($sort === 'f' && $byOrder === 'd') {
            krsort($fieldindex);
        }

        return $fieldindex;
    }

    /**
     * Ermittelt den höchsten Zählerwert im Index (für die Tag-Cloud-Skalierung).
     *
     * @param array<int|string, int> $fieldindex
     */
    private function resolveMaxCount(array $fieldindex): ?int
    {
        if (empty($fieldindex)) {
            return null;
        }

        return max($fieldindex);
    }

    /**
     * Baut die Tag-Liste/-Cloud auf. Bei ECO-Codes kommen die
     * Eröffnungsnamen aus der Sprachdatei "dbChess_eco" (siehe EcoCodes).
     *
     * @param array<int|string, int> $fieldindex
     * @return array<int, array<string, mixed>>
     */
    private function buildTagCloud(array $fieldindex, string $field, ?int $currentMax): array
    {
        $ecoNames = $field === 'eco' ? EcoCodes::all() : [];
        $logMax = $currentMax ? log($currentMax + 1) : 0.0;

        $tags = [];

        foreach ($fieldindex as $key => $value) {
            $tags[] = [
                'key' => $key,
                'count' => $value,
                'url' => $this->addToUrl('&amp;index=' . $this->urlEncode((string) $key) . '&amp;ce_id=' . $this->id),
                'size' => $this->resolveTagSize($value, $logMax),
                'ecoName' => $ecoNames[$key] ?? '',
            ];
        }

        return $tags;
    }

    /**
     * Berechnet die Tag-Größe auf einer logarithmischen Skala: die Größe
     * wächst mit zunehmender Häufigkeit immer langsamer (exponentiell
     * abflachend), statt linear proportional zum Maximalwert zu sein.
     * Dadurch dominiert ein einzelnes, sehr häufiges Tag die Cloud nicht
     * mehr so stark wie bei linearer Skalierung.
     */
    private function resolveTagSize(int $value, float $logMax): int
    {
        if ($logMax <= 0.0) {
            return 1;
        }

        $ratio = log($value + 1) / $logMax;

        return (int) round($ratio * (max(1, (int) $this->dbChess_index_tag_buckets) - 1)) + 1;
    }
}
