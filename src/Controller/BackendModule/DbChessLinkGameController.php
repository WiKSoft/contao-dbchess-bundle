<?php

namespace Wiksoft\DbChessBundle\Controller\BackendModule;

use Contao\Backend;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Input;
use Contao\StringUtil;

class DbChessLinkGameController extends Backend
{
    public function linkGame(DataContainer $dc)
    {
        if (Input::get('key') != 'linkGame') {
            return '';
        }
        $this->import('Database');
        $i = 0;
        $result = $this->Database->prepare("SELECT * FROM tl_dbChess_games WHERE pid=?")->execute($dc->id);
        while ($result->next()) {
            $row = $result->row();
            $arrSet = array();
            $arrLink = $this->Database->prepare("SELECT * FROM tl_dbChess_games WHERE id<>? AND pid=? AND date=? AND event=? AND site=? AND round=? AND white=? AND black=? AND result=? ORDER BY date ASC")
                ->execute($row['id'], $row['pid'], $row['date'], $row['event'], $row['site'], $row['round'], $row['white'], $row['black'], $row['result']);

            while ($arrLink->next()) {
                $rowLink = $arrLink->row();
                $arrSet[] = $rowLink['id'];
            }
            if ($arrSet) {
                $arrSet[] = $row['id'];
                sort($arrSet);
                $sid = serialize($arrSet);
                $this->Database->prepare("UPDATE tl_dbChess_games SET sid=?, gameLink='1' WHERE id=?")->execute($sid, $row['id']);
                $i++;
            }
        }

        return '
<div id="tl_buttons">
    <a href="' . StringUtil::ampersand(str_replace('&key=linkGame', '', Environment::get('request'))) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>
<h2 class="sub_headline">' . $GLOBALS['TL_LANG']['tl_dbChess_games']['linkGame'][1] . '</h2>
<div class="tl_box"><p class="tl_confirm">' . $GLOBALS['TL_LANG']['tl_dbChess_games']['linkGame'][2] . $i . '</p></div>
';
    }
}
