<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class ModuleTranslationSourceExtractor
{
    public const ADMIN_DOMAIN = 'Modules.Everpsblog.Admin';
    public const SHOP_DOMAIN = 'Modules.Everpsblog.Shop';

    /**
     * @return array<int, array{domain:string,key:string}>
     */
    public function extract(string $scope = 'all'): array
    {
        $entries = [];
        $moduleRoot = dirname(__DIR__, 2);

        $this->extractExplicitTranslations($moduleRoot, $entries);
        $this->extractImplicitAdminTranslations($moduleRoot, $entries);

        ksort($entries);

        $filtered = [];
        foreach ($entries as $entry) {
            if (!$this->matchesScope($entry['domain'], $scope)) {
                continue;
            }

            $filtered[] = $entry;
        }

        return $filtered;
    }

    /**
     * @param array<string, array{domain:string,key:string}> $entries
     */
    private function extractExplicitTranslations(string $moduleRoot, array &$entries): void
    {
        foreach ($this->iterateFiles($moduleRoot, ['php', 'twig', 'tpl']) as $filePath) {
            $content = file_get_contents($filePath);
            if (false === $content || '' === $content) {
                continue;
            }

            $this->extractByPattern(
                $content,
                '/\btransAdmin\(\s*(\'(?:\\\\.|[^\'])*\'|"(?:\\\\.|[^"])*")/s',
                self::ADMIN_DOMAIN,
                $entries
            );
            $this->extractByPattern(
                $content,
                '/\btransShop\(\s*(\'(?:\\\\.|[^\'])*\'|"(?:\\\\.|[^"])*")/s',
                self::SHOP_DOMAIN,
                $entries
            );

            if (preg_match_all('/\btrans\(\s*(\'(?:\\\\.|[^\'])*\'|"(?:\\\\.|[^"])*")\s*,.*?[\'"]Modules\.Everpsblog\.(Admin|Shop)[\'"]/s', $content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $domain = 'Admin' === (string) $match[2] ? self::ADMIN_DOMAIN : self::SHOP_DOMAIN;
                    $this->addEntry($entries, $domain, $this->decodeLiteral((string) $match[1]));
                }
            }

            if (preg_match_all('/(\'(?:\\\\.|[^\'])*\'|"(?:\\\\.|[^"])*")\s*\|\s*trans\([^)]*[\'"]Modules\.Everpsblog\.(Admin|Shop)[\'"][^)]*\)/s', $content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $domain = 'Admin' === (string) $match[2] ? self::ADMIN_DOMAIN : self::SHOP_DOMAIN;
                    $this->addEntry($entries, $domain, $this->decodeLiteral((string) $match[1]));
                }
            }

            if (preg_match_all('/\{l\s+s=(["\'])(.*?)\1\s+d=(["\'])Modules\.Everpsblog\.(Admin|Shop)\3[^}]*\}/s', $content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $domain = 'Admin' === (string) $match[4] ? self::ADMIN_DOMAIN : self::SHOP_DOMAIN;
                    $this->addEntry($entries, $domain, $this->decodeQuotedValue((string) $match[2]));
                }
            }
        }
    }

    /**
     * @param array<string, array{domain:string,key:string}> $entries
     */
    private function extractImplicitAdminTranslations(string $moduleRoot, array &$entries): void
    {
        $formDirectory = $moduleRoot . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Form' . DIRECTORY_SEPARATOR . 'Type' . DIRECTORY_SEPARATOR . 'Admin';
        foreach ($this->iterateFiles($formDirectory, ['php']) as $filePath) {
            $this->extractFormAdminStringsFromFile($filePath, $entries);
        }

        $gridDirectory = $moduleRoot . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Grid' . DIRECTORY_SEPARATOR . 'Definition';
        foreach ($this->iterateFiles($gridDirectory, ['php']) as $filePath) {
            $this->extractGridAdminStringsFromFile($filePath, $entries);
        }

        $abstractControllerFile = $moduleRoot . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . 'Admin' . DIRECTORY_SEPARATOR . 'AbstractDomainController.php';
        if (is_file($abstractControllerFile)) {
            $this->extractAdminMetadataStringsFromFile($abstractControllerFile, $entries);
        }
    }

    /**
     * @param array<string, array{domain:string,key:string}> $entries
     */
    private function extractFormAdminStringsFromFile(string $filePath, array &$entries): void
    {
        $content = file_get_contents($filePath);
        if (false === $content || '' === $content) {
            return;
        }

        $this->extractAdminMetadataStringsByPattern(
            $content,
            '/[\'"](label|help|placeholder|invalid_message|autocomplete_placeholder|autocomplete_loading_text|autocomplete_empty_text|autocomplete_min_length_text|autocomplete_remove_text|message)[\'"]\s*=>\s*(\'(?:\\\\.|[^\'])*\'|"(?:\\\\.|[^"])*")/s',
            $entries
        );
        $this->extractAdminChoiceLabels($content, $entries);
        $this->extractAdminMetadataStringsByPattern(
            $content,
            '/\bsprintf\(\s*(\'(?:\\\\.|[^\'])*\'|"(?:\\\\.|[^"])*")/s',
            $entries
        );
    }

    /**
     * @param array<string, array{domain:string,key:string}> $entries
     */
    private function extractGridAdminStringsFromFile(string $filePath, array &$entries): void
    {
        $content = file_get_contents($filePath);
        if (false === $content || '' === $content) {
            return;
        }

        if (!preg_match_all('/\'(?:\\\\.|[^\'])*\'|"(?:\\\\.|[^"])*"/s', $content, $matches)) {
            return;
        }

        foreach ($matches[0] as $literal) {
            $value = $this->decodeLiteral((string) $literal);
            if ($this->looksHumanFacing($value)) {
                $this->addEntry($entries, self::ADMIN_DOMAIN, $value);
            }
        }
    }

    /**
     * @param array<string, array{domain:string,key:string}> $entries
     */
    private function extractAdminMetadataStringsFromFile(string $filePath, array &$entries): void
    {
        $content = file_get_contents($filePath);
        if (false === $content || '' === $content) {
            return;
        }

        $this->extractAdminMetadataStringsByPattern(
            $content,
            '/[\'"](label|title|name|message)[\'"]\s*=>\s*(\'(?:\\\\.|[^\'])*\'|"(?:\\\\.|[^"])*")/s',
            $entries
        );
    }

    /**
     * @param array<string, array{domain:string,key:string}> $entries
     */
    private function extractByPattern(string $content, string $pattern, string $domain, array &$entries): void
    {
        if (!preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            return;
        }

        foreach ($matches as $match) {
            $this->addEntry($entries, $domain, $this->decodeLiteral((string) $match[1]));
        }
    }

    /**
     * @param array<string, array{domain:string,key:string}> $entries
     */
    private function extractAdminMetadataStringsByPattern(string $content, string $pattern, array &$entries): void
    {
        if (!preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            return;
        }

        foreach ($matches as $match) {
            $literal = (string) $match[count($match) - 1];
            $value = $this->decodeLiteral($literal);
            if ($this->looksHumanFacing($value)) {
                $this->addEntry($entries, self::ADMIN_DOMAIN, $value);
            }
        }
    }

    /**
     * @param array<string, array{domain:string,key:string}> $entries
     */
    private function extractAdminChoiceLabels(string $content, array &$entries): void
    {
        if (!preg_match_all('/[\'"]choices[\'"]\s*=>\s*\[(.*?)\]/s', $content, $choiceBlocks, PREG_SET_ORDER)) {
            return;
        }

        foreach ($choiceBlocks as $choiceBlock) {
            if (!isset($choiceBlock[1]) || !preg_match_all('/(\'(?:\\\\.|[^\'])*\'|"(?:\\\\.|[^"])*")\s*=>/s', (string) $choiceBlock[1], $labels, PREG_SET_ORDER)) {
                continue;
            }

            foreach ($labels as $labelMatch) {
                $value = $this->decodeLiteral((string) $labelMatch[1]);
                if ($this->looksHumanFacing($value)) {
                    $this->addEntry($entries, self::ADMIN_DOMAIN, $value);
                }
            }
        }
    }

    /**
     * @param array<string, array{domain:string,key:string}> $entries
     */
    private function addEntry(array &$entries, string $domain, string $key): void
    {
        $key = trim($key);
        if ('' === $key) {
            return;
        }

        $entries[$domain . "\0" . $key] = [
            'domain' => $domain,
            'key' => $key,
        ];
    }

    private function decodeLiteral(string $literal): string
    {
        $quote = substr($literal, 0, 1);
        if ('' === $quote) {
            return '';
        }

        return $this->decodeQuotedValue(substr($literal, 1, -1), $quote);
    }

    private function decodeQuotedValue(string $value, string $quote = "'"): string
    {
        $value = str_replace(['\\' . $quote, '\\\\'], [$quote, '\\'], $value);

        if ('"' === $quote) {
            return stripcslashes($value);
        }

        return $value;
    }

    private function matchesScope(string $domain, string $scope): bool
    {
        if ('admin' === $scope) {
            return self::ADMIN_DOMAIN === $domain;
        }

        if ('shop' === $scope) {
            return self::SHOP_DOMAIN === $domain;
        }

        return true;
    }

    private function looksHumanFacing(string $value): bool
    {
        $value = trim($value);
        if ('' === $value || !preg_match('/[A-Za-z]/', $value)) {
            return false;
        }

        if (preg_match('/^Modules\.Everpsblog\./', $value)
            || preg_match('/^everpsblog_/', $value)
            || preg_match('/^AdminEverPsBlog/', $value)
            || preg_match('/^@/', $value)
            || preg_match('/^#[0-9a-fA-F]{6}$/', $value)
            || preg_match('/^[a-z0-9_.:-]+$/', $value)
            || preg_match('/^[YMDHhms:\/\-]+$/', $value)
            || false !== strpos($value, '\\')
            || false !== strpos($value, '/')
            || false !== strpos($value, '::')
            || false !== strpos($value, '_PS_')
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param string[] $extensions
     *
     * @return \Generator<int, string>
     */
    private function iterateFiles(string $path, array $extensions): \Generator
    {
        if (is_file($path)) {
            yield $path;

            return;
        }

        if (!is_dir($path)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            if (!in_array(strtolower((string) $fileInfo->getExtension()), $extensions, true)) {
                continue;
            }

            yield $fileInfo->getPathname();
        }
    }
}
