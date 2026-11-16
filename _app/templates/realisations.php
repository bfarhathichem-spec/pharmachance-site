<?php $sel = preg_replace('/[^a-z-]/', '', (string)($_GET['service'] ?? 'tous')) ?: 'tous'; ?>
<section class="entete"><div class="t"><p class="fil"><a href="<?= url() ?>">Accueil</a> / Réalisations</p><h1>Nos réalisations</h1></div><p class="intro">Façades, enseignes, vitrines, agencements : une sélection de projets menés partout en Tunisie.</p></section>
<section class="wrap def"><p><?= h(texte_site('realisations_intro')) ?></p></section>
<section class="wrap" style="padding-bottom:120px">
<?php $toutes = realisations(); $compte = []; foreach ($toutes as $x) foreach ($x['services'] ?? [] as $c) $compte[$c] = ($compte[$c] ?? 0) + 1;
$sel = isset($compte[$sel]) ? $sel : 'tous'; ?>
<span class="ancre-filtres" aria-hidden="true"></span><div class="filtres" role="group" aria-label="Filtrer par thématique" data-initial="<?= h($sel) ?>"><button type="button" data-k="tous" aria-pressed="<?= $sel === 'tous' ? 'true' : 'false' ?>">Tous</button><?php foreach (services() as $s) if (!empty($compte[$s['cle']])): ?><button type="button" data-k="<?= h($s['cle']) ?>" aria-pressed="<?= $sel === $s['cle'] ? 'true' : 'false' ?>"><?= h($s['nom']) ?></button><?php endif; ?></div>
<div class="grille"<?= en_edition() ? ' data-liste="realisations"' : '' ?>><?php foreach ($toutes as $x) echo carte($x); ?></div>
<p class="vide">Aucune réalisation publiée pour ce service pour l'instant.</p>
<p style="margin-top:48px;font-size:17px">Vous cherchez une vitrine en particulier ? <a class="lien" href="<?= url('vitrines') ?>">Voir les vitrines par thème</a></p></section>
<section class="wrap rt-themes"><h2 class="xt">Nos réalisations <b>par thématique</b></h2>
<?php foreach (services() as $sv) if (!empty($compte[$sv['cle']])): ?><div class="rt-l"><h3><a href="<?= url('services/' . $sv['slug']) ?>"><?= h($sv['nom']) ?></a></h3><p><?= h($sv['accroche'] ?? $sv['court']) ?></p><a class="lien" href="<?= url('realisations?service=' . rawurlencode($sv['cle'])) ?>">Voir <?= $compte[$sv['cle']] > 1 ? 'les ' . $compte[$sv['cle']] . ' réalisations' : 'la réalisation' ?></a></div><?php endif; ?>
</section>
<?= cta() ?>
