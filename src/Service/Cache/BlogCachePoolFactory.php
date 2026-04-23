<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Service\Cache;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

final class BlogCachePoolFactory
{
    /** @var array<string, CacheItemPoolInterface> */
    private static $pools = [];

    public static function create(string $namespace = 'default'): CacheItemPoolInterface
    {
        $namespace = self::normalizeNamespace($namespace);
        if (isset(self::$pools[$namespace])) {
            return self::$pools[$namespace];
        }

        $cacheDirectory = self::resolveCacheDirectory($namespace);
        if (!is_dir($cacheDirectory)) {
            @mkdir($cacheDirectory, 0755, true);
        }

        self::$pools[$namespace] = new TagAwareAdapter(
            new FilesystemAdapter('everpsblog.' . $namespace, 0, $cacheDirectory)
        );

        return self::$pools[$namespace];
    }

    private static function resolveCacheDirectory(string $namespace): string
    {
        $baseDirectory = defined('_PS_CACHE_DIR_')
            ? rtrim((string) _PS_CACHE_DIR_, DIRECTORY_SEPARATOR)
            : rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);

        return $baseDirectory
            . DIRECTORY_SEPARATOR
            . 'everpsblog'
            . DIRECTORY_SEPARATOR
            . $namespace;
    }

    private static function normalizeNamespace(string $namespace): string
    {
        $namespace = trim($namespace);
        if ('' === $namespace) {
            return 'default';
        }

        return trim((string) preg_replace('/[^A-Za-z0-9_.-]+/', '_', $namespace), '_');
    }
}
