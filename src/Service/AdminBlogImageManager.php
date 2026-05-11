<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Service;

use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

if (!defined('_PS_VERSION_')) {
    exit;
}


class AdminBlogImageManager
{
    /** @var BlogImageService */
    private $blogImageService;

    /** @var ImageUploader */
    private $imageUploader;
    /** @var BlogFrontCacheInvalidator */
    private $cacheInvalidator;

    public function __construct(BlogImageService $blogImageService, ImageUploader $imageUploader, ?BlogFrontCacheInvalidator $cacheInvalidator = null)
    {
        $this->blogImageService = $blogImageService;
        $this->imageUploader = $imageUploader;
        $this->cacheInvalidator = $cacheInvalidator ?: new BlogFrontCacheInvalidator();
    }

    public function upload($uploadedImage, int $elementId, int $shopId, string $imageType): void
    {
        if (!$uploadedImage instanceof UploadedFile) {
            return;
        }

        $extension = strtolower((string) ($uploadedImage->guessExtension() ?: $uploadedImage->getClientOriginalExtension() ?: 'jpg'));
        if ('jpeg' === $extension) {
            $extension = 'jpg';
        }
        if (!in_array($extension, ['jpg', 'png', 'gif', 'webp'], true)) {
            throw new \RuntimeException('Unsupported image format.');
        }

        $targetDirectory = rtrim(_PS_IMG_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $imageType;
        if (!is_dir($targetDirectory) && !@mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
            throw new \RuntimeException('Unable to create the image destination directory.');
        }

        $this->deleteFiles($elementId, $imageType);

        $targetFileName = sprintf('%d.%s', $elementId, $extension);
        $storedPath = $this->imageUploader->upload($uploadedImage, $targetDirectory, $targetFileName);
        $storedFileName = basename($storedPath);

        $image = $this->blogImageService->getBlogImage($elementId, $shopId, $imageType);
        if (!\Validate::isLoadedObject($image)) {
            $image = $this->blogImageService->createImageModel();
        }

        $image->id_element = $elementId;
        $image->id_shop = $shopId;
        $image->image_type = $imageType;
        $image->image_link = 'img/' . $imageType . '/' . $storedFileName;
        if (!(bool) $image->save()) {
            throw new \RuntimeException('Unable to save the image reference.');
        }

        $this->blogImageService->clearCache();
        $this->cacheInvalidator->invalidateImageMutation($elementId, $imageType);
    }

    public function delete(int $elementId, int $shopId, string $imageType): void
    {
        $image = $this->blogImageService->getBlogImage($elementId, $shopId, $imageType);

        if (\Validate::isLoadedObject($image)) {
            $this->deleteReferencedImageFile((string) $image->image_link);
            $imageId = (int) ($image->id ?: ($image->id_ever_image ?? 0));
            if ($imageId > 0) {
                \Db::getInstance()->delete(
                    'ever_blog_image_shop',
                    'id_ever_image = ' . $imageId . ' AND id_shop = ' . (int) $shopId
                );
                \Db::getInstance()->delete('ever_blog_image', 'id_ever_image = ' . $imageId);
            }
        }

        $this->deleteFiles($elementId, $imageType);
        $this->blogImageService->clearCache();
        $this->cacheInvalidator->invalidateImageMutation($elementId, $imageType);
    }

    public function hasImage(int $elementId, int $shopId, string $imageType): bool
    {
        return \Validate::isLoadedObject($this->blogImageService->getBlogImage($elementId, $shopId, $imageType));
    }

    public function getImageUrlIfExists(int $elementId, int $shopId, string $imageType): string
    {
        if (!$this->hasImage($elementId, $shopId, $imageType)) {
            return '';
        }

        return (string) $this->blogImageService->getBlogImageUrl($elementId, $shopId, $imageType);
    }

    public function buildImageHelp(int $elementId, int $shopId, string $imageType, string $label, string $openLabel): string
    {
        $url = $this->getImageUrlIfExists($elementId, $shopId, $imageType);
        if ('' === $url) {
            return '';
        }

        $previewUrl = $this->appendTimestampToUrl($url);

        return sprintf(
            '<span class="ever-featured-image-preview"><img src="%1$s" data-ever-preview-src="%1$s" alt="%2$s" loading="lazy"><span>%2$s: <button type="button" class="btn btn-link p-0 ever-image-preview-trigger" data-ever-preview-src="%1$s" data-ever-preview-alt="%2$s">%3$s</button></span></span>',
            htmlspecialchars($previewUrl, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($openLabel, ENT_QUOTES, 'UTF-8')
        );
    }

    private function appendTimestampToUrl(string $url): string
    {
        $separator = false === strpos($url, '?') ? '?' : '&';

        return $url . $separator . 't=' . time();
    }

    private function deleteFiles(int $elementId, string $imageType): void
    {
        $targetDirectory = rtrim(_PS_IMG_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $imageType;
        foreach ((array) glob($targetDirectory . DIRECTORY_SEPARATOR . $elementId . '.*') as $existingFile) {
            $this->deleteLocalImageFile((string) $existingFile);
        }

        $thumbDirectory = $targetDirectory . DIRECTORY_SEPARATOR . 'thumbs';
        foreach ((array) glob($thumbDirectory . DIRECTORY_SEPARATOR . $elementId . '-*') as $existingFile) {
            $this->deleteLocalImageFile((string) $existingFile);
        }
    }

    private function deleteReferencedImageFile(string $imageLink): void
    {
        $imageLink = trim($imageLink);
        if ('' === $imageLink || preg_match('/^https?:\/\//i', $imageLink)) {
            return;
        }

        $relativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($imageLink, '/\\'));
        $this->deleteLocalImageFile(rtrim(_PS_ROOT_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $relativePath);
    }

    private function deleteLocalImageFile(string $filePath): void
    {
        $realFile = realpath($filePath);
        $realImageDirectory = realpath(_PS_IMG_DIR_);

        if (!$realFile || !$realImageDirectory || !is_file($realFile)) {
            return;
        }

        $realImageDirectory = rtrim($realImageDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (0 !== strpos($realFile, $realImageDirectory)) {
            return;
        }

        @unlink($realFile);
    }
}
