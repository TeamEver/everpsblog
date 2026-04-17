<?php

namespace PrestaShop\Module\Everpsblog\Tests\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Everpsblog\Entity\Post;

class RepositoryIntegrationTest extends TestCase
{
    /** @var EntityManager */
    private $entityManager;

    protected function setUp(): void
    {
        $config = Setup::createAnnotationMetadataConfiguration([
            __DIR__ . '/../../src/Entity',
        ], true, null, null, false);

        $this->entityManager = EntityManager::create([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ], $config);

        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('CREATE TABLE ever_blog_post (id_ever_post INTEGER PRIMARY KEY AUTOINCREMENT, id_shop INTEGER, id_author INTEGER, id_default_category INTEGER, post_status VARCHAR(255), active INTEGER, indexable INTEGER, follow INTEGER, sitemap INTEGER, psswd VARCHAR(255), starred INTEGER, count INTEGER, allowed_groups VARCHAR(255), groups TEXT, date_add DATETIME, date_upd DATETIME)');
        $connection->executeStatement('CREATE TABLE ever_blog_post_lang (id_ever_post INTEGER, id_lang INTEGER, title VARCHAR(255), meta_title VARCHAR(255), meta_description VARCHAR(255), link_rewrite VARCHAR(255), content TEXT, excerpt VARCHAR(255), PRIMARY KEY (id_ever_post, id_lang))');
        $connection->executeStatement('CREATE TABLE ever_blog_post_shop (id_ever_post INTEGER, id_shop INTEGER, PRIMARY KEY (id_ever_post, id_shop))');

        $connection->executeStatement("INSERT INTO ever_blog_post (id_ever_post, id_shop, id_author, id_default_category, post_status, active, starred, count) VALUES (1, 1, 1, 10, 'published', 1, 1, 12)");
        $connection->executeStatement("INSERT INTO ever_blog_post_lang (id_ever_post, id_lang, title, link_rewrite, content, excerpt) VALUES (1, 1, 'Hello', 'hello', 'content', 'excerpt')");
        $connection->executeStatement('INSERT INTO ever_blog_post_shop (id_ever_post, id_shop) VALUES (1, 1)');
    }

    public function testLatestPostsSupportsLanguageAndShopFilters(): void
    {
        $repository = $this->entityManager->getRepository(Post::class);
        $posts = $repository->findLatestPosts(1, 1, 0, 10, 'published');

        $this->assertCount(1, $posts);
        $this->assertSame('Hello', $posts[0]['translations'][0]['title']);
    }
}
