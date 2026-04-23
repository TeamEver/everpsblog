<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Service\BlogSitemapService;
use PrestaShop\Module\Everpsblog\Service\ContextStateService;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

abstract class AbstractDomainController extends FrameworkBundleAdminController
{
    /** @var ContextStateService */
    protected $contextStateService;

    public function __construct(ContextStateService $contextStateService)
    {
        $this->contextStateService = $contextStateService;
    }

    protected function getContextShopId(): int
    {
        return $this->contextStateService->getShopId();
    }

    protected function getContextLangId(): int
    {
        return $this->contextStateService->getLanguageId();
    }

    protected function transAdmin(string $message, array $parameters = []): string
    {
        return $this->trans($message, 'Modules.Everpsblog.Admin', $parameters);
    }

    /**
     * Build a debug-friendly description of a caught exception.
     * Always safe to display in the BO; includes class + message + root cause
     * so the operator can act on the error without grep-ing the PS logs.
     */
    protected function describeException(\Throwable $exception): string
    {
        $parts = [];
        $current = $exception;
        while (null !== $current) {
            $parts[] = sprintf(
                '%s: %s (@%s:%d)',
                (new \ReflectionClass($current))->getShortName(),
                $current->getMessage(),
                basename($current->getFile()),
                $current->getLine()
            );
            $current = $current->getPrevious();
        }

        return implode(' <= ', $parts);
    }

    protected function getAdminNavigationLinks(): array
    {
        return [
            ['key' => 'post', 'label' => 'Posts', 'url' => $this->generateUrl('everpsblog_admin_post')],
            ['key' => 'category', 'label' => 'Categories', 'url' => $this->generateUrl('everpsblog_admin_category')],
            ['key' => 'tag', 'label' => 'Tags', 'url' => $this->generateUrl('everpsblog_admin_tag')],
            ['key' => 'author', 'label' => 'Authors', 'url' => $this->generateUrl('everpsblog_admin_author')],
            ['key' => 'comment', 'label' => 'Comments', 'url' => $this->generateUrl('everpsblog_admin_comment')],
            ['key' => 'configuration', 'label' => 'Configuration', 'url' => $this->generateUrl('everpsblog_admin_dashboard')],
        ];
    }

    /**
     * Build the language switcher payload consumed by the modern form template.
     * Returns one entry per active Prestashop language with its real ISO code
     * as label, so the BO never shows "FR/FR/EN/EN" duplicates caused by label
     * parsing.
     *
     * @return array<int, array<string, int|string>>
     */
    protected function getEverBlogLanguages(): array
    {
        $languages = [];
        try {
            $rawLanguages = \Language::getLanguages(false);
        } catch (\Throwable $exception) {
            return [];
        }

        foreach ($rawLanguages as $language) {
            $idLang = (int) ($language['id_lang'] ?? 0);
            if ($idLang <= 0) {
                continue;
            }

            $isoCode = strtoupper((string) ($language['iso_code'] ?? ''));
            $name = trim((string) ($language['name'] ?? ''));

            if ('' === $isoCode) {
                $isoCode = 'L' . $idLang;
            }

            $languages[] = [
                'id' => $idLang,
                'label' => $isoCode,
                'name' => '' !== $name ? $name : $isoCode,
            ];
        }

        return $languages;
    }

    protected function refreshSitemapsAfterBackOfficeChange(BlogSitemapService $blogSitemapService, bool $flashWarning = true): bool
    {
        try {
            $refreshed = (bool) $blogSitemapService->refreshForShop($this->getContextShopId());
            if (!$refreshed && $flashWarning) {
                $this->addFlash('warning', $this->transAdmin('Sitemaps were regenerated, but robots.txt could not be updated.'));
            }

            return $refreshed;
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog(
                '[everpsblog][AdminSitemapRefresh] ' . $exception->getMessage()
                    . ' @ ' . $exception->getFile() . ':' . $exception->getLine(),
                3
            );
            if ($flashWarning) {
                $this->addFlash(
                    'warning',
                    $this->transAdmin(
                        'Sitemaps could not be regenerated: %error%',
                        ['%error%' => $this->describeException($exception)]
                    )
                );
            }

            return false;
        }
    }

    /**
     * @param array<string, string> $targetFields field name => button label
     *
     * @return array<string, array<string, int|string>>
     */
    protected function buildQcdPageBuilderTargets(string $targetType, ?int $targetId, array $targetFields): array
    {
        $targetId = (int) $targetId;
        if ($targetId <= 0 || empty($targetFields) || !$this->isQcdPageBuilderActive()) {
            return [];
        }

        $targets = [];
        foreach (\Language::getLanguages(false) as $language) {
            $idLang = (int) ($language['id_lang'] ?? 0);
            if ($idLang <= 0) {
                continue;
            }

            foreach ($targetFields as $targetField => $label) {
                $targetField = (string) $targetField;
                $builderUrl = $this->buildQcdPageBuilderUrl($targetType, $targetId, $targetField, $idLang);
                if ('' === $builderUrl) {
                    continue;
                }

                $fieldName = sprintf('%s_%d', $targetField, $idLang);
                $targets[$fieldName] = [
                    'label' => (string) $label,
                    'builder_url' => $builderUrl,
                    'target_type' => $targetType,
                    'target_id' => $targetId,
                    'target_field' => $targetField,
                    'id_shop' => $this->getContextShopId(),
                    'id_lang' => $idLang,
                ];
            }
        }

        return $targets;
    }

    private function isQcdPageBuilderActive(): bool
    {
        try {
            $module = \Module::getInstanceByName('qcdpagebuilder');
        } catch (\Throwable $exception) {
            return false;
        }

        return $module instanceof \Module && (bool) $module->active;
    }

    private function buildQcdPageBuilderUrl(string $targetType, int $targetId, string $targetField, int $idLang): string
    {
        if (!$this->isValidQcdIdentifier($targetType) || !$this->isValidQcdIdentifier($targetField)) {
            return '';
        }

        try {
            return $this->generateUrl('admin_qcd_pagebuilder', [
                'zone' => 'bo_wysiwyg',
                'target_type' => $targetType,
                'target_id' => $targetId,
                'target_field' => $targetField,
                'id_shop' => $this->getContextShopId(),
                'id_lang' => $idLang,
                'embed' => 1,
                'isEmbbed' => 1,
            ]);
        } catch (\Throwable $exception) {
            return '';
        }
    }

    private function isValidQcdIdentifier(string $value): bool
    {
        return (bool) preg_match('/^[a-z0-9_]{2,64}$/', $value);
    }

}
