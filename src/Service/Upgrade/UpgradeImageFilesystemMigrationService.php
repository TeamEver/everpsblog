<?php

namespace PrestaShop\Module\Everpsblog\Service\Upgrade;

class UpgradeImageFilesystemMigrationService
{
    /**
     * @var array<string, string>
     */
    private $legacyFolders = [
        'post' => 'posts/post_image_',
        'category' => 'categories/category_image_',
        'tag' => 'tags/tag_image_',
        'author' => 'authors/author_image_',
    ];

    public function migrate(): bool
    {
        $result = true;

        foreach (array_keys($this->legacyFolders) as $type) {
            $targetDir = _PS_IMG_DIR_ . $type;
            if (!is_dir($targetDir)) {
                $result = @mkdir($targetDir, 0755, true) && $result;
            }
        }

        $images = \Db::getInstance()->executeS(
            'SELECT id_ever_image, id_element, image_type
             FROM `' . _DB_PREFIX_ . 'ever_blog_image`'
        ) ?: [];

        foreach ($images as $image) {
            $result = $this->migrateSingleImage((int) $image['id_ever_image'], (int) $image['id_element'], (string) $image['image_type']) && $result;
        }

        foreach ($this->legacyFolders as $legacyPrefix) {
            $result = $this->cleanupLegacyFolder(_PS_MODULE_DIR_ . 'everpsblog/views/img/' . dirname($legacyPrefix) . '/') && $result;
        }

        return $result;
    }

    private function migrateSingleImage(int $idImage, int $idElement, string $imageType): bool
    {
        if (!isset($this->legacyFolders[$imageType])) {
            return true;
        }

        $legacyFile = _PS_MODULE_DIR_ . 'everpsblog/views/img/' . $this->legacyFolders[$imageType] . $idElement . '.jpg';
        $destination = _PS_IMG_DIR_ . $imageType . '/' . $idElement . '.jpg';

        if (!is_file($legacyFile)) {
            return true;
        }

        if (!is_file($destination) && !@copy($legacyFile, $destination)) {
            return false;
        }

        $newLink = 'img/' . $imageType . '/' . $idElement . '.jpg';
        $updated = \Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'ever_blog_image`
             SET image_link = "' . pSQL($newLink) . '"
             WHERE id_ever_image = ' . $idImage
        );

        if (!$updated) {
            return false;
        }

        if (is_file($legacyFile) && !@unlink($legacyFile)) {
            return false;
        }

        return true;
    }

    private function cleanupLegacyFolder(string $dir): bool
    {
        if (!is_dir($dir)) {
            return true;
        }

        $items = scandir($dir);
        if (!is_array($items)) {
            return false;
        }

        $result = true;
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . $item;
            if (is_dir($path)) {
                $result = $this->cleanupLegacyFolder($path . '/') && $result;
                if (is_dir($path)) {
                    $result = @rmdir($path) && $result;
                }

                continue;
            }

            $result = @unlink($path) && $result;
        }

        $remaining = scandir($dir);
        if (is_array($remaining) && count($remaining) === 2) {
            $result = @rmdir($dir) && $result;
        }

        return $result;
    }
}
