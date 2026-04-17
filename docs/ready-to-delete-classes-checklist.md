# Checklist “ready-to-delete classes/”

> Statut: **pré-suppression validée** pour la bascule Symfony/Doctrine.

## 1) Hooks historiques
- [ ] Inventaire des hooks legacy encore déclenchés (`beforeEverBlogInit`, `afterEverBlogInit`, hooks `actionObjectEverPsBlog*`).
- [ ] Vérifier qu’un équivalent Symfony/Doctrine existe pour chaque hook critique.
- [ ] Capturer le payload minimal attendu (IDs, shop/lang, statut, slug).
- [ ] Confirmer l’absence de modules tiers consommant uniquement les hooks `ObjectModel`.

## 2) Permissions Back Office
- [ ] Vérifier le mapping ACL sur `AdminEverPsBlogPost|Category|Tag|Author|Comment`.
- [ ] Contrôler la parité droits CRUD après redirection vers routes Symfony.
- [ ] Vérifier les actions de masse et restrictions employé/profil.

## 3) Routes legacy
- [ ] Cartographier routes legacy FO/BO encore appelées.
- [ ] Vérifier redirections 301/302 vers routes Symfony actives.
- [ ] Journaliser l’usage réel des contrôleurs legacy (logs de détection).
- [ ] Confirmer que les tabs BO pointent uniquement vers les routes Symfony signées.

## 4) Payloads attendus
- [ ] Parité des payloads CRUD pour post/category/tag/author/comment.
- [ ] Parité des compteurs (listing, pagination, comments count, filtres).
- [ ] Parité des contraintes (required fields, statuts publiés, IDs liés).

## 5) SEO / canonical
- [ ] Vérifier canonical FO sur post/category/tag/author.
- [ ] Vérifier robots (`index|noindex`, `follow|nofollow`) sur écrans migrés.
- [ ] Vérifier méta title/description en pagination.

## 6) Multi-lang / multi-shop
- [ ] Vérifier les jointures translation/shop sur tous les repositories Doctrine.
- [ ] Vérifier fallback des langues manquantes.
- [ ] Vérifier isolation multi-boutique pour lectures/écritures.

## 7) Plan d’exécution de suppression en 2 temps
### (a) Dépréciation + détection d’usage
- [x] Logging explicite des accès legacy FO/BO.
- [x] Logging explicite au chargement des classes `classes/EverPsBlog*`.
- [ ] Stabilisation d’une fenêtre d’observation (minimum 1 cycle de release).

### (b) Suppression finale
- [ ] Supprimer `/classes/*` et les `require_once` restants.
- [ ] Supprimer `controllers/front/*` et `controllers/admin/*` devenus inactifs.
- [ ] Purger les références de compatibilité dans services/routes/docs.
- [ ] Exécuter suite de parité complète avant release de retrait.
