<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\CreatePostCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeletePostCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\UpdatePostCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandBus\CommandBusInterface;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler\PostCommandDataBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class PostController extends AbstractDomainController
{
    /** @var CommandBusInterface */
    private $commandBus;
    /** @var PostCommandDataBuilder */
    private $dataBuilder;

    public function __construct(CommandBusInterface $commandBus, PostCommandDataBuilder $dataBuilder)
    {
        $this->commandBus = $commandBus;
        $this->dataBuilder = $dataBuilder;
    }

    public function indexAction(Request $request): RedirectResponse
    {
        return $this->redirectToLegacyController($request, 'AdminEverPsBlogPost');
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
