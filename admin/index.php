<?php
declare(strict_types=1);
$app = is_dir(__DIR__ . '/../../app') ? __DIR__ . '/../../app' : __DIR__ . '/../_app';
require $app . '/lib.php';
require $app . '/articles.php';
require $app . '/pages.php';
require $app . '/edition.php';
entetes_securite(true);
demarrer_session();

$p = preg_replace('/[^a-z-]/', '', (string)($_GET['p'] ?? 'tableau')) ?: 'tableau';
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
function flash_sortie(): never { header('Location: ' . url('admin/?sortie=1')); exit; }
function flash(string $m): void { $_SESSION['flash'] = $m; }
function aller(string $q): never { header('Location: ' . url('admin/?' . $q)); exit; }
function post(string $k, string $d = ''): string { return trim((string)($_POST[$k] ?? $d)); }
function cle_id(string $nom): string { return in_array($nom, ['services', 'themes', 'articles', 'secteurs'], true) ? 'slug' : 'id'; }
function deplacer(string $nom, string $id, int $sens): void {
    $l = trier(charger($nom)); $k = cle_id($nom);
    foreach ($l as $i => $x) if ($x[$k] === $id) { $j = $i + $sens; if (isset($l[$j])) { [$l[$i], $l[$j]] = [$l[$j], $l[$i]]; } break; }
    foreach ($l as $i => &$x) $x['ordre'] = $i + 1; unset($x);
    enregistrer($nom, $l);
}
function maj(string $nom, string $id, callable $f): void {
    $l = charger($nom); $k = cle_id($nom);
    foreach ($l as &$x) if (($x[$k] ?? '') === $id) $x = $f($x); unset($x);
    enregistrer($nom, $l);
}
function slug_unique(string $nom, string $base, string $sauf = ''): string {
    $k = 'slug'; $s = $base; $n = 2;
    $pris = array_map(fn($x) => $x[$k] ?? '', array_filter(charger($nom), fn($x) => ($x[cle_id($nom)] ?? '') !== $sauf));
    while (in_array($s, $pris, true)) $s = $base . '-' . $n++;
    return $s;
}
function vignette(string $src, string $alt = ''): string { return $src ? '<img class="vg" src="' . h(media($src)) . '" alt="' . h($alt) . '">' : '<span class="vg vide">—</span>'; }

