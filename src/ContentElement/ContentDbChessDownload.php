<?php

declare(strict_types=1);

namespace Wiksoft\DbChessBundle\ContentElement;

use Contao\Config;
use Contao\ContentElement;
use Contao\Controller;
use Contao\Database;
use Contao\Dbafs;
use Contao\Environment;
use Contao\File;
use Contao\Files;
use Contao\FilesModel;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;

class ContentDbChessDownload extends ContentElement
{
    /** Cachezeit der PGN-Datei in Sekunden (1 Tag) */
    private const CACHE_TTL = 86400;

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_dbChess_download';

    private string $gameAlias = '';

    private int $partien_gesamt = 0;

    private string $dbChess_downloadfile = '';

    private int $dbChess_downloadfilesize = 0;

    public function generate()
    {
        $this->import(Database::class, 'Database');
        $this->import(Files::class, 'Files');

        // Dateiname generieren: Basiert auf dem Link-Titel, sofern gesetzt.
        // Ist kein Link-Titel konfiguriert, bleibt gameAlias hier bewusst leer,
        // damit unten der Alias der ersten passenden Partie als Fallback greift
        // (StringUtil::generateAlias('') liefert '', der '-id'-Suffix machte den
        // Wert vorher aber immer truthy, wodurch der Fallback nie ausgeführt wurde).
        $this->gameAlias = $this->linkTitle ? StringUtil::generateAlias($this->linkTitle) . '-id' . $this->id : '';

        [$where, $params] = $this->buildCollectionWhere();
        $filter = $this->buildFilterClause();
        $sorting = $this->buildSortingClause();

        $result = $this->Database
            ->prepare("SELECT * FROM tl_dbChess_games WHERE ($where) $filter $sorting")
            ->execute(...$params);

        if (!$this->gameAlias) {
            $row = $result->first();
            $this->gameAlias = $row->alias ?? ('dbChess-download-id' . $this->id);
            $result->reset();
        }

        $gameslist = $result->fetchAllAssoc();

        if ($this->dbChess_dl_featured) {
            $gameslist = $this->filterFeaturedGames($gameslist);
        }

        $this->partien_gesamt = count($gameslist);

        // Pfad zum temp-Verzeichnis
        $pgnPath = System::getContainer()->getParameter('contao.upload_path') . '/dbChess';
        $pgnFile = $pgnPath . '/' . $this->gameAlias . '.pgn';

        $requestedFile = Input::get('file', true);

        // Die Datei wird jetzt NUR erzeugt/geschrieben, wenn tatsächlich ein
        // Download angefordert wurde (Query-Parameter "file" passt exakt
        // zum erwarteten Pfad). Ein reiner Seitenaufruf schreibt nichts
        // mehr auf die Platte und stößt keinen Dbafs::syncFiles() an.
        if ($requestedFile !== '' && $requestedFile === $pgnFile) {
            return $this->handleDownload($pgnPath, $pgnFile, $gameslist, $requestedFile);
        }

        // Normale Seitenansicht: nur Metadaten für den Link ermitteln.
        $this->dbChess_downloadfile = $pgnFile;
        $this->dbChess_downloadfilesize = $this->resolveDisplaySize($pgnFile, $gameslist);

        return parent::generate();
    }

    /**
     * Erzeugt (falls nötig) die PGN-Datei und liefert sie an den Browser aus.
     * Wird ausschließlich bei einem tatsächlichen Download-Request aufgerufen.
     *
     * @param array<int, array<string, mixed>> $gameslist
     */
    private function handleDownload(string $pgnPath, string $pgnFile, array $gameslist, string $requestedFile)
    {
        $this->Files->mkdir($pgnPath);
        $objFile = new File($pgnFile, true);
        $tstamp = time() - self::CACHE_TTL;

        if (!is_file($pgnFile) || $objFile->mtime < $tstamp) {
            $pgnText = $this->buildPgnText($gameslist);

            $fh = $this->Files->fopen($pgnFile, 'wb');
            $this->Files->fputs($fh, $pgnText);
            $this->Files->fclose($fh);
            // Dateien synchronisieren
            Dbafs::syncFiles();
        }

        // ID der Datei ermitteln
        $objFile = FilesModel::findByPath($pgnFile);
        if ($objFile === null) {
            return '';
        }
        if (!Validator::isUuid($objFile->uuid)) {
            return '<p class="error">' . $GLOBALS['TL_LANG']['ERR']['version2format'] . '</p>';
        }

        $allowedDownload = StringUtil::trimsplit(',', strtolower(Config::get('allowedDownload')));

        if (!in_array($objFile->extension, $allowedDownload, true)) {
            return '';
        }

        // Bricht die Ausführung normalerweise selbst ab (exit).
        Controller::sendFileToBrowser($requestedFile);

        $this->dbChess_downloadfile = $objFile->path;
        $this->dbChess_downloadfilesize = $objFile->filesize;

        return parent::generate();
    }

