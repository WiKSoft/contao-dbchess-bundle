<?php

namespace Wiksoft\DbChessBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class WiksoftDbChessBundle extends Bundle
{
    /**
     * Contao-Ressourcen (contao/config, contao/dca, contao/languages,
     * contao/templates) liegen auf Root-Ebene des Pakets, NICHT unter src/.
     * Ohne diesen Override würde Symfony den Bundle-Pfad aus dem Verzeichnis
     * der Bundle-Klassendatei ableiten (also "src/") und der contao/-Ordner
     * würde nie gefunden werden.
     */
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
