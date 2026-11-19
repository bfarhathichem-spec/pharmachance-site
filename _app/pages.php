<?php
/* Pages de contenu (légales) éditables dans l'admin */
function pages_site(): array { return trier(charger('pages', [])); }

function nom_legal(): string {
    $r = reglages(); $n = trim($r['raison_sociale'] ?? '') ?: 'PharmaChance';
    return $n;
}

/* remplace {{cle}} par les réglages ; une ligne dont une valeur est vide est retirée */
function jetons(string $txt): string {
    $r = reglages();
    $v = ['nom_legal' => nom_legal(), 'site' => 'pharmachance.com', 'annees' => $r['annees'] ?? ''];
    foreach ($r as $k => $x) if (is_string($x) && !isset($v[$k])) $v[$k] = trim($x);
    $o = [];
    foreach (preg_split('/\R/u', $txt) as $l) {
        $vide = false;
        $l = preg_replace_callback('/\{\{(\??)([a-z_]+)\}\}/', function ($m) use ($v, &$vide) { $x = $v[$m[2]] ?? ''; if ($x === '') $vide = true; return $m[1] === '?' ? '' : $x; }, $l);
        if (!$vide) $o[] = $l;
    }
    return implode("\n", $o);
}

/* informations légales manquantes, pour l'alerte de l'admin */
function legal_manquant(): array {
    $r = reglages(); $m = [];
    foreach (['raison_sociale' => 'raison sociale', 'forme_juridique' => 'forme juridique', 'identifiant_unique' => 'identifiant unique (RNE)', 'matricule_fiscal' => 'matricule fiscal', 'directeur_publication' => 'directeur de la publication'] as $k => $l)
        if (trim($r[$k] ?? '') === '') $m[] = $l;
    return $m;
}

/* Chiffres par défaut du bandeau de l'accueil, calculés depuis le contenu du site. */
function chiffres_auto(): array
{
    $annees = trim((string)(reglages()['annees'] ?? ''));
    $themes = array_filter(themes(), fn($t) => !empty($t['images']));
    $c = [[count(realisations()), 'réalisations à découvrir'], [count(services()), 'expertises réunies'], [count($themes), 'thèmes de vitrines'], [1, 'seul interlocuteur']];
    if (ctype_digit($annees)) { array_unshift($c, [(int)$annees, "ans d'expérience"]); array_pop($c); }
    return $c;
}

/* Identifiant Google Analytics : réglage de l'admin, ou celui du site tant que le réglage n'a jamais été enregistré */
function ga_id(): string { $r = reglages(); return array_key_exists('ga_id', $r) ? trim((string)$r['ga_id']) : 'G-YC1P4DS5KQ'; }

/* Textes légaux complétés automatiquement quand la mesure d'audience est activée */
function ajuster_legal(string $slug, string $txt): string {
    if (ga_id() === '' || str_contains($txt, 'Google Analytics')) return $txt;
    if ($slug === 'cookies') {
        $txt = str_replace("Ce site n'utilise aucun cookie publicitaire, aucun outil de mesure d'audience tiers et aucun bouton de réseau social qui vous suivrait. Il n'y a donc rien à accepter.",
            "Ce site n'utilise aucun cookie publicitaire et aucun bouton de réseau social qui vous suivrait. Il mesure sa fréquentation avec Google Analytics, uniquement si vous l'acceptez dans le bandeau affiché à votre première visite.", $txt);
        $txt = str_replace('## Le seul cookie utilisé', '## Les cookies utilisés', $txt);
        $txt = preg_replace('/(\| Cookie de session \|[^\n]*\n)/u', "$1| _ga et _ga_* (Google Analytics) | Mesurer la fréquentation du site (pages vues, durée, type d'appareil, ville approximative), de façon statistique. Déposés seulement si vous acceptez la mesure d'audience | 13 mois |\n", $txt, 1) ?? $txt;
        $txt = str_replace("Ce cookie est strictement nécessaire au fonctionnement du site.", "Le cookie de session est strictement nécessaire au fonctionnement du site.", $txt);
        $txt .= "\n\n## Mesure d'audience (Google Analytics)\n\nSi vous l'acceptez, Google Analytics enregistre votre visite de façon statistique : pages consultées, durée, type d'appareil, ville approximative. L'adresse IP n'est pas conservée par Google Analytics 4. Les données sont traitées par Google Ireland Limited et peuvent être transférées hors de Tunisie.\n\nVous pouvez changer d'avis à tout moment avec le lien « Gérer les cookies » en bas de chaque page : si vous refusez, aucun cookie de mesure n'est déposé.";
    }
    if ($slug === 'confidentialite') {
        $txt .= "\n\n## Mesure d'audience\n\nAvec votre accord, donné dans le bandeau des cookies, le site mesure sa fréquentation avec Google Analytics (Google Ireland Limited). Ces statistiques servent uniquement à améliorer le site. Les cookies de mesure sont conservés 13 mois au plus. Vous pouvez retirer votre accord à tout moment avec le lien « Gérer les cookies » en bas de chaque page. Le détail figure dans la page [cookies](cookies).";
    }
    return $txt;
}

/* Projets de façade : une photo avant et une photo après sont attendues */
function facade_incomplete(array $r): bool {
    return in_array('facades', $r['services'] ?? [], true) && (empty($r['avant']) || empty($r['apres']));
}

/* Corrections de contenu appliquées une seule fois aux données du site en ligne */
function migrations(): void {
    $fait = charger('migrations', []);
    if (in_array('magenta-autres-secteurs-2', $fait, true)) return;
    $sec = charger('secteurs', []);
    if (!trouver($sec, 'slug', 'commerces')) { $sec[] = ['slug' => 'commerces', 'nom' => 'Commerces et boutiques', 'intro' => '', 'ordre' => count($sec) + 1, 'publie' => true]; enregistrer('secteurs', $sec); }
    $l = charger('realisations');
    $trouve = false;
    foreach ($l as &$x) if (($x['image'] ?? '') === 'img/facade-magenta.jpg') { $trouve = true; if (($x['secteur'] ?? '') === '') { $x['secteur'] = 'commerces'; $x['en_avant'] = false; } }
    unset($x);
    if (!$trouve) $l[] = ['id' => 'r03', 'slug' => 'boutique-magenta', 'titre' => 'Boutique Magenta : entrée et habillage floral', 'ville' => '', 'annee' => '', 'surface' => '', 'services' => ['facades'],
        'description' => 'Une entrée de boutique mise en scène avec une arche florale et des oriflammes assortis.', 'interventions' => ["Habillage de l'entrée", 'Décor floral', 'Signalétique extérieure'],
        'image' => 'img/facade-magenta.jpg', 'cadrage' => '50% 40%', 'galerie' => ['img/facade-magenta.jpg'], 'avant' => '', 'apres' => '', 'en_avant' => false, 'publie' => true, 'ordre' => count($l) + 1, 'secteur' => 'commerces'];
    enregistrer('realisations', $l);
    $fait[] = 'magenta-autres-secteurs-2'; enregistrer('migrations', $fait);
}
