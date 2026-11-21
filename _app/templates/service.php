<?php $rel = array_slice(array_values(array_filter(realisations(), fn($x) => in_array($s['cle'], $x['services'] ?? [], true))), 0, 6); ?>
<section class="entete"><div class="t"><p class="fil"><a href="<?= url() ?>">Accueil</a> / <a href="<?= url('services') ?>">Services</a> / <?= h($s['nom']) ?></p><h1><?= h($s['nom']) ?></h1></div><p class="intro"><?= h($s['intro']) ?></p></section>
<?= visuel_haut($s['image'], $s['nom'], $s['cadrage'] ?? '50% 50%') ?>
<?php if (!empty($s['accroche'])): ?><section class="wrap def"><p><?= h($s['accroche']) ?></p></section><?php endif; ?>
<section class="sec deux">
  <?php if (!empty($s['besoins'])): ?><div><h2 class="h-moy">Pour qui ?</h2><ul class="liste"><?php foreach ($s['besoins'] as $b): ?><li><?= h($b) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <div><h2 class="h-moy">Ce que nous faisons</h2><ul class="liste"><?php foreach ($s['comprend'] ?? [] as $c): ?><li><?= h($c) ?></li><?php endforeach; ?></ul><p style="margin-top:28px"><a class="btn plein" href="<?= url('contact?projet=' . rawurlencode($s['cle'])) ?>">Demander un devis pour ce projet</a></p></div>
</section>
<?php if (!empty($s['texte'])): [$corps] = md($s['texte']); ?><section class="wrap serv-texte"><div class="texte"><?= $corps ?></div></section><?php endif; ?>
<?php if ($rel): ?><section class="wrap" style="padding-bottom:120px"><div class="head"><h2 class="h-moy">Réalisations associées</h2><a class="lien" href="<?= url('realisations?service=' . rawurlencode($s['cle'])) ?>">Toutes les réalisations « <?= h($s['nom']) ?> »</a></div><div class="grille"><?php foreach ($rel as $x) echo carte($x); ?></div></section><?php endif; ?>
<?php if (!empty($s['faq'])) echo faq_bloc($s['faq'], 'Questions fréquentes : ' . mb_strtolower($s['nom'])); ?>
<?php $autres = array_values(array_filter(services(), fn($x) => $x['slug'] !== $s['slug'])); ?>
<section class="wrap serv-autres"><h2 class="h-moy">Nos autres expertises</h2><p><?php foreach ($autres as $k => $x): ?><a class="lien" href="<?= url('services/' . $x['slug']) ?>"><?= h($x['nom']) ?></a><?= $k < count($autres) - 1 ? ' · ' : '' ?><?php endforeach; ?></p></section>
<?= cta() ?>
