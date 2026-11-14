<?php $L = articles(); $cats = array_values(array_unique(array_map(fn($a) => $a['categorie'] ?? 'Conseils', $L))); ?>
<section class="art-tete">
  <p class="fil"><a href="<?= url() ?>">Accueil</a> / Articles</p>
  <h1 class="geant" aria-label="Articles"><span class="defile-mot">Articles</span></h1>
  <p class="art-sous">Les conseils de nos agenceurs pour créer, rénover et faire vivre votre pharmacie.</p>
</section>
<section class="wrap def centre"><p><?= h(texte_site('articles_intro')) ?></p></section>
<section class="wrap art-liste">
  <?php if (count($cats) > 1): ?><div class="filtres" role="group" aria-label="Filtrer par sujet" data-initial="tous"><button type="button" data-k="tous" aria-pressed="true">Tous</button><?php foreach ($cats as $c): ?><button type="button" data-k="<?= h(slugify($c)) ?>" aria-pressed="false"><?= h($c) ?></button><?php endforeach; ?></div><?php endif; ?>
  <div class="art-grille grille">
  <?php foreach ($L as $i => $a): ?>
    <?= str_replace('<a class="art-carte', '<a data-cat="' . h(slugify($a['categorie'] ?? 'conseils')) . '" class="art-carte', carte_article($a, 'f' . ($i % 6))) ?>
  <?php endforeach; ?>
  </div>
  <?php if (!$L): ?><p class="vide" style="display:block">Les premiers articles arrivent bientôt.</p><?php endif; ?>
</section>
<?= cta('Une question sur votre pharmacie ?', 'Nos articles ne remplacent pas une visite : envoyez-nous une photo du local, nous vous répondons.') ?>
