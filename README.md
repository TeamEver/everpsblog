# Ever PS Blog Module for PrestaShop 1.7 & 8

## English
Ever PS Blog is a free and multilingual module that adds a complete blog to your PrestaShop store. Create authors, categories, tags and posts, attach images and products, and manage comments easily. The module is multiâ€‘shop ready and optimized for SEO.

### Installation
1. Upload the module from the PrestaShop administration panel.
2. Install it and configure your options.
3. Start writing posts and organizing them into categories and tags.

### Developer Hooks
- `actionBeforeEverBlogInitContent` (int blog_post_number, array everpsblogposts, array evercategories, int page)
- `actionBeforeEverCategoryInitContent` (blog_category obj, array blog_posts)
- `actionBeforeEverAuthorInitContent` (blog_author obj)
- `actionBeforeEverPostInitContent` (blog_post obj, array blog_tags, array blog_products, blog_author obj)
- `actionBeforeEverAuthorInitContent` (blog_tag obj, array blog_posts)

### Webdesigner Hooks
- `displayBeforeEverLoop`
- `displayAfterEverLoop`
- `displayBeforeEverAuthor`
- `displayAfterEverAuthor`
- `displayBeforeEverCategory`
- `displayAfterEverCategory`
- `displayBeforeEverTag`
- `displayAfterEverTag`
- `displayBeforeEverPost`
- `displayAfterEverPost`
- `displayBeforeEverComment`
- `displayAfterEverComment`

You can use a module like **Ever Block** to insert content on these hooks.

### Documentation
Le plan de migration progressif Symfony/Doctrine est dÃ©taillÃ© ici: `docs/migration-plan-symfony-doctrine.md`.

La matrice de compatibilitÃ© montante des scripts d'upgrade est documentÃ©e dans `docs/upgrade-compatibility.md`.

A detailed guide in French is available at <https://www.team-ever.com/prestashop-1-7-un-module-de-blog-gratuit/>. You can also [support the project with a donation](https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE).

### Upgrade from 5.x
When updating to version 6.0.0, run the module upgrade from the back office. The `upgrade_module_6_0_0` script creates the new multishop association tables and migrates existing `id_shop` values so your posts, categories, authors, tags and images remain linked to the proper shops.

### WooCommerce REST Import
You can now fetch posts from a WooCommerce store using its REST API. Configure the API URL and credentials in the module settings and click **Import WooCommerce posts**. Tags and linked product IDs detected in the API data are also imported. Imported posts are linked with their categories and tags and a `wordpress_redirects.txt` file is generated to help you create 301 redirects.
The importer detects the language code from the remote data (when available) and assigns post content to the matching PrestaShop language.

### WordPress REST Import
If WooCommerce is not installed, you can still import posts using the standard WordPress REST API. Provide the API URL in the module settings and click **Import WordPress posts** to fetch all blog content. Featured images, categories, authors, tags and excerpts are imported, WordPress content is cleaned and adapted to Bootstrap compatible HTML and a `wordpress_redirects.txt` file lists old WordPress URLs so you can add 301 redirects in your `.htaccess`.
When posts include a language code (e.g. from Polylang), the module assigns the content only to the corresponding PrestaShop language.

---

## FranÃ§ais
Ever PS Blog est un module gratuit et multilingue qui ajoute un blog complet Ã  votre boutique PrestaShop. CrÃ©ez des auteurs, des catÃ©gories, des tags et des articles, associez des images et des produits, et gÃ©rez facilement les commentaires. Le module est compatible multi-boutique et optimisÃ© pour le SEO.

### Installation
1. TÃ©lÃ©chargez le module depuis le panneau dâ€™administration de PrestaShop.
2. Installez-le puis configurez vos options.
3. Commencez Ã  rÃ©diger vos articles et Ã  les organiser par catÃ©gories et tags.

### Hooks dÃ©veloppeur
- `actionBeforeEverBlogInitContent` (int blog_post_number, array everpsblogposts, array evercategories, int page)
- `actionBeforeEverCategoryInitContent` (obj blog_category, array blog_posts)
- `actionBeforeEverAuthorInitContent` (obj blog_author)
- `actionBeforeEverPostInitContent` (obj blog_post, array blog_tags, array blog_products, obj blog_author)
- `actionBeforeEverAuthorInitContent` (obj blog_tag, array blog_posts)

### Hooks webdesigner
- `displayBeforeEverLoop`
- `displayAfterEverLoop`
- `displayBeforeEverAuthor`
- `displayAfterEverAuthor`
- `displayBeforeEverCategory`
- `displayAfterEverCategory`
- `displayBeforeEverTag`
- `displayAfterEverTag`
- `displayBeforeEverPost`
- `displayAfterEverPost`
- `displayBeforeEverComment`
- `displayAfterEverComment`

Le module **Ever Block** peut Ãªtre utilisÃ© pour ajouter du contenu sur ces hooks.

### Documentation
Un guide complet est disponible en franÃ§ais Ã  lâ€™adresse suivante : <https://www.team-ever.com/prestashop-1-7-un-module-de-blog-gratuit/>. Vous pouvez aussi [soutenir le projet par un don](https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE).

