# Ever PS Blog for Prestashop 1.7 & 8

Multilingual blog module for Prestashop 1.7  & 8

Prestashop administrators can create authors, tags categories and posts. Comments can be allowed on blog, users can be banned too.

https://www.team-ever.com/produit/prestashop-module-de-blog-gratuit/

## Prestashop 1.7 & 8 free blog module
This free module allows you to create a blog on Prestashop 1.7 & 8

[You can make a donation to support the development of free modules by clicking on this link](https://www.paypal.com/donate?hosted_button_id=3CM3XREMKTMSE)

## Hooks for developpers
You can use actions hooks by hooking on these custom action hooks :
- actionBeforeEverBlogInitContent (params : int blog_post_number, array everpsblogposts, array evercategories, int page)
- actionBeforeEverCategoryInitContent (params : blog_category obj, array blog_posts)
- actionBeforeEverAuthorInitContent (params : obj blog_author)
- actionBeforeEverPostInitContent (params : blog_post obj, array blog_tags, array blog_products, obj blog_author)
- actionBeforeEverAuthorInitContent (params : obj blog_tag, array blog_posts)

## Hooks for webdesigners
Tou can use these diplay hooks by hooking modules on theses custom display hooks :
- displayBeforeEverLoop (no params) : displayed before post loop
- displayAfterEverLoop (no params) : displayed after post loop
- displayBeforeEverAuthor (params: obj everblogauthor) : displayed before author page
- displayAfterEverAuthor (params: obj everblogauthor) : displayed after author page
- displayBeforeEverCategory (params: obj everblogcategory) : displayed before category page
- displayAfterEverCategory (params: obj everblogcategory) : displayed after category page
- displayBeforeEverTag (params: obj everblogtag) : displayed before tag page
- displayAfterEverTag (params: obj everblogtag) : displayed after tag page
- displayBeforeEverPost (params: obj everblogpost) : displayed before post page
- displayAfterEverPost (params: obj everblogpost) : displayed after post page
- displayBeforeEverComment (no params) : displayed before comments on post page
- displayAfterEverComment (params: obj everblogpost) : displayed after comments on post page

In order to use these hooks, [you can use free HTML blocks module Ever Block, available here](https://www.team-ever.com/prestashop-module-bloc-editeur-html-illimite-shortcode/)

## Documentation (French only)
Available at https://www.team-ever.com/prestashop-1-7-un-module-de-blog-gratuit/
