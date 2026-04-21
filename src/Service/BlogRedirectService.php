<?php

namespace PrestaShop\Module\Everpsblog\Service;

final class BlogRedirectService
{
    /** @var bool|null */
    private $redirectTableExists;

    public function saveRedirect(string $sourceUrl, string $targetUrl, int $shopId, string $entityType = '', int $entityId = 0): bool
    {
        if (!$this->tableExists()) {
            $this->fixDatabase();
        }
        if (!$this->tableExists()) {
            return false;
        }

        $sourcePath = $this->normalizeSourcePath($sourceUrl);
        $targetUrl = trim($targetUrl);
        $shopId = (int) $shopId;

        if ('' === $sourcePath || '' === $targetUrl || $shopId <= 0) {
            return false;
        }

        $targetPath = $this->normalizeSourcePath($targetUrl);
        if ($targetPath === $sourcePath) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $sourceHash = md5($sourcePath);
        $existingId = (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT id_ever_redirect FROM `' . _DB_PREFIX_ . 'ever_blog_redirect`
             WHERE id_shop = ' . (int) $shopId . '
             AND source_hash = "' . pSQL($sourceHash) . '"'
        );

        $payload = [
            'id_shop' => $shopId,
            'source_url' => pSQL($sourceUrl),
            'source_path' => pSQL($sourcePath),
            'source_hash' => pSQL($sourceHash),
            'target_url' => pSQL($targetUrl),
            'entity_type' => '' !== $entityType ? pSQL($entityType) : null,
            'id_element' => $entityId > 0 ? (int) $entityId : null,
            'http_code' => 301,
            'active' => 1,
            'date_upd' => pSQL($now),
        ];

        if ($existingId > 0) {
            return (bool) \Db::getInstance()->update(
                'ever_blog_redirect',
                $payload,
                'id_ever_redirect = ' . (int) $existingId
            );
        }

        $payload['hits'] = 0;
        $payload['date_add'] = pSQL($now);

        return (bool) \Db::getInstance()->insert('ever_blog_redirect', $payload);
    }

    public function saveRedirects(array $redirects, int $shopId): int
    {
        $count = 0;
        foreach ($redirects as $entityType => $items) {
            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $sourceUrl => $targetUrl) {
                if ($this->saveRedirect((string) $sourceUrl, (string) $targetUrl, $shopId, (string) $entityType)) {
                    ++$count;
                }
            }
        }

        return $count;
    }

    public function findRedirectForCurrentRequest(int $shopId): ?array
    {
        if ($shopId <= 0 || empty($_SERVER['REQUEST_URI'])) {
            return null;
        }
        if (!$this->tableExists()) {
            return null;
        }

        $candidates = $this->getCurrentRequestCandidates();
        foreach ($candidates as $sourcePath) {
            $row = $this->findBySourcePath($sourcePath, $shopId);
            if (!is_array($row)) {
                continue;
            }

            $targetUrl = (string) ($row['target_url'] ?? '');
            if ('' === $targetUrl || in_array($this->normalizeSourcePath($targetUrl), $candidates, true)) {
                continue;
            }

            $this->markHit((int) $row['id_ever_redirect']);

            return [
                'target_url' => $targetUrl,
                'http_code' => max(300, min(399, (int) ($row['http_code'] ?? 301))),
            ];
        }

        return null;
    }

    public function normalizeSourcePath(string $sourceUrl): string
    {
        $sourceUrl = trim(html_entity_decode($sourceUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ('' === $sourceUrl) {
            return '';
        }

        $parts = @parse_url($sourceUrl);
        $path = is_array($parts) && isset($parts['path']) ? (string) $parts['path'] : $sourceUrl;
        $query = is_array($parts) && isset($parts['query']) ? (string) $parts['query'] : '';

        if ('' === $path) {
            $path = '/';
        }

        $path = rawurldecode($path);
        $path = preg_replace('#/+#', '/', $path);
        if ('' === $path || '/' !== $path[0]) {
            $path = '/' . $path;
        }

        $path = '/' === $path ? '/' : rtrim($path, '/');

        return '' !== $query ? $path . '?' . $query : $path;
    }

    private function findBySourcePath(string $sourcePath, int $shopId): ?array
    {
        $sourceHash = md5($sourcePath);
        $row = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT *
             FROM `' . _DB_PREFIX_ . 'ever_blog_redirect`
             WHERE id_shop IN (0, ' . (int) $shopId . ')
             AND source_hash = "' . pSQL($sourceHash) . '"
             AND source_path = "' . pSQL($sourcePath) . '"
             AND active = 1
             ORDER BY id_shop DESC, id_ever_redirect DESC'
        );

        return is_array($row) ? $row : null;
    }

    private function getCurrentRequestCandidates(): array
    {
        $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        $candidates = [$this->normalizeSourcePath($requestUri)];

        $baseUri = defined('__PS_BASE_URI__') ? (string) __PS_BASE_URI__ : '';
        if ('' !== $baseUri && '/' !== $baseUri) {
            $baseUri = '/' . trim($baseUri, '/') . '/';
            $parts = @parse_url($requestUri);
            $path = is_array($parts) && isset($parts['path']) ? (string) $parts['path'] : $requestUri;
            $query = is_array($parts) && isset($parts['query']) ? '?' . (string) $parts['query'] : '';

            if (0 === strpos($path, $baseUri)) {
                $candidates[] = $this->normalizeSourcePath('/' . substr($path, strlen($baseUri)) . $query);
            }
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    private function tableExists(): bool
    {
        if (null !== $this->redirectTableExists) {
            return (bool) $this->redirectTableExists;
        }

        $this->redirectTableExists = (bool) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT COUNT(*)
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = "' . pSQL(_DB_PREFIX_ . 'ever_blog_redirect') . '"'
        );

        return (bool) $this->redirectTableExists;
    }

    private function fixDatabase(): void
    {
        try {
            (new DatabaseIntegrityService())->checkAndFix();
            $this->redirectTableExists = null;
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog('[everpsblog][BlogRedirect] Unable to ensure redirect table: ' . $exception->getMessage(), 3);
        }
    }

    private function markHit(int $redirectId): void
    {
        if ($redirectId <= 0) {
            return;
        }

        \Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'ever_blog_redirect`
             SET hits = hits + 1, last_hit = "' . pSQL(date('Y-m-d H:i:s')) . '"
             WHERE id_ever_redirect = ' . (int) $redirectId
        );
    }
}
