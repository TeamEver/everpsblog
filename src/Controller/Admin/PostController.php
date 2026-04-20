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

    public function __construct(
        \PrestaShop\Module\Everpsblog\Service\ContextStateService $contextStateService,
        CommandBusInterface $commandBus,
        PostCommandAssembler $commandAssembler,
        PostGridDefinitionFactory $definitionFactory,
        PostGridDataFactory $dataFactory,
        PostFormDataProvider $formDataProvider,
        SensitiveActionLogger $sensitiveActionLogger,
        BlogImageService $blogImageService,
        ImageUploader $imageUploader
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
            'navigationLinks' => $this->getAdminNavigationLinks(),
        ]);
    }

    public function formAction(Request $request, ?int $postId = null): Response
    {
        $isEdit = null !== $postId;
        $csrfTokenId = $isEdit ? 'everpsblog_post_update_' . $postId : 'everpsblog_post_create';
        $featuredImageHelp = $isEdit ? $this->buildFeaturedImageHelp((int) $postId) : '';
        $form = $this->createForm(PostType::class, $this->formDataProvider->getData($postId), [
            'method' => Request::METHOD_POST,
            'action' => $isEdit
                ? $this->generateUrl('everpsblog_admin_post_edit', ['postId' => $postId])
                : $this->generateUrl('everpsblog_admin_post_form'),
            'featured_image_help' => $featuredImageHelp,
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
                    $this->handleFeaturedImageUpload($form->get('publication_tab')->get('featured_image_file')->getData(), (int) $savedPostId);
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

        foreach ((array) glob($targetDirectory . DIRECTORY_SEPARATOR . $postId . '.*') as $existingFile) {
            @unlink($existingFile);
        }

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

    private function buildFeaturedImageHelp(int $postId): string
    {
        $shopId = $this->getContextShopId();
        $image = $this->blogImageService->getBlogImage($postId, $shopId, 'post');
        if (!\Validate::isLoadedObject($image)) {
            return '';
        }
        $url = (string) $this->blogImageService->getBlogImageUrl($postId, $shopId, 'post');

        return sprintf(
            'Image actuelle : <a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
            htmlspecialchars($url, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($url, ENT_QUOTES, 'UTF-8')
        );
    }
}
