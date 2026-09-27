<?php

namespace Wiksoft\DbChessBundle\Controller\BackendModule;

use Contao\Backend;
use Contao\BackendUser;
use Contao\DataContainer;
use Contao\Environment;
use Contao\File;
use Contao\Files;
use Contao\FileUpload;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;

class DbChessImportController extends Backend
{
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

            $this->import('Database');
            $this->import(Files::class, 'Files');

            foreach ($arrUploaded as $strCsvFile) {
                $objFile = new File($strCsvFile, true);

                if ($objFile->extension != 'pgn') {
                    Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $objFile->extension));
                    continue;
                }

                /* Partiedaten auslesen */
                $inhalt = $objFile->getContent();

                $tags = array();
                $partien_anz = 0;
                $leerzeile = $this->leerzeileSuchen($inhalt, 0);
                /* PGN-Datei nach Tags durchsuchen */
                for ($i = 0; $i < strlen($inhalt); $i++) {
                    if ($inhalt[$i] == '[') {
                        $tag_start = $i;
                        $tag_ende = strpos($inhalt, ']', $i + 1);
                        if ($tag_ende !== false) {
                            $tag_wert_start = strpos($inhalt, "\"", $tag_start);
                            $tag_wert_ende = strpos($inhalt, "\"", $tag_wert_start + 1);
                            $tag_wert = substr($inhalt, $tag_wert_start + 1, $tag_wert_ende - ($tag_wert_start + 1));
                            $tag = trim(substr($inhalt, $tag_start + 1, $tag_wert_start - ($tag_start + 1)));
                            if (strpos($tag_wert, '?') === 0) {
                                $tag_wert = '';
                            }
                            $onegametags[strtolower($tag)] = $tag_wert;
                            $i = $tag_ende;
                        }
                    } elseif ($i >= $leerzeile) {
                        $n = $this->leerzeileSuchen($inhalt, $i + 1);
                        $onegametags['pgn'] = trim(substr($inhalt, $i, $n - $i));
                        $tags[] = $onegametags;
                        unset($onegametags);
                        $partien_anz++;
                        $i = $n;
                        $pos_a = strpos($inhalt, '[', $i);
                        if ($pos_a !== false) {
                            $i = $pos_a - 1;
                            $leerzeile = $this->leerzeileSuchen($inhalt, $i + 1);
                        } else {
                            break;
                        }
                    }
                }
                if (!empty($tags)) {
                    // Alle Partien einer PGN-Datei in einer Transaktion importieren:
                    // schneller (kein Autocommit pro Query) und atomar (ein Fehler
                    // mitten in einer großen Datei lässt keine Partien-Teilmenge
                    // zurück, bereits erfolgreich importierte Dateien bleiben
                    // trotzdem erhalten, da jede Datei ihre eigene Transaktion hat).
                    $this->Database->beginTransaction();

                    try {
                        foreach ($tags as $nr => $partie) {
                            $attr = array();
                            $value = array();
                            foreach ($partie as $key => $tag) {
                                if ($this->Database->fieldExists($key, $dc->table)) {
                                    if (!$tag) {
                                        $tag = '?';
                                    }
                                    if ($key == 'pgn') {
                                        $tag = str_replace("\r", " ", $tag);
                                        $tag = str_replace("\n", " ", $tag);
                                        $tag = preg_replace('/ {2,}/', ' ', $tag);
                                    }
                                    $value[] = $tag;
                                    $attr[] = $key;
                                }
                            }
                            $attr[] = 'tstamp';
                            $value[] = time();
                            $attr[] = 'pid';
                            $value[] = $dc->id;
                            $arrSet = array_combine($attr, $value);
                            $id = $this->Database->prepare("INSERT INTO " . $dc->table . " %s")
                                ->set($arrSet)
                                ->execute()
                                ->insertId;
                            // Alias generieren und speichern
                            $alias = $this->generateAlias($arrSet, $id);
                            $this->Database->prepare("UPDATE " . $dc->table . " SET alias=? WHERE id=?")
                                ->execute($alias, $id);
                        }

                        $this->Database->commitTransaction();
                    } catch (\Exception $e) {
                        $this->Database->rollbackTransaction();

                        throw $e;
                    }
                }
            }
        }

        // Return form
        $strCsrfToken = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();

        return '
<div id="tl_buttons">
<a href="' . StringUtil::ampersand(str_replace('&key=import', '', Environment::get('request'))) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
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

    protected function leerzeileSuchen($zeichenkette, $pos)
    {
        $lz_1 = strpos($zeichenkette, "\n\r\n", $pos);
        $lz_2 = strpos($zeichenkette, "\n\n", $pos);
        if ($lz_1 === false) {
            if ($lz_2 === false) {
                return strlen($zeichenkette) - 1;
            }
            return $lz_2;
        } else {
            if ($lz_2 === false) {
                return $lz_1;
            }
        }
        return min($lz_1, $lz_2);
    }

    public function generateAlias($arrValue, $id)
    {
        $white = $black = '_';
        $date = $site = $event = $round = '';
        if ($arrValue['white'] != '?') {
            $arrWhite = explode(',', $arrValue['white']);
            $white = $arrWhite[0];
        }
        if ($arrValue['black'] != '?') {
            $arrBlack = explode(',', $arrValue['black']);
            $black = $arrBlack[0];
        }
        if (substr($arrValue['date'], 0, 4) != '????') {
            $arrDate = explode('.', $arrValue['date']);
            $date = $arrDate[0];
        }
        if ($arrValue['site'] != '?') {
            $site = '_' . $arrValue['site'];
        }
        if ($arrValue['event'] != '?') {
            $event = '_' . $arrValue['event'];
        }
        if ($arrValue['round'] != '?') {
            $round = '_' . $arrValue['round'];
        }
        $varValue = $white . '-' . $black . '_' . $date . $site . $event . $round;
        $varValue = StringUtil::generateAlias($varValue);

        $objAlias = $this->Database->prepare("SELECT id FROM tl_dbChess_games WHERE id=? OR alias=?")
            ->execute($id, $varValue);

        if ($objAlias->numRows > 1) {
            $varValue .= '-id-' . $id;
        }

        return $varValue;
    }
}