function enregistrer_edition(array $d): array {
    $s = surcharges();
    $n = 0;
    foreach ((array)($d['textes'] ?? []) as $ori => $nouv) {
        $ori = cle_texte((string)$ori); if ($ori === '' || mb_strlen($ori) > 3000 || !is_string($nouv)) continue;
        $nouv = trim((string)preg_replace('/[\s\x{00A0}]+/u', ' ', strip_tags($nouv)));
        if (mb_strlen($nouv) > 3000) $nouv = mb_substr($nouv, 0, 3000);
        if ($nouv === $ori) unset($s['textes'][$ori]); else $s['textes'][$ori] = $nouv;
        $n++;
    }
    $pgImg = strtolower((string)($d['chemin'] ?? ''));
    if (preg_match('#^/[a-z0-9/._-]{0,200}$#', $pgImg)) foreach ((array)($d['images'] ?? []) as $cle => $nouv) {
        [$portee, $ori, $rang] = array_pad(explode('|', (string)$cle, 3), 3, '');
        $nouv = (string)$nouv;
        if (!preg_match('/^[a-z0-9.#-]{1,60}$/', $portee) || !preg_match('#^(img|uploads)/[A-Za-z0-9/_.-]+$#', $ori) || str_contains($ori, '..') || !ctype_digit($rang)) continue;
        $k = "$pgImg|$portee|$ori|$rang";
        if ($nouv === '' || $nouv === $ori) { unset($s['images_p'][$k]); $n++; continue; }
        if (!preg_match('#^uploads/\d{4}/\d{2}/[a-z0-9-]+\.jpg$#', $nouv) || !is_file(UPLOAD_DIR . '/' . substr($nouv, 8))) continue;
        $s['images_p'][$k] = $nouv; $n++;
    }
    foreach ((array)($d['blocs'] ?? []) as $pg => $c) {
        if (!preg_match('/^[a-z0-9-]{1,40}$/', (string)$pg) || !is_array($c)) continue;
        $ok = fn($l) => array_values(array_unique(array_filter(array_map('strval', (array)$l), fn($x) => preg_match('/^[a-z0-9.-]{1,60}$/', $x))));
        $s['blocs'][$pg] = ['ordre' => $ok($c['ordre'] ?? []), 'masques' => $ok($c['masques'] ?? [])]; $n++;
    }
    enregistrer('surcharges', $s);
    foreach ((array)($d['champs'] ?? []) as $cle => $chemin) {
        [$nom, $id, $champ] = array_pad(explode(':', (string)$cle, 3), 3, '');
        $chemin = (string)$chemin;
        if ($nom !== 'realisations' || !in_array($champ, ['avant', 'apres'], true)) continue;
        if (!preg_match('#^uploads/\d{4}/\d{2}/[a-z0-9-]+\.jpg$#', $chemin) || !is_file(UPLOAD_DIR . '/' . substr($chemin, 8))) continue;
        maj('realisations', $id, function ($x) use ($champ, $chemin) { $x[$champ] = $chemin; return $x; }); $n++;
    }
    foreach ((array)($d['listes'] ?? []) as $nom => $ids) {
        if (!in_array($nom, ['realisations', 'services'], true)) continue;
        $k = cle_id($nom); $ids = array_map('strval', (array)$ids);
        $l = trier(charger($nom)); $pos = array_flip($ids);
        $dans = array_values(array_filter($l, fn($x) => isset($pos[(string)($x[$k] ?? '')])));
        usort($dans, fn($a, $b) => $pos[(string)$a[$k]] <=> $pos[(string)$b[$k]]);
        $i = 0; $res = [];
        foreach ($l as $x) $res[] = isset($pos[(string)($x[$k] ?? '')]) ? $dans[$i++] : $x; // les éléments non affichés gardent leur place
        foreach ($res as $j => &$x) $x['ordre'] = $j + 1; unset($x);
        enregistrer($nom, $res); $n++;
    }
    return ['ok' => true, 'message' => $n ? 'Modifications enregistrées.' : 'Rien à enregistrer.'];
}

