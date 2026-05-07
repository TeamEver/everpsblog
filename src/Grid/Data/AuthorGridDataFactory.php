<?php

namespace PrestaShop\Module\Everpsblog\Grid\Data;

use PrestaShop\Module\Everpsblog\Core\Grid\GridData;
use PrestaShop\Module\Everpsblog\Repository\AuthorRepository;
use PrestaShop\Module\Everpsblog\Service\AdminRouteSigner;
use PrestaShop\Module\Everpsblog\Service\BlogImageService;
use Symfony\Component\Routing\RouterInterface;

final class AuthorGridDataFactory
{
    use GridRecordFilterTrait;
    use FrontPreviewActionTrait;

    /** @var AuthorRepository */
    private $authorRepository;
    /** @var AdminRouteSigner */
    private $routeSigner;
    /** @var RouterInterface */
    private $router;
    /** @var bool */
    private $useLegacyFallback;
    /** @var bool */
    private $useModernDeleteAction;
    /** @var BlogImageService|null */
    private $blogImageService;

    public function __construct(
        AuthorRepository $authorRepository,
        AdminRouteSigner $routeSigner,
        RouterInterface $router,
        bool $useLegacyFallback = true,
        bool $useModernDeleteAction = false,
        ?BlogImageService $blogImageService = null
    )
    {
        $this->authorRepository = $authorRepository;
        $this->routeSigner = $routeSigner;
        $this->router = $router;
        $this->useLegacyFallback = $useLegacyFallback;
        $this->useModernDeleteAction = $useModernDeleteAction;
        $this->blogImageService = $blogImageService;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function build(int $shopId, int $langId, array $filters = []): GridData
    {
        $rows = $this->authorRepository->findByShopAndLanguage($shopId, $langId);
        $records = [];

        foreach ($rows as $row) {
            $authorId = (int) ($row['id'] ?? 0);
            $records[] = [
                'id_ever_author' => $authorId,
                'featured_image' => $this->resolveAuthorImage($authorId, $shopId),
                'nickhandle' => (string) ($row['nickhandle'] ?? ''),
                'active' => (string) ($row['active'] ?? 0),
            ];
        }
        $records = $this->filterRecords($records, $filters, [
            'id_ever_author',
            'nickhandle',
            'active',
        ]);

        foreach ($records as &$record) {
            $id = (int) $record['id_ever_author'];
            $record['_actions'] = [
                'edit' => $this->resolveUrl(
                    'everpsblog_admin_author_edit',
                    ['authorId' => $id],
                    'AdminEverPsBlogAuthor',
                    ['updateauthor' => $id]
                ),
                'delete' => $this->useModernDeleteAction
                    ? $this->resolveUrl('everpsblog_admin_author_delete', ['authorId' => $id], 'AdminEverPsBlogAuthor', ['deleteauthor' => $id])
                    : $this->routeSigner->sign('AdminEverPsBlogAuthor', ['deleteauthor' => $id]),
                'delete_legacy' => $this->routeSigner->sign('AdminEverPsBlogAuthor', ['deleteauthor' => $id]),
                'preview' => $this->buildAuthorPreviewUrl($id, $shopId, $langId),
            ];
            $record['_bulk_actions'] = [
                'delete' => $this->resolveBulkActionUrl('everpsblog_admin_author_bulk_delete', 'AdminEverPsBlogAuthor'),
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

    private function resolveAuthorImage(int $authorId, int $shopId): ?string
    {
        if ($authorId <= 0 || null === $this->blogImageService) {
            return null;
        }

        try {
            $image = $this->blogImageService->getBlogImage($authorId, $shopId, 'author');
            if (!\Validate::isLoadedObject($image)) {
                return null;
            }

            return (string) $this->blogImageService->getBlogImageUrl($authorId, $shopId, 'author');
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
