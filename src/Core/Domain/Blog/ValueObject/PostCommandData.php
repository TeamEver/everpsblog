<?php

namespace PrestaShop\Module\Everpsblog\Core\Domain\Blog\ValueObject;

class PostCommandData
{
    /** @var int */
    private $shopId;
    /** @var int */
    private $authorId;
    /** @var int */
    private $defaultCategoryId;
    /** @var int */
    private $unclassedCategoryId;
    /** @var int */
    private $rootCategoryId;
    /** @var string */
    private $postStatus;
    /** @var string|null */
    private $password;
    /** @var string */
    private $dateAdd;
    /** @var int */
    private $indexable;
    /** @var int */
    private $follow;
    /** @var int */
    private $sitemap;
    /** @var int */
    private $starred;
    /** @var int[] */
    private $postCategories;
    /** @var array */
    private $allowedGroups;
    /** @var array */
    private $postTags;
    /** @var array */
    private $postProducts;
    /** @var array<int, array<string, string>> */
    private $translations;

    public function __construct(
        int $shopId,
        int $authorId,
        int $defaultCategoryId,
        int $unclassedCategoryId,
        int $rootCategoryId,
        string $postStatus,
        ?string $password,
        string $dateAdd,
        int $indexable,
        int $follow,
        int $sitemap,
        int $starred,
        array $postCategories,
        array $allowedGroups,
        array $postTags,
        array $postProducts,
        array $translations
    ) {
        $this->shopId = $shopId;
        $this->authorId = $authorId;
        $this->defaultCategoryId = $defaultCategoryId;
        $this->unclassedCategoryId = $unclassedCategoryId;
        $this->rootCategoryId = $rootCategoryId;
        $this->postStatus = $postStatus;
        $this->password = $password;
        $this->dateAdd = $dateAdd;
        $this->indexable = $indexable;
        $this->follow = $follow;
        $this->sitemap = $sitemap;
        $this->starred = $starred;
        $this->postCategories = $postCategories;
        $this->allowedGroups = $allowedGroups;
        $this->postTags = $postTags;
        $this->postProducts = $postProducts;
        $this->translations = $translations;
    }

    public function toArray(): array
    {
        return [
            'shop_id' => $this->shopId,
            'author_id' => $this->authorId,
            'default_category_id' => $this->defaultCategoryId,
            'unclassed_category_id' => $this->unclassedCategoryId,
            'root_category_id' => $this->rootCategoryId,
            'post_status' => $this->postStatus,
            'password' => $this->password,
            'date_add' => $this->dateAdd,
            'indexable' => $this->indexable,
            'follow' => $this->follow,
            'sitemap' => $this->sitemap,
            'starred' => $this->starred,
            'post_categories' => $this->postCategories,
            'allowed_groups' => $this->allowedGroups,
            'post_tags' => $this->postTags,
            'post_products' => $this->postProducts,
            'translations' => $this->translations,
        ];
    }
}
