<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Application\Blog\CommentCommandAssembler;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeleteCommentCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandBus\CommandBusInterface;
use PrestaShop\Module\Everpsblog\Form\DataProvider\CommentFormDataProvider;
use PrestaShop\Module\Everpsblog\Form\Type\Admin\CommentType;
use PrestaShop\Module\Everpsblog\Grid\Data\CommentGridDataFactory;
use PrestaShop\Module\Everpsblog\Grid\Definition\CommentGridDefinitionFactory;
use PrestaShop\Module\Everpsblog\Service\BlogSitemapService;
use PrestaShop\Module\Everpsblog\Service\ContextStateService;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}


class CommentController extends AbstractDomainController
{
    private $commandBus;
    private $commandAssembler;
    private $definitionFactory;
    private $dataFactory;
    private $formDataProvider;
    private $blogSitemapService;

    public function __construct(ContextStateService $contextStateService, CommandBusInterface $commandBus, CommentCommandAssembler $commandAssembler, CommentGridDefinitionFactory $definitionFactory, CommentGridDataFactory $dataFactory, CommentFormDataProvider $formDataProvider, BlogSitemapService $blogSitemapService)
    {
        parent::__construct($contextStateService);
        $this->commandBus = $commandBus;
        $this->commandAssembler = $commandAssembler;
        $this->definitionFactory = $definitionFactory;
        $this->dataFactory = $dataFactory;
        $this->formDataProvider = $formDataProvider;
        $this->blogSitemapService = $blogSitemapService;
    }

    public function indexAction(Request $request): Response
    {
        return $this->render('@Modules/everpsblog/views/templates/admin/modern/resource.html.twig', [
            'definition' => $this->definitionFactory->build(),
            'data' => $this->dataFactory->build($this->getContextLangId(), $request->query->all()),
            'resource' => 'comment',
            'currentResource' => 'comment',
            'createUrl' => $this->generateUrl('everpsblog_admin_comment_form'),
            'navigationLinks' => $this->getAdminNavigationLinks(),
        ]);
    }

    public function formAction(Request $request, ?int $commentId = null): Response
    {
        $isEdit = null !== $commentId;
        $csrfTokenId = $isEdit ? 'everpsblog_comment_update_' . $commentId : 'everpsblog_comment_create';

        $form = $this->createForm(CommentType::class, $this->formDataProvider->getData($commentId), [
            'method' => Request::METHOD_POST,
            'action' => $isEdit
                ? $this->generateUrl('everpsblog_admin_comment_edit', ['commentId' => $commentId])
                : $this->generateUrl('everpsblog_admin_comment_form'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->validateCsrfToken($request, $csrfTokenId);

            if ($form->isValid()) {
                try {
                    $savedCommentId = $isEdit
                        ? $this->commandBus->handle($this->commandAssembler->assembleUpdate((int) $commentId, (array) $form->getData()))
                        : $this->commandBus->handle($this->commandAssembler->assembleCreate((array) $form->getData()));
                    $this->refreshSitemapsAfterBackOfficeChange($this->blogSitemapService);
                    $submitAction = (string) $request->request->get('_submit_action', 'save');

                    $this->addFlash('success', $isEdit ? $this->transAdmin('Comment updated.') : $this->transAdmin('Comment created.'));

                    if ('save_and_stay' === $submitAction) {
                        return $this->redirectToRoute('everpsblog_admin_comment_edit', ['commentId' => $savedCommentId]);
                    }

                    return $this->redirectToRoute('everpsblog_admin_comment');
                } catch (\Throwable $exception) {
                    $message = $this->transAdmin('Unable to save comment: %error%', ['%error%' => $this->describeException($exception)]);
                    $form->addError(new FormError($message));
                    $this->addFlash('error', $message);
                    \PrestaShopLogger::addLog(
                        '[everpsblog][CommentController::formAction] ' . $exception->getMessage()
                            . ' @ ' . $exception->getFile() . ':' . $exception->getLine(),
                        3
                    );
                }
            }
        }

        return $this->render('@Modules/everpsblog/views/templates/admin/modern/form.html.twig', [
            'resource' => 'comment',
            'entityId' => $commentId,
            'csrfTokenId' => $csrfTokenId,
            'form' => $form->createView(),
            'currentResource' => 'comment',
            'cancelUrl' => $this->generateUrl('everpsblog_admin_comment'),
            'createUrl' => $this->generateUrl('everpsblog_admin_comment_form'),
            'navigationLinks' => $this->getAdminNavigationLinks(),
            'everBlogLanguages' => $this->getEverBlogLanguages(),
        ]);
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_comment_create');

        $commentId = $this->commandBus->handle($this->commandAssembler->assembleCreate($request->request->all()));
        $this->refreshSitemapsAfterBackOfficeChange($this->blogSitemapService, false);

        return new JsonResponse(['id_ever_comment' => $commentId], JsonResponse::HTTP_CREATED);
    }

    public function updateAction(int $commentId, Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_comment_update_' . $commentId);

        $updatedCommentId = $this->commandBus->handle($this->commandAssembler->assembleUpdate($commentId, $request->request->all()));
        $this->refreshSitemapsAfterBackOfficeChange($this->blogSitemapService, false);

        return new JsonResponse(['id_ever_comment' => $updatedCommentId], JsonResponse::HTTP_OK);
    }

    public function deleteAction(int $commentId, Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_comment_delete_' . $commentId);

        $this->commandBus->handle(new DeleteCommentCommand($commentId));

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    private function validateCsrfToken(Request $request, string $tokenId): void
    {
        $token = (string) ($request->request->get('_csrf_token') ?: $request->request->get('_token') ?: $request->headers->get('X-CSRF-TOKEN'));

        if (!$this->isCsrfTokenValid($tokenId, $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }
}
