<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}


final class BlogThemeResolver
{
    public const CONFIGURATION_KEY = 'EVERPSBLOG_THEME';
    public const DEFAULT_THEME = 'default';
    public const THEMES_RELATIVE_DIRECTORY = 'views/themes';

    /** @var string */
    private $moduleName;
    /** @var string */
    private $moduleRootPath;

    public function __construct(string $moduleName = 'everpsblog', ?string $moduleRootPath = null)
    {
        $this->moduleName = trim($moduleName) !== '' ? $moduleName : 'everpsblog';
        $rootPath = $moduleRootPath ?: dirname(__DIR__, 2);
        $this->moduleRootPath = rtrim(str_replace('\\', '/', $rootPath), '/');
    }

    /**
     * @return string[]
     */
    public function getAvailableThemes(): array
    {
        $themes = [];
        $directories = glob($this->getThemesAbsoluteDirectory() . '/*', GLOB_ONLYDIR);

        if (is_array($directories)) {
            foreach ($directories as $directory) {
                $theme = $this->normalizeThemeName((string) basename($directory));
                if ($theme === '') {
                    continue;
                }

                $themes[$theme] = $theme;
            }
        }

        if (!$themes) {
            $themes[self::DEFAULT_THEME] = self::DEFAULT_THEME;
        }

        uksort($themes, function (string $left, string $right): int {
            if ($left === self::DEFAULT_THEME && $right !== self::DEFAULT_THEME) {
                return -1;
            }

            if ($right === self::DEFAULT_THEME && $left !== self::DEFAULT_THEME) {
                return 1;
            }

            return strcmp($left, $right);
        });

        return array_values($themes);
    }

    /**
     * @return array<string, string>
     */
    public function getThemeChoices(): array
    {
        $choices = [];
        foreach ($this->getAvailableThemes() as $theme) {
            $choices[$this->humanizeThemeName($theme)] = $theme;
        }

        return $choices;
    }

    public function resolveTheme(?string $configuredTheme): string
    {
        $theme = $this->normalizeThemeName((string) $configuredTheme);
        $availableThemes = $this->getAvailableThemes();

        if ($theme !== '' && in_array($theme, $availableThemes, true)) {
            return $theme;
        }

        if (in_array(self::DEFAULT_THEME, $availableThemes, true)) {
            return self::DEFAULT_THEME;
        }

        $fallbackTheme = reset($availableThemes);

        return is_string($fallbackTheme) && $fallbackTheme !== ''
            ? $fallbackTheme
            : self::DEFAULT_THEME;
    }

    public function getAreaTemplateResourcePath(string $area, string $template, ?string $configuredTheme): string
    {
        return $this->getThemeResourceBasePath($configuredTheme)
            . '/'
            . $this->normalizeArea($area)
            . '/'
            . $this->normalizeTemplatePath($template);
    }

    public function getAreaTemplateRelativePath(string $area, string $template, ?string $configuredTheme): string
    {
        return self::THEMES_RELATIVE_DIRECTORY
            . '/'
            . $this->resolveTheme($configuredTheme)
            . '/'
            . $this->normalizeArea($area)
            . '/'
            . $this->normalizeTemplatePath($template);
    }

    public function getAreaAbsolutePath(string $area, ?string $configuredTheme): string
    {
        return $this->getThemeAbsoluteDirectory($this->resolveTheme($configuredTheme))
            . '/'
            . $this->normalizeArea($area);
    }

    /**
     * @return array<string, string>
     */
    public function buildSmartyContext(?string $configuredTheme): array
    {
        $theme = $this->resolveTheme($configuredTheme);
        $themeResourceBase = $this->getThemeResourceBasePath($theme);

        return [
            'everpsblog_active_theme' => $theme,
            'everpsblog_theme_resource_base' => $themeResourceBase,
            'everpsblog_theme_front_template_base' => $themeResourceBase . '/front',
            'everpsblog_theme_hook_template_base' => $themeResourceBase . '/hook',
            'blog_path' => $this->getAreaAbsolutePath('front', $theme),
        ];
    }

    private function getThemeResourceBasePath(?string $configuredTheme): string
    {
        return 'module:'
            . $this->moduleName
            . '/'
            . self::THEMES_RELATIVE_DIRECTORY
            . '/'
            . $this->resolveTheme($configuredTheme);
    }

    private function getThemesAbsoluteDirectory(): string
    {
        return $this->moduleRootPath . '/' . self::THEMES_RELATIVE_DIRECTORY;
    }

    private function getThemeAbsoluteDirectory(string $theme): string
    {
        return $this->getThemesAbsoluteDirectory() . '/' . $this->normalizeThemeName($theme);
    }

    private function normalizeArea(string $area): string
    {
        $area = trim(str_replace('\\', '/', $area), '/');

        return $area !== '' ? $area : 'front';
    }

    private function normalizeTemplatePath(string $template): string
    {
        return ltrim(str_replace('\\', '/', $template), '/');
    }

    private function normalizeThemeName(string $theme): string
    {
        $theme = strtolower(trim($theme));
        $theme = preg_replace('/[^a-z0-9_-]+/', '-', $theme);

        return trim((string) $theme, '-_');
    }

    private function humanizeThemeName(string $theme): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $theme));
    }
}
