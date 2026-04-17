<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Application\Blog\CategoryCommandAssembler;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeleteCategoryCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandBus\CommandBusInterface;
use PrestaShop\Module\Everpsblog\Form\DataProvider\CategoryFormDataProvider;
use PrestaShop\Module\Everpsblog\Form\Type\Admin\CategoryType;
use PrestaShop\Module\Everpsblog\Grid\Data\CategoryGridDataFactory;
use PrestaShop\Module\Everpsblog\Grid\Definition\CategoryGridDefinitionFactory;
use PrestaShop\Module\Everpsblog\Service\ContextStateService;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends AbstractDomainController
{
    private $commandBus;
    private $commandAssembler;
    private $definitionFactory;
    private $dataFactory;
    private $formDataProvider;

    public function __construct(ContextStateService $contextStateService, CommandBusInterface $commandBus, CategoryCommandAssembler $commandAssembler, CategoryGridDefinitionFactory $definitionFactory, CategoryGridDataFactory $dataFactory, CategoryFormDataProvider $formDataProvider)
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
            'resource' => 'category',
            'currentResource' => 'category',
            'createUrl' => $this->generateUrl('everpsblog_admin_category_form'),
            'navigationLinks' => $this->getAdminNavigationLinks(),
        ]);
    }

    public function formAction(Request $request, ?int $categoryId = null): Response
    {
        $isEdit = null !== $categoryId;
        $csrfTokenId = $isEdit ? 'everpsblog_category_update_' . $categoryId : 'everpsblog_category_create';

        $form = $this->createForm(CategoryType::class, $this->formDataProvider->getData($categoryId), [
            'method' => Request::METHOD_POST,
            'action' => $isEdit
                ? $this->generateUrl('everpsblog_admin_category_edit', ['categoryId' => $categoryId])
                : $this->generateUrl('everpsblog_admin_category_form'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->validateCsrfToken($request, $csrfTokenId);

            if ($form->isValid()) {
                $data = (array) $form->getData();

                try {
                    $savedCategoryId = $isEdit
                        ? $this->commandBus->handle($this->commandAssembler->assembleUpdate((int) $categoryId, $data))
                        : $this->commandBus->handle($this->commandAssembler->assembleCreate($data));
                    $submitAction = (string) $request->request->get('_submit_action', 'save');

                    $this->addFlash('success', $isEdit ? 'Catégorie mise à jour.' : 'Catégorie créée.');

                    if ('save_and_stay' === $submitAction) {
                        return $this->redirectToRoute('everpsblog_admin_category_edit', ['categoryId' => $savedCategoryId]);
                    }

                    return $this->redirectToRoute('everpsblog_admin_category');
                } catch (\Throwable $exception) {
                    $form->addError(new FormError('Impossible d\'enregistrer la catégorie.'));
                }
            }
        }

        return $this->render('@Modules/everpsblog/views/templates/admin/modern/form.html.twig', [
            'resource' => 'category',
            'entityId' => $categoryId,
            'csrfTokenId' => $csrfTokenId,
            'form' => $form->createView(),
            'currentResource' => 'category',
            'cancelUrl' => $this->generateUrl('everpsblog_admin_category'),
            'createUrl' => $this->generateUrl('everpsblog_admin_category_form'),
            'navigationLinks' => $this->getAdminNavigationLinks(),
        ]);
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_category_create');

        $categoryId = $this->commandBus->handle($this->commandAssembler->assembleCreate($request->request->all()));

        return new JsonResponse(['id_ever_category' => $categoryId], JsonResponse::HTTP_CREATED);
    }

    public function updateAction(int $categoryId, Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_category_update_' . $categoryId);

        $updatedCategoryId = $this->commandBus->handle($this->commandAssembler->assembleUpdate($categoryId, $request->request->all()));

        return new JsonResponse(['id_ever_category' => $updatedCategoryId], JsonResponse::HTTP_OK);
    }

    public function deleteAction(int $categoryId, Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_category_delete_' . $categoryId);

        $this->commandBus->handle(new DeleteCategoryCommand($categoryId));

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
