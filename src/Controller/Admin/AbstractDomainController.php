<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Controller\Admin;

use PrestaShop\Module\Everpsblog\Grid\Data\FrontPreviewActionTrait;
use PrestaShop\Module\Everpsblog\Service\BlogScheduledTaskRunner;
use PrestaShop\Module\Everpsblog\Service\BlogSitemapService;
use PrestaShop\Module\Everpsblog\Service\ContextStateService;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

if (!defined('_PS_VERSION_')) {
    exit;
}


abstract class AbstractDomainController extends FrameworkBundleAdminController
{
    use FrontPreviewActionTrait;

    /** @var ContextStateService */
    protected $contextStateService;
    /** @var Environment|null */
    private $templateRenderer;
    /** @var FormFactoryInterface|null */
    private $formFactory;
    /** @var RouterInterface|null */
    private $router;
    /** @var RequestStack|null */
    private $requestStack;
    /** @var CsrfTokenManagerInterface|null */
    private $csrfTokenManager;
    /** @var TranslatorInterface|null */
    private $translator;
    /** @var BlogScheduledTaskRunner|null */
    private $scheduledTaskRunner;

    public function __construct(ContextStateService $contextStateService)
    {
        $this->contextStateService = $contextStateService;
    }

    public function setTemplateRenderer(Environment $templateRenderer): void
    {
        $this->templateRenderer = $templateRenderer;
    }

