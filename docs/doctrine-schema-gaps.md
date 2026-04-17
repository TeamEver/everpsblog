# Doctrine mapping vs schéma SQL réel

## Alignements réalisés
- Les colonnes `allowed_groups` sont mappées en `string(255)` nullable.
- Les colonnes `groups` sont mappées en `text` nullable.
- Les colonnes `count` sont mappées en `integer` avec défaut `0`.
- Les flags `active`, `indexable`, `follow`, `sitemap`, `starred` sont mappés en `integer` (et non `boolean`) pour coller aux `int(1|10)` SQL.
- Ajout du mapping `id_shop` sur les tables racines `post/category/tag/author/image`.
- Ajout des associations de jointure (langues, shops, taxonomies, produits) via entités de jointure dédiées.

## Écarts conservés (documentés)
1. Les IDs de langue, boutique et produit restent des scalaires (`int`) plutôt que des relations Doctrine vers les tables PrestaShop Core (`lang`, `shop`, `product`) afin d'éviter un couplage fort à la couche Core.
2. Les colonnes JSON legacy (`post_categories`, `post_tags`, `post_products`, `category_products`, `tag_products`, `author_products`) ne sont pas exploitées dans les relations Doctrine, la source de vérité retenue est la table de jointure dédiée.
3. L’association `Image -> Post/Category/Tag/Author` est polymorphe via (`image_type`, `id_element`) et ne peut pas être exprimée proprement en relation Doctrine forte sans discriminator custom : les requêtes restent orientées repository.
