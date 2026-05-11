<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Service;

use Context;

if (!defined('_PS_VERSION_')) {
    exit;
}


final class AdminRouteSigner
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function sign(string $legacyController, array $parameters = []): string
    {
        return Context::getContext()->link->getAdminLink($legacyController, true, [], $parameters);
    }
}
