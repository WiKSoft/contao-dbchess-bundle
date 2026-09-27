# wiksoft/contao-dbchess-bundle – Migration von Contao 3/4.13 auf Contao 5.7

## Was schon erledigt ist

- Neue flache Ressourcenstruktur `contao/{config,dca,languages,templates}` statt
  `src/Resources/contao/...` (das ist die "neue Struktur", nach der du gefragt hattest).
- `composer.json` als eigenständiges Paket `wiksoft/contao-dbchess-bundle` (Typ `contao-bundle`),
  PSR-4-Autoloading über `src/`.
- `src/WiksoftDbChessBundle.php` + `src/ContaoManager/Plugin.php` – ersetzt
  `config/autoload.php` / `ClassLoader::addClasses()`, die es in Contao 5 nicht mehr gibt
  (der alte Contao-3-Klassenlader wurde in Contao 5.0 entfernt).
- Alle 7 Klassen aus `classes/` wurden nach `src/` verschoben, bekamen einen echten
  Namespace (`Wiksoft\DbChessBundle\...`) und referenzieren Contao-Kernklassen jetzt über
  `use Contao\Xyz;` statt über bare `\Xyz` – das war zwingend nötig, weil genau dieser
  Mechanismus (globale, klassenlader-vermittelte Namen) in Contao 5 weggefallen ist:
  - `dbChess_index.php` → `src/Module/ModuleDbChessIndex.php`
  - `dbChess_list.php` → `src/ContentElement/ContentDbChessList.php`
  - `dbChess_download.php` → `src/ContentElement/ContentDbChessDownload.php`
  - `dbChess_Import.php` → `src/Controller/BackendModule/DbChessImportController.php`
  - `dbChess_Export.php` → `src/Controller/BackendModule/DbChessExportController.php`
  - `dbChess_linkGame.php` → `src/Controller/BackendModule/DbChessLinkGameController.php`
  - `dbChess_unlinkGame.php` → `src/Controller/BackendModule/DbChessUnlinkGameController.php`
- `TL_MODE == 'BE'`-Prüfung im Frontend-Modul ersetzt durch den `ScopeMatcher`-Service
  (TL_MODE ist seit Contao 4.9 deprecated und in 5 wahrscheinlich ganz entfernt).
- `utf8_strtoupper()` durch natives `mb_strtoupper()` ersetzt (Contao-eigene
  UTF8-Polyfills gibt es in Contao 5 nicht mehr).
- `contao/config/config.php` registriert Module/Content-Elemente/Backend-Aktionen jetzt
  über `::class`-Konstanten (FQCN) statt über Klassennamen-Strings.
- DCA-Dateien bleiben strukturell unverändert (das klassische Array-Format wird in
  Contao 5 weiterhin unterstützt) – nur die inline definierten Callback-Klassen in
  `tl_module.php`, `tl_content.php` und `tl_dbChess_games.php` haben jetzt
  `use Contao\Backend;` etc. bekommen.

## Bereits ergänzt (Templates, Sprachdateien & Assets)

- Alle 5 Templates (`mod_dbChess_index`, `mod_dbChess_indexCloud`,
  `ce_dbChess_list_default`, `ce_dbChess_list_table`, `ce_dbChess_download`) liegen jetzt
  unverändert in `contao/templates/`. Es sind klassische PHP-Templates
  (`$this->xxx`, Insert-Tag-Syntax `{{label::...}}`) – diese werden in Contao 5 für
  nicht auf Twig migrierte Module/Content-Elemente weiterhin voll unterstützt, eine
  explizite Registrierung wie `TemplateLoader::addFiles()` ist nicht mehr nötig, da
  Templates unter `contao/templates/` eines Bundles automatisch gefunden werden.
- **Gefundener Bug, der unter Contao 5.7 zum Fatal Error geführt hätte**: In
  `ce_dbChess_list_table.html5` stand `if (dbChess_list_jumpTo)` – eine nackte,
  nicht existierende PHP-Konstante statt `$this->dbChess_list_jumpTo`. Unter PHP 7
  war das nur eine Warnung (die Konstante wurde stillschweigend als String
  behandelt), seit PHP 8 (Voraussetzung für Contao 5.7) ist eine undefinierte
  Konstante ein **Fatal Error**. Ich habe das auf `$this->dbChess_list_jumpTo`
  korrigiert.

