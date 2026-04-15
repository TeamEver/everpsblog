<?php

namespace PrestaShop\Module\Everpsblog\Service\Security;

use Configuration;
use Context;
use Profile;
use PrestaShop\Module\Everpsblog\Security\BlogPermission;
use Tab;

class BackOfficePermissionProvider
{
    private const CONFIG_KEY = 'EVERPSBLOG_PROFILE_PERMISSIONS';

    private const RESOURCE_TAB_MAP = [
        BlogPermission::RES_POST => 'AdminEverPsBlogPost',
        BlogPermission::RES_CATEGORY => 'AdminEverPsBlogCategory',
        BlogPermission::RES_TAG => 'AdminEverPsBlogTag',
        BlogPermission::RES_AUTHOR => 'AdminEverPsBlogAuthor',
        BlogPermission::RES_COMMENT => 'AdminEverPsBlogComment',
    ];

    public function isGranted(string $resource, string $permission): bool
    {
        $employee = Context::getContext()->employee;
        if (!$employee || !(int) $employee->id_profile) {
            return false;
        }

        $profileId = (int) $employee->id_profile;
        $grantedActions = $this->getProfilePermissions($profileId, $resource);

        return in_array($permission, $grantedActions, true);
    }

    /**
     * @return string[]
     */
    private function getProfilePermissions(int $profileId, string $resource): array
    {
        $configuredPermissions = $this->getConfiguredPermissions();
        if (isset($configuredPermissions[$profileId][$resource]) && is_array($configuredPermissions[$profileId][$resource])) {
            return $configuredPermissions[$profileId][$resource];
        }

        return $this->buildFallbackPermissions($profileId, $resource);
    }

    /**
     * @return array<int, array<string, array<int, string>>>
     */
    private function getConfiguredPermissions(): array
    {
        $raw = (string) Configuration::get(self::CONFIG_KEY);
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return string[]
     */
    private function buildFallbackPermissions(int $profileId, string $resource): array
    {
        if (!isset(self::RESOURCE_TAB_MAP[$resource])) {
            return [];
        }

        $tabId = (int) Tab::getIdFromClassName(self::RESOURCE_TAB_MAP[$resource]);
        if ($tabId <= 0) {
            return [];
        }

        $profileAccess = Profile::getProfileAccess($profileId, $tabId);
        if (!is_array($profileAccess)) {
            return [];
        }

        $permissions = [];

        if (!empty($profileAccess['view'])) {
            $permissions[] = BlogPermission::READ;
        }
        if (!empty($profileAccess['add'])) {
            $permissions[] = BlogPermission::CREATE;
        }
        if (!empty($profileAccess['edit'])) {
            $permissions[] = BlogPermission::UPDATE;
            $permissions[] = BlogPermission::PUBLISH;
        }
        if (!empty($profileAccess['delete'])) {
            $permissions[] = BlogPermission::DELETE;
        }

        return $permissions;
    }
}
