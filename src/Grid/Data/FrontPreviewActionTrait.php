<?php

namespace PrestaShop\Module\Everpsblog\Grid\Data;

trait FrontPreviewActionTrait
{
    private function buildFrontPreviewUrl(
        string $controller,
        string $idParameter,
        int $id,
        string $linkRewrite,
        int $shopId,
        int $langId
    ): string {
        $linkRewrite = trim($linkRewrite);
        if ($id <= 0 || '' === $linkRewrite) {
            return '';
        }

        return $this->generateFrontModuleUrl($controller, [
            $idParameter => $id,
            'link_rewrite' => $linkRewrite,
            'preview' => $this->getPreviewToken(),
        ], $shopId, $langId);
    }

    private function buildPostPreviewUrl(int $postId, int $shopId, int $langId): string
    {
        return $this->buildFrontPreviewUrl(
            'post',
            'id_ever_post',
            $postId,
            $this->resolveLinkRewriteFromDb('ever_blog_post_lang', 'id_ever_post', $postId, $langId),
            $shopId,
            $langId
        );
    }

    private function buildCategoryPreviewUrl(int $categoryId, int $shopId, int $langId): string
    {
        return $this->buildFrontPreviewUrl(
            'category',
            'id_ever_category',
            $categoryId,
            $this->resolveLinkRewriteFromDb('ever_blog_category_lang', 'id_ever_category', $categoryId, $langId),
            $shopId,
            $langId
        );
    }

    private function buildTagPreviewUrl(int $tagId, int $shopId, int $langId): string
    {
        return $this->buildFrontPreviewUrl(
            'tag',
            'id_ever_tag',
            $tagId,
            $this->resolveLinkRewriteFromDb('ever_blog_tag_lang', 'id_ever_tag', $tagId, $langId),
            $shopId,
            $langId
        );
    }

    private function buildAuthorPreviewUrl(int $authorId, int $shopId, int $langId): string
    {
        return $this->buildFrontPreviewUrl(
            'author',
            'id_ever_author',
            $authorId,
            $this->resolveLinkRewriteFromDb('ever_blog_author_lang', 'id_ever_author', $authorId, $langId),
            $shopId,
            $langId
        );
    }

    private function buildCommentPreviewUrl(int $commentId, int $postId, int $shopId, int $langId): string
    {
        $url = $this->buildPostPreviewUrl($postId, $shopId, $langId);
        if ('' === $url || $commentId <= 0) {
            return $url;
        }

        return $url . '#' . $commentId;
    }

    private function resolveLinkRewriteFromDb(string $table, string $idColumn, int $id, int $langId): string
    {
        if ($id <= 0 || $langId <= 0) {
            return '';
        }

        return (string) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT `link_rewrite`
             FROM `' . _DB_PREFIX_ . bqSQL($table) . '`
             WHERE `' . bqSQL($idColumn) . '` = ' . (int) $id . '
             AND `id_lang` = ' . (int) $langId
        );
    }

    private function generateFrontModuleUrl(string $controller, array $params, int $shopId, int $langId): string
    {
        $context = \Context::getContext();
        if (!isset($context->link) || !$context->link instanceof \Link) {
            $context->link = new \Link();
        }

        try {
            return (string) $context->link->getModuleLink(
                'everpsblog',
                $controller,
                $params,
                true,
                $langId,
                $shopId
            );
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog('[everpsblog][preview-link] ' . $exception->getMessage(), 2);

            return '';
        }
    }

    private function getPreviewToken(): string
    {
        return (string) \Tools::encrypt('everpsblog/preview');
    }
}
