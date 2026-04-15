<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Form\DataProvider\AuthorFormDataProvider;
use PrestaShop\Module\Everpsblog\Form\Type\Admin\AuthorType;
use PrestaShop\Module\Everpsblog\Grid\Data\AuthorGridDataFactory;
use PrestaShop\Module\Everpsblog\Grid\Definition\AuthorGridDefinitionFactory;
use PrestaShop\Module\Everpsblog\Security\BlogPermission;
use PrestaShop\Module\Everpsblog\Service\ContextStateService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorController extends AbstractDomainController
{
    private $definitionFactory;
    private $dataFactory;
    private $formDataProvider;

    public function __construct(ContextStateService $contextStateService, AuthorGridDefinitionFactory $definitionFactory, AuthorGridDataFactory $dataFactory, AuthorFormDataProvider $formDataProvider)
    {
        parent::__construct($contextStateService);
        $this->definitionFactory = $definitionFactory;
        $this->dataFactory = $dataFactory;
        $this->formDataProvider = $formDataProvider;
    }

    public function indexAction(Request $request): Response
    {
        $this->denyBlogAccess(BlogPermission::READ, BlogPermission::RES_AUTHOR);
        return $this->render('@Modules/everpsblog/views/templates/admin/modern/resource.html.twig', [
            'definition' => $this->definitionFactory->build(),
            'data' => $this->dataFactory->build($this->getContextShopId(), $this->getContextLangId(), $request->query->all()),
            'resource' => 'author',
        ]);
    }

    public function formAction(Request $request, ?int $authorId = null): Response
    {
        $this->denyBlogAccess($authorId ? BlogPermission::UPDATE : BlogPermission::CREATE, BlogPermission::RES_AUTHOR);

        $form = $this->createForm(AuthorType::class, $this->formDataProvider->getData($authorId));
        $form->handleRequest($request);

        return $this->render('@Modules/everpsblog/views/templates/admin/modern/form.html.twig', [
            'resource' => 'author',
            'entityId' => $authorId,
            'form' => $form->createView(),
        ]);
    }
}
