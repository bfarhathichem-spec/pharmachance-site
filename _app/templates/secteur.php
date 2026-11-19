<?php $P = realisations_autres($sec['slug']); ?>
<section class="entete"><div class="t"><p class="fil"><a href="<?= url() ?>">Accueil</a> / <a href="<?= url('autres-secteurs') ?>">Autres secteurs</a> / <?= h($sec['nom']) ?></p><h1><?= h($sec['nom']) ?></h1></div><p class="intro"><?= h(trim($sec['intro'] ?? '') ?: 'Les projets de PharmaChance dans ce secteur.') ?></p></section>
<section class="wrap" style="padding-bottom:100px"><div class="grille"><?php foreach ($P as $x) echo carte($x); ?></div>
<?php if (!$P): ?><p class="vide" style="display:block">Les premiers projets arrivent bientôt.</p><?php endif; ?>
<p style="margin-top:48px;font-size:17px"><a class="lien" href="<?= url('autres-secteurs') ?>">Tous les autres secteurs</a> · <a class="lien" href="<?= url('realisations') ?>">Nos réalisations de pharmacies</a></p></section>
<?= cta('Un projet similaire ?') ?>
