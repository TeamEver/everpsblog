<?php

namespace PrestaShop\Module\Everpsblog\Grid\Data;

use PrestaShop\Module\Everpsblog\Core\Grid\GridData;
use PrestaShop\Module\Everpsblog\Repository\CommentRepository;
use PrestaShop\Module\Everpsblog\Service\AdminRouteSigner;
use Symfony\Component\Routing\RouterInterface;

final class CommentGridDataFactory
{
    use GridRecordFilterTrait;

    /** @var CommentRepository */
    private $commentRepository;
    /** @var AdminRouteSigner */
    private $routeSigner;
    /** @var RouterInterface */
    private $router;
    /** @var bool */
    private $useLegacyFallback;
    /** @var bool */
    private $useModernDeleteAction;

    public function __construct(
        CommentRepository $commentRepository,
        AdminRouteSigner $routeSigner,
        RouterInterface $router,
        bool $useLegacyFallback = true,
        bool $useModernDeleteAction = false
    )
    {
        $this->commentRepository = $commentRepository;
        $this->routeSigner = $routeSigner;
        $this->router = $router;
        $this->useLegacyFallback = $useLegacyFallback;
        $this->useModernDeleteAction = $useModernDeleteAction;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function build(int $langId, array $filters = []): GridData
    {
        $postIdFilter = (int) ($filters['id_ever_post'] ?? 0);
        $rows = $postIdFilter > 0
            ? $this->commentRepository->findByPostAndLanguage($postIdFilter, $langId)
            : $this->commentRepository->findBackOfficeList($langId);
        $records = [];

        foreach ($rows as $row) {
            $records[] = [
                'id_ever_comment' => (int) ($row['id_ever_comment'] ?? $row['id'] ?? 0),
                'id_ever_post' => (int) ($row['id_ever_post'] ?? $row['postId'] ?? 0),
                'name' => (string) ($row['name'] ?? ''),
                'user_email' => (string) ($row['user_email'] ?? $row['userEmail'] ?? ''),
                'comment' => (string) ($row['comment'] ?? ''),
                'active' => (string) ($row['active'] ?? 0)
            ];
        }
        $records = $this->filterRecords($records, $filters, [
            'id_ever_comment',
            'id_ever_post',
            'name',
            'user_email',
            'comment',
            'active',
        ]);

        foreach ($records as &$record) {
            $id = (int) $record['id_ever_comment'];
            $record['_actions'] = [
                'edit' => $this->resolveUrl(
                    'everpsblog_admin_comment_edit',
                    ['commentId' => $id],
                    'AdminEverPsBlogComment',
                    ['updatecomment' => $id]
                ),
                'delete' => $this->useModernDeleteAction
                    ? $this->resolveUrl('everpsblog_admin_comment_delete', ['commentId' => $id], 'AdminEverPsBlogComment', ['deletecomment' => $id])
                    : $this->routeSigner->sign('AdminEverPsBlogComment', ['deletecomment' => $id]),
                'delete_legacy' => $this->routeSigner->sign('AdminEverPsBlogComment', ['deletecomment' => $id]),
            ];
            $record['_bulk_actions'] = [
                'delete' => $this->resolveBulkActionUrl('everpsblog_admin_comment_bulk_delete', 'AdminEverPsBlogComment'),
                'approveall' => $this->resolveBulkActionUrl('everpsblog_admin_comment_bulk_approve', 'AdminEverPsBlogComment'),
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
