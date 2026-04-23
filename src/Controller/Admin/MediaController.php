<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Service\ContextStateService;
use PrestaShop\Module\Everpsblog\Service\ImageUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MediaController extends AbstractDomainController
{
    /** @var ImageUploader */
    private $imageUploader;

    public function __construct(ContextStateService $contextStateService, ImageUploader $imageUploader)
    {
        parent::__construct($contextStateService);
        $this->imageUploader = $imageUploader;
    }

    public function tinymceUploadAction(Request $request): JsonResponse
    {
        if (!$this->isValidUploadToken($request)) {
            return new JsonResponse(['error' => $this->transAdmin('Invalid security token.')], JsonResponse::HTTP_FORBIDDEN);
        }

        $uploadedImage = $request->files->get('file') ?: $request->files->get('image');
        if (!$uploadedImage instanceof UploadedFile || !$uploadedImage->isValid()) {
            return new JsonResponse(['error' => $this->transAdmin('Please select a valid image file.')], JsonResponse::HTTP_BAD_REQUEST);
        }

        $extension = strtolower((string) ($uploadedImage->guessExtension() ?: $uploadedImage->getClientOriginalExtension() ?: 'jpg'));
        if ('jpeg' === $extension) {
            $extension = 'jpg';
        }
        if (!in_array($extension, ['jpg', 'png', 'gif', 'webp'], true)) {
            return new JsonResponse(['error' => $this->transAdmin('Unsupported image format.')], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $targetDirectory = rtrim(_PS_IMG_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'everpsblog' . DIRECTORY_SEPARATOR . 'tinymce';
            $targetFileName = sprintf('%s-%s.%s', date('YmdHis'), bin2hex(random_bytes(8)), $extension);
            $storedPath = $this->imageUploader->upload($uploadedImage, $targetDirectory, $targetFileName);
            $storedFileName = basename($storedPath);
            $baseUrl = rtrim(\Tools::getHttpHost(true) . __PS_BASE_URI__, '/');

            return new JsonResponse([
                'location' => $baseUrl . '/img/everpsblog/tinymce/' . rawurlencode($storedFileName),
            ]);
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog(
                '[everpsblog][MediaController::tinymceUploadAction] ' . $exception->getMessage()
                    . ' @ ' . $exception->getFile() . ':' . $exception->getLine(),
                3
            );

            return new JsonResponse(['error' => $this->transAdmin('Unable to upload the image.')], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function isValidUploadToken(Request $request): bool
    {
        $token = (string) ($request->request->get('_token') ?: $request->headers->get('X-CSRF-TOKEN'));
        if ('' !== $token && $this->isCsrfTokenValid('everpsblog_tinymce_upload', $token)) {
            return true;
        }

        $legacyToken = (string) $request->request->get('_legacy_token');
        if ('' === $legacyToken) {
            return false;
        }

        return hash_equals((string) \Tools::getAdminTokenLite('AdminEverPsBlog'), $legacyToken);
    }
}
