<?php

namespace PrestaShop\Module\Everpsblog\Service;

use PrestaShop\Module\Everpsblog\Model\BlogImageModel;
use Psr\Cache\CacheItemPoolInterface;

class BlogImageService
{
    private $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    private function cacheKey($suffix)
    {
        return 'everpsblog.image.' . md5($suffix);
    }

    public function clearCache()
    {
        $this->cache->clear();
    }

    public function getImageTypes()
    {
        return ['post', 'post_banner', 'category', 'category_banner', 'tag', 'tag_banner', 'author', 'author_banner'];
    }

    public function createImageModel()
    {
        return new BlogImageModel();
    }

    public function getAllBlogImages()
    {
        $key = $this->cacheKey('all');
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $item->get();
        }

        $sql = new \DbQuery();
        $sql->select('id_ever_image');
        $sql->from('ever_blog_image');
        $images = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) ?: [];
        $result = [];
        foreach ($images as $image) {
            $result[] = new BlogImageModel((int) $image['id_ever_image']);
        }
        $item->set($result);
        $this->cache->save($item);

        return $result;
    }

    public function getBlogImage($idElement, $shopId, $imageType)
    {
        $idElement = (int) $idElement;
        $shopId = (int) $shopId;
        $key = $this->cacheKey('one.' . $idElement . '.' . $shopId . '.' . $imageType);
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $item->get();
        }

        $sql = new \DbQuery();
        $sql->select('id_ever_image');
        $sql->from('ever_blog_image');
        $sql->where('id_element = ' . $idElement);
        $sql->where('id_shop = ' . $shopId);
        $sql->where('image_type = "' . pSQL($imageType) . '"');
        $idImage = (int) \Db::getInstance()->getValue($sql);

        $image = new BlogImageModel($idImage);
        $result = \Validate::isLoadedObject($image) ? $image : false;
        $item->set($result);
        $this->cache->save($item);

        return $result;
    }

    public function getBlogImageUrl($idElement, $shopId, $imageType)
    {
        $key = $this->cacheKey('url.' . (int) $idElement . '.' . (int) $shopId . '.' . $imageType);
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $item->get();
        }

        $image = $this->getBlogImage($idElement, $shopId, $imageType);
        if (!\Validate::isLoadedObject($image)) {
            $url = \Tools::getHttpHost(true) . __PS_BASE_URI__ . '/img/' . \Configuration::get('PS_LOGO');
            $item->set($url);
            $this->cache->save($item);

            return $url;
        }

        $url = \Tools::getHttpHost(true) . __PS_BASE_URI__ . ltrim((string) $image->image_link, '/');
        $item->set($url);
        $this->cache->save($item);

        return $url;
    }

    public function getBlogThumbUrl($idElement, $shopId, $imageType, $width = 320, $height = 180)
    {
        $idElement = (int) $idElement;
        $width = (int) $width;
        $height = (int) $height;
        $key = $this->cacheKey('thumb.' . $idElement . '.' . (int) $shopId . '.' . $imageType . '.' . $width . 'x' . $height);
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $item->get();
        }

        $extension = 'jpg';
        $original = _PS_IMG_DIR_ . $imageType . '/' . $idElement . '.' . $extension;
        if (!file_exists($original)) {
            $image = $this->getBlogImage($idElement, $shopId, $imageType);
            if (\Validate::isLoadedObject($image)) {
                $source = _PS_ROOT_DIR_ . '/' . ltrim($image->image_link, '/');
                if (file_exists($source)) {
                    $extension = pathinfo($source, PATHINFO_EXTENSION);
                    $original = _PS_IMG_DIR_ . $imageType . '/' . $idElement . '.' . $extension;
                    if (!file_exists($original)) {
                        \Tools::copy($source, $original);
                    }
                }
            }
        }

        if (!file_exists($original)) {
            $url = $this->getBlogImageUrl($idElement, $shopId, $imageType);
            $item->set($url);
            $this->cache->save($item);

            return $url;
        }

        $thumbDir = _PS_IMG_DIR_ . $imageType . '/thumbs/';
        if (!file_exists($thumbDir)) {
            @mkdir($thumbDir, 0755, true);
        }
        $thumbFile = $thumbDir . $idElement . '-' . $width . 'x' . $height . '.' . $extension;
        if (!file_exists($thumbFile)) {
            \ImageManager::resize($original, $thumbFile, $width, $height);
        }

        $url = \Tools::getHttpHost(true) . __PS_BASE_URI__ . 'img/' . $imageType . '/thumbs/' . $idElement . '-' . $width . 'x' . $height . '.' . $extension;
        $item->set($url);
        $this->cache->save($item);

        return $url;
    }

    public function oldBlogFileExist($idElement, $imageType)
    {
        $file = _PS_MODULE_DIR_ . 'everpsblog/views/img/posts/post_image_' . (int) $idElement . '.jpg';
        return file_exists($file);
    }

    public function migrateImagesByType($table, $idColumn, $imageType, $prefix, $shopId)
    {
        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from($table);
        $rows = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) ?: [];
        $result = true;
        foreach ($rows as $row) {
            $id = (int) $row[$idColumn];
            $exists = $this->getBlogImage($id, (int) $shopId, $imageType);
            if (!\Validate::isLoadedObject($exists) && $this->oldBlogFileExist($id, $imageType)) {
                $featuredImage = new BlogImageModel();
                $featuredImage->id_element = $id;
                $featuredImage->image_type = $imageType;
                $featuredImage->image_link = $prefix . $id . '.jpg';
                $featuredImage->id_shop = (int) $shopId;
                $result = (bool) $featuredImage->save() && $result;
            }
        }

        return $result;
    }
}
