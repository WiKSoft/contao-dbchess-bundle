<?php

namespace Wiksoft\DbChessBundle\Controller\BackendModule;

use Contao\Backend;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;

class DbChessExportController extends Backend
{
    public function exportPgn(DataContainer $dc)
    {
        if (Input::get('key') != 'exportPgn') {
            return '';
        }

        $this->import('Database');
        $output = '';
        $result = $this->Database->prepare("SELECT * FROM tl_dbChess_games WHERE pid=? ORDER BY date")->execute($dc->id);
        while ($result->next()) {
            $row = $result->row();
            $pgnText = '';
            if ($row['event']) $pgnText .= '[Event "' . $this->escapePgnTagValue($row['event']) . '"]' . "\n";
            if ($row['site']) $pgnText .= '[Site "' . $this->escapePgnTagValue($row['site']) . '"]' . "\n";
            if ($row['date']) $pgnText .= '[Date "' . $this->escapePgnTagValue($row['date']) . '"]' . "\n";
            if ($row['round']) $pgnText .= '[Round "' . $this->escapePgnTagValue($row['round']) . '"]' . "\n";
            if ($row['white']) $pgnText .= '[White "' . $this->escapePgnTagValue($row['white']) . '"]' . "\n";
            if ($row['black']) $pgnText .= '[Black "' . $this->escapePgnTagValue($row['black']) . '"]' . "\n";
            if ($row['result']) $pgnText .= '[Result "' . $this->escapePgnTagValue($row['result']) . '"] ' . "\n";
            if ($row['eco']) $pgnText .= '[ECO "' . $this->escapePgnTagValue($row['eco']) . '"] ' . "\n";
            if ($row['whiteelo']) $pgnText .= '[WhiteElo "' . $this->escapePgnTagValue($row['whiteelo']) . '"]' . "\n";
            if ($row['blackelo']) $pgnText .= '[BlackElo "' . $this->escapePgnTagValue($row['blackelo']) . '"]' . "\n";
            if ($row['source']) $pgnText .= '[Source "' . $this->escapePgnTagValue($row['source']) . '"] ' . "\n";
            if ($row['annotator']) $pgnText .= '[Annotator "' . $this->escapePgnTagValue($row['annotator']) . '"] ' . "\n";
            if ($row['fen']) {
                $pgnText .= '[FEN "' . $this->escapePgnTagValue($row['fen']) . '"] ' . "\n";
                $pgnText .= '[SetUp "1"] ' . "\n";
            }
            $pgnText .= "\n" . wordwrap(html_entity_decode($row['pgn'], ENT_QUOTES), 80, "\n", false) . "\n";
            $output .= $pgnText . "\n";
        }

        // Dateiname generieren
        $result = $this->Database->prepare("SELECT * FROM tl_dbChess_collection WHERE id=?")
            ->limit(1)
            ->execute($dc->id);
        $exportFile = StringUtil::generateAlias(StringUtil::decodeEntities($result->name)) . date("_Ymd-Hi");

        header('Content-Type: text/plain');
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="' . $exportFile . '.pgn"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Expires: 0');

        echo $output;
        exit;
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
