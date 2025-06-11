# Ever PS Blog Module for PrestaShop 1.7 & 8

## English
Ever PS Blog is a free and multilingual module that adds a complete blog to your PrestaShop store. Create authors, categories, tags and posts, attach images and products, and manage comments easily. The module is multi‑shop ready and optimized for SEO.

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
A detailed guide in French is available at <https://www.team-ever.com/prestashop-1-7-un-module-de-blog-gratuit/>. You can also [support the project with a donation](https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE).

### Upgrade from 5.x
When updating to version 6.0.0, run the module upgrade from the back office. The `upgrade_module_6_0_0` script creates the new multishop association tables and migrates existing `id_shop` values so your posts, categories, authors, tags and images remain linked to the proper shops.

### WooCommerce REST Import
You can now fetch posts from a WooCommerce store using its REST API. Configure the API URL and credentials in the module settings and click **Import WooCommerce posts**. Tags and linked product IDs detected in the API data are also imported.

### WordPress REST Import
If WooCommerce is not installed, you can still import posts using the standard WordPress REST API. Provide the API URL in the module settings and click **Import WordPress posts** to fetch all blog content. Featured images, categories, authors and excerpts are imported and WordPress shortcodes are converted to Bootstrap compatible HTML.

---

## Français
Ever PS Blog est un module gratuit et multilingue qui ajoute un blog complet à votre boutique PrestaShop. Créez des auteurs, des catégories, des tags et des articles, associez des images et des produits, et gérez facilement les commentaires. Le module est compatible multi-boutique et optimisé pour le SEO.

### Installation
1. Téléchargez le module depuis le panneau d’administration de PrestaShop.
2. Installez-le puis configurez vos options.
3. Commencez à rédiger vos articles et à les organiser par catégories et tags.

### Hooks développeur
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

Le module **Ever Block** peut être utilisé pour ajouter du contenu sur ces hooks.

### Documentation
Un guide complet est disponible en français à l’adresse suivante : <https://www.team-ever.com/prestashop-1-7-un-module-de-blog-gratuit/>. Vous pouvez aussi [soutenir le projet par un don](https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE).

### Mise à jour depuis la version 5.x
Lors du passage à la version 6.0.0, lancez la mise à jour du module depuis le back office. Le script `upgrade_module_6_0_0` crée les nouvelles tables d’association multi-boutiques et migre les valeurs `id_shop` existantes afin que vos articles, catégories, auteurs, tags et images restent liés aux bonnes boutiques.

---

## Español
Ever PS Blog es un módulo gratuito y multilingüe que añade un blog completo a tu tienda PrestaShop. Permite crear autores, categorías, etiquetas y artículos, adjuntar imágenes y productos, y administrar fácilmente los comentarios. El módulo es compatible con multitienda y está optimizado para SEO.

### Instalación
1. Sube el módulo desde el panel de administración de PrestaShop.
2. Instálalo y configura tus opciones.
3. Comienza a redactar entradas y organízalas en categorías y etiquetas.

### Hooks para desarrolladores
- `actionBeforeEverBlogInitContent` (int blog_post_number, array everpsblogposts, array evercategories, int page)
- `actionBeforeEverCategoryInitContent` (obj blog_category, array blog_posts)
- `actionBeforeEverAuthorInitContent` (obj blog_author)
- `actionBeforeEverPostInitContent` (obj blog_post, array blog_tags, array blog_products, obj blog_author)
- `actionBeforeEverAuthorInitContent` (obj blog_tag, array blog_posts)

### Hooks para diseñadores
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

Puedes usar un módulo como **Ever Block** para insertar contenido en estos hooks.

### Documentación
Hay una guía detallada en francés disponible en <https://www.team-ever.com/prestashop-1-7-un-module-de-blog-gratuit/>. También puedes [apoyar el proyecto con una donación](https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE).

### Actualización desde la versión 5.x
Al actualizar a la versión 6.0.0 ejecuta la actualización del módulo desde el back office. El script `upgrade_module_6_0_0` crea las nuevas tablas de asociación para multitienda y migra los valores `id_shop` existentes para que tus entradas, categorías, autores, etiquetas e imágenes se mantengan vinculados a las tiendas correctas.

---

## Italiano
Ever PS Blog è un modulo gratuito e multilingue che aggiunge un blog completo al tuo negozio PrestaShop. Consente di creare autori, categorie, tag e articoli, allegare immagini e prodotti e gestire facilmente i commenti. Il modulo è compatibile con multishop ed è ottimizzato per la SEO.

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

È possibile utilizzare un modulo come **Ever Block** per inserire contenuti in questi hook.

### Documentazione
È disponibile una guida dettagliata in francese su <https://www.team-ever.com/prestashop-1-7-un-module-de-blog-gratuit/>. Puoi anche [sostenere il progetto con una donazione](https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE).

### Aggiornamento dalla versione 5.x
Quando si passa alla versione 6.0.0, eseguire l’aggiornamento del modulo dal back office. Lo script `upgrade_module_6_0_0` crea le nuove tabelle di associazione multishop e migra i valori `id_shop` esistenti affinché i tuoi articoli, categorie, autori, tag e immagini rimangano collegati ai negozi corretti.
