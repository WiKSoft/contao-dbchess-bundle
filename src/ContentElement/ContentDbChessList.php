<?php

declare(strict_types=1);

namespace Wiksoft\DbChessBundle\ContentElement;

use Contao\ContentElement;
use Contao\Database;
use Contao\FrontendTemplate;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Wiksoft\DbChessBundle\Helper\GameQuery;

class ContentDbChessList extends ContentElement
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_dbChess_list_default';

    /**
     * Generate module
     */
    protected function compile()
    {
        System::loadLanguageFile('tl_dbChess_games');
        System::loadLanguageFile('tl_module');

        // Datenfelder um id und sid ergänzen
        $arrFields = StringUtil::deserialize($this->dbChess_list_fields, true);
        $arrFields[] = 'id';
        $arrFields[] = 'sid';

        // Feldbezeichnungen für die Tabellenkopfzeile vormerken (Zuweisung ans
        // Template erfolgt weiter unten, NACH einem möglichen Template-Wechsel)
        $fieldLabels = [];
        foreach ($arrFields as $f) {
            $fieldLabels[$f] = $GLOBALS['TL_LANG']['tl_dbChess_games'][$f][0] ?? $f;
        }

        [$where, $params] = GameQuery::collectionWhere($this->dbChess_list_collection);
        $filter = GameQuery::filterClause($this->dbChess_list_filter);
        $sorting = GameQuery::sortingClause($this->dbChess_list_sortfields, $this->dbChess_list_byorder);

        $result = Database::getInstance()
            ->prepare("SELECT * FROM tl_dbChess_games WHERE ($where) $filter $sorting")
            ->execute(...$params);

        $objJumpTo = $this->dbChess_list_jumpTo ? PageModel::findById($this->dbChess_list_jumpTo) : null;
        $gameslist = [];

        while ($result->next()) {
            $game = [];

            if ($objJumpTo !== null) {
                $game['href'] = $objJumpTo->getFrontendUrl('/items/' . $result->alias);
            }

            foreach ($arrFields as $field) {
                $value = $result->$field;

                if ($field === 'pgn') {
                    $value = StringUtil::substr((string) $value, 30);
                } elseif ($field === 'date') {
                    $value = $this->formatDate((string) $value);
                }

                $game[$field] = $value;
            }

            $gameslist[] = $game;
        }

        // Verknüpfte Partien nur einmalig anzeigen, Kommentatoren und Quellen
        // dabei zusammenführen
        $gameslist = GameQuery::removeLinkedDuplicates($gameslist, true, static function (array $game, array $linked): array {
            foreach (['annotator', 'source'] as $field) {
                if (!isset($game[$field]) || (string) $linked[$field] === '' || str_contains((string) $game[$field], (string) $linked[$field])) {
                    continue;
                }

                $game[$field] = ((string) $game[$field] !== '' ? $game[$field] . '; ' : '') . $linked[$field];
            }

            return $game;
        });

        // Use a custom template
        if ($this->dbChess_list_template != '') {
            $this->Template = new FrontendTemplate($this->dbChess_list_template);
        }

        $this->Template->dbChess_list_jumpTo = $this->dbChess_list_jumpTo;
        $this->Template->icon = 'bundles/wiksoftdbchess/images/iconBoard.png';
        $this->Template->gameslist = array_values($gameslist);
        $this->Template->fieldLabels = $fieldLabels;
        $this->Template->lblPlay = $GLOBALS['TL_LANG']['tl_module']['dbChess_play'] ?? '';
    }

    /**
     * Wandelt ein PGN-Datum (JJJJ.MM.TT) in TT.MM.JJJJ um. Werte in einem
     * anderen Format (z.B. "?") werden unverändert zurückgegeben.
     */
    private function formatDate(string $date): string
    {
        $parts = explode('.', $date);

        if (\count($parts) !== 3) {
            return $date;
        }

        return $parts[2] . '.' . $parts[1] . '.' . $parts[0];
    }
}
