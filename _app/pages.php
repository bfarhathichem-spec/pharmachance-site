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
        $l = preg_replace_callback('/\{\{([a-z_]+)\}\}/', function ($m) use ($v, &$vide) { $x = $v[$m[1]] ?? ''; if ($x === '') $vide = true; return $x; }, $l);
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
