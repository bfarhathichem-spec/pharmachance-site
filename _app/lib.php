<?php
declare(strict_types=1);

/* ---------- Chemins ---------- */
define('APP_DIR', __DIR__);
/* En production (déploiement Git OVH), le code est dans <site>/_app et les contenus
   du client dans un dossier frère du site, jamais touché par les déploiements. */
define('EN_PROD', basename(__DIR__) === '_app');
define('WWW_DIR', EN_PROD ? dirname(__DIR__) : (realpath(__DIR__ . '/../www') ?: __DIR__ . '/../www'));
define('DATA_DIR', EN_PROD ? dirname(WWW_DIR) . '/pharmachance-donnees' : (realpath(__DIR__ . '/../data') ?: __DIR__ . '/../data'));
define('SEED_DIR', EN_PROD ? __DIR__ . '/seed' : DATA_DIR);
define('UPLOAD_DIR', DATA_DIR . '/uploads');
define('ORIG_DIR', DATA_DIR . '/originaux');
define('FICHIERS_DEMANDES', DATA_DIR . '/demandes-fichiers');

mb_internal_encoding('UTF-8');
date_default_timezone_set('Africa/Tunis');

/* ---------- Utilitaires ---------- */
function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function base(): string {
    static $b = null;
    if ($b === null) {
        $b = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        if (str_ends_with($b, '/admin')) $b = substr($b, 0, -6);
    }
    return $b;
}
function url(string $chemin = ''): string { return base() . '/' . ltrim($chemin, '/'); }
function media(string $chemin, string $sorte = ''): string {
    if ($chemin === '') return '';
    if (empty($GLOBALS['pc_marqueurs'])) return url($chemin);   // admin, sitemap : chemin brut
    $ori = $GLOBALS['pc_miniatures'][$chemin] ?? null;           // miniature calculée depuis une photo d'origine
    $v = $sorte !== '' ? $sorte : ($ori !== null ? 'm' : 'f');
    return '__PCIMG:' . $v . ':' . rtrim(strtr(base64_encode($ori ?? $chemin), '+/', '-_'), '=') . '__';
}

function slugify(string $s): string {
    $s = mb_strtolower(trim($s));
    $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
    if ($t !== false) $s = $t;
    $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';
    return trim($s, '-') ?: 'element';
}
function lignes(string $s): array { return array_values(array_filter(array_map('trim', preg_split('/\R/u', $s) ?: []), fn($x) => $x !== '')); }
function paragraphes(string $s): string {
    $out = '';
    foreach (preg_split('/\R{2,}/u', trim($s)) ?: [] as $p) if (trim($p) !== '') $out .= '<p>' . nl2br(h(trim($p))) . '</p>';
    return $out;
}

