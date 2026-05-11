<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Grid\Data;

use PrestaShop\Module\Everpsblog\Core\Grid\GridData;
use PrestaShop\Module\Everpsblog\Repository\PostRepository;
use PrestaShop\Module\Everpsblog\Service\AdminRouteSigner;
use PrestaShop\Module\Everpsblog\Service\BlogImageService;
use Symfony\Component\Routing\RouterInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}


final class PostGridDataFactory
{
    use GridRecordFilterTrait;
    use FrontPreviewActionTrait;

    /** @var PostRepository */
    private $postRepository;
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
        PostRepository $postRepository,
        AdminRouteSigner $routeSigner,
        RouterInterface $router,
        bool $useLegacyFallback = true,
        bool $useModernDeleteAction = false,
        ?BlogImageService $blogImageService = null
    )
    {
        $this->postRepository = $postRepository;
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
        $orderBy = $this->resolveOrderBy($filters['orderBy'] ?? 'date_add');
        $orderWay = $this->resolveOrderWay($filters['orderWay'] ?? 'DESC');
        unset($filters['orderBy'], $filters['orderWay']);

        $rows = $this->postRepository->findBackOfficeList($langId, $shopId, 50, $orderBy, $orderWay);
        $records = [];

        foreach ($rows as $row) {
            $postId = (int) ($row['id_ever_post'] ?? $row['id'] ?? 0);
            $records[] = [
                'id_ever_post' => $postId,
                'featured_image' => $this->resolveFeaturedImage($postId, $shopId),
                'title' => (string) ($row['title'] ?? ''),
                'post_status' => (string) ($row['post_status'] ?? $row['status'] ?? ''),
                'date_add' => $this->formatDateAdd($row['date_add'] ?? null),
                'count' => (int) ($row['count'] ?? $row['viewCount'] ?? 0),
            ];
        }
        $records = $this->filterRecords($records, $filters, [
            'id_ever_post',
            'title',
            'post_status',
            'date_add',
            'count',
        ]);

        foreach ($records as &$record) {
            $id = (int) $record['id_ever_post'];
            $record['_actions'] = [
                'edit' => $this->resolveUrl(
                    'everpsblog_admin_post_edit',
                    ['postId' => $id],
                    'AdminEverPsBlogPost',
                    ['updatepost' => $id]
                ),
                'delete' => $this->useModernDeleteAction
                    ? $this->resolveUrl('everpsblog_admin_post_delete', ['postId' => $id], 'AdminEverPsBlogPost', ['deletepost' => $id])
                    : $this->routeSigner->sign('AdminEverPsBlogPost', ['deletepost' => $id]),
                'delete_legacy' => $this->routeSigner->sign('AdminEverPsBlogPost', ['deletepost' => $id]),
                'duplicate' => $this->resolveUrl(
                    'everpsblog_admin_post_duplicate',
                    ['postId' => $id],
                    'AdminEverPsBlogPost',
                    ['duplicatepost' => $id]
                ),
                'preview' => $this->buildPostPreviewUrl($id, $shopId, $langId),
            ];
            $record['_bulk_actions'] = [
                'delete' => $this->resolveBulkActionUrl('everpsblog_admin_post_bulk', 'AdminEverPsBlogPost'),
                'publishall' => $this->resolveBulkActionUrl('everpsblog_admin_post_bulk', 'AdminEverPsBlogPost'),
                'duplicate' => $this->resolveBulkActionUrl('everpsblog_admin_post_bulk', 'AdminEverPsBlogPost'),
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

    private function resolveOrderBy($orderBy): string
    {
        $orderBy = (string) $orderBy;
        $allowed = ['id_ever_post', 'title', 'post_status', 'count', 'date_add'];

        return in_array($orderBy, $allowed, true) ? $orderBy : 'date_add';
    }

    private function resolveOrderWay($orderWay): string
    {
        return 'ASC' === strtoupper((string) $orderWay) ? 'ASC' : 'DESC';
    }

    private function formatDateAdd($value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        $date = trim((string) $value);
        if ('' === $date) {
            return '';
        }

        try {
            return (new \DateTimeImmutable($date))->format('Y-m-d H:i:s');
        } catch (\Throwable $exception) {
            return $date;
        }
    }

    private function resolveFeaturedImage(int $postId, int $shopId): ?string
    {
        if ($postId <= 0 || null === $this->blogImageService) {
            return null;
        }

        try {
            $image = $this->blogImageService->getBlogImage($postId, $shopId, 'post');
            if (!\Validate::isLoadedObject($image)) {
                return null;
            }

            return (string) $this->blogImageService->getBlogImageUrl($postId, $shopId, 'post');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
