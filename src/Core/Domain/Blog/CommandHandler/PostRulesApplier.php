<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\CommandHandler;

use EverPsBlogPost;
use InvalidArgumentException;

class PostRulesApplier
{
    /**
     * @param array<string, mixed> $payload
     */
    public function apply(EverPsBlogPost $post, array $payload): void
    {
        $post->id_shop = (int) $payload['shop_id'];
        $post->id_author = (int) $payload['author_id'];
        $post->date_add = (string) $payload['date_add'];
        $post->date_upd = date('Y-m-d H:i:s');
        $post->indexable = (int) $payload['indexable'];
        $post->follow = (int) $payload['follow'];
        $post->sitemap = (int) $payload['sitemap'];
        $post->starred = (int) $payload['starred'];

        $post->id_default_category = $this->resolveDefaultCategory($payload);
        $post->post_categories = json_encode($this->resolveCategoryAssociations($payload, (int) $post->id_default_category));
        $post->allowed_groups = json_encode($payload['allowed_groups']);
        $post->post_tags = json_encode($payload['post_tags']);
        $post->post_products = json_encode($payload['post_products']);
        $post->post_status = $this->resolveStatus($payload);
        $post->psswd = $this->resolvePassword($payload, $post->post_status);

        foreach ($payload['translations'] as $idLang => $translation) {
            $post->title[$idLang] = $translation['title'];
            $post->content[$idLang] = $translation['content'];
            $post->excerpt[$idLang] = $translation['excerpt'];
            $post->meta_title[$idLang] = $translation['meta_title'];
            $post->meta_description[$idLang] = $translation['meta_description'];
            $post->link_rewrite[$idLang] = $translation['link_rewrite'];
        }
    }

    private function resolveDefaultCategory(array $payload): int
    {
        $defaultCategoryId = (int) $payload['default_category_id'];

        if (!$defaultCategoryId) {
            return (int) $payload['unclassed_category_id'];
        }

        if ($defaultCategoryId === (int) $payload['root_category_id']) {
            throw new InvalidArgumentException('Default category cannot be root category.');
        }

        return $defaultCategoryId;
    }

    private function resolveCategoryAssociations(array $payload, int $defaultCategoryId): array
    {
        $categories = $payload['post_categories'];
        if (!in_array($defaultCategoryId, $categories)) {
            $categories[] = $defaultCategoryId;
        }

        return array_values(array_unique(array_map('intval', $categories)));
    }

    private function resolveStatus(array $payload): string
    {
        if ((string) $payload['date_add'] > date('Y-m-d H:i:s')) {
            return 'planned';
        }

        return (string) $payload['post_status'];
    }

    private function resolvePassword(array $payload, string $status): ?string
    {
        if ($status !== 'protected' || empty($payload['password'])) {
            return null;
        }

        return md5(_COOKIE_KEY_ . $payload['password']);
    }
}