## Bereits ergänzt (Sprachdateien & Assets)

- `languages/de/*.php` unverändert nach `contao/languages/de/` übernommen (Inhalt
  identisch, nur der Pfad hat sich geändert).
- `assets/dbChess.css` → `public/dbChess.css`; `assets/images/*` → `public/images/*`
  (Symfony-Bundle-Konvention: statische Dateien liegen im `public/`-Ordner des Bundles).
  Da die CSS-Datei ihre Icons relativ referenziert (`url("images/iconPGN.gif")` usw.),
  funktioniert das unverändert, weil `images/` weiterhin relativ neben `dbChess.css`
  liegt.
- Die beiden `.htaccess`-Dateien aus `assets/` und `assets/images/` habe ich **bewusst
  nicht** übernommen – das waren Zugriffsschutz-Relikte für den alten
  `system/modules/`-Pfad und sind im neuen `public/`-Ordner-Konzept (der ohnehin über
  Symfonys öffentliches Web-Verzeichnis ausgeliefert wird) nicht mehr relevant.
- Ein Punkt zur Prüfung: Das Icon in `dbChess_download` (`iconPGN.gif`) sowie die
  Header-Icons für PGN-Import/-Export in der CSS (`images/iconPGN.gif`) verweisen
  denkbar auf dasselbe Icon, das im Original aus einer *anderen* Erweiterung
  (`pgn4web`) kam. Da du jetzt aber eine eigene `iconPGN.gif` im Bundle hast, ist das
  vermutlich ohnehin gelöst – nur zur Info, falls dir das Icon fehlt oder anders
  aussieht als erwartet.
- **Wichtig zu prüfen nach der Installation**: Der Contao Manager führt beim
  Composer-Update automatisch `assets:install` aus, danach liegen `public/dbChess.css`
  und `public/images/*` unter `web/bundles/<name>/…`. Ich habe in `config.php` und den
  Klassen testweise den Pfad `bundles/wiksoftdbchess/…` angenommen – bitte nach der
  Installation im Contao Manager-Log bzw. unter `web/bundles/` verifizieren, wie der
  Ordner tatsächlich heißt, und ggf. anpassen.

## Bugfix nach erster Installation: `getPath()` fehlte in der Bundle-Klasse

**Ursache, warum nach der Installation gar nichts aus `contao/` (weder DCA noch
Backend-Module noch Content-Elemente) geladen wurde:** Symfony ermittelt den
Bundle-Pfad standardmäßig aus dem Verzeichnis der Bundle-Klassendatei – bei
`src/WiksoftDbChessBundle.php` wäre das `.../src/`. Der `contao/`-Ordner liegt aber
auf Root-Ebene des Pakets (Geschwisterordner von `src/`), nicht unter `src/contao/`.
Contao hat den Ordner deshalb nie gefunden – ganz ohne Fehlermeldung, weil das
schlicht als "kein contao/-Ordner in diesem Bundle vorhanden" interpretiert wird.

**Fix:** `getPath()` in `src/WiksoftDbChessBundle.php` überschreiben:
```php
public function getPath(): string
{
    return \dirname(__DIR__);
}
```
Das ist die offiziell empfohlene Vorgehensweise für die neue, flache
`contao/`-Ordnerstruktur (siehe Contao-Entwicklerdokumentation).

**Um den Fix einzuspielen:** Reicht es, per FTP die Datei
`vendor/wiksoft/contao-dbchess-bundle/src/WiksoftDbChessBundle.php` direkt zu bearbeiten
(oder zu ersetzen) – ein neuer Composer-Lauf ist dafür nicht nötig, da PHP-Dateien
sofort wirksam werden. Danach `var/cache/prod/` erneut leeren (Bundle-/Ressourcen-
Cache muss neu aufgebaut werden) und im Contao Manager „Datenbank aktualisieren"
erneut anstoßen.

## Twig-Migration der Templates

