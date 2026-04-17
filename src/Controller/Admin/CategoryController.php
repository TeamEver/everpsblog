<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Form\DataProvider\CategoryFormDataProvider;
use PrestaShop\Module\Everpsblog\Form\Type\Admin\CategoryType;
use PrestaShop\Module\Everpsblog\Grid\Data\CategoryGridDataFactory;
use PrestaShop\Module\Everpsblog\Grid\Definition\CategoryGridDefinitionFactory;
use PrestaShop\Module\Everpsblog\Security\BlogPermission;
use PrestaShop\Module\Everpsblog\Service\ContextStateService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends AbstractDomainController
{
    private $definitionFactory;
    private $dataFactory;
    private $formDataProvider;

    public function __construct(ContextStateService $contextStateService, CategoryGridDefinitionFactory $definitionFactory, CategoryGridDataFactory $dataFactory, CategoryFormDataProvider $formDataProvider)
    {
        parent::__construct($contextStateService);
        $this->definitionFactory = $definitionFactory;
        $this->dataFactory = $dataFactory;
        $this->formDataProvider = $formDataProvider;
    }

    public function indexAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(BlogPermission::READ, BlogPermission::RES_CATEGORY);
        return $this->render('@Modules/everpsblog/views/templates/admin/modern/resource.html.twig', [
            'definition' => $this->definitionFactory->build(),
            'data' => $this->dataFactory->build($this->getContextShopId(), $this->getContextLangId(), $request->query->all()),
            'resource' => 'category',
        ]);
    }

    public function formAction(Request $request, ?int $categoryId = null): Response
    {
        $this->denyAccessUnlessGranted($categoryId ? BlogPermission::UPDATE : BlogPermission::CREATE, BlogPermission::RES_CATEGORY);

        $form = $this->createForm(CategoryType::class, $this->formDataProvider->getData($categoryId));
        $form->handleRequest($request);

        return $this->render('@Modules/everpsblog/views/templates/admin/modern/form.html.twig', [
            'resource' => 'category',
            'entityId' => $categoryId,
            'form' => $form->createView(),
        ]);
    }
}
