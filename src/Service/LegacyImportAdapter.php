<?php

namespace PrestaShop\Module\Everpsblog\Service;

/**
 * LEGACY import adapter.
 *
 * TODO PrestaShop 9: rewrite import pipelines (RSS/JSON/WordPress) to use the
 *      new Doctrine-backed services instead of the removed ObjectModel classes
 *      (EverPsBlogCategory, EverPsBlogTag, EverPsBlogAuthor, EverPsBlogPost).
 *
 * @deprecated Only used by the legacy import handlers in everpsblog.php. Calling
 *             any method here triggers a "Class not found" error because the
 *             legacy ObjectModel classes have been removed during refactoring.
 */
class LegacyImportAdapter
{
    private $blogImageService;

    public function __construct(BlogImageService $blogImageService)
    {
        $this->blogImageService = $blogImageService;
    }

    public function getOrCreateCategoryByLinkRewrite($linkRewrite)
    {
        $this->throwMigrationRequired('getOrCreateCategoryByLinkRewrite');
    }

    public function getOrCreateTagByLinkRewrite($linkRewrite)
    {
        $this->throwMigrationRequired('getOrCreateTagByLinkRewrite');
    }

    public function getOrCreateAuthorByNickhandle($nickhandle)
    {
        $this->throwMigrationRequired('getOrCreateAuthorByNickhandle');
    }

    public function getOrCreatePostByLinkRewrite($linkRewrite)
    {
        $this->throwMigrationRequired('getOrCreatePostByLinkRewrite');
    }

    public function getOrCreatePostImage($postId, $shopId)
    {
        $image = $this->blogImageService->getBlogImage((int) $postId, (int) $shopId, 'post');
        if (!\Validate::isLoadedObject($image)) {
            $image = $this->blogImageService->createImageModel();
        }

        return $image;
    }

    /**
     * @throws \RuntimeException
     */
    private function throwMigrationRequired(string $method): void
    {
        throw new \RuntimeException(sprintf(
            'LegacyImportAdapter::%s() is not available anymore. The ObjectModel classes '
            . '(EverPsBlogCategory, EverPsBlogTag, EverPsBlogAuthor, EverPsBlogPost) were '
            . 'removed during the PrestaShop 9 refactor. Import features need to be '
            . 'reimplemented on top of the new Doctrine-based repositories.',
            $method
        ));
    }
}
