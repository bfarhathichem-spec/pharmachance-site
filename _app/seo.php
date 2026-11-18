<?php
/* Référencement : questions fréquentes, données structurées, fil d'Ariane, miniatures */

function faq(): array {
    $r = reglages(); $zone = $r['zone'] ?? 'Toute la Tunisie';
    return charger('faq', null) ?? [
        ['q' => "Que fait PharmaChance ?", 'r' => (!empty($r['annees']) ? "Depuis " . $r['annees'] . " ans, " : '') . "PharmaChance conçoit, construit, aménage et rénove des pharmacies en Tunisie. Nous prenons en charge la création complète d'une officine, sa rénovation, l'agencement intérieur (comptoirs, linéaires, rangements), le merchandising, les enseignes et façades, ainsi que les vitrines thématiques."],
        ['q' => "Dans quelles villes intervenez-vous ?", 'r' => "Notre bureau est à Ennaser 2, à Tunis. Zone d'intervention : " . mb_strtolower($zone) . "."],
        ['q' => "Pouvez-vous transformer un local vide en pharmacie ?", 'r' => "Oui. Nous transformons un local brut, un garage ou un espace commercial vide en officine prête à ouvrir : organisation des espaces, travaux, agencement, mobilier et façade, avec une seule équipe du début à la fin."],
        ['q' => "Comment se déroule un projet d'aménagement de pharmacie ?", 'r' => "En cinq étapes : visite du local, relevé et étude, présentation du projet en image, validation par le pharmacien, puis fabrication et pose par nos équipes."],
        ['q' => "Combien coûte l'aménagement d'une pharmacie ?", 'r' => "Le budget dépend de la surface, de l'état du local et des prestations choisies (agencement seul, rénovation, façade, création complète). Nous établissons un devis détaillé après la visite et le relevé du local."],
        ['q' => "Fabriquez-vous les enseignes et la croix de pharmacie ?", 'r' => "Nous concevons les enseignes et les façades, les fabriquons dans notre atelier et les posons : enseignes lumineuses bilingues français et arabe, lettres découpées, caducée, habillage de façade et vitrophanie."],
        ['q' => "Proposez-vous des vitrines de pharmacie pour chaque saison ?", 'r' => "Oui. Nous créons des vitrines thématiques prêtes à poser : grippe et vaccination, immunité, allergies de printemps, protection solaire, moustiques, soins capillaires, rentrée, Ramadan, fin d'année, et des thèmes sur mesure."],
        ['q' => "Comment demander un devis ?", 'r' => "Par le formulaire de la page Contact, par téléphone au " . ($r['telephone'] ?? '') . " ou sur WhatsApp. Joignez une photo ou un plan du local : nous vous rappelons pour organiser la visite."],
    ];
}

function hote(): string { return (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'pharmachance.com')); }

function ld_entreprise(): array {
    $r = reglages(); $h = hote();
    $reseaux = array_values(array_filter([$r['facebook'] ?? '', $r['instagram'] ?? '', $r['linkedin'] ?? '']));
    $d = ['@type' => ['LocalBusiness', 'GeneralContractor'], '@id' => $h . url() . '#entreprise', 'name' => 'PharmaChance',
        'description' => (!empty($r['annees']) ? $r['annees'] . " ans d'expérience. " : '') . "Conception, construction, aménagement et rénovation de pharmacies en Tunisie : agencement, merchandising, enseignes, façades et vitrines.",
        'url' => $h . url(), 'logo' => $h . url('img/logo-couleur.png'), 'image' => $h . url('img/facade-nord-hilton.jpg'),
        'telephone' => $r['telephone'] ?? '', 'email' => $r['email'] ?? '',
        'address' => ['@type' => 'PostalAddress', 'streetAddress' => 'Rue Mahrez Chairaat, Résidence Aden', 'addressLocality' => 'Ennaser 2, Tunis', 'addressCountry' => 'TN'],
        'areaServed' => ['@type' => 'Country', 'name' => 'Tunisie'],
        'knowsAbout' => ['agencement de pharmacie', 'aménagement de pharmacie', 'rénovation de pharmacie', 'mobilier de pharmacie', 'merchandising en pharmacie', 'enseigne de pharmacie', 'façade de pharmacie', 'vitrine de pharmacie'],
        'hasOfferCatalog' => ['@type' => 'OfferCatalog', 'name' => 'Services PharmaChance', 'itemListElement' => array_map(fn($s) => ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => $s['nom'], 'url' => $h . url('services/' . $s['slug'])]], services())]];
    if ($reseaux) $d['sameAs'] = $reseaux;
    return $d;
}

