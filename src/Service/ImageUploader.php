<?php

namespace PrestaShop\Module\Everpsblog\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploader
{
    public function upload(UploadedFile $file, string $targetDirectory, string $targetFileName): string
    {
        $file->move($targetDirectory, $targetFileName);

        return $targetDirectory . DIRECTORY_SEPARATOR . $targetFileName;
    }
}
