<?php

namespace PrestaShop\Module\Everpsblog\Grid\Data;

use PrestaShop\Module\Everpsblog\Core\Grid\GridData;
use PrestaShop\Module\Everpsblog\Repository\AuthorRepository;
use PrestaShop\Module\Everpsblog\Service\AdminRouteSigner;

final class AuthorGridDataFactory
{
    /** @var AuthorRepository */
    private $authorRepository;
    /** @var AdminRouteSigner */
    private $routeSigner;

    public function __construct(AuthorRepository $authorRepository, AdminRouteSigner $routeSigner)
    {
        $this->authorRepository = $authorRepository;
        $this->routeSigner = $routeSigner;
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
                'edit' => $this->routeSigner->sign('AdminEverPsBlogAuthor', ['updateauthor' => $id]),
                'delete' => $this->routeSigner->sign('AdminEverPsBlogAuthor', ['deleteauthor' => $id]),
            ];
        }

        return new GridData($records);
    }
}