Alle 4 Templates (`mod_dbChess_index`, `ce_dbChess_list_default`,
`ce_dbChess_list_table`, `ce_dbChess_download`) wurden von klassischen
PHP-Templates (`.html5`) auf Twig (`.html.twig`) umgestellt. Contao 5 bringt dafür eine eingebaute Template-Hierarchie
mit: Ein Twig-Template mit demselben Basisnamen im selben `contao/templates/`-Ordner
wird automatisch bevorzugt geladen und bekommt exakt dieselben Variablen, die die
PHP-Klasse über `$this->Template->xxx = ...` zuweist (`Template->getData()` wird zum
Twig-Kontext). Die drei Klassen (`ModuleDbChessIndex`, `ContentDbChessList`,
`ContentDbChessDownload`) mussten dafür **nicht** zu Fragment-Controllern umgebaut
werden – nur ihre `compile()`-Methoden liefern jetzt sauber aufbereitete Daten statt
dass die Templates selbst auf `$GLOBALS['TL_LANG']` oder Controller-Hilfsmethoden wie
`addToUrl()` zugreifen (Twig hat darauf keinen Zugriff):

- `ModuleDbChessIndex`: baut jetzt ein `tags`-Array (`key`, `count`, `url`, `size`)
  für die Tag-Cloud/Index-Liste vor, sowie `lblPlay` für die Play-Beschriftung.
- `ContentDbChessList`: baut ein `fieldLabels`-Array (Feldname → Sprachdatei-Label)
  für die Tabellenkopfzeile vor, sowie ebenfalls `lblPlay`.
- `ContentDbChessDownload`: keine Änderung nötig, ihr Template nutzte keine Labels.

**Escaping-Hinweis:** Twig escaped standardmäßig automatisch (anders als die alten
PHP-Templates). Wo bereits vorformatiertes HTML/URLs übergeben werden (`cssID`,
`style`, `href`, das in `ContentDbChessDownload` bereits vorab escapte `title`), wird
im Twig-Template bewusst der `|raw`-Filter gesetzt, um doppeltes Escaping zu
vermeiden. Reine Textfelder (Partiedaten wie Datum, Namen, Ergebnis) werden bewusst
NICHT `|raw` ausgegeben – das escaped jetzt sogar sauberer als vorher.

**Kleiner bekannter Nebeneffekt:** Die Template-Auswahl-Dropdowns in der DCA
(`getTemplateGroup('mod_dbChess_index')` bzw. `getTemplateGroup('ce_dbChess_list')`)
scannen das Dateisystem nach passenden Templates für die Auswahlliste im Backend.
Ob diese Methode `.html.twig`-Dateien ebenfalls als Auswahloption anzeigt, wurde
nicht geprüft – die aktuell in der DCA hinterlegten Standardwerte
(`mod_dbChess_index`, `ce_dbChess_list_default`) funktionieren aber in jedem Fall
weiter, da die Namensauflösung unabhängig von der Dateiendung erfolgt.

## Offener Punkt: ECO-Namen im Index werden berechnet, aber nirgends angezeigt

