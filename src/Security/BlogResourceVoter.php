<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Security;

use PrestaShop\Module\Everpsblog\Service\Security\BackOfficePermissionProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

if (!defined('_PS_VERSION_')) {
    exit;
}


class BlogResourceVoter extends Voter
{
    /** @var BackOfficePermissionProvider */
    private $permissionProvider;

    public function __construct(BackOfficePermissionProvider $permissionProvider)
    {
        $this->permissionProvider = $permissionProvider;
    }

    protected function supports($attribute, $subject): bool
    {
        if (!is_string($subject)) {
            return false;
        }

        return in_array($attribute, [
            BlogPermission::READ,
            BlogPermission::CREATE,
            BlogPermission::UPDATE,
            BlogPermission::DELETE,
            BlogPermission::PUBLISH,
        ], true);
    }

    /**
     * @param string $attribute
     * @param string $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        return $this->permissionProvider->isGranted($subject, $attribute);
    }
}
