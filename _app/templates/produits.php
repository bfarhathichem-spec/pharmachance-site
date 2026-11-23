<?php $T = types_produits(); $tousP = produits(); $p0 = $tousP[0] ?? null; ?>
<section class="entete"><div class="t"><p class="fil"><a href="<?= url() ?>">Accueil</a> / Produits</p><h1>Nos produits pour la pharmacie</h1></div><p class="intro">Présentoirs, comptoirs, gondoles, rangements, enseignes : du mobilier et des équipements fabriqués pour l'officine. Chaque produit s'adapte à votre espace : demandez un devis.</p></section>
<?php if ($p0 && $p0['image']) echo visuel_haut($p0['image'], $p0['nom'], $p0['cadrage'] ?? '50% 50%'); ?>
<section class="wrap def"><p>Nous ne vendons pas sur catalogue avec un prix fixe : un présentoir ou un comptoir se dessine selon la place disponible, vos gammes et vos couleurs. Choisissez un modèle ou un type de produit, décrivez votre besoin, et nous revenons vers vous avec une proposition et un devis.</p></section>
<?php if (count($T) > 1): ?><section class="wrap"><nav class="filtres" aria-label="Types de produits"><?php foreach ($T as $t): ?><a class="filtre-lien" href="#<?= h($t['slug']) ?>"><?= h($t['nom']) ?></a><?php endforeach; ?></nav></section><?php endif; ?>
<?php foreach ($T as $t): $P = produits(false, $t['slug']); ?>
<section class="wrap secteur-bloc" id="<?= h($t['slug']) ?>"><div class="head"><h2 class="xt"><?= h($t['nom']) ?></h2><?php if ($P): ?><a class="lien" href="<?= url('produits/' . $t['slug']) ?>"><?= count($P) > 1 ? 'Voir les ' . count($P) . ' produits' : 'Voir le produit' ?></a><?php endif; ?></div>
<?php if (trim($t['description'] ?? '') !== ''): ?><p class="secteur-intro"><?= h($t['description']) ?></p><?php endif; ?>
<?php if ($P): ?><div class="grille"><?php foreach (array_slice($P, 0, 6) as $x) echo carte_produit($x); ?></div>
<?php else: ?><p><a class="btn ligne" href="<?= url('contact?produit=' . rawurlencode('type:' . $t['slug'])) ?>">Demander un devis : <?= h(mb_strtolower($t['nom'])) ?></a></p><?php endif; ?></section>
<?php endforeach; ?>
<div style="height:60px"></div>
<?= cta('Un équipement en tête ?', 'Envoyez-nous une photo de l\'emplacement et ses mesures : nous vous proposons un modèle et un devis.') ?>
