<?php

namespace PrestaShop\Module\Everpsblog\Grid\Data;

use PrestaShop\Module\Everpsblog\Core\Grid\GridData;
use PrestaShop\Module\Everpsblog\Repository\TagRepository;
use PrestaShop\Module\Everpsblog\Service\AdminRouteSigner;
use Symfony\Component\Routing\RouterInterface;

final class TagGridDataFactory
{
    use GridRecordFilterTrait;
    use FrontPreviewActionTrait;

    /** @var TagRepository */
    private $tagRepository;
    /** @var AdminRouteSigner */
    private $routeSigner;
    /** @var RouterInterface */
    private $router;
    /** @var bool */
    private $useLegacyFallback;
    /** @var bool */
    private $useModernDeleteAction;

    public function __construct(
        TagRepository $tagRepository,
        AdminRouteSigner $routeSigner,
        RouterInterface $router,
        bool $useLegacyFallback = true,
        bool $useModernDeleteAction = false
    )
    {
        $this->tagRepository = $tagRepository;
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
        $rows = $this->tagRepository->findBackOfficeList($langId, $shopId);
        $records = [];

        foreach ($rows as $row) {
            $records[] = [
                'id_ever_tag' => (int) ($row['id_ever_tag'] ?? $row['id'] ?? 0),
                'title' => (string) ($row['title'] ?? ''),
                'link_rewrite' => (string) ($row['link_rewrite'] ?? ''),
                'count' => (int) ($row['count'] ?? 0),
                'active' => (string) ($row['active'] ?? 0),
            ];
        }
        $records = $this->filterRecords($records, $filters, [
            'id_ever_tag',
            'title',
            'link_rewrite',
            'count',
            'active',
        ]);

        foreach ($records as &$record) {
            $id = (int) $record['id_ever_tag'];
            $record['_actions'] = [
                'edit' => $this->resolveUrl(
                    'everpsblog_admin_tag_edit',
                    ['tagId' => $id],
                    'AdminEverPsBlogTag',
                    ['updatetag' => $id]
                ),
                'delete' => $this->useModernDeleteAction
                    ? $this->resolveUrl('everpsblog_admin_tag_delete', ['tagId' => $id], 'AdminEverPsBlogTag', ['deletetag' => $id])
                    : $this->routeSigner->sign('AdminEverPsBlogTag', ['deletetag' => $id]),
                'delete_legacy' => $this->routeSigner->sign('AdminEverPsBlogTag', ['deletetag' => $id]),
                'preview' => $this->buildTagPreviewUrl($id, $shopId, $langId),
            ];
            $record['_bulk_actions'] = [
                'delete' => $this->resolveBulkActionUrl('everpsblog_admin_tag_bulk_delete', 'AdminEverPsBlogTag'),
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
