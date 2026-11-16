<?php
/* Vues de l'administration. Variables disponibles : $page, $flash, $erreur, $moi */
$titres = ['tableau' => 'Tableau de bord', 'realisations' => 'Réalisations', 'realisation' => 'Réalisation', 'services' => 'Services', 'service' => 'Service',
    'themes' => 'Vitrines', 'theme' => 'Thème de vitrine', 'temoignages' => 'Témoignages', 'temoignage' => 'Témoignage', 'articles' => 'Articles', 'article' => 'Article', 'pages' => 'Pages légales', 'page' => 'Page légale', 'demandes' => 'Demandes de devis',
    'demande' => 'Demande', 'reglages' => 'Réglages', 'compte' => 'Mon compte', 'aide' => 'Aide', 'connexion' => 'Connexion', 'installation' => 'Créer le compte administrateur'];
$titre = $titres[$page] ?? 'Page introuvable';
$nouv = count(array_filter(charger('demandes'), fn($d) => ($d['statut'] ?? '') !== 'traitee'));
$cadrages = ['50% 50%' => 'Centre', '50% 20%' => 'Haut', '50% 80%' => 'Bas', '30% 50%' => 'Gauche', '70% 50%' => 'Droite'];

function bouton_post(string $action, string $liste, string $id, string $libelle, string $classe = 'btn-l', string $extra = '', string $confirmer = ''): string {
    return '<form method="post" class="inline"' . ($confirmer ? ' data-confirmer="' . h($confirmer) . '"' : '') . '>' . champ_csrf()
        . '<input type="hidden" name="action" value="' . h($action) . '"><input type="hidden" name="liste" value="' . h($liste) . '"><input type="hidden" name="id" value="' . h($id) . '">' . $extra
        . '<button class="' . h($classe) . '" title="' . h($libelle) . '" aria-label="' . h($libelle) . '">' . $libelle . '</button></form>';
}
function interrupteur(string $liste, string $id, string $champ, bool $actif, string $oui, string $non): string {
    return bouton_post('basculer', $liste, $id, $actif ? $oui : $non, 'pastille ' . ($actif ? 'on' : 'off'), '<input type="hidden" name="champ" value="' . h($champ) . '">');
}
function ordre(string $liste, string $id, bool $premier, bool $dernier): string {
    return '<span class="ordre">' . ($premier ? '<span class="btn-l vide"></span>' : bouton_post('monter', $liste, $id, '↑'))
        . ($dernier ? '<span class="btn-l vide"></span>' : bouton_post('descendre', $liste, $id, '↓')) . '</span>';
}
function choix_cadrage(string $actuel, array $cadrages): string {
    $o = '<select name="cadrage" id="cadrage">';
    foreach ($cadrages as $v => $l) $o .= '<option value="' . h($v) . '"' . ($v === $actuel ? ' selected' : '') . '>' . h($l) . '</option>';
    return $o . '</select>';
}
function date_fr(string $iso): string { $t = strtotime($iso); return $t ? date('d/m/Y à H:i', $t) : ''; }
?><!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= h($titre) ?> · Administration PharmaChance</title>
<link rel="icon" href="<?= h(url('img/logo-couleur.png')) ?>">
<link rel="stylesheet" href="<?= h(url('admin/admin.css')) ?>">
</head>
<body class="adm<?= in_array($page, ['connexion', 'installation'], true) ? ' seul' : '' ?>">

<?php if (in_array($page, ['connexion', 'installation'], true)): ?>
<main class="boite">
  <img src="<?= h(url('img/logo-couleur.png')) ?>" alt="PharmaChance" class="logo-c">
  <h1><?= h($titre) ?></h1>
  <?php if ($flash): ?><p class="flash"><?= h($flash) ?></p><?php endif; ?>
  <?php if (!empty($erreur)): ?><p class="err" role="alert"><?= h($erreur) ?></p><?php elseif (isset($_GET['sortie'])): ?><p class="flash" role="status">Vous êtes déconnecté.</p><?php endif; ?>
  <form method="post" class="pile">
    <?= champ_csrf() ?>
    <?php if ($page === 'installation'): ?>
      <p class="aide">Première ouverture de l'administration. Choisissez l'adresse et le mot de passe qui serviront à vous connecter. Personne d'autre ne les connaît.</p>
      <label>Code d'installation <small>fourni avec le site</small><input name="code" required autocomplete="off"></label>
      <label>Votre nom<input name="nom" autocomplete="name"></label>
      <label>Adresse e-mail<input type="email" name="email" required autocomplete="username" value="admin@pharmachance.com"></label>
      <label>Mot de passe (10 caractères minimum)<input type="password" name="mdp" required minlength="10" autocomplete="new-password"></label>
      <label>Mot de passe, une seconde fois<input type="password" name="mdp2" required minlength="10" autocomplete="new-password"></label>
      <button class="btn">Créer le compte</button>
    <?php else: ?>
      <label>Adresse e-mail<input type="email" name="email" required autocomplete="username" autofocus></label>
      <label>Mot de passe<input type="password" name="mdp" required autocomplete="current-password"></label>
      <button class="btn">Se connecter</button>
    <?php endif; ?>
  </form>
  <a class="retour" href="<?= h(url()) ?>">Retour au site</a>
</main>

<?php else: ?>
<header class="barre">
  <button class="burger" aria-label="Menu" aria-expanded="false" aria-controls="nav">☰</button>
  <a class="marque" href="<?= h(url('admin/')) ?>"><img src="<?= h(url('img/logo-couleur.png')) ?>" alt=""> <span>Administration</span></a>
  <a class="voir" href="<?= h(url('?edition=1')) ?>">Modifier le site en direct</a>
  <a class="voir" href="<?= h(url('?edition=0')) ?>" target="_blank" rel="noopener">Voir le site</a>
</header>
<nav id="nav" class="nav">
  <?php foreach (['tableau' => 'Tableau de bord', 'demandes' => 'Demandes de devis', 'realisations' => 'Réalisations', 'services' => 'Services', 'themes' => 'Vitrines', 'articles' => 'Articles', 'temoignages' => 'Témoignages', 'pages' => 'Pages légales', 'reglages' => 'Réglages', 'compte' => 'Mon compte', 'aide' => 'Aide'] as $k => $l):
    $actif = $page === $k || $page === rtrim($k, 's') || ($k === 'themes' && $page === 'theme'); ?>
    <a href="<?= h(url('admin/?p=' . $k)) ?>"<?= $actif ? ' aria-current="page"' : '' ?>><?= h($l) ?><?= $k === 'demandes' && $nouv ? ' <span class="badge">' . $nouv . '</span>' : '' ?></a>
  <?php endforeach; ?>
  <a href="<?= h(url('admin/?p=deconnexion')) ?>" class="sortir">Se déconnecter</a>
</nav>
<main class="contenu">
<?php if ($flash): ?><p class="flash" role="status"><?= h($flash) ?></p><?php endif; ?>

<?php switch ($page):

