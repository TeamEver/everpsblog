<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

if (!defined('_PS_VERSION_')) {
    exit;
}


class ImageUploader
{
    public function upload(UploadedFile $file, string $targetDirectory, string $targetFileName): string
    {
        if (!is_dir($targetDirectory) && !@mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
            throw new \RuntimeException('Unable to create the image destination directory.');
        }

        $webpPath = $this->convertUploadedFileToWebp($file, $targetDirectory, $targetFileName);
        if (null !== $webpPath) {
            return $webpPath;
        }

        $file->move($targetDirectory, $targetFileName);

        return $targetDirectory . DIRECTORY_SEPARATOR . $targetFileName;
    }

    private function convertUploadedFileToWebp(UploadedFile $file, string $targetDirectory, string $targetFileName): ?string
    {
        if (!function_exists('imagewebp')) {
            return null;
        }

        $sourcePath = (string) $file->getRealPath();
        if ('' === $sourcePath || !is_file($sourcePath)) {
            return null;
        }

        $mimeType = (string) ($file->getMimeType() ?: '');
        $image = $this->createImageResource($sourcePath, $mimeType);
        if (!$image) {
            return null;
        }

        $targetBaseName = pathinfo($targetFileName, PATHINFO_FILENAME);
        $targetPath = rtrim($targetDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $targetBaseName . '.webp';

        $saved = imagewebp($image, $targetPath, 82);
        imagedestroy($image);

        if (!$saved || !is_file($targetPath)) {
            @unlink($targetPath);

            return null;
        }

        return $targetPath;
    }

    /**
     * @return \GdImage|false|null
     */
    private function createImageResource(string $sourcePath, string $mimeType)
    {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/pjpeg':
                return function_exists('imagecreatefromjpeg') ? imagecreatefromjpeg($sourcePath) : null;
            case 'image/png':
                if (!function_exists('imagecreatefrompng')) {
                    return null;
                }

                $image = imagecreatefrompng($sourcePath);
                if ($image) {
                    if (function_exists('imagepalettetotruecolor')) {
                        imagepalettetotruecolor($image);
                    }
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                }

                return $image;
        }

        return null;
    }
}
