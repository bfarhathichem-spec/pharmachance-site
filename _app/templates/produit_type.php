<?php $P = produits(false, $typ['slug']); ?>
<section class="entete"><div class="t"><p class="fil"><a href="<?= url() ?>">Accueil</a> / <a href="<?= url('produits') ?>">Produits</a> / <?= h($typ['nom']) ?></p><h1><?= h($typ['nom']) ?></h1></div><p class="intro"><?= h(trim($typ['description'] ?? '') ?: 'Nos produits de ce type, fabriqués sur mesure.') ?></p></section>
<?php if ($P && $P[0]['image']) echo visuel_haut($P[0]['image'], $P[0]['nom'], $P[0]['cadrage'] ?? '50% 50%'); ?>
<section class="wrap" style="padding-bottom:100px"><?php if ($P): ?><div class="grille"><?php foreach ($P as $x) echo carte_produit($x); ?></div><?php else: ?><p class="vide" style="display:block">Les modèles arrivent bientôt. Décrivez-nous votre besoin, nous vous proposons une solution.</p><?php endif; ?>
<p style="margin-top:40px"><a class="btn plein" href="<?= url('contact?produit=' . rawurlencode('type:' . $typ['slug'])) ?>">Demander un devis</a> <a class="lien" style="margin-left:18px" href="<?= url('produits') ?>">Tous les produits</a></p></section>
<?= cta() ?>