/* ================= TABLEAU DE BORD ================= */
case 'tableau':
  $R = realisations(true); $D = charger('demandes'); ?>
  <h1>Bonjour<?= !empty($moi['nom']) ? ' ' . h($moi['nom']) : '' ?></h1>
  <div class="tuiles">
    <a class="tuile" href="<?= h(url('admin/?p=demandes')) ?>"><b><?= $nouv ?></b> demande<?= $nouv > 1 ? 's' : '' ?> à traiter</a>
    <a class="tuile" href="<?= h(url('admin/?p=realisations')) ?>"><b><?= count(array_filter($R, fn($r) => !empty($r['publie']))) ?></b> réalisations en ligne sur <?= count($R) ?></a>
    <a class="tuile" href="<?= h(url('admin/?p=temoignages')) ?>"><b><?= count(temoignages()) ?></b> témoignage<?= count(temoignages()) > 1 ? 's' : '' ?> en ligne</a>
  </div>
  <section class="bloc">
    <h2>Modifier le site en direct</h2>
    <p class="aide">Ouvrez n'importe quelle page du site en mode édition : cliquez sur un texte pour le réécrire, glissez une photo sur une image pour la remplacer, faites glisser les sections pour changer leur ordre ou masquez-les. Rien ne change pour les visiteurs tant que vous n'avez pas cliqué sur « Enregistrer ».</p>
    <p class="rangee"><a class="btn" href="<?= h(url('?edition=1')) ?>">Ouvrir le site en mode édition</a></p>
  </section>
  <section class="bloc">
    <h2>Actions rapides</h2>
    <p class="rangee"><a class="btn" href="<?= h(url('admin/?p=realisation')) ?>">Ajouter une réalisation</a>
    <a class="btn-s" href="<?= h(url('admin/?p=temoignage')) ?>">Ajouter un témoignage</a>
    <a class="btn-s" href="<?= h(url('admin/?p=reglages')) ?>">Modifier les coordonnées</a></p>
  </section>
  <?php if ($D): ?>
  <section class="bloc">
    <h2>Dernières demandes</h2>
    <ul class="liste-d">
    <?php foreach (array_slice($D, 0, 5) as $d): ?>
      <li><a href="<?= h(url('admin/?p=demande&id=' . rawurlencode($d['id']))) ?>"><b><?= h($d['nom']) ?></b> · <?= h($d['ville']) ?> <span class="mut"><?= h(date_fr($d['date'])) ?></span></a><?= $d['statut'] !== 'traitee' ? '<span class="pastille on">Nouvelle</span>' : '' ?></li>
    <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>
  <?php $fi = array_filter(realisations(true), 'facade_incomplete'); if ($fi): ?>
    <section class="bloc alerte-bloc"><h2>Façades à compléter <small>photo avant et photo après</small></h2>
      <p class="aide">Cliquez sur « Ajouter les photos » : la page du projet s'ouvre sur le site, déposez la photo avant et la photo après dans les deux cadres, puis cliquez sur « Enregistrer ».</p>
      <ul class="a-faire"><?php foreach ($fi as $x): ?><li><?= vignette($x['image'] ?? '', $x['titre']) ?><b><?= h($x['titre']) ?></b><a class="btn" href="<?= h(url('realisations/' . $x['slug'] . '?edition=1#avant-apres')) ?>">Ajouter les photos</a><a class="btn-s" href="<?= h(url('admin/?p=realisation&id=' . rawurlencode($x['id']))) ?>">Fiche complète</a></li><?php endforeach; ?></ul>
    </section>
  <?php endif; ?>
  <?php $mq = legal_manquant(); if ($mq): ?>
    <p class="alerte">Informations légales à compléter pour les mentions légales et les CGV : <?= h(implode(', ', $mq)) ?>. <a href="<?= h(url('admin/?p=reglages#legal')) ?>">Les renseigner</a></p>
  <?php endif; ?>
  <?php if (str_contains((string)(reglages()['annees'] ?? ''), 'XX')): ?>
    <p class="alerte">Le nombre d'années d'expérience n'est pas encore renseigné. Il s'affiche comme « [XX] » sur le site. <a href="<?= h(url('admin/?p=reglages')) ?>">Le renseigner</a></p>
  <?php endif; ?>
<?php break;

/* ================= RÉALISATIONS ================= */
case 'realisations':
  $L = realisations(true); $n = count($L); ?>
  <div class="tete"><h1>Réalisations</h1><a class="btn" href="<?= h(url('admin/?p=realisation')) ?>">Ajouter une réalisation</a></div>
  <p class="aide">L'ordre ici est celui du site. « Mise en avant » place le projet sur la page d'accueil.</p>
  <?php if (!$L): ?><p class="vide-msg">Aucune réalisation. Ajoutez votre premier projet.</p><?php endif; ?>
  <ul class="lignes">
  <?php foreach ($L as $i => $r): ?>
    <li>
      <?= ordre('realisations', $r['id'], $i === 0, $i === $n - 1) ?>
      <a class="lien-l" href="<?= h(url('admin/?p=realisation&id=' . rawurlencode($r['id']))) ?>"><?= vignette($r['image'] ?? '', $r['titre']) ?>
        <span><b><?= h($r['titre']) ?></b><small><?= h(implode(' · ', array_filter([$r['ville'] ?? '', $r['annee'] ?? '']))) ?><?php if (facade_incomplete($r)): ?> <em class="manque">· Avant / après à ajouter</em><?php endif; ?></small></span></a>
      <span class="actions">
        <?= interrupteur('realisations', $r['id'], 'en_avant', !empty($r['en_avant']), 'Mise en avant', 'Pas en avant') ?>
        <?= interrupteur('realisations', $r['id'], 'publie', !empty($r['publie']), 'En ligne', 'Hors ligne') ?>
      </span>
    </li>
  <?php endforeach; ?>
  </ul>
<?php break;

