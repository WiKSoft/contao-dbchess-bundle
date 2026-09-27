<?php

namespace Wiksoft\DbChessBundle\Controller\BackendModule;

use Contao\Backend;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Input;
use Contao\StringUtil;

class DbChessUnlinkGameController extends Backend
{
    public function unlinkGame(DataContainer $dc)
    {
        if (Input::get('key') != 'unlinkGame') {
            return '';
        }
        $this->import('Database');
        $this->Database->prepare("UPDATE tl_dbChess_games SET sid=?, gameLink='0' WHERE pid=?")->execute('', $dc->id);

        return '
<div id="tl_buttons">
    <a href="' . StringUtil::ampersand(str_replace('&key=unlinkGame', '', Environment::get('request'))) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>
<h2 class="sub_headline">' . $GLOBALS['TL_LANG']['tl_dbChess_games']['unlinkGame'][1] . '</h2>
<div class="tl_box"><p class="tl_confirm">' . $GLOBALS['TL_LANG']['tl_dbChess_games']['unlinkGame'][2] . '</p></div>
';
    }
}
