<?php
$r = reglages();
$servs = services();
$actif = $actif ?? '';
$titre = $titre ?? 'PharmaChance';
$desc = $desc ?? '';
$schemeHost = (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'pharmachance.com'));
$canon = $schemeHost . strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$ld = $ld ?? ld_graphe();
$gaId = (function_exists('admin_connecte') && admin_connecte()) ? '' : ga_id();
$cur = fn($k) => $actif === $k ? ' aria-current="page"' : '';
?><!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<script>document.documentElement.classList.add('js')</script>
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= h($titre) ?></title>
<meta name="description" content="<?= h($desc) ?>">
<link rel="canonical" href="<?= h($canon) ?>">
<meta property="og:type" content="<?= h($og_type ?? 'website') ?>"><meta property="og:url" content="<?= h($canon) ?>"><meta property="og:site_name" content="PharmaChance"><meta name="twitter:card" content="summary_large_image"><meta property="og:title" content="<?= h($titre) ?>"><meta property="og:description" content="<?= h($desc) ?>">
<meta property="og:image" content="<?= h($schemeHost . media($og_image ?? 'img/facade-nord-hilton.jpg')) ?>"><meta property="og:locale" content="fr_TN">
<meta name="theme-color" content="#FFFFFF">
<?php if (!empty($r['gsc_verif'])): ?><meta name="google-site-verification" content="<?= h($r['gsc_verif']) ?>"><?php endif; ?>
<link rel="icon" href="<?= url('img/logo-couleur.png') ?>">
<link rel="preload" href="<?= url('assets/fonts/comfortaa-latin-700-normal.woff2') ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= url('assets/style.css') ?>?v=<?= @filemtime(WWW_DIR . '/assets/style.css') ?>">
<script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
</head>
<body>
<a class="evitement" href="#contenu">Aller au contenu</a>
<header class="top">
  <a class="logo" href="<?= url() ?>" aria-label="PharmaChance, accueil"><img src="<?= h(media('img/logo-couleur.png')) ?>" alt="PharmaChance" width="160" height="56"></a>
  <nav class="nav-bureau" aria-label="Navigation principale">
    <a href="<?= url() ?>"<?= $cur('accueil') ?>>Accueil</a>
    <div class="sous"><a href="<?= url('services') ?>"<?= $cur('services') ?>>Services<svg width="10" height="7" viewBox="0 0 10 7" aria-hidden="true"><path d="M1 1l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.8"/></svg></a>
      <div class="sous-menu"><div class="sm-in">
        <?php foreach ($servs as $s): ?><a href="<?= url('services/' . $s['slug']) ?>"><i></i><span><b><?= h($s['nom']) ?></b><small><?= h($s['court']) ?></small></span></a><?php endforeach; ?>
        <div class="sm-pied"><a class="sm-tout" href="<?= url('services') ?>">Tous les services</a><a class="sm-tout" href="<?= url('vitrines') ?>">Thèmes des vitrines</a></div>
      </div></div>
    </div>
    <a href="<?= url('realisations') ?>"<?= $cur('realisations') ?>>Réalisations</a>
    <a href="<?= url('a-propos') ?>"<?= $cur('apropos') ?>>À propos</a>
    <a href="<?= url('articles') ?>"<?= $cur('articles') ?>>Articles</a>
    <a href="<?= url('contact') ?>"<?= $cur('contact') ?>>Contact</a>
    <a class="btn plein" href="<?= url('contact') ?>">Demander un devis</a>
  </nav>
  <button class="menu-btn" type="button" aria-expanded="false" aria-controls="mnav" aria-label="Ouvrir le menu"><span></span><span></span></button>
</header>
<div class="mnav" id="mnav" hidden>
  <div class="mnav-top"><a href="<?= url() ?>" aria-label="PharmaChance, accueil"><img src="<?= h(media('img/logo-couleur.png')) ?>" alt="PharmaChance"></a><button class="mnav-fermer" type="button" aria-label="Fermer le menu"><span></span><span></span></button></div>
  <nav aria-label="Navigation mobile">
    <a href="<?= url() ?>"<?= $cur('accueil') ?>>Accueil</a>
    <details<?= $actif === 'services' ? ' open' : '' ?>><summary>Services</summary><div class="mserv"><?php foreach ($servs as $s): ?><a href="<?= url('services/' . $s['slug']) ?>"><?= h($s['nom']) ?></a><?php endforeach; ?><a href="<?= url('services') ?>">Tous les services</a></div></details>
    <a href="<?= url('realisations') ?>"<?= $cur('realisations') ?>>Réalisations</a>
    <a href="<?= url('vitrines') ?>"<?= $cur('vitrines') ?>>Thèmes des vitrines</a>
    <a href="<?= url('a-propos') ?>"<?= $cur('apropos') ?>>À propos</a>
    <a href="<?= url('articles') ?>"<?= $cur('articles') ?>>Articles</a>
    <a href="<?= url('contact') ?>"<?= $cur('contact') ?>>Contact</a>
  </nav>
  <div class="mnav-bas"><a class="btn plein" href="<?= url('contact') ?>">Demander un devis</a><a class="btn ligne" href="<?= h(lien_wa()) ?>" target="_blank" rel="noopener">WhatsApp</a><a class="btn ligne" href="<?= h(tel_lien()) ?>">Appeler le <?= h($r['telephone'] ?? '') ?></a></div>
