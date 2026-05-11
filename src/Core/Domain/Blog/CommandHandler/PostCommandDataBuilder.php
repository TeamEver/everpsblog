<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\ValueObject\PostCommandData;
use PrestaShop\Module\Everpsblog\Service\BlogInstallService;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}


class PostCommandDataBuilder
{
    /** @var int */
    private $shopId;
    /** @var int */
    private $unclassedCategoryId;
    /** @var BlogInstallService|null */
    private $blogInstallService;

    public function __construct(int $shopId, ?int $unclassedCategoryId = 0, ?BlogInstallService $blogInstallService = null)
    {
        $this->shopId = $shopId;
        $this->unclassedCategoryId = (int) $unclassedCategoryId;
        $this->blogInstallService = $blogInstallService;
    }

    public function buildFromRequestData(array $data): PostCommandData
    {
        $rootCategoryId = $this->resolveRootCategoryId();
        $unclassedCategoryId = $this->resolveUnclassedCategoryId($rootCategoryId);
        $dateAdd = $this->normalizeDateAdd($data['date_add'] ?? '');

        return new PostCommandData(
            $this->shopId,
            (int) ($data['id_author'] ?? 0),
            (int) ($data['id_default_category'] ?? 0),
            $unclassedCategoryId,
            $rootCategoryId,
            (string) ($data['post_status'] ?? 'draft'),
            isset($data['psswd']) ? (string) $data['psswd'] : null,
            $dateAdd,
            (int) ($data['indexable'] ?? 0),
            (int) ($data['follow'] ?? 0),
            (int) ($data['sitemap'] ?? 1),
            (int) ($data['starred'] ?? 0),
            $this->toArray($data['post_categories'] ?? []),
            $this->toArray($data['allowed_groups'] ?? []),
            $this->toArray($data['post_tags'] ?? []),
            $this->toArray($data['post_products'] ?? []),
            $this->buildTranslations($data)
        );
    }

    private function resolveRootCategoryId(): int
    {
        if (null !== $this->blogInstallService) {
            $rootId = $this->blogInstallService->getRootCategoryId($this->shopId);
            if ($rootId > 0) {
                return $rootId;
            }

            $rootId = $this->blogInstallService->ensureRootCategory($this->shopId);
            if ($rootId > 0) {
                return $rootId;
            }
        }

        $db = \Db::getInstance();
        $rootId = (int) $db->getValue(
            'SELECT id_ever_category FROM `' . _DB_PREFIX_ . 'ever_blog_category`
             WHERE is_root_category = 1 AND id_shop = ' . (int) $this->shopId
        );

        return $rootId > 0 ? $rootId : 0;
    }

    private function resolveUnclassedCategoryId(int $rootCategoryId): int
    {
        if (null !== $this->blogInstallService) {
            $unclassedId = $this->blogInstallService->getUnclassedCategoryId($this->shopId);
            if ($unclassedId > 0) {
                $this->unclassedCategoryId = $unclassedId;

                return $unclassedId;
            }

            $unclassedId = $this->blogInstallService->ensureUnclassedCategory($this->shopId, $rootCategoryId);
            if ($unclassedId > 0) {
                $this->unclassedCategoryId = $unclassedId;

                return $unclassedId;
            }
        }

        if ($this->unclassedCategoryId > 0 && $this->categoryBelongsToCurrentShop($this->unclassedCategoryId)) {
            return $this->unclassedCategoryId;
        }

        return 0;
    }

    private function categoryBelongsToCurrentShop(int $categoryId): bool
    {
        return (bool) \Db::getInstance()->getValue(
            'SELECT c.id_ever_category
             FROM `' . _DB_PREFIX_ . 'ever_blog_category` c
             LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_category_shop` cs
                ON cs.id_ever_category = c.id_ever_category
             WHERE c.id_ever_category = ' . (int) $categoryId . '
                AND (c.id_shop = ' . (int) $this->shopId . ' OR cs.id_shop = ' . (int) $this->shopId . ')'
        );
    }

    private function buildTranslations(array $data): array
    {
        $translations = [];

        foreach (\Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];
            $title = (string) ($data['title_' . $idLang] ?? '');

            $translations[$idLang] = [
                'title' => $title,
                'content' => (string) ($data['content_' . $idLang] ?? ''),
                'excerpt' => (string) ($data['excerpt_' . $idLang] ?? ''),
                'meta_title' => (string) ($data['meta_title_' . $idLang] ?? ''),
                'meta_description' => (string) ($data['meta_description_' . $idLang] ?? ''),
                'link_rewrite' => Tools::str2url((string) ($data['link_rewrite_' . $idLang] ?? $title)),
            ];
        }

        return $translations;
    }

    private function toArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (null === $value || '' === $value) {
            return [];
        }

        return [$value];
    }

    private function normalizeDateAdd($value): string
    {
        $date = trim(str_replace('T', ' ', (string) $value));
        if ('' === $date) {
            return date('Y-m-d H:i:s');
        }

        foreach (['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d', 'd-m-Y H:i:s', 'd-m-Y H:i', 'd-m-Y', 'd/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y'] as $format) {
            $parsed = \DateTimeImmutable::createFromFormat('!' . $format, $date);
            $errors = \DateTimeImmutable::getLastErrors();
            if (false !== $parsed && (false === $errors || (0 === $errors['warning_count'] && 0 === $errors['error_count']))) {
                return $parsed->format('Y-m-d H:i:s');
            }
        }

        try {
            return (new \DateTimeImmutable($date))->format('Y-m-d H:i:s');
        } catch (\Throwable $exception) {
            return date('Y-m-d H:i:s');
        }
    }
}
