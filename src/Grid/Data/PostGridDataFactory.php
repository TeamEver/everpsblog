<?php

namespace PrestaShop\Module\Everpsblog\Grid\Data;

use PrestaShop\Module\Everpsblog\Core\Grid\GridData;
use PrestaShop\Module\Everpsblog\Repository\PostRepository;
use PrestaShop\Module\Everpsblog\Service\AdminRouteSigner;

final class PostGridDataFactory
{
    /** @var PostRepository */
    private $postRepository;
    /** @var AdminRouteSigner */
    private $routeSigner;

    public function __construct(PostRepository $postRepository, AdminRouteSigner $routeSigner)
    {
        $this->postRepository = $postRepository;
        $this->routeSigner = $routeSigner;
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
                'edit' => $this->routeSigner->sign('AdminEverPsBlogPost', ['updatepost' => $id]),
                'delete' => $this->routeSigner->sign('AdminEverPsBlogPost', ['deletepost' => $id]),
            ];
        }

        return new GridData($records);
    }
}
