<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Controller\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\Everpsblog\Form\Type\Admin\ConfigurationType;
use PrestaShop\Module\Everpsblog\Service\BlogSitemapService;
use PrestaShop\Module\Everpsblog\Service\ContextStateService;
use PrestaShop\Module\Everpsblog\Service\ModuleTranslationCatalogService;
use PrestaShop\Module\Everpsblog\Service\WordPressRestImporter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigurationController extends AbstractDomainController
{
    /** @var WordPressRestImporter */
    private $wordPressRestImporter;
    /** @var BlogSitemapService */
    private $blogSitemapService;
    /** @var ModuleTranslationCatalogService */
    private $translationCatalogService;

    public function __construct(
        ContextStateService $contextStateService,
        WordPressRestImporter $wordPressRestImporter,
        BlogSitemapService $blogSitemapService,
        ModuleTranslationCatalogService $translationCatalogService
    ) {
        parent::__construct($contextStateService);
        $this->wordPressRestImporter = $wordPressRestImporter;
        $this->blogSitemapService = $blogSitemapService;
        $this->translationCatalogService = $translationCatalogService;
    }

    public function indexAction(Request $request): Response
    {
        $configurationData = [
            'route' => (string) \Configuration::get('EVERPSBLOG_ROUTE'),
            'allow_comments' => (bool) \Configuration::get('EVERBLOG_ALLOW_COMMENTS'),
            'check_comments' => (bool) \Configuration::get('EVERBLOG_CHECK_COMMENTS'),
            'rss_enabled' => $this->getBooleanConfiguration('EVERBLOG_RSS', false),
            'posts_per_page' => (int) \Configuration::get('EVERPSBLOG_PAGINATION'),
            'home_posts' => (int) \Configuration::get('EVERPSBLOG_HOME_NBR'),
            'product_posts' => (int) \Configuration::get('EVERPSBLOG_PRODUCT_NBR'),
            'excerpt_length' => (int) \Configuration::get('EVERPSBLOG_EXCERPT'),
            'title_length' => (int) \Configuration::get('EVERPSBLOG_TITLE_LENGTH'),
            'default_author_id' => (int) \Configuration::get('EVERBLOG_DEFAULT_AUTHOR_ID'),
            'header_bg_color' => $this->getHeaderBackgroundColor(),
            'header_title_color' => $this->getHeaderTitleColor(),
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
            'author_choices' => $this->getAuthorChoices(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            \Configuration::updateValue('EVERPSBLOG_ROUTE', (string) $formData['route']);
            \Configuration::updateValue('EVERBLOG_ALLOW_COMMENTS', (bool) $formData['allow_comments']);
            \Configuration::updateValue('EVERBLOG_CHECK_COMMENTS', (bool) $formData['check_comments']);
            \Configuration::updateValue('EVERBLOG_RSS', (bool) $formData['rss_enabled']);
            \Configuration::updateValue('EVERPSBLOG_PAGINATION', (int) $formData['posts_per_page']);
            \Configuration::updateValue('EVERPSBLOG_HOME_NBR', (int) $formData['home_posts']);
            \Configuration::updateValue('EVERPSBLOG_PRODUCT_NBR', (int) $formData['product_posts']);
            \Configuration::updateValue('EVERPSBLOG_EXCERPT', (int) $formData['excerpt_length']);
            \Configuration::updateValue('EVERPSBLOG_TITLE_LENGTH', (int) $formData['title_length']);
            \Configuration::updateValue('EVERBLOG_DEFAULT_AUTHOR_ID', (int) $formData['default_author_id']);
            \Configuration::updateValue('EVERBLOG_HEADER_BG_COLOR', $this->normalizeHexColor((string) ($formData['header_bg_color'] ?? ''), '#0a0f54'));
            \Configuration::updateValue('EVERBLOG_HEADER_TITLE_COLOR', $this->normalizeHexColor((string) ($formData['header_title_color'] ?? ''), '#ffffff'));
            \Configuration::updateValue('EVERBLOG_TOP_TEXT', $this->extractLocalizedFormData($formData, 'top_text'), true);
            \Configuration::updateValue('EVERBLOG_BOTTOM_TEXT', $this->extractLocalizedFormData($formData, 'bottom_text'), true);
            \Configuration::updateValue('EVER_WP_API_URL', trim((string) $formData['wordpress_api_url']));
            \Configuration::updateValue('EVER_WP_API_USER', trim((string) $formData['wordpress_api_user']));
            \Configuration::updateValue('EVER_WP_API_PASSWORD', (string) $formData['wordpress_api_password']);
            \Configuration::updateValue('EVERBLOG_IMPORT_POST_STATE', (string) $formData['wordpress_import_post_status']);
            \Configuration::updateValue('EVERBLOG_ENABLE_AUTHORS', (bool) $formData['wordpress_enable_authors']);
            \Configuration::updateValue('EVERBLOG_ENABLE_CATS', (bool) $formData['wordpress_enable_categories']);
            \Configuration::updateValue('EVERBLOG_ENABLE_TAGS', (bool) $formData['wordpress_enable_tags']);

            $reassignedPosts = $this->assignOrphanPostsToDefaultAuthor((int) $formData['default_author_id']);
            if ($reassignedPosts > 0) {
                $this->addFlash(
                    'success',
                    $this->transAdmin(
                        '%count% orphan post(s) were reassigned to the default author.',
                        ['%count%' => $reassignedPosts]
                    )
                );
            }

            $this->addFlash('success', $this->transAdmin('Settings saved.'));

            if ($request->request->has('import_wordpress_blog')) {
                $this->importWordPressBlog($formData);
            } else {
                $this->refreshSitemapsAfterBackOfficeChange($this->blogSitemapService);
            }

            return $this->redirectToRoute('everpsblog_admin_dashboard');
        }

        return $this->render('@Modules/everpsblog/views/templates/admin/modern/configuration.html.twig', [
            'currentResource' => 'configuration',
            'navigationLinks' => $this->getAdminNavigationLinks(),
            'configurationForm' => $form->createView(),
            'sitemapUrls' => $this->blogSitemapService->getSitemapIndexes($this->getContextShopId()),
            'translationExportUrl' => $this->generateUrl('everpsblog_admin_translation_export'),
            'translationImportUrl' => $this->generateUrl('everpsblog_admin_translation_import'),
            'qcdPageBuilderTargets' => $this->buildQcdPageBuilderTargets('everpsblog_configuration', 1, [
                'top_text' => $this->transAdmin('Edit the top blog content with Page Builder'),
                'bottom_text' => $this->transAdmin('Edit the bottom blog content with Page Builder'),
            ]),
        ]);
    }

    public function exportTranslationsAction(): Response
    {
        $payload = $this->translationCatalogService->export();
        $response = new Response(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="everpsblog-translations-' . date('Ymd-His') . '.json"');

        return $response;
    }

    public function importTranslationsAction(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('everpsblog_translation_import', (string) $request->request->get('_token'))) {
            $this->addFlash('error', $this->transAdmin('Invalid security token.'));

            return $this->redirectToRoute('everpsblog_admin_dashboard');
        }

        $file = $request->files->get('translation_file');
        if (null === $file || !is_callable([$file, 'getRealPath'])) {
            $this->addFlash('error', $this->transAdmin('Please select a translation export file.'));

            return $this->redirectToRoute('everpsblog_admin_dashboard');
        }

        $path = (string) $file->getRealPath();
        $content = is_file($path) ? file_get_contents($path) : false;
        if (false === $content || '' === trim((string) $content)) {
            $this->addFlash('error', $this->transAdmin('The selected translation file is empty.'));

            return $this->redirectToRoute('everpsblog_admin_dashboard');
        }

        try {
            $stats = $this->translationCatalogService->importFromJson((string) $content);
            $this->addFlash(
                'success',
                $this->transAdmin(
                    'Translations imported: %imported% item(s), %skipped% skipped.',
                    [
                        '%imported%' => (int) $stats['imported'],
                        '%skipped%' => (int) $stats['skipped'],
                    ]
                )
            );
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog('EverPsBlog translation import failed: ' . $exception->getMessage(), 3);
            $this->addFlash('error', $this->transAdmin('Unable to import translations: %error%', ['%error%' => $this->describeException($exception)]));
        }

        return $this->redirectToRoute('everpsblog_admin_dashboard');
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

    /**
     * @return array<string,int>
     */
    private function getAuthorChoices(): array
    {
        $shopId = $this->getContextShopId();
        $rows = \Db::getInstance()->executeS(
            'SELECT DISTINCT a.id_ever_author, a.nickhandle
             FROM `' . _DB_PREFIX_ . 'ever_blog_author` a
             LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_author_shop` aps
                ON aps.id_ever_author = a.id_ever_author
             WHERE a.id_shop = ' . (int) $shopId . '
                OR aps.id_shop = ' . (int) $shopId . '
             ORDER BY a.nickhandle ASC'
        ) ?: [];

        $choices = [];
        foreach ($rows as $row) {
            $authorId = (int) ($row['id_ever_author'] ?? 0);
            $nickhandle = trim((string) ($row['nickhandle'] ?? ''));
            if ($authorId <= 0) {
                continue;
            }
            $choices[$nickhandle ?: '#' . $authorId] = $authorId;
        }

        return $choices;
    }

    private function assignOrphanPostsToDefaultAuthor(int $defaultAuthorId): int
    {
        if ($defaultAuthorId <= 0) {
            return 0;
        }

        $shopId = $this->getContextShopId();
        $authorExists = (bool) \Db::getInstance()->getValue(
            'SELECT a.id_ever_author
             FROM `' . _DB_PREFIX_ . 'ever_blog_author` a
             LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_author_shop` aps
                ON aps.id_ever_author = a.id_ever_author
             WHERE a.id_ever_author = ' . (int) $defaultAuthorId . '
                AND (a.id_shop = ' . (int) $shopId . ' OR aps.id_shop = ' . (int) $shopId . ')'
        );

        if (!$authorExists) {
            return 0;
        }

        $db = \Db::getInstance();
        $db->update(
            'ever_blog_post',
            ['id_author' => $defaultAuthorId],
            'id_author = 0 AND id_shop = ' . (int) $shopId
        );

        return (int) $db->Affected_Rows();
    }

    private function importWordPressBlog(array $formData): void
    {
        $apiUrl = trim((string) ($formData['wordpress_api_url'] ?? ''));
        if ('' === $apiUrl) {
            $this->addFlash('error', $this->transAdmin('Enter the WordPress URL before starting the import.'));

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
                $this->transAdmin(
                    'WordPress import completed: %created% created post(s), %updated% updated post(s), %categories% category item(s), %tags% tag item(s), %authors% author item(s), %images% image(s), %redirects% redirect(s), %skipped% skipped item(s).',
                    [
                        '%created%' => (int) $stats['posts_created'],
                        '%updated%' => (int) $stats['posts_updated'],
                        '%categories%' => (int) $stats['categories'],
                        '%tags%' => (int) $stats['tags'],
                        '%authors%' => (int) $stats['authors'],
                        '%images%' => (int) $stats['images'],
                        '%redirects%' => (int) ($stats['redirects'] ?? 0),
                        '%skipped%' => (int) $stats['skipped'],
                    ]
                )
            );
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog('EverPsBlog WordPress import failed: ' . $exception->getMessage(), 3);
            $this->addFlash('error', $this->transAdmin('Unable to import WordPress content: %error%', ['%error%' => $this->describeException($exception)]));
        }
    }

    private function getHeaderBackgroundColor(): string
    {
        return $this->normalizeHexColor((string) \Configuration::get('EVERBLOG_HEADER_BG_COLOR'), '#0a0f54');
    }

    private function getHeaderTitleColor(): string
    {
        return $this->normalizeHexColor((string) \Configuration::get('EVERBLOG_HEADER_TITLE_COLOR'), '#ffffff');
    }

    private function normalizeHexColor(string $color, string $default): string
    {
        $color = trim($color);

        return (bool) preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : $default;
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
