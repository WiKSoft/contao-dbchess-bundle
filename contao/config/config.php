<?php

use Wiksoft\DbChessBundle\ContentElement\ContentDbChessDownload;
use Wiksoft\DbChessBundle\ContentElement\ContentDbChessList;
use Wiksoft\DbChessBundle\Controller\BackendModule\DbChessExportController;
use Wiksoft\DbChessBundle\Controller\BackendModule\DbChessImportController;
use Wiksoft\DbChessBundle\Controller\BackendModule\DbChessLinkGameController;
use Wiksoft\DbChessBundle\Controller\BackendModule\DbChessUnlinkGameController;
use Wiksoft\DbChessBundle\Module\ModuleDbChessIndex;

/**
 * -------------------------------------------------------------------------
 * CONTENT ELEMENTS
 * -------------------------------------------------------------------------
 */
$GLOBALS['TL_CTE']['schach']['dbChess_list'] = ContentDbChessList::class;
$GLOBALS['TL_CTE']['schach']['dbChess_download'] = ContentDbChessDownload::class;

/**
 * CSS files
 * Hinweis: Pfad gilt nach "assets:install" (läuft im Contao Manager automatisch).
 * Den tatsächlichen Ordnernamen unter web/bundles/ nach der Installation prüfen.
 */
$GLOBALS['TL_CSS'][] = 'bundles/wiksoftdbchess/dbChess.css';

/**
 * Back end modules
 */
$GLOBALS['BE_MOD']['content']['dbChess_eco'] = array(
    'tables' => array('tl_dbChess_eco'),
    'icon' => 'bundles/wiksoftdbchess/images/iconEco.png',
);

$GLOBALS['BE_MOD']['content']['dbChess_collection'] = array(
    'tables' => array('tl_dbChess_collection', 'tl_dbChess_games'),
    'icon' => 'bundles/wiksoftdbchess/images/iconBoard.png',
);

$GLOBALS['BE_MOD']['content']['dbChess_collection']['exportPgn'] = array(DbChessExportController::class, 'exportPgn');
$GLOBALS['BE_MOD']['content']['dbChess_collection']['importPgn'] = array(DbChessImportController::class, 'importPgn');
$GLOBALS['BE_MOD']['content']['dbChess_collection']['linkGame'] = array(DbChessLinkGameController::class, 'linkGame');
$GLOBALS['BE_MOD']['content']['dbChess_collection']['unlinkGame'] = array(DbChessUnlinkGameController::class, 'unlinkGame');

/**
 * Front end modules
 */
$GLOBALS['FE_MOD']['schach']['dbChess_index'] = ModuleDbChessIndex::class;
