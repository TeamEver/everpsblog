<?php

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Form\Type\Admin\ConfigurationType;
use PrestaShop\Module\Everpsblog\Service\ContextStateService;
use PrestaShop\Module\Everpsblog\Service\WordPressRestImporter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigurationController extends AbstractDomainController
{
    /** @var WordPressRestImporter */
    private $wordPressRestImporter;

    public function __construct(ContextStateService $contextStateService, WordPressRestImporter $wordPressRestImporter)
    {
        parent::__construct($contextStateService);
        $this->wordPressRestImporter = $wordPressRestImporter;
    }

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
            'header_bg_color' => $this->getHeaderBackgroundColor(),
            'wordpress_api_url' => (string) \Configuration::get('EVER_WP_API_URL'),
            'wordpress_api_user' => (string) \Configuration::get('EVER_WP_API_USER'),
            'wordpress_api_password' => (string) \Configuration::get('EVER_WP_API_PASSWORD'),
            'wordpress_import_post_status' => (string) (\Configuration::get('EVERBLOG_IMPORT_POST_STATE') ?: 'published'),
            'wordpress_enable_authors' => $this->getBooleanConfiguration('EVERBLOG_ENABLE_AUTHORS', true),
            'wordpress_enable_categories' => $this->getBooleanConfiguration('EVERBLOG_ENABLE_CATS', true),
            'wordpress_enable_tags' => $this->getBooleanConfiguration('EVERBLOG_ENABLE_TAGS', true),
        ];
        $configurationData = array_merge($configurationData, $this->getLocalizedBlogContentData());

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
            \Configuration::updateValue('EVERBLOG_HEADER_BG_COLOR', $this->normalizeHeaderBackgroundColor((string) ($formData['header_bg_color'] ?? '')));
            \Configuration::updateValue('EVERBLOG_TOP_TEXT', $this->extractLocalizedFormData($formData, 'top_text'), true);
            \Configuration::updateValue('EVERBLOG_BOTTOM_TEXT', $this->extractLocalizedFormData($formData, 'bottom_text'), true);
            \Configuration::updateValue('EVER_WP_API_URL', trim((string) $formData['wordpress_api_url']));
            \Configuration::updateValue('EVER_WP_API_USER', trim((string) $formData['wordpress_api_user']));
            \Configuration::updateValue('EVER_WP_API_PASSWORD', (string) $formData['wordpress_api_password']);
            \Configuration::updateValue('EVERBLOG_IMPORT_POST_STATE', (string) $formData['wordpress_import_post_status']);
            \Configuration::updateValue('EVERBLOG_ENABLE_AUTHORS', (bool) $formData['wordpress_enable_authors']);
            \Configuration::updateValue('EVERBLOG_ENABLE_CATS', (bool) $formData['wordpress_enable_categories']);
            \Configuration::updateValue('EVERBLOG_ENABLE_TAGS', (bool) $formData['wordpress_enable_tags']);

            $this->addFlash('success', 'La configuration a été enregistrée.');

            if ($request->request->has('import_wordpress_blog')) {
                $this->importWordPressBlog($formData);
            }

            return $this->redirectToRoute('everpsblog_admin_dashboard');
        }

        return $this->render('@Modules/everpsblog/views/templates/admin/modern/configuration.html.twig', [
            'currentResource' => 'configuration',
            'navigationLinks' => $this->getAdminNavigationLinks(),
            'configurationForm' => $form->createView(),
        ]);
    }

    private function getLocalizedBlogContentData(): array
    {
        $topText = $this->getConfigInMultipleLangs('EVERBLOG_TOP_TEXT');
        $bottomText = $this->getConfigInMultipleLangs('EVERBLOG_BOTTOM_TEXT');
        $data = [];

        foreach (\Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];
            $data['top_text_' . $idLang] = (string) ($topText[$idLang] ?? '');
            $data['bottom_text_' . $idLang] = (string) ($bottomText[$idLang] ?? '');
        }

        return $data;
    }

    private function extractLocalizedFormData(array $formData, string $prefix): array
    {
        $values = [];
        foreach (\Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];
            $values[$idLang] = (string) ($formData[$prefix . '_' . $idLang] ?? '');
        }

        return $values;
    }

    private function getConfigInMultipleLangs(string $key): array
    {
        if (is_callable(['Configuration', 'getConfigInMultipleLangs'])) {
            return (array) \Configuration::getConfigInMultipleLangs($key);
        }

        $values = [];
        foreach (\Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];
            $values[$idLang] = (string) \Configuration::get($key, $idLang);
        }

        return $values;
    }

    private function importWordPressBlog(array $formData): void
    {
        $apiUrl = trim((string) ($formData['wordpress_api_url'] ?? ''));
        if ('' === $apiUrl) {
            $this->addFlash('error', 'Renseignez l\'URL WordPress avant de lancer l\'import.');

            return;
        }

        try {
            $stats = $this->wordPressRestImporter->import(
                $apiUrl,
                trim((string) ($formData['wordpress_api_user'] ?? '')),
                (string) ($formData['wordpress_api_password'] ?? ''),
                $this->getContextShopId(),
                $this->getContextLangId()
            );

            $this->addFlash(
                'success',
                sprintf(
                    'Import WordPress termine : %d article(s) cree(s), %d article(s) mis a jour, %d categorie(s), %d tag(s), %d auteur(s), %d image(s), %d redirection(s), %d element(s) ignore(s).',
                    (int) $stats['posts_created'],
                    (int) $stats['posts_updated'],
                    (int) $stats['categories'],
                    (int) $stats['tags'],
                    (int) $stats['authors'],
                    (int) $stats['images'],
                    (int) ($stats['redirects'] ?? 0),
                    (int) $stats['skipped']
                )
            );
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog('EverPsBlog WordPress import failed: ' . $exception->getMessage(), 3);
            $this->addFlash('error', 'Import WordPress impossible : ' . $this->describeException($exception));
        }
    }

    private function getHeaderBackgroundColor(): string
    {
        return $this->normalizeHeaderBackgroundColor((string) \Configuration::get('EVERBLOG_HEADER_BG_COLOR'));
    }

    private function normalizeHeaderBackgroundColor(string $color): string
    {
        $color = trim($color);

        return (bool) preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#0a0f54';
    }

    private function getBooleanConfiguration(string $key, bool $default): bool
    {
        $value = \Configuration::get($key);
        if (false === $value || null === $value || '' === $value) {
            return $default;
        }

        return (bool) $value;
    }
}
