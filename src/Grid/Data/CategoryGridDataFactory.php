<?php

namespace PrestaShop\Module\Everpsblog\Grid\Data;

use PrestaShop\Module\Everpsblog\Core\Grid\GridData;
use PrestaShop\Module\Everpsblog\Repository\CategoryRepository;
use PrestaShop\Module\Everpsblog\Service\AdminRouteSigner;

final class CategoryGridDataFactory
{
    /** @var CategoryRepository */
    private $categoryRepository;
    /** @var AdminRouteSigner */
    private $routeSigner;

    public function __construct(CategoryRepository $categoryRepository, AdminRouteSigner $routeSigner)
    {
        $this->categoryRepository = $categoryRepository;
        $this->routeSigner = $routeSigner;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function build(int $shopId, int $langId, array $filters = []): GridData
    {
        $rows = $this->categoryRepository->findByShopAndLanguage($shopId, $langId);
        $records = [];

        foreach ($rows as $row) {
            $records[] = [
                'id_ever_category' => $row['id'] ?? $row['id'.substr('id_ever_category',3)] ?? 0,
                'title' => $row['cl']['title'] ?? '',
                'active' => (string) ($row['active'] ?? 0)
            ];
        }

        foreach ($records as &$record) {
            $id = (int) $record['id_ever_category'];
            $record['_actions'] = [
                'edit' => $this->routeSigner->sign('AdminEverPsBlogCategory', ['updatecategory' => $id]),
                'delete' => $this->routeSigner->sign('AdminEverPsBlogCategory', ['deletecategory' => $id]),
            ];
        }

        return new GridData($records);
    }
}
