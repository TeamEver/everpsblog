<?php

declare(strict_types=1);

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

use PrestaShop\Module\Everpsblog\Service\BlogScheduledTaskRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

if (!defined('_PS_VERSION_')) {
    exit;
}


class RunCronCommand extends Command
{
    private const EXIT_SUCCESS = 0;
    private const EXIT_FAILURE = 1;

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

            return self::EXIT_FAILURE;
        }

        $module = \Module::getInstanceByName('everpsblog');
        if (!$module instanceof \EverPsBlog || !$module->active) {
            $io->error('The EverPsBlog module is not available or not active.');

            return self::EXIT_FAILURE;
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

        if (count($shopIds) === 0) {
            $io->warning('No shop found to process.');

            return self::EXIT_SUCCESS;
        }

        $context = \Context::getContext();
        $scheduledTaskRunner = new BlogScheduledTaskRunner();

        foreach ($shopIds as $idShop) {
            $shop = new \Shop((int) $idShop);
            \Shop::setContext(\Shop::CONTEXT_SHOP, (int) $idShop);
            $context->shop = $shop;

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

            if (!isset($context->employee) || !$context->employee->id) {
                $employeeId = (int) \Configuration::get('EVERBLOG_ADMIN_EMAIL');
                if ($employeeId) {
                    $context->employee = new \Employee($employeeId);
                }
            }

            $io->section(sprintf('Running cron tasks for shop #%d', $idShop));

            $summary = $scheduledTaskRunner->runForShop((int) $idShop, true, true);

            $io->text(
                (int) $summary['trash_removed'] > 0
                    ? sprintf('Trash emptied: %d post(s) deleted.', (int) $summary['trash_removed'])
                    : 'No trash to empty.'
            );
            $io->text(
                (int) $summary['planned_published'] > 0
                    ? sprintf('Planned posts published: %d.', (int) $summary['planned_published'])
                    : 'No planned posts to publish.'
            );
            $io->text(
                (int) $summary['pending_notifications_sent'] > 0
                    ? sprintf('Pending notifications sent for %d post(s).', (int) $summary['pending_notifications_sent'])
                    : 'No pending notifications to send.'
            );

            if ((bool) $summary['sitemaps_refreshed']) {
                $io->text('Sitemaps generated successfully.');
            } else {
                $io->text('Sitemaps generation did not return a positive result.');
            }
        }

        $io->success('All EverPsBlog cron tasks have been processed.');

        return self::EXIT_SUCCESS;
    }
}
