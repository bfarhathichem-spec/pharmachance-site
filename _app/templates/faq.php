<?php $gen = faq(); $parArt = array_values(array_filter(articles(), fn($a) => !empty($a['faq']))); ?>
<section class="entete"><div class="t"><p class="fil"><a href="<?= url() ?>">Accueil</a> / Questions fréquentes</p><h1>Questions <b>fréquentes</b></h1></div><p class="intro">Les réponses aux questions que les pharmaciens nous posent le plus souvent. Une autre question ? Appelez-nous ou écrivez-nous sur WhatsApp.</p></section>
<?= faq_bloc($gen, 'PharmaChance et nos services') ?>
<?php foreach ($parArt as $a): ?><section class="wrap faq"><h2 class="h-moy"><?= h($a['categorie'] ?? 'Conseils') ?></h2><?php foreach ($a['faq'] as $f): ?><details><summary><?= h($f['q']) ?></summary><p><?= h($f['r']) ?> <a class="lien" href="<?= url('articles/' . $a['slug']) ?>">Lire l'article</a></p></details><?php endforeach; ?></section><?php endforeach; ?>
<div style="height:100px"></div>
<?= cta() ?>
