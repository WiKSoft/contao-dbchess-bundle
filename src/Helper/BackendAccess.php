<?php

declare(strict_types=1);

namespace Wiksoft\DbChessBundle\Helper;

use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\AbstractAction;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\Database;
use Contao\System;

/**
 * Rechteprüfung für die Sammlungs-Aktionen im Backend (Import, Export,
 * Verknüpfen, Lösen). Diese laufen über "key"-Callbacks, die Contao nicht
 * selbst prüft (anders als act=edit/create/delete). Geprüft wird daher
 * über dieselben DataContainer-Berechtigungen, die Contao auch für die
 * normalen Aktionen verwendet (Modulzugriff, DCA-Flags wie "notCreatable",
 * eigene Voter).
 */
final class BackendAccess
{
    /**
     * Lädt die Partiesammlung und prüft, ob der Benutzer sie lesen darf.
     *
     * @return array<string, mixed>
     */
    public static function collection(int $id): array
    {
        $row = Database::getInstance()
            ->prepare('SELECT * FROM tl_dbChess_collection WHERE id=?')
            ->limit(1)
            ->execute($id)
            ->row();

        if (!$row) {
            throw new AccessDeniedException(\sprintf('Partiesammlung ID %s existiert nicht.', $id));
        }

        self::denyUnlessGranted(new ReadAction('tl_dbChess_collection', $row));

        return $row;
    }

    public static function denyUnlessGranted(AbstractAction $action): void
    {
        $table = $action->getDataSource();

        // Die Voter werten die geladene DCA aus (z.B. ausgeschlossene Felder)
        Controller::loadDataContainer($table);

        if (!System::getContainer()->get('security.helper')->isGranted(ContaoCorePermissions::DC_PREFIX . $table, $action)) {
            throw new AccessDeniedException(\sprintf('Keine Berechtigung für %s.', $table));
        }
    }
}