    public function setFormFactory(FormFactoryInterface $formFactory): void
    {
        $this->formFactory = $formFactory;
    }

    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    public function setCsrfTokenManager(CsrfTokenManagerInterface $csrfTokenManager): void
    {
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    protected function createForm(string $type, $data = null, array $options = []): FormInterface
    {
        if (!$this->formFactory instanceof FormFactoryInterface) {
            throw new \LogicException('You cannot create the Ever PS Blog admin form because the form factory is not injected.');
        }

        return $this->formFactory->create($type, $data, $options);
    }

    protected function generateUrl(string $route, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        if (!$this->router instanceof RouterInterface) {
            throw new \LogicException('You cannot generate the Ever PS Blog admin URL because the router is not injected.');
        }

        return $this->router->generate($route, $parameters, $referenceType);
    }

    protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
    {
        return new RedirectResponse($this->generateUrl($route, $parameters), $status);
    }

    protected function addFlash(string $type, $message): void
    {
        $session = $this->getSession();
        if (!method_exists($session, 'getFlashBag')) {
            throw new \LogicException('You cannot add an Ever PS Blog admin flash message because the session flash bag is not available.');
        }

        $session->getFlashBag()->add($type, $message);
    }

    protected function isCsrfTokenValid(string $id, ?string $token): bool
    {
        if (!$this->csrfTokenManager instanceof CsrfTokenManagerInterface) {
            throw new \LogicException('You cannot validate the Ever PS Blog admin CSRF token because the token manager is not injected.');
        }

        return $this->csrfTokenManager->isTokenValid(new CsrfToken($id, $token));
    }

    protected function trans($key, $domain, array $parameters = [])
    {
        if (!$this->translator instanceof TranslatorInterface) {
            return strtr((string) $key, $parameters);
        }

        return $this->translator->trans((string) $key, $parameters, (string) $domain);
    }

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $hasInvalidSubmittedForm = false;
        foreach ($parameters as $key => $value) {
            if (!$value instanceof FormInterface) {
                continue;
            }

            if ($value->isSubmitted() && !$value->isValid()) {
                $hasInvalidSubmittedForm = true;
            }

            $parameters[$key] = $value->createView();
        }

        if (!$this->templateRenderer instanceof Environment) {
            throw new \LogicException(
                'You cannot render the Ever PS Blog admin template because the Twig service is not injected.'
            );
        }

        $response = $response ?: new Response();
        if (Response::HTTP_OK === $response->getStatusCode() && $hasInvalidSubmittedForm) {
            $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $response->setContent((string) $this->templateRenderer->render($view, $parameters));

        return $response;
    }

    protected function getContextShopId(): int
    {
        return $this->contextStateService->getShopId();
    }

    protected function getContextLangId(): int
    {
        return $this->contextStateService->getLanguageId();
    }

    protected function transAdmin(string $message, array $parameters = []): string
    {
        return $this->trans($message, 'Modules.Everpsblog.Admin', $parameters);
    }

    private function getSession(): SessionInterface
    {
        if (!$this->requestStack instanceof RequestStack) {
            throw new \LogicException('You cannot access the Ever PS Blog admin session because the request stack is not injected.');
        }

        return $this->requestStack->getSession();
    }

    /**
     * Build a debug-friendly description of a caught exception.
     * Always safe to display in the BO; includes class + message + root cause
     * so the operator can act on the error without grep-ing the PS logs.
     */
    protected function describeException(\Throwable $exception): string
    {
        $parts = [];
        $current = $exception;
        while (null !== $current) {
            $parts[] = sprintf(
                '%s: %s (@%s:%d)',
                (new \ReflectionClass($current))->getShortName(),
                $current->getMessage(),
                basename($current->getFile()),
                $current->getLine()
            );
            $current = $current->getPrevious();
        }

        return implode(' <= ', $parts);
    }

    protected function getAdminNavigationLinks(): array
    {
        return [
            ['key' => 'post', 'label' => 'Posts', 'url' => $this->generateUrl('everpsblog_admin_post')],
            ['key' => 'category', 'label' => 'Categories', 'url' => $this->generateUrl('everpsblog_admin_category')],
            ['key' => 'tag', 'label' => 'Tags', 'url' => $this->generateUrl('everpsblog_admin_tag')],
            ['key' => 'author', 'label' => 'Authors', 'url' => $this->generateUrl('everpsblog_admin_author')],
            ['key' => 'comment', 'label' => 'Comments', 'url' => $this->generateUrl('everpsblog_admin_comment')],
            ['key' => 'configuration', 'label' => 'Configuration', 'url' => $this->generateUrl('everpsblog_admin_dashboard')],
        ];
    }

    protected function buildPreviewUrlForResource(string $resource, ?int $resourceId): string
    {
        $resourceId = (int) $resourceId;
        if ($resourceId <= 0) {
            return '';
        }

        $shopId = $this->getContextShopId();
        $langId = $this->getContextLangId();

        switch ($resource) {
            case 'post':
                return $this->buildPostPreviewUrl($resourceId, $shopId, $langId);
            case 'category':
                return $this->buildCategoryPreviewUrl($resourceId, $shopId, $langId);
            case 'tag':
                return $this->buildTagPreviewUrl($resourceId, $shopId, $langId);
            case 'author':
                return $this->buildAuthorPreviewUrl($resourceId, $shopId, $langId);
        }

        return '';
    }

    /**
     * Build the language switcher payload consumed by the modern form template.
     * Returns one entry per active Prestashop language with its real ISO code
     * as label, so the BO never shows "FR/FR/EN/EN" duplicates caused by label
     * parsing.
     *
     * @return array<int, array<string, int|string>>
     */
    protected function getEverBlogLanguages(): array
    {
        $languages = [];
        try {
            $rawLanguages = \Language::getLanguages(false);
        } catch (\Throwable $exception) {
            return [];
        }

        foreach ($rawLanguages as $language) {
            $idLang = (int) ($language['id_lang'] ?? 0);
            if ($idLang <= 0) {
                continue;
            }

            $isoCode = strtoupper((string) ($language['iso_code'] ?? ''));
            $name = trim((string) ($language['name'] ?? ''));

            if ('' === $isoCode) {
                $isoCode = 'L' . $idLang;
            }

            $languages[] = [
                'id' => $idLang,
                'label' => $isoCode,
                'name' => '' !== $name ? $name : $isoCode,
            ];
        }

        return $languages;
    }

    protected function refreshSitemapsAfterBackOfficeChange(BlogSitemapService $blogSitemapService, bool $flashWarning = true): bool
    {
        $this->runScheduledTasksAfterBackOfficeSave($flashWarning);

        try {
            $refreshed = (bool) $blogSitemapService->refreshForShop($this->getContextShopId());
            if (!$refreshed && $flashWarning) {
                $this->addFlash('warning', $this->transAdmin('Sitemaps were regenerated, but robots.txt could not be updated.'));
            }

            return $refreshed;
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog(
                '[everpsblog][AdminSitemapRefresh] ' . $exception->getMessage()
                    . ' @ ' . $exception->getFile() . ':' . $exception->getLine(),
                3
            );
            if ($flashWarning) {
                $this->addFlash(
                    'warning',
                    $this->transAdmin(
                        'Sitemaps could not be regenerated: %error%',
                        ['%error%' => $this->describeException($exception)]
                    )
                );
            }

            return false;
        }
    }

    /**
     * @return array{trash_removed:int,planned_published:int,pending_notifications_sent:int,sitemaps_refreshed:bool|null}
     */
    protected function runScheduledTasksAfterBackOfficeSave(bool $flashWarning = true): array
    {
        try {
            return $this->getScheduledTaskRunner()->runForShop($this->getContextShopId());
        } catch (\Throwable $exception) {
            \PrestaShopLogger::addLog(
                '[everpsblog][AdminScheduledTasks] ' . $exception->getMessage()
                    . ' @ ' . $exception->getFile() . ':' . $exception->getLine(),
                3
            );
            if ($flashWarning) {
                $this->addFlash(
                    'warning',
                    $this->transAdmin(
                        'Automatic maintenance tasks could not be executed: %error%',
                        ['%error%' => $this->describeException($exception)]
                    )
                );
            }

            return [
                'trash_removed' => 0,
                'planned_published' => 0,
                'pending_notifications_sent' => 0,
                'sitemaps_refreshed' => null,
            ];
        }
    }

    /**
     * @param array<string, string> $targetFields field name => button label
     *
     * @return array<string, array<string, int|string>>
     */
    protected function buildQcdPageBuilderTargets(string $targetType, ?int $targetId, array $targetFields): array
    {
        $targetId = (int) $targetId;
        if ($targetId <= 0 || empty($targetFields) || !$this->isQcdPageBuilderActive()) {
            return [];
        }

        $targets = [];
        foreach (\Language::getLanguages(false) as $language) {
            $idLang = (int) ($language['id_lang'] ?? 0);
            if ($idLang <= 0) {
                continue;
            }

            foreach ($targetFields as $targetField => $label) {
                $targetField = (string) $targetField;
                $builderUrl = $this->buildQcdPageBuilderUrl($targetType, $targetId, $targetField, $idLang);
                if ('' === $builderUrl) {
                    continue;
                }

                $fieldName = sprintf('%s_%d', $targetField, $idLang);
                $targets[$fieldName] = [
                    'label' => (string) $label,
                    'builder_url' => $builderUrl,
                    'target_type' => $targetType,
                    'target_id' => $targetId,
                    'target_field' => $targetField,
                    'id_shop' => $this->getContextShopId(),
                    'id_lang' => $idLang,
                ];
            }
        }

        return $targets;
    }

    private function isQcdPageBuilderActive(): bool
    {
        if (!\Module::isInstalled('qcdpagebuilder') || !\Module::isEnabled('qcdpagebuilder')) {
            return false;
        }

        try {
            $module = \Module::getInstanceByName('qcdpagebuilder');
        } catch (\Throwable $exception) {
            return false;
        }

        if (!$module instanceof \Module || !(bool) $module->active) {
            return false;
        }

        if (method_exists($module, 'isEnabledForShopContext')) {
            return (bool) $module->isEnabledForShopContext();
        }

        return true;
    }

    private function buildQcdPageBuilderUrl(string $targetType, int $targetId, string $targetField, int $idLang): string
    {
        if (!$this->isValidQcdIdentifier($targetType) || !$this->isValidQcdIdentifier($targetField)) {
            return '';
        }

        try {
            return $this->generateUrl('admin_qcd_pagebuilder', [
                'zone' => 'bo_wysiwyg',
                'target_type' => $targetType,
                'target_id' => $targetId,
                'target_field' => $targetField,
                'id_shop' => $this->getContextShopId(),
                'id_lang' => $idLang,
                'embed' => 1,
                'isEmbbed' => 1,
            ]);
        } catch (\Throwable $exception) {
            return '';
        }
    }

    private function isValidQcdIdentifier(string $value): bool
    {
        return (bool) preg_match('/^[a-z0-9_]{2,64}$/', $value);
    }

    private function getScheduledTaskRunner(): BlogScheduledTaskRunner
    {
        if (null === $this->scheduledTaskRunner) {
            $this->scheduledTaskRunner = new BlogScheduledTaskRunner();
        }

        return $this->scheduledTaskRunner;
    }

}