case 'realisation':
  $r = trouver(charger('realisations'), 'id', (string)($_GET['id'] ?? '')) ?? ['id' => '', 'titre' => '', 'slug' => '', 'ville' => '', 'annee' => date('Y'), 'surface' => '', 'description' => '', 'interventions' => [], 'services' => [], 'galerie' => [], 'image' => '', 'avant' => '', 'apres' => '', 'cadrage' => '50% 50%', 'publie' => true, 'en_avant' => false];
  $neuf = $r['id'] === ''; ?>
  <p class="fil"><a href="<?= h(url('admin/?p=realisations')) ?>">Réalisations</a></p>
  <div class="tete"><h1><?= $neuf ? 'Nouvelle réalisation' : h($r['titre']) ?></h1>
    <?php if (!$neuf && !empty($r['publie'])): ?><a class="btn-s" target="_blank" rel="noopener" href="<?= h(url('realisations/' . $r['slug'])) ?>">Voir sur le site</a><?php endif; ?></div>
  <form method="post" enctype="multipart/form-data" class="fiche">
    <?= champ_csrf() ?><input type="hidden" name="action" value="realisation"><input type="hidden" name="id" value="<?= h($r['id']) ?>">
    <section class="bloc">
      <h2>Le projet</h2>
      <label>Nom du projet<input name="titre" required value="<?= h($r['titre']) ?>" placeholder="Pharmacie du Lac"></label>
      <div class="g3">
        <label>Ville<input name="ville" value="<?= h($r['ville']) ?>" placeholder="Tunis"></label>
        <label>Année<input name="annee" inputmode="numeric" value="<?= h((string)$r['annee']) ?>"></label>
        <label>Surface<input name="surface" value="<?= h((string)$r['surface']) ?>" placeholder="120 m²"></label>
      </div>
      <label>Description<textarea name="description" rows="5"><?= h($r['description']) ?></textarea></label>
      <label>Ce que nous avons fait <small>une ligne par intervention</small><textarea name="interventions" rows="4"><?= h(implode("\n", $r['interventions'] ?? [])) ?></textarea></label>
      <fieldset><legend>Services concernés</legend><div class="cases">
        <?php foreach (services(true) as $s): ?>
          <label class="case"><input type="checkbox" name="services[]" value="<?= h($s['cle']) ?>"<?= in_array($s['cle'], $r['services'] ?? [], true) ? ' checked' : '' ?>> <?= h($s['nom']) ?></label>
        <?php endforeach; ?></div></fieldset>
    </section>

    <section class="bloc">
      <h2>Photos</h2>
      <?php if ($r['galerie']): ?>
        <p class="aide">Choisissez la photo principale (celle des listes et de l'accueil). Cochez « Retirer » pour supprimer une photo.</p>
        <div class="galerie-a">
        <?php foreach ($r['galerie'] as $g): ?>
          <figure><?= vignette($g) ?>
            <label class="case"><input type="radio" name="principale" value="<?= h($g) ?>"<?= $g === $r['image'] ? ' checked' : '' ?>> Principale</label>
            <label class="case danger"><input type="checkbox" name="retirer[]" value="<?= h($g) ?>"> Retirer</label></figure>
        <?php endforeach; ?></div>
      <?php endif; ?>
      <label class="depot">Ajouter des photos <small>JPG, PNG ou WebP, plusieurs à la fois. Elles sont redimensionnées automatiquement.</small>
        <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple></label>
      <label>Cadrage de la photo principale<?= choix_cadrage($r['cadrage'] ?? '50% 50%', $cadrages) ?></label>
    </section>

    <section class="bloc">
      <h2>Avant / après <small class="aa-statut"><?= in_array('facades', $r['services'] ?? [], true) ? 'obligatoire pour une façade' : 'facultatif, obligatoire pour une façade' ?></small></h2>
      <p class="aide">Deux photos prises du même point de vue, par exemple la façade avant les travaux puis la façade terminée. Sur le site, elles s'affichent en haut de la page du projet avec une ligne à faire glisser pour comparer.</p>
      <script>document.addEventListener('change', function (e) { if (e.target.name !== 'services[]' || e.target.value !== 'facades') return; var t = document.querySelector('.aa-statut'); if (t) t.textContent = e.target.checked ? 'obligatoire pour une façade' : 'facultatif, obligatoire pour une façade'; });</script>
      <?php if (facade_incomplete($r) && $r['id'] !== ''): ?><p class="alerte">Ce projet de façade n'a pas encore sa photo avant et sa photo après.</p><?php endif; ?>
      <div class="g2">
        <?php foreach (['avant' => 'Photo avant', 'apres' => 'Photo après'] as $k => $l): ?>
        <div><p class="lib"><?= $l ?></p>
          <?php if ($r[$k]): ?><?= vignette($r[$k]) ?><label class="case danger"><input type="checkbox" name="retirer_<?= $k ?>" value="1"> Retirer</label><?php endif; ?>
          <input type="file" name="<?= $k ?>" accept="image/jpeg,image/png,image/webp" aria-label="<?= $l ?>"></div>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="bloc">
      <h2>Publication</h2>
      <label class="case"><input type="checkbox" name="publie" value="1"<?= !empty($r['publie']) ? ' checked' : '' ?>> En ligne sur le site</label>
      <label class="case"><input type="checkbox" name="en_avant" value="1"<?= !empty($r['en_avant']) ? ' checked' : '' ?>> Mise en avant sur l'accueil</label>
      <details><summary>Adresse de la page</summary>
        <label>pharmachance.com/realisations/<input name="slug" value="<?= h($r['slug']) ?>" placeholder="créée à partir du nom"></label></details>
    </section>
    <div class="pied-f">
      <button class="btn"><?= $neuf ? 'Créer la réalisation' : 'Enregistrer' ?></button>
    </div>
  </form>
  <?php if (!$neuf): ?><div class="zone-danger"><?= bouton_post('supprimer', 'realisations', $r['id'], 'Supprimer cette réalisation', 'btn-danger', '', 'Supprimer définitivement « ' . $r['titre'] . ' » ?') ?></div><?php endif; ?>
<?php break;

/* ================= SERVICES ================= */
case 'services':
  $L = services(true); $n = count($L); ?>
  <div class="tete"><h1>Services</h1><a class="btn-s" href="<?= h(url('admin/?p=service')) ?>">Ajouter un service</a></div>
  <p class="aide">Les services forment le menu « Services » du site, dans cet ordre.</p>
  <ul class="lignes">
  <?php foreach ($L as $i => $s): ?>
    <li><?= ordre('services', $s['slug'], $i === 0, $i === $n - 1) ?>
      <a class="lien-l" href="<?= h(url('admin/?p=service&id=' . rawurlencode($s['slug']))) ?>"><?= vignette($s['image'] ?? '', $s['nom']) ?><span><b><?= h($s['nom']) ?></b><small><?= h($s['court'] ?? '') ?></small></span></a>
      <span class="actions"><?= interrupteur('services', $s['slug'], 'publie', !empty($s['publie']), 'En ligne', 'Hors ligne') ?></span></li>
  <?php endforeach; ?>
  </ul>
<?php break;

case 'service':
  $s = trouver(charger('services'), 'slug', (string)($_GET['id'] ?? '')) ?? ['slug' => '', 'nom' => '', 'court' => '', 'intro' => '', 'besoins' => [], 'comprend' => [], 'image' => '', 'cadrage' => '50% 50%', 'publie' => true];
  $neuf = $s['slug'] === ''; ?>
  <p class="fil"><a href="<?= h(url('admin/?p=services')) ?>">Services</a></p>
  <div class="tete"><h1><?= $neuf ? 'Nouveau service' : h($s['nom']) ?></h1>
    <?php if (!$neuf && !empty($s['publie'])): ?><a class="btn-s" target="_blank" rel="noopener" href="<?= h(url('services/' . $s['slug'])) ?>">Voir sur le site</a><?php endif; ?></div>
  <form method="post" enctype="multipart/form-data" class="fiche">
    <?= champ_csrf() ?><input type="hidden" name="action" value="service"><input type="hidden" name="id" value="<?= h($s['slug']) ?>">
    <section class="bloc">
      <label>Nom<input name="nom" required value="<?= h($s['nom']) ?>"></label>
      <label>Phrase courte <small>affichée dans le menu et les cartes</small><input name="court" maxlength="140" value="<?= h($s['court']) ?>"></label>
      <label>Présentation<textarea name="intro" rows="5"><?= h($s['intro']) ?></textarea></label>
      <label>Pour qui, pour quel besoin <small>une ligne par point</small><textarea name="besoins" rows="4"><?= h(implode("\n", $s['besoins'] ?? [])) ?></textarea></label>
      <label>Ce que comprend le service <small>une ligne par point</small><textarea name="comprend" rows="5"><?= h(implode("\n", $s['comprend'] ?? [])) ?></textarea></label>
    </section>
    <section class="bloc">
      <h2>Contenu pour Google et les IA</h2>
      <label>Définition <small>une phrase qui dit ce qu'est ce service, lisible seule</small><textarea name="accroche" rows="3"><?= h($s['accroche'] ?? '') ?></textarea></label>
      <label>Texte détaillé <small>## Titre, - liste, **gras**, [lien](adresse)</small><textarea name="texte" rows="16" style="font-family:ui-monospace,Consolas,monospace;font-size:14px"><?= h($s['texte'] ?? '') ?></textarea></label>
      <label>Questions fréquentes <small>question sur la 1re ligne, réponse dessous, une ligne vide entre deux</small><textarea name="faq" rows="9"><?= h(faq_vers_texte($s['faq'] ?? [])) ?></textarea></label>
    </section>
    <section class="bloc">
      <h2>Image</h2>
      <?= vignette($s['image'] ?? '') ?>
      <label class="depot">Remplacer l'image<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
      <label>Cadrage<?= choix_cadrage($s['cadrage'] ?? '50% 50%', $cadrages) ?></label>
      <label class="case"><input type="checkbox" name="publie" value="1"<?= !empty($s['publie']) ? ' checked' : '' ?>> En ligne sur le site</label>
    </section>
    <div class="pied-f"><button class="btn"><?= $neuf ? 'Créer le service' : 'Enregistrer' ?></button></div>
  </form>
  <?php if (!$neuf): ?><div class="zone-danger"><?= bouton_post('supprimer', 'services', $s['slug'], 'Supprimer ce service', 'btn-danger', '', 'Supprimer définitivement le service « ' . $s['nom'] . ' » ?') ?></div><?php endif; ?>
<?php break;

/* ================= THÈMES DE VITRINES ================= */
case 'themes':
  $L = themes(true); $n = count($L); ?>
  <div class="tete"><h1>Vitrines</h1><a class="btn-s" href="<?= h(url('admin/?p=theme')) ?>">Ajouter un thème</a></div>
  <p class="aide">Les thèmes saisonniers affichés sur la page Vitrines. Un thème sans photo s'affiche avec la mention « Photos à venir ».</p>
  <ul class="lignes">
  <?php foreach ($L as $i => $t): ?>
    <li><?= ordre('themes', $t['slug'], $i === 0, $i === $n - 1) ?>
      <a class="lien-l" href="<?= h(url('admin/?p=theme&id=' . rawurlencode($t['slug']))) ?>"><?= vignette($t['images'][0] ?? '', $t['nom']) ?><span><b><?= h($t['nom']) ?></b><small><?= count($t['images'] ?? []) ?> photo<?= count($t['images'] ?? []) > 1 ? 's' : '' ?></small></span></a>
      <span class="actions"><?= interrupteur('themes', $t['slug'], 'publie', !empty($t['publie']), 'En ligne', 'Hors ligne') ?></span></li>
  <?php endforeach; ?>
  </ul>
<?php break;

case 'theme':
  $t = trouver(charger('themes'), 'slug', (string)($_GET['id'] ?? '')) ?? ['slug' => '', 'nom' => '', 'images' => [], 'publie' => true];
  $neuf = $t['slug'] === ''; ?>
  <p class="fil"><a href="<?= h(url('admin/?p=themes')) ?>">Vitrines</a></p>
  <h1><?= $neuf ? 'Nouveau thème' : h($t['nom']) ?></h1>
  <form method="post" enctype="multipart/form-data" class="fiche">
    <?= champ_csrf() ?><input type="hidden" name="action" value="theme"><input type="hidden" name="id" value="<?= h($t['slug']) ?>">
    <section class="bloc">
      <label>Nom du thème<input name="nom" required value="<?= h($t['nom']) ?>" placeholder="Solaire"></label>
      <label>Description <small>une phrase sur le message de la vitrine</small><input name="description" value="<?= h($t['description'] ?? '') ?>"></label>
      <label>Période conseillée<input name="periode" value="<?= h($t['periode'] ?? '') ?>" placeholder="Mai à août"></label>
      <?php if ($t['images']): ?><div class="galerie-a">
        <?php foreach ($t['images'] as $g): ?><figure><?= vignette($g) ?><label class="case danger"><input type="checkbox" name="retirer[]" value="<?= h($g) ?>"> Retirer</label></figure><?php endforeach; ?>
      </div><?php endif; ?>
      <label class="depot">Ajouter des photos<input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple></label>
      <label class="case"><input type="checkbox" name="publie" value="1"<?= !empty($t['publie']) ? ' checked' : '' ?>> En ligne sur le site</label>
    </section>
    <div class="pied-f"><button class="btn"><?= $neuf ? 'Créer le thème' : 'Enregistrer' ?></button></div>
  </form>
  <?php if (!$neuf): ?><div class="zone-danger"><?= bouton_post('supprimer', 'themes', $t['slug'], 'Supprimer ce thème', 'btn-danger', '', 'Supprimer le thème « ' . $t['nom'] . ' » ?') ?></div><?php endif; ?>
<?php break;

/* ================= ARTICLES ================= */
case 'articles':
  $L = articles(true); $n = count($L); ?>
  <div class="tete"><h1>Articles</h1><a class="btn" href="<?= h(url('admin/?p=article')) ?>">Écrire un article</a></div>
  <p class="aide">Les articles apparaissent dans le menu « Articles ». « Mise en avant » les affiche aussi sur l'accueil (les 3 premiers). Un nouvel article se place en tête.</p>
  <?php if (!$L): ?><p class="vide-msg">Aucun article pour l'instant.</p><?php endif; ?>
  <ul class="lignes">
  <?php foreach ($L as $i => $a): ?>
    <li><?= ordre('articles', $a['slug'], $i === 0, $i === $n - 1) ?>
      <a class="lien-l" href="<?= h(url('admin/?p=article&id=' . rawurlencode($a['slug']))) ?>"><?= vignette($a['image'] ?? '', $a['titre']) ?><span><b><?= h($a['titre']) ?></b><small><?= h(($a['categorie'] ?? '') . ' · ' . date_longue($a['date'] ?? '') . ' · ' . temps_lecture($a['contenu']) . ' min') ?></small></span></a>
      <span class="actions"><?= interrupteur('articles', $a['slug'], 'en_avant', !empty($a['en_avant']), 'Mise en avant', 'Pas en avant') ?><?= interrupteur('articles', $a['slug'], 'publie', !empty($a['publie']), 'En ligne', 'Brouillon') ?></span></li>
  <?php endforeach; ?>
  </ul>
<?php break;

case 'article':
  $a = trouver(charger('articles', []), 'slug', (string)($_GET['id'] ?? '')) ?? ['slug' => '', 'titre' => '', 'categorie' => 'Conseils', 'resume' => '', 'contenu' => '', 'faq' => [], 'image' => '', 'image_alt' => '', 'date' => date('Y-m-d'), 'publie' => false, 'en_avant' => false];
  $neuf = $a['slug'] === ''; $cats = array_values(array_unique(array_filter(array_map(fn($x) => $x['categorie'] ?? '', charger('articles', []))))); ?>
  <p class="fil"><a href="<?= h(url('admin/?p=articles')) ?>">Articles</a></p>
  <div class="tete"><h1><?= $neuf ? 'Nouvel article' : h($a['titre']) ?></h1>
    <?php if (!$neuf && !empty($a['publie'])): ?><a class="btn-s" target="_blank" rel="noopener" href="<?= h(url('articles/' . $a['slug'])) ?>">Voir sur le site</a><?php endif; ?></div>
  <form method="post" enctype="multipart/form-data" class="fiche">
    <?= champ_csrf() ?><input type="hidden" name="action" value="article"><input type="hidden" name="id" value="<?= h($a['slug']) ?>">
    <section class="bloc">
      <label>Titre <small>la question que se pose le pharmacien marche bien</small><input name="titre" required maxlength="140" value="<?= h($a['titre']) ?>"></label>
      <div class="g3">
        <label>Rubrique<input name="categorie" list="cats" value="<?= h($a['categorie']) ?>"><datalist id="cats"><?php foreach ($cats as $c): ?><option value="<?= h($c) ?>"><?php endforeach; ?></datalist></label>
        <label>Date<input type="date" name="date" value="<?= h($a['date']) ?>"></label>
        <label>Adresse <small>facultatif</small><input name="slug" value="<?= h($a['slug']) ?>" <?= $neuf ? '' : 'readonly' ?> placeholder="créée à partir du titre"></label>
      </div>
      <label>Résumé <small>2 phrases, affichées sous le titre et dans Google</small><textarea name="resume" rows="3" maxlength="320"><?= h($a['resume']) ?></textarea></label>
    </section>
    <section class="bloc">
      <h2>Image de couverture</h2>
      <?= vignette($a['image'] ?? '') ?>
      <label class="depot">Choisir une image <small>format paysage, 1600 px de large idéalement</small><input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
      <label>Description de l'image <small>pour Google et les personnes malvoyantes</small><input name="image_alt" value="<?= h($a['image_alt'] ?? '') ?>"></label>
    </section>
    <section class="bloc">
      <h2>Texte</h2>
      <details class="aide-format"><summary>Comment mettre en forme</summary>
        <p class="aide">Laissez une ligne vide entre deux paragraphes.<br>
        <code>## Titre de partie</code> crée un titre (il apparaît dans le sommaire).<br>
        <code>### Sous-titre</code> crée un sous-titre.<br>
        <code>- élément</code> crée une liste à puces, <code>1. élément</code> une liste numérotée.<br>
        <code>**mots**</code> met en gras. <code>[texte](https://adresse)</code> crée un lien.<br>
        <code>&gt; phrase</code> crée un encadré vert.<br>
        Tableau : <code>| Colonne | Colonne |</code>, puis <code>| --- | --- |</code>, puis une ligne par rangée.</p></details>
      <label>Contenu<textarea name="contenu" rows="24" required style="font-family:ui-monospace,Consolas,monospace;font-size:14px;line-height:1.6"><?= h($a['contenu']) ?></textarea></label>
      <div class="g2">
        <label class="depot">Ajouter une image dans le texte <small>elle s'ajoute à la fin, déplacez la ligne si besoin</small><input type="file" name="illustration" accept="image/jpeg,image/png,image/webp"></label>
        <label>Légende de cette image<input name="illustration_alt"></label>
      </div>
    </section>
    <section class="bloc">
      <h2>Questions fréquentes <small>facultatif, très utile pour Google et les IA</small></h2>
      <label>Une question par bloc : la question sur la première ligne, la réponse dessous, une ligne vide entre deux blocs.<textarea name="faq" rows="9"><?= h(faq_vers_texte($a['faq'] ?? [])) ?></textarea></label>
    </section>
    <section class="bloc">
      <label class="case"><input type="checkbox" name="publie" value="1"<?= !empty($a['publie']) ? ' checked' : '' ?>> En ligne sur le site</label>
      <label class="case"><input type="checkbox" name="en_avant" value="1"<?= !empty($a['en_avant']) ? ' checked' : '' ?>> Mis en avant sur l'accueil</label>
    </section>
    <div class="pied-f"><button class="btn"><?= $neuf ? "Créer l'article" : 'Enregistrer' ?></button></div>
  </form>
  <?php if (!$neuf): ?><div class="zone-danger"><?= bouton_post('supprimer', 'articles', $a['slug'], 'Supprimer cet article', 'btn-danger', '', 'Supprimer définitivement « ' . $a['titre'] . ' » ?') ?></div><?php endif; ?>
<?php break;

/* ================= PAGES LÉGALES ================= */
case 'pages': ?>
  <h1>Pages légales</h1>
  <p class="aide">Textes rédigés comme base de travail : faites-les relire par votre conseil juridique avant publication définitive. Les mots entre doubles accolades, comme {{telephone}}, sont remplacés par vos réglages.</p>
  <?php if ($mq = legal_manquant()): ?><p class="alerte">À compléter dans les réglages : <?= h(implode(', ', $mq)) ?>. <a href="<?= h(url('admin/?p=reglages#legal')) ?>">Les renseigner</a></p><?php endif; ?>
  <ul class="lignes"><?php foreach (pages_site() as $x): ?><li><a class="lien-l" href="<?= h(url('admin/?p=page&id=' . rawurlencode($x['slug']))) ?>"><span><b><?= h($x['titre']) ?></b><small>pharmachance.com/<?= h($x['slug']) ?> · mise à jour le <?= h(date_longue($x['maj'] ?? '')) ?></small></span></a><span class="actions"><a class="btn-s" href="<?= h(url($x['slug'])) ?>" target="_blank" rel="noopener">Voir</a></span></li><?php endforeach; ?></ul>
<?php break;

case 'page':
  $x = trouver(charger('pages', []), 'slug', (string)($_GET['id'] ?? ''));
  if (!$x) { echo '<h1>Page introuvable</h1>'; break; } ?>
  <p class="fil"><a href="<?= h(url('admin/?p=pages')) ?>">Pages légales</a></p>
  <div class="tete"><h1><?= h($x['titre']) ?></h1><a class="btn-s" target="_blank" rel="noopener" href="<?= h(url($x['slug'])) ?>">Voir sur le site</a></div>
  <form method="post" class="fiche"><?= champ_csrf() ?><input type="hidden" name="action" value="page"><input type="hidden" name="id" value="<?= h($x['slug']) ?>">
    <section class="bloc">
      <label>Titre<input name="titre" value="<?= h($x['titre']) ?>"></label>
      <label>Phrase d'introduction<input name="resume" value="<?= h($x['resume'] ?? '') ?>"></label>
      <p class="aide">Mise en forme : <code>## Titre</code>, <code>- liste</code>, <code>**gras**</code>, <code>[texte](adresse)</code>. Valeurs automatiques : <code>{{nom_legal}}</code> <code>{{adresse}}</code> <code>{{telephone}}</code> <code>{{email}}</code> <code>{{identifiant_unique}}</code> <code>{{matricule_fiscal}}</code>.</p>
      <label>Texte<textarea name="contenu" rows="30" style="font-family:ui-monospace,Consolas,monospace;font-size:14px;line-height:1.6"><?= h($x['contenu']) ?></textarea></label>
    </section>
    <div class="pied-f"><button class="btn">Enregistrer</button></div>
  </form>
<?php break;

/* ================= AIDE ================= */
case 'aide': ?>
  <h1>Aide : gérer le site</h1>
  <section class="bloc"><h2>Page d'accueil</h2>
    <p>Ce que vous modifiez ici apparaît directement sur l'accueil.</p>
    <ul class="aide-l">
      <li>Titre, texte et grande photo du haut : <a href="<?= h(url('admin/?p=reglages')) ?>">Réglages</a>, rubrique « Page d'accueil ».</li>
      <li>Défilement « Nos réalisations » : ce sont les projets cochés « Mise en avant » dans <a href="<?= h(url('admin/?p=realisations')) ?>">Réalisations</a>, dans l'ordre de la liste (flèches ↑ ↓). Huit projets au maximum.</li>
      <li>Bloc avant / après : choisissez le projet dans Réglages. Le projet doit avoir une photo avant et une photo après.</li>
      <li>Vitrines : les quatre premiers thèmes de <a href="<?= h(url('admin/?p=themes')) ?>">Vitrines</a> qui ont des photos.</li>
      <li>Conseils d'agenceurs : les trois premiers articles cochés « Mise en avant » dans <a href="<?= h(url('admin/?p=articles')) ?>">Articles</a>.</li>
      <li>Témoignages : la section apparaît dès qu'un témoignage est en ligne avec l'accord du pharmacien.</li>
    </ul></section>
  <section class="bloc"><h2>Ajouter un projet</h2>
    <ol class="aide-l"><li>Réalisations, puis « Ajouter une réalisation ».</li><li>Nom, ville, année, description, et une ligne par intervention.</li><li>Cochez les services concernés : le projet apparaîtra dans le bon filtre et sur la page du service.</li><li>Ajoutez les photos (plusieurs à la fois), choisissez la principale et son cadrage.</li><li>Facultatif : une photo avant et une photo après, prises du même point de vue.</li><li>Cochez « En ligne », puis « Créer la réalisation ».</li></ol>
    <p class="aide">Les photos sont réduites et allégées automatiquement. Demandez l'accord du pharmacien avant de publier une photo où le nom de sa pharmacie est lisible.</p></section>
  <section class="bloc"><h2>Demandes de devis</h2><p>Chaque demande envoyée depuis la page Contact arrive dans <a href="<?= h(url('admin/?p=demandes')) ?>">Demandes de devis</a> et par e-mail à l'adresse choisie dans Réglages. Marquez-la « traitée » une fois le client rappelé.</p></section>
  <section class="bloc"><h2>Accès</h2><p>Chaque personne a son propre compte. Pour donner ou retirer un accès : <a href="<?= h(url('admin/?p=compte')) ?>">Mon compte</a>. Ne partagez jamais votre mot de passe.</p></section>
<?php break;

/* ================= TÉMOIGNAGES ================= */
case 'temoignages':
  $L = temoignages(true); $n = count($L); ?>
  <div class="tete"><h1>Témoignages</h1><a class="btn" href="<?= h(url('admin/?p=temoignage')) ?>">Ajouter un témoignage</a></div>
  <p class="aide">La section « Ils nous font confiance » n'apparaît sur le site que lorsqu'au moins un témoignage est en ligne. Un témoignage ne peut être publié qu'avec l'accord écrit du pharmacien.</p>
  <?php if (!$L): ?><p class="vide-msg">Aucun témoignage pour l'instant.</p><?php endif; ?>
  <ul class="lignes">
  <?php foreach ($L as $i => $t): ?>
    <li><?= ordre('temoignages', $t['id'], $i === 0, $i === $n - 1) ?>
      <a class="lien-l" href="<?= h(url('admin/?p=temoignage&id=' . rawurlencode($t['id']))) ?>"><?= vignette($t['image'] ?? '', $t['nom']) ?><span><b><?= h($t['nom']) ?></b><small><?= h(mb_strimwidth($t['texte'], 0, 90, '…')) ?></small></span></a>
      <span class="actions"><?php if (empty($t['accord'])): ?><span class="pastille off">Accord manquant</span><?php else: ?><?= interrupteur('temoignages', $t['id'], 'publie', !empty($t['publie']), 'En ligne', 'Hors ligne') ?><?php endif; ?></span></li>
  <?php endforeach; ?>
  </ul>
<?php break;

case 'temoignage':
  $t = trouver(charger('temoignages'), 'id', (string)($_GET['id'] ?? '')) ?? ['id' => '', 'texte' => '', 'nom' => '', 'etablissement' => '', 'realisation' => '', 'image' => '', 'accord' => false, 'publie' => false];
  $neuf = $t['id'] === ''; ?>
  <p class="fil"><a href="<?= h(url('admin/?p=temoignages')) ?>">Témoignages</a></p>
  <h1><?= $neuf ? 'Nouveau témoignage' : h($t['nom']) ?></h1>
  <form method="post" enctype="multipart/form-data" class="fiche">
    <?= champ_csrf() ?><input type="hidden" name="action" value="temoignage"><input type="hidden" name="id" value="<?= h($t['id']) ?>">
    <section class="bloc">
      <label>Témoignage<textarea name="texte" rows="5" required><?= h($t['texte']) ?></textarea></label>
      <div class="g2">
        <label>Nom du pharmacien<input name="nom" required value="<?= h($t['nom']) ?>" placeholder="Dr Amel B."></label>
        <label>Pharmacie et ville<input name="etablissement" value="<?= h($t['etablissement']) ?>" placeholder="Pharmacie du Centre, Sousse"></label>
      </div>
      <label>Projet lié <small>facultatif, ajoute un lien vers la réalisation</small>
        <select name="realisation"><option value="">Aucun</option>
          <?php foreach (realisations(true) as $r): ?><option value="<?= h($r['id']) ?>"<?= $r['id'] === $t['realisation'] ? ' selected' : '' ?>><?= h($r['titre']) ?></option><?php endforeach; ?>
        </select></label>
      <p class="lib">Photo <small>facultatif</small></p>
      <?php if ($t['image']): ?><?= vignette($t['image']) ?><label class="case danger"><input type="checkbox" name="retirer_image" value="1"> Retirer</label><?php endif; ?>
      <input type="file" name="image" accept="image/jpeg,image/png,image/webp" aria-label="Photo">
    </section>
    <section class="bloc">
      <label class="case"><input type="checkbox" name="accord" value="1"<?= !empty($t['accord']) ? ' checked' : '' ?>> J'ai l'accord écrit de ce pharmacien pour publier son nom et son témoignage</label>
      <label class="case"><input type="checkbox" name="publie" value="1"<?= !empty($t['publie']) ? ' checked' : '' ?>> En ligne sur le site</label>
    </section>
    <div class="pied-f"><button class="btn"><?= $neuf ? 'Créer le témoignage' : 'Enregistrer' ?></button></div>
  </form>
  <?php if (!$neuf): ?><div class="zone-danger"><?= bouton_post('supprimer', 'temoignages', $t['id'], 'Supprimer ce témoignage', 'btn-danger', '', 'Supprimer ce témoignage ?') ?></div><?php endif; ?>
<?php break;

/* ================= DEMANDES ================= */
case 'demandes':
  $D = charger('demandes'); $filtre = ($_GET['f'] ?? '') === 'toutes' ? 'toutes' : 'nouvelles';
  $V = $filtre === 'toutes' ? $D : array_values(array_filter($D, fn($d) => $d['statut'] !== 'traitee')); ?>
  <div class="tete"><h1>Demandes de devis</h1><?php if ($D): ?><a class="btn-s" href="<?= h(url('admin/?p=export')) ?>">Exporter (Excel)</a><?php endif; ?></div>
  <p class="onglets"><a href="<?= h(url('admin/?p=demandes')) ?>"<?= $filtre === 'nouvelles' ? ' aria-current="page"' : '' ?>>À traiter (<?= $nouv ?>)</a><a href="<?= h(url('admin/?p=demandes&f=toutes')) ?>"<?= $filtre === 'toutes' ? ' aria-current="page"' : '' ?>>Toutes (<?= count($D) ?>)</a></p>
  <?php if (!$V): ?><p class="vide-msg"><?= $filtre === 'nouvelles' ? 'Aucune demande en attente.' : 'Aucune demande reçue pour l\'instant. Elles arrivent ici depuis la page Contact.' ?></p><?php endif; ?>
  <ul class="lignes">
  <?php foreach ($V as $d): ?>
    <li><a class="lien-l" href="<?= h(url('admin/?p=demande&id=' . rawurlencode($d['id']))) ?>"><span><b><?= h($d['nom']) ?></b> <span class="mut">· <?= h($d['ville']) ?><?= $d['projet'] ? ' · ' . h(nom_service($d['projet'])) : '' ?></span><small><?= h(date_fr($d['date'])) ?><?= !empty($d['fichiers']) ? ' · ' . count($d['fichiers']) . ' fichier(s)' : '' ?></small></span></a>
      <span class="actions"><span class="pastille <?= $d['statut'] === 'traitee' ? 'off' : 'on' ?>"><?= $d['statut'] === 'traitee' ? 'Traitée' : 'Nouvelle' ?></span></span></li>
  <?php endforeach; ?>
  </ul>
<?php break;

case 'demande':
  $d = trouver(charger('demandes'), 'id', (string)($_GET['id'] ?? ''));
  if (!$d) { echo '<h1>Demande introuvable</h1><p><a href="' . h(url('admin/?p=demandes')) . '">Retour aux demandes</a></p>'; break; }
  $tel = preg_replace('/[^\d+]/', '', $d['tel']); $wa = preg_replace('/\D/', '', $d['tel']); if (strlen($wa) === 8) $wa = '216' . $wa; ?>
  <p class="fil"><a href="<?= h(url('admin/?p=demandes')) ?>">Demandes de devis</a></p>
  <div class="tete"><h1><?= h($d['nom']) ?></h1><span class="pastille <?= $d['statut'] === 'traitee' ? 'off' : 'on' ?>"><?= $d['statut'] === 'traitee' ? 'Traitée' : 'Nouvelle' ?></span></div>
  <section class="bloc">
    <dl class="infos">
      <dt>Reçue le</dt><dd><?= h(date_fr($d['date'])) ?></dd>
      <dt>Téléphone</dt><dd><a href="tel:<?= h($tel) ?>"><?= h($d['tel']) ?></a> · <a href="https://wa.me/<?= h($wa) ?>" target="_blank" rel="noopener">WhatsApp</a></dd>
      <?php if ($d['email']): ?><dt>E-mail</dt><dd><a href="mailto:<?= h($d['email']) ?>"><?= h($d['email']) ?></a></dd><?php endif; ?>
      <dt>Ville</dt><dd><?= h($d['ville']) ?></dd>
      <?php if ($d['projet']): ?><dt>Projet</dt><dd><?= h(nom_service($d['projet'])) ?></dd><?php endif; ?>
      <?php if ($d['budget']): ?><dt>Budget</dt><dd><?= h($d['budget']) ?></dd><?php endif; ?>
    </dl>
    <h2>Message</h2>
    <div class="message"><?= nl2br(h($d['message'])) ?></div>
    <?php if (!empty($d['fichiers'])): ?>
      <h2>Fichiers joints</h2>
      <ul class="pj"><?php foreach ($d['fichiers'] as $f): ?><li><a href="<?= h(url('admin/?p=fichier&id=' . rawurlencode($d['id']) . '&f=' . rawurlencode($f))) ?>" target="_blank" rel="noopener">Ouvrir le fichier <?= h($f) ?></a></li><?php endforeach; ?></ul>
    <?php endif; ?>
  </section>
  <div class="rangee">
    <?= bouton_post('statut', 'demandes', $d['id'], $d['statut'] === 'traitee' ? 'Remettre en « à traiter »' : 'Marquer comme traitée', $d['statut'] === 'traitee' ? 'btn-s' : 'btn', '<input type="hidden" name="statut" value="' . ($d['statut'] === 'traitee' ? 'nouvelle' : 'traitee') . '">') ?>
    <?= bouton_post('supprimer', 'demandes', $d['id'], 'Supprimer', 'btn-danger', '', 'Supprimer cette demande ? Les fichiers joints resteront archivés sur le serveur.') ?>
  </div>
<?php break;

/* ================= RÉGLAGES ================= */
case 'reglages':
  $g = reglages(); ?>
  <h1>Réglages</h1>
  <form method="post" class="fiche" enctype="multipart/form-data">
    <?= champ_csrf() ?><input type="hidden" name="action" value="reglages">
    <section class="bloc">
      <h2>Coordonnées</h2>
      <div class="g2">
        <label>Téléphone affiché<input name="telephone" value="<?= h($g['telephone'] ?? '') ?>"></label>
        <label>Numéro WhatsApp <small>avec l'indicatif, ex. 21623067271</small><input name="whatsapp" inputmode="numeric" value="<?= h($g['whatsapp'] ?? '') ?>"></label>
        <label>E-mail affiché<input type="email" name="email" value="<?= h($g['email'] ?? '') ?>"></label>
        <label>E-mail qui reçoit les demandes<input type="email" name="email_notifications" value="<?= h($g['email_notifications'] ?? '') ?>"></label>
      </div>
      <label>Adresse<input name="adresse" value="<?= h($g['adresse'] ?? '') ?>"></label>
      <label>Horaires <small>facultatif, affichés sur la page À propos</small><input name="horaires" value="<?= h($g['horaires'] ?? '') ?>" placeholder="Du lundi au samedi, 8 h 30 – 18 h"></label>
      <label>Zone d'intervention<input name="zone" value="<?= h($g['zone'] ?? '') ?>" placeholder="Toute la Tunisie"></label>
    </section>
    <section class="bloc">
      <h2>Page d'accueil</h2>
      <label>Années d'expérience<input name="annees" value="<?= h($g['annees'] ?? '') ?>" placeholder="26"></label>
      <label>Titre principal<input name="hero_titre" value="<?= h($g['hero_titre'] ?? '') ?>"></label>
      <label>Texte sous le titre<textarea name="hero_texte" rows="3"><?= h($g['hero_texte'] ?? '') ?></textarea></label>
      <p class="lib">Grande photo de l'accueil <small>si vide, la photo « après » du projet avant / après ci-dessous est utilisée</small></p>
      <?php if (!empty($g['hero_image'])): ?><?= vignette($g['hero_image']) ?><label class="case danger"><input type="checkbox" name="retirer_hero_image" value="1"> Retirer cette photo</label><?php endif; ?>
      <label class="depot">Choisir une photo <small>format paysage, 2000 px de large idéalement</small><input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp"></label>
      <label>Projet lié à la grande photo <small>l'étiquette sur la photo mène à ce projet</small>
        <select name="hero_lien"><option value="">Aucun</option><?php foreach (realisations(true) as $r): ?><option value="<?= h($r['slug']) ?>"<?= $r['slug'] === ($g['hero_lien'] ?? '') ? ' selected' : '' ?>><?= h($r['titre']) ?></option><?php endforeach; ?></select></label>
      <label>Avant / après affiché sur l'accueil
        <select name="aa_accueil"><option value="">Aucun</option>
        <?php foreach (realisations(true) as $r) if (!empty($r['avant']) && !empty($r['apres'])): ?>
          <option value="<?= h($r['slug']) ?>"<?= $r['slug'] === ($g['aa_accueil'] ?? '') ? ' selected' : '' ?>><?= h($r['titre']) ?></option>
        <?php endif; ?></select></label>
    </section>
    <section class="bloc">
      <h2>Bandeau des chiffres <small>sous la grande photo de l'accueil, jusqu'à 4 chiffres</small></h2>
      <p class="aide">Écrivez un nombre (vous pouvez ajouter un signe après : 500+, 98 %) et le texte affiché en dessous. Une ligne vide est masquée. Si toutes les lignes sont vides, le site revient aux chiffres calculés automatiquement.</p>
      <?php $ch = array_values((array)($g['chiffres'] ?? [])); if (!$ch) $ch = array_map(fn($x) => ['valeur' => (string)$x[0], 'libelle' => $x[1]], chiffres_auto()); for ($i = 0; $i < 4; $i++): $c = $ch[$i] ?? []; ?>
      <div class="ligne-chiffre g2" style="grid-template-columns:1fr 2fr">
        <label>Chiffre <?= $i + 1 ?><input name="chiffre_valeur[]" value="<?= h($c['valeur'] ?? '') ?>" placeholder="<?= ['26', '18', '7', '10'][$i] ?>" maxlength="12"></label>
        <label>Texte<input name="chiffre_libelle[]" value="<?= h($c['libelle'] ?? '') ?>" placeholder="<?= ["ans d'expérience", 'réalisations à découvrir', 'expertises réunies', 'thèmes de vitrines'][$i] ?>" maxlength="60"></label>
      </div>
      <?php endfor; ?>
    </section>
    <section class="bloc">
      <h2>Google : statistiques et référencement</h2>
      <label>Identifiant Google Analytics <small>commence par G-, laisser vide pour désactiver la mesure d'audience</small><input name="ga_id" value="<?= h(ga_id()) ?>" placeholder="G-XXXXXXXXXX" pattern="[Gg]-[A-Za-z0-9]{4,16}"></label>
      <label>Code de validation Google Search Console <small>facultatif, la balise meta fournie par Google</small><input name="gsc_verif" value="<?= h($g['gsc_verif'] ?? '') ?>" placeholder="&lt;meta name=&quot;google-site-verification&quot; content=&quot;…&quot;&gt;"></label>
      <p class="aide">La mesure d'audience ne démarre que si le visiteur l'accepte dans le bandeau des cookies. Vos propres visites, quand vous êtes connecté, ne sont pas comptées.</p>
    </section>
    <section class="bloc">
      <h2>Réseaux sociaux <small>laisser vide pour masquer</small></h2>
      <label>Facebook<input type="url" name="facebook" value="<?= h($g['facebook'] ?? '') ?>" placeholder="https://facebook.com/…"></label>
      <label>Instagram<input type="url" name="instagram" value="<?= h($g['instagram'] ?? '') ?>" placeholder="https://instagram.com/…"></label>
      <label>LinkedIn<input type="url" name="linkedin" value="<?= h($g['linkedin'] ?? '') ?>" placeholder="https://linkedin.com/company/…"></label>
    </section>
    <section class="bloc" id="legal">
      <h2>Informations légales <small>affichées dans les mentions légales, les CGV et le pied de page</small></h2>
      <div class="g2">
        <label>Raison sociale<input name="raison_sociale" value="<?= h($g['raison_sociale'] ?? '') ?>" placeholder="PharmaChance SARL"></label>
        <label>Forme juridique<input name="forme_juridique" value="<?= h($g['forme_juridique'] ?? '') ?>" placeholder="SARL, SUARL, SA…"></label>
        <label>Capital social<input name="capital" value="<?= h($g['capital'] ?? '') ?>" placeholder="10 000 DT"></label>
        <label>Identifiant unique (RNE)<input name="identifiant_unique" value="<?= h($g['identifiant_unique'] ?? '') ?>"></label>
        <label>Matricule fiscal<input name="matricule_fiscal" value="<?= h($g['matricule_fiscal'] ?? '') ?>"></label>
        <label>Directeur de la publication<input name="directeur_publication" value="<?= h($g['directeur_publication'] ?? '') ?>" placeholder="Nom et prénom"></label>
      </div>
      <p class="aide">Une ligne laissée vide n'apparaît pas sur le site. Le texte des pages se modifie dans <a href="<?= h(url('admin/?p=pages')) ?>">Pages légales</a>.</p>
    </section>
    <div class="pied-f"><button class="btn">Enregistrer les réglages</button></div>
  </form>
<?php break;

/* ================= COMPTE ================= */
case 'compte': ?>
  <h1>Mon compte</h1>
  <section class="bloc">
    <h2>Changer mon mot de passe</h2>
    <form method="post" class="pile court"><?= champ_csrf() ?><input type="hidden" name="action" value="mdp">
      <label>Mot de passe actuel<input type="password" name="actuel" required autocomplete="current-password"></label>
      <label>Nouveau mot de passe (10 caractères minimum)<input type="password" name="nouveau" required minlength="10" autocomplete="new-password"></label>
      <label>Nouveau mot de passe, une seconde fois<input type="password" name="nouveau2" required minlength="10" autocomplete="new-password"></label>
      <button class="btn">Changer le mot de passe</button></form>
  </section>
  <section class="bloc">
    <h2>Accès à l'administration</h2>
    <ul class="lignes">
    <?php foreach (utilisateurs() as $u): ?>
      <li><span class="lien-l"><span><b><?= h($u['nom']) ?></b><small><?= h($u['email']) ?></small></span></span>
        <span class="actions"><?= $u['id'] === $moi['id'] ? '<span class="pastille off">Vous</span>' : bouton_post('suppr_compte', 'comptes', $u['id'], 'Retirer l\'accès', 'btn-danger', '', 'Retirer l\'accès de ' . $u['email'] . ' ?') ?></span></li>
    <?php endforeach; ?>
    </ul>
    <details><summary>Donner accès à une autre personne</summary>
      <form method="post" class="pile court"><?= champ_csrf() ?><input type="hidden" name="action" value="ajout_compte">
        <label>Nom<input name="nom"></label>
        <label>E-mail<input type="email" name="email" required></label>
        <label>Mot de passe provisoire (10 caractères minimum)<input type="password" name="mdp" required minlength="10" autocomplete="new-password"></label>
        <button class="btn-s">Ajouter l'accès</button></form>
    </details>
  </section>
<?php break;

default: ?>
  <h1>Page introuvable</h1><p><a href="<?= h(url('admin/')) ?>">Retour au tableau de bord</a></p>
<?php endswitch; ?>
</main>
<?php endif; ?>
<script>
document.querySelectorAll('form[data-confirmer]').forEach(f => f.addEventListener('submit', e => { if (!confirm(f.dataset.confirmer)) e.preventDefault(); }));
const b = document.querySelector('.burger'), n = document.getElementById('nav');
if (b) b.addEventListener('click', () => { const o = n.classList.toggle('ouvert'); b.setAttribute('aria-expanded', o); });
document.querySelectorAll('.fiche').forEach(f => { let modif = false; f.addEventListener('input', () => modif = true); f.addEventListener('submit', () => modif = false);
  addEventListener('beforeunload', e => { if (modif) e.preventDefault(); }); });
</script>
</body>
</html>
