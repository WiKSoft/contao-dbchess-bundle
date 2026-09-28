<?php

namespace Wiksoft\DbChessBundle\Controller\BackendModule;

use Contao\Backend;
use Contao\CoreBundle\Security\DataContainer\UpdateAction;
use Contao\DataContainer;
use Contao\Input;
use Contao\Message;
use Wiksoft\DbChessBundle\Helper\BackendAccess;

class DbChessUnlinkGameController extends Backend
{
    use ConfirmFormTrait;

    public function unlinkGame(DataContainer $dc)
    {
        if (Input::get('key') != 'unlinkGame') {
            return '';
        }

        BackendAccess::collection((int) $dc->id);

        if (!$this->isConfirmed('tl_dbChess_unlinkGame')) {
            return $this->confirmForm(
                'tl_dbChess_unlinkGame',
                'unlinkGame',
                $GLOBALS['TL_LANG']['tl_dbChess_games']['unlinkGame'][1],
                $GLOBALS['TL_LANG']['tl_dbChess_games']['unlinkGameConfirm'],
                $GLOBALS['TL_LANG']['tl_dbChess_games']['unlinkGame'][0]
            );
        }

        $this->import('Database');
        $arrNew = ['sid' => '', 'gameLink' => '0'];
        $result = $this->Database->prepare("SELECT * FROM tl_dbChess_games WHERE pid=? AND sid!=''")->execute($dc->id);

        // Erst alle Rechte prüfen, dann ändern - sonst bliebe bei einer
        // verweigerten Partie eine halb gelöste Sammlung zurück
        $ids = [];
        while ($result->next()) {
            BackendAccess::denyUnlessGranted(new UpdateAction('tl_dbChess_games', $result->row(), $arrNew));
            $ids[] = (int) $result->id;
        }

        if ($ids) {
            $this->Database->prepare("UPDATE tl_dbChess_games %s WHERE id IN (" . implode(',', array_fill(0, \count($ids), '?')) . ")")
                ->set($arrNew)
                ->execute(...$ids);
        }

        Message::addConfirmation($GLOBALS['TL_LANG']['tl_dbChess_games']['unlinkGame'][2]);
        $this->redirect($this->backUrl('unlinkGame'));
    }
}
