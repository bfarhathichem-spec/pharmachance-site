<?php $L = secteurs_actifs(); ?>
<section class="entete"><div class="t"><p class="fil"><a href="<?= url() ?>">Accueil</a> / Autres secteurs</p><h1>Nos réalisations dans d'autres secteurs</h1></div><p class="intro">Au-delà des pharmacies, PharmaChance aménage aussi d'autres lieux qui reçoivent du public. Voici une sélection de ces projets.</p></section>
<?php if (count($L) > 1): ?><section class="wrap"><nav class="filtres" aria-label="Secteurs"><?php foreach ($L as $s): ?><a class="filtre-lien" href="#<?= h($s['slug']) ?>"><?= h($s['nom']) ?></a><?php endforeach; ?></nav></section><?php endif; ?>
<?php foreach ($L as $s): $P = realisations_autres($s['slug']); ?>
<section class="wrap secteur-bloc" id="<?= h($s['slug']) ?>"><div class="head"><h2 class="xt"><?= h($s['nom']) ?></h2><?php if (count($P) > 6): ?><a class="lien" href="<?= url('autres-secteurs/' . $s['slug']) ?>">Voir les <?= count($P) ?> projets</a><?php endif; ?></div>
<?php if (trim($s['intro'] ?? '') !== ''): ?><p class="secteur-intro"><?= h($s['intro']) ?></p><?php endif; ?>
<div class="grille"><?php foreach (array_slice($P, 0, 6) as $x) echo carte($x); ?></div></section>
<?php endforeach; ?>
<?php if (!$L): ?><section class="wrap"><p class="vide" style="display:block">Les premiers projets arrivent bientôt.</p></section><?php endif; ?>
<div style="height:80px"></div>
<?= cta('Un projet hors pharmacie ?', 'Hôtel, école, commerce : envoyez-nous une photo du lieu, nous revenons vers vous avec une première idée.') ?>
