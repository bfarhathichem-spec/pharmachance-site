<?php $L = secteurs_actifs(); $tous = secteurs(); ?>
<section class="entete"><div class="t"><p class="fil"><a href="<?= url() ?>">Accueil</a> / Autres secteurs</p><h1>Nos réalisations dans d'autres secteurs</h1></div><p class="intro">Hôtels, écoles, pâtisseries, boutiques : ce que nous avons appris en aménageant des pharmacies sert aussi à d'autres lieux qui accueillent du public.</p></section>
<section class="wrap def"><p>Une pharmacie demande un accueil clair, des produits faciles à trouver, une façade qu'on voit de la rue et un mobilier qui dure. Ces besoins, on les retrouve dans le hall d'un hôtel, à l'entrée d'une école ou derrière le comptoir d'une pâtisserie. Nous y appliquons la même méthode et la même équipe : visite du lieu, plan, projet en image, puis fabrication et pose.</p></section>
<section class="wrap sx-offre"><h2 class="xt">Ce que nous réalisons <b>hors pharmacie</b></h2>
  <ul class="sx-liste">
    <li><b>Façades et enseignes</b><span>Enseignes lumineuses, habillage de façade et de l'entrée, vitrophanie : le lieu se repère de loin.</span></li>
    <li><b>Agencement sur mesure</b><span>Comptoirs d'accueil et de vente, présentoirs, rangements, fabriqués aux dimensions exactes du local.</span></li>
    <li><b>Vitrines et décors</b><span>Vitrines thématiques, décors de fête ou de saison, mise en scène de l'entrée.</span></li>
    <li><b>Signalétique</b><span>Panneaux extérieurs, orientation dans le bâtiment, repères pour les visiteurs.</span></li>
  </ul>
</section>
<?php if (count($L) > 1): ?><section class="wrap"><nav class="filtres" aria-label="Secteurs"><?php foreach ($L as $s): ?><a class="filtre-lien" href="#<?= h($s['slug']) ?>"><?= h($s['nom']) ?></a><?php endforeach; ?></nav></section><?php endif; ?>
<?php foreach ($L as $s): $P = realisations_autres($s['slug']); ?>
<section class="wrap secteur-bloc" id="<?= h($s['slug']) ?>"><div class="head"><h2 class="xt"><?= h($s['nom']) ?></h2><a class="lien" href="<?= url('autres-secteurs/' . $s['slug']) ?>"><?= count($P) > 1 ? 'Voir les ' . count($P) . ' projets' : 'Voir le secteur' ?></a></div>
<?php if (trim($s['intro'] ?? '') !== ''): ?><p class="secteur-intro"><?= h($s['intro']) ?></p><?php endif; ?>
<div class="grille"><?php foreach (array_slice($P, 0, 6) as $x) echo carte($x); ?></div></section>
<?php endforeach; ?>
<?php $sans = array_values(array_filter($tous, fn($s) => !in_array($s['slug'], array_column($L, 'slug'), true))); if ($sans): ?>
<section class="wrap sx-aussi"><h2 class="h-moy">Nous intervenons aussi pour</h2><div class="sx-cartes"><?php foreach ($sans as $s): ?><div class="sx-carte"><h3><?= h($s['nom']) ?></h3><?php if (trim($s['intro'] ?? '') !== ''): ?><p><?= h($s['intro']) ?></p><?php endif; ?></div><?php endforeach; ?></div></section>
<?php endif; ?>
<?php if (!$L && !$sans): ?><section class="wrap"><p class="vide" style="display:block">Les premiers projets arrivent bientôt.</p></section><?php endif; ?>
<section class="xp"><div class="xp-tete"><h2>Un projet, <b>cinq étapes</b></h2><p>Que ce soit une pharmacie, un hôtel ou une boutique, vous validez avant chaque étape.</p></div><?= chemin() ?></section>
<div style="height:80px"></div>
<?= cta('Un projet hors pharmacie ?', 'Hôtel, école, pâtisserie ou commerce : envoyez-nous une photo du lieu, nous revenons vers vous avec une première idée et un devis.') ?>
