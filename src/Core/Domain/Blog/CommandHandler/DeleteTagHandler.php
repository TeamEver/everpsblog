<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeleteTagCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\TagWriteRepository;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;

if (!defined('_PS_VERSION_')) {
    exit;
}


class DeleteTagHandler
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

    public function __invoke(DeleteTagCommand $command): void
    {
        $this->repository->delete($command->getTagId());
        $this->cacheInvalidator->invalidateTagMutation($command->getTagId());
    }
}
