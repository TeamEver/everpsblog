<?php

$moduleFile = __DIR__ . '/../everpsblog.php';
$content = file_get_contents($moduleFile);

$checks = [
    'uninstall_entrypoint' => "public function uninstall()",
    'install_seed_service' => "getBlogInstallService()->seedRootAndUnclassedCategories",
    'unclassed_recreate_service' => "getBlogInstallService()->recreateUnclassedCategory",
    'taxonomy_drop_service' => "getBlogTaxonomyService()->dropPostTaxonomies",
    'taxonomy_insert_service' => "getBlogTaxonomyService()->insert",
    'sitemap_service' => "getBlogSitemapService()->generate",
    'import_adapter_post' => "getLegacyImportAdapter()->getOrCreatePostByLinkRewrite",
    'import_adapter_category' => "getLegacyImportAdapter()->getOrCreateCategoryByLinkRewrite",
    'import_adapter_author' => "getLegacyImportAdapter()->getOrCreateAuthorByNickhandle",
    'import_adapter_tag' => "getLegacyImportAdapter()->getOrCreateTagByLinkRewrite",
    'import_adapter_image' => "getLegacyImportAdapter()->getOrCreatePostImage",
];

$errors = [];
foreach ($checks as $name => $needle) {
    if (strpos($content, $needle) === false) {
        $errors[] = sprintf('Missing check: %s (%s)', $name, $needle);
    }
}

if (!empty($errors)) {
    foreach ($errors as $error) {
        fwrite(STDERR, $error . PHP_EOL);
    }

    exit(1);
}

echo "All non-regression checks passed." . PHP_EOL;
