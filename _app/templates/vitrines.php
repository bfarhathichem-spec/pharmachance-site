<?php $ts = array_values(array_filter(themes(), fn($t) => !empty($t['images']))); ?>
<section class="entete"><div class="t"><p class="fil"><a href="<?= url() ?>">Accueil</a> / <a href="<?= url('services/' . slug_service('vitrines')) ?>">Vitrines thématiques</a> / Thèmes</p><h1>Les thèmes de vitrines</h1></div><p class="intro">Choisissez un thème sur exemple : nous l'adaptons à votre vitrine, puis nous le fabriquons et le posons.</p></section>
<section class="wrap def"><p><?= h(texte_site('vitrines_intro')) ?></p></section>
<section class="wrap"><div class="themes">
<?php foreach ($ts as $t): $n = count($t['images'] ?? []); ?><a class="theme" href="#<?= h($t['slug']) ?>"><div class="im"><img src="<?= h(media(miniature($t['images'][0]))) ?>" alt="Vitrine de pharmacie : <?= h($t['nom']) ?>" loading="lazy"></div><h3><?= h($t['nom']) ?></h3><p><?= h($t['periode'] ?? '') ?><?= !empty($t['periode']) ? ' · ' : '' ?><?= $n ?> exemple<?= $n === 1 ? '' : 's' ?></p></a><?php endforeach; ?>
</div>
<?php foreach ($ts as $t) if (!empty($t['images'])): ?><section class="theme-bloc" id="<?= h($t['slug']) ?>"><h2><?= h($t['nom']) ?></h2><?php if (!empty($t['description'])): ?><p class="theme-desc"><?= h($t['description']) ?><?= !empty($t['periode']) ? ' <b>Période conseillée : ' . h(mb_strtolower($t['periode'])) . '.</b>' : '' ?></p><?php endif; ?><?= galerie($t['images'], 'Vitrine ' . $t['nom']) ?></section><?php endif; ?>
</section><div style="height:100px"></div>
<?= cta('Votre prochaine vitrine ?', 'Dites-nous le thème et la date : nous préparons la vitrine à temps.') ?>
