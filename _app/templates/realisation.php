<?php
$sect = ($p['secteur'] ?? '') !== '' ? trouver(secteurs(true), 'slug', $p['secteur']) : null;
$liste = $sect ? realisations_autres($sect['slug']) : realisations(); $i = 0; foreach ($liste as $k => $x) if ($x['slug'] === $p['slug']) $i = $k;
$prev = $liste[($i - 1 + count($liste)) % count($liste)]; $next = $liste[($i + 1) % count($liste)];
$tem = array_values(array_filter(temoignages(), fn($t) => ($t['realisation'] ?? '') === $p['slug']));
$gal = array_values(array_filter($p['galerie'] ?? [], fn($g) => $g !== ($p['avant'] ?? '')));
?>
<section class="entete"><div class="t"><p class="fil"><a href="<?= url() ?>">Accueil</a> / <?php if ($sect): ?><a href="<?= url('autres-secteurs') ?>">Autres secteurs</a> / <a href="<?= url('autres-secteurs/' . $sect['slug']) ?>"><?= h($sect['nom']) ?></a><?php else: ?><a href="<?= url('realisations') ?>">Réalisations</a><?php endif; ?> / <?= h($p['titre']) ?></p><h1><?= h($p['titre']) ?></h1>
<div class="meta"><?php foreach (['ville', 'annee', 'surface'] as $c) if (trim($p[$c] ?? '') !== '') echo '<span>' . h($p[$c]) . '</span>'; foreach ($p['services'] ?? [] as $c) echo '<span>' . h(nom_service($c)) . '</span>'; ?></div></div>
<div class="intro"><?= paragraphes($p['description'] ?? '') ?></div></section>
<div class="grand-visuel" data-zoom><img src="<?= h(media($p['image'])) ?>" alt="<?= h($p['titre']) ?>" style="object-position:<?= h($p['cadrage'] ?? '50% 50%') ?>"></div>
<?php if (en_edition()): ?><section class="pc-ui pc-aa-slots wrap" id="avant-apres"><h2>Photos avant / après de ce projet<?= in_array('facades', $p['services'] ?? [], true) ? ' <small>(obligatoire pour une façade)</small>' : '' ?></h2><p>Cliquez sur un cadre ou glissez-y une photo, puis « Enregistrer ». Prenez les deux photos du même endroit.</p><div class="pc-slots">
<?php foreach (['avant' => 'Photo avant les travaux', 'apres' => 'Photo après les travaux'] as $k => $l): ?><button type="button" class="pc-slot" data-champ="realisations:<?= h($p['id']) ?>:<?= $k ?>"><?php if (!empty($p[$k])): ?><img src="<?= h(url($p[$k])) ?>" alt=""><?php endif; ?><span><?= $l ?></span><em><?= !empty($p[$k]) ? 'Changer la photo' : 'Ajouter la photo' ?></em></button><?php endforeach; ?>
</div><p class="pc-fiche"><a href="<?= h(url('admin/?p=realisation&id=' . rawurlencode($p['id']))) ?>">Ouvrir la fiche complète du projet dans l'administration</a> (nom, ville, galerie de photos, services…)</p></section><?php endif; ?>
<?php $aaHaut = !empty($p['avant']) && in_array('facades', $p['services'] ?? [], true); if ($aaHaut): ?><section class="wrap aa-haut" style="padding-top:56px"><h2 class="h-moy">Avant, après</h2><?= avap($p['avant'], $p['apres'] ?: $p['image']) ?></section><?php endif; ?>
<section class="wrap def"><p><?= resume_realisation($p) ?></p></section>
<section class="sec deux">
  <?php if (!empty($p['interventions'])): ?><div><h2 class="h-moy">Ce que nous avons réalisé</h2><ul class="liste"><?php foreach ($p['interventions'] as $x): ?><li><?= h($x) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <div><h2 class="h-moy">Services concernés</h2><p style="font-size:18px;line-height:2.1"><?php foreach ($p['services'] ?? [] as $c): ?><a class="lien" href="<?= url('services/' . slug_service($c)) ?>"><?= h(nom_service($c)) ?></a><br><?php endforeach; ?></p>
  <p style="margin-top:20px"><a class="btn plein" href="<?= url('contact?projet=' . rawurlencode($p['services'][0] ?? '')) ?>">Un projet similaire ? Demandez un devis</a></p></div>
</section>
<?php if (!empty($p['avant']) && !$aaHaut): ?><section class="wrap" style="padding-bottom:100px"><h2 class="h-moy">Avant, après</h2><?= avap($p['avant'], $p['apres'] ?: $p['image']) ?></section><?php endif; ?>
<?php if ($gal): ?><section class="wrap" style="padding-bottom:60px"><h2 class="h-moy">Galerie</h2><?= galerie($gal, $p['titre']) ?></section><?php endif; ?>
<?= temoignages_bloc($tem) ?>
<section class="wrap" style="padding-bottom:100px"><nav class="nav-projets" aria-label="Autres réalisations"><a class="lien" href="<?= url(lien_realisation($prev)) ?>">Projet précédent : <?= h($prev['titre']) ?></a><a class="lien" href="<?= url(lien_realisation($next)) ?>">Projet suivant : <?= h($next['titre']) ?></a></nav></section>
<?php $sp = trouver(services(), 'cle', $p['services'][0] ?? ''); if ($sp && !empty($sp['accroche'])): ?><section class="wrap serv-autres"><h2 class="h-moy">À propos de ce type de projet</h2><p><?= h($sp['accroche']) ?> <a class="lien" href="<?= url('services/' . $sp['slug']) ?>">En savoir plus sur « <?= h($sp['nom']) ?> »</a></p></section><?php endif; ?>
<?php if ($sp && !empty($sp['faq'])): ?><section class="wrap faq" style="padding-top:0"><h2 class="h-moy">Questions fréquentes</h2><?php foreach ($sp['faq'] as $f): ?><details><summary><?= h($f['q']) ?></summary><p><?= h($f['r']) ?></p></details><?php endforeach; ?></section><div style="height:100px"></div><?php endif; ?>
<?= cta('Un projet similaire ?') ?>