/* ---------- Première connexion : création du compte ---------- */
if (!utilisateurs()) {
    $erreur = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifier_csrf();
        $email = post('email'); $mdp = (string)($_POST['mdp'] ?? ''); $mdp2 = (string)($_POST['mdp2'] ?? '');
        $hc = trim((string)(@file_get_contents(DATA_DIR . '/code-installation.hash') ?: @file_get_contents(SEED_DIR . '/code-installation.hash')));
        $saisi = strtoupper(trim(post('code')));
        if ($hc === '' || !password_verify($saisi, $hc)) { noter_echec($_SERVER['REMOTE_ADDR'] ?? ''); $erreur = "Code d'installation incorrect."; }
        elseif (!tentatives_ok($_SERVER['REMOTE_ADDR'] ?? '')) $erreur = 'Trop de tentatives. Réessayez dans 15 minutes.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreur = 'Adresse e-mail invalide.';
        elseif (mb_strlen($mdp) < 10) $erreur = 'Le mot de passe doit faire au moins 10 caractères.';
        elseif ($mdp !== $mdp2) $erreur = 'Les deux mots de passe ne sont pas identiques.';
        else {
            enregistrer('comptes', [['id' => bin2hex(random_bytes(6)), 'nom' => post('nom') ?: 'Administrateur', 'email' => mb_strtolower($email), 'hash' => password_hash($mdp, PASSWORD_DEFAULT), 'cree' => date('c')]]);
            @unlink(DATA_DIR . '/code-installation.hash');
            flash('Compte créé. Connectez-vous.'); aller('p=connexion');
        }
    }
    $page = 'installation';
}
/* ---------- Connexion ---------- */
elseif ($p === 'deconnexion') { session_unset(); session_destroy(); setcookie('pc_edition', '0', ['expires' => time() - 3600, 'path' => base() . '/']); setcookie('pc_session', '', ['expires' => time() - 3600, 'path' => base() . '/']); flash_sortie(); }
elseif (!($moi = utilisateur())) {
    $erreur = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifier_csrf();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!tentatives_ok($ip)) $erreur = 'Trop de tentatives. Réessayez dans 15 minutes.';
        else {
            $u = trouver(utilisateurs(), 'email', mb_strtolower(post('email')));
            if ($u && password_verify((string)($_POST['mdp'] ?? ''), $u['hash'])) {
                session_regenerate_id(true); $_SESSION['uid'] = $u['id']; $_SESSION['vu'] = time();
                aller('p=tableau');
            }
            noter_echec($ip); usleep(400000); $erreur = 'E-mail ou mot de passe incorrect.';
        }
    }
    $page = 'connexion';
}
/* ---------- Espace connecté ---------- */
else {
    $page = $p;
    /* Édition visuelle : photo déposée sur une page, puis enregistrement de toutes les modifications */
    if ($p === 'edition-image' || $p === 'edition-enregistrer') {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) { http_response_code(400); exit(json_encode(['ok' => false, 'message' => 'Session expirée : rechargez la page.'])); }
        if ($p === 'edition-image') {
            $src = traiter_image($_FILES['photo'] ?? [], 'photo');
            if (!$src) { http_response_code(422); exit(json_encode(['ok' => false, 'message' => 'Photo refusée : utilisez un JPEG, PNG ou WebP de moins de 20 Mo.'])); }
            exit(json_encode(['ok' => true, 'chemin' => $src, 'url' => url($src)]));
        }
        exit(json_encode(enregistrer_edition(json_decode((string)($_POST['donnees'] ?? ''), true) ?: [])));
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifier_csrf();
        $a = post('action'); $id = post('id');
        switch ($a) {
        /* ordre, publication, suppression communs */
        case 'monter': case 'descendre':
            $nom = post('liste'); if (in_array($nom, ['realisations', 'services', 'themes', 'temoignages', 'articles', 'secteurs'], true)) deplacer($nom, $id, $a === 'monter' ? -1 : 1);
            aller('p=' . $nom);
        case 'basculer':
            $nom = post('liste'); $champ = post('champ');
            if (in_array($nom, ['realisations', 'services', 'themes', 'temoignages', 'articles', 'secteurs'], true) && in_array($champ, ['publie', 'en_avant'], true))
                maj($nom, $id, function ($x) use ($champ) { $x[$champ] = empty($x[$champ]); return $x; });
            aller('p=' . $nom);
        case 'supprimer':
            $nom = post('liste');
            if ($nom === 'services') {
                $s = trouver(charger('services'), 'slug', $id);
                $util = $s ? array_filter(charger('realisations'), fn($r) => in_array($s['cle'], $r['services'] ?? [], true)) : [];
                if ($util) { flash('Ce service est utilisé par ' . count($util) . ' réalisation(s). Dépubliez-le plutôt, ou retirez-le de ces réalisations.'); aller('p=services'); }
            }
            if ($nom === 'secteurs') {
                $util = array_filter(charger('realisations'), fn($r) => ($r['secteur'] ?? '') === $id);
                if ($util) { flash('Ce secteur contient ' . count($util) . ' projet(s). Changez d\'abord leur secteur, ou mettez le secteur hors ligne.'); aller('p=secteur&id=' . rawurlencode($id)); }
            }
            if (in_array($nom, ['realisations', 'services', 'themes', 'temoignages', 'demandes', 'articles', 'secteurs'], true)) {
                $k = $nom === 'demandes' ? 'id' : cle_id($nom);
                enregistrer($nom, array_values(array_filter(charger($nom), fn($x) => ($x[$k] ?? '') !== $id)));
                flash('Élément supprimé.');
            }
            aller('p=' . $nom);

        /* réalisations */
        case 'realisation':
            $l = charger('realisations'); $ex = $id ? trouver($l, 'id', $id) : null;
            $titre = post('titre');
            if ($titre === '') { flash('Le nom du projet est obligatoire.'); aller('p=realisation&id=' . rawurlencode($id)); }
            $r = $ex ?? ['id' => 'r' . bin2hex(random_bytes(4)), 'ordre' => count($l) + 1, 'galerie' => [], 'publie' => false, 'en_avant' => false];
            $r['titre'] = $titre; $r['ville'] = post('ville'); $r['annee'] = post('annee'); $r['surface'] = post('surface');
            $r['description'] = post('description'); $r['interventions'] = lignes(post('interventions'));
            $r['services'] = array_values(array_intersect((array)($_POST['services'] ?? []), array_column(charger('services'), 'cle')));
            $r['secteur'] = in_array(post('secteur'), array_column(charger('secteurs', []), 'slug'), true) ? post('secteur') : '';
            $r['cadrage'] = in_array(post('cadrage'), ['50% 20%', '50% 50%', '50% 80%', '30% 50%', '70% 50%'], true) ? post('cadrage') : '50% 50%';
            $r['publie'] = !empty($_POST['publie']); $r['en_avant'] = !empty($_POST['en_avant']);
            $r['slug'] = slug_unique('realisations', slugify(post('slug') ?: $titre), $r['id']);
            // galerie : retraits puis ajouts
            $retirer = (array)($_POST['retirer'] ?? []);
            $r['galerie'] = array_values(array_filter($r['galerie'] ?? [], fn($g) => !in_array($g, $retirer, true)));
            foreach (fichiers_multiples('photos') as $f) if ($src = traiter_image($f, $r['slug'])) $r['galerie'][] = $src;
            $principale = post('principale');
            if ($principale && in_array($principale, $r['galerie'], true)) $r['image'] = $principale;
            if (empty($r['image']) || !in_array($r['image'], $r['galerie'], true)) $r['image'] = $r['galerie'][0] ?? '';
            // avant / après
            if (!empty($_POST['retirer_avant'])) $r['avant'] = '';
            if (!empty($_POST['retirer_apres'])) $r['apres'] = '';
            if ($src = traiter_image($_FILES['avant'] ?? [], $r['slug'] . '-avant')) $r['avant'] = $src;
            if ($src = traiter_image($_FILES['apres'] ?? [], $r['slug'] . '-apres')) $r['apres'] = $src;
            $r['avant'] ??= ''; $r['apres'] ??= '';
            if ($r['publie'] && !$r['image']) { $r['publie'] = false; flash('Enregistré, mais pas publié : ajoutez au moins une photo.'); }
            elseif ($r['publie'] && facade_incomplete($r) && empty($ex['publie'])) { $r['publie'] = false; flash('Enregistré, mais pas publié : un projet de façade doit avoir une photo avant et une photo après.'); }
            elseif (facade_incomplete($r)) flash('Réalisation enregistrée. Pensez à ajouter la photo avant et la photo après de cette façade.');
            else flash($ex ? 'Réalisation enregistrée.' : 'Réalisation créée.');
            if ($ex) { foreach ($l as &$x) if ($x['id'] === $id) $x = $r; unset($x); } else $l[] = $r;
            enregistrer('realisations', $l);
            aller('p=realisation&id=' . rawurlencode($r['id']));

        /* services */
        case 'service':
            $l = charger('services'); $ex = $id ? trouver($l, 'slug', $id) : null;
            $nom = post('nom'); if ($nom === '') { flash('Le nom est obligatoire.'); aller('p=services'); }
            $s = $ex ?? ['ordre' => count($l) + 1, 'publie' => false, 'cadrage' => '50% 50%', 'image' => ''];
            $s['nom'] = $nom; $s['court'] = post('court'); $s['intro'] = post('intro');
            $s['besoins'] = lignes(post('besoins')); $s['comprend'] = lignes(post('comprend')); $s['accroche'] = post('accroche'); $s['texte'] = str_replace("\r\n", "\n", (string)($_POST['texte'] ?? '')); $s['faq'] = faq_depuis_texte((string)($_POST['faq'] ?? ''));
            $s['publie'] = !empty($_POST['publie']);
            $s['cadrage'] = in_array(post('cadrage'), ['50% 20%', '50% 50%', '50% 80%', '30% 50%', '70% 50%'], true) ? post('cadrage') : '50% 50%';
            if (!$ex) { $s['slug'] = slug_unique('services', slugify($nom)); $s['cle'] = $s['slug']; }
            if ($src = traiter_image($_FILES['image'] ?? [], $s['slug'])) $s['image'] = $src;
            if ($ex) { foreach ($l as &$x) if ($x['slug'] === $id) $x = $s; unset($x); } else $l[] = $s;
            enregistrer('services', $l); flash('Service enregistré.');
            aller('p=service&id=' . rawurlencode($s['slug']));

        /* thèmes de vitrines */
        case 'secteur':
            $l = charger('secteurs', []); $ex = $id ? trouver($l, 'slug', $id) : null;
            $nom = post('nom'); if ($nom === '') { flash('Le nom est obligatoire.'); aller('p=secteurs'); }
            $x = $ex ?? ['ordre' => count($l) + 1];
            $x['nom'] = $nom; $x['intro'] = post('intro'); $x['points'] = lignes(post('points')); $x['publie'] = !empty($_POST['publie']);
            if (!$ex) $x['slug'] = slug_unique('secteurs', slugify($nom));
            if ($ex) { foreach ($l as &$y) if ($y['slug'] === $id) $y = $x; unset($y); } else $l[] = $x;
            enregistrer('secteurs', $l); flash($ex ? 'Secteur enregistré.' : 'Secteur créé. Ajoutez-y maintenant un projet.');
            aller($ex ? 'p=secteur&id=' . rawurlencode($x['slug']) : 'p=secteurs');

        case 'theme':
            $l = charger('themes'); $ex = $id ? trouver($l, 'slug', $id) : null;
            $nom = post('nom'); if ($nom === '') { flash('Le nom est obligatoire.'); aller('p=themes'); }
            $t = $ex ?? ['ordre' => count($l) + 1, 'publie' => true, 'images' => []];
            $t['nom'] = $nom; $t['publie'] = !empty($_POST['publie']); $t['description'] = post('description'); $t['periode'] = post('periode');
            if (!$ex) $t['slug'] = slug_unique('themes', slugify($nom));
            $retirer = (array)($_POST['retirer'] ?? []);
            $t['images'] = array_values(array_filter($t['images'], fn($g) => !in_array($g, $retirer, true)));
            foreach (fichiers_multiples('photos') as $f) if ($src = traiter_image($f, 'vitrine-' . $t['slug'])) $t['images'][] = $src;
            if ($ex) { foreach ($l as &$x) if ($x['slug'] === $id) $x = $t; unset($x); } else $l[] = $t;
            enregistrer('themes', $l); flash('Thème enregistré.');
            aller('p=theme&id=' . rawurlencode($t['slug']));

        /* articles */
        case 'article':
            $l = charger('articles', []); $ex = $id ? trouver($l, 'slug', $id) : null;
            $titre = post('titre');
            if ($titre === '' || post('contenu') === '') { flash('Le titre et le texte sont obligatoires.'); aller('p=article&id=' . rawurlencode($id)); }
            $x = $ex ?? ['ordre' => 0, 'image' => '', 'date' => date('Y-m-d')];
            $x['titre'] = $titre; $x['categorie'] = post('categorie') ?: 'Conseils'; $x['resume'] = post('resume');
            $x['contenu'] = str_replace("\r\n", "\n", (string)($_POST['contenu'] ?? ''));
            $x['faq'] = faq_depuis_texte((string)($_POST['faq'] ?? ''));
            $x['image_alt'] = post('image_alt') ?: $titre;
            $x['date'] = preg_match('/^\d{4}-\d{2}-\d{2}$/', post('date')) ? post('date') : ($x['date'] ?? date('Y-m-d'));
            if ($ex) $x['maj'] = date('Y-m-d');
            $x['publie'] = !empty($_POST['publie']); $x['en_avant'] = !empty($_POST['en_avant']);
            $x['slug'] = $ex ? $ex['slug'] : slug_unique('articles', slugify(post('slug') ?: $titre));
            if ($src = traiter_image($_FILES['image'] ?? [], 'article-' . $x['slug'])) $x['image'] = $src;
            if ($src = traiter_image($_FILES['illustration'] ?? [], 'article-' . $x['slug'])) $x['contenu'] .= "\n\n![" . (post('illustration_alt') ?: $titre) . "](" . $src . ")";
            if ($x['publie'] && !$x['image']) { $x['publie'] = false; flash('Enregistré, mais pas publié : ajoutez une image de couverture.'); }
            else flash($ex ? 'Article enregistré.' : 'Article créé.');
            if ($ex) { foreach ($l as &$y) if ($y['slug'] === $id) $y = $x; unset($y); } else { foreach ($l as &$y) $y['ordre'] = ($y['ordre'] ?? 0) + 1; unset($y); $x['ordre'] = 1; array_unshift($l, $x); }
            enregistrer('articles', $l);
            aller('p=article&id=' . rawurlencode($x['slug']));

        /* pages légales */
        case 'page':
            $l = charger('pages', []); $trouve = false;
            foreach ($l as &$x) if ($x['slug'] === $id) { $x['titre'] = post('titre') ?: $x['titre']; $x['resume'] = post('resume'); $x['contenu'] = str_replace("\r\n", "\n", (string)($_POST['contenu'] ?? '')); $x['maj'] = date('Y-m-d'); $trouve = true; }
            unset($x);
            if ($trouve) { enregistrer('pages', $l); flash('Page enregistrée.'); }
            aller('p=page&id=' . rawurlencode($id));

        /* témoignages */
        case 'temoignage':
            $l = charger('temoignages'); $ex = $id ? trouver($l, 'id', $id) : null;
            if (post('texte') === '' || post('nom') === '') { flash('Le texte et le nom sont obligatoires.'); aller('p=temoignage&id=' . rawurlencode($id)); }
            $t = $ex ?? ['id' => 't' . bin2hex(random_bytes(4)), 'ordre' => count($l) + 1, 'image' => ''];
            $t['texte'] = post('texte'); $t['nom'] = post('nom'); $t['etablissement'] = post('etablissement');
            $t['realisation'] = post('realisation'); $t['accord'] = !empty($_POST['accord']);
            $t['publie'] = !empty($_POST['publie']) && $t['accord'];
            if (!empty($_POST['retirer_image'])) $t['image'] = '';
            if ($src = traiter_image($_FILES['image'] ?? [], 'temoignage', 600)) $t['image'] = $src;
            if ($ex) { foreach ($l as &$x) if ($x['id'] === $id) $x = $t; unset($x); } else $l[] = $t;
            enregistrer('temoignages', $l);
            flash(!empty($_POST['publie']) && !$t['accord'] ? "Enregistré, mais pas publié : cochez l'accord du client." : 'Témoignage enregistré.');
            aller('p=temoignages');

        /* demandes */
        case 'statut':
            $l = charger('demandes');
            foreach ($l as &$x) if ($x['id'] === $id) $x['statut'] = post('statut') === 'traitee' ? 'traitee' : 'nouvelle'; unset($x);
            enregistrer('demandes', $l); aller('p=demande&id=' . rawurlencode($id));

        /* réglages */
        case 'reglages':
            $r = reglages();
            foreach (['telephone', 'whatsapp', 'email', 'email_notifications', 'adresse', 'zone', 'horaires', 'annees', 'hero_titre', 'hero_texte', 'facebook', 'instagram', 'linkedin', 'aa_accueil', 'raison_sociale', 'forme_juridique', 'capital', 'identifiant_unique', 'matricule_fiscal', 'directeur_publication'] as $k) $r[$k] = post($k);
            $ga = strtoupper(post('ga_id')); $r['ga_id'] = preg_match('/^G-[A-Z0-9]{4,16}$/', $ga) ? $ga : '';
            $gsc = post('gsc_verif'); if (preg_match('/content="([^"]+)"/', $gsc, $mm)) $gsc = $mm[1]; $r['gsc_verif'] = preg_match('/^[A-Za-z0-9_-]{10,80}$/', $gsc) ? $gsc : '';
            $r['whatsapp'] = preg_replace('/\D/', '', $r['whatsapp']);
            $r['hero_lien'] = post('hero_lien');
            $vals = (array)($_POST['chiffre_valeur'] ?? []); $libs = (array)($_POST['chiffre_libelle'] ?? []); $r['chiffres'] = [];
            for ($i = 0; $i < 4; $i++) { $v = mb_substr(trim((string)($vals[$i] ?? '')), 0, 12); $l = mb_substr(trim((string)($libs[$i] ?? '')), 0, 60); if ($v !== '' || $l !== '') $r['chiffres'][] = ['valeur' => $v, 'libelle' => $l]; }
            if (!empty($_POST['retirer_hero_image'])) $r['hero_image'] = '';
            if ($src = traiter_image($_FILES['hero_image'] ?? [], 'accueil')) $r['hero_image'] = $src;
            enregistrer('reglages', $r); flash('Réglages enregistrés.'); aller('p=reglages');

        /* compte */
        case 'mdp':
            $u = utilisateur(); $n = (string)($_POST['nouveau'] ?? '');
            if (!password_verify((string)($_POST['actuel'] ?? ''), $u['hash'])) flash('Mot de passe actuel incorrect.');
            elseif (mb_strlen($n) < 10 || $n !== ($_POST['nouveau2'] ?? '')) flash('Le nouveau mot de passe doit faire 10 caractères minimum et être saisi deux fois à l\'identique.');
            else { $l = utilisateurs(); foreach ($l as &$x) if ($x['id'] === $u['id']) $x['hash'] = password_hash($n, PASSWORD_DEFAULT); unset($x); enregistrer('comptes', $l); flash('Mot de passe modifié.'); }
            aller('p=compte');
        case 'ajout_compte':
            $email = mb_strtolower(post('email')); $n = (string)($_POST['mdp'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($n) < 10) flash('E-mail valide et mot de passe de 10 caractères minimum requis.');
            elseif (trouver(utilisateurs(), 'email', $email)) flash('Ce compte existe déjà.');
            else { $l = utilisateurs(); $l[] = ['id' => bin2hex(random_bytes(6)), 'nom' => post('nom') ?: $email, 'email' => $email, 'hash' => password_hash($n, PASSWORD_DEFAULT), 'cree' => date('c')]; enregistrer('comptes', $l); flash('Compte ajouté.'); }
            aller('p=compte');
        case 'suppr_compte':
            if ($id === utilisateur()['id']) flash('Vous ne pouvez pas supprimer votre propre compte.');
            else { enregistrer('comptes', array_values(array_filter(utilisateurs(), fn($x) => $x['id'] !== $id))); flash('Compte supprimé.'); }
            aller('p=compte');
        }
        aller('p=tableau');
    }
    /* téléchargement d'une pièce jointe de demande */
    if ($p === 'fichier') {
        $id = preg_replace('/[^0-9a-f-]/', '', (string)($_GET['id'] ?? '')); $f = basename((string)($_GET['f'] ?? ''));
        $chemin = FICHIERS_DEMANDES . "/$id/$f";
        if ($id && $f && is_file($chemin)) {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($chemin);
            header('Content-Type: ' . $mime); header('Content-Disposition: inline; filename="demande-' . $id . '-' . $f . '"'); header('Content-Length: ' . filesize($chemin));
            readfile($chemin); exit;
        }
        http_response_code(404); exit('Fichier introuvable.');
    }
    /* export CSV des demandes (Excel FR : point-virgule + BOM) */
    if ($p === 'export') {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="demandes-pharmachance-' . date('Y-m-d') . '.csv"');
        $o = fopen('php://output', 'w'); fwrite($o, "\xEF\xBB\xBF");
        fputcsv($o, ['Date', 'Nom', 'Téléphone', 'E-mail', 'Ville', 'Projet', 'Budget', 'Message', 'Fichiers', 'Statut'], ';');
        foreach (charger('demandes') as $d) fputcsv($o, [date('d/m/Y H:i', strtotime($d['date'])), $d['nom'], $d['tel'], $d['email'], $d['ville'], $d['projet'] ? nom_service($d['projet']) : '', $d['budget'], $d['message'], count($d['fichiers'] ?? []), $d['statut'] === 'traitee' ? 'Traitée' : 'Nouvelle'], ';');
        exit;
    }
}

require $app . '/admin-vues.php';