/* ---------- Stockage JSON (verrouillé) ---------- */
function charger(string $nom, $defaut = []) {
    $f = DATA_DIR . "/$nom.json";
    if (!is_file($f) && SEED_DIR !== DATA_DIR && is_file(SEED_DIR . "/$nom.json")) { // premier lancement : contenus d'origine
        if (!is_dir(DATA_DIR)) @mkdir(DATA_DIR, 0750, true);
        @copy(SEED_DIR . "/$nom.json", $f);
    }
    if (!is_file($f)) return $defaut;
    $fh = fopen($f, 'r'); flock($fh, LOCK_SH);
    $txt = stream_get_contents($fh); flock($fh, LOCK_UN); fclose($fh);
    $d = json_decode((string)$txt, true);
    return is_array($d) ? $d : $defaut;
}
function enregistrer(string $nom, $donnees): void {
    if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0750, true);
    $f = DATA_DIR . "/$nom.json";
    if (is_file($f)) { @copy($f, DATA_DIR . "/$nom.bak.json"); }
    $tmp = $f . '.' . bin2hex(random_bytes(4)) . '.tmp';
    file_put_contents($tmp, json_encode($donnees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    rename($tmp, $f);
}
function trier(array $l): array { usort($l, fn($a, $b) => ($a['ordre'] ?? 0) <=> ($b['ordre'] ?? 0)); return $l; }
function publies(array $l): array { return array_values(array_filter(trier($l), fn($x) => !empty($x['publie']))); }
function trouver(array $l, string $champ, string $val): ?array { foreach ($l as $x) if (($x[$champ] ?? null) === $val) return $x; return null; }

function reglages(): array { static $r = null; return $r ??= charger('reglages', []); }
function services(bool $tous = false): array { $s = charger('services'); return $tous ? trier($s) : publies($s); }
/* Réalisations : sans précision, les pharmacies seulement ; les autres secteurs ont leurs propres pages */
function realisations(bool $tous = false): array { $s = charger('realisations'); return $tous ? trier($s) : array_values(array_filter(publies($s), fn($x) => ($x['secteur'] ?? '') === '')); }
function realisations_autres(string $secteur = ''): array { return array_values(array_filter(publies(charger('realisations')), fn($x) => ($x['secteur'] ?? '') !== '' && ($secteur === '' || $x['secteur'] === $secteur))); }
function secteurs(bool $tous = false): array { $s = charger('secteurs', []); return $tous ? trier($s) : publies($s); }
/* secteurs en ligne qui ont au moins un projet publié */
function secteurs_actifs(): array { $a = realisations_autres(); return array_values(array_filter(secteurs(), fn($s) => (bool)array_filter($a, fn($x) => $x['secteur'] === $s['slug']))); }
function produits(bool $tous = false, string $type = ''): array { $s = charger('produits', []); $l = $tous ? trier($s) : publies($s); return $type === '' ? $l : array_values(array_filter($l, fn($x) => ($x['type'] ?? '') === $type)); }
function types_produits(bool $tous = false): array { $s = charger('types_produits', []); return $tous ? trier($s) : publies($s); }
function lien_realisation(array $r): string { return ($r['secteur'] ?? '') === '' ? 'realisations/' . $r['slug'] : 'autres-secteurs/' . $r['secteur'] . '/' . $r['slug']; }
function themes(bool $tous = false): array { $s = charger('themes'); return $tous ? trier($s) : publies($s); }
function temoignages(bool $tous = false): array { $s = charger('temoignages'); return $tous ? trier($s) : publies($s); }
function nom_service(string $cle): string { foreach (charger('services') as $s) if ($s['cle'] === $cle) return $s['nom']; return $cle; }
function slug_service(string $cle): string { foreach (charger('services') as $s) if ($s['cle'] === $cle) return $s['slug']; return $cle; }

/* ---------- Session et sécurité ---------- */
function demarrer_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name('pc_session');
    session_set_cookie_params(['lifetime' => 0, 'path' => base() . '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Strict']);
    session_start();
}
function csrf(): string { demarrer_session(); return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function champ_csrf(): string { return '<input type="hidden" name="csrf" value="' . h(csrf()) . '">'; }
function verifier_csrf(): void {
    demarrer_session();
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) { http_response_code(400); exit('Session expirée. Rechargez la page et recommencez.'); }
}
function entetes_securite(bool $admin = false): void {
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if ($admin) { header('Cache-Control: no-store'); header('X-Robots-Tag: noindex, nofollow'); }
}

/* ---------- Comptes ---------- */
function utilisateurs(): array { return charger('comptes', []); }
function utilisateur(): ?array {
    demarrer_session();
    if (empty($_SESSION['uid'])) return null;
    if (($_SESSION['vu'] ?? 0) < time() - 7200) { session_unset(); return null; } // 2 h d'inactivité
    $_SESSION['vu'] = time();
    return trouver(utilisateurs(), 'id', (string)$_SESSION['uid']);
}
function tentatives_ok(string $ip): bool {
    $t = charger('tentatives', []);
    $t = array_filter($t, fn($x) => $x['t'] > time() - 900);
    $n = count(array_filter($t, fn($x) => $x['ip'] === $ip));
    return $n < 6;
}
function noter_echec(string $ip): void {
    $t = array_values(array_filter(charger('tentatives', []), fn($x) => $x['t'] > time() - 900));
    $t[] = ['ip' => $ip, 't' => time()];
    enregistrer('tentatives', $t);
}

