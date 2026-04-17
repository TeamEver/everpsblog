<?php

namespace PrestaShop\Module\Everpsblog\Application\Blog;

use InvalidArgumentException;

class RequestValidationException extends InvalidArgumentException
{
    /** @var array<string, string[]> */
    private $fieldErrors;

    /** @var string[] */
    private $globalErrors;

    /**
     * @param array<string, string[]> $fieldErrors
     * @param string[] $globalErrors
     */
    public function __construct(array $fieldErrors = [], array $globalErrors = [])
    {
        $this->fieldErrors = $fieldErrors;
        $this->globalErrors = $globalErrors;

        parent::__construct($this->buildMessage());
    }

    /**
     * @return array<string, string[]>
     */
    public function getFieldErrors(): array
    {
        return $this->fieldErrors;
    }

    /**
     * @return string[]
     */
    public function getGlobalErrors(): array
    {
        return $this->globalErrors;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'global' => $this->globalErrors,
            'fields' => $this->fieldErrors,
        ];
    }

    private function buildMessage(): string
    {
        $parts = [];

        foreach ($this->globalErrors as $error) {
            $parts[] = $error;
        }

        foreach ($this->fieldErrors as $field => $errors) {
            foreach ($errors as $error) {
                $parts[] = sprintf('%s: %s', $field, $error);
            }
        }

        if (empty($parts)) {
            return 'Invalid request data.';
        }

        return implode(' ', $parts);
    }
}
