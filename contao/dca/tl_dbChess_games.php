<?php

use Contao\Backend;
use Contao\Config;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\StringUtil;

/**
 * Table tl_dbChess_games
 */
$GLOBALS['TL_DCA']['tl_dbChess_games'] = array
    (
    // Config
    'config' => array
        (
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_dbChess_collection',
        'enableVersioning' => true,
        'sql' => array
            (
            'keys' => array
                (
                'id' => 'primary',
                'pid' => 'index'
            )
        ),
        'onsubmit_callback' => array
            (
            array('tl_dbChess_games', 'saveGame')
        ),
        'ondelete_callback' => array
            (
            array('tl_dbChess_games', 'deleteGame')
        ),
        'oncut_callback' => array
            (
            array('tl_dbChess_games', 'cutGame')
        ),
    ),
    // List
    'list' => array
        (
        'sorting' => array
            (
            'mode' => 4,
            'flag' => 1,
            'fields' => array('date'),
            'headerFields' => array('name'),
            'panelLayout' => 'filter;sort,search,limit',
            'child_record_callback' => array('tl_dbChess_games', 'showGame')
        ),
        'label' => array
            (
            'showColumns' => false,
            'fields' => array('date', 'white', 'result', 'black', 'event', 'round', 'source', 'annotator', 'sid', 'pgn'),
            'format' => '<strong>%s: %s %s %s</strong><br>%s (%s)<br>%s [%s] <span style="color: red;float:right">%s</span><br><em>%s</em>',
            'maxCharacters' => 200
        ),
        'global_operations' => array
            (
            'linkGame' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['linkGame'],
                'href' => 'key=linkGame',
                'class' => 'header_linkGame',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ),
            'unlinkGame' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['unlinkGame'],
                'href' => 'key=unlinkGame',
                'class' => 'header_unlinkGame',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ),
            'importPgn' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['importPgn'],
                'href' => 'key=importPgn',
                'class' => 'header_pgn_import',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ),
            'exportPgn' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['exportPgn'],
                'href' => 'key=exportPgn',
                'class' => 'header_pgn_export',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ),
            'all' => array
                (
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ),
        'operations' => array
            (
            'edit' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif'
            ),
            'copy' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['copy'],
                'href' => 'act=paste&amp;mode=copy',
                'icon' => 'copy.gif',
                'attributes' => 'onclick="Backend.getScrollOffset()"'
            ),
            'cut' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['cut'],
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.gif',
                'attributes' => 'onclick="Backend.getScrollOffset()"'
            ),
            'delete' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"',
            ),
            'show' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif'
            ),
        )
    ),
    // Palettes
    'palettes' => array
        (
        'default' => '{gameSevenTags_legend},event,site,date,round,white,black,result;{gameTags_legend},eco,whiteelo,blackelo,source,annotator;{gameMoveTag_legend},fen,pgn;{gameOption_legend},alias,gameLink,gameFeatured,remark'
    ),
    // Fields
    'fields' => array
        (
        'id' => array
            (
            'search' => true,
            'sorting' => true,
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array
            (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'pid' => array
            (
            'foreignKey' => 'tl_dbChess_collection.name',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => array('type' => 'belongsTo', 'load' => 'eager')
        ),
        'sid' => array
            (
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'gameLink' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['gameLink'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'filter' => true,
            'load_callback' => array
                (
                array('tl_dbChess_games', 'loadFieldSid')
            ),
            'save_callback' => array
                (
                array('tl_dbChess_games', 'saveFieldSid')
            ),
            'eval' => array('tl_class' => 'clr long'),
            'sql' => "char(1) NOT NULL default ''"
        ),
        'gameFeatured' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['gameFeatured'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'filter' => true,
            'eval' => array('includeBlankOption' => TRUE, 'submitOnChange' => FALSE, 'multiple' => FALSE, 'tl_class' => 'clr long'),
            'sql' => "char(1) NOT NULL default ''"
        ),
        'alias' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['alias'],
            'exclude' => true,
            'inputType' => 'text',
            'search' => true,
            'eval' => array('rgxp' => 'alias', 'doNotCopy' => true, 'maxlength' => 128, 'tl_class' => 'w50'),
            'save_callback' => array
                (
                array('tl_dbChess_games', 'generateAlias')
            ),
            'sql' => "varchar(255) BINARY NOT NULL default ''"
        ),
        'event' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['event'],
            'save_callback' => array
                (
                array('tl_dbChess_games', 'saveFieldSevenTagRoster')
            ),
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => array('mandatory' => false, 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'site' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['site'],
            'save_callback' => array
                (
                array('tl_dbChess_games', 'saveFieldSevenTagRoster')
            ),
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => array('decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'date' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['date'],
            'save_callback' => array
                (
                array('tl_dbChess_games', 'saveFieldDate')
            ),
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => array('decodeEntities' => true, 'maxlength' => 10, 'tl_class' => 'w50'),
            'sql' => "varchar(10) NOT NULL default ''"
        ),
        'round' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['round'],
            'save_callback' => array
                (
                array('tl_dbChess_games', 'saveFieldSevenTagRoster')
            ),
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => array('decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'white' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['white'],
            'save_callback' => array
                (
                array('tl_dbChess_games', 'saveFieldSevenTagRoster')
            ),
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => array('decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'includeBlankOption' => 'true', 'blankOptionLabel' => 'NN'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'black' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['black'],
            'save_callback' => array
                (
                array('tl_dbChess_games', 'saveFieldSevenTagRoster')
            ),
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => array('decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'result' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['result'],
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'select',
            'options' => array('*', '1-0', '1/2-1/2', '0-1'),
            'eval' => array('tl_class' => 'w50'),
            'sql' => "varchar(7) NOT NULL default ''"
        ),
        'eco' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['eco'],
            'load_callback' => array
                (
                array('tl_dbChess_games', 'loadFieldEco')
            ),
            'save_callback' => array
                (
                array('tl_dbChess_games', 'saveFieldEco')
            ),
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'select',
            'foreignKey' => 'tl_dbChess_eco.ecoCode',
            'eval' => array('includeBlankOption' => TRUE, 'tl_class' => 'w50'),
            'sql' => "varchar(3) NOT NULL default ''"
        ),
        'whiteelo' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['whiteelo'],
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => array('rgxp' => 'digit', 'maxlength' => 4, 'tl_class' => 'clr w50'),
            'sql' => "varchar(4) NOT NULL default ''"
        ),
        'blackelo' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['blackelo'],
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => array('rgxp' => 'digit', 'maxlength' => 4, 'tl_class' => 'w50'),
            'sql' => "varchar(4) NOT NULL default ''"
        ),
        'annotator' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['annotator'],
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => array('decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'source' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['source'],
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => array('decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'clr long'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'fen' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['fen'],
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => array('decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'clr long'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'pgn' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['pgn'],
            'save_callback' => array
                (
                array('tl_dbChess_games', 'saveFieldPgn')
            ),
            'exclude' => true,
            'filter' => false,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'textarea',
            'eval' => array('decodeEntities' => true, 'cols' => '20', 'rows' => '10', 'tl_class' => 'clr'),
            'sql' => "text NULL"
        ),
        'remark' => array
            (
            'label' => &$GLOBALS['TL_LANG']['tl_dbChess_games']['remark'],
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'textarea',
            'eval' => array('decodeEntities' => false, 'rte' => 'tinyMCE', 'cols' => '20', 'rows' => '10', 'tl_class' => 'clr'),
            'sql' => "text NULL"
        ),
    )
);

class tl_dbChess_games extends Backend {

    public function saveGame(DataContainer $dc) {
        // Return if there is no active record (override all)
        if (!$dc->activeRecord) {
            return;
        }
        $this->import('Database');
        if ($dc->activeRecord->gameLink) {
            // Partien verknüpfen
            // ID's der zu verknüpfenden Partien
            $arrSid = $this->sidToIntIds(StringUtil::deserialize($dc->activeRecord->sid, true));
            if (!$arrSid) {
                return;
            }
            sort($arrSid);
            $sid = serialize($arrSid);
            // 'sid' sowie "Seven Tag Roster" Felder in allen verknüpften Partien speichern
            $arrNewSet = array('event' => $dc->activeRecord->event,
                'site' => $dc->activeRecord->site,
                'date' => $dc->activeRecord->date,
                'round' => $dc->activeRecord->round,
                'white' => $dc->activeRecord->white,
                'black' => $dc->activeRecord->black,
                'result' => $dc->activeRecord->result,
                'sid' => $sid,
                'gameLink' => '1');
            $placeholders = implode(',', array_fill(0, count($arrSid), '?'));
            $result = $this->Database->prepare("SELECT * FROM tl_dbChess_games WHERE id IN ($placeholders)")->execute(...$arrSid);
            while ($result->next()) {
                $row = $result->row();
                $this->Database->prepare("UPDATE tl_dbChess_games %s WHERE id=?")
                        ->set($arrNewSet)
                        ->execute($row['id']);
            }
        } else {
            // wenn Verknüpfung der Partie gelöscht wurde, sid und gameLink der anderen Partien ebenfalls löschen
            $arrSid = $this->sidToIntIds(StringUtil::deserialize($dc->activeRecord->sid, true));
            if (!$arrSid) {
                return;
            }
            $placeholders = implode(',', array_fill(0, count($arrSid), '?'));
            $result = $this->Database->prepare("SELECT id FROM tl_dbChess_games WHERE id IN ($placeholders)")->execute(...$arrSid);
            while ($result->next()) {
                $row = $result->row();
                $this->Database->prepare("UPDATE tl_dbChess_games SET sid='', gameLink='0' WHERE id=?")->execute($row['id']);
            }
        }
    }

    public function deleteGame(DataContainer $dc) {
        // Partie wird gelöscht
        // Datensatz der gelöscht wird auswählen
        $result = $this->Database->prepare("SELECT * FROM tl_dbChess_games WHERE id=?")
                ->limit(1)
                ->execute($dc->id);
        $row = $result->row();
        // zurück wenn Partie nicht verknüpft war
        if (!$row['sid']) {
            return;
        }
        // id aus der sid entfernen
        $arrSid = $this->sidToIntIds(StringUtil::deserialize($row['sid'], true));
        $key = array_search((int) $dc->id, $arrSid, true);
        if ($key !== false) {
            unset($arrSid[$key]);
        }
        $arrSid = array_values($arrSid);

        if (!$arrSid) {
            return;
        }

        if (count($arrSid) == 1) {
            // sid löschen wenn alleine
            $sid = '';
            $gameLink = '0';
        } else {
            // sid der verbleibenen Partien
            $sid = serialize($arrSid);
            $gameLink = '1';
        }
        // sid der verknüpften Partien neu schreiben
        $placeholders = implode(',', array_fill(0, count($arrSid), '?'));
        $result = $this->Database->prepare("SELECT * FROM tl_dbChess_games WHERE id IN ($placeholders)")->execute(...$arrSid);
        while ($result->next()) {
            $row = $result->row();
            $this->Database->prepare("UPDATE tl_dbChess_games SET sid=?, gameLink=? WHERE id=?")->execute($sid, $gameLink, $row['id']);
        }
    }

    public function cutGame(DataContainer $dc) {
        // Partie wird verschoben
        // Datensatz der verschoben wird auswählen
        $result = $this->Database->prepare("SELECT * FROM tl_dbChess_games WHERE id=?")
                ->limit(1)
                ->execute($dc->id);
        $row = $result->row();
        // zurück wenn Partie nicht verknüpft war
        if (!$row['sid']) {
            return;
        }

        // id aus der sid entfernen
        $arrSid = $this->sidToIntIds(StringUtil::deserialize($row['sid'], true));
        $key = array_search((int) $dc->id, $arrSid, true);
        if ($key !== false) {
            unset($arrSid[$key]);
        }
        $arrSid = array_values($arrSid);
        $pidNew = $row['pid'];

        if (!$arrSid) {
            return;
        }

        if (count($arrSid) == 1) {
            // sid löschen wenn alleine
            $sid = '';
            $gameLink = '0';
        } else {
            // sid der verbleibenen Partien
            $sid = serialize($arrSid);
            $gameLink = '1';
        }
        // sid der verknüpften Partien neu schreiben
        $placeholders = implode(',', array_fill(0, count($arrSid), '?'));
        $result = $this->Database->prepare("SELECT * FROM tl_dbChess_games WHERE id IN ($placeholders)")->execute(...$arrSid);
        while ($result->next()) {
            $row = $result->row();
            if ($pidNew == $row['pid']) {
                // Abbruch wenn alte und neue pid identisch
                return;
            }
            $this->Database->prepare("UPDATE tl_dbChess_games SET sid=?, gameLink=? WHERE id=?")->execute($sid, $gameLink, $row['id']);
        }
        // sid in der verschobenen Partie löschen
        $this->Database->prepare("UPDATE tl_dbChess_games SET sid='', gameLink='0' WHERE id=?")->execute($dc->id);
    }

    /**
     * Wandelt deserialisierte 'sid'-Werte in eine bereinigte Liste von
     * Partie-IDs (int) um. Nicht-numerische bzw. leere Einträge werden
     * verworfen, damit sie nicht ungeprüft in eine SQL-WHERE-Bedingung
     * gelangen können (z.B. falls 'sid' durch einen manipulierten
     * PGN-Import mit beliebigem Inhalt befüllt wurde).
     *
     * @param array $arrSid
     * @return array<int, int>
     */
    private function sidToIntIds(array $arrSid): array {
        return array_values(array_unique(array_filter(array_map('intval', $arrSid))));
    }

    public function loadFieldSid($field, DataContainer $dc) {
        // Partien mit identischem Seven-Tag-Roster in der aktuellen Partiesammlung suchen und auswählen
        $result = $this->Database->prepare("SELECT id FROM tl_dbChess_games WHERE pid=? AND date=? AND event=? AND site=? AND round=? AND white=? AND black=? AND result=? ORDER BY date ASC")
                ->execute($dc->activeRecord->pid, $dc->activeRecord->date, $dc->activeRecord->event, $dc->activeRecord->site, $dc->activeRecord->round, $dc->activeRecord->white, $dc->activeRecord->black, $dc->activeRecord->result);
        $arrSet = array();
        while ($result->next()) {
            $row = $result->row();
            $arrSet[] = $row['id'];
        }
        if (count($arrSet) === 1) {
            // keine zu verknüpfenden Partien vorhanden > Auswahlfeld 'disabled'
            $GLOBALS['TL_DCA']['tl_dbChess_games']['fields']['gameLink']['eval']['disabled'] = true;
            $GLOBALS['TL_LANG']['tl_dbChess_games']['gameLink'][1] = $GLOBALS['TL_LANG']['tl_dbChess_games']['gameLink'][3];
        } else {
            // id der zu verknüpfenden Partien anzeigen
            $GLOBALS['TL_LANG']['tl_dbChess_games']['gameLink'][1] = $GLOBALS['TL_LANG']['tl_dbChess_games']['gameLink'][2] . implode(', ', $arrSet);
        }
        return $field;
    }

    public function saveFieldSid($field, DataContainer $dc) {
        // wenn Partien verknüpft werden sollen, die ID's im Feld 'sid' als array speichern
        if ($field) {
            $result = $this->Database->prepare("SELECT id FROM tl_dbChess_games WHERE pid=? AND date=? AND event=? AND site=? AND round=? AND white=? AND black=? AND result=? ORDER BY date ASC")
                    ->execute($dc->activeRecord->pid, $dc->activeRecord->date, $dc->activeRecord->event, $dc->activeRecord->site, $dc->activeRecord->round, $dc->activeRecord->white, $dc->activeRecord->black, $dc->activeRecord->result);
            $arrSet = array();
            while ($result->next()) {
                $row = $result->row();
                $arrSet[] = $row['id'];
            }
            $arrSet = serialize($arrSet);
            $dc->activeRecord->sid = $arrSet;
        }
        return $field;
    }

    public function saveFieldPgn($field, DataContainer $dc) {
        /* Zeilenumbrüche und überflüssige Leerzeichen entfernen */
        $field = str_replace("\r", " ", $field);
        $field = str_replace("\n", " ", $field);
        $field = preg_replace('/ {2,}/', ' ', $field);
        // überprüfen ob Ergebnis am Ende des Notation steht
        if (strrpos($field, $dc->activeRecord->result, -1) === FALSE || strrpos($field, $dc->activeRecord->result, -1) + strlen($dc->activeRecord->result) !== strlen($field)) {
            $field .= ' ' . $dc->activeRecord->result;
        }
        return $field;
    }

    public function saveFieldSevenTagRoster($field, DataContainer $dc) {
        // Felder des Seven-Tag-Roster wenn leer, mit einem '?' füllen
        if (!$field) {
            $field = '?';
        }
        return $field;
    }

    public function saveFieldDate($field, DataContainer $dc) {
        // date auf Gültigkeit prüfen
        if (!$field) {
            // date ist leer
            $field = '????.??.??';
            return $field;
        }
        if (strlen($field) == 10 && substr($field, 4, 1) == '.' && substr($field, 7, 1) == '.') {
            $zeichen = count_chars($field, 1);
            foreach ($zeichen as $key => $value) {
                if (($key < 48 || $key > 57) && $key != 46 && $key != 63) {
                    throw new Exception($GLOBALS['TL_LANG']['tl_dbChess_games']['errorDate']);
                }
            }
            return $field;
        }
        throw new Exception($GLOBALS['TL_LANG']['tl_dbChess_games']['errorDate']);
    }

    public function loadFieldEco($field, DataContainer $dc) {
        // 'id' des ECO-Codes aus der Datenbank in das Feld 'eco' eintragen
        $this->import('Database');
        $objSession = $this->Database->prepare("SELECT * FROM tl_dbChess_eco WHERE ecoCode=?")->execute($field);
        if (!$objSession->id) {
            $field = '';
        } else {
            $field = $objSession->id;
        }
        return $field;
    }

    public function saveFieldEco($field, DataContainer $dc) {
        // Feld 'eco', mittels 'ecoCode' der Tabelle 'tl_dbChess_eco', speichern
        $objSession = $this->Database->prepare("SELECT * FROM tl_dbChess_eco WHERE id=?")->execute($field);
        return $objSession->ecoCode;
    }

    public function showGame($arrRow) {
        // Anzeige der Partien im BE
        $class = 'limit_height';

        // Limit the element's height
        if (!Config::get('doNotCollapse')) {
            $class .= ' h64';
        }

        $annotator = '';
        if ($arrRow['annotator']) {
            $annotator = ' [' . StringUtil::specialchars($arrRow['annotator']) . ']';
        }
        $source = '';
        if ($arrRow['source']) {
            $source = ' &copy;' . StringUtil::specialchars($arrRow['source']);
        }
        $sid = '';
        if ($arrRow['sid']) {
            // Escaped, da 'sid' theoretisch auch nicht-numerische Werte enthalten
            // könnte (z.B. durch einen manipulierten PGN-Import), die hier sonst
            // ungeprüft in die Backend-Ausgabe fließen würden.
            $sid = implode(', ', array_map(
                static fn ($value) => StringUtil::specialchars((string) $value),
                StringUtil::deserialize($arrRow['sid'], true)
            ));
        }
        $featured = 'none';
        if ($arrRow['gameFeatured']) {
            $featured = '1px solid blue';
        }

        // Alle Feldwerte escapen, da diese Ausgabe (anders als die Twig-Frontend-
        // Templates) kein automatisches Escaping hat und direkt aus Partiedaten
        // gespeist wird, die z.B. per PGN-Import befüllt werden können.
        $game = '<div class="' . trim($class) . '"><strong>' . StringUtil::specialchars($arrRow['date']) . ': ' . StringUtil::specialchars($arrRow['white']) . ' ' . StringUtil::specialchars($arrRow['result']) . ' ' . StringUtil::specialchars($arrRow['black'])
                . ' [' . StringUtil::specialchars($arrRow['eco']) . ']</strong>' . '<span style="color: blue;float:right;border: ' . $featured . '">' . (int) $arrRow['id'] . '</span><br>' . StringUtil::specialchars($arrRow['event'])
                . ' (' . StringUtil::specialchars($arrRow['round']) . '), ' . StringUtil::specialchars($arrRow['site']) . '<br>' . $source . $annotator
                . '<span style="color: red;float:right">' . $sid . '</span><br><em>' . StringUtil::specialchars($arrRow['pgn']) . '</em></div>' . "\n";

        return $game;
    }

    public function generateAlias($varValue, DataContainer $dc) {
        $autoAlias = false;

        // generiert ein alias wenn nicht vorhanden
        if ($varValue == '') {
            $autoAlias = true;
            $white = $black = '_';
            $date = $site = $event = $round = '';
            if ($dc->activeRecord->white != '?') {
                $arrWhite = explode(',', $dc->activeRecord->white);
                $white = $arrWhite[0];
            }
            if ($dc->activeRecord->black != '?') {
                $arrBlack = explode(',', $dc->activeRecord->black);
                $black = $arrBlack[0];
            }
            if (substr($dc->activeRecord->date, 0, 4) != '????') {
                $arrDate = explode('.', $dc->activeRecord->date);
                $date = $arrDate[0];
            }
            if ($dc->activeRecord->site != '?') {
                $site = '_' . $dc->activeRecord->site;
            }
            if ($dc->activeRecord->event != '?') {
                $event = '_' . $dc->activeRecord->event;
            }
            if ($dc->activeRecord->round != '?') {
                $round = '_' . $dc->activeRecord->round;
            }
            $varValue = $white . '-' . $black . '_' . $date . $site . $event . $round;
            $varValue = StringUtil::generateAlias($varValue);
        }

        $objAlias = $this->Database->prepare("SELECT id FROM tl_dbChess_games WHERE id=? OR alias=?")
                ->execute($dc->id, $varValue);

        // überprüfen ob der alias bereits existiert
        if ($objAlias->numRows > 1) {
            if (!$autoAlias) {
                throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
            }
            // alias existiert, id anhängen
            $varValue .= '-id-' . $dc->id;
        }

        return $varValue;
    }

}
