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
        PostDuplicator $postDuplicator
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
        $form = $this->createForm(PostType::class, $this->formDataProvider->getData($postId), [
            'method' => Request::METHOD_POST,
            'action' => $isEdit
                ? $this->generateUrl('everpsblog_admin_post_edit', ['postId' => $postId])
                : $this->generateUrl('everpsblog_admin_post_form'),
            'featured_image_help' => $featuredImageHelp,
            'has_featured_image' => $hasFeaturedImage,
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
                    $this->handleFeaturedImageUpload($publicationForm->get('featured_image_file')->getData(), (int) $savedPostId);
                    $submitAction = (string) $request->request->get('_submit_action', 'save');

                    $this->addFlash('success', $isEdit ? 'Article mis à jour.' : 'Article créé.');

                    if ('save_and_stay' === $submitAction) {
                        return $this->redirectToRoute('everpsblog_admin_post_edit', ['postId' => $savedPostId]);
                    }

                    return $this->redirectToRoute('everpsblog_admin_post');
                } catch (\Throwable $exception) {
                    $debug = (string) $this->describeException($exception);
                    $message = sprintf('Impossible d\'enregistrer l\'article : %s', $debug);
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
            'navigationLinks' => $this->getAdminNavigationLinks(),
            'qcdPageBuilderTargets' => $this->buildQcdPageBuilderTargets('everpsblog_post', $postId, [
                'content' => 'Editer le contenu avec Page Builder',
            ]),
        ]);
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_post_create');

        $command = $this->commandAssembler->assembleCreate($request->request->all());
        $postId = $this->commandBus->handle($command);

        return new JsonResponse(['id_ever_post' => $postId], JsonResponse::HTTP_CREATED);
    }

    public function updateAction(int $postId, Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_post_update_' . $postId);

        $command = $this->commandAssembler->assembleUpdate($postId, $request->request->all());

        $updatedPostId = $this->commandBus->handle($command);

        return new JsonResponse(['id_ever_post' => $updatedPostId], JsonResponse::HTTP_OK);
    }

    public function deleteAction(int $postId, Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_post_delete_' . $postId);

        $this->commandBus->handle(new DeletePostCommand($postId));
        $this->sensitiveActionLogger->log('bo_post_delete', ['post_id' => $postId]);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Duplique un article depuis la liste BO et redirige vers le formulaire d'édition de la copie.
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
            $this->addFlash('success', sprintf('Article #%d dupliqué avec succès (copie #%d).', $postId, $newPostId));

            return $this->redirectToRoute('everpsblog_admin_post_edit', ['postId' => $newPostId]);
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog(
                '[everpsblog][PostController::duplicateAction] ' . $exception->getMessage()
                    . ' @ ' . $exception->getFile() . ':' . $exception->getLine(),
                3
            );
            $this->addFlash('error', sprintf('Impossible de dupliquer l\'article #%d : %s', $postId, $exception->getMessage()));

            return $this->redirectToRoute('everpsblog_admin_post');
        }
    }

    /**
     * Dispatcher des actions groupées du grid Articles (delete / publishall / duplicate).
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
            $this->addFlash('warning', 'Veuillez sélectionner au moins un article.');

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
                $this->addFlash('error', 'Action groupée inconnue.');

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
            $this->addFlash('success', sprintf('%d article(s) dupliqué(s).', $success));
        }
        if (!empty($failures)) {
            $this->addFlash('error', sprintf('Échec de duplication pour : #%s.', implode(', #', $failures)));
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
            $this->addFlash('success', sprintf('%d article(s) supprimé(s).', $success));
        }
        if (!empty($failures)) {
            $this->addFlash('error', sprintf('Échec de suppression pour : #%s.', implode(', #', $failures)));
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
            $this->addFlash('success', sprintf('%d article(s) publié(s).', $success));
        } else {
            $this->addFlash('error', 'Aucun article publié.');
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
        if (!$uploadedImage instanceof UploadedFile) {
            return;
        }

        $shopId = $this->getContextShopId();
        $extension = strtolower((string) ($uploadedImage->guessExtension() ?: $uploadedImage->getClientOriginalExtension() ?: 'jpg'));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            throw new \RuntimeException('Format d\'image non pris en charge.');
        }

        $targetDirectory = rtrim(_PS_IMG_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'post';
        if (!is_dir($targetDirectory) && !@mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
            throw new \RuntimeException('Impossible de créer le dossier de destination des images.');
        }

        $this->deleteFeaturedImageFiles($postId);

        $targetFileName = sprintf('%d.%s', $postId, $extension);
        $this->imageUploader->upload($uploadedImage, $targetDirectory, $targetFileName);

        $image = $this->blogImageService->getBlogImage($postId, $shopId, 'post');
        if (!\Validate::isLoadedObject($image)) {
            $image = $this->blogImageService->createImageModel();
        }

        $image->id_element = $postId;
        $image->id_shop = $shopId;
        $image->image_type = 'post';
        $image->image_link = 'img/post/' . $targetFileName;
        if (!(bool) $image->save()) {
            throw new \RuntimeException('Impossible d\'enregistrer la référence de l\'image.');
        }

        $this->blogImageService->clearCache();
    }

    private function deleteFeaturedImage(int $postId): void
    {
        $shopId = $this->getContextShopId();
        $image = $this->blogImageService->getBlogImage($postId, $shopId, 'post');

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

        $this->deleteFeaturedImageFiles($postId);
        $this->blogImageService->clearCache();
    }

    private function deleteFeaturedImageFiles(int $postId): void
    {
        $targetDirectory = rtrim(_PS_IMG_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'post';
        if (!is_dir($targetDirectory)) {
            return;
        }

        foreach ((array) glob($targetDirectory . DIRECTORY_SEPARATOR . $postId . '.*') as $existingFile) {
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
        $shopId = $this->getContextShopId();
        $image = $this->blogImageService->getBlogImage($postId, $shopId, 'post');

        return \Validate::isLoadedObject($image);
    }

    private function buildFeaturedImageHelp(int $postId): string
    {
        $shopId = $this->getContextShopId();
        $image = $this->blogImageService->getBlogImage($postId, $shopId, 'post');
        if (!\Validate::isLoadedObject($image)) {
            return '';
        }
        $url = (string) $this->blogImageService->getBlogImageUrl($postId, $shopId, 'post');
        if ('' === $url) {
            return '';
        }

        return sprintf(
            'Image actuelle : <a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
            htmlspecialchars($url, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($url, ENT_QUOTES, 'UTF-8')
        );
    }

}