    /**
     * Ermittelt die für die Anzeige benötigte Dateigröße, ohne dafür die
     * PGN-Datei neu zu schreiben. Existiert bereits eine aktuelle (nicht
     * abgelaufene) Datei von einem vorherigen Download, wird deren reale
     * Größe gelesen. Andernfalls wird der PGN-Text einmalig im Speicher
     * aufgebaut, um die zu erwartende Größe zu schätzen.
     *
     * @param array<int, array<string, mixed>> $gameslist
     */
    private function resolveDisplaySize(string $pgnFile, array $gameslist): int
    {
        if (is_file($pgnFile) && filemtime($pgnFile) >= (time() - self::CACHE_TTL)) {
            return (int) filesize($pgnFile);
        }

        return strlen($this->buildPgnText($gameslist));
    }

    /**
     * Generate module
     */
    protected function compile()
    {
        $objFile = new File($this->dbChess_downloadfile, true);

        if ($this->linkTitle == '') {
            $this->linkTitle = $objFile->basename;
        }

        $strHref = Environment::get('request');

        // Remove an existing file parameter (see #5683)
        if (preg_match('/(&(amp;)?|\?)file=/', $strHref)) {
            $strHref = preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $strHref);
        }

        $strHref .= ((Config::get('disableAlias') || strpos($strHref, '?') !== false) ? '&amp;' : '?') . 'file=' . System::urlEncode($objFile->value);

        // Falls die Datei physisch existiert, deren reale Größe nutzen;
        // ansonsten die zuvor in generate() ermittelte/geschätzte Größe.
        $filesize = is_file($this->dbChess_downloadfile) ? $objFile->filesize : $this->dbChess_downloadfilesize;

