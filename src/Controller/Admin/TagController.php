<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Form\DataProvider\TagFormDataProvider;
use PrestaShop\Module\Everpsblog\Form\Type\Admin\TagType;
use PrestaShop\Module\Everpsblog\Grid\Data\TagGridDataFactory;
use PrestaShop\Module\Everpsblog\Grid\Definition\TagGridDefinitionFactory;
use PrestaShop\Module\Everpsblog\Security\BlogPermission;
use PrestaShop\Module\Everpsblog\Service\ContextStateService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TagController extends AbstractDomainController
{
    private $definitionFactory;
    private $dataFactory;
    private $formDataProvider;

    public function __construct(ContextStateService $contextStateService, TagGridDefinitionFactory $definitionFactory, TagGridDataFactory $dataFactory, TagFormDataProvider $formDataProvider)
    {
        parent::__construct($contextStateService);
        $this->definitionFactory = $definitionFactory;
        $this->dataFactory = $dataFactory;
        $this->formDataProvider = $formDataProvider;
    }

    public function indexAction(Request $request): Response
    {
        $this->denyBlogAccess(BlogPermission::READ, BlogPermission::RES_TAG);
        return $this->render('@Modules/everpsblog/views/templates/admin/modern/resource.html.twig', [
            'definition' => $this->definitionFactory->build(),
            'data' => $this->dataFactory->build($this->getContextShopId(), $this->getContextLangId(), $request->query->all()),
            'resource' => 'tag',
        ]);
    }

    public function formAction(Request $request, ?int $tagId = null): Response
    {
        $this->denyBlogAccess($tagId ? BlogPermission::UPDATE : BlogPermission::CREATE, BlogPermission::RES_TAG);

        $form = $this->createForm(TagType::class, $this->formDataProvider->getData($tagId));
        $form->handleRequest($request);

        return $this->render('@Modules/everpsblog/views/templates/admin/modern/form.html.twig', [
            'resource' => 'tag',
            'entityId' => $tagId,
            'form' => $form->createView(),
        ]);
    }
}
