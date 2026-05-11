<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandBus;

if (!defined('_PS_VERSION_')) {
    exit;
}


interface CommandBusInterface
{
    /**
     * @param object $command
     *
     * @return mixed
     */
    public function handle($command);
}
