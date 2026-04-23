<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\Everpsblog\Service\ContextStateService;
use PrestaShop\Module\Everpsblog\Service\ProductAutocompleteProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ProductAutocompleteController extends AbstractDomainController
{
    /** @var ProductAutocompleteProvider */
    private $productAutocompleteProvider;

    public function __construct(ContextStateService $contextStateService, ProductAutocompleteProvider $productAutocompleteProvider)
    {
        parent::__construct($contextStateService);
        $this->productAutocompleteProvider = $productAutocompleteProvider;
    }

    public function searchAction(Request $request): JsonResponse
    {
        $query = trim((string) $request->query->get('q', ''));
        if ('' === $query) {
            return new JsonResponse(['products' => []], JsonResponse::HTTP_OK);
        }

        if (mb_strlen($query) < 2 && !ctype_digit($query)) {
            return new JsonResponse(['products' => []], JsonResponse::HTTP_OK);
        }

        return new JsonResponse([
            'products' => $this->productAutocompleteProvider->searchProducts(
                $query,
                $this->getContextShopId(),
                $this->getContextLangId(),
                20
            ),
        ], JsonResponse::HTTP_OK);
    }
}
