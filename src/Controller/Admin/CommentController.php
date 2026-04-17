<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Form\DataProvider\CommentFormDataProvider;
use PrestaShop\Module\Everpsblog\Form\Type\Admin\CommentType;
use PrestaShop\Module\Everpsblog\Grid\Data\CommentGridDataFactory;
use PrestaShop\Module\Everpsblog\Grid\Definition\CommentGridDefinitionFactory;
use PrestaShop\Module\Everpsblog\Security\BlogPermission;
use PrestaShop\Module\Everpsblog\Service\ContextStateService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends AbstractDomainController
{
    private $definitionFactory;
    private $dataFactory;
    private $formDataProvider;

    public function __construct(ContextStateService $contextStateService, CommentGridDefinitionFactory $definitionFactory, CommentGridDataFactory $dataFactory, CommentFormDataProvider $formDataProvider)
    {
        parent::__construct($contextStateService);
        $this->definitionFactory = $definitionFactory;
        $this->dataFactory = $dataFactory;
        $this->formDataProvider = $formDataProvider;
    }

    public function indexAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(BlogPermission::READ, BlogPermission::RES_COMMENT);
        return $this->render('@Modules/everpsblog/views/templates/admin/modern/resource.html.twig', [
            'definition' => $this->definitionFactory->build(),
            'data' => $this->dataFactory->build($this->getContextLangId(), $request->query->all()),
            'resource' => 'comment',
        ]);
    }

    public function formAction(Request $request, ?int $commentId = null): Response
    {
        $this->denyAccessUnlessGranted($commentId ? BlogPermission::UPDATE : BlogPermission::CREATE, BlogPermission::RES_COMMENT);

        $form = $this->createForm(CommentType::class, $this->formDataProvider->getData($commentId));
        $form->handleRequest($request);

        return $this->render('@Modules/everpsblog/views/templates/admin/modern/form.html.twig', [
            'resource' => 'comment',
            'entityId' => $commentId,
            'form' => $form->createView(),
        ]);
    }
}