### Mise Ã  jour depuis la version 5.x
Lors du passage Ã  la version 6.0.0, lancez la mise Ã  jour du module depuis le back office. Le script `upgrade_module_6_0_0` crÃ©e les nouvelles tables dâ€™association multi-boutiques et migre les valeurs `id_shop` existantes afin que vos articles, catÃ©gories, auteurs, tags et images restent liÃ©s aux bonnes boutiques.

---

## EspaÃ±ol
Ever PS Blog es un mÃ³dulo gratuito y multilingÃ¼e que aÃ±ade un blog completo a tu tienda PrestaShop. Permite crear autores, categorÃ­as, etiquetas y artÃ­culos, adjuntar imÃ¡genes y productos, y administrar fÃ¡cilmente los comentarios. El mÃ³dulo es compatible con multitienda y estÃ¡ optimizado para SEO.

### InstalaciÃ³n
1. Sube el mÃ³dulo desde el panel de administraciÃ³n de PrestaShop.
2. InstÃ¡lalo y configura tus opciones.
3. Comienza a redactar entradas y organÃ­zalas en categorÃ­as y etiquetas.

### Hooks para desarrolladores
- `actionBeforeEverBlogInitContent` (int blog_post_number, array everpsblogposts, array evercategories, int page)
- `actionBeforeEverCategoryInitContent` (obj blog_category, array blog_posts)
- `actionBeforeEverAuthorInitContent` (obj blog_author)
- `actionBeforeEverPostInitContent` (obj blog_post, array blog_tags, array blog_products, obj blog_author)
- `actionBeforeEverAuthorInitContent` (obj blog_tag, array blog_posts)

### Hooks para diseÃ±adores
- `displayBeforeEverLoop`
- `displayAfterEverLoop`
- `displayBeforeEverAuthor`
- `displayAfterEverAuthor`
- `displayBeforeEverCategory`
- `displayAfterEverCategory`
- `displayBeforeEverTag`
- `displayAfterEverTag`
- `displayBeforeEverPost`
- `displayAfterEverPost`
- `displayBeforeEverComment`
- `displayAfterEverComment`

Puedes usar un mÃ³dulo como **Ever Block** para insertar contenido en estos hooks.

### DocumentaciÃ³n
Hay una guÃ­a detallada en francÃ©s disponible en <https://www.team-ever.com/prestashop-1-7-un-module-de-blog-gratuit/>. TambiÃ©n puedes [apoyar el proyecto con una donaciÃ³n](https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE).

### ActualizaciÃ³n desde la versiÃ³n 5.x
Al actualizar a la versiÃ³n 6.0.0 ejecuta la actualizaciÃ³n del mÃ³dulo desde el back office. El script `upgrade_module_6_0_0` crea las nuevas tablas de asociaciÃ³n para multitienda y migra los valores `id_shop` existentes para que tus entradas, categorÃ­as, autores, etiquetas e imÃ¡genes se mantengan vinculados a las tiendas correctas.

---

## Italiano
Ever PS Blog Ã¨ un modulo gratuito e multilingue che aggiunge un blog completo al tuo negozio PrestaShop. Consente di creare autori, categorie, tag e articoli, allegare immagini e prodotti e gestire facilmente i commenti. Il modulo Ã¨ compatibile con multishop ed Ã¨ ottimizzato per la SEO.

### Installazione
1. Carica il modulo dal pannello di amministrazione di PrestaShop.
2. Installalo e configura le tue opzioni.
3. Inizia a scrivere articoli e a organizzarli in categorie e tag.

### Hook per sviluppatori
- `actionBeforeEverBlogInitContent` (int blog_post_number, array everpsblogposts, array evercategories, int page)
- `actionBeforeEverCategoryInitContent` (obj blog_category, array blog_posts)
- `actionBeforeEverAuthorInitContent` (obj blog_author)
- `actionBeforeEverPostInitContent` (obj blog_post, array blog_tags, array blog_products, obj blog_author)
- `actionBeforeEverAuthorInitContent` (obj blog_tag, array blog_posts)

### Hook per web designer
- `displayBeforeEverLoop`
- `displayAfterEverLoop`
- `displayBeforeEverAuthor`
- `displayAfterEverAuthor`
- `displayBeforeEverCategory`
- `displayAfterEverCategory`
- `displayBeforeEverTag`
- `displayAfterEverTag`
- `displayBeforeEverPost`
- `displayAfterEverPost`
- `displayBeforeEverComment`
- `displayAfterEverComment`

Ãˆ possibile utilizzare un modulo come **Ever Block** per inserire contenuti in questi hook.

### Documentazione
Ãˆ disponibile una guida dettagliata in francese su <https://www.team-ever.com/prestashop-1-7-un-module-de-blog-gratuit/>. Puoi anche [sostenere il progetto con una donazione](https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE).

### Aggiornamento dalla versione 5.x
Quando si passa alla versione 6.0.0, eseguire lâ€™aggiornamento del modulo dal back office. Lo script `upgrade_module_6_0_0` crea le nuove tabelle di associazione multishop e migra i valori `id_shop` esistenti affinchÃ© i tuoi articoli, categorie, autori, tag e immagini rimangano collegati ai negozi corretti.
