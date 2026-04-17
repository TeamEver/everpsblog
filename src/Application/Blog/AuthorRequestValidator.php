<?php

namespace PrestaShop\Module\Everpsblog\Application\Blog;

class AuthorRequestValidator extends AbstractRequestValidator
{
    public function validate(array $requestData): array
    {
        $this->resetErrors();

        $nickhandle = trim((string) ($requestData['nickhandle'] ?? ''));
        if ('' === $nickhandle) {
            $this->addFieldError('nickhandle', 'Le pseudo est obligatoire.');
        }

        $requestData = $this->normalizeSeoFields($requestData, 'meta_title_');

        $employeeId = (int) ($requestData['id_employee'] ?? 0);
        if ($employeeId > 0 && !$this->existsInPrestashopTable('employee', 'id_employee', $employeeId)) {
            $this->addFieldError('id_employee', sprintf('Employé introuvable (id: %d).', $employeeId));
        }

        $this->throwIfInvalid();

        return $requestData;
    }
}
