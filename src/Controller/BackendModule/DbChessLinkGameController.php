<?php

namespace Wiksoft\DbChessBundle\Controller\BackendModule;

use Contao\Backend;
use Contao\CoreBundle\Security\DataContainer\UpdateAction;
use Contao\DataContainer;
use Contao\Input;
use Contao\Message;
use Wiksoft\DbChessBundle\Helper\BackendAccess;

class DbChessLinkGameController extends Backend
{
    use ConfirmFormTrait;

    public function linkGame(DataContainer $dc)
    {
        if (Input::get('key') != 'linkGame') {
            return '';
        }

        BackendAccess::collection((int) $dc->id);

        if (!$this->isConfirmed('tl_dbChess_linkGame')) {
            return $this->confirmForm(
                'tl_dbChess_linkGame',
                'linkGame',
                $GLOBALS['TL_LANG']['tl_dbChess_games']['linkGame'][1],
                $GLOBALS['TL_LANG']['tl_dbChess_games']['linkGameConfirm'],
                $GLOBALS['TL_LANG']['tl_dbChess_games']['linkGame'][0]
            );
        }

        $this->import('Database');
        $updates = [];
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
                $arrNew = ['sid' => serialize($arrSet), 'gameLink' => '1'];
                BackendAccess::denyUnlessGranted(new UpdateAction('tl_dbChess_games', $row, $arrNew));
                $updates[(int) $row['id']] = $arrNew;
            }
        }

        // Erst alle Rechte prüfen (oben), dann ändern - sonst bliebe bei einer
        // verweigerten Partie eine halb verknüpfte Sammlung zurück
        foreach ($updates as $id => $arrNew) {
            $this->Database->prepare("UPDATE tl_dbChess_games %s WHERE id=?")->set($arrNew)->execute($id);
        }

        Message::addConfirmation($GLOBALS['TL_LANG']['tl_dbChess_games']['linkGame'][2] . \count($updates));
        $this->redirect($this->backUrl('linkGame'));
    }
}
