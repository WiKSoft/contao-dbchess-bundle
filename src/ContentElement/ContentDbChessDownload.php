<?php

declare(strict_types=1);

namespace Wiksoft\DbChessBundle\ContentElement;

use Contao\Config;
use Contao\ContentElement;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\Database;
use Contao\Environment;
use Contao\File;
use Contao\Folder;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Wiksoft\DbChessBundle\Helper\GameQuery;
use Wiksoft\DbChessBundle\Pgn\PgnWriter;

class ContentDbChessDownload extends ContentElement
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_dbChess_download';

    private int $partien_gesamt = 0;

    private string $dbChess_downloadfile = '';

    private int $dbChess_downloadfilesize = 0;

    public function generate()
    {
        [$where, $params] = GameQuery::collectionWhere($this->dbChess_list_collection);
        $filter = GameQuery::filterClause($this->dbChess_list_filter);
        $sorting = GameQuery::sortingClause($this->dbChess_list_sortfields, $this->dbChess_list_byorder);

        $gameslist = Database::getInstance()
            ->prepare("SELECT * FROM tl_dbChess_games WHERE ($where) $filter $sorting")
            ->execute(...$params)
            ->fetchAllAssoc();

        if ($this->dbChess_dl_featured) {
            // Aus verknüpften Partien nur die erste (hervorgehobene) behalten
            $gameslist = GameQuery::removeLinkedDuplicates($gameslist);
        }

        $this->partien_gesamt = \count($gameslist);

        $pgnFile = System::getContainer()->getParameter('contao.upload_path') . '/dbChess/' . $this->buildFileName($gameslist) . '.pgn';
        $pgnText = PgnWriter::games($gameslist);

        // Die Datei wird NUR bei einem tatsächlichen Download geschrieben
        // (Query-Parameter "file" passt exakt zum erwarteten Pfad). Ein reiner
        // Seitenaufruf schreibt nichts auf die Platte.
        $requestedFile = Input::get('file', true);

        if ($requestedFile !== null && $requestedFile === $pgnFile) {
            $this->sendDownload($pgnFile, $pgnText);
        }

        $this->dbChess_downloadfile = $pgnFile;
        $this->dbChess_downloadfilesize = \strlen($pgnText);

        return parent::generate();
    }

    /**
     * Dateiname: basiert auf dem Link-Titel, sonst auf dem Alias der ersten
     * Partie. Die ID des Inhaltselements wird immer angehängt, damit sich
     * zwei Download-Elemente mit unterschiedlichem Filter nie eine Datei
     * teilen.
     *
     * @param array<int, array<string, mixed>> $gameslist
     */
    private function buildFileName(array $gameslist): string
    {
        $name = $this->linkTitle ? StringUtil::generateAlias($this->linkTitle) : (string) ($gameslist[array_key_first($gameslist)]['alias'] ?? '');

        return ($name !== '' ? $name : 'dbChess-download') . '-id' . $this->id;
    }

    /**
     * Schreibt die PGN-Datei (nur wenn sich der Inhalt geändert hat) und
     * liefert sie an den Browser aus.
     */
    private function sendDownload(string $pgnFile, string $pgnText): void
    {
        $allowedDownload = StringUtil::trimsplit(',', strtolower((string) Config::get('allowedDownload')));

        if (!\in_array('pgn', $allowedDownload, true)) {
            return;
        }

        $projectDir = System::getContainer()->getParameter('kernel.project_dir');
        $absolutePath = $projectDir . '/' . $pgnFile;

        if (!is_file($absolutePath) || md5_file($absolutePath) !== md5($pgnText)) {
            new Folder(\dirname($pgnFile));

            // File::close() registriert die Datei auch in der Dateiverwaltung
            $objFile = new File($pgnFile);
            $objFile->write($pgnText);
            $objFile->close();
        }

        $response = new BinaryFileResponse($absolutePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($pgnFile));
        $response->headers->set('Content-Type', 'application/x-chess-pgn');

        throw new ResponseException($response);
    }

    /**
     * Generate module
     */
    protected function compile()
    {
        $objFile = new File($this->dbChess_downloadfile);

        if ($this->linkTitle == '') {
            $this->linkTitle = $objFile->basename;
        }

        $strHref = Environment::get('request');

        // Remove an existing file parameter (see #5683)
        if (preg_match('/(&(amp;)?|\?)file=/', $strHref)) {
            $strHref = preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $strHref);
        }

        $strHref .= (str_contains($strHref, '?') ? '&amp;' : '?') . 'file=' . System::urlEncode($objFile->value);

        System::loadLanguageFile('tl_content');

        $this->Template->link = $this->linkTitle;
        $this->Template->title = StringUtil::specialchars($this->titleText ?: sprintf($GLOBALS['TL_LANG']['MSC']['download'], $objFile->basename));
        $this->Template->href = $strHref;
        $this->Template->filesize = $this->getReadableSize($this->dbChess_downloadfilesize, 1);
        $this->Template->icon = 'bundles/wiksoftdbchess/images/iconPGN.gif';
        $this->Template->mime = $objFile->mime;
        $this->Template->extension = $objFile->extension;
        $this->Template->path = $objFile->dirname;
        $this->Template->anzahl = $this->partien_gesamt;
        $this->Template->lblGames = $GLOBALS['TL_LANG']['tl_content']['dbChess_games_count'] ?? 'Partien';
    }
}
