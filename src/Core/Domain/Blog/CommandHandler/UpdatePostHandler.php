<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Command\UpdatePostCommand;
use PrestaShop\Module\Everpsblog\Core\Domain\Blog\Repository\PostWriteRepository;
use PrestaShop\Module\Everpsblog\Entity\Post;
use PrestaShop\Module\Everpsblog\Service\BlogRedirectService;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheRelationResolver;

if (!defined('_PS_VERSION_')) {
    exit;
}


class UpdatePostHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var PostRulesApplier */
    private $rulesApplier;
    /** @var PostWriteRepository */
    private $postWriteRepository;
    /** @var BlogRedirectService */
    private $blogRedirectService;
    /** @var BlogFrontCacheInvalidator */
    private $cacheInvalidator;
    /** @var BlogFrontCacheRelationResolver */
    private $cacheRelationResolver;

    public function __construct(
        EntityManagerInterface $entityManager,
        PostRulesApplier $rulesApplier,
        PostWriteRepository $postWriteRepository,
        BlogRedirectService $blogRedirectService,
        ?BlogFrontCacheInvalidator $cacheInvalidator = null,
        ?BlogFrontCacheRelationResolver $cacheRelationResolver = null
    ) {
        $this->entityManager = $entityManager;
        $this->rulesApplier = $rulesApplier;
        $this->postWriteRepository = $postWriteRepository;
        $this->blogRedirectService = $blogRedirectService;
        $this->cacheInvalidator = $cacheInvalidator ?: new BlogFrontCacheInvalidator();
        $this->cacheRelationResolver = $cacheRelationResolver ?: new BlogFrontCacheRelationResolver();
    }

    public function __invoke(UpdatePostCommand $command): int
    {
        /** @var Post|null $post */
        $post = $this->entityManager->getRepository(Post::class)->find($command->getPostId());
        if (null === $post) {
            throw new InvalidArgumentException(sprintf('Post with id %d not found.', $command->getPostId()));
        }

        $data = $command->getData()->toArray();
        $previousSlugs = $this->postWriteRepository->getLocalizedSlugs((int) $post->getId());
        $beforeSnapshot = $this->cacheRelationResolver->getPostSnapshot((int) $post->getId());

        $relations = $this->rulesApplier->apply($post, $data);
        $this->postWriteRepository->save($post, $relations);
        $this->cacheInvalidator->invalidatePostMutation(
            (int) $post->getId(),
            $beforeSnapshot,
            $this->cacheRelationResolver->getPostSnapshot((int) $post->getId())
        );
        $this->saveSlugRedirects((int) $post->getId(), $data, $previousSlugs);

        return (int) $post->getId();
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, string> $previousSlugs
     */
    private function saveSlugRedirects(int $postId, array $data, array $previousSlugs): void
    {
        $shopId = (int) ($data['shop_id'] ?? 0);
        $translations = isset($data['translations']) && is_array($data['translations'])
            ? $data['translations']
            : [];

        if ($postId <= 0 || $shopId <= 0 || empty($previousSlugs) || empty($translations)) {
            return;
        }

        $link = new \Link();
        foreach ($previousSlugs as $langId => $previousSlug) {
            $translation = $translations[$langId] ?? null;
            if (!is_array($translation)) {
                continue;
            }

            $newSlug = trim((string) ($translation['link_rewrite'] ?? ''));
            if ('' === $newSlug || $newSlug === $previousSlug) {
                continue;
            }

            $sourceUrl = (string) $link->getModuleLink(
                'everpsblog',
                'post',
                [
                    'id_ever_post' => $postId,
                    'link_rewrite' => $previousSlug,
                ],
                true,
                (int) $langId,
                $shopId
            );
            $targetUrl = (string) $link->getModuleLink(
                'everpsblog',
                'post',
                [
                    'id_ever_post' => $postId,
                    'link_rewrite' => $newSlug,
                ],
                true,
                (int) $langId,
                $shopId
            );

            if ('' === $sourceUrl || '' === $targetUrl || $sourceUrl === $targetUrl) {
                continue;
            }

            $this->blogRedirectService->saveRedirect($sourceUrl, $targetUrl, $shopId, 'post', $postId);
        }
    }
}
