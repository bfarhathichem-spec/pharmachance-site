<?php $typ = trouver(types_produits(true), 'slug', $x['type'] ?? ''); $gal = array_values(array_filter($x['galerie'] ?? [], fn($g) => $g !== $x['image'])); $autres = array_slice(array_values(array_filter(produits(false, $x['type'] ?? ''), fn($y) => $y['slug'] !== $x['slug'])), 0, 3); ?>
<section class="entete"><div class="t"><p class="fil"><a href="<?= url() ?>">Accueil</a> / <a href="<?= url('produits') ?>">Produits</a><?php if ($typ): ?> / <a href="<?= url('produits/' . $typ['slug']) ?>"><?= h($typ['nom']) ?></a><?php endif; ?> / <?= h($x['nom']) ?></p><h1><?= h($x['nom']) ?></h1><?php if ($typ): ?><div class="meta"><span><?= h($typ['nom']) ?></span><span>Sur devis</span></div><?php endif; ?></div>
<div class="intro"><?= paragraphes($x['description'] ?? '') ?></div></section>
<?= visuel_haut($x['image'] ?? '', $x['nom'], $x['cadrage'] ?? '50% 50%') ?>
<section class="sec deux">
  <?php if (!empty($x['caracteristiques'])): ?><div><h2 class="h-moy">Caractéristiques</h2><ul class="liste"><?php foreach ($x['caracteristiques'] as $c): ?><li><?= h($c) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <div><h2 class="h-moy">Prix sur devis</h2><p style="font-size:18px">Dimensions, finitions et couleurs s'adaptent à votre pharmacie. Envoyez-nous votre demande : nous revenons vers vous avec une proposition chiffrée.</p>
  <p style="margin-top:24px"><a class="btn plein" href="<?= url('contact?produit=' . rawurlencode($x['slug'])) ?>">Demander un devis pour ce produit</a> <a class="btn ligne" href="<?= h(lien_wa('Bonjour, je souhaite un devis pour : ' . $x['nom'])) ?>" target="_blank" rel="noopener">WhatsApp</a></p></div>
</section>
<?php if ($gal): ?><section class="wrap" style="padding-bottom:60px"><h2 class="h-moy">Photos</h2><?= galerie($gal, $x['nom']) ?></section><?php endif; ?>
<?php if ($autres): ?><section class="wrap" style="padding-bottom:100px"><div class="head"><h2 class="h-moy">Dans la même gamme</h2><a class="lien" href="<?= url('produits/' . $typ['slug']) ?>">Tout voir</a></div><div class="grille"><?php foreach ($autres as $y) echo carte_produit($y); ?></div></section><?php endif; ?>
<?= cta('Ce produit vous intéresse ?') ?>
