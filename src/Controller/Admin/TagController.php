<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Application\Blog\TagCommandAssembler;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeleteTagCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandBus\CommandBusInterface;
use PrestaShop\Module\Everpsblog\Form\DataProvider\TagFormDataProvider;
use PrestaShop\Module\Everpsblog\Form\Type\Admin\TagType;
use PrestaShop\Module\Everpsblog\Grid\Data\TagGridDataFactory;
use PrestaShop\Module\Everpsblog\Grid\Definition\TagGridDefinitionFactory;
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

    public function __construct(ContextStateService $contextStateService, CommandBusInterface $commandBus, TagCommandAssembler $commandAssembler, TagGridDefinitionFactory $definitionFactory, TagGridDataFactory $dataFactory, TagFormDataProvider $formDataProvider)
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

        $form = $this->createForm(TagType::class, $this->formDataProvider->getData($tagId), [
            'method' => Request::METHOD_POST,
            'action' => $isEdit
                ? $this->generateUrl('everpsblog_admin_tag_edit', ['tagId' => $tagId])
                : $this->generateUrl('everpsblog_admin_tag_form'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->validateCsrfToken($request, $csrfTokenId);

            if ($form->isValid()) {
                try {
                    $savedTagId = $isEdit
                        ? $this->commandBus->handle($this->commandAssembler->assembleUpdate((int) $tagId, (array) $form->getData()))
                        : $this->commandBus->handle($this->commandAssembler->assembleCreate((array) $form->getData()));

                    $this->addFlash('success', $isEdit ? 'Tag mis à jour.' : 'Tag créé.');

                    return $this->redirectToRoute('everpsblog_admin_tag_edit', ['tagId' => $savedTagId]);
                } catch (\Throwable $exception) {
                    $form->addError(new FormError('Impossible d\'enregistrer le tag.'));
                }
            }
        }

        return $this->render('@Modules/everpsblog/views/templates/admin/modern/form.html.twig', [
            'resource' => 'tag',
            'entityId' => $tagId,
            'csrfTokenId' => $csrfTokenId,
            'form' => $form->createView(),
            'currentResource' => 'tag',
            'createUrl' => $this->generateUrl('everpsblog_admin_tag_form'),
            'navigationLinks' => $this->getAdminNavigationLinks(),
        ]);
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_tag_create');

        $tagId = $this->commandBus->handle($this->commandAssembler->assembleCreate($request->request->all()));

        return new JsonResponse(['id_ever_tag' => $tagId], JsonResponse::HTTP_CREATED);
    }

    public function updateAction(int $tagId, Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_tag_update_' . $tagId);

        $updatedTagId = $this->commandBus->handle($this->commandAssembler->assembleUpdate($tagId, $request->request->all()));

        return new JsonResponse(['id_ever_tag' => $updatedTagId], JsonResponse::HTTP_OK);
    }

    public function deleteAction(int $tagId, Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_tag_delete_' . $tagId);

        $this->commandBus->handle(new DeleteTagCommand($tagId));

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
