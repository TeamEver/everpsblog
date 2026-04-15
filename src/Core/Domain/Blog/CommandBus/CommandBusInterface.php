<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandBus;

interface CommandBusInterface
{
    /**
     * @param object $command
     *
     * @return mixed
     */
    public function handle($command);
}
