<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Application\Blog\AuthorCommandAssembler;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeleteAuthorCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandBus\CommandBusInterface;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\AuthorWriteRepository;
use PrestaShop\Module\Everpsblog\Form\DataProvider\AuthorFormDataProvider;
use PrestaShop\Module\Everpsblog\Form\Type\Admin\AuthorType;
use PrestaShop\Module\Everpsblog\Grid\Data\AuthorGridDataFactory;
use PrestaShop\Module\Everpsblog\Grid\Definition\AuthorGridDefinitionFactory;
use PrestaShop\Module\Everpsblog\Service\BlogImageService;
use PrestaShop\Module\Everpsblog\Service\ContextStateService;
use PrestaShop\Module\Everpsblog\Service\ImageUploader;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorController extends AbstractDomainController
{
    private $commandBus;
    private $commandAssembler;
    private $definitionFactory;
    private $dataFactory;
    private $formDataProvider;
    private $authorWriteRepository;
    private $blogImageService;
    private $imageUploader;

    public function __construct(
        ContextStateService $contextStateService,
        CommandBusInterface $commandBus,
        AuthorCommandAssembler $commandAssembler,
        AuthorGridDefinitionFactory $definitionFactory,
        AuthorGridDataFactory $dataFactory,
        AuthorFormDataProvider $formDataProvider,
        AuthorWriteRepository $authorWriteRepository,
        BlogImageService $blogImageService,
        ImageUploader $imageUploader
    ) {
        parent::__construct($contextStateService);
        $this->commandBus = $commandBus;
        $this->commandAssembler = $commandAssembler;
        $this->definitionFactory = $definitionFactory;
        $this->dataFactory = $dataFactory;
        $this->formDataProvider = $formDataProvider;
        $this->authorWriteRepository = $authorWriteRepository;
        $this->blogImageService = $blogImageService;
        $this->imageUploader = $imageUploader;
    }

    public function indexAction(Request $request): Response
    {
        return $this->render('@Modules/everpsblog/views/templates/admin/modern/resource.html.twig', [
            'definition' => $this->definitionFactory->build(),
            'data' => $this->dataFactory->build($this->getContextShopId(), $this->getContextLangId(), $request->query->all()),
            'resource' => 'author',
            'currentResource' => 'author',
            'createUrl' => $this->generateUrl('everpsblog_admin_author_form'),
            'navigationLinks' => $this->getAdminNavigationLinks(),
        ]);
    }

    public function formAction(Request $request, ?int $authorId = null): Response
    {
        $isEdit = null !== $authorId;
        $csrfTokenId = $isEdit ? 'everpsblog_author_update_' . $authorId : 'everpsblog_author_create';
        $authorImageHelp = $isEdit ? $this->buildAuthorImageHelp((int) $authorId) : '';
        $hasAuthorImage = $isEdit ? $this->hasAuthorImage((int) $authorId) : false;

        $form = $this->createForm(AuthorType::class, $this->formDataProvider->getData($authorId), [
            'method' => Request::METHOD_POST,
            'action' => $isEdit
                ? $this->generateUrl('everpsblog_admin_author_edit', ['authorId' => $authorId])
                : $this->generateUrl('everpsblog_admin_author_form'),
            'author_image_help' => $authorImageHelp,
            'has_author_image' => $hasAuthorImage,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->validateCsrfToken($request, $csrfTokenId);

            if ($form->isValid()) {
                try {
                    $savedAuthorId = $isEdit
                        ? $this->commandBus->handle($this->commandAssembler->assembleUpdate((int) $authorId, (array) $form->getData()))
                        : $this->commandBus->handle($this->commandAssembler->assembleCreate((array) $form->getData()));
                    if ((bool) $form->get('delete_author_image')->getData()) {
                        $this->deleteAuthorImage((int) $savedAuthorId);
                    }
                    $this->handleAuthorImageUpload($form->get('author_image_file')->getData(), (int) $savedAuthorId);
                    $submitAction = (string) $request->request->get('_submit_action', 'save');

                    $this->addFlash('success', $isEdit ? 'Auteur mis à jour.' : 'Auteur créé.');

                    if ('save_and_stay' === $submitAction) {
                        return $this->redirectToRoute('everpsblog_admin_author_edit', ['authorId' => $savedAuthorId]);
                    }

                    return $this->redirectToRoute('everpsblog_admin_author');
                } catch (\Throwable $exception) {
                    $message = sprintf('Impossible d\'enregistrer l\'auteur : %s', $this->describeException($exception));
                    $form->addError(new FormError($message));
                    $this->addFlash('error', $message);
                    \PrestaShopLogger::addLog(
                        '[everpsblog][AuthorController::formAction] ' . $exception->getMessage()
                            . ' @ ' . $exception->getFile() . ':' . $exception->getLine(),
                        3
                    );
                }
            }
        }

        return $this->render('@Modules/everpsblog/views/templates/admin/modern/form.html.twig', [
            'resource' => 'author',
            'entityId' => $authorId,
            'csrfTokenId' => $csrfTokenId,
            'form' => $form->createView(),
            'currentResource' => 'author',
            'cancelUrl' => $this->generateUrl('everpsblog_admin_author'),
            'createUrl' => $this->generateUrl('everpsblog_admin_author_form'),
            'navigationLinks' => $this->getAdminNavigationLinks(),
            'qcdPageBuilderTargets' => $this->buildQcdPageBuilderTargets('everpsblog_author', $authorId, [
                'content' => 'Editer la biographie avec Page Builder',
                'bottom_content' => 'Editer le bas de page avec Page Builder',
            ]),
        ]);
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_author_create');

        $authorId = $this->commandBus->handle($this->commandAssembler->assembleCreate($request->request->all()));

        return new JsonResponse(['id_ever_author' => $authorId], JsonResponse::HTTP_CREATED);
    }

    public function updateAction(int $authorId, Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_author_update_' . $authorId);

        $updatedAuthorId = $this->commandBus->handle($this->commandAssembler->assembleUpdate($authorId, $request->request->all()));

        return new JsonResponse(['id_ever_author' => $updatedAuthorId], JsonResponse::HTTP_OK);
    }

    /**
     * Returns metadata the UI needs before deleting an author:
     * - number of posts attached
     * - candidate authors to reassign posts to
     */
    public function deletePreflightAction(int $authorId): JsonResponse
    {
        $postsCount = $this->authorWriteRepository->countPostsForAuthor($authorId);
        $otherAuthors = $this->authorWriteRepository->listOtherAuthors($authorId);

        return new JsonResponse([
            'author_id' => $authorId,
            'posts_count' => $postsCount,
            'reassignable' => $postsCount > 0 && !empty($otherAuthors),
            'other_authors' => $otherAuthors,
        ]);
    }

    public function deleteAction(int $authorId, Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_author_delete_' . $authorId);

        $reassignTo = $this->extractReassignAuthorId($request);

        try {
            $this->commandBus->handle(new DeleteAuthorCommand($authorId, $reassignTo));
            $this->deleteAuthorImage($authorId);
        } catch (\RuntimeException $exception) {
            // Reassignment required – surface a 409 with the candidate list so the UI can prompt.
            $otherAuthors = $this->authorWriteRepository->listOtherAuthors($authorId);

            return new JsonResponse([
                'error' => $exception->getMessage(),
                'posts_count' => $this->authorWriteRepository->countPostsForAuthor($authorId),
                'other_authors' => $otherAuthors,
            ], JsonResponse::HTTP_CONFLICT);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    private function handleAuthorImageUpload($uploadedImage, int $authorId): void
    {
        if (!$uploadedImage instanceof UploadedFile) {
            return;
        }

        $shopId = $this->getContextShopId();
        $extension = strtolower((string) ($uploadedImage->guessExtension() ?: $uploadedImage->getClientOriginalExtension() ?: 'jpg'));
        if ('jpeg' === $extension) {
            $extension = 'jpg';
        }
        if (!in_array($extension, ['jpg', 'png', 'gif', 'webp'], true)) {
            throw new \RuntimeException('Format d\'image non pris en charge.');
        }

        $targetDirectory = rtrim(_PS_IMG_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'author';
        if (!is_dir($targetDirectory) && !@mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
            throw new \RuntimeException('Impossible de creer le dossier de destination des images auteur.');
        }

        $this->deleteAuthorImageFiles($authorId);

        $targetFileName = sprintf('%d.%s', $authorId, $extension);
        $this->imageUploader->upload($uploadedImage, $targetDirectory, $targetFileName);

        $image = $this->blogImageService->getBlogImage($authorId, $shopId, 'author');
        if (!\Validate::isLoadedObject($image)) {
            $image = $this->blogImageService->createImageModel();
        }

        $image->id_element = $authorId;
        $image->id_shop = $shopId;
        $image->image_type = 'author';
        $image->image_link = 'img/author/' . $targetFileName;
        if (!(bool) $image->save()) {
            throw new \RuntimeException('Impossible d\'enregistrer la reference de l\'image auteur.');
        }

        $this->blogImageService->clearCache();
    }

    private function deleteAuthorImage(int $authorId): void
    {
        $shopId = $this->getContextShopId();
        $image = $this->blogImageService->getBlogImage($authorId, $shopId, 'author');

        if (\Validate::isLoadedObject($image)) {
            $this->deleteReferencedImageFile((string) $image->image_link);
            $imageId = (int) ($image->id ?: $image->id_ever_image);

            if ($imageId > 0) {
                \Db::getInstance()->delete(
                    'ever_blog_image_shop',
                    'id_ever_image = ' . $imageId . ' AND id_shop = ' . $shopId
                );
                \Db::getInstance()->delete(
                    'ever_blog_image',
                    'id_ever_image = ' . $imageId
                    . ' AND id_element = ' . $authorId
                    . ' AND id_shop = ' . $shopId
                    . ' AND image_type = "author"'
                );
            }
        }

        $this->deleteAuthorImageFiles($authorId);
        $this->blogImageService->clearCache();
    }

    private function deleteAuthorImageFiles(int $authorId): void
    {
        $targetDirectory = rtrim(_PS_IMG_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'author';
        foreach ((array) glob($targetDirectory . DIRECTORY_SEPARATOR . $authorId . '.*') as $existingFile) {
            $this->deleteLocalImageFile((string) $existingFile);
        }

        $thumbDirectory = $targetDirectory . DIRECTORY_SEPARATOR . 'thumbs';
        foreach ((array) glob($thumbDirectory . DIRECTORY_SEPARATOR . $authorId . '-*') as $existingFile) {
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

    private function hasAuthorImage(int $authorId): bool
    {
        $image = $this->blogImageService->getBlogImage($authorId, $this->getContextShopId(), 'author');

        return \Validate::isLoadedObject($image);
    }

    private function buildAuthorImageHelp(int $authorId): string
    {
        $shopId = $this->getContextShopId();
        $image = $this->blogImageService->getBlogImage($authorId, $shopId, 'author');
        if (!\Validate::isLoadedObject($image)) {
            return '';
        }

        $url = (string) $this->blogImageService->getBlogImageUrl($authorId, $shopId, 'author');
        $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

        return sprintf(
            '<span class="ever-featured-image-preview"><img src="%1$s" alt="Image auteur actuelle" loading="lazy"><span>Image actuelle : <a href="%1$s" target="_blank" rel="noopener noreferrer">ouvrir dans un nouvel onglet</a></span></span>',
            $escapedUrl
        );
    }

    private function extractReassignAuthorId(Request $request): ?int
    {
        $raw = $request->request->get('reassign_author_id');
        if (null === $raw) {
            $raw = $request->query->get('reassign_author_id');
        }
        if (null === $raw) {
            $payload = json_decode((string) $request->getContent(), true);
            if (is_array($payload) && isset($payload['reassign_author_id'])) {
                $raw = $payload['reassign_author_id'];
            }
        }

        if (null === $raw || '' === $raw) {
            return null;
        }

        $value = (int) $raw;

        return $value > 0 ? $value : null;
    }

    private function validateCsrfToken(Request $request, string $tokenId): void
    {
        $token = (string) ($request->request->get('_csrf_token') ?: $request->request->get('_token') ?: $request->headers->get('X-CSRF-TOKEN'));

        if (!$this->isCsrfTokenValid($tokenId, $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }
}
