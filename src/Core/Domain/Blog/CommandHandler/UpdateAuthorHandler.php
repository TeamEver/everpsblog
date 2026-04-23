<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\UpdateAuthorCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\AuthorWriteRepository;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;

class UpdateAuthorHandler
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

    public function __invoke(UpdateAuthorCommand $command): int
    {
        $this->repository->update($command->getAuthorId(), $command->getData());
        $this->cacheInvalidator->invalidateAuthorMutation($command->getAuthorId());

        return $command->getAuthorId();
    }
}