`ModuleDbChessIndex::compile()` lädt für den Index-Feldwert „ECO" zusätzlich die
Klartext-Eröffnungsnamen aus `tl_dbChess_eco` nach und legt sie als `ecoName` in
jedem Tag des `tags`-Arrays ab. Es gibt aktuell aber **kein** Template, das
`tag.ecoName` ausgibt – `mod_dbChess_index.html.twig` ist das einzige
Index-Template im Bundle und zeigt nur `tag.key`/`tag.count` an. Ursprünglich war
hier eine dritte Template-Variante geplant (die den ECO-Namen neben dem Code
anzeigt, per `{% extends %}` auf einer Tag-Cloud-Variante aufbauend) – diese
Templates (`mod_dbChess_indexCloud.html.twig`, `mod_dbChess_indexEco.html.twig`)
wurden nie angelegt. Bis ein solches Template ergänzt wird, ist der
ECO-Namen-Lookup toter Code (eine zusätzliche, aktuell ungenutzte Datenbank-Abfrage
pro Aufruf mit Index-Feld „ECO"). Wer die Funktion nicht braucht, kann
`fetchEcoNames()`/die Zuweisung von `ecoName` in `buildTagCloud()` ersatzlos
entfernen.

## Punkte, die nach der Installation getestet werden sollten

- **`REQUEST_TOKEN`** (in `DbChessImportController`): Diese Konstante ist ein
  Contao-3/4-Relikt. Falls sie in 5.7 nicht mehr existiert, durch den CSRF-Token-Service
  ersetzen: `System::getContainer()->get('contao.csrf.token_manager')`.
- **Backend-Custom-Actions** (`linkGame`, `unlinkGame`, `importPgn`, `exportPgn` als
  `global_operations` mit `key=...`): Der Mechanismus über
  `$GLOBALS['BE_MOD'][...]['aktion'] = array(Klasse::class, 'methode')` wird von
  `Contao\Backend::getBackendModule()` grundsätzlich weiterhin unterstützt – bitte im
  Backend einmal durchklicken (Partien verknüpfen/entknüpfen, PGN-Import/-Export).
- **PGN-Download-Element**: nutzt `Contao\Dbafs::syncFiles()` und legt Dateien im
  Upload-Verzeichnis ab – bitte einmal live testen, ob der automatische Datei-Sync
  in 5.7 identisch funktioniert.

## Installation über FTP + Contao Manager (ohne Git, ohne Konsole)

Composer unterstützt sogenannte **Path-Repositories**: ein Paket, das einfach als
Ordner irgendwo im Projekt-Dateisystem liegt, statt aus einem Git-Repository geklont
zu werden. In Kombination mit direktem FTP-Zugriff auf die `composer.json` lässt sich
das komplett ohne Git und ohne SSH-Konsole einrichten.

1. **Ordner per FTP hochladen.** Lade den kompletten Inhalt dieses ZIPs (den Ordner
   `contao-dbchess-bundle/`) in einen neuen Ordner im Projekt hoch, z. B.:
   ```
   packages/wiksoft/contao-dbchess-bundle/
   ```
   Wichtig: **nicht** in `vendor/` hochladen – dieser Ordner wird von Composer
   verwaltet und kann jederzeit überschrieben werden. `packages/` (oder ein anderer
   Name deiner Wahl) auf gleicher Ebene wie `vendor/`, `public/`/`web/` und
   `composer.json` ist der richtige Ort.

2. **Path-Repository in der `composer.json` eintragen.** Öffne die **Root-`composer.json`**
   deines Projekts (im Hauptverzeichnis, neben `vendor/`, `public/`/`web/` usw.) per FTP
   in deinem Texteditor und ergänze dort im `"repositories"`-Array folgenden Block
   (das Array ggf. neu anlegen, falls es noch nicht existiert):
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
   `"symlink": false` sorgt dafür, dass Composer den Ordner beim Installieren nach
   `vendor/wiksoft/contao-dbchess-bundle` **kopiert** (statt zu verlinken) – dadurch bleibt
   die Installation stabil, auch wenn du den `packages/`-Ordner später aufräumst.
   Wenn du stattdessen künftige Änderungen einfach durch erneutes FTP-Hochladen ins
   `packages/`-Verzeichnis einspielen willst, kannst du `"symlink": true` setzen
   (dann bleibt `vendor/...` ein Verweis auf `packages/...` und Änderungen wirken
   sofort, ohne erneutes "Composer update" – dafür darf der `packages/`-Ordner dann
   nie gelöscht werden).
   Datei speichern und per FTP zurück auf den Server hochladen.

3. **Paket im Contao Manager hinzufügen.** Jetzt den Contao Manager im Browser öffnen,
   unter "Pakete" nach `wiksoft/contao-dbchess-bundle` suchen (wird jetzt über das gerade
   eingetragene Path-Repository gefunden) und mit Version `1.0.0` hinzufügen.

4. Der Contao Manager führt Composer-Install, `contao:migrate` (mit Bestätigung)
   sowie `cache:clear`/`assets:install` automatisch im Hintergrund aus – weiterhin
   komplett ohne Konsole.

5. **Bei künftigen Änderungen** an der Erweiterung: neue Dateien per FTP in
   `packages/wiksoft/contao-dbchess-bundle/` hochladen, die `version` in dessen
   `composer.json` erhöhen (z. B. auf `1.0.1`) und im Contao Manager ein
   "Composer Update" für das Paket anstoßen.

## Datenbank-Migration nach der Installation

Nach der Installation die Datenbank-Migration im Contao Manager bestätigen (neue DCA
sollte inhaltlich identisch zur alten sein, es sollten also keine schema-brechenden
Änderungen anfallen – die Tabellen bleiben gleich benannt).

## Zielverzeichnisstruktur

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
