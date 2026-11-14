<?php
$r = reglages(); $reals = realisations(); $servs = services(); $themes = array_filter(themes(), fn($t) => !empty($t['images']));
$annees = trim($r['annees'] ?? ''); $ok = ctype_digit($annees);
$ph = array_values(array_filter($reals, fn($x) => !empty($x['image'])));
$valeurs = [
  ['Écouter avant de dessiner', "Chaque projet commence par une visite de votre officine, pour comprendre votre façon de travailler, vos patients et votre quartier, avant de dessiner le plan."],
  ['Montrer avant de fabriquer', "Vous voyez votre future pharmacie en image et vous la corrigez autant que nécessaire. Rien n'est fabriqué sans votre accord."],
  ['Fabriquer avec soin', "Mobilier, enseignes et vitrines sont fabriqués dans notre atelier puis posés par nos équipes : un seul responsable de la qualité."],
  ['Respecter votre activité', "Quand l'officine reste ouverte, le chantier avance par zones et les travaux bruyants se font hors des heures d'ouverture."],
];
?>
<section class="xh ap-h">
  <p class="fil"><a href="<?= url() ?>">Accueil</a> / À propos</p>
  <h1 class="xh-t"><?= $ok ? h($annees) . ' ans à aménager<br><b>les pharmacies de Tunisie.</b>' : 'Nous aménageons<br><b>les pharmacies de Tunisie.</b>' ?></h1>
  <div class="xh-bas"><p>PharmaChance réunit le conseil, l'agencement, le merchandising et les vitrines : les quatre feuilles de notre trèfle. Une seule équipe accompagne le pharmacien, du premier relevé à la dernière vitrine.</p><div class="btns"><a class="btn plein" href="<?= url('contact') ?>">Parler de votre projet</a></div></div>
</section>
<?php if ($ph): ?><div class="xh-visuel" data-zoom><img src="<?= h(media($ph[min(6, count($ph) - 1)]['image'])) ?>" alt="Pharmacie aménagée par PharmaChance" style="object-position:<?= h($ph[min(6, count($ph) - 1)]['cadrage'] ?? '50% 50%') ?>"></div><?php endif; ?>
<section class="xn" aria-label="PharmaChance en chiffres">
  <?php foreach (array_filter([$ok ? [(int)$annees, "ans d'expérience"] : null, [count($reals), 'réalisations en ligne'], [count($servs), 'expertises'], [count($themes), 'thèmes de vitrines']]) as [$v, $l]): ?><div><b data-compte="<?= $v ?>"><?= $v ?></b><span><?= h($l) ?></span></div><?php endforeach; ?>
</section>
<section class="ap-val">
  <h2 class="xt">Notre façon <b>de travailler</b></h2>
  <ol><?php foreach ($valeurs as $k => [$t, $p]): ?><li><span class="n"><?= $k + 1 ?></span><h3><?= h($t) ?></h3><p><?= h($p) ?></p></li><?php endforeach; ?></ol>
</section>
<section class="xp"><div class="xp-tete"><h2>Un projet, <b>cinq étapes</b></h2><p>Vous validez avant chaque étape. Un seul interlocuteur du début à la fin.</p></div><?= chemin() ?></section>
<section class="wrap ap-trefle">
  <div class="head"><h2 class="xt">Les quatre feuilles <b>du trèfle</b></h2><a class="lien" href="<?= url('services') ?>">Tous les services</a></div>
  <div class="ap-grille"><?php foreach ([['etude', 'Conseils'], ['agencement', 'Agencement'], ['merchandising', 'Merchandising'], ['vitrines', 'Vitrines']] as $k => [$cle, $lab]): $s = trouver($servs, 'cle', $cle); if (!$s) continue; ?>
    <a class="ap-f f<?= $k ?>" href="<?= url('services/' . $s['slug']) ?>"><span class="im"><img src="<?= h(media(miniature($s['image']))) ?>" alt="" loading="lazy" style="object-position:<?= h($s['cadrage'] ?? '50% 50%') ?>"></span><b><?= h($lab) ?></b><small><?= h($s['court']) ?></small></a>
  <?php endforeach; ?></div>
</section>
<section class="wrap ap-ou">
  <div><h2 class="xt">Où nous <b>trouver</b></h2><p>Notre bureau est à Ennaser 2, à Tunis. Nous intervenons <?= h(mb_strtolower($r['zone'] ?? 'partout en Tunisie')) ?> : visites, chantiers et poses se font chez vous.</p></div>
  <ul class="coords"><li><b>Adresse</b><span class="txt-coord"><?= h($r['adresse'] ?? '') ?></span></li><li><b>Téléphone</b><a href="<?= h(tel_lien()) ?>"><?= h($r['telephone'] ?? '') ?></a></li><li><b>WhatsApp</b><a href="<?= h(lien_wa()) ?>" target="_blank" rel="noopener">Écrire sur WhatsApp</a></li><li><b>E-mail</b><a href="mailto:<?= h($r['email'] ?? '') ?>"><?= h($r['email'] ?? '') ?></a></li><?php if (!empty($r['horaires'])): ?><li><b>Horaires</b><span class="txt-coord"><?= h($r['horaires']) ?></span></li><?php endif; ?></ul>
</section>
<?= cta('Parlons de votre pharmacie') ?>
