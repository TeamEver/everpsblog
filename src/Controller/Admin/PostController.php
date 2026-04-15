<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\CreatePostCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeletePostCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\UpdatePostCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandBus\CommandBusInterface;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler\PostCommandDataBuilder;
use PrestaShop\Module\Everpsblog\Form\DataProvider\PostFormDataProvider;
use PrestaShop\Module\Everpsblog\Form\Type\Admin\PostType;
use PrestaShop\Module\Everpsblog\Grid\Data\PostGridDataFactory;
use PrestaShop\Module\Everpsblog\Grid\Definition\PostGridDefinitionFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostController extends AbstractDomainController
{
    private $commandBus;
    private $dataBuilder;
    private $definitionFactory;
    private $dataFactory;
    private $formDataProvider;

    public function __construct(
        CommandBusInterface $commandBus,
        PostCommandDataBuilder $dataBuilder,
        PostGridDefinitionFactory $definitionFactory,
        PostGridDataFactory $dataFactory,
        PostFormDataProvider $formDataProvider
    ) {
        $this->commandBus = $commandBus;
        $this->dataBuilder = $dataBuilder;
        $this->definitionFactory = $definitionFactory;
        $this->dataFactory = $dataFactory;
        $this->formDataProvider = $formDataProvider;
    }

    public function indexAction(Request $request): Response
    {
        $definition = $this->definitionFactory->build();
        $data = $this->dataFactory->build($this->getContextShopId(), $this->getContextLangId(), $request->query->all());

        return $this->render('@Modules/everpsblog/views/templates/admin/modern/resource.html.twig', [
            'definition' => $definition,
            'data' => $data,
            'resource' => 'post',
        ]);
    }

    public function formAction(Request $request, ?int $postId = null): Response
    {
        $form = $this->createForm(PostType::class, $this->formDataProvider->getData($postId));
        $form->handleRequest($request);

        return $this->render('@Modules/everpsblog/views/templates/admin/modern/form.html.twig', [
            'resource' => 'post',
            'entityId' => $postId,
            'form' => $form->createView(),
        ]);
    }

    public function createAction(Request $request): JsonResponse
    {
        $command = new CreatePostCommand(
            $this->dataBuilder->buildFromRequestData($request->request->all())
        );

        $postId = $this->commandBus->handle($command);

        return new JsonResponse(['id_ever_post' => $postId], JsonResponse::HTTP_CREATED);
    }

    public function updateAction(int $postId, Request $request): JsonResponse
    {
        $command = new UpdatePostCommand(
            $postId,
            $this->dataBuilder->buildFromRequestData($request->request->all())
        );

        $updatedPostId = $this->commandBus->handle($command);

        return new JsonResponse(['id_ever_post' => $updatedPostId], JsonResponse::HTTP_OK);
    }

    public function deleteAction(int $postId): JsonResponse
    {
        $this->commandBus->handle(new DeletePostCommand($postId));

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
