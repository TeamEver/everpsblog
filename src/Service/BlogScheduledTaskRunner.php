<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Service;

use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheInvalidator;
use PrestaShop\Module\Everpsblog\Service\Cache\BlogFrontCacheRelationResolver;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class BlogScheduledTaskRunner
{
    /** @var BlogFrontCacheRelationResolver */
    private $relationResolver;

    /** @var BlogFrontCacheInvalidator */
    private $cacheInvalidator;

    /** @var BlogSitemapService */
    private $blogSitemapService;

    public function __construct(
        ?BlogFrontCacheRelationResolver $relationResolver = null,
        ?BlogFrontCacheInvalidator $cacheInvalidator = null,
        ?BlogSitemapService $blogSitemapService = null
    ) {
        $this->relationResolver = $relationResolver ?: new BlogFrontCacheRelationResolver();
        $this->cacheInvalidator = $cacheInvalidator ?: new BlogFrontCacheInvalidator();
        $this->blogSitemapService = $blogSitemapService ?: new BlogSitemapService();
    }

    /**
     * @return array{trash_removed:int,planned_published:int,pending_notifications_sent:int,sitemaps_refreshed:bool|null}
     */
    public function runForShop(int $shopId, bool $sendPendingNotifications = false, bool $refreshSitemaps = false): array
    {
        $summary = [
            'trash_removed' => 0,
            'planned_published' => 0,
            'pending_notifications_sent' => 0,
            'sitemaps_refreshed' => null,
        ];

        if ($shopId <= 0) {
            return $summary;
        }

        $summary['trash_removed'] = $this->emptyTrash($shopId);
        $summary['planned_published'] = $this->publishPlannedPosts($shopId);

        if ($sendPendingNotifications) {
            $summary['pending_notifications_sent'] = $this->sendPendingNotification($shopId);
        }

        if ($refreshSitemaps) {
            $summary['sitemaps_refreshed'] = $this->refreshSitemaps($shopId);
        }

        return $summary;
    }

    public function emptyTrash(int $shopId): int
    {
        $shopId = (int) $shopId;
        if ($shopId <= 0) {
            return 0;
        }

        $limitDate = date('Y-m-d H:i:s', strtotime('-' . $this->getEmptyTrashDelayDays() . ' days'));
        $rows = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT DISTINCT p.`id_ever_post`
             FROM `' . _DB_PREFIX_ . 'ever_blog_post` p
             LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_post_shop` ps
                ON ps.`id_ever_post` = p.`id_ever_post`
             WHERE (p.`id_shop` = ' . $shopId . ' OR ps.`id_shop` = ' . $shopId . ')
                AND p.`post_status` = "trash"
                AND COALESCE(p.`date_upd`, p.`date_add`) <= "' . pSQL($limitDate) . '"'
        );

        if (!is_array($rows) || empty($rows)) {
            return 0;
        }

        $deleted = 0;
        foreach ($rows as $row) {
            $postId = (int) ($row['id_ever_post'] ?? 0);
            if ($postId <= 0) {
                continue;
            }

            $beforeSnapshot = $this->relationResolver->getPostSnapshot($postId);
            $this->deletePostImages($postId);

            \Db::getInstance()->delete('ever_blog_post_lang', 'id_ever_post = ' . $postId);
            \Db::getInstance()->delete('ever_blog_post_shop', 'id_ever_post = ' . $postId);
            \Db::getInstance()->delete('ever_blog_post_category', 'id_ever_post = ' . $postId);
            \Db::getInstance()->delete('ever_blog_post_tag', 'id_ever_post = ' . $postId);
            \Db::getInstance()->delete('ever_blog_post_product', 'id_ever_post = ' . $postId);
            \Db::getInstance()->delete('ever_blog_redirect', 'entity_type = "post" AND id_element = ' . $postId);
            \Db::getInstance()->delete('ever_blog_post', 'id_ever_post = ' . $postId);

            $this->cacheInvalidator->invalidatePostMutation($postId, $beforeSnapshot, []);
            ++$deleted;
        }

        return $deleted;
    }

    public function publishPlannedPosts(int $shopId): int
    {
        $shopId = (int) $shopId;
        if ($shopId <= 0) {
            return 0;
        }

        $rows = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT DISTINCT p.`id_ever_post`
             FROM `' . _DB_PREFIX_ . 'ever_blog_post` p
             LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_post_shop` ps
                ON ps.`id_ever_post` = p.`id_ever_post`
             WHERE (p.`id_shop` = ' . $shopId . ' OR ps.`id_shop` = ' . $shopId . ')
                AND p.`post_status` = "planned"
                AND p.`date_add` <= "' . pSQL(date('Y-m-d H:i:s')) . '"'
        );

        if (!is_array($rows) || empty($rows)) {
            return 0;
        }

        $published = 0;
        foreach ($rows as $row) {
            $postId = (int) ($row['id_ever_post'] ?? 0);
            if ($postId <= 0) {
                continue;
            }

            $snapshot = $this->relationResolver->getPostSnapshot($postId);
            \Db::getInstance()->update(
                'ever_blog_post',
                [
                    'post_status' => 'published',
                    'active' => 1,
                    'date_upd' => date('Y-m-d H:i:s'),
                ],
                'id_ever_post = ' . $postId
            );
            $this->cacheInvalidator->invalidatePostMutation($postId, $snapshot, $snapshot);
            ++$published;
        }

        return $published;
    }

    public function sendPendingNotification(int $shopId): int
    {
        $shopId = (int) $shopId;
        if ($shopId <= 0) {
            return 0;
        }

        $employee = $this->resolveNotificationEmployee();
        if (null === $employee) {
            return 0;
        }

        $langId = (int) ($employee->id_lang ?: \Configuration::get('PS_LANG_DEFAULT'));
        $posts = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT DISTINCT p.`id_ever_post`, pl.`title`
             FROM `' . _DB_PREFIX_ . 'ever_blog_post` p
             LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_post_shop` ps
                ON ps.`id_ever_post` = p.`id_ever_post`
             LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_post_lang` pl
                ON pl.`id_ever_post` = p.`id_ever_post`
                AND pl.`id_lang` = ' . $langId . '
             WHERE (p.`id_shop` = ' . $shopId . ' OR ps.`id_shop` = ' . $shopId . ')
                AND p.`post_status` = "pending"
             ORDER BY p.`date_add` DESC, p.`id_ever_post` DESC'
        );

        if (!is_array($posts) || empty($posts)) {
            return 0;
        }

        $postList = '';
        foreach ($posts as $post) {
            $title = trim((string) ($post['title'] ?? ''));
            if ('' === $title) {
                $title = '#' . (int) ($post['id_ever_post'] ?? 0);
            }

            $postList .= '<p>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        $mailSent = (bool) \Mail::send(
            $langId,
            'pending',
            (string) \Context::getContext()->getTranslator()->trans(
                'Review on pending posts',
                [],
                'Modules.Everpsblog.Admin'
            ),
            [
                '{shop_name}' => (string) \Configuration::get('PS_SHOP_NAME'),
                '{shop_logo}' => _PS_IMG_DIR_ . \Configuration::get('PS_LOGO'),
                '{posts}' => $postList,
            ],
            (string) $employee->email,
            null,
            (string) \Configuration::get('PS_SHOP_EMAIL'),
            (string) \Configuration::get('PS_SHOP_NAME'),
            null,
            null,
            _PS_MODULE_DIR_ . 'everpsblog/mails/'
        );

        return $mailSent ? count($posts) : 0;
    }

    public function refreshSitemaps(int $shopId): bool
    {
        return (bool) $this->blogSitemapService->refreshForShop((int) $shopId);
    }

    private function getEmptyTrashDelayDays(): int
    {
        $configured = \Configuration::get('EVERBLOG_EMPTY_TRASH');
        if (false === $configured || null === $configured || '' === $configured) {
            return 7;
        }

        return max(0, (int) $configured);
    }

    private function deletePostImages(int $postId): void
    {
        $imageRows = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT `id_ever_image`, `image_type`, `image_link`
             FROM `' . _DB_PREFIX_ . 'ever_blog_image`
             WHERE `id_element` = ' . (int) $postId . '
                AND `image_type` IN ("post", "post_banner")'
        );

        if (is_array($imageRows)) {
            foreach ($imageRows as $imageRow) {
                $imageId = (int) ($imageRow['id_ever_image'] ?? 0);
                if ($imageId <= 0) {
                    continue;
                }

                $this->deleteReferencedImageFile((string) ($imageRow['image_link'] ?? ''));
                \Db::getInstance()->delete('ever_blog_image_shop', 'id_ever_image = ' . $imageId);
                \Db::getInstance()->delete('ever_blog_image', 'id_ever_image = ' . $imageId);
            }
        }

        foreach (['post', 'post_banner'] as $imageType) {
            $this->deleteImageFiles($postId, $imageType);
        }

        $this->deleteLegacyPostImageFiles($postId);
    }

    private function deleteLegacyPostImageFiles(int $postId): void
    {
        $legacyFiles = [
            _PS_MODULE_DIR_ . 'everpsblog/views/img/posts/post_image_' . $postId . '.jpg',
            _PS_IMG_DIR_ . 'posts/' . $postId . '.jpg',
        ];

        foreach ($legacyFiles as $legacyFile) {
            if (is_file($legacyFile)) {
                @unlink($legacyFile);
            }
        }
    }

    private function deleteImageFiles(int $elementId, string $imageType): void
    {
        $targetDirectory = rtrim(_PS_IMG_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $imageType;
        foreach ((array) glob($targetDirectory . DIRECTORY_SEPARATOR . $elementId . '.*') as $existingFile) {
            $this->deleteLocalImageFile((string) $existingFile);
        }

        $thumbDirectory = $targetDirectory . DIRECTORY_SEPARATOR . 'thumbs';
        foreach ((array) glob($thumbDirectory . DIRECTORY_SEPARATOR . $elementId . '-*') as $existingFile) {
            $this->deleteLocalImageFile((string) $existingFile);
        }
    }

    private function deleteReferencedImageFile(string $imageLink): void
    {
        $imageLink = trim($imageLink);
        if ('' === $imageLink || preg_match('/^https?:\/\//i', $imageLink)) {
            return;
        }

        $relativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($imageLink, '/\\'));
        $this->deleteLocalImageFile(rtrim(_PS_ROOT_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $relativePath);
    }

    private function deleteLocalImageFile(string $filePath): void
    {
        $realFile = realpath($filePath);
        $realImageDirectory = realpath(_PS_IMG_DIR_);

        if (!$realFile || !$realImageDirectory || !is_file($realFile)) {
            return;
        }

        $realImageDirectory = rtrim($realImageDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (0 !== strpos($realFile, $realImageDirectory)) {
            return;
        }

        @unlink($realFile);
    }

    private function resolveNotificationEmployee(): ?\Employee
    {
        $configured = trim((string) \Configuration::get('EVERBLOG_ADMIN_EMAIL'));
        if ('' === $configured) {
            return null;
        }

        $employeeId = 0;
        if (\Validate::isEmail($configured)) {
            $employeeId = (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                'SELECT `id_employee`
                 FROM `' . _DB_PREFIX_ . 'employee`
                 WHERE `email` = "' . pSQL($configured) . '"'
            );
        } else {
            $employeeId = (int) $configured;
        }

        if ($employeeId <= 0) {
            return null;
        }

        $employee = new \Employee($employeeId);

        return \Validate::isLoadedObject($employee) ? $employee : null;
    }
}
