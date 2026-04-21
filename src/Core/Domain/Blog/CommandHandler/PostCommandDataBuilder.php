<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\ValueObject\PostCommandData;
use PrestaShop\Module\Everpsblog\Service\BlogInstallService;
use Tools;

class PostCommandDataBuilder
{
    /** @var int */
    private $shopId;
    /** @var int */
    private $unclassedCategoryId;
    /** @var BlogInstallService|null */
    private $blogInstallService;

    public function __construct(int $shopId, int $unclassedCategoryId, ?BlogInstallService $blogInstallService = null)
    {
        $this->shopId = $shopId;
        $this->unclassedCategoryId = $unclassedCategoryId;
        $this->blogInstallService = $blogInstallService;
    }

    public function buildFromRequestData(array $data): PostCommandData
    {
        $rootCategoryId = $this->resolveRootCategoryId();
        $dateAdd = $this->normalizeDateAdd($data['date_add'] ?? '');

        return new PostCommandData(
            $this->shopId,
            (int) ($data['id_author'] ?? 0),
            (int) ($data['id_default_category'] ?? 0),
            $this->unclassedCategoryId,
            $rootCategoryId,
            (string) ($data['post_status'] ?? 'draft'),
            isset($data['psswd']) ? (string) $data['psswd'] : null,
            $dateAdd,
            (int) ($data['indexable'] ?? 0),
            (int) ($data['follow'] ?? 0),
            (int) ($data['sitemap'] ?? 0),
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
        }

        $db = \Db::getInstance();
        $rootId = (int) $db->getValue(
            'SELECT id_ever_category FROM `' . _DB_PREFIX_ . 'ever_blog_category`
             WHERE is_root_category = 1 AND id_shop = ' . (int) $this->shopId
        );

        return $rootId > 0 ? $rootId : 0;
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
