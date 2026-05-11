<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Service\Audit;

use Context;
use PrestaShopLogger;
use Psr\Log\LoggerInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}


class SensitiveActionLogger
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function log(string $action, array $context = []): void
    {
        $employeeId = (int) (Context::getContext()->employee->id ?? 0);
        $shopId = (int) (Context::getContext()->shop->id ?? 0);

        $normalizedContext = array_merge([
            'employee_id' => $employeeId,
            'shop_id' => $shopId,
        ], $context);

        $message = sprintf('[everpsblog][sensitive_action] %s %s', $action, json_encode($normalizedContext));
        $this->logger->notice($message, $normalizedContext);
        PrestaShopLogger::addLog($message, 1, null, 'EverPsBlog', 0, true);
    }
}
