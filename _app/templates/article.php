<?php
[$corps, $som] = md($a['contenu']);
$autres = array_slice(array_values(array_filter(articles(), fn($x) => $x['slug'] !== $a['slug'])), 0, 3);
?>
<article class="art">
<header class="art-entete">
  <p class="fil"><a href="<?= url() ?>">Accueil</a> / <a href="<?= url('articles') ?>">Articles</a> / <?= h($a['categorie'] ?? 'Conseils') ?></p>
  <p class="art-meta"><span><?= h($a['categorie'] ?? 'Conseils') ?></span><span><?= h(date_longue($a['date'] ?? '')) ?></span><span><?= temps_lecture($a['contenu']) ?> min de lecture</span></p>
  <h1><?= h($a['titre']) ?></h1>
  <p class="art-chapo"><?= h($a['resume']) ?></p>
</header>
<div class="zoom-hero" data-zoom><img src="<?= h(media($a['image'])) ?>" alt="<?= h($a['image_alt'] ?? $a['titre']) ?>" fetchpriority="high"></div>
<div class="art-corps">
  <?php if (count($som) > 2): ?><nav class="sommaire" aria-label="Sommaire"><b>Sommaire</b><ol><?php foreach ($som as $s): ?><li><a href="#<?= h($s['id']) ?>"><?= h($s['t']) ?></a></li><?php endforeach; ?></ol></nav><?php else: ?><div></div><?php endif; ?>
  <div class="texte">
    <?= $corps ?>
    <?php if (!empty($a['faq'])): ?><section class="art-faq"><h2 id="questions-frequentes">Questions fréquentes</h2><?php foreach ($a['faq'] as $f): ?><details><summary><?= h($f['q']) ?></summary><p><?= h($f['r']) ?></p></details><?php endforeach; ?></section><?php endif; ?>
    <p class="art-signature">Article rédigé par l'équipe PharmaChance, agenceurs de pharmacies en Tunisie.</p>
  </div>
</div>
</article>
<?php if ($autres): ?><section class="wrap art-autres"><div class="head"><h2 class="h-moy">À lire aussi</h2><a class="lien" href="<?= url('articles') ?>">Tous les articles</a></div><div class="art-grille trois"><?php foreach ($autres as $x) echo carte_article($x); ?></div></section><?php endif; ?>
<?= cta('Parlons de votre projet') ?>
