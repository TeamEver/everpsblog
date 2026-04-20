<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Form\Type\Admin\ConfigurationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigurationController extends AbstractDomainController
{
    public function indexAction(Request $request): Response
    {
        $configurationData = [
            'route' => (string) \Configuration::get('EVERPSBLOG_ROUTE'),
            'allow_comments' => (bool) \Configuration::get('EVERBLOG_ALLOW_COMMENTS'),
            'check_comments' => (bool) \Configuration::get('EVERBLOG_CHECK_COMMENTS'),
            'posts_per_page' => (int) \Configuration::get('EVERPSBLOG_PAGINATION'),
            'home_posts' => (int) \Configuration::get('EVERPSBLOG_HOME_NBR'),
            'product_posts' => (int) \Configuration::get('EVERPSBLOG_PRODUCT_NBR'),
            'excerpt_length' => (int) \Configuration::get('EVERPSBLOG_EXCERPT'),
            'title_length' => (int) \Configuration::get('EVERPSBLOG_TITLE_LENGTH'),
        ];

        $form = $this->createForm(ConfigurationType::class, $configurationData, [
            'method' => Request::METHOD_POST,
            'action' => $this->generateUrl('everpsblog_admin_dashboard'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            \Configuration::updateValue('EVERPSBLOG_ROUTE', (string) $formData['route']);
            \Configuration::updateValue('EVERBLOG_ALLOW_COMMENTS', (bool) $formData['allow_comments']);
            \Configuration::updateValue('EVERBLOG_CHECK_COMMENTS', (bool) $formData['check_comments']);
            \Configuration::updateValue('EVERPSBLOG_PAGINATION', (int) $formData['posts_per_page']);
            \Configuration::updateValue('EVERPSBLOG_HOME_NBR', (int) $formData['home_posts']);
            \Configuration::updateValue('EVERPSBLOG_PRODUCT_NBR', (int) $formData['product_posts']);
            \Configuration::updateValue('EVERPSBLOG_EXCERPT', (int) $formData['excerpt_length']);
            \Configuration::updateValue('EVERPSBLOG_TITLE_LENGTH', (int) $formData['title_length']);

            $this->addFlash('success', 'La configuration a été enregistrée.');

            return $this->redirectToRoute('everpsblog_admin_dashboard');
        }

        return $this->render('@Modules/everpsblog/views/templates/admin/modern/configuration.html.twig', [
            'currentResource' => 'configuration',
            'navigationLinks' => $this->getAdminNavigationLinks(),
            'configurationForm' => $form->createView(),
        ]);
    }
}
