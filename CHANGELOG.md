# Changelog

Alle nennenswerten Änderungen an `wiksoft/contao-dbchess-bundle`.
Die Versionsnummern folgen [Semantic Versioning](https://semver.org/lang/de/).

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

[1.1.1]: https://github.com/WiKSoft/contao-dbchess-bundle/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/WiKSoft/contao-dbchess-bundle/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/WiKSoft/contao-dbchess-bundle/releases/tag/v1.0.0
