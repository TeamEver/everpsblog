<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\UpdateTagCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\TagWriteRepository;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;

class UpdateTagHandler
{
    /** @var TagWriteRepository */
    private $repository;
    /** @var BlogFrontCacheInvalidator */
    private $cacheInvalidator;

    public function __construct(TagWriteRepository $repository, ?BlogFrontCacheInvalidator $cacheInvalidator = null)
    {
        $this->repository = $repository;
        $this->cacheInvalidator = $cacheInvalidator ?: new BlogFrontCacheInvalidator();
    }

    public function __invoke(UpdateTagCommand $command): int
    {
        $this->repository->update($command->getTagId(), $command->getData());
        $this->cacheInvalidator->invalidateTagMutation($command->getTagId());

        return $command->getTagId();
    }
}
