<section class="entete"><div class="t"><p class="fil"><a href="<?= url() ?>">Accueil</a> / Plan du site</p><h1>Plan du site</h1></div></section>
<section class="wrap plan">
  <div><h2>Pages</h2><a href="<?= url() ?>">Accueil</a><a href="<?= url('a-propos') ?>">À propos</a><a href="<?= url('services') ?>">Services</a><a href="<?= url('realisations') ?>">Réalisations</a><a href="<?= url('vitrines') ?>">Thèmes des vitrines</a><a href="<?= url('articles') ?>">Articles</a><a href="<?= url('faq') ?>">Questions fréquentes</a><a href="<?= url('contact') ?>">Contact et devis</a></div>
  <div><h2>Services</h2><?php foreach (services() as $s): ?><a href="<?= url('services/' . $s['slug']) ?>"><?= h($s['nom']) ?></a><?php endforeach; ?></div>
  <div><h2>Réalisations</h2><?php foreach (realisations() as $p): ?><a href="<?= url(lien_realisation($p)) ?>"><?= h($p['titre']) ?></a><?php endforeach; ?></div>
  <?php if (secteurs_actifs()): ?><div><h2>Autres secteurs</h2><a href="<?= url('autres-secteurs') ?>">Tous les autres secteurs</a><?php foreach (secteurs_actifs() as $x): ?><a href="<?= url('autres-secteurs/' . $x['slug']) ?>"><?= h($x['nom']) ?></a><?php endforeach; ?><?php foreach (realisations_autres() as $p): ?><a href="<?= url(lien_realisation($p)) ?>"><?= h($p['titre']) ?></a><?php endforeach; ?></div><?php endif; ?>
  <div><h2>Articles</h2><?php foreach (articles() as $a): ?><a href="<?= url('articles/' . $a['slug']) ?>"><?= h($a['titre']) ?></a><?php endforeach; ?></div>
  <div><h2>Informations</h2><?php foreach (pages_site() as $x): ?><a href="<?= url($x['slug']) ?>"><?= h($x['titre']) ?></a><?php endforeach; ?></div>
</section>
<div style="height:100px"></div>
