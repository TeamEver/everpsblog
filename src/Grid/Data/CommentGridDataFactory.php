<?php

namespace PrestaShop\Module\Everpsblog\Grid\Data;

use PrestaShop\Module\Everpsblog\Core\Grid\GridData;
use PrestaShop\Module\Everpsblog\Repository\CommentRepository;
use PrestaShop\Module\Everpsblog\Service\AdminRouteSigner;

final class CommentGridDataFactory
{
    /** @var CommentRepository */
    private $commentRepository;
    /** @var AdminRouteSigner */
    private $routeSigner;

    public function __construct(CommentRepository $commentRepository, AdminRouteSigner $routeSigner)
    {
        $this->commentRepository = $commentRepository;
        $this->routeSigner = $routeSigner;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function build(int $langId, array $filters = []): GridData
    {
        $rows = $this->commentRepository->findByPostAndLanguage((int) ($filters['id_ever_post'] ?? 0), $langId);
        $records = [];

        foreach ($rows as $row) {
            $records[] = [
                'id_ever_comment' => $row['id'] ?? $row['id'.substr('id_ever_comment',3)] ?? 0,
                'id_ever_post' => $row['postId'] ?? 0,
                'active' => (string) ($row['active'] ?? 0)
            ];
        }

        foreach ($records as &$record) {
            $id = (int) $record['id_ever_comment'];
            $record['_actions'] = [
                'edit' => $this->routeSigner->sign('AdminEverPsBlogComment', ['updatecomment' => $id]),
                'delete' => $this->routeSigner->sign('AdminEverPsBlogComment', ['deletecomment' => $id]),
            ];
        }

        return new GridData($records);
    }
}
