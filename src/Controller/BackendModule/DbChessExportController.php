<?php

namespace Wiksoft\DbChessBundle\Controller\BackendModule;

use Contao\Backend;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Wiksoft\DbChessBundle\Pgn\PgnWriter;

class DbChessExportController extends Backend
{
    public function exportPgn(DataContainer $dc)
    {
        if (Input::get('key') != 'exportPgn') {
            return '';
        }

        $games = $this->Database->prepare("SELECT * FROM tl_dbChess_games WHERE pid=? ORDER BY date")
            ->execute($dc->id)
            ->fetchAllAssoc();

        // Dateiname generieren
        $collection = $this->Database->prepare("SELECT name FROM tl_dbChess_collection WHERE id=?")
            ->limit(1)
            ->execute($dc->id);
        $exportFile = StringUtil::generateAlias(StringUtil::decodeEntities((string) $collection->name)) . date('_Ymd-Hi') . '.pgn';

        $response = new Response(PgnWriter::games($games));
        $response->headers->set('Content-Type', 'application/x-chess-pgn; charset=UTF-8');
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $exportFile, preg_replace('/[^\x20-\x7e]|[%\/\\\\]/', '_', $exportFile)));
        $response->headers->set('Cache-Control', 'no-store');

        throw new ResponseException($response);
    }
}
