<?php
/**
 * 2019-2025 Team Ever
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2025 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace PrestaShop\Module\Everpsblog\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RunCronCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('everpsblog:cron:run')
            ->setDescription('Run all EverPsBlog cron tasks.')
            ->addOption(
                'shop-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'Run cron tasks for a specific shop identifier'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if (!\Module::isInstalled('everpsblog')) {
            $io->error('The EverPsBlog module is not installed.');

            return Command::FAILURE;
        }

        $module = \Module::getInstanceByName('everpsblog');
        if (!\Validate::isLoadedObject($module) || !$module->active) {
            $io->error('The EverPsBlog module is not available or not active.');

            return Command::FAILURE;
        }

        $module->cron = true;
        $module->context = \Context::getContext();

        $shopIdOption = (int) $input->getOption('shop-id');
        $shopIds = [];
        if ($shopIdOption > 0) {
            $shopIds = [$shopIdOption];
        } else {
            $shopIds = array_map('intval', (array) \Shop::getShops(true, null, true));
        }

        if (empty($shopIds)) {
            $io->warning('No shop found to process.');

            return Command::SUCCESS;
        }

        $context = \Context::getContext();

        foreach ($shopIds as $idShop) {
            $shop = new \Shop((int) $idShop);
            \Shop::setContext(\Shop::CONTEXT_SHOP, (int) $idShop);
            $context->shop = $shop;
            $context->id_shop = (int) $idShop;

            $langId = (int) \Configuration::get(
                'PS_LANG_DEFAULT',
                null,
                (int) $shop->id_shop_group,
                (int) $idShop
            );
            if (!$langId) {
                $langId = (int) \Configuration::get('PS_LANG_DEFAULT');
            }

            $context->language = new \Language($langId);
            $context->id_lang = $langId;

            if (!isset($context->employee) || !$context->employee->id) {
                $employeeId = (int) \Configuration::get('EVERBLOG_ADMIN_EMAIL');
                if ($employeeId) {
                    $context->employee = new \Employee($employeeId);
                }
            }

            $io->section(sprintf('Running cron tasks for shop #%d', $idShop));

            $emptyTrash = (bool) $module->emptyTrash((int) $idShop);
            $io->text($emptyTrash ? 'Trash emptied successfully.' : 'No trash to empty.');

            $planned = (bool) $module->publishPlannedPosts((int) $idShop);
            $io->text($planned ? 'Planned posts have been published.' : 'No planned posts to publish.');

            $pending = (bool) $module->sendPendingNotification((int) $idShop);
            $io->text($pending ? 'Pending notifications have been sent.' : 'No pending notifications to send.');

            $sitemaps = $module->generateBlogSitemap((int) $idShop, true);
            if ($sitemaps) {
                $io->text('Sitemaps generated successfully.');
            } else {
                $io->text('Sitemaps generation did not return a positive result.');
            }
        }

        $io->success('All EverPsBlog cron tasks have been processed.');

        return Command::SUCCESS;
    }
}
