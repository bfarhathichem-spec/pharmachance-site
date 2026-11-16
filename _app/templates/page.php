<?php [$corps, $som] = md(jetons(ajuster_legal($pg['slug'], $pg['contenu']))); ?>
<section class="entete"><div class="t"><p class="fil"><a href="<?= url() ?>">Accueil</a> / <?= h($pg['titre']) ?></p><h1><?= h($pg['titre']) ?></h1></div><p class="intro"><?= h($pg['resume'] ?? '') ?><br><small class="maj">Mise à jour le <?= h(date_longue($pg['maj'] ?? date('Y-m-d'))) ?></small></p></section>
<div class="art-corps page-legale">
  <?php if (count($som) > 3): ?><nav class="sommaire" aria-label="Sommaire"><b>Sommaire</b><ol><?php foreach ($som as $s): ?><li><a href="#<?= h($s['id']) ?>"><?= h($s['t']) ?></a></li><?php endforeach; ?></ol></nav><?php else: ?><div></div><?php endif; ?>
  <div class="texte"><?= $corps ?>
    <p class="art-signature">Autres informations : <?php foreach (pages_site() as $x) if ($x['slug'] !== $pg['slug']): ?><a class="lien" href="<?= url($x['slug']) ?>"><?= h($x['titre']) ?></a> · <?php endif; ?><a class="lien" href="<?= url('plan-du-site') ?>">Plan du site</a></p>
  </div>
</div>
<div style="height:60px"></div>
<?= cta('Une question ?', 'Appelez-nous ou écrivez-nous sur WhatsApp : nous vous répondons rapidement.') ?>
