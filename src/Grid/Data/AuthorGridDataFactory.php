<?php

namespace PrestaShop\Module\Everpsblog\Grid\Data;

use PrestaShop\Module\Everpsblog\Core\Grid\GridData;
use PrestaShop\Module\Everpsblog\Repository\AuthorRepository;
use PrestaShop\Module\Everpsblog\Service\AdminRouteSigner;
use Symfony\Component\Routing\RouterInterface;

final class AuthorGridDataFactory
{
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

    public function __construct(
        AuthorRepository $authorRepository,
        AdminRouteSigner $routeSigner,
        RouterInterface $router,
        bool $useLegacyFallback = true,
        bool $useModernDeleteAction = false
    )
    {
        $this->authorRepository = $authorRepository;
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
        $rows = $this->authorRepository->findByShopAndLanguage($shopId, $langId);
        $records = [];

        foreach ($rows as $row) {
            $records[] = [
                'id_ever_author' => $row['id'] ?? $row['id'.substr('id_ever_author',3)] ?? 0,
                'nickhandle' => $row['nickhandle'] ?? '',
                'active' => (string) ($row['active'] ?? 0)
            ];
        }

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
}
