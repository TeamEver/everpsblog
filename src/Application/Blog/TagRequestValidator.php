<?php

namespace PrestaShop\Module\Everpsblog\Application\Blog;

class TagRequestValidator extends AbstractRequestValidator
{
    public function validate(array $requestData): array
    {
        $this->resetErrors();

        $this->ensureDefaultTitle($requestData);
        $requestData = $this->normalizeSeoFields($requestData);

        $this->throwIfInvalid();

        return $requestData;
    }
}
