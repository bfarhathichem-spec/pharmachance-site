<?php
declare(strict_types=1);
$app = is_dir(__DIR__ . '/../app') ? __DIR__ . '/../app' : __DIR__ . '/_app';
require $app . '/lib.php';
require $app . '/composants.php';
require $app . '/seo.php';
require $app . '/articles.php';
require $app . '/pages.php';
require $app . '/edition.php';
entetes_securite();

$chemin = trim(rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'), '/');
$b = trim(base(), '/');
if ($b !== '' && str_starts_with($chemin, $b)) $chemin = trim(substr($chemin, strlen($b)), '/');
$chemin = preg_replace('#^index\.php/?#', '', $chemin);
$parts = $chemin === '' ? [] : explode('/', $chemin);

function page404(): void { http_response_code(404); rendre('404', ['titre' => 'Page introuvable | PharmaChance', 'desc' => '']); }

switch ($parts[0] ?? '') {
case '':
    rendre('accueil', ['titre' => 'Agencement et aménagement de pharmacie en Tunisie | PharmaChance', 'actif' => 'accueil',
        'desc' => "PharmaChance crée, agence et rénove des pharmacies partout en Tunisie : mobilier et comptoirs, merchandising, enseignes, façades et vitrines. Devis sur visite.",
        'ld' => ld_graphe(['@type' => 'WebSite', '@id' => hote() . url() . '#site', 'url' => hote() . url(), 'name' => 'PharmaChance', 'inLanguage' => 'fr-TN'], ld_faq(faq()))]);
    break;
case 'services':
    if (isset($parts[1])) {
        $s = trouver(services(), 'slug', $parts[1]);
        if (!$s) { page404(); break; }
        rendre('service', ['s' => $s, 'titre' => $s['nom'] . ' de pharmacie en Tunisie | PharmaChance', 'desc' => mb_substr(($s['accroche'] ?? '') ?: $s['intro'], 0, 158), 'actif' => 'services', 'og_image' => $s['image'],
            'ld' => ld_graphe(...array_merge([['@type' => 'Service', 'name' => $s['nom'] . ' de pharmacie', 'serviceType' => $s['nom'], 'description' => ($s['accroche'] ?? '') ?: $s['intro'], 'audience' => ['@type' => 'BusinessAudience', 'audienceType' => 'Pharmaciens titulaires d\'officine'], 'areaServed' => ['@type' => 'Country', 'name' => 'Tunisie'], 'provider' => ['@id' => hote() . url() . '#entreprise'], 'url' => hote() . url('services/' . $s['slug'])], ld_ariane([['Services', 'services'], [$s['nom'], null]])], !empty($s['faq']) ? [ld_faq($s['faq'])] : []))]);
    } else rendre('services', ['titre' => 'Création, agencement, façades et vitrines de pharmacie | PharmaChance', 'desc' => "Création complète, rénovation, agencement, merchandising, enseignes, façades et vitrines : les services de PharmaChance pour les pharmacies en Tunisie.", 'actif' => 'services', 'ld' => ld_graphe(ld_ariane([['Services', null]]))]);
    break;
case 'realisations':
    if (isset($parts[1])) {
        $p = trouver(realisations(), 'slug', $parts[1]);
        if (!$p) { page404(); break; }
        rendre('realisation', ['p' => $p, 'titre' => $p['titre'] . ' : réalisation pharmacie | PharmaChance', 'desc' => mb_substr($p['description'] ?? '', 0, 155), 'actif' => 'realisations', 'og_image' => $p['image'],
            'ld' => ld_graphe(['@type' => 'CreativeWork', 'name' => $p['titre'], 'description' => $p['description'] ?? '', 'creator' => ['@id' => hote() . url() . '#entreprise'], 'image' => array_map(fn($g) => hote() . media($g), $p['galerie'] ?: [$p['image']]), 'about' => array_map('nom_service', $p['services'] ?? [])], ld_ariane([['Réalisations', 'realisations'], [$p['titre'], null]]))]);
    } else rendre('realisations', ['titre' => 'Réalisations : façades, vitrines et agencements de pharmacies en Tunisie | PharmaChance', 'desc' => 'Façades, enseignes, vitrines et agencements de pharmacies réalisés par PharmaChance en Tunisie, classés par thématique.', 'actif' => 'realisations', 'ld' => ld_graphe(ld_ariane([['Réalisations', null]]))]);
    break;
case 'vitrines':
    rendre('vitrines', ['titre' => 'Vitrines de pharmacie par thème : grippe, solaire, Ramadan… | PharmaChance', 'desc' => "Vitrines de pharmacie saisonnières conçues, fabriquées et posées par PharmaChance : grippe, immunité, allergies, solaire, moustiques, capillaire, rentrée, Ramadan.", 'actif' => 'vitrines', 'ld' => ld_graphe(ld_ariane([['Vitrines', null]]))]);
    break;
case 'articles':
    if (isset($parts[1])) {
        $a = trouver(articles(), 'slug', $parts[1]);
        if (!$a) { page404(); break; }
        $h = hote();
        $ld = ['@type' => 'BlogPosting', 'headline' => $a['titre'], 'description' => $a['resume'], 'image' => $h . media($a['image']), 'datePublished' => $a['date'] ?? '', 'dateModified' => $a['maj'] ?? ($a['date'] ?? ''),
            'inLanguage' => 'fr-TN', 'articleSection' => $a['categorie'] ?? '', 'wordCount' => str_word_count($a['contenu']), 'author' => ['@id' => $h . url() . '#entreprise'], 'publisher' => ['@id' => $h . url() . '#entreprise'], 'mainEntityOfPage' => $h . url('articles/' . $a['slug'])];
        $g = [$ld, ld_ariane([['Articles', 'articles'], [$a['titre'], null]])];
        if (!empty($a['faq'])) $g[] = ld_faq($a['faq']);
        rendre('article', ['a' => $a, 'titre' => $a['titre'] . ' | PharmaChance', 'desc' => mb_substr($a['resume'], 0, 158), 'actif' => 'articles', 'og_image' => $a['image'], 'og_type' => 'article', 'ld' => ld_graphe(...$g)]);
    } else rendre('articles', ['titre' => 'Articles et conseils : aménagement de pharmacie | PharmaChance', 'desc' => "Conseils d'agenceurs pour ouvrir, rénover et animer une pharmacie en Tunisie : comptoirs, merchandising, vitrines, façades.", 'actif' => 'articles',
        'ld' => ld_graphe(['@type' => 'Blog', 'name' => 'Articles PharmaChance', 'url' => hote() . url('articles'), 'publisher' => ['@id' => hote() . url() . '#entreprise'], 'blogPost' => array_map(fn($a) => ['@type' => 'BlogPosting', 'headline' => $a['titre'], 'url' => hote() . url('articles/' . $a['slug']), 'datePublished' => $a['date'] ?? ''], articles())], ld_ariane([['Articles', null]]))]);
    break;
case 'contact':
    $v = ['titre' => 'Devis aménagement de pharmacie, contact | PharmaChance', 'desc' => "Demandez un devis à PharmaChance pour créer, agencer ou rénover votre pharmacie en Tunisie. Téléphone, WhatsApp ou formulaire avec photo du local.", 'actif' => 'contact', 'ld' => ld_graphe(['@type' => 'ContactPage', 'name' => 'Contact et demande de devis', 'url' => hote() . url('contact'), 'about' => ['@id' => hote() . url() . '#entreprise']], ld_faq(texte_site('contact_faq')), ld_ariane([['Contact', null]]))];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { require $app . '/contact.php'; $v += traiter_contact(); }
    rendre('contact', $v);
    break;
case 'mentions-legales': case 'cgv': case 'confidentialite': case 'cookies':
    $pg = trouver(pages_site(), 'slug', $parts[0]);
    if (!$pg) { page404(); break; }
    rendre('page', ['pg' => $pg, 'titre' => $pg['titre'] . ' | PharmaChance', 'desc' => $pg['resume'] ?? '', 'ld' => ld_graphe(ld_ariane([[$pg['titre'], null]]))]);
    break;
case 'a-propos':
    rendre('apropos', ['titre' => "À propos : " . ((reglages()['annees'] ?? '') ? reglages()['annees'] . " ans d'aménagement de pharmacies" : "aménagement de pharmacies") . " en Tunisie | PharmaChance", 'desc' => "PharmaChance conçoit, fabrique et pose l'agencement des pharmacies en Tunisie : conseil, mobilier, merchandising, enseignes et vitrines. Une seule équipe du relevé à l'ouverture.", 'actif' => 'apropos',
        'ld' => ld_graphe(['@type' => 'AboutPage', 'name' => 'À propos de PharmaChance', 'url' => hote() . url('a-propos'), 'about' => ['@id' => hote() . url() . '#entreprise']], ld_ariane([['À propos', null]]))]);
    break;
case 'faq':
    $toutes = faq(); foreach (articles() as $a) foreach ($a['faq'] ?? [] as $f) $toutes[] = $f;
    rendre('faq', ['titre' => "Questions fréquentes : aménagement de pharmacie | PharmaChance", 'desc' => "Délais, budget, étapes, vitrines, enseignes : les réponses de PharmaChance aux questions des pharmaciens.", 'ld' => ld_graphe(ld_faq($toutes), ld_ariane([['Questions fréquentes', null]]))]);
    break;
case 'plan-du-site':
    rendre('plan', ['titre' => 'Plan du site | PharmaChance', 'desc' => 'Toutes les pages du site PharmaChance.']);
    break;
case 'sitemap.xml':
    header('Content-Type: application/xml; charset=utf-8');
    $hote = (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'pharmachance.com'));
    $u = ['', 'a-propos', 'services', 'realisations', 'vitrines', 'articles', 'faq', 'contact', 'plan-du-site'];
    foreach (pages_site() as $x) $u[] = $x['slug'];
    foreach (articles() as $a) $u[] = 'articles/' . $a['slug'];
    foreach (services() as $s) $u[] = 'services/' . $s['slug'];
    foreach (realisations() as $p) $u[] = 'realisations/' . $p['slug'];
    echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    $lm = date('Y-m-d', max(array_map(fn($f) => @filemtime(DATA_DIR . "/$f.json") ?: 0, ['services', 'realisations', 'themes', 'reglages', 'articles', 'pages'])));
    foreach ($u as $x) echo '<url><loc>' . h($hote . url($x)) . '</loc><lastmod>' . $lm . '</lastmod></url>';
    echo '</urlset>';
    break;
case 'uploads': // photos envoyées depuis l'admin, stockées hors du dossier déployé
    $rel = implode('/', array_slice($parts, 1));
    $f = realpath(UPLOAD_DIR . '/' . $rel); $base = realpath(UPLOAD_DIR);
    if (!$f || !$base || !str_starts_with($f, $base . DIRECTORY_SEPARATOR) || !is_file($f) || !preg_match('/\.(jpe?g|png|webp)$/i', $f)) { page404(); break; }
    $mime = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'][strtolower(pathinfo($f, PATHINFO_EXTENSION))];
    $etag = '"' . md5($f . filemtime($f)) . '"';
    header('Content-Type: ' . $mime); header('Cache-Control: public, max-age=2592000'); header('ETag: ' . $etag);
    if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) { http_response_code(304); exit; }
    header('Content-Length: ' . filesize($f)); readfile($f); exit;
case 'llms.txt':
    header('Content-Type: text/plain; charset=utf-8');
    require $app . '/templates/llms.php';
    break;
default:
    page404();
}
