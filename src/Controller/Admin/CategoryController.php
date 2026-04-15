<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends AbstractDomainController
{
    public function indexAction(Request $request): RedirectResponse
    {
        return $this->redirectToLegacyController($request, 'AdminEverPsBlogCategory');
    }
}