</div>
<main id="contenu">
<?= $contenu ?>
</main>
<footer class="xf">
  <div class="xf-haut">
    <div class="xf-acc"><p>Conseils, agencement, merchandising, vitrines.</p><a class="btn plein" href="<?= url('contact') ?>">Demander un devis</a></div>
    <div><strong>Navigation</strong><a href="<?= url() ?>">Accueil</a><a href="<?= url('services') ?>">Services</a><a href="<?= url('realisations') ?>">Réalisations</a><a href="<?= url('vitrines') ?>">Thèmes des vitrines</a><a href="<?= url('a-propos') ?>">À propos</a><a href="<?= url('articles') ?>">Articles</a><a href="<?= url('faq') ?>">Questions fréquentes</a><a href="<?= url('contact') ?>">Contact</a></div>
    <div><strong>Services</strong><?php foreach ($servs as $s): ?><a href="<?= url('services/' . $s['slug']) ?>"><?= h($s['nom']) ?></a><?php endforeach; ?></div>
    <div><strong>Contact</strong><a href="<?= h(tel_lien()) ?>"><?= h($r['telephone'] ?? '') ?></a><a href="<?= h(lien_wa()) ?>" target="_blank" rel="noopener">WhatsApp</a><a href="mailto:<?= h($r['email'] ?? '') ?>"><?= h($r['email'] ?? '') ?></a><span><?= h($r['adresse'] ?? '') ?></span><span>Intervention : <?= h($r['zone'] ?? 'toute la Tunisie') ?></span>
      <?php foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn'] as $k => $n) if (!empty($r[$k])): ?><a href="<?= h($r[$k]) ?>" rel="noopener"><?= $n ?></a><?php endif; ?>
    </div>
  </div>
  <div class="xf-mot" aria-hidden="true">pharmachance</div>
  <div class="bas"><span>© <?= date('Y') ?> <?= h(nom_legal()) ?><?= !empty($r['annees']) ? ' · ' . h($r['annees']) . " ans d'expérience" : '' ?></span><span class="liens-legaux"><?php foreach (pages_site() as $x): ?><a href="<?= url($x['slug']) ?>"><?= h($x['titre']) ?></a><?php endforeach; ?><a href="<?= url('plan-du-site') ?>">Plan du site</a><?php if ($gaId): ?><a href="#cookies" class="gerer-cookies">Gérer les cookies</a><?php endif; ?></span></div>
</footer>
<a class="wa" href="<?= h(lien_wa()) ?>" target="_blank" rel="noopener" aria-label="Nous écrire sur WhatsApp"><?= WA_SVG ?></a>
<nav class="barre-bas" aria-label="Actions rapides">
  <a href="<?= h(tel_lien()) ?>"><svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8a15.1 15.1 0 006.6 6.6l2.2-2.2a1 1 0 011-.25 11.4 11.4 0 003.6.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1z" fill="currentColor"/></svg>Appeler</a>
  <a class="wa-bas" href="<?= h(lien_wa()) ?>" target="_blank" rel="noopener"><?= str_replace('width="30" height="30"', 'width="22" height="22"', WA_SVG) ?>WhatsApp</a>
  <a class="plein" href="<?= url('contact') ?>"><svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v12H7l-3 3z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>Devis</a>
</nav>
<div class="visionneuse" role="dialog" aria-modal="true" aria-label="Photo agrandie"><img alt=""><button class="btn plein" type="button">Fermer</button></div>
<?php if ($gaId): ?><div class="cookies" role="dialog" aria-live="polite" aria-label="Mesure d'audience" hidden data-ga="<?= h($gaId) ?>"><p>Nous aimerions mesurer la fréquentation du site avec Google Analytics, pour savoir quelles pages vous sont utiles. Rien n'est enregistré sans votre accord. <a class="lien" href="<?= url('cookies') ?>">En savoir plus</a></p><div class="btns"><button type="button" class="btn ligne" data-mesure="non">Refuser</button><button type="button" class="btn plein" data-mesure="oui">Accepter</button></div></div><?php endif; ?>
<script src="<?= url('assets/site.js') ?>?v=<?= @filemtime(WWW_DIR . '/assets/site.js') ?>" defer></script>
</body>
</html>