/* ---------- Images ---------- */
function traiter_image(array $f, string $prefixe = 'img', int $max = 2200): ?string {
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) return null;
    if ($f['size'] > 20 * 1024 * 1024) return null;
    $info = @getimagesize($f['tmp_name']);
    if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) return null;
    $src = match ($info[2]) { IMAGETYPE_JPEG => @imagecreatefromjpeg($f['tmp_name']), IMAGETYPE_PNG => @imagecreatefrompng($f['tmp_name']), IMAGETYPE_WEBP => @imagecreatefromwebp($f['tmp_name']) };
    if (!$src) return null;
    // orientation des photos de téléphone
    if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $o = (@exif_read_data($f['tmp_name'])['Orientation'] ?? 1);
        if ($o == 3) $src = imagerotate($src, 180, 0); elseif ($o == 6) $src = imagerotate($src, -90, 0); elseif ($o == 8) $src = imagerotate($src, 90, 0);
    }
    $w = imagesx($src); $hh = imagesy($src); $r = min(1, $max / max($w, $hh));
    $nw = (int)round($w * $r); $nh = (int)round($hh * $r);
    $dst = imagecreatetruecolor($nw, $nh);
    imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $hh);
    $dossier = date('Y/m');
    @mkdir(UPLOAD_DIR . '/' . $dossier, 0755, true);
    $nom = slugify($prefixe) . '-' . bin2hex(random_bytes(4));
    imageinterlace($dst, true);
    imagejpeg($dst, UPLOAD_DIR . "/$dossier/$nom.jpg", 84);
    if (function_exists('imagewebp')) { // miniature légère pour les listes et galeries
        $r2 = min(1, 900 / max($nw, $nh)); $mw = (int)round($nw * $r2); $mh = (int)round($nh * $r2);
        $min = imagecreatetruecolor($mw, $mh); imagecopyresampled($min, $dst, 0, 0, 0, 0, $mw, $mh, $nw, $nh);
        @mkdir(UPLOAD_DIR . "/min/$dossier", 0755, true); imagewebp($min, UPLOAD_DIR . "/min/$dossier/$nom.webp", 78); imagedestroy($min);
    }
    @mkdir(ORIG_DIR . '/' . $dossier, 0750, true);  // l'original est conservé hors du site
    @move_uploaded_file($f['tmp_name'], ORIG_DIR . "/$dossier/$nom." . image_type_to_extension($info[2], false));
    imagedestroy($src); imagedestroy($dst);
    return "uploads/$dossier/$nom.jpg";
}
function fichiers_multiples(string $champ): array {
    $out = [];
    if (empty($_FILES[$champ]['name']) || !is_array($_FILES[$champ]['name'])) return $out;
    foreach ($_FILES[$champ]['name'] as $i => $n) {
        $out[] = ['name' => $n, 'type' => $_FILES[$champ]['type'][$i], 'tmp_name' => $_FILES[$champ]['tmp_name'][$i], 'error' => $_FILES[$champ]['error'][$i], 'size' => $_FILES[$champ]['size'][$i]];
    }
    return $out;
}

/* ---------- Rendu ---------- */
function rendre(string $gabarit, array $v = []): void {
    $GLOBALS['pc_marqueurs'] = function_exists('finaliser_page');
    extract($v);
    ob_start();
    require APP_DIR . "/templates/$gabarit.php";
    $contenu = ob_get_clean();
    if (function_exists('ordonner_blocs')) $contenu = ordonner_blocs($gabarit, $contenu);
    ob_start();
    require APP_DIR . '/templates/layout.php';
    $page = ob_get_clean();
    echo function_exists('finaliser_page') ? finaliser_page($page, $gabarit) : $page;
}
function lien_wa(string $texte = "Bonjour, je souhaite parler d'un projet de pharmacie."): string {
    return 'https://wa.me/' . preg_replace('/\D/', '', reglages()['whatsapp'] ?? '') . '?text=' . rawurlencode($texte);
}
function tel_lien(): string { return 'tel:' . preg_replace('/[^\d+]/', '', reglages()['telephone'] ?? ''); }
