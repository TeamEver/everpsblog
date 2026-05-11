<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}


class BlogInstallService
{
    /**
     * Seed the Root + Unclassed categories for every shop.
     *
     * @param object|null $module Legacy compatibility argument.
     *
     * @return bool
     */
    public function seedRootAndUnclassedCategories($module)
    {
        foreach (\Shop::getShops() as $shop) {
            $shopId = (int) $shop['id_shop'];

            $rootId = $this->ensureRootCategory($shopId);
            if ($rootId <= 0) {
                return false;
            }

            if ($this->getUnclassedCategoryId($shopId) <= 0) {
                $unclassedId = $this->ensureUnclassedCategory($shopId, $rootId, $module);
                if ($unclassedId <= 0) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Recreate the Unclassed category when it has been deleted.
     *
     * @param object|null $module Legacy compatibility argument.
     * @param int    $shopId
     * @param int    $rootCategoryId
     *
     * @return int Newly created unclassed category id, or 0 on failure.
     */
    public function recreateUnclassedCategory($module, $shopId, $rootCategoryId)
    {
        return $this->ensureUnclassedCategory((int) $shopId, (int) $rootCategoryId, $module);
    }

    public function ensureUnclassedCategory(int $shopId, int $rootCategoryId, $module = null): int
    {
        $db = \Db::getInstance();
        $now = date('Y-m-d H:i:s');

        $existingId = $this->getUnclassedCategoryId($shopId);
        if ($existingId > 0) {
            return $existingId;
        }

        if ($rootCategoryId <= 0) {
            return 0;
        }

        $unclassedTitle = $this->transAdmin('Unclassed');
        $unclassedId = $this->insertCategory(
            $db,
            [
                'id_parent_category' => (int) $rootCategoryId,
                'id_shop' => (int) $shopId,
                'date_add' => $now,
                'date_upd' => $now,
                'indexable' => 1,
                'follow' => 1,
                'sitemap' => 1,
                'active' => 1,
                'is_root_category' => 0,
            ],
            [
                'title' => $unclassedTitle,
                'content' => '',
                'link_rewrite' => \Tools::str2url($unclassedTitle) ?: 'unclassed',
            ]
        );

        if ($unclassedId <= 0) {
            return 0;
        }

        \Configuration::updateValue('EVERBLOG_UNCLASSED_ID', $unclassedId, false, null, $shopId);

        return $unclassedId;
    }

    /**
     * Retrieve (or 0 if missing) the root category id for a given shop.
     */
    public function getRootCategoryId(int $shopId): int
    {
        $db = \Db::getInstance();

        return (int) $db->getValue(
            'SELECT id_ever_category FROM `' . _DB_PREFIX_ . 'ever_blog_category`
             WHERE is_root_category = 1 AND id_shop = ' . (int) $shopId
        );
    }

    public function isRootCategoryId(int $categoryId, ?int $shopId = null): bool
    {
        if ($categoryId <= 0) {
            return false;
        }

        $where = 'c.id_ever_category = ' . (int) $categoryId . ' AND c.is_root_category = 1';
        if (null !== $shopId && $shopId > 0) {
            $where .= ' AND (c.id_shop = ' . (int) $shopId . ' OR cs.id_shop = ' . (int) $shopId . ')';
        }

        return (bool) \Db::getInstance()->getValue(
            'SELECT c.id_ever_category
             FROM `' . _DB_PREFIX_ . 'ever_blog_category` c
             LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_category_shop` cs
                ON cs.id_ever_category = c.id_ever_category
             WHERE ' . $where
        );
    }

    public function ensureRootCategory(int $shopId): int
    {
        $rootId = $this->getRootCategoryId($shopId);
        if ($rootId > 0) {
            return $rootId;
        }

        $now = date('Y-m-d H:i:s');

        return $this->insertCategory(
            \Db::getInstance(),
            [
                'id_parent_category' => 0,
                'id_shop' => $shopId,
                'date_add' => $now,
                'date_upd' => $now,
                'indexable' => 1,
                'follow' => 1,
                'sitemap' => 1,
                'active' => 1,
                'is_root_category' => 1,
            ],
            [
                'title' => 'Root',
                'content' => 'Root',
                'link_rewrite' => 'root',
            ]
        );
    }

    public function getUnclassedCategoryId(int $shopId): int
    {
        $db = \Db::getInstance();
        $configuredId = (int) \Configuration::get('EVERBLOG_UNCLASSED_ID', null, null, $shopId);
        if ($configuredId > 0 && $this->categoryBelongsToShop($configuredId, $shopId)) {
            return $configuredId;
        }

        $globalConfiguredId = (int) \Configuration::get('EVERBLOG_UNCLASSED_ID');
        if ($globalConfiguredId > 0 && $this->categoryBelongsToShop($globalConfiguredId, $shopId)) {
            \Configuration::updateValue('EVERBLOG_UNCLASSED_ID', $globalConfiguredId, false, null, $shopId);

            return $globalConfiguredId;
        }

        $unclassedId = (int) $db->getValue(
            'SELECT c.id_ever_category
             FROM `' . _DB_PREFIX_ . 'ever_blog_category` c
             INNER JOIN `' . _DB_PREFIX_ . 'ever_blog_category` root
                ON root.id_ever_category = c.id_parent_category
                AND root.is_root_category = 1
                AND root.id_shop = ' . (int) $shopId . '
             INNER JOIN `' . _DB_PREFIX_ . 'ever_blog_category_lang` cl
                ON cl.id_ever_category = c.id_ever_category
             WHERE COALESCE(c.is_root_category, 0) = 0
                AND c.id_shop = ' . (int) $shopId . '
                AND cl.link_rewrite IN ("unclassed", "non-classe")
             ORDER BY c.id_ever_category ASC'
        );

        if ($unclassedId > 0) {
            \Configuration::updateValue('EVERBLOG_UNCLASSED_ID', $unclassedId, false, null, $shopId);
        }

        return $unclassedId;
    }

    public function isUnclassedCategoryId(int $categoryId, ?int $shopId = null): bool
    {
        if ($categoryId <= 0) {
            return false;
        }

        $shopIds = null !== $shopId && $shopId > 0 ? [$shopId] : $this->getCategoryShopIds($categoryId);
        foreach ($shopIds as $categoryShopId) {
            if ($categoryId === $this->getUnclassedCategoryId((int) $categoryShopId)) {
                return true;
            }
        }

        $globalConfiguredId = (int) \Configuration::get('EVERBLOG_UNCLASSED_ID');

        return $globalConfiguredId > 0 && $categoryId === $globalConfiguredId;
    }

    public function isProtectedCategoryId(int $categoryId, ?int $shopId = null): bool
    {
        return $this->isRootCategoryId($categoryId, $shopId) || $this->isUnclassedCategoryId($categoryId, $shopId);
    }

    private function categoryBelongsToShop(int $categoryId, int $shopId): bool
    {
        if ($categoryId <= 0 || $shopId <= 0) {
            return false;
        }

        return (bool) \Db::getInstance()->getValue(
            'SELECT c.id_ever_category
             FROM `' . _DB_PREFIX_ . 'ever_blog_category` c
             LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_category_shop` cs
                ON cs.id_ever_category = c.id_ever_category
             WHERE c.id_ever_category = ' . (int) $categoryId . '
                AND (c.id_shop = ' . (int) $shopId . ' OR cs.id_shop = ' . (int) $shopId . ')'
        );
    }

    /**
     * @return int[]
     */
    private function getCategoryShopIds(int $categoryId): array
    {
        $rows = \Db::getInstance()->executeS(
            'SELECT DISTINCT id_shop
             FROM (
                SELECT id_shop
                FROM `' . _DB_PREFIX_ . 'ever_blog_category`
                WHERE id_ever_category = ' . (int) $categoryId . '
                UNION
                SELECT id_shop
                FROM `' . _DB_PREFIX_ . 'ever_blog_category_shop`
                WHERE id_ever_category = ' . (int) $categoryId . '
             ) shops
             WHERE id_shop > 0'
        ) ?: [];

        return array_values(array_filter(array_map(static function (array $row): int {
            return (int) ($row['id_shop'] ?? 0);
        }, $rows)));
    }

    private function transAdmin(string $message): string
    {
        try {
            return \Context::getContext()->getTranslator()->trans($message, [], 'Modules.Everpsblog.Admin');
        } catch (\Throwable $exception) {
            return $message;
        }
    }

    /**
     * @param \Db                  $db
     * @param array<string, mixed> $category
     * @param array<string, mixed> $defaultTranslation
     *
     * @return int
     */
    private function insertCategory($db, array $category, array $defaultTranslation): int
    {
        if (!$db->insert('ever_blog_category', $category)) {
            return 0;
        }

        $categoryId = (int) $db->Insert_ID();
        if ($categoryId <= 0) {
            return 0;
        }

        $db->insert('ever_blog_category_shop', [
            'id_ever_category' => $categoryId,
            'id_shop' => (int) $category['id_shop'],
        ]);

        foreach (\Language::getLanguages(false) as $language) {
            $langId = (int) $language['id_lang'];
            $db->insert('ever_blog_category_lang', [
                'id_ever_category' => $categoryId,
                'id_lang' => $langId,
                'title' => (string) $defaultTranslation['title'],
                'meta_title' => (string) $defaultTranslation['title'],
                'meta_description' => '',
                'link_rewrite' => (string) $defaultTranslation['link_rewrite'],
                'content' => (string) $defaultTranslation['content'],
                'bottom_content' => '',
            ]);
        }

        return $categoryId;
    }
}
