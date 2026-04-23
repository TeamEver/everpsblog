<?php

namespace PrestaShop\Module\Everpsblog\Application\Blog;

class CommentRequestValidator extends AbstractRequestValidator
{
    public function validate(array $requestData): array
    {
        $this->resetErrors();

        $postId = (int) ($requestData['id_ever_post'] ?? 0);
        if ($postId <= 0) {
            $this->addFieldError('id_ever_post', $this->transAdmin('This field is required.'));
        } elseif (!$this->existsInModuleTable('ever_blog_post', 'id_ever_post', $postId)) {
            $this->addFieldError('id_ever_post', $this->transAdmin('Post not found (id: %id%).', ['%id%' => $postId]));
        }

        $nickname = trim((string) ($requestData['nickname'] ?? $requestData['name'] ?? ''));
        if ('' === $nickname) {
            $this->addFieldError('nickname', $this->transAdmin('Name is required.'));
        }

        $content = trim((string) ($requestData['content'] ?? $requestData['comment'] ?? ''));
        if ('' === $content) {
            $this->addFieldError('content', $this->transAdmin('Comment is required.'));
        }

        $requestData['name'] = $nickname;
        $requestData['comment'] = $content;

        $this->throwIfInvalid();

        return $requestData;
    }
}
