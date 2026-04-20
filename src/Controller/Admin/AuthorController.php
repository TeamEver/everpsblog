<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Application\Blog\AuthorCommandAssembler;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeleteAuthorCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandBus\CommandBusInterface;
use PrestaShop\Module\Everpsblog\Form\DataProvider\AuthorFormDataProvider;
use PrestaShop\Module\Everpsblog\Form\Type\Admin\AuthorType;
use PrestaShop\Module\Everpsblog\Grid\Data\AuthorGridDataFactory;
use PrestaShop\Module\Everpsblog\Grid\Definition\AuthorGridDefinitionFactory;
use PrestaShop\Module\Everpsblog\Service\ContextStateService;
use Symfony\Component\Form\FormError;
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

    public function __construct(ContextStateService $contextStateService, CommandBusInterface $commandBus, AuthorCommandAssembler $commandAssembler, AuthorGridDefinitionFactory $definitionFactory, AuthorGridDataFactory $dataFactory, AuthorFormDataProvider $formDataProvider)
    {
        parent::__construct($contextStateService);
        $this->commandBus = $commandBus;
        $this->commandAssembler = $commandAssembler;
        $this->definitionFactory = $definitionFactory;
        $this->dataFactory = $dataFactory;
        $this->formDataProvider = $formDataProvider;
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

        $form = $this->createForm(AuthorType::class, $this->formDataProvider->getData($authorId), [
            'method' => Request::METHOD_POST,
            'action' => $isEdit
                ? $this->generateUrl('everpsblog_admin_author_edit', ['authorId' => $authorId])
                : $this->generateUrl('everpsblog_admin_author_form'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->validateCsrfToken($request, $csrfTokenId);

            if ($form->isValid()) {
                try {
                    $savedAuthorId = $isEdit
                        ? $this->commandBus->handle($this->commandAssembler->assembleUpdate((int) $authorId, (array) $form->getData()))
                        : $this->commandBus->handle($this->commandAssembler->assembleCreate((array) $form->getData()));
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

    public function deleteAction(int $authorId, Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_author_delete_' . $authorId);

        $this->commandBus->handle(new DeleteAuthorCommand($authorId));

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
