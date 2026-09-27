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
  - **PGN-Import** – `.pgn`-Datei hochladen, Partien werden zerlegt und die Tags
    in die Felder übernommen.
  - **PGN-Export** – Sammlung als PGN-Datei ausgeben.
  - **Partien verknüpfen** – findet identische Partien (gleiches Datum, Event,
    Ort, Runde, Spieler, Ergebnis), z. B. dieselbe Partie mit Kommentaren
    verschiedener Autoren, und verknüpft sie über das Feld `sid`.
  - **Verknüpfung aufheben**.

### ECO-Codes (`tl_dbChess_eco`)

Nachschlagetabelle ECO-Code → Eröffnungsname (z. B. `B90`).

## Frontend

### Inhaltselement „dbChess_list" – Partieliste

- Zeigt Partien aus ausgewählten Sammlungen als Liste oder Tabelle
  (Templates `ce_dbChess_list_default` / `ce_dbChess_list_table`).
- Konfigurierbar: angezeigte Felder, Sortierfelder und -richtung, zusätzlicher
  SQL-Filter, Weiterleitungsseite (Link auf die Einzelpartie per Alias, z. B. zu
  einem Nachspiel-Viewer).
- Verknüpfte Partien erscheinen nur einmal; Kommentatoren und Quellen werden
  dabei zusammengeführt.

### Inhaltselement „dbChess_download" – PGN-Download

- Erzeugt aus den gewählten Sammlungen (mit Filter, Sortierung, optional nur
  „featured"-Partien) eine PGN-Datei zum Herunterladen.
- Die Datei wird unter `files/dbChess/` abgelegt und einen Tag lang gecacht.

### Frontend-Modul „dbChess_index" – Index / Tag-Cloud

- Baut einen Index über ein wählbares Feld auf (z. B. alle Spieler Weiß+Schwarz,
  Turniere, Orte, ECO-Codes, Kommentatoren) mit Anzahl der Partien, als Liste
  oder Cloud.
- Klick auf einen Eintrag zeigt die zugehörige Partieliste.
- Ausnahmewerte ausblendbar, Sortierung nach Häufigkeit oder alphabetisch.

Ein Brett zum Nachspielen der Partien ist nicht enthalten – dafür wird auf der
Zielseite eine separate Lösung benötigt.

## Installation (FTP + Contao Manager, ohne Git/Konsole)

1. **Ordner hochladen** nach `packages/wiksoft/contao-dbchess-bundle/` im
   Projektverzeichnis (auf gleicher Ebene wie `vendor/` und `composer.json`,
   **nicht** in `vendor/`).

2. **Path-Repository** in der Root-`composer.json` des Projekts eintragen:
   ```json
   "repositories": [
       {
           "type": "path",
           "url": "packages/wiksoft/contao-dbchess-bundle",
           "options": {
               "symlink": false
           }
       }
   ]
   ```
   Mit `"symlink": false` wird das Paket nach `vendor/` kopiert. Mit
   `"symlink": true` wirken Änderungen in `packages/` sofort (der Ordner darf
   dann aber nie gelöscht werden).

3. **Paket im Contao Manager hinzufügen**: nach `wiksoft/contao-dbchess-bundle`
   suchen und installieren. Der Manager führt Composer, `assets:install`,
   Cache-Leeren und die Datenbank-Migration automatisch aus.

4. **Updates**: geänderte Dateien nach `packages/…` hochladen, `version` in der
   `composer.json` des Bundles erhöhen und im Contao Manager ein Update anstoßen.

## Verzeichnisstruktur

```
contao-dbchess-bundle/
├── composer.json
├── contao/
│   ├── config/config.php
│   ├── dca/
│   │   ├── tl_content.php
│   │   ├── tl_dbChess_collection.php
│   │   ├── tl_dbChess_eco.php
│   │   ├── tl_dbChess_games.php
│   │   └── tl_module.php
│   ├── languages/de/*.php
│   └── templates/*.html.twig
├── public/
│   ├── dbChess.css
│   └── images/*.gif, *.png
└── src/
    ├── WiksoftDbChessBundle.php
    ├── ContaoManager/Plugin.php
    ├── Module/ModuleDbChessIndex.php
    ├── ContentElement/
    │   ├── ContentDbChessList.php
    │   └── ContentDbChessDownload.php
    └── Controller/BackendModule/
        ├── DbChessExportController.php
        ├── DbChessImportController.php
        ├── DbChessLinkGameController.php
        └── DbChessUnlinkGameController.php
```
