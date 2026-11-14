<section class="entete"><div class="t"><p class="fil"><a href="<?= url() ?>">Accueil</a> / Services</p><h1>Nos expertises, un seul interlocuteur</h1></div><p class="intro">Vous pouvez nous confier toute la pharmacie, ou seulement la partie qui vous pose problème.</p></section>
<section class="wrap def"><p><?= h(texte_site('services_intro')) ?></p></section>
<section class="wrap" style="padding-bottom:120px"><div class="cartes-serv">
<?php foreach (services() as $s): ?><a class="cs" href="<?= url('services/' . $s['slug']) ?>"><div class="im"><img src="<?= h(media(miniature($s['image']))) ?>" alt="" loading="lazy" style="object-position:<?= h($s['cadrage']) ?>"></div><div class="b"><h2><?= h($s['nom']) ?></h2><p><?= h($s['court']) ?></p><p class="cs-def"><?= h($s['accroche'] ?? '') ?></p><span class="lien">Découvrir</span></div></a><?php endforeach; ?>
</div></section>
<section class="bande"><h2>Comment se passe un projet</h2><p class="lead">Vous validez avant chaque étape.</p><?= chemin() ?></section><div style="height:100px"></div><?= cta() ?>
