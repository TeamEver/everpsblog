<?php

namespace PrestaShop\Module\Everpsblog\Service;

class BlogInstallService
{
    /**
     * Seed the Root + Unclassed categories for every shop.
     *
     * @param object $module Legacy module to translate strings with l().
     *
     * @return bool
     */
    public function seedRootAndUnclassedCategories($module)
    {
        $db = \Db::getInstance();
        $now = date('Y-m-d H:i:s');

        foreach (\Shop::getShops() as $shop) {
            $shopId = (int) $shop['id_shop'];

            // Skip shops where a root category already exists (idempotent install)
            $rootId = (int) $db->getValue(
                'SELECT id_ever_category FROM `' . _DB_PREFIX_ . 'ever_blog_category`
                 WHERE is_root_category = 1 AND id_shop = ' . $shopId
            );

            if ($rootId <= 0) {
                $rootId = $this->insertCategory(
                    $db,
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

                if ($rootId <= 0) {
                    return false;
                }
            }

            $unclassedConfigured = (int) \Configuration::get('EVERBLOG_UNCLASSED_ID');
            $unclassedExists = false;
            if ($unclassedConfigured > 0) {
                $unclassedExists = (bool) $db->getValue(
                    'SELECT id_ever_category FROM `' . _DB_PREFIX_ . 'ever_blog_category`
                     WHERE id_ever_category = ' . $unclassedConfigured
                );
            }

            if (!$unclassedExists) {
                $unclassedTitle = method_exists($module, 'l') ? (string) $module->l('Unclassed') : 'Unclassed';
                $unclassedId = $this->insertCategory(
                    $db,
                    [
                        'id_parent_category' => $rootId,
                        'id_shop' => $shopId,
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
                    return false;
                }

                \Configuration::updateValue('EVERBLOG_UNCLASSED_ID', $unclassedId);
            }
        }

        return true;
    }

    /**
     * Recreate the Unclassed category when it has been deleted.
     *
     * @param object $module Legacy module to translate strings with l().
     * @param int    $shopId
     * @param int    $rootCategoryId
     *
     * @return int Newly created unclassed category id, or 0 on failure.
     */
    public function recreateUnclassedCategory($module, $shopId, $rootCategoryId)
    {
        $db = \Db::getInstance();
        $now = date('Y-m-d H:i:s');

        $unclassedTitle = method_exists($module, 'l') ? (string) $module->l('Unclassed') : 'Unclassed';
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

        \Configuration::updateValue('EVERBLOG_UNCLASSED_ID', $unclassedId);

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
