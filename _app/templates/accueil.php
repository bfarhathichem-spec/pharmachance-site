<?php
$r = reglages(); $servs = services(); $reals = realisations();
$vedette = array_values(array_filter($reals, fn($x) => !empty($x['en_avant'])));
if (count($vedette) < 6) $vedette = array_slice($reals, 0, 8);
$aa = trouver($reals, 'slug', $r['aa_accueil'] ?? '');
if (!$aa || empty($aa['avant'])) foreach ($reals as $x) if (!empty($x['avant'])) { $aa = $x; break; }
$themesAvecImages = array_values(array_filter(themes(), fn($t) => !empty($t['images'])));
$titre = (string)($r['hero_titre'] ?? ''); $coupe = mb_strpos($titre, ',');
$t1 = tr($coupe !== false ? mb_substr($titre, 0, $coupe + 1) : $titre); $t2 = tr($coupe !== false ? trim(mb_substr($titre, $coupe + 1)) : '');
$ed = en_edition();
$n = 0; $mots = function (string $s) use (&$n): string { $o = ''; foreach (preg_split('/\s+/u', trim($s)) as $m) if ($m !== '') $o .= '<span class="m"><span style="--i:' . ($n++) . '">' . h($m) . '</span></span> '; return $o; };
$phare = $aa ?: ($vedette[0] ?? null);
$annees = trim((string)($r['annees'] ?? '')); $anneesOk = $annees !== '' && !str_contains($annees, 'XX');
$manifeste = ($anneesOk ? "Depuis $annees ans, nous" : 'Nous') . " dessinons, fabriquons et posons les pharmacies de Tunisie, avec une seule équipe du premier relevé à la dernière vitrine.";
$chiffres = chiffres_auto();
$perso = array_values(array_filter((array)($r['chiffres'] ?? []), fn($c) => trim((string)($c['valeur'] ?? '')) !== '' && trim((string)($c['libelle'] ?? '')) !== ''));
if ($perso) $chiffres = array_map(fn($c) => [trim((string)$c['valeur']), trim((string)$c['libelle'])], $perso);
?>
<section class="xh">
  <?php if ($ed): ?><h1 class="xh-t xh-fixe"><?= h($t1) ?> <?php if ($t2): ?><b><?= h($t2) ?></b><?php endif; ?></h1>
  <?php else: ?><h1 class="xh-t" aria-label="<?= h(trim($t1 . ' ' . $t2)) ?>"><span aria-hidden="true"><?= $mots($t1) ?><?php if ($t2): ?><b><?= $mots($t2) ?></b><?php endif; ?></span></h1><?php endif; ?>
  <div class="xh-bas">
    <p><?= h($r['hero_texte'] ?? '') ?></p>
    <div class="btns"><a class="btn plein" href="<?= url('contact') ?>">Demander un devis</a><a class="btn ligne" href="<?= url('realisations') ?>">Voir les réalisations</a></div>
  </div>
</section>
<?php $heroImg = $r['hero_image'] ?? ''; $lien = trouver($reals, 'slug', $r['hero_lien'] ?? '') ?: ($heroImg ? null : $phare);
if ($heroImg || $phare): ?>
<div class="xh-visuel" data-zoom>
  <img src="<?= h(media($heroImg ?: ($phare['apres'] ?: $phare['image']))) ?>" alt="<?= h($lien ? $lien['titre'] . ' : réalisation PharmaChance' : 'Pharmacie aménagée par PharmaChance') ?>" fetchpriority="high" style="object-position:<?= h($heroImg ? '50% 50%' : ($phare['cadrage'] ?? '50% 50%')) ?>">
  <?php if ($lien): ?><a class="xh-etq" href="<?= url('realisations/' . $lien['slug']) ?>"><i></i><?= h($lien['titre']) ?></a><?php endif; ?>
</div>
<?php endif; ?>
<section class="xn" aria-label="PharmaChance en chiffres" style="--n:<?= max(1, min(4, count($chiffres))) ?>">
  <?php foreach ($chiffres as [$v, $l]): $v = (string)$v; $n = preg_match('/^\d+/', $v, $mm) ? $mm[0] : ''; ?><div><b<?= $n !== '' ? ' data-compte="' . (int)$n . '" data-suite="' . h(substr($v, strlen($n))) . '"' : '' ?>><?= h($v) ?></b><span><?= h($l) ?></span></div><?php endforeach; ?>
</section>
<div class="xm" aria-hidden="true"><span class="defile-mot"><?= str_repeat('Conseils <i></i> Agencement <i></i> Merchandising <i></i> Vitrines <i></i> ', 4) ?></span></div>
<section class="xa">
  <?php $manifeste = tr($manifeste); if ($ed): ?><p class="xa-t"><?= h($manifeste) ?></p><?php else: ?><p class="xa-t allume"><?php foreach (preg_split('/\s+/u', $manifeste) as $m) echo '<i>' . h($m) . '</i> '; ?></p><?php endif; ?>
  <div class="xa-col">
    <?php foreach (array_slice($vedette, 1, 3) as $k => $x): ?><a class="xa-im xa-<?= $k ?>" data-par="<?= [0.10, -0.08, 0.14][$k] ?>" href="<?= url('realisations/' . $x['slug']) ?>"><img src="<?= h(media(miniature($x['image']))) ?>" alt="<?= h($x['titre']) ?>" loading="lazy" style="object-position:<?= h($x['cadrage'] ?? '50% 50%') ?>"></a><?php endforeach; ?>
  </div>
