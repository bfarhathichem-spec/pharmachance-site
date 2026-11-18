<?php
declare(strict_types=1);
/* Édition visuelle du site.
   Les modifications faites directement sur les pages sont rangées dans data/surcharges.json :
   - textes : texte d'origine => nouveau texte (un texte identique change partout sur le site)
   - images : chemin d'origine => nouvelle photo
   - blocs  : par gabarit de page, ordre des sections et sections masquées */

function surcharges(?array $nouveau = null): array {
    static $s = null;
    if ($nouveau !== null) $s = $nouveau;
    if ($s === null) {
        $s = charger('surcharges', []);
        $s += ['textes' => [], 'images' => [], 'images_p' => [], 'blocs' => []];
    }
    return $s;
}

/* Une session n'est ouverte sur le site public que si la personne s'est déjà connectée à l'admin. */
function admin_connecte(): bool {
    static $c = null;
    if ($c === null) $c = !empty($_COOKIE['pc_session']) && utilisateur() !== null;
    return $c;
}
function en_edition(): bool {
    static $e = null;
    if ($e !== null) return $e;
    $e = false;
    if (empty($_COOKIE['pc_session'])) return $e;
    $voulu = ($_COOKIE['pc_edition'] ?? '') === '1';
    if (isset($_GET['edition'])) {
        $voulu = $_GET['edition'] === '1';
        if (!headers_sent()) setcookie('pc_edition', $voulu ? '1' : '0', ['path' => base() . '/', 'samesite' => 'Lax', 'httponly' => false, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    }
    return $e = $voulu && admin_connecte();
}

function cle_texte(string $s): string {
    return trim((string)preg_replace('/[\s\x{00A0}]+/u', ' ', html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}
/* Texte surchargé (avant découpage en mots animés, par exemple) */
function tr(string $s): string {
    $t = surcharges()['textes'];
    $k = cle_texte($s);
    return array_key_exists($k, $t) ? (string)$t[$k] : $s;
}

/* Remplace les textes du corps de page selon les surcharges. */
function appliquer_textes(string $html): string {
    $t = surcharges()['textes'];
    if (!$t) return $html;
    $debut = stripos($html, '<body');
    if ($debut === false) return $html;
    $tete = substr($html, 0, $debut); $corps = substr($html, $debut);
    $corps = preg_replace_callback('~(<script\b[^>]*>.*?</script>|<style\b[^>]*>.*?</style>|<!--.*?-->)|(?<=>)([^<]+)(?=<)~s', function ($m) use ($t) {
        if (($m[1] ?? '') !== '') return $m[0];
        $seg = $m[2];
        $k = cle_texte($seg);
        if ($k === '' || !array_key_exists($k, $t)) return $seg;
        preg_match('/^(\s*)/u', $seg, $a); preg_match('/(\s*)$/u', $seg, $b);
        return $a[1] . h((string)$t[$k]) . $b[1];
    }, $corps) ?? $corps;
    return $tete . $corps;
}

/* ---------- Blocs : sections de premier niveau d'une page ---------- */
function decouper_blocs(string $html): ?array {
    $vides = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'source', 'track', 'wbr', 'param'];
    $n = strlen($html); $i = 0; $prof = 0; $deb = 0; $tag = ''; $blocs = []; $dernier = 0;
    while ($i < $n && preg_match('~<!--.*?-->|<(/?)([a-zA-Z][a-zA-Z0-9-]*)\b[^>]*?(/?)>~s', $html, $m, PREG_OFFSET_CAPTURE, $i)) {
        $pos = $m[0][1]; $txt = $m[0][0]; $fin = $pos + strlen($txt);
        if ($prof === 0 && trim(substr($html, $dernier, $pos - $dernier)) !== '') return null; // texte libre au premier niveau
        if (str_starts_with($txt, '<!--')) { if ($prof === 0) $dernier = $fin; $i = $fin; continue; }
        $ferme = $m[1][0] === '/'; $nom = strtolower($m[2][0]); $auto = $m[3][0] === '/' || in_array($nom, $vides, true);
        if (!$ferme && in_array($nom, ['script', 'style'], true)) {
            $f = stripos($html, '</' . $nom, $fin); if ($f === false) return null;
            $fin = strpos($html, '>', $f) + 1;
            if ($prof === 0) { $blocs[] = [$pos, $fin, $nom]; $dernier = $fin; }
            $i = $fin; continue;
        }
        if ($ferme) {
            $prof--;
            if ($prof < 0) return null;
            if ($prof === 0) { $blocs[] = [$deb, $fin, $tag]; $dernier = $fin; }
        } elseif (!$auto) {
            if ($prof === 0) { $deb = $pos; $tag = $nom; }
            $prof++;
        } elseif ($prof === 0) { $blocs[] = [$pos, $fin, $nom]; $dernier = $fin; }
        $i = $fin;
    }
    if ($prof !== 0 || trim(substr($html, $dernier)) !== '') return null;
    $out = []; $vus = [];
    foreach ($blocs as [$a, $b, $t]) {
        $code = substr($html, $a, $b - $a);
        preg_match('~^<[a-z0-9-]+\b[^>]*?\bclass="([^"]*)"~i', $code, $c);
        $base = $t . (isset($c[1]) && trim($c[1]) !== '' ? '.' . preg_split('/\s+/', trim($c[1]))[0] : '');
        $base = strtolower((string)preg_replace('/[^a-z0-9.-]/i', '', $base));
        if (str_ends_with($base, '.pc-ui')) { $out[] = ['id' => '', 'code' => $code, 'nom' => '', 'fixe' => true]; continue; } // outils d'édition : jamais déplacés
        $vus[$base] = ($vus[$base] ?? 0) + 1;
        $id = $vus[$base] > 1 ? $base . '-' . $vus[$base] : $base;
        $out[] = ['id' => $id, 'code' => $code, 'nom' => nom_bloc($code, $t)];
    }
    return $out;
}
function nom_bloc(string $code, string $tag): string {
    if (preg_match('~<h[12]\b[^>]*>(.*?)</h[12]>~si', $code, $m)) { $t = cle_texte(strip_tags($m[1])); if ($t !== '') return mb_strimwidth($t, 0, 48, '…'); }
    $noms = ['xh-visuel' => 'Grande photo', 'xn' => 'Bandeau des chiffres', 'xm' => 'Mots qui défilent', 'xa' => 'Manifeste et photos', 'phrase-projet' => 'Grande phrase « prochain projet »', 'grand-visuel' => 'Grande photo', 'def' => 'Paragraphe d\'introduction', 'cta' => 'Bloc contact'];
    if (preg_match('~^<[a-z0-9-]+\b[^>]*?\bclass="([^"]*)"~i', $code, $c)) foreach (preg_split('/\s+/', trim($c[1])) as $cl) if (isset($noms[$cl])) return $noms[$cl];
    if (preg_match('~^<div style="height~', $code)) return 'Espace vide';
    $t = cle_texte(strip_tags($code));
    return $t !== '' ? mb_strimwidth($t, 0, 48, '…') : 'Bloc';
}
function ordonner_blocs(string $page, string $html): string {
    $blocs = decouper_blocs($html);
    if (!$blocs) return resoudre_images($html, 'contenu');
    $conf = surcharges()['blocs'][$page] ?? [];
    $ordre = array_values((array)($conf['ordre'] ?? [])); $masques = (array)($conf['masques'] ?? []);
    $fixes = []; foreach ($blocs as $k => $b) if (!empty($b['fixe'])) $fixes[$k] = $b['code'];
    $blocs = array_values(array_filter($blocs, fn($b) => empty($b['fixe'])));
    $parId = []; foreach ($blocs as $b) $parId[$b['id']] = $b;
    $res = [];
    foreach ($ordre as $id) if (isset($parId[$id])) $res[] = $id;
    foreach ($blocs as $k => $b) if (!in_array($b['id'], $res, true)) { // nouveau bloc : placé après son voisin d'origine
        $avant = $k > 0 ? $blocs[$k - 1]['id'] : null; $p = $avant !== null ? array_search($avant, $res, true) : -1;
        array_splice($res, $p === false ? count($res) : $p + 1, 0, [$b['id']]);
    }
    $edition = en_edition(); $o = '';
    foreach ($fixes as $k => $code) array_splice($res, min($k, count($res)), 0, ['#fixe' . $k]);
    foreach ($res as $id) {
        if (str_starts_with($id, '#fixe')) { $o .= resoudre_images($fixes[(int)substr($id, 5)], 'outils') . "\n"; continue; }
        $b = $parId[$id]; $cache = in_array($id, $masques, true);
        if ($cache && !$edition) continue;
        $code = resoudre_images($b['code'], $id);
        if ($edition) $code = (string)preg_replace('~^<([a-z0-9-]+)~i', '<$1 data-bloc="' . h($id) . '" data-bloc-nom="' . h($b['nom']) . '"' . ($cache ? ' data-bloc-masque' : ''), $code, 1);
        $o .= $code . "\n";
    }
    return $o;
}

/* Fin de rendu : textes surchargés, puis outils d'édition pour les personnes connectées. */
/* Adresse de la page, pour rattacher chaque photo modifiée à son emplacement exact */
function page_cle(): string {
    $c = '/' . trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    $b = base(); if ($b !== '' && str_starts_with($c, $b)) $c = '/' . trim(substr($c, strlen($b)), '/');
    return strtolower($c);
}
/* Chaque photo est indépendante : sa clé est la page, la section qui la contient, la photo d'origine
   et son rang dans cette section. Déplacer une section ne change donc pas les photos d'emplacement. */
function resoudre_images(string $html, string $portee, bool $tete = false): string {
    static $ancienPris = [];
    $s = surcharges(); $inst = (array)$s['images_p']; $ancien = (array)$s['images'];
    $pg = page_cle(); $corps = $tete ? stripos($html, '<body') : 0; $cpt = []; $ed = en_edition();
    return (string)preg_replace_callback('/__PCIMG:([fmd]):([A-Za-z0-9_-]+)__/', function ($m) use ($inst, $ancien, $pg, $portee, $corps, &$cpt, $ed, &$ancienPris) {
        $v = $m[1][0]; $pos = $m[0][1];
        $ori = (string)base64_decode(strtr($m[2][0], '-_', '+/'));
        if ($corps === false || $pos < $corps) $n = -1;            // en-tête (aperçu de partage) : photo d'origine
        elseif ($v === 'd') $n = $cpt[$ori] ?? 0;                 // lien d'agrandissement : même rang que la vignette qui suit
        else { $n = $cpt[$ori] ?? 0; $cpt[$ori] = $n + 1; }
        $cle = "$pg|$portee|$ori|$n";
        if (isset($inst[$cle])) $nouv = $inst[$cle];
        elseif ($n >= 0 && isset($ancien[$ori]) && (empty($ancienPris[$ori]) || $ancienPris[$ori] === "$portee|$n")) { $nouv = $ancien[$ori]; $ancienPris[$ori] = "$portee|$n"; } // ancien réglage : premier emplacement seulement
        else $nouv = $ori;
        $chemin = $v === 'm' ? min_de($nouv) : $nouv;
        return url($chemin) . ($ed && $n >= 0 ? '#o=' . rawurlencode("$portee|$ori|$n") : '');
    }, $html, -1, $c, PREG_OFFSET_CAPTURE);
}

function finaliser_page(string $html, string $page): string {
    $html = resoudre_images($html, 'cadre', true);
    $html = appliquer_textes($html);
    if (!admin_connecte()) return $html;
    if (!headers_sent()) header('Cache-Control: private, no-store');
    $v = fn($f) => @filemtime(WWW_DIR . '/assets/' . $f);
    $conf = ['csrf' => csrf(), 'page' => $page, 'chemin' => page_cle(), 'edition' => en_edition(), 'admin' => url('admin/'),
        'inverse' => (object)array_flip(array_map('strval', array_filter(surcharges()['textes'], fn($x) => is_string($x) || is_numeric($x))))];
    $outils = '<link rel="stylesheet" href="' . url('assets/edition.css') . '?v=' . $v('edition.css') . '">'
        . '<script id="pc-ed-conf" type="application/json">' . json_encode($conf, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) . '</script>'
        . '<script src="' . url('assets/edition.js') . '?v=' . $v('edition.js') . '" defer></script>';
    if (en_edition()) $html = preg_replace('~<html\b([^>]*)>~', '<html$1 class="pc-ed">', $html, 1) ?? $html;
    return (string)preg_replace('~</body>~i', $outils . '</body>', $html, 1);
}
