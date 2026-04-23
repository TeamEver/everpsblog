<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\CreateTagCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\TagWriteRepository;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;

class CreateTagHandler
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

    public function __invoke(CreateTagCommand $command): int
    {
        $tagId = $this->repository->create($command->getData());
        $this->cacheInvalidator->invalidateTagMutation($tagId);

        return $tagId;
    }
}