</section>
<section class="wrap xq"><div><h2 class="xt">L'agenceur <b>des pharmacies</b> en Tunisie</h2><p class="xq-def"><?= h(texte_site('accueil_def')) ?></p></div>
  <ul><?php foreach (texte_site('accueil_pourquoi') as [$t, $d]): ?><li><b><?= h($t) ?></b><span><?= h($d) ?></span></li><?php endforeach; ?></ul></section>
<section class="xs">
  <div class="xs-tete"><h2>Nos <b>expertises</b></h2><p>Confiez-nous toute la pharmacie, ou seulement ce qui vous pose problème aujourd'hui.</p></div>
  <div class="xs-corps">
    <ol class="xs-liste">
      <?php foreach ($servs as $k => $s): ?><li><a href="<?= url('services/' . $s['slug']) ?>" data-k="<?= $k ?>"<?= $k === 0 ? ' class="actif"' : '' ?>><img src="<?= h(media(miniature($s['image']))) ?>" alt="" loading="lazy"><span class="nom"><?= h($s['nom']) ?></span><span class="court"><?= h($s['court']) ?></span><svg width="28" height="28" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17L17 7M9 7h8v8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a></li><?php endforeach; ?>
    </ol>
    <div class="xs-apercu" aria-hidden="true"><?php foreach ($servs as $k => $s): ?><img src="<?= h(media($s['image'])) ?>" alt="" loading="lazy" data-k="<?= $k ?>"<?= $k === 0 ? ' class="actif"' : '' ?> style="object-position:<?= h($s['cadrage'] ?? '50% 50%') ?>"><?php endforeach; ?></div>
  </div>
</section>
<section class="xp"><div class="xp-tete"><h2>De A à Z, <b>avec vous</b></h2><p>Une seule équipe suit votre projet. Vous validez avant chaque étape.</p></div><?= chemin() ?></section>
<?php if ($aa): ?>
<section class="wrap xav"><div class="head"><div><h2 class="xt">Le même local, <b>quelques semaines plus tard</b></h2><p><?= h(implode(', ', array_slice($aa['interventions'] ?? [], 0, 4))) ?>.</p></div><a class="lien" href="<?= url('realisations/' . $aa['slug']) ?>">Voir le projet</a></div><?= avap($aa['avant'], $aa['apres'] ?: $aa['image']) ?></section>
<?php endif; ?>
<section class="xr">
  <div class="xr-pin">
    <div class="head wrap"><h2 class="xt">Nos <b>réalisations</b></h2><a class="lien" href="<?= url('realisations') ?>">Voir toutes les réalisations</a></div>
    <div class="xr-piste" tabindex="0" aria-label="Réalisations, faire défiler horizontalement"><?php foreach (array_slice($vedette, 0, 8) as $x) echo carte($x); ?><a class="xr-fin" href="<?= url('realisations') ?>"><span><?= count($reals) ?> projets</span><b>Tout voir</b></a></div>
    <div class="xr-nav wrap"><div class="xr-jauge" aria-hidden="true"><i></i></div><button class="xr-prec" type="button" aria-label="Réalisations précédentes"><svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button><button class="xr-suiv" type="button" aria-label="Réalisations suivantes"><svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button></div>
  </div>
</section>
<?php if ($themesAvecImages): ?>
<section class="bande xv"><div class="head"><div><h2 class="xt">Une vitrine pour <b>chaque saison</b></h2><p>Choisissez un thème, nous concevons et posons la vitrine.</p></div><a class="lien" href="<?= url('vitrines') ?>">Tous les thèmes</a></div>
<div class="themes defile"><?php foreach (array_slice($themesAvecImages, 0, 4) as $t): ?><a class="theme" href="<?= url('vitrines#' . $t['slug']) ?>"><div class="im"><img src="<?= h(media(miniature($t['images'][0]))) ?>" alt="Vitrine de pharmacie : <?= h($t['nom']) ?>" loading="lazy"></div><h3><?= h($t['nom']) ?></h3><p><?= count($t['images']) ?> exemple<?= count($t['images']) > 1 ? 's' : '' ?></p></a><?php endforeach; ?></div></section>
<?php endif; ?>
<?= temoignages_bloc(temoignages()) ?>
<?php $ua = array_slice(array_values(array_filter(articles(), fn($x) => !empty($x['en_avant']))), 0, 3); if ($ua): ?>
<section class="wrap art-accueil"><div class="head"><h2 class="xt">Conseils <b>d'agenceurs</b></h2><a class="lien" href="<?= url('articles') ?>">Tous les articles</a></div><div class="art-grille trois"><?php foreach ($ua as $x) echo carte_article($x); ?></div></section>
<?php endif; ?>
<?= faq_bloc(faq()) ?>
<div style="height:100px"></div>
<?= cta() ?>
