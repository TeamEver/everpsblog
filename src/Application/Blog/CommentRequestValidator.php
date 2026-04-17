<?php

namespace PrestaShop\Module\Everpsblog\Application\Blog;

class CommentRequestValidator extends AbstractRequestValidator
{
    public function validate(array $requestData): array
    {
        $this->resetErrors();

        $postId = (int) ($requestData['id_ever_post'] ?? 0);
        if ($postId <= 0) {
            $this->addFieldError('id_ever_post', 'Ce champ est obligatoire.');
        } elseif (!$this->existsInModuleTable('ever_blog_post', 'id_ever_post', $postId)) {
            $this->addFieldError('id_ever_post', sprintf('Article introuvable (id: %d).', $postId));
        }

        $nickname = trim((string) ($requestData['nickname'] ?? $requestData['name'] ?? ''));
        if ('' === $nickname) {
            $this->addFieldError('nickname', 'Le nom est obligatoire.');
        }

        $content = trim((string) ($requestData['content'] ?? $requestData['comment'] ?? ''));
        if ('' === $content) {
            $this->addFieldError('content', 'Le commentaire est obligatoire.');
        }

        $requestData['name'] = $nickname;
        $requestData['comment'] = $content;

        $this->throwIfInvalid();

        return $requestData;
    }
}
