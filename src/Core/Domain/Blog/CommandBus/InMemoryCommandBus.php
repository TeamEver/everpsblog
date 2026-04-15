<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandBus;

use RuntimeException;

class InMemoryCommandBus implements CommandBusInterface
{
    /** @var array<string, callable> */
    private $handlers;

    /**
     * @param array<string, callable> $handlers
     */
    public function __construct(array $handlers)
    {
        $this->handlers = $handlers;
    }

    public function handle($command)
    {
        $commandClass = get_class($command);

        if (!isset($this->handlers[$commandClass])) {
            throw new RuntimeException(sprintf('No handler registered for "%s".', $commandClass));
        }

        return call_user_func($this->handlers[$commandClass], $command);
    }
}
