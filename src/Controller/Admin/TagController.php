<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Application\Blog\TagCommandAssembler;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeleteTagCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandBus\CommandBusInterface;
use PrestaShop\Module\Everpsblog\Form\DataProvider\TagFormDataProvider;
use PrestaShop\Module\Everpsblog\Form\Type\Admin\TagType;
use PrestaShop\Module\Everpsblog\Grid\Data\TagGridDataFactory;
use PrestaShop\Module\Everpsblog\Grid\Definition\TagGridDefinitionFactory;
use PrestaShop\Module\Everpsblog\Service\AdminBlogImageManager;
use PrestaShop\Module\Everpsblog\Service\BlogSitemapService;
use PrestaShop\Module\Everpsblog\Service\ContextStateService;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TagController extends AbstractDomainController
{
    private $commandBus;
    private $commandAssembler;
    private $definitionFactory;
    private $dataFactory;
    private $formDataProvider;
    private $blogSitemapService;
    private $adminBlogImageManager;

    public function __construct(ContextStateService $contextStateService, CommandBusInterface $commandBus, TagCommandAssembler $commandAssembler, TagGridDefinitionFactory $definitionFactory, TagGridDataFactory $dataFactory, TagFormDataProvider $formDataProvider, BlogSitemapService $blogSitemapService, AdminBlogImageManager $adminBlogImageManager)
    {
        parent::__construct($contextStateService);
        $this->commandBus = $commandBus;
        $this->commandAssembler = $commandAssembler;
        $this->definitionFactory = $definitionFactory;
        $this->dataFactory = $dataFactory;
        $this->formDataProvider = $formDataProvider;
        $this->blogSitemapService = $blogSitemapService;
        $this->adminBlogImageManager = $adminBlogImageManager;
    }

    public function indexAction(Request $request): Response
    {
        return $this->render('@Modules/everpsblog/views/templates/admin/modern/resource.html.twig', [
            'definition' => $this->definitionFactory->build(),
            'data' => $this->dataFactory->build($this->getContextShopId(), $this->getContextLangId(), $request->query->all()),
            'resource' => 'tag',
            'currentResource' => 'tag',
            'createUrl' => $this->generateUrl('everpsblog_admin_tag_form'),
            'navigationLinks' => $this->getAdminNavigationLinks(),
        ]);
    }

    public function formAction(Request $request, ?int $tagId = null): Response
    {
        $isEdit = null !== $tagId;
        $csrfTokenId = $isEdit ? 'everpsblog_tag_update_' . $tagId : 'everpsblog_tag_create';
        $bannerImageHelp = $isEdit ? $this->buildBannerImageHelp((int) $tagId) : '';
        $hasBannerImage = $isEdit ? $this->hasBannerImage((int) $tagId) : false;

        $form = $this->createForm(TagType::class, $this->formDataProvider->getData($tagId), [
            'method' => Request::METHOD_POST,
            'action' => $isEdit
                ? $this->generateUrl('everpsblog_admin_tag_edit', ['tagId' => $tagId])
                : $this->generateUrl('everpsblog_admin_tag_form'),
            'banner_image_help' => $bannerImageHelp,
            'has_banner_image' => $hasBannerImage,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->validateCsrfToken($request, $csrfTokenId);

            if ($form->isValid()) {
                try {
                    $savedTagId = $isEdit
                        ? $this->commandBus->handle($this->commandAssembler->assembleUpdate((int) $tagId, (array) $form->getData()))
                        : $this->commandBus->handle($this->commandAssembler->assembleCreate((array) $form->getData()));
                    if ((bool) $form->get('delete_banner_image')->getData()) {
                        $this->deleteBannerImage((int) $savedTagId);
                    }
                    $this->handleBannerImageUpload($form->get('banner_image_file')->getData(), (int) $savedTagId);
                    $this->refreshSitemapsAfterBackOfficeChange($this->blogSitemapService);
                    $submitAction = (string) $request->request->get('_submit_action', 'save');

                    $this->addFlash('success', $isEdit ? $this->transAdmin('Tag updated.') : $this->transAdmin('Tag created.'));

                    if ('save_and_stay' === $submitAction) {
                        return $this->redirectToRoute('everpsblog_admin_tag_edit', ['tagId' => $savedTagId]);
                    }

                    return $this->redirectToRoute('everpsblog_admin_tag');
                } catch (\Throwable $exception) {
                    $message = $this->transAdmin('Unable to save tag: %error%', ['%error%' => $this->describeException($exception)]);
                    $form->addError(new FormError($message));
                    $this->addFlash('error', $message);
                    \PrestaShopLogger::addLog(
                        '[everpsblog][TagController::formAction] ' . $exception->getMessage()
                            . ' @ ' . $exception->getFile() . ':' . $exception->getLine(),
                        3
                    );
                }
            }
        }

        return $this->render('@Modules/everpsblog/views/templates/admin/modern/form.html.twig', [
            'resource' => 'tag',
            'entityId' => $tagId,
            'csrfTokenId' => $csrfTokenId,
            'form' => $form->createView(),
            'currentResource' => 'tag',
            'cancelUrl' => $this->generateUrl('everpsblog_admin_tag'),
            'createUrl' => $this->generateUrl('everpsblog_admin_tag_form'),
            'navigationLinks' => $this->getAdminNavigationLinks(),
            'qcdPageBuilderTargets' => $this->buildQcdPageBuilderTargets('everpsblog_tag', $tagId, [
                'content' => $this->transAdmin('Edit content with Page Builder'),
                'bottom_content' => $this->transAdmin('Edit bottom content with Page Builder'),
            ]),
        ]);
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_tag_create');

        $tagId = $this->commandBus->handle($this->commandAssembler->assembleCreate($request->request->all()));
        $sitemapsRefreshed = $this->refreshSitemapsAfterBackOfficeChange($this->blogSitemapService, false);

        return new JsonResponse(['id_ever_tag' => $tagId, 'sitemaps_refreshed' => $sitemapsRefreshed], JsonResponse::HTTP_CREATED);
    }

    public function updateAction(int $tagId, Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_tag_update_' . $tagId);

        $updatedTagId = $this->commandBus->handle($this->commandAssembler->assembleUpdate($tagId, $request->request->all()));
        $sitemapsRefreshed = $this->refreshSitemapsAfterBackOfficeChange($this->blogSitemapService, false);

        return new JsonResponse(['id_ever_tag' => $updatedTagId, 'sitemaps_refreshed' => $sitemapsRefreshed], JsonResponse::HTTP_OK);
    }

    public function deleteAction(int $tagId, Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_tag_delete_' . $tagId);

        $this->commandBus->handle(new DeleteTagCommand($tagId));
        $this->deleteBannerImage($tagId);
        $this->refreshSitemapsAfterBackOfficeChange($this->blogSitemapService, false);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    private function validateCsrfToken(Request $request, string $tokenId): void
    {
        $token = (string) ($request->request->get('_csrf_token') ?: $request->request->get('_token') ?: $request->headers->get('X-CSRF-TOKEN'));

        if (!$this->isCsrfTokenValid($tokenId, $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }

    private function handleBannerImageUpload($uploadedImage, int $tagId): void
    {
        $this->adminBlogImageManager->upload($uploadedImage, $tagId, $this->getContextShopId(), 'tag_banner');
    }

    private function deleteBannerImage(int $tagId): void
    {
        $this->adminBlogImageManager->delete($tagId, $this->getContextShopId(), 'tag_banner');
    }

    private function hasBannerImage(int $tagId): bool
    {
        return $this->adminBlogImageManager->hasImage($tagId, $this->getContextShopId(), 'tag_banner');
    }

    private function buildBannerImageHelp(int $tagId): string
    {
        return $this->adminBlogImageManager->buildImageHelp(
            $tagId,
            $this->getContextShopId(),
            'tag_banner',
            $this->transAdmin('Current banner image'),
            $this->transAdmin('open in a new tab')
        );
    }
}
