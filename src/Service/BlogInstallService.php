<?php

namespace PrestaShop\Module\Everpsblog\Service;

class BlogInstallService
{
    /**
     * @param object $module Legacy module to translate strings with l().
     */
    public function seedRootAndUnclassedCategories($module)
    {
        foreach (\Shop::getShops() as $shop) {
            $rootCategory = new \EverPsBlogCategory();
            $rootCategory->is_root_category = 1;
            $rootCategory->active = 1;
            $rootCategory->id_shop = (int) $shop['id_shop'];
            $rootCategory->id_shop_list = [(int) $shop['id_shop']];

            foreach (\Language::getLanguages(false) as $language) {
                $rootCategory->title[$language['id_lang']] = 'Root';
                $rootCategory->content[$language['id_lang']] = 'Root';
                $rootCategory->link_rewrite[$language['id_lang']] = 'root';
            }

            $rootCategory->save();

            $unclassedCategory = new \EverPsBlogCategory();
            $unclassedCategory->id_parent_category = 0;
            $unclassedCategory->active = 1;
            $unclassedCategory->id_shop = (int) $shop['id_shop'];
            $unclassedCategory->id_shop_list = [(int) $shop['id_shop']];

            foreach (\Language::getLanguages(false) as $language) {
                $unclassedCategory->title[$language['id_lang']] = $module->l('Unclassed');
                $unclassedCategory->content[$language['id_lang']] = '';
                $unclassedCategory->link_rewrite[$language['id_lang']] = $module->l('Unclassed');
            }

            $unclassedCategory->save();
            \Configuration::updateValue('EVERBLOG_UNCLASSED_ID', (int) $unclassedCategory->id);
        }

        return true;
    }

    /**
     * @param object $module Legacy module to translate strings with l().
     */
    public function recreateUnclassedCategory($module, $shopId, $rootCategoryId)
    {
        $unclassedCategory = new \EverPsBlogCategory();
        $unclassedCategory->id_parent_category = (int) $rootCategoryId;
        $unclassedCategory->active = 1;
        $unclassedCategory->id_shop = (int) $shopId;
        $unclassedCategory->id_shop_list = [(int) $shopId];

        foreach (\Language::getLanguages(false) as $language) {
            $unclassedCategory->title[$language['id_lang']] = $module->l('Unclassed');
            $unclassedCategory->content[$language['id_lang']] = '';
            $unclassedCategory->link_rewrite[$language['id_lang']] = $module->l('Unclassed');
        }

        $unclassedCategory->save();
        \Configuration::updateValue('EVERBLOG_UNCLASSED_ID', (int) $unclassedCategory->id);

        return $unclassedCategory;
    }
}
