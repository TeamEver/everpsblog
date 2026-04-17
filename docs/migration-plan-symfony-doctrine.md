# Plan de migration progressive vers Symfony/Doctrine

Ce plan propose une coexistence contrôlée entre le legacy (`ObjectModel`, contrôleurs historiques) et la nouvelle stack Symfony/Doctrine afin de limiter le risque en production.

## Objectifs
- Migrer de façon incrémentale, ressource par ressource.
- Garder une compatibilité totale des hooks, tabs BO et modules tiers pendant la transition.
- Permettre une bascule par écran BO via feature flags, avec rollback immédiat.

## Pré-requis transverses
- Ajouter une configuration de feature flags (table de config module + UI BO):
  - `EVERPSBLOG_FF_BO_POSTS_SYMFONY`
  - `EVERPSBLOG_FF_BO_CATEGORIES_SYMFONY`
  - `EVERPSBLOG_FF_BO_TAGS_SYMFONY`
  - `EVERPSBLOG_FF_BO_AUTHORS_SYMFONY`
  - `EVERPSBLOG_FF_BO_COMMENTS_SYMFONY`
- Conserver des façades de service stables côté contrôleurs pour éviter de dupliquer la logique métier.
- Définir un protocole de rollback: désactivation du flag => retour immédiat au contrôleur legacy.

---


## Décision appliquée (BO)
- Les contrôleurs BO legacy ne portent plus de logique métier: ils servent uniquement de proxy de redirection vers les routes Symfony (`/src/Controller/Admin`).
- Le BO n'utilise plus les templates Smarty legacy (`views/templates/admin/*.tpl`) pour les écrans métier Posts/Catégories/Tags/Auteurs/Commentaires ; les écrans actifs sont rendus via Twig (`views/templates/admin/modern/*.html.twig`).
- La couche legacy front reste limitée au contrôleur front historique (`classes/controller/FrontController.php`) pour conserver la compatibilité FO pendant la transition.

## Phase 1 — Ressource pilote : Posts

### Périmètre
- Migrer uniquement l'écran BO Posts (listing + création/édition + suppression) vers Symfony/Doctrine.
- Ne pas changer le contrat fonctionnel des hooks existants.

### Implémentation
1. **Données**
   - Finaliser les entités/repositories Doctrine pour `Post` et relations strictement nécessaires.
   - Encapsuler les accès legacy restants derrière des adapters (anti-corruption layer).
2. **BO Symfony**
   - Activer le contrôleur Symfony Posts derrière `EVERPSBLOG_FF_BO_POSTS_SYMFONY`.
   - Conserver les routes/tabs legacy en fallback quand le flag est à `0`.
3. **Hooks**
   - Vérifier l'émission des hooks historiques aux mêmes points de cycle de vie.
   - Ajouter un test de non-régression sur le payload minimal des hooks critiques.

### Critères d’acceptation
- Parité fonctionnelle BO Posts entre legacy et Symfony.
- Temps de réponse équivalent ou meilleur sur listing/édition.
- Aucun module tiers cassé sur hooks liés aux posts.

---

## Phase 2 — Catégories, Tags, Auteurs, Commentaires

### Périmètre
- Étendre le modèle Symfony/Doctrine aux écrans BO:
  - Catégories
  - Tags
  - Auteurs
  - Commentaires

### Implémentation
1. **Migration par lot fonctionnel**
   - Ordre recommandé: Catégories → Tags → Auteurs → Commentaires.
   - Activer chaque écran via son feature flag dédié.
2. **Relations métier**
   - Valider l’intégrité des associations (`PostCategory`, `PostTag`, liens auteur/commentaire).
   - Vérifier les comportements multi-boutique et multi-langue à chaque étape.
3. **Interop legacy**
   - Maintenir la compatibilité des services legacy tant que toutes les ressources ne sont pas migrées.

### Critères d’acceptation
- Chaque écran BO peut être basculé indépendamment.
- Aucun écart de comportement observable sur workflows éditoriaux.
- Pas de régression sur permissions BO et actions de masse.

---

## Phase 3 — Retrait legacy (`ObjectModel` + contrôleurs restants)

### Périmètre
- Supprimer les dépendances legacy une fois toutes les ressources validées en production.

### Implémentation
1. **Nettoyage code**
   - Déprécier puis retirer les classes `ObjectModel` non utilisées.
   - Supprimer les contrôleurs admin legacy devenus inactifs.
2. **Routage et tabs**
   - Pointer définitivement tabs/routage BO vers Symfony.
   - Garder une fenêtre de sécurité (release N+1) avant suppression définitive des fallbacks.
3. **Documentation & support**
   - Mettre à jour la doc technique (architecture, debug, rollback).
   - Préparer une note de migration pour intégrateurs/modules tiers.

### Critères d’acceptation
- Zéro dépendance runtime au legacy pour le BO Blog.
- Couverture de tests suffisante pour autoriser la suppression des fallbacks.
- Chemin d’upgrade documenté et reproductible.

---

## Stratégie de tests de non-régression (coexistence)

### 1) Tests automatisés
- **Tests unitaires**
  - Assemblage commande/DTO par ressource.
  - Mapping entités Doctrine et contraintes métier.
- **Tests d’intégration**
  - Contrôleurs Symfony sous feature flag ON.
  - Fallback legacy sous feature flag OFF.
- **Tests hooks/module tabs**
  - Vérifier que les hooks historiques sont toujours déclenchés.
  - Vérifier présence/accès des tabs BO existants selon droits employés.

### 2) Tests de parité (golden master)
- Capturer un jeu de scénarios legacy (CRUD + filtres + actions de masse).
- Rejouer les mêmes scénarios en Symfony et comparer:
  - statut HTTP,
  - messages BO,
  - effets DB,
  - hooks déclenchés.

### 3) Validation manuelle ciblée
- Parcours éditeur complet: créer/éditer/publier/dépublier/supprimer.
- Vérification multi-langue, multi-boutique, SEO et association produits.

## Gouvernance de déploiement
- Déployer flag OFF par défaut.
- Activer progressivement par environnement puis par boutique pilote.
- Sur incident: repasser immédiatement le flag concerné à OFF (rollback sans redeploy).
