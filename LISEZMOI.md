# PharmaChance : site pharmachance.com

Code du site, déployé automatiquement par OVH (hébergement pharmhm) à chaque mise à jour de la branche `main`.

- Les pages publiques sont à la racine, l'administration dans `admin/`.
- Le code PHP est dans `_app/` (fermé au public).
- Les contenus gérés par le client (textes, projets, photos, demandes, comptes) sont dans le dossier `pharmachance-donnees`, à côté du site sur l'hébergement. Ils ne sont pas dans ce dépôt et aucun déploiement ne les modifie.
- `_app/seed/` contient les contenus d'origine, copiés une seule fois au premier lancement.
