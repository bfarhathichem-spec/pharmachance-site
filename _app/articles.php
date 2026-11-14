<?php
/* Articles : données et mise en forme du texte (format simple, proche du Markdown) */

function articles(bool $tous = false): array {
    $l = charger('articles', []);
    if ($tous) return trier($l);
    return array_values(array_filter(trier($l), fn($a) => !empty($a['publie'])));
}

function temps_lecture(string $txt): int { return max(1, (int)round(str_word_count(strip_tags($txt), 0, 'àâäéèêëîïôöùûüçœÀÂÉÈÊÎÔÙÛÇ') / 200)); }

function date_longue(string $iso): string {
    $m = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $t = strtotime($iso) ?: time();
    return (int)date('j', $t) . ' ' . $m[(int)date('n', $t) - 1] . ' ' . date('Y', $t);
}

function ancre(string $s): string { return slugify($s) ?: 'section'; }

/* gras, liens ; le texte est échappé avant */
function md_ligne(string $s): string {
    $s = h($s);
    $s = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $s);
    $s = preg_replace_callback('/\[([^\]]+)\]\(([^)\s]+)\)/u', function ($m) {
        $href = html_entity_decode($m[2], ENT_QUOTES);
        if (!preg_match('#^(https?://|/|mailto:|tel:|\#)#', $href)) $href = url($href);
        $ext = preg_match('#^https?://#', $href) ? ' rel="noopener" target="_blank"' : '';
        return '<a class="lien" href="' . h($href) . '"' . $ext . '>' . $m[1] . '</a>';
    }, $s);
    return $s;
}

/* Rend le contenu ; renvoie [html, sommaire] */
function md(string $txt): array {
    $lignes = preg_split('/\R/u', trim($txt)); $o = ''; $som = []; $i = 0; $n = count($lignes);
    while ($i < $n) {
        $l = rtrim($lignes[$i]);
        if (trim($l) === '') { $i++; continue; }
        if (preg_match('/^(#{2,3})\s+(.+)$/u', $l, $m)) {
            $id = ancre($m[2]); $niv = strlen($m[1]);
            if ($niv === 2) $som[] = ['id' => $id, 't' => $m[2]];
            $o .= "<h$niv id=\"" . h($id) . "\">" . md_ligne($m[2]) . "</h$niv>"; $i++; continue;
        }
        if (preg_match('/^!\[([^\]]*)\]\(([^)\s]+)\)$/u', trim($l), $m)) {
            $o .= '<figure class="fig rv"><img src="' . h(media($m[2])) . '" alt="' . h($m[1]) . '" loading="lazy" decoding="async"><figcaption>' . h($m[1]) . '</figcaption></figure>'; $i++; continue;
        }
        if (str_starts_with(trim($l), '|')) {
            $rows = [];
            while ($i < $n && str_starts_with(trim($lignes[$i]), '|')) { $rows[] = trim($lignes[$i]); $i++; }
            $cells = fn($r) => array_map('trim', explode('|', trim($r, " |")));
            $o .= '<div class="tableau"><table><thead><tr>' . implode('', array_map(fn($c) => '<th>' . md_ligne($c) . '</th>', $cells($rows[0]))) . '</tr></thead><tbody>';
            foreach (array_slice($rows, 2) as $r) $o .= '<tr>' . implode('', array_map(fn($c) => '<td>' . md_ligne($c) . '</td>', $cells($r))) . '</tr>';
            $o .= '</tbody></table></div>'; continue;
        }
        if (preg_match('/^\s*[-*]\s+/u', $l) || preg_match('/^\s*\d+\.\s+/u', $l)) {
            $ord = (bool)preg_match('/^\s*\d+\./', $l); $items = [];
            while ($i < $n && preg_match($ord ? '/^\s*\d+\.\s+(.*)$/u' : '/^\s*[-*]\s+(.*)$/u', $lignes[$i], $m)) { $items[] = str_starts_with($m[1], '[ ] ') ? '<li class="coche">' . md_ligne(substr($m[1], 4)) . '</li>' : '<li>' . md_ligne($m[1]) . '</li>'; $i++; }
            $o .= ($ord ? '<ol>' : '<ul>') . implode('', $items) . ($ord ? '</ol>' : '</ul>'); continue;
        }
        if (str_starts_with($l, '>')) {
            $q = [];
            while ($i < $n && str_starts_with($lignes[$i], '>')) { $q[] = ltrim(substr($lignes[$i], 1)); $i++; }
            $o .= '<aside class="encadre">' . md_ligne(implode(' ', $q)) . '</aside>'; continue;
        }
        $p = [];
        while ($i < $n && trim($lignes[$i]) !== '' && !preg_match('/^(#{2,3}\s|\||>|\s*[-*]\s|\s*\d+\.\s|!\[)/u', $lignes[$i])) { $p[] = trim($lignes[$i]); $i++; }
        $o .= '<p>' . md_ligne(implode(' ', $p)) . '</p>';
    }
    return [$o, $som];
}

/* FAQ saisie dans l'admin : blocs séparés par une ligne vide, 1re ligne = question */
function faq_depuis_texte(string $t): array {
    $o = [];
    foreach (preg_split('/\R\s*\R/u', trim($t)) as $b) {
        $l = preg_split('/\R/u', trim($b)); $q = trim(array_shift($l) ?? ''); $r = trim(implode(' ', $l));
        if ($q !== '' && $r !== '') $o[] = ['q' => $q, 'r' => $r];
    }
    return $o;
}
function faq_vers_texte(array $f): string { return implode("\n\n", array_map(fn($x) => $x['q'] . "\n" . $x['r'], $f)); }

function carte_article(array $a, string $cls = ''): string {
    return '<a class="art-carte rv ' . h($cls) . '" href="' . url('articles/' . $a['slug']) . '">'
        . '<span class="im"><img src="' . h(media(miniature($a['image']))) . '" alt="' . h($a['image_alt'] ?? $a['titre']) . '" loading="lazy" decoding="async"></span>'
        . '<span class="txt"><small>' . h($a['categorie'] ?? 'Conseils') . ' · ' . temps_lecture($a['contenu']) . ' min de lecture</small>'
        . '<b>' . h($a['titre']) . '</b><span class="res">' . h($a['resume']) . '</span></span></a>';
}
