<?php

namespace Wiksoft\DbChessBundle\ContentElement;

use Contao\ContentElement;
use Contao\FrontendTemplate;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Config;
use Contao\System;

class ContentDbChessList extends ContentElement
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_dbChess_list_default';

    public function generate()
    {
        return parent::generate();
    }

    /**
     * Generate module
     */
    protected function compile()
    {
        $this->import('Database');
        System::loadLanguageFile('tl_dbChess_games');
        System::loadLanguageFile('tl_module');
        $gameslist = $collectionlist = array();

        // Partiesammlungen auswählen
        $arrCollection = StringUtil::deserialize($this->dbChess_list_collection, true);
        $collection = array();
        foreach ($arrCollection as $valueCollection) {
            $collection[] = 'pid=' . $valueCollection;
        }
        if ($collection) {
            $pidCollection = implode(' OR ', $collection);
        } else {
            $pidCollection = "pid IS NULL";
        }

        // Datenfelder um id und sid ergänzen
        $arrFields = StringUtil::deserialize($this->dbChess_list_fields, true);
        $arrFields[] = 'id';
        $arrFields[] = 'sid';

        // Feldbezeichnungen für die Tabellenkopfzeile vormerken (Zuweisung ans
        // Template erfolgt weiter unten, NACH einem möglichen Template-Wechsel)
        $fieldLabels = array();
        foreach ($arrFields as $f) {
            $fieldLabels[$f] = $GLOBALS['TL_LANG']['tl_dbChess_games'][$f][0] ?? $f;
        }

        $arrSorting = StringUtil::deserialize($this->dbChess_list_sortfields, true);

        // Erlaubte Spaltennamen für die ORDER-BY-Klausel (identisch mit den
        // 'options' von dbChess_list_sortfields in tl_content.php). Da
        // Spaltennamen sich nicht per Platzhalter parametrisieren lassen,
        // wird der Wert aus dem Backend-Auswahlfeld hier gegen diese feste
        // Liste geprüft, bevor er in die SQL-Query interpoliert wird.
        $arrAllowedSortFields = array('event', 'site', 'date', 'round', 'result', 'white', 'black', 'eco', 'whiteelo', 'blackelo', 'annotator', 'source');

        if ($this->dbChess_list_byorder == 'a') {
            $byOrder = ' ASC';
        } else {
            $byOrder = ' DESC';
        }
        $sorting = '';
        foreach ($arrSorting as $valueSorting) {
            if (!in_array($valueSorting, $arrAllowedSortFields, true)) {
                continue;
            }
            if ($sorting) {
                $sorting .= ', ';
            }
            if ($valueSorting == 'round') {
                $valueSorting = "CAST(round AS UNSIGNED)";
            }
            $sorting .= $valueSorting . $byOrder;
        }
        if ($sorting) {
            $sorting = " ORDER BY " . $sorting . ", gameFeatured DESC";
        } else {
            $sorting = " ORDER BY gameFeatured DESC";
        }

        $filter = $this->dbChess_list_filter;
        if ($filter) {
            $filter = StringUtil::decodeEntities("AND " . '(' . $filter) . ')';
        }

        $result = $this->Database->prepare("SELECT * FROM tl_dbChess_games WHERE (" . $pidCollection . ") " . $filter . $sorting)->execute();
        while ($result->next()) {
            $game = array();
            if ($this->dbChess_list_jumpTo) {
                $objJumpTo = PageModel::findByPk($this->dbChess_list_jumpTo);
                $game['href'] = $objJumpTo?->getFrontendUrl(((Config::get('useAutoItem') && !Config::get('disableAlias')) ? '/' : '/items/') . $result->alias);
            }
            foreach ($arrFields as $key => $valueFields) {
                if ($arrFields[$key] == 'pgn') {
                    $game[$arrFields[$key]] = StringUtil::substr($result->$valueFields, 30);
                } elseif ($arrFields[$key] == 'date') {
                    $arrDate = explode('.', $result->$valueFields);
                    $game[$arrFields[$key]] = $arrDate[2] . '.' . $arrDate[1] . '.' . $arrDate[0];
                } else {
                    $game[$arrFields[$key]] = $result->$valueFields;
                }
            }
            $gameslist[] = $game;
            unset($game);
        }

        // Verknüpfte Partien nur einmalig anzeigen
        foreach ($gameslist as $key => $value) {
            if (!isset($gameslist[$key])) {
                continue;
            }
            if ($gameslist[$key]['sid']) {
                $sid = StringUtil::deserialize($gameslist[$key]['sid']);
                $gameslist[$key]['sid'] = count($sid);
                foreach ($sid as $valueSid) {
                    if ($valueSid != $gameslist[$key]['id']) {
                        foreach ($gameslist as $sidKey => $sidValue) {
                            if ($gameslist[$sidKey]['id'] == $valueSid) {
                                if (isset($gameslist[$key]['annotator']) && !strstr($gameslist[$key]['annotator'], $gameslist[$sidKey]['annotator'])) {
                                    if ($gameslist[$key]['annotator']) {
                                        $gameslist[$key]['annotator'] .= '; ';
                                    }
                                    $gameslist[$key]['annotator'] .= $gameslist[$sidKey]['annotator'];
                                }
                                if (isset($gameslist[$key]['source']) && !strstr($gameslist[$key]['source'], $gameslist[$sidKey]['source'])) {
                                    if ($gameslist[$key]['source']) {
                                        $gameslist[$key]['source'] .= '; ';
                                    }
                                    $gameslist[$key]['source'] .= $gameslist[$sidKey]['source'];
                                }
                                unset($gameslist[$sidKey]);
                            }
                        }
                    }
                }
            }
        }

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
}
