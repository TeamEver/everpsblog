# Compatibilité montante des upgrades Ever PS Blog

Ce document décrit le chemin **source -> cible** garanti pour les scripts de migration actifs après extraction de la logique legacy vers des services dédiés.

## Plages de compatibilité

| Version source | Version cible | Script(s) exécuté(s) | Garanties |
|---|---|---|---|
| `3.0.0` | `3.0.1` | `upgrade-3.0.1.php` | Renommage des hooks historique -> `action*`, création des tables auteur/taxonomies, création idempotente de l’onglet BO auteurs, migration JSON -> tables de liaison. |
| `4.1.0` | `4.1.2` | `upgrade-4.1.0.php`, `upgrade-4.1.2.php` | Création idempotente de `ever_blog_image` et migration conditionnelle des références d’images legacy. |
| `5.0.0` | `5.0.1` | `upgrade-5.0.1.php` | Bascule idempotente des images depuis `views/img/*` vers `/img/{type}/`, mise à jour des liens DB et nettoyage non destructif des dossiers legacy. |

## Idempotence

Chaque service d’upgrade est conçu pour supporter la réexécution :

- `CREATE TABLE IF NOT EXISTS` pour les structures.
- Vérification préalable de colonne (`information_schema`) avant `ALTER TABLE`.
- `INSERT IGNORE` sur les tables de liaison post/taxonomie.
- Vérification d’existence avant copie/suppression de fichiers.
- Vérification d’existence des hooks/tabs avant renommage/création.

## Retrait de la dépendance legacy `/classes` dans les upgrades

Les scripts d’upgrade actifs migrés (`3.0.1`, `4.1.0`, `4.1.2`, `5.0.1`) n’appellent plus directement les classes legacy (`Hook`, `Tab`, `Language`, modèles custom) et délèguent leur logique à des services `src/Service/Upgrade/*` basés sur SQL ciblé + opérations de fichiers idempotentes.
