<?php

namespace PrestaShop\Module\Everpsblog\Service;

use Context;

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
