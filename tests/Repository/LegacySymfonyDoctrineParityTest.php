<?php

namespace PrestaShop\Module\Everpsblog\Tests\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Everpsblog\Entity\Author;
use PrestaShop\Module\Everpsblog\Entity\Category;
use PrestaShop\Module\Everpsblog\Entity\Comment;
use PrestaShop\Module\Everpsblog\Entity\Post;
use PrestaShop\Module\Everpsblog\Entity\Tag;

class LegacySymfonyDoctrineParityTest extends TestCase
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

        $schema = [
            'CREATE TABLE ever_blog_author (id_ever_author INTEGER PRIMARY KEY AUTOINCREMENT, id_employee INTEGER, id_shop INTEGER, nickhandle VARCHAR(255), active INTEGER, indexable INTEGER, follow INTEGER, sitemap INTEGER, allowed_groups VARCHAR(255), count INTEGER)',
            'CREATE TABLE ever_blog_author_lang (id_ever_author INTEGER, id_lang INTEGER, meta_title VARCHAR(255), meta_description VARCHAR(255), link_rewrite VARCHAR(255), content TEXT, bottom_content TEXT, PRIMARY KEY (id_ever_author, id_lang))',
            'CREATE TABLE ever_blog_author_shop (id_ever_author INTEGER, id_shop INTEGER, PRIMARY KEY (id_ever_author, id_shop))',

            'CREATE TABLE ever_blog_category (id_ever_category INTEGER PRIMARY KEY AUTOINCREMENT, id_parent_category INTEGER, id_shop INTEGER, active INTEGER, indexable INTEGER, follow INTEGER, sitemap INTEGER, is_root_category INTEGER, count INTEGER, allowed_groups VARCHAR(255), groups TEXT, date_add DATETIME, date_upd DATETIME)',
            'CREATE TABLE ever_blog_category_lang (id_ever_category INTEGER, id_lang INTEGER, title VARCHAR(255), meta_title VARCHAR(255), meta_description VARCHAR(255), link_rewrite VARCHAR(255), content TEXT, bottom_content TEXT, PRIMARY KEY (id_ever_category, id_lang))',
            'CREATE TABLE ever_blog_category_shop (id_ever_category INTEGER, id_shop INTEGER, PRIMARY KEY (id_ever_category, id_shop))',

            'CREATE TABLE ever_blog_tag (id_ever_tag INTEGER PRIMARY KEY AUTOINCREMENT, id_shop INTEGER, active INTEGER, indexable INTEGER, follow INTEGER, sitemap INTEGER, allowed_groups VARCHAR(255), count INTEGER)',
            'CREATE TABLE ever_blog_tag_lang (id_ever_tag INTEGER, id_lang INTEGER, title VARCHAR(255), meta_title VARCHAR(255), meta_description VARCHAR(255), link_rewrite VARCHAR(255), content TEXT, bottom_content TEXT, PRIMARY KEY (id_ever_tag, id_lang))',
            'CREATE TABLE ever_blog_tag_shop (id_ever_tag INTEGER, id_shop INTEGER, PRIMARY KEY (id_ever_tag, id_shop))',

            'CREATE TABLE ever_blog_post (id_ever_post INTEGER PRIMARY KEY AUTOINCREMENT, id_shop INTEGER, id_author INTEGER, id_default_category INTEGER, post_status VARCHAR(255), active INTEGER, indexable INTEGER, follow INTEGER, sitemap INTEGER, psswd VARCHAR(255), starred INTEGER, count INTEGER, allowed_groups VARCHAR(255), groups TEXT, date_add DATETIME, date_upd DATETIME)',
            'CREATE TABLE ever_blog_post_lang (id_ever_post INTEGER, id_lang INTEGER, title VARCHAR(255), meta_title VARCHAR(255), meta_description VARCHAR(255), link_rewrite VARCHAR(255), content TEXT, excerpt VARCHAR(255), PRIMARY KEY (id_ever_post, id_lang))',
            'CREATE TABLE ever_blog_post_shop (id_ever_post INTEGER, id_shop INTEGER, PRIMARY KEY (id_ever_post, id_shop))',
            'CREATE TABLE ever_blog_post_category (id_ever_post INTEGER, id_ever_post_category INTEGER, PRIMARY KEY (id_ever_post, id_ever_post_category))',
            'CREATE TABLE ever_blog_post_tag (id_ever_post INTEGER, id_ever_post_tag INTEGER, PRIMARY KEY (id_ever_post, id_ever_post_tag))',

            'CREATE TABLE ever_blog_comments (id_ever_comment INTEGER PRIMARY KEY AUTOINCREMENT, id_ever_post INTEGER, id_lang INTEGER, comment TEXT, name TEXT, user_email TEXT, active INTEGER, date_add DATETIME, date_upd DATETIME)',
        ];

        foreach ($schema as $sql) {
            $connection->executeStatement($sql);
        }

        $connection->executeStatement("INSERT INTO ever_blog_author (id_ever_author, id_employee, id_shop, nickhandle, active, indexable, follow, sitemap, count) VALUES (1, 5, 1, 'john-doe', 1, 1, 1, 1, 8)");
        $connection->executeStatement("INSERT INTO ever_blog_author_lang (id_ever_author, id_lang, meta_title, meta_description, link_rewrite, content, bottom_content) VALUES (1, 1, 'Auteur John', 'Bio John', 'john-doe', 'Bio', '')");
        $connection->executeStatement('INSERT INTO ever_blog_author_shop (id_ever_author, id_shop) VALUES (1, 1)');

        $connection->executeStatement("INSERT INTO ever_blog_category (id_ever_category, id_parent_category, id_shop, active, indexable, follow, sitemap, is_root_category, count) VALUES (10, 0, 1, 1, 1, 1, 1, 0, 3)");
        $connection->executeStatement("INSERT INTO ever_blog_category_lang (id_ever_category, id_lang, title, meta_title, meta_description, link_rewrite, content, bottom_content) VALUES (10, 1, 'Tech', 'Tech meta', 'Tech desc', 'tech', 'Tech content', '')");
        $connection->executeStatement('INSERT INTO ever_blog_category_shop (id_ever_category, id_shop) VALUES (10, 1)');

        $connection->executeStatement("INSERT INTO ever_blog_tag (id_ever_tag, id_shop, active, indexable, follow, sitemap, count) VALUES (20, 1, 1, 1, 1, 1, 5)");
        $connection->executeStatement("INSERT INTO ever_blog_tag_lang (id_ever_tag, id_lang, title, meta_title, meta_description, link_rewrite, content, bottom_content) VALUES (20, 1, 'Symfony', 'Symfony meta', 'Symfony desc', 'symfony', 'Tag content', '')");
        $connection->executeStatement('INSERT INTO ever_blog_tag_shop (id_ever_tag, id_shop) VALUES (20, 1)');

        $connection->executeStatement("INSERT INTO ever_blog_post (id_ever_post, id_shop, id_author, id_default_category, post_status, active, indexable, follow, sitemap, starred, count) VALUES (100, 1, 1, 10, 'published', 1, 1, 1, 1, 1, 12)");
        $connection->executeStatement("INSERT INTO ever_blog_post_lang (id_ever_post, id_lang, title, meta_title, meta_description, link_rewrite, content, excerpt) VALUES (100, 1, 'Hello Doctrine', 'Post meta', 'Post desc', 'hello-doctrine', 'content', 'excerpt')");
        $connection->executeStatement('INSERT INTO ever_blog_post_shop (id_ever_post, id_shop) VALUES (100, 1)');
        $connection->executeStatement('INSERT INTO ever_blog_post_category (id_ever_post, id_ever_post_category) VALUES (100, 10)');
        $connection->executeStatement('INSERT INTO ever_blog_post_tag (id_ever_post, id_ever_post_tag) VALUES (100, 20)');

        $connection->executeStatement("INSERT INTO ever_blog_comments (id_ever_comment, id_ever_post, id_lang, comment, name, user_email, active, date_add, date_upd) VALUES (900, 100, 1, 'Great post', 'Alice', 'alice@example.com', 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00')");
    }

    public function testPostWorkflowParityLegacyVsDoctrine(): void
    {
        $legacy = [
            'id' => 100,
            'title' => 'Hello Doctrine',
            'status' => 'published',
        ];

        $rows = $this->entityManager->getRepository(Post::class)->findLatestPosts(1, 1, 0, 10, 'published');
        $doctrine = [
            'id' => (int) $rows[0]['id'],
            'title' => (string) $rows[0]['translations'][0]['title'],
            'status' => (string) $rows[0]['status'],
        ];

        $this->assertSame($legacy, $doctrine);
    }

    public function testCategoryWorkflowParityLegacyVsDoctrine(): void
    {
        $legacy = [
            'id' => 10,
            'title' => 'Tech',
            'link_rewrite' => 'tech',
        ];

        $rows = $this->entityManager->getRepository(Category::class)->findAllCategories(1, 1, 1);
        $doctrine = [
            'id' => (int) $rows[0]['id'],
            'title' => (string) $rows[0]['translations'][0]['title'],
            'link_rewrite' => (string) $rows[0]['translations'][0]['linkRewrite'],
        ];

        $this->assertSame($legacy, $doctrine);
    }

    public function testTagWorkflowParityLegacyVsDoctrine(): void
    {
        $legacy = [
            'id' => 20,
            'title' => 'Symfony',
            'link_rewrite' => 'symfony',
        ];

        $rows = $this->entityManager->getRepository(Tag::class)->findAllTags(1, 1, 1);
        $doctrine = [
            'id' => (int) $rows[0]['id'],
            'title' => (string) $rows[0]['translations'][0]['title'],
            'link_rewrite' => (string) $rows[0]['translations'][0]['linkRewrite'],
        ];

        $this->assertSame($legacy, $doctrine);
    }

    public function testAuthorWorkflowParityLegacyVsDoctrine(): void
    {
        $legacy = [
            'id' => 1,
            'nickhandle' => 'john-doe',
            'meta_title' => 'Auteur John',
        ];

        $rows = $this->entityManager->getRepository(Author::class)->findAllAuthors(1, 1, 1);
        $doctrine = [
            'id' => (int) $rows[0]['id'],
            'nickhandle' => (string) $rows[0]['nickhandle'],
            'meta_title' => (string) $rows[0]['translations'][0]['metaTitle'],
        ];

        $this->assertSame($legacy, $doctrine);
    }

    public function testCommentWorkflowParityLegacyVsDoctrine(): void
    {
        $legacy = [
            'count_by_post' => 1,
            'latest_email' => 'alice@example.com',
            'latest_comment' => 'Great post',
        ];

        $commentRepository = $this->entityManager->getRepository(Comment::class);
        $byEmail = $commentRepository->findCommentsByEmail('alice@example.com', 1, 1);

        $doctrine = [
            'count_by_post' => $commentRepository->countCommentsByPost(100, 1, 1),
            'latest_email' => (string) $byEmail[0]['userEmail'],
            'latest_comment' => (string) $byEmail[0]['comment'],
        ];

        $this->assertSame($legacy, $doctrine);
    }
}