function ld_faq(array $f): array {
    return ['@type' => 'FAQPage', 'mainEntity' => array_map(fn($x) => ['@type' => 'Question', 'name' => $x['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $x['r']]], $f)];
}

/* $chemin = [[libellé, url relative|null], …] */
function ld_ariane(array $chemin): array {
    $h = hote(); $l = [['Accueil', '']]; array_push($l, ...$chemin); $i = 0;
    return ['@type' => 'BreadcrumbList', 'itemListElement' => array_map(function ($x) use ($h, &$i) { $i++; return array_filter(['@type' => 'ListItem', 'position' => $i, 'name' => $x[0], 'item' => $x[1] !== null ? $h . url($x[1]) : null]); }, $l)];
}

function ld_graphe(array ...$noeuds): array { return ['@context' => 'https://schema.org', '@graph' => array_merge([ld_entreprise()], $noeuds)]; }

function faq_bloc(array $f, string $titre = 'Questions fréquentes'): string {
    $o = '<section class="wrap faq"><h2 class="h-moy">' . h($titre) . '</h2>';
    foreach ($f as $x) $o .= '<details><summary>' . h($x['q']) . '</summary><p>' . h($x['r']) . '</p></details>';
    return $o . '</section>';
}

/* Miniature WebP 900 px si elle existe (générée à l'envoi ou au déploiement) */
function min_de(string $src): string {
    $m = preg_replace('#^(img|uploads)/(.+)\.(jpe?g|png)$#i', '$1/min/$2.webp', $src);
    $fichier = str_starts_with($m, 'uploads/') ? UPLOAD_DIR . '/' . substr($m, 8) : WWW_DIR . '/' . $m;
    return ($m !== $src && is_file($fichier)) ? $m : $src;
}
function miniature(string $src): string {
    if ($src === '') return '';
    if (!empty($GLOBALS['pc_marqueurs'])) { $r = 'min:' . $src; $GLOBALS['pc_miniatures'][$r] = $src; return $r; }
    return min_de($src);
}

/* Textes éditoriaux (data/textes.json) avec {annees} remplacé */
function texte_site(string $cle) {
    static $t = null; $t ??= charger('textes', []);
    $v = $t[$cle] ?? '';
    $a = trim((string)(reglages()['annees'] ?? ''));
    $f = fn($s) => str_replace('{annees}', $a !== '' ? $a : 'plus de 20', $s);
    if (is_array($v)) return array_map(fn($x) => is_array($x) ? array_map($f, $x) : $f($x), $v);
    return $f($v);
}

/* Résumé « en bref » d'une réalisation, construit à partir de ses données */
function resume_realisation(array $p): string {
    $serv = array_map('nom_service', $p['services'] ?? []);
    $o = h($p['titre']) . ' est une réalisation de PharmaChance, agenceur de pharmacies en Tunisie';
    if (trim($p['ville'] ?? '') !== '') $o .= ', à ' . h($p['ville']);
    if (trim($p['annee'] ?? '') !== '') $o .= ' (' . h($p['annee']) . ')';
    $o .= '.';
    if ($serv) $o .= ' Elle relève de ' . (count($serv) > 1 ? 'nos services ' : 'notre service ') . h(implode(' et ', $serv)) . '.';
    if (!empty($p['interventions'])) $o .= ' Interventions : ' . h(mb_strtolower(implode(', ', $p['interventions']))) . '.';
    return $o;
}