        $this->Template->link = $this->linkTitle;
        $this->Template->title = StringUtil::specialchars($this->titleText ?: sprintf($GLOBALS['TL_LANG']['MSC']['download'], $objFile->basename));
        $this->Template->href = $strHref;
        $this->Template->filesize = $this->getReadableSize($filesize, 1);
        $this->Template->icon = 'bundles/wiksoftdbchess/images/iconPGN.gif';
        $this->Template->mime = $objFile->mime;
        $this->Template->extension = $objFile->extension;
        $this->Template->path = $objFile->dirname;
        $this->Template->anzahl = $this->partien_gesamt;
    }

    /**
     * Baut die WHERE-Bedingung für die ausgewählten Partiesammlungen als
     * parametrisierte Query (statt String-Konkatenation der pid-Werte).
     *
     * @return array{0: string, 1: array<int, int|string>}
     */
    private function buildCollectionWhere(): array
    {
        $arrCollection = StringUtil::deserialize($this->dbChess_list_collection, true);

        if (empty($arrCollection)) {
            return ['pid IS NULL', []];
        }

        $placeholders = implode(',', array_fill(0, count($arrCollection), '?'));

        return ['pid IN (' . $placeholders . ')', $arrCollection];
    }

    /**
     * Spaltennamen, die als Sortierfeld in eine ORDER-BY-Klausel eingesetzt
     * werden dürfen (identisch mit den 'options' von dbChess_list_sortfields
     * in tl_content.php). Da Spaltennamen sich nicht per Platzhalter
     * parametrisieren lassen, wird hier stattdessen der Wert aus dem
     * Backend-Auswahlfeld gegen diese feste Liste geprüft, bevor er in die
     * SQL-Query interpoliert wird.
     */
    private const ALLOWED_SORT_FIELDS = ['event', 'site', 'date', 'round', 'result', 'white', 'black', 'eco', 'whiteelo', 'blackelo', 'annotator', 'source'];

    /**
     * Baut die ORDER-BY-Klausel anhand der im Backend gewählten Sortierfelder.
     */
    private function buildSortingClause(): string
    {
        $arrSorting = StringUtil::deserialize($this->dbChess_list_sortfields, true);
        $byOrder = $this->dbChess_list_byorder == 'a' ? 'ASC' : 'DESC';

        $fields = [];
        foreach ($arrSorting as $field) {
            if (!in_array($field, self::ALLOWED_SORT_FIELDS, true)) {
                continue;
            }
            if ($field === 'round') {
                $field = 'CAST(round AS UNSIGNED)';
            }
            $fields[] = $field . ' ' . $byOrder;
        }

        if (empty($fields)) {
            return 'ORDER BY gameFeatured DESC';
        }

        return 'ORDER BY ' . implode(', ', $fields) . ', gameFeatured DESC';
    }

    /**
     * Liefert die optionale, im Backend konfigurierte Filterbedingung.
     *
     * Hinweis: Der Wert wird unverändert in die Query übernommen, da es sich
     * um einen freien SQL-Ausdruck handelt (keine Parametrisierung möglich).
     * Das Feld sollte daher nur von vertrauenswürdigen Redakteuren im
     * Backend gepflegt werden.
     */
    private function buildFilterClause(): string
    {
        if (!$this->dbChess_list_filter) {
            return '';
        }

        return 'AND (' . StringUtil::decodeEntities($this->dbChess_list_filter) . ')';
    }

    /**
     * Entfernt aus einer "Featured"-Auswahl (sid) alle referenzierten
     * Partien, damit sie nicht zusätzlich einzeln in der Liste auftauchen.
     *
     * @param array<int, array<string, mixed>> $gameslist
     * @return array<int, array<string, mixed>>
     */
    private function filterFeaturedGames(array $gameslist): array
    {
        foreach ($gameslist as $key => $entry) {
            if (!isset($gameslist[$key]) || !$entry['sid']) {
                continue;
            }

            $sidList = StringUtil::deserialize($entry['sid']);

            foreach ($sidList as $sid) {
                if ($sid == $entry['id']) {
                    continue;
                }

                foreach ($gameslist as $sidKey => $sidValue) {
                    if ($sidValue['id'] == $sid) {
                        unset($gameslist[$sidKey]);
                    }
                }
            }
        }

        return $gameslist;
    }

    /**
     * Erstellt den vollständigen PGN-Text für eine Liste von Partien.
     *
     * @param array<int, array<string, mixed>> $gameslist
     */
    private function buildPgnText(array $gameslist): string
    {
        $pgnText = '';

        foreach ($gameslist as $row) {
            $pgnText .= $this->buildPgnHeader($row);
            $pgnText .= "\n" . wordwrap(html_entity_decode((string) $row['pgn'], ENT_QUOTES), 80, "\n", false) . "\n\n";
        }

        return $pgnText;
    }

    /**
     * Erstellt den PGN-Tag-Header (Event, Site, Date, ...) für eine einzelne Partie.
     *
     * @param array<string, mixed> $row
     */
    private function buildPgnHeader(array $row): string
    {
        static $tags = [
            'Event' => 'event',
            'Site' => 'site',
            'Date' => 'date',
            'Round' => 'round',
            'White' => 'white',
            'Black' => 'black',
            'Result' => 'result',
            'ECO' => 'eco',
            'WhiteElo' => 'whiteelo',
            'BlackElo' => 'blackelo',
            'Source' => 'source',
            'Annotator' => 'annotator',
        ];

        $header = '';
        foreach ($tags as $tag => $field) {
            if (!empty($row[$field])) {
                $header .= '[' . $tag . ' "' . $this->escapePgnTagValue((string) $row[$field]) . '"]' . "\n";
            }
        }

        if (!empty($row['fen'])) {
            $header .= '[FEN "' . $this->escapePgnTagValue((string) $row['fen']) . '"]' . "\n";
            $header .= '[SetUp "1"]' . "\n";
        }

        return $header;
    }

    /**
     * Maskiert Anführungszeichen und Backslashes gemäß PGN-Spezifikation,
     * damit Feldwerte mit '"' (z.B. Spielernamen) kein kaputtes PGN-Tag
     * erzeugen.
     */
    private function escapePgnTagValue(string $value): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
    }
}
