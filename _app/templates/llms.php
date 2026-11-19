<?php $r = reglages(); $h = hote(); ?>
# PharmaChance

> <?= texte_site('accueil_def') ?>

- Site : <?= $h . url() ?>

- Téléphone : <?= $r['telephone'] ?? '' ?>

- E-mail : <?= $r['email'] ?? '' ?>

- Adresse : <?= $r['adresse'] ?? '' ?>


## Services

<?php foreach (services() as $s): ?>
- [<?= $s['nom'] ?>](<?= $h . url('services/' . $s['slug']) ?>) : <?= $s['accroche'] ?? $s['intro'] ?>

<?php endforeach; ?>

## Réalisations

<?php foreach (realisations() as $p): ?>
- [<?= $p['titre'] ?>](<?= $h . url(lien_realisation($p)) ?>) : <?= $p['description'] ?>

<?php endforeach; ?>
<?php if (secteurs_actifs()): ?>
## Autres secteurs (hors pharmacie)

<?php foreach (realisations_autres() as $p): $sx = trouver(secteurs(), 'slug', $p['secteur']); ?>
- [<?= $p['titre'] ?>](<?= $h . url(lien_realisation($p)) ?>) (<?= $sx['nom'] ?? '' ?>) : <?= $p['description'] ?? '' ?>

<?php endforeach; endif; ?>

## Vitrines par thème

<?php foreach (themes() as $t) if (!empty($t['images'])): ?>
- <?= $t['nom'] ?> (<?= $t['periode'] ?? '' ?>) : <?= $t['description'] ?? '' ?> <?= $h . url('vitrines#' . $t['slug']) ?>

<?php endif; ?>

## Articles et conseils

<?php foreach (articles() as $a): ?>
- [<?= $a['titre'] ?>](<?= $h . url('articles/' . $a['slug']) ?>) : <?= $a['resume'] ?>

<?php endforeach; ?>

## Questions fréquentes

<?php foreach (faq() as $f): ?>
### <?= $f['q'] ?>

<?= $f['r'] ?>


<?php endforeach; ?>
## Informations

- [À propos](<?= $h . url('a-propos') ?>)
- [Questions fréquentes](<?= $h . url('faq') ?>)
<?php foreach (pages_site() as $x): ?>
- [<?= $x['titre'] ?>](<?= $h . url($x['slug']) ?>)
<?php endforeach; ?>

## Contact

- [Demander un devis](<?= $h . url('contact') ?>)
