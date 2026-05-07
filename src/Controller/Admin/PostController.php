<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Application\Blog\PostCommandAssembler;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeletePostCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandBus\CommandBusInterface;
use PrestaShop\Module\Everpsblog\Form\DataProvider\PostFormDataProvider;
use PrestaShop\Module\Everpsblog\Form\Type\Admin\PostType;
use PrestaShop\Module\Everpsblog\Grid\Data\PostGridDataFactory;
use PrestaShop\Module\Everpsblog\Grid\Definition\PostGridDefinitionFactory;
use PrestaShop\Module\Everpsblog\Service\Audit\SensitiveActionLogger;
use PrestaShop\Module\Everpsblog\Service\BlogImageService;
use PrestaShop\Module\Everpsblog\Service\BlogSitemapService;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;
use PrestaShop\Module\Everpsblog\Service\ImageUploader;
use PrestaShop\Module\Everpsblog\Service\PostDuplicator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostController extends AbstractDomainController
{
    private $commandBus;
    private $commandAssembler;
    private $definitionFactory;
    private $dataFactory;
    private $formDataProvider;
    private $sensitiveActionLogger;
    private $blogImageService;
    private $imageUploader;
    private $postDuplicator;
    private $blogSitemapService;
    private $cacheInvalidator;

    public function __construct(
        \PrestaShop\Module\Everpsblog\Service\ContextStateService $contextStateService,
        CommandBusInterface $commandBus,
        PostCommandAssembler $commandAssembler,
        PostGridDefinitionFactory $definitionFactory,
        PostGridDataFactory $dataFactory,
        PostFormDataProvider $formDataProvider,
        SensitiveActionLogger $sensitiveActionLogger,
        BlogImageService $blogImageService,
        ImageUploader $imageUploader,
        PostDuplicator $postDuplicator,
        BlogSitemapService $blogSitemapService,
        ?BlogFrontCacheInvalidator $cacheInvalidator = null
    ) {
        parent::__construct($contextStateService);
        $this->commandBus = $commandBus;
        $this->commandAssembler = $commandAssembler;
        $this->definitionFactory = $definitionFactory;
        $this->dataFactory = $dataFactory;
        $this->formDataProvider = $formDataProvider;
        $this->sensitiveActionLogger = $sensitiveActionLogger;
        $this->blogImageService = $blogImageService;
        $this->imageUploader = $imageUploader;
        $this->postDuplicator = $postDuplicator;
        $this->blogSitemapService = $blogSitemapService;
        $this->cacheInvalidator = $cacheInvalidator ?: new BlogFrontCacheInvalidator();
    }

    public function indexAction(Request $request): Response
    {
        $definition = $this->definitionFactory->build();
        $data = $this->dataFactory->build($this->getContextShopId(), $this->getContextLangId(), $request->query->all());

        return $this->render('@Modules/everpsblog/views/templates/admin/modern/resource.html.twig', [
            'definition' => $definition,
            'data' => $data,
            'resource' => 'post',
            'currentResource' => 'post',
            'createUrl' => $this->generateUrl('everpsblog_admin_post_form'),
            'bulkActionUrl' => $this->generateUrl('everpsblog_admin_post_bulk'),
            'bulkCsrfTokenId' => 'everpsblog_post_bulk',
            'navigationLinks' => $this->getAdminNavigationLinks(),
        ]);
    }

    public function formAction(Request $request, ?int $postId = null): Response
    {
        $isEdit = null !== $postId;
        $csrfTokenId = $isEdit ? 'everpsblog_post_update_' . $postId : 'everpsblog_post_create';
        $featuredImageHelp = $isEdit ? $this->buildFeaturedImageHelp((int) $postId) : '';
        $hasFeaturedImage = $isEdit ? $this->hasFeaturedImage((int) $postId) : false;
        $bannerImageHelp = $isEdit ? $this->buildBannerImageHelp((int) $postId) : '';
        $hasBannerImage = $isEdit ? $this->hasBannerImage((int) $postId) : false;
        $form = $this->createForm(PostType::class, $this->formDataProvider->getData($postId), [
            'method' => Request::METHOD_POST,
            'action' => $isEdit
                ? $this->generateUrl('everpsblog_admin_post_edit', ['postId' => $postId])
                : $this->generateUrl('everpsblog_admin_post_form'),
            'featured_image_help' => $featuredImageHelp,
            'has_featured_image' => $hasFeaturedImage,
            'banner_image_help' => $bannerImageHelp,
            'has_banner_image' => $hasBannerImage,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->validateCsrfToken($request, $csrfTokenId);

            if ($form->isValid()) {
                $postData = (array) $form->getData();

                try {
                    $savedPostId = $isEdit
                        ? $this->commandBus->handle($this->commandAssembler->assembleUpdate((int) $postId, $postData))
                        : $this->commandBus->handle($this->commandAssembler->assembleCreate($postData));
                    $publicationForm = $form->get('publication_tab');
                    if ((bool) $publicationForm->get('delete_featured_image')->getData()) {
                        $this->deleteFeaturedImage((int) $savedPostId);
                    }
                    if ((bool) $publicationForm->get('delete_banner_image')->getData()) {
                        $this->deleteBannerImage((int) $savedPostId);
                    }
                    $this->handleFeaturedImageUpload($publicationForm->get('featured_image_file')->getData(), (int) $savedPostId);
                    $this->handleBannerImageUpload($publicationForm->get('banner_image_file')->getData(), (int) $savedPostId);
                    $this->refreshSitemapsAfterBackOfficeChange($this->blogSitemapService);
                    $submitAction = (string) $request->request->get('_submit_action', 'save');

                    $this->addFlash('success', $isEdit ? $this->transAdmin('Post updated.') : $this->transAdmin('Post created.'));

                    if ('save_and_stay' === $submitAction) {
                        return $this->redirectToRoute('everpsblog_admin_post_edit', ['postId' => $savedPostId]);
                    }

                    return $this->redirectToRoute('everpsblog_admin_post');
                } catch (\Throwable $exception) {
                    $debug = (string) $this->describeException($exception);
                    $message = $this->transAdmin('Unable to save post: %error%', ['%error%' => $debug]);
                    $form->addError(new FormError($message));
                    $this->addFlash('error', $message);
                    \PrestaShopLogger::addLog(
                        '[everpsblog][PostController::formAction] ' . $exception->getMessage()
                            . ' @ ' . $exception->getFile() . ':' . $exception->getLine(),
                        3
                    );
                }
            }
        }

        return $this->render('@Modules/everpsblog/views/templates/admin/modern/form.html.twig', [
            'resource' => 'post',
            'entityId' => $postId,
            'csrfTokenId' => $csrfTokenId,
            'form' => $form->createView(),
            'currentResource' => 'post',
            'cancelUrl' => $this->generateUrl('everpsblog_admin_post'),
            'createUrl' => $this->generateUrl('everpsblog_admin_post_form'),
            'previewUrl' => $isEdit ? $this->buildPreviewUrlForResource('post', (int) $postId) : '',
            'navigationLinks' => $this->getAdminNavigationLinks(),
            'everBlogLanguages' => $this->getEverBlogLanguages(),
            'qcdPageBuilderTargets' => $this->buildQcdPageBuilderTargets('everpsblog_post', $postId, [
                'content' => $this->transAdmin('Edit content with Page Builder'),
            ]),
        ]);
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_post_create');

        $command = $this->commandAssembler->assembleCreate($request->request->all());
        $postId = $this->commandBus->handle($command);
        $sitemapsRefreshed = $this->refreshSitemapsAfterBackOfficeChange($this->blogSitemapService, false);

        return new JsonResponse(['id_ever_post' => $postId, 'sitemaps_refreshed' => $sitemapsRefreshed], JsonResponse::HTTP_CREATED);
    }

    public function updateAction(int $postId, Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_post_update_' . $postId);

        $command = $this->commandAssembler->assembleUpdate($postId, $request->request->all());

        $updatedPostId = $this->commandBus->handle($command);
        $sitemapsRefreshed = $this->refreshSitemapsAfterBackOfficeChange($this->blogSitemapService, false);

        return new JsonResponse(['id_ever_post' => $updatedPostId, 'sitemaps_refreshed' => $sitemapsRefreshed], JsonResponse::HTTP_OK);
    }

    public function deleteAction(int $postId, Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_post_delete_' . $postId);

        $this->commandBus->handle(new DeletePostCommand($postId));
        $this->sensitiveActionLogger->log('bo_post_delete', ['post_id' => $postId]);
        $this->refreshSitemapsAfterBackOfficeChange($this->blogSitemapService, false);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Duplicates a post from the back office list and redirects to the copied post form.
     */
    public function duplicateAction(int $postId, Request $request): Response
    {
        $this->validateCsrfToken($request, 'everpsblog_post_duplicate_' . $postId);

        try {
            $newPostId = $this->postDuplicator->duplicate($postId, $this->getContextShopId());
            $this->sensitiveActionLogger->log('bo_post_duplicate', [
                'source_post_id' => $postId,
                'new_post_id' => $newPostId,
            ]);
            $this->refreshSitemapsAfterBackOfficeChange($this->blogSitemapService);
            $this->addFlash(
                'success',
                $this->transAdmin(
                    'Post #%post_id% duplicated successfully (copy #%copy_id%).',
                    ['%post_id%' => $postId, '%copy_id%' => $newPostId]
                )
            );

            return $this->redirectToRoute('everpsblog_admin_post_edit', ['postId' => $newPostId]);
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog(
                '[everpsblog][PostController::duplicateAction] ' . $exception->getMessage()
                    . ' @ ' . $exception->getFile() . ':' . $exception->getLine(),
                3
            );
            $this->addFlash(
                'error',
                $this->transAdmin(
                    'Unable to duplicate post #%post_id%: %error%',
                    ['%post_id%' => $postId, '%error%' => $exception->getMessage()]
                )
            );

            return $this->redirectToRoute('everpsblog_admin_post');
        }
    }

    /**
     * Dispatches post grid bulk actions (delete / publishall / duplicate).
     */
    public function bulkAction(Request $request): Response
    {
        $this->validateCsrfToken($request, 'everpsblog_post_bulk');

        $action = (string) $request->request->get('bulk_action', '');
        $rawIds = (array) $request->request->get('bulk_ids', []);
        $ids = [];
        foreach ($rawIds as $rawId) {
            $id = (int) $rawId;
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        $ids = array_values(array_unique($ids));

        if (empty($ids)) {
            $this->addFlash('warning', $this->transAdmin('Please select at least one post.'));

            return $this->redirectToRoute('everpsblog_admin_post');
        }

        switch ($action) {
            case 'duplicate':
                return $this->handleBulkDuplicate($ids);
            case 'delete':
                return $this->handleBulkDelete($ids);
            case 'publishall':
                return $this->handleBulkPublish($ids);
            default:
                $this->addFlash('error', $this->transAdmin('Unknown bulk action.'));

                return $this->redirectToRoute('everpsblog_admin_post');
        }
    }

    private function handleBulkDuplicate(array $ids): Response
    {
        $shopId = $this->getContextShopId();
        $success = 0;
        $failures = [];

        foreach ($ids as $postId) {
            try {
                $newPostId = $this->postDuplicator->duplicate((int) $postId, $shopId);
                $this->sensitiveActionLogger->log('bo_post_duplicate', [
                    'source_post_id' => (int) $postId,
                    'new_post_id' => $newPostId,
                    'bulk' => true,
                ]);
                ++$success;
            } catch (\Throwable $exception) {
                $failures[] = (int) $postId;
                \PrestaShopLogger::addLog(
                    '[everpsblog][PostController::bulkDuplicate] #' . (int) $postId . ' ' . $exception->getMessage()
                        . ' @ ' . $exception->getFile() . ':' . $exception->getLine(),
                    3
                );
            }
        }

        if ($success > 0) {
            $this->refreshSitemapsAfterBackOfficeChange($this->blogSitemapService);
            $this->addFlash('success', $this->transAdmin('%count% post(s) duplicated.', ['%count%' => $success]));
        }
        if (!empty($failures)) {
            $this->addFlash('error', $this->transAdmin('Duplication failed for: #%ids%.', ['%ids%' => implode(', #', $failures)]));
        }

        return $this->redirectToRoute('everpsblog_admin_post');
    }

    private function handleBulkDelete(array $ids): Response
    {
        $success = 0;
        $failures = [];

        foreach ($ids as $postId) {
            try {
                $this->commandBus->handle(new DeletePostCommand((int) $postId));
                $this->sensitiveActionLogger->log('bo_post_delete', [
                    'post_id' => (int) $postId,
                    'bulk' => true,
                ]);
                ++$success;
            } catch (\Throwable $exception) {
                $failures[] = (int) $postId;
                \PrestaShopLogger::addLog(
                    '[everpsblog][PostController::bulkDelete] #' . (int) $postId . ' ' . $exception->getMessage(),
                    3
                );
            }
        }

        if ($success > 0) {
            $this->refreshSitemapsAfterBackOfficeChange($this->blogSitemapService);
            $this->addFlash('success', $this->transAdmin('%count% post(s) deleted.', ['%count%' => $success]));
        }
        if (!empty($failures)) {
            $this->addFlash('error', $this->transAdmin('Deletion failed for: #%ids%.', ['%ids%' => implode(', #', $failures)]));
        }

        return $this->redirectToRoute('everpsblog_admin_post');
    }

    private function handleBulkPublish(array $ids): Response
    {
        $now = date('Y-m-d H:i:s');
        $escapedIds = array_map('intval', $ids);
        $inClause = implode(',', $escapedIds);
        $success = 0;

        try {
            $success = (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'ever_blog_post`
                 WHERE `id_ever_post` IN (' . $inClause . ')
                 AND `id_shop` = ' . (int) $this->getContextShopId()
            );

            \Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'ever_blog_post`
                 SET `post_status` = \'published\', `active` = 1, `date_upd` = \'' . pSQL($now) . '\'
                 WHERE `id_ever_post` IN (' . $inClause . ')
                 AND `id_shop` = ' . (int) $this->getContextShopId()
            );

            foreach ($escapedIds as $postId) {
                $this->sensitiveActionLogger->log('bo_post_publish', [
                    'post_id' => $postId,
                    'bulk' => true,
                ]);
            }
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog(
                '[everpsblog][PostController::bulkPublish] ' . $exception->getMessage(),
                3
            );
        }

        if ($success > 0) {
            $this->refreshSitemapsAfterBackOfficeChange($this->blogSitemapService);
            $this->addFlash('success', $this->transAdmin('%count% post(s) published.', ['%count%' => $success]));
        } else {
            $this->addFlash('error', $this->transAdmin('No post was published.'));
        }

        return $this->redirectToRoute('everpsblog_admin_post');
    }

    private function validateCsrfToken(Request $request, string $tokenId): void
    {
        $token = (string) (
            $request->request->get('_csrf_token')
            ?: $request->request->get('_token')
            ?: $request->headers->get('X-CSRF-TOKEN')
        );

        if (!$this->isCsrfTokenValid($tokenId, $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }

    private function handleFeaturedImageUpload($uploadedImage, int $postId): void
    {
        $this->handleBlogImageUpload($uploadedImage, $postId, 'post');
    }

    private function handleBannerImageUpload($uploadedImage, int $postId): void
    {
        $this->handleBlogImageUpload($uploadedImage, $postId, 'post_banner');
    }

    private function handleBlogImageUpload($uploadedImage, int $postId, string $imageType): void
    {
        if (!$uploadedImage instanceof UploadedFile) {
            return;
        }

        $shopId = $this->getContextShopId();
        $extension = strtolower((string) ($uploadedImage->guessExtension() ?: $uploadedImage->getClientOriginalExtension() ?: 'jpg'));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            throw new \RuntimeException($this->transAdmin('Unsupported image format.'));
        }
        if ('jpeg' === $extension) {
            $extension = 'jpg';
        }

        $targetDirectory = rtrim(_PS_IMG_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $imageType;
        if (!is_dir($targetDirectory) && !@mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
            throw new \RuntimeException($this->transAdmin('Unable to create the image destination directory.'));
        }

        $this->deleteBlogImageFiles($postId, $imageType);

        $targetFileName = sprintf('%d.%s', $postId, $extension);
        $storedPath = $this->imageUploader->upload($uploadedImage, $targetDirectory, $targetFileName);
        $storedFileName = basename($storedPath);

        $image = $this->blogImageService->getBlogImage($postId, $shopId, $imageType);
        if (!\Validate::isLoadedObject($image)) {
            $image = $this->blogImageService->createImageModel();
        }

        $image->id_element = $postId;
        $image->id_shop = $shopId;
        $image->image_type = $imageType;
        $image->image_link = 'img/' . $imageType . '/' . $storedFileName;
        if (!(bool) $image->save()) {
            throw new \RuntimeException($this->transAdmin('Unable to save the image reference.'));
        }

        $this->blogImageService->clearCache();
        $this->cacheInvalidator->invalidateImageMutation($postId, $imageType);
    }

    private function deleteFeaturedImage(int $postId): void
    {
        $this->deleteBlogImage($postId, 'post');
    }

    private function deleteBannerImage(int $postId): void
    {
        $this->deleteBlogImage($postId, 'post_banner');
    }

    private function deleteBlogImage(int $postId, string $imageType): void
    {
        $shopId = $this->getContextShopId();
        $image = $this->blogImageService->getBlogImage($postId, $shopId, $imageType);

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

        $this->deleteBlogImageFiles($postId, $imageType);
        $this->blogImageService->clearCache();
        $this->cacheInvalidator->invalidateImageMutation($postId, $imageType);
    }

    private function deleteBlogImageFiles(int $postId, string $imageType): void
    {
        $targetDirectory = rtrim(_PS_IMG_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $imageType;
        if (!is_dir($targetDirectory)) {
            return;
        }

        foreach ((array) glob($targetDirectory . DIRECTORY_SEPARATOR . $postId . '.*') as $existingFile) {
            @unlink($existingFile);
        }

        $thumbDirectory = $targetDirectory . DIRECTORY_SEPARATOR . 'thumbs';
        if (!is_dir($thumbDirectory)) {
            return;
        }

        foreach ((array) glob($thumbDirectory . DIRECTORY_SEPARATOR . $postId . '-*') as $existingFile) {
            @unlink($existingFile);
        }
    }

    private function deleteReferencedImageFile(string $imageLink): void
    {
        $imageLink = trim($imageLink);
        if ('' === $imageLink || 0 === strpos($imageLink, 'http://') || 0 === strpos($imageLink, 'https://')) {
            return;
        }

        $relative = ltrim($imageLink, '/\\');
        $candidates = [
            rtrim(_PS_ROOT_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative),
            rtrim(_PS_IMG_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, preg_replace('#^img/#', '', $relative)),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                @unlink($candidate);
            }
        }
    }

    private function hasFeaturedImage(int $postId): bool
    {
        return $this->hasBlogImage($postId, 'post');
    }

    private function hasBannerImage(int $postId): bool
    {
        return $this->hasBlogImage($postId, 'post_banner');
    }

    private function hasBlogImage(int $postId, string $imageType): bool
    {
        $shopId = $this->getContextShopId();
        $image = $this->blogImageService->getBlogImage($postId, $shopId, $imageType);

        return \Validate::isLoadedObject($image);
    }

    private function buildFeaturedImageHelp(int $postId): string
    {
        return $this->buildImageHelp($postId, 'post', $this->transAdmin('Current image'));
    }

    private function buildBannerImageHelp(int $postId): string
    {
        return $this->buildImageHelp($postId, 'post_banner', $this->transAdmin('Current banner image'));
    }

    private function buildImageHelp(int $postId, string $imageType, string $label): string
    {
        $shopId = $this->getContextShopId();
        $image = $this->blogImageService->getBlogImage($postId, $shopId, $imageType);
        if (!\Validate::isLoadedObject($image)) {
            return '';
        }
        $url = (string) $this->blogImageService->getBlogImageUrl($postId, $shopId, $imageType);
        if ('' === $url) {
            return '';
        }
        $previewUrl = $this->appendTimestampToUrl($url);

        return sprintf(
            '<span class="ever-featured-image-preview"><img src="%1$s" data-ever-preview-src="%1$s" alt="%2$s" loading="lazy"><span>%2$s: <button type="button" class="btn btn-link p-0 ever-image-preview-trigger" data-ever-preview-src="%1$s" data-ever-preview-alt="%2$s">%3$s</button></span></span>',
            htmlspecialchars($previewUrl, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($this->transAdmin('Open preview'), ENT_QUOTES, 'UTF-8')
        );
    }

    private function appendTimestampToUrl(string $url): string
    {
        $separator = false === strpos($url, '?') ? '?' : '&';

        return $url . $separator . 't=' . time();
    }

}
