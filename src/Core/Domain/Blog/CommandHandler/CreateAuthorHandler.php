<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\CreateAuthorCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\AuthorWriteRepository;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;

class CreateAuthorHandler
{
    /** @var AuthorWriteRepository */
    private $repository;
    /** @var BlogFrontCacheInvalidator */
    private $cacheInvalidator;

    public function __construct(AuthorWriteRepository $repository, ?BlogFrontCacheInvalidator $cacheInvalidator = null)
    {
        $this->repository = $repository;
        $this->cacheInvalidator = $cacheInvalidator ?: new BlogFrontCacheInvalidator();
    }

    public function __invoke(CreateAuthorCommand $command): int
    {
        $authorId = $this->repository->create($command->getData());
        $this->cacheInvalidator->invalidateAuthorMutation($authorId);

        return $authorId;
    }
}
