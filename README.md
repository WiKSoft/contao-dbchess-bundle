# wiksoft/contao-dbchess-bundle

Schachpartien-Datenbank für Contao 5: Partien per PGN importieren und pflegen,
Dubletten verknüpfen, im Frontend als Liste oder Index/Tag-Cloud anzeigen und
Sammlungen als PGN zum Download anbieten.

## Backend (unter „Inhalte")

### Partiesammlungen (`tl_dbChess_collection` → `tl_dbChess_games`)

- Partien werden in benannten Sammlungen gruppiert (z. B. pro Turnier/Saison).
- Pro Partie werden die PGN-Kopfdaten gespeichert: Event, Ort, Datum, Runde,
  Weiß, Schwarz, Ergebnis, ECO, Elo Weiß/Schwarz, Kommentator, Quelle, FEN –
  dazu die **PGN-Notation**, eine Bemerkung (Rich-Text), ein Alias sowie ein
  „featured"-Kennzeichen.
- Aktionen für eine ganze Sammlung:
  - **PGN-Import** – `.pgn`-Datei hochladen, Partien werden zerlegt und die
    Standard-Tags (Event, Site, Date, Round, White, Black, Result, ECO,
    WhiteElo, BlackElo, Source, Annotator, FEN) sowie `Remark` in die Felder
    übernommen; andere Tags werden ignoriert.
  - **PGN-Export** – Sammlung als PGN-Datei ausgeben.
  - **Partien verknüpfen** – findet identische Partien (gleiches Datum, Event,
    Ort, Runde, Spieler, Ergebnis), z. B. dieselbe Partie mit Kommentaren
    verschiedener Autoren, und verknüpft sie über das Feld `sid`.
  - **Verknüpfung aufheben**.

### ECO-Codes

Die 500 ECO-Codes (A00–E99) mit Eröffnungsnamen und Zugfolge stehen in den
Sprachdateien `contao/languages/en/dbChess_eco.php` (Englisch) und
`contao/languages/de/dbChess_eco.php` (Deutsch). Die Figuren in den
Zugfolgen sind als Symbole geschrieben (♚ ♛ ♜ ♝ ♞). Die Einträge liefern die
Auswahl im Feld „ECO“ der Partie und die Namen im Index-Modul (`tag.ecoName`).
Für andere Sprachen nutzt Contao Englisch als Rückfallsprache. Einzelne Namen
lassen sich im Projekt überschreiben, z. B. in
`contao/languages/de/dbChess_eco.php`:

```php
$GLOBALS['TL_LANG']['dbChess_eco']['B90'] = 'Sizilianisch, Najdorf-Variante: 1.e4 c5 …';
```

## Frontend

### Inhaltselement „dbChess_list" – Partieliste

- Zeigt Partien aus ausgewählten Sammlungen als Liste oder Tabelle
  (Templates `ce_dbChess_list_default` / `ce_dbChess_list_table`).
- Konfigurierbar: angezeigte Felder, Sortierfelder und -richtung, zusätzlicher
  SQL-Filter (nur von Administratoren bearbeitbar, Insert-Tags wie
  `{{date::Y}}` werden ersetzt), Weiterleitungsseite (Link auf die Einzelpartie per Alias, z. B. zu
  einem Nachspiel-Viewer).
- Verknüpfte Partien erscheinen nur einmal; Kommentatoren und Quellen werden
  dabei zusammengeführt.

### Inhaltselement „dbChess_download" – PGN-Download

- Erzeugt aus den gewählten Sammlungen (mit Filter, Sortierung, optional nur
  „featured"-Partien) eine PGN-Datei zum Herunterladen.
- Die Datei wird beim Download unter `files/dbChess/` abgelegt und nur neu
  geschrieben, wenn sich der Inhalt geändert hat.

### Frontend-Modul „dbChess_index" – Index / Tag-Cloud

- Baut einen Index über ein wählbares Feld auf (z. B. alle Spieler Weiß+Schwarz,
  Turniere, Orte, ECO-Codes, Kommentatoren) mit Anzahl der Partien, als Liste
  oder Cloud.
- Klick auf einen Eintrag zeigt die zugehörige Partieliste.
- Ausnahmewerte ausblendbar, Sortierung nach Häufigkeit oder alphabetisch.

## Partien nachspielen

Ein Brett zum Nachspielen der Partien ist in dieser Erweiterung nicht
enthalten. Empfohlen wird dafür
[`wiksoft/contao-lichess-pgnviewer-bundle`](https://github.com/WiKSoft/contao-lichess-pgnviewer-bundle),
das den [PGN-Viewer von lichess.org](https://github.com/lichess-org/pgn-viewer)
in Contao einbindet und direkt auf die Schachdatenbank zugreift:

- Das Inhaltselement „Schachpartie (lichess PGN-Viewer)" zeigt Partien aus der
  Datenbank an, entweder eine Liste aus Sammlungen (mit Filter und Sortierung)
  oder einzeln ausgewählte Partien.
- Das Frontend-Modul „lichessPgnviewer Reader" zeigt die Partie an, deren
  Alias in der URL steht. Es ist damit das passende Ziel für die
  Weiterleitungsseite von „dbChess_list" und „dbChess_index".
- Verknüpfte Partien (`sid`) werden als umschaltbare Fassungen angezeigt, die
  Bemerkung (`remark`) erscheint unter dem Brett.

```bash
composer require wiksoft/contao-lichess-pgnviewer-bundle
```

## Anforderungen

- PHP ^8.1
- Contao ^5.3
- empfohlen: `wiksoft/contao-lichess-pgnviewer-bundle` zum Nachspielen der
  Partien (siehe [Partien nachspielen](#partien-nachspielen))

## Installation

1. Das Paket installieren, entweder im **Contao Manager** (nach
   `wiksoft/contao-dbchess-bundle` suchen und installieren) oder auf der
   **Kommandozeile**:
   ```bash
   composer require wiksoft/contao-dbchess-bundle
   ```

2. Anschließend die Datenbank aktualisieren, entweder im **Contao Manager**
   unter *Systemwartung → Datenbank aktualisieren* oder auf der
   **Kommandozeile**:
   ```bash
   vendor/bin/contao-console contao:migrate
   ```

3. Damit PGN-Dateien importiert und exportiert (Sammlungen) bzw. im Frontend
   zum Download angeboten werden können, muss
   der Dateityp `pgn` in den Backend-Einstellungen erlaubt sein. Unter
   *System → Einstellungen* `pgn` in beide Felder eintragen:
   - **Erlaubte Upload-Dateitypen** (Abschnitt *Datei-Uploads*) – für das
     Hochladen bzw. Importieren von `.pgn`-Dateien
   - **Erlaubte Download-Dateitypen** (Abschnitt *Dateien und Bilder*) – für
     das Herunterladen bzw. Exportieren von `.pgn`-Dateien

   Der Eintrag wird kommagetrennt an die vorhandene Liste angehängt,
   z. B. `…,zip,pgn`.

## Lizenz

LGPL-3.0-or-later, siehe [LICENSE](LICENSE).
