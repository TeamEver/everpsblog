<?php

namespace PrestaShop\Module\Everpsblog\Grid\Data;

use PrestaShop\Module\Everpsblog\Core\Grid\GridData;
use PrestaShop\Module\Everpsblog\Repository\TagRepository;
use PrestaShop\Module\Everpsblog\Service\AdminRouteSigner;

final class TagGridDataFactory
{
    /** @var TagRepository */
    private $tagRepository;
    /** @var AdminRouteSigner */
    private $routeSigner;

    public function __construct(TagRepository $tagRepository, AdminRouteSigner $routeSigner)
    {
        $this->tagRepository = $tagRepository;
        $this->routeSigner = $routeSigner;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function build(int $shopId, int $langId, array $filters = []): GridData
    {
        $rows = $this->tagRepository->findByShopAndLanguage($shopId, $langId);
        $records = [];

        foreach ($rows as $row) {
            $records[] = [
                'id_ever_tag' => $row['id'] ?? $row['id'.substr('id_ever_tag',3)] ?? 0,
                'title' => $row['tl']['title'] ?? '',
                'active' => (string) ($row['active'] ?? 0)
            ];
        }

        foreach ($records as &$record) {
            $id = (int) $record['id_ever_tag'];
            $record['_actions'] = [
                'edit' => $this->routeSigner->sign('AdminEverPsBlogTag', ['updatetag' => $id]),
                'delete' => $this->routeSigner->sign('AdminEverPsBlogTag', ['deletetag' => $id]),
            ];
        }

        return new GridData($records);
    }
}
