<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Command;

use PrestaShop\Module\Everpsblog\Service\WordPressRestImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

if (!defined('_PS_VERSION_')) {
    exit;
}


class ImportWordPressCommand extends Command
{
    private const EXIT_SUCCESS = 0;
    private const EXIT_FAILURE = 1;

    /** @var WordPressRestImporter */
    private $wordPressRestImporter;

    public function __construct(WordPressRestImporter $wordPressRestImporter)
    {
        parent::__construct();
        $this->wordPressRestImporter = $wordPressRestImporter;
    }

    protected function configure()
    {
        $this
            ->setName('everpsblog:wordpress:import')
            ->setDescription('Import a WordPress blog through the WordPress REST API.')
            ->addOption('shop-id', null, InputOption::VALUE_OPTIONAL, 'Import for a specific PrestaShop shop identifier.')
            ->addOption('lang-id', null, InputOption::VALUE_OPTIONAL, 'Use a specific default language identifier for imported content.')
            ->addOption('site-url', null, InputOption::VALUE_OPTIONAL, 'WordPress site URL or wp-json/wp/v2 API URL.')
            ->addOption('username', null, InputOption::VALUE_OPTIONAL, 'WordPress REST username. Defaults to BO configuration.')
            ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'WordPress application password. Defaults to BO configuration.');
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

        $shopIds = $this->resolveShopIds($input);
        if (count($shopIds) === 0) {
            $io->warning('No shop found to process.');

            return self::EXIT_SUCCESS;
        }

        $totals = $this->emptyStats();
        $failures = 0;
        foreach ($shopIds as $shopId) {
            $shop = new \Shop((int) $shopId);
            if (!\Validate::isLoadedObject($shop)) {
                $io->warning(sprintf('Shop #%d was not found.', (int) $shopId));
                ++$failures;
                continue;
            }

            $langId = $this->resolveLanguageId($input, $shop);
            $this->initializeContext($shop, $langId);

            $siteUrl = $this->resolveOptionOrConfiguration($input, 'site-url', 'EVER_WP_API_URL', $shop);
            $username = $this->resolveOptionOrConfiguration($input, 'username', 'EVER_WP_API_USER', $shop);
            $password = $this->resolveOptionOrConfiguration($input, 'password', 'EVER_WP_API_PASSWORD', $shop);

            if ('' === trim($siteUrl)) {
                $io->error(sprintf('No WordPress REST URL configured for shop #%d.', (int) $shop->id));
                ++$failures;
                continue;
            }

            $io->section(sprintf('Importing WordPress blog for shop #%d', (int) $shop->id));
            try {
                $stats = $this->wordPressRestImporter->import($siteUrl, $username, $password, (int) $shop->id, $langId);
                $this->mergeStats($totals, $stats);
                $this->renderStats($io, $stats);
            } catch (\Throwable $exception) {
                \PrestaShopLogger::addLog('EverPsBlog WordPress CLI import failed: ' . $exception->getMessage(), 3);
                $io->error(sprintf('Import failed for shop #%d: %s', (int) $shop->id, $exception->getMessage()));
                ++$failures;
            }
        }

        $io->section('Import totals');
        $this->renderStats($io, $totals);

        if ($failures > 0) {
            $io->warning(sprintf('%d shop import(s) failed or were skipped.', $failures));

            return self::EXIT_FAILURE;
        }

        $io->success('WordPress import completed.');

        return self::EXIT_SUCCESS;
    }

    private function resolveShopIds(InputInterface $input): array
    {
        $shopId = (int) $input->getOption('shop-id');
        if ($shopId > 0) {
            return [$shopId];
        }

        return array_map('intval', (array) \Shop::getShops(true, null, true));
    }

    private function resolveLanguageId(InputInterface $input, \Shop $shop): int
    {
        $langId = (int) $input->getOption('lang-id');
        if ($langId > 0) {
            return $langId;
        }

        $langId = (int) \Configuration::get('PS_LANG_DEFAULT', null, (int) $shop->id_shop_group, (int) $shop->id);

        return $langId > 0 ? $langId : (int) \Configuration::get('PS_LANG_DEFAULT');
    }

    private function initializeContext(\Shop $shop, int $langId): void
    {
        \Shop::setContext(\Shop::CONTEXT_SHOP, (int) $shop->id);

        $context = \Context::getContext();
        $context->shop = $shop;
        $context->language = new \Language($langId);
    }

    private function resolveOptionOrConfiguration(InputInterface $input, string $optionName, string $configurationKey, \Shop $shop): string
    {
        $option = $input->getOption($optionName);
        if (null !== $option) {
            return (string) $option;
        }

        return (string) \Configuration::get($configurationKey, null, (int) $shop->id_shop_group, (int) $shop->id);
    }

    private function emptyStats(): array
    {
        return [
            'posts_created' => 0,
            'posts_updated' => 0,
            'categories' => 0,
            'tags' => 0,
            'authors' => 0,
            'images' => 0,
            'redirects' => 0,
            'skipped' => 0,
        ];
    }

    private function mergeStats(array &$totals, array $stats): void
    {
        foreach ($totals as $key => $value) {
            $totals[$key] = (int) $value + (int) ($stats[$key] ?? 0);
        }
    }

    private function renderStats(SymfonyStyle $io, array $stats): void
    {
        $io->table(
            ['Metric', 'Count'],
            [
                ['Posts created', (int) ($stats['posts_created'] ?? 0)],
                ['Posts updated', (int) ($stats['posts_updated'] ?? 0)],
                ['Categories', (int) ($stats['categories'] ?? 0)],
                ['Tags', (int) ($stats['tags'] ?? 0)],
                ['Authors', (int) ($stats['authors'] ?? 0)],
                ['Images', (int) ($stats['images'] ?? 0)],
                ['Redirects', (int) ($stats['redirects'] ?? 0)],
                ['Skipped', (int) ($stats['skipped'] ?? 0)],
            ]
        );
    }
}
