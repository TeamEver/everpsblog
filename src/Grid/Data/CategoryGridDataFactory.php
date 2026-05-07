<?php

namespace PrestaShop\Module\Everpsblog\Grid\Data;

use PrestaShop\Module\Everpsblog\Core\Grid\GridData;
use PrestaShop\Module\Everpsblog\Repository\CategoryRepository;
use PrestaShop\Module\Everpsblog\Service\AdminRouteSigner;
use PrestaShop\Module\Everpsblog\Service\BlogInstallService;
use Symfony\Component\Routing\RouterInterface;

final class CategoryGridDataFactory
{
    use GridRecordFilterTrait;
    use FrontPreviewActionTrait;

    /** @var CategoryRepository */
    private $categoryRepository;
    /** @var BlogInstallService */
    private $blogInstallService;
    /** @var AdminRouteSigner */
    private $routeSigner;
    /** @var RouterInterface */
    private $router;
    /** @var bool */
    private $useLegacyFallback;
    /** @var bool */
    private $useModernDeleteAction;

    public function __construct(
        CategoryRepository $categoryRepository,
        BlogInstallService $blogInstallService,
        AdminRouteSigner $routeSigner,
        RouterInterface $router,
        bool $useLegacyFallback = true,
        bool $useModernDeleteAction = false
    )
    {
        $this->categoryRepository = $categoryRepository;
        $this->blogInstallService = $blogInstallService;
        $this->routeSigner = $routeSigner;
        $this->router = $router;
        $this->useLegacyFallback = $useLegacyFallback;
        $this->useModernDeleteAction = $useModernDeleteAction;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function build(int $shopId, int $langId, array $filters = []): GridData
    {
        $rows = $this->categoryRepository->findByShopAndLanguage($shopId, $langId);
        $unclassedCategoryId = $this->blogInstallService->getUnclassedCategoryId($shopId);
        $records = [];

        foreach ($rows as $row) {
            $translations = $row['translations'] ?? ($row['cl'] ?? []);
            if (is_array($translations) && isset($translations[0])) {
                $translation = $translations[0];
            } else {
                $translation = is_array($translations) ? $translations : [];
            }
            $title = is_array($translation) ? (string) ($translation['title'] ?? '') : '';
            $id = (int) ($row['id'] ?? 0);
            $records[] = [
                'id_ever_category' => $id,
                'title' => $title,
                'active' => (string) ($row['active'] ?? 0),
                '_is_deletable' => $id > 0 && $id !== $unclassedCategoryId,
            ];
        }
        $records = $this->filterRecords($records, $filters, [
            'id_ever_category',
            'title',
            'active',
        ]);

        foreach ($records as &$record) {
            $id = (int) $record['id_ever_category'];
            $record['_actions'] = [
                'edit' => $this->resolveUrl(
                    'everpsblog_admin_category_edit',
                    ['categoryId' => $id],
                    'AdminEverPsBlogCategory',
                    ['updatecategory' => $id]
                ),
                'preview' => $this->buildCategoryPreviewUrl($id, $shopId, $langId),
            ];
            if (!empty($record['_is_deletable'])) {
                $record['_actions']['delete'] = $this->useModernDeleteAction
                    ? $this->resolveUrl('everpsblog_admin_category_delete', ['categoryId' => $id], 'AdminEverPsBlogCategory', ['deletecategory' => $id])
                    : $this->routeSigner->sign('AdminEverPsBlogCategory', ['deletecategory' => $id]);
                $record['_actions']['delete_legacy'] = $this->routeSigner->sign('AdminEverPsBlogCategory', ['deletecategory' => $id]);
            }
            $record['_bulk_actions'] = [
                'delete' => $this->resolveBulkActionUrl('everpsblog_admin_category_bulk_delete', 'AdminEverPsBlogCategory'),
            ];
        }

        return new GridData($records);
    }

    /**
     * @param array<string, mixed> $modernParameters
     * @param array<string, mixed> $legacyParameters
     */
    private function resolveUrl(string $modernRoute, array $modernParameters, string $legacyController, array $legacyParameters): string
    {
        if ($this->routeExists($modernRoute)) {
            return $this->router->generate($modernRoute, $modernParameters);
        }

        return $this->useLegacyFallback ? $this->routeSigner->sign($legacyController, $legacyParameters) : '';
    }

    private function resolveBulkActionUrl(string $modernRoute, string $legacyController): string
    {
        if ($this->routeExists($modernRoute)) {
            return $this->router->generate($modernRoute);
        }

        return $this->useLegacyFallback ? $this->routeSigner->sign($legacyController) : '';
    }

    private function routeExists(string $routeName): bool
    {
        return null !== $this->router->getRouteCollection()->get($routeName);
    }
}
