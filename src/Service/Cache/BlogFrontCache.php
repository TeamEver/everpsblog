<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Service\Cache;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Psr\Cache\CacheItemPoolInterface;

final class BlogFrontCache
{
    /** @var CacheItemPoolInterface */
    private $cache;

    public function __construct(?CacheItemPoolInterface $cache = null)
    {
        $this->cache = $cache ?: BlogCachePoolFactory::create('front');
    }

    /**
     * @param array<int|string, mixed> $parts
     * @param callable(): mixed $resolver
     * @param string[] $tags
     * @param null|callable(mixed): array<int, string> $dynamicTagsResolver
     *
     * @return mixed
     */
    public function remember(string $scope, array $parts, callable $resolver, array $tags = [], ?callable $dynamicTagsResolver = null)
    {
        $item = $this->cache->getItem($this->buildKey($scope, $parts));
        if ($item->isHit()) {
            return $item->get();
        }

        $value = $resolver();
        $resolvedTags = $this->normalizeTags($tags);
        if (null !== $dynamicTagsResolver) {
            $resolvedTags = array_merge($resolvedTags, $this->normalizeTags((array) $dynamicTagsResolver($value)));
        }

        $item->set($value);
        if (!empty($resolvedTags) && method_exists($item, 'tag')) {
            $item->tag($resolvedTags);
        }
        $this->cache->save($item);

        return $value;
    }

    /**
     * @param string[] $tags
     */
    public function invalidateTags(array $tags): void
    {
        $tags = $this->normalizeTags($tags);
        if (empty($tags)) {
            return;
        }

        if (method_exists($this->cache, 'invalidateTags')) {
            $this->cache->invalidateTags($tags);

            return;
        }

        $this->cache->clear();
    }

    public function clear(): void
    {
        $this->cache->clear();
    }

    /**
     * @param array<int|string, mixed> $parts
     */
    public function buildKey(string $scope, array $parts): string
    {
        $scope = trim((string) preg_replace('/[^A-Za-z0-9_.-]+/', '.', str_replace('\\', '.', $scope)), '.');
        if ('' === $scope) {
            $scope = 'scope';
        }

        $payload = json_encode($parts);
        if (false === $payload) {
            $payload = serialize($parts);
        }

        return 'everpsblog.front.' . $scope . '.' . md5((string) $payload);
    }

    /**
     * @param string[] $tags
     *
     * @return string[]
     */
    private function normalizeTags(array $tags): array
    {
        $normalized = [];
        foreach ($tags as $tag) {
            $tag = trim((string) $tag);
            if ('' === $tag) {
                continue;
            }

            $normalized[] = trim((string) preg_replace('/[^A-Za-z0-9_.-]+/', '.', $tag), '.');
        }

        return array_values(array_unique(array_filter($normalized)));
    }
}
