<?php

namespace Wiksoft\DbChessBundle\Controller\BackendModule;

use Contao\Backend;
use Contao\BackendUser;
use Contao\DataContainer;
use Contao\Environment;
use Contao\File;
use Contao\FileUpload;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Wiksoft\DbChessBundle\Helper\GameAlias;
use Wiksoft\DbChessBundle\Pgn\PgnReader;

class DbChessImportController extends Backend
{
    /**
     * PGN-Tags, die importiert werden dürfen (Tag => maximale Länge laut
     * Datenbankspalte, null = unbegrenzt). Andere Tags werden ignoriert,
     * damit eine PGN-Datei keine internen Felder (id, pid, sid, alias,
     * gameFeatured, …) überschreiben kann.
     */
    private const IMPORT_FIELDS = [
        'event' => 255,
        'site' => 255,
        'date' => 10,
        'round' => 255,
        'white' => 255,
        'black' => 255,
        'result' => 7,
        'eco' => 3,
        'whiteelo' => 4,
        'blackelo' => 4,
        'source' => 255,
        'annotator' => 255,
        'fen' => 255,
        'remark' => null,
    ];

    /** Felder des "Seven Tag Roster", die wie im Backend-Formular bei fehlendem Wert "?" erhalten */
    private const ROSTER_FIELDS = ['event', 'site', 'round', 'white', 'black'];

    private const RESULTS = ['1-0', '0-1', '1/2-1/2', '*'];

    public function importPgn(DataContainer $dc)
    {
        if (Input::get('key') != 'importPgn') {
            return '';
        }

        $this->import(BackendUser::class, 'User');
        $class = $this->User->uploader;

        if (!class_exists($class)) {
            $class = FileUpload::class;
        }

        $objUploader = new $class();

        // Import pgn
        if (Input::post('FORM_SUBMIT') == 'tl_table_import') {
            $arrUploaded = $objUploader->uploadTo('system/tmp');

            if (empty($arrUploaded)) {
                Message::addError($GLOBALS['TL_LANG']['ERR']['all_fields']);
                $this->reload();
            }

            foreach ($arrUploaded as $strUploadedFile) {
                $objFile = new File($strUploadedFile);

                try {
                    if ($objFile->extension != 'pgn') {
                        Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $objFile->extension));
                        continue;
                    }

                    $count = $this->importGames(PgnReader::parse($objFile->getContent()), (int) $dc->id);
                    Message::addConfirmation(sprintf($GLOBALS['TL_LANG']['tl_dbChess_games']['importConfirm'], $count, $objFile->name));
                } finally {
                    // Hochgeladene Datei aus system/tmp entfernen
                    $objFile->delete();
                }
            }

            $this->reload();
        }

        // Return form
        $strCsrfToken = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();

        return '
<div id="tl_buttons">
<a href="' . StringUtil::ampersand(str_replace('&key=importPgn', '', Environment::get('request'))) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>

<h2 class="sub_headline">' . $GLOBALS['TL_LANG']['tl_dbChess_games']['importPgn'][1] . '</h2>
' . Message::generate() . '
<form action="' . StringUtil::ampersand(Environment::get('request'), true) . '" id="tl_table_import" class="tl_form" method="post" enctype="multipart/form-data">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_table_import">
<input type="hidden" name="REQUEST_TOKEN" value="' . StringUtil::specialchars($strCsrfToken) . '">

<div class="tl_tbox">

  <h3>' . $GLOBALS['TL_LANG']['tl_dbChess_games']['importPgn'][0] . '</h3>' . $objUploader->generateMarkup() . (isset($GLOBALS['TL_LANG']['tl_dbChess_games']['importPgn'][1]) ? '
  <p class="tl_help tl_tip">' . $GLOBALS['TL_LANG']['tl_dbChess_games']['importPgn'][1] . '</p>' : '') . '
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="' . StringUtil::specialchars($GLOBALS['TL_LANG']['tl_dbChess_games']['importPgn'][0]) . '">
</div>

</div>
</form>';
    }

    /**
     * Importiert alle Partien einer PGN-Datei in einer Transaktion:
     * schneller (kein Autocommit pro Query) und atomar (ein Fehler mitten in
     * einer großen Datei lässt keine Partien-Teilmenge zurück; bereits
     * erfolgreich importierte Dateien bleiben trotzdem erhalten, da jede
     * Datei ihre eigene Transaktion hat).
     *
     * @param list<array<string, string>> $games
     */
    private function importGames(array $games, int $pid): int
    {
        if (!$games) {
            return 0;
        }

        $this->Database->beginTransaction();

        try {
            foreach ($games as $tags) {
                $arrSet = $this->normalizeGame($tags);
                $arrSet['tstamp'] = time();
                $arrSet['pid'] = $pid;

                $id = (int) $this->Database->prepare("INSERT INTO tl_dbChess_games %s")
                    ->set($arrSet)
                    ->execute()
                    ->insertId;

                // Alias generieren und speichern
                $alias = GameAlias::unique(GameAlias::build($arrSet), $id);
                $this->Database->prepare("UPDATE tl_dbChess_games SET alias=? WHERE id=?")
                    ->execute($alias, $id);
            }

            $this->Database->commitTransaction();
        } catch (\Throwable $e) {
            $this->Database->rollbackTransaction();

            throw $e;
        }

        return \count($games);
    }

    /**
     * Übernimmt nur die erlaubten Tags und bringt die Werte in dasselbe
     * Format, das auch das Backend-Formular erzeugt.
     *
     * @param array<string, string> $tags
     * @return array<string, string>
     */
    private function normalizeGame(array $tags): array
    {
        $arrSet = [];

        foreach (self::IMPORT_FIELDS as $field => $maxLength) {
            $value = trim($tags[$field] ?? '');
            $unknown = $value === '' || preg_match('/^[?.]+$/', $value);

            if (\in_array($field, self::ROSTER_FIELDS, true)) {
                $value = $unknown ? '?' : $value;
            } elseif ($field === 'date') {
                $value = $unknown ? '????.??.??' : $value;
            } elseif ($field === 'result') {
                $value = \in_array($value, self::RESULTS, true) ? $value : '*';
            } elseif ($field === 'whiteelo' || $field === 'blackelo') {
                $value = ctype_digit($value) ? $value : '';
            } elseif ($unknown) {
                $value = '';
            }

            $arrSet[$field] = $maxLength !== null ? mb_substr($value, 0, $maxLength) : $value;
        }

        // Zeilenumbrüche und überflüssige Leerzeichen entfernt bereits der PgnReader
        $arrSet['pgn'] = $tags['pgn'] ?? '';

        return $arrSet;
    }
}
