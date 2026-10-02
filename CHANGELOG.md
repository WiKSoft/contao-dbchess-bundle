# Changelog

Alle nennenswerten Änderungen an `wiksoft/contao-dbchess-bundle`.
Die Versionsnummern folgen [Semantic Versioning](https://semver.org/lang/de/).

## [Unreleased]

### Geändert

- Partien-Index (Modul): Die Tag-Links sind jetzt einfache Query-String-URLs
  auf die aktuelle Seite (`/quellen.html?index=Aftenposten`). Bisher
  entstanden über `addToUrl()` Folder-URLs, die Sonderzeichen dreifach
  kodierten (`%252520`) und alle aktuellen Parameter übernahmen, wodurch
  verschachtelte URLs entstanden. Der Parameter `ce_id` entfällt; alte
  Links mit `ce_id` liefern 404.

## [1.2.0] – 2026-10-01

### Neu

- Die Felder „PGN" und „Bemerkung" einer Partie prüfen beim Speichern die
  Länge (höchstens 65.535 Byte, Grenze der Datenbankspalte). Statt eines
  Datenbankfehlers, bei dem alle Eingaben verloren gingen, erscheint eine
  Meldung am Feld.
- `composer.json`: `wiksoft/contao-lichess-pgnviewer-bundle` als Empfehlung
  (`suggest`) zum Nachspielen der Partien.

### Behoben

- Partienliste (Inhaltselement) und Partien-Index (Modul) ohne
  Weiterleitungsseite: Im Debug-Modus brach die Seite mit „Key "href" …
  does not exist" ab, weil die Templates `game.href` abfragen, der Schlüssel
  aber nur mit Weiterleitungsseite gesetzt war. `href` ist jetzt immer
  vorhanden (ohne Weiterleitungsseite `null`).

### Dokumentation

- README: neuer Abschnitt „Partien nachspielen" mit Empfehlung des
  lichess PGN-Viewers, Hinweis auf den Dateityp `pgn` in den erlaubten
  Upload- und Download-Dateitypen.

## [1.1.1] – 2026-09-30

### Geändert

- Die Icons der Schaltflächen „Partien verknüpfen" und „Verknüpfung lösen"
  sind jetzt eigene SVG-Grafiken (`link.svg`, `link_break.svg`) im Stil der
  Contao-Backend-Icons. Sie ersetzen die bisherigen PNG-Dateien.

## [1.1.0] – 2026-09-29

### Neu

- Die ECO-Codes (A00–E99) mit Eröffnungsname und Zugfolge stehen jetzt in
  Sprachdateien statt in der Tabelle `tl_dbChess_eco`: Englisch
  (`contao/languages/en/dbChess_eco.php`) und Deutsch
  (`contao/languages/de/dbChess_eco.php`). Einzelne Namen lassen sich im
  Projekt überschreiben.
- Die Zugfolgen verwenden Figurensymbole (♚ ♛ ♜ ♝ ♞); alle wurden auf gültige
  Züge geprüft.
- Das Auswahlfeld „ECO" zeigt jeden Code einmal mit Eröffnungsnamen und hat
  ein Suchfeld.

### Behoben

- Ein ECO-Code, der nicht in der Liste steht, ging beim Speichern einer
  Partie im Backend verloren.

### Entfernt

- Backend-Modul „Schacheröffnungen" und Tabelle `tl_dbChess_eco`.

### Hinweis zum Update

Beim Aktualisieren der Datenbank wird das Löschen der Tabelle
`tl_dbChess_eco` vorgeschlagen. Wer dort eigene Namen gepflegt hat, überträgt
sie vorher in eine Sprachdatei.

## [1.0.0] – 2026-09-28

Erste veröffentlichte Version: Partiesammlungen mit PGN-Import und -Export,
Verknüpfen von Dubletten, ECO-Codes, Inhaltselemente „dbChess_list"
(Partieliste) und „dbChess_download" (PGN-Download), Frontend-Modul
„dbChess_index" (Index/Tag-Cloud), Rechteprüfung und CSRF-Schutz für die
Sammlungs-Aktionen, Sprachdateien Deutsch und Englisch.

[1.2.0]: https://github.com/WiKSoft/contao-dbchess-bundle/compare/v1.1.1...v1.2.0
[1.1.1]: https://github.com/WiKSoft/contao-dbchess-bundle/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/WiKSoft/contao-dbchess-bundle/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/WiKSoft/contao-dbchess-bundle/releases/tag/v1.0.0
