<?php

namespace PrestaShop\Module\Everpsblog\Grid\Data;

use PrestaShop\Module\Everpsblog\Core\Grid\GridData;
use PrestaShop\Module\Everpsblog\Repository\PostRepository;
use PrestaShop\Module\Everpsblog\Service\AdminRouteSigner;
use Symfony\Component\Routing\RouterInterface;

final class PostGridDataFactory
{
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

    public function __construct(
        PostRepository $postRepository,
        AdminRouteSigner $routeSigner,
        RouterInterface $router,
        bool $useLegacyFallback = true,
        bool $useModernDeleteAction = false
    )
    {
        $this->postRepository = $postRepository;
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
        $rows = $this->postRepository->findBackOfficeList($langId, $shopId);
        $records = [];

        foreach ($rows as $row) {
            $records[] = [
                'id_ever_post' => $row['id'] ?? $row['id'.substr('id_ever_post',3)] ?? 0,
                'title' => $row['pl']['title'] ?? '',
                'post_status' => $row['postStatus'] ?? '',
                'count' => $row['count'] ?? 0
            ];
        }

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
            ];
            $record['_bulk_actions'] = [
                'delete' => $this->resolveBulkActionUrl('everpsblog_admin_post_bulk_delete', 'AdminEverPsBlogPost'),
                'publishall' => $this->resolveBulkActionUrl('everpsblog_admin_post_bulk_publish', 'AdminEverPsBlogPost'),
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
