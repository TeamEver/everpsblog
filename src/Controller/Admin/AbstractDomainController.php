<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use Context;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractDomainController
{
    protected function redirectToLegacyController(Request $request, string $legacyController): RedirectResponse
    {
        $queryParameters = $request->query->all();
        unset($queryParameters['legacy_redirect']);
        $queryParameters['legacy_proxy'] = 1;

        $legacyUrl = Context::getContext()->link->getAdminLink(
            $legacyController,
            true,
            [],
            $queryParameters
        );

        return new RedirectResponse($legacyUrl, RedirectResponse::HTTP_FOUND);
    }
}
