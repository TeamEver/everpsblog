<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Command;

use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheWarmer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class WarmupCacheCommand extends Command
{
    private const EXIT_SUCCESS = 0;
    private const EXIT_FAILURE = 1;

    /** @var BlogFrontCacheWarmer */
    private $cacheWarmer;

    public function __construct(?BlogFrontCacheWarmer $cacheWarmer = null)
    {
        parent::__construct();
        $this->cacheWarmer = $cacheWarmer ?: new BlogFrontCacheWarmer();
    }

    protected function configure()
    {
        $this
            ->setName('everpsblog:cache:warmup')
            ->setDescription('Warm the EverPsBlog front cache.')
            ->addOption(
                'shop-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'Warm the cache for a specific shop identifier'
            )
            ->addOption(
                'lang-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'Warm the cache for a specific language identifier'
            )
            ->addOption(
                'page-limit',
                null,
                InputOption::VALUE_OPTIONAL,
                'Number of paginated listing pages to warm per entity family',
                3
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if (!\Module::isInstalled('everpsblog')) {
            $io->error('The EverPsBlog module is not installed.');

            return self::EXIT_FAILURE;
        }

        $module = \Module::getInstanceByName('everpsblog');
        if (!$module instanceof \EverPsBlog || !$module->active) {
            $io->error('The EverPsBlog module is not available or not active.');

            return self::EXIT_FAILURE;
        }

        $pageLimit = max(1, (int) $input->getOption('page-limit'));
        $shopIdOption = (int) $input->getOption('shop-id');
        $langIdOption = (int) $input->getOption('lang-id');

        $shopIds = $shopIdOption > 0
            ? [$shopIdOption]
            : array_map('intval', (array) \Shop::getShops(true, null, true));

        if (count($shopIds) === 0) {
            $io->warning('No shop found to process.');

            return self::EXIT_SUCCESS;
        }

        $context = \Context::getContext();
        foreach ($shopIds as $shopId) {
            $shop = new \Shop((int) $shopId);
            \Shop::setContext(\Shop::CONTEXT_SHOP, (int) $shopId);
            $context->shop = $shop;

            $langIds = $langIdOption > 0 ? [$langIdOption] : $this->resolveShopLanguageIds((int) $shopId);
            if (empty($langIds)) {
                $langIds = [(int) \Configuration::get('PS_LANG_DEFAULT')];
            }

            $io->section(sprintf('Warming EverPsBlog cache for shop #%d', $shopId));
            foreach ($langIds as $langId) {
                $language = new \Language((int) $langId);
                if (!\Validate::isLoadedObject($language)) {
                    $io->warning(sprintf('Skipping language #%d because it is not available.', $langId));
                    continue;
                }

                $context->language = $language;

                $stats = $this->cacheWarmer->warm((int) $shopId, (int) $langId, $pageLimit);
                $io->text(sprintf(
                    'Language #%d warmed: %d blog page(s), %d categor(ies), %d tag(s), %d author(s), %d post(s).',
                    $langId,
                    (int) ($stats['blog_pages'] ?? 0),
                    (int) ($stats['categories'] ?? 0),
                    (int) ($stats['tags'] ?? 0),
                    (int) ($stats['authors'] ?? 0),
                    (int) ($stats['posts'] ?? 0)
                ));
            }
        }

        $io->success('EverPsBlog front cache warmup completed.');

        return self::EXIT_SUCCESS;
    }

    /**
     * @return int[]
     */
    private function resolveShopLanguageIds(int $shopId): array
    {
        $langIds = [];
        foreach (\Language::getLanguages(true, $shopId) as $language) {
            $langId = (int) ($language['id_lang'] ?? 0);
            if ($langId > 0) {
                $langIds[] = $langId;
            }
        }

        return array_values(array_unique($langIds));
    }
}
