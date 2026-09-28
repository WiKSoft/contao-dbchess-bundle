<?php

declare(strict_types=1);

namespace Wiksoft\DbChessBundle\Controller\BackendModule;

use Contao\Environment;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;

/**
 * Bestätigungsseite für Sammlungs-Aktionen, die Daten ändern (Verknüpfen,
 * Lösen). Ausgeführt wird nur nach dem Absenden des Formulars per POST:
 * Contao prüft dabei das Request-Token, sodass ein bloßer Link (z.B. in
 * einer E-Mail) die Aktion nicht auslösen kann (CSRF).
 */
trait ConfirmFormTrait
{
    private function isConfirmed(string $formId): bool
    {
        return Input::post('FORM_SUBMIT') === $formId;
    }

    /**
     * URL der Partienliste (aktuelle URL ohne den "key"-Parameter).
     */
    private function backUrl(string $key): string
    {
        return str_replace('&key=' . $key, '', Environment::get('request'));
    }

    private function confirmForm(string $formId, string $key, string $headline, string $question, string $submitLabel): string
    {
        $strCsrfToken = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();

        return '
<div id="tl_buttons">
<a href="' . StringUtil::ampersand($this->backUrl($key)) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>

<h2 class="sub_headline">' . $headline . '</h2>
<form action="' . StringUtil::ampersand(Environment::get('request'), true) . '" id="' . $formId . '" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="' . $formId . '">
<input type="hidden" name="REQUEST_TOKEN" value="' . StringUtil::specialchars($strCsrfToken) . '">

<div class="tl_tbox">
  <p>' . $question . '</p>
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="' . StringUtil::specialchars($submitLabel) . '">
</div>

</div>
</form>';
    }
}
