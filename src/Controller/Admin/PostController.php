<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Application\Blog\PostCommandAssembler;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\DeletePostCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandBus\CommandBusInterface;
use PrestaShop\Module\Everpsblog\Form\DataProvider\PostFormDataProvider;
use PrestaShop\Module\Everpsblog\Form\Type\Admin\PostType;
use PrestaShop\Module\Everpsblog\Grid\Data\PostGridDataFactory;
use PrestaShop\Module\Everpsblog\Grid\Definition\PostGridDefinitionFactory;
use PrestaShop\Module\Everpsblog\Service\Audit\SensitiveActionLogger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostController extends AbstractDomainController
{
    private $commandBus;
    private $commandAssembler;
    private $definitionFactory;
    private $dataFactory;
    private $formDataProvider;
    private $sensitiveActionLogger;

    public function __construct(
        \PrestaShop\Module\Everpsblog\Service\ContextStateService $contextStateService,
        CommandBusInterface $commandBus,
        PostCommandAssembler $commandAssembler,
        PostGridDefinitionFactory $definitionFactory,
        PostGridDataFactory $dataFactory,
        PostFormDataProvider $formDataProvider,
        SensitiveActionLogger $sensitiveActionLogger
    ) {
        parent::__construct($contextStateService);
        $this->commandBus = $commandBus;
        $this->commandAssembler = $commandAssembler;
        $this->definitionFactory = $definitionFactory;
        $this->dataFactory = $dataFactory;
        $this->formDataProvider = $formDataProvider;
        $this->sensitiveActionLogger = $sensitiveActionLogger;
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
        $this->validateCsrfToken($request, 'everpsblog_post_create');

        $command = $this->commandAssembler->assembleCreate($request->request->all());
        $postId = $this->commandBus->handle($command);

        return new JsonResponse(['id_ever_post' => $postId], JsonResponse::HTTP_CREATED);
    }

    public function updateAction(int $postId, Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_post_update_' . $postId);

        $command = $this->commandAssembler->assembleUpdate($postId, $request->request->all());

        $updatedPostId = $this->commandBus->handle($command);

        return new JsonResponse(['id_ever_post' => $updatedPostId], JsonResponse::HTTP_OK);
    }

    public function deleteAction(int $postId, Request $request): JsonResponse
    {
        $this->validateCsrfToken($request, 'everpsblog_post_delete_' . $postId);

        $this->commandBus->handle(new DeletePostCommand($postId));
        $this->sensitiveActionLogger->log('bo_post_delete', ['post_id' => $postId]);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    private function validateCsrfToken(Request $request, string $tokenId): void
    {
        $token = (string) ($request->request->get('_csrf_token') ?: $request->headers->get('X-CSRF-TOKEN'));

        if (!$this->isCsrfTokenValid($tokenId, $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }
}
