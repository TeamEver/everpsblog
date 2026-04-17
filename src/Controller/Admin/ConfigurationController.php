<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Security\BlogPermission;
use Symfony\Component\HttpFoundation\Response;

class ConfigurationController extends AbstractDomainController
{
    public function indexAction(): Response
    {
        $this->denyBlogAccess(BlogPermission::READ, BlogPermission::RES_POST);

        return $this->render('@Modules/everpsblog/views/templates/admin/modern/configuration.html.twig', [
            'postUrl' => $this->generateUrl('everpsblog_admin_post'),
            'categoryUrl' => $this->generateUrl('everpsblog_admin_category'),
            'tagUrl' => $this->generateUrl('everpsblog_admin_tag'),
            'authorUrl' => $this->generateUrl('everpsblog_admin_author'),
            'commentUrl' => $this->generateUrl('everpsblog_admin_comment'),
            'legacyConfigureUrl' => $this->generateUrl('admin_module_manage_action', [
                'module_name' => 'everpsblog',
                'action' => 'configure',
            ]),
        ]);
    }
}
