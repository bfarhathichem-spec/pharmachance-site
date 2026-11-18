<?php
declare(strict_types=1);

const WA_SVG = '<svg width="30" height="30" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2zm0 18.2a8.2 8.2 0 01-4.2-1.15l-.3-.18-3 .78.8-2.9-.2-.3A8.2 8.2 0 1112 20.2zm4.5-6.1c-.25-.12-1.46-.72-1.69-.8s-.39-.12-.55.12-.63.8-.78.97-.29.18-.53.06a6.7 6.7 0 01-3.34-2.92c-.25-.43.25-.4.72-1.34a.45.45 0 00-.02-.43c-.06-.12-.55-1.33-.76-1.82s-.4-.41-.55-.42h-.47a.9.9 0 00-.65.3 2.7 2.7 0 00-.85 2.03 4.7 4.7 0 001 2.5 10.8 10.8 0 004.13 3.65c1.54.66 2.14.72 2.91.6a2.5 2.5 0 001.64-1.16 2 2 0 00.14-1.15c-.06-.1-.22-.16-.47-.28z" fill="currentColor"/></svg>';

function carte(array $r, string $cls = ''): string {
    $cats = h(implode(' ', $r['services'] ?? []));
    $ville = trim($r['ville'] ?? '') !== '' ? '<span>' . h($r['ville']) . '</span>' : '';
    $item = function_exists('en_edition') && en_edition() ? ' data-item="' . h((string)($r['id'] ?? '')) . '"' : '';
    return '<a class="carte ' . h($cls) . '" href="' . url('realisations/' . $r['slug']) . '" data-cat="' . $cats . '"' . $item . '>'
        . '<img src="' . h(media(miniature($r['image']))) . '" alt="' . h($r['titre'] . ' : réalisation PharmaChance') . '" loading="lazy" decoding="async" style="object-position:' . h($r['cadrage'] ?? '50% 50%') . '">'
        . '<span class="cap"><b>' . h($r['titre']) . '</b>' . $ville . '</span></a>';
}

function avap(string $avant, string $apres): string {
    if ($avant === '' || $apres === '') return '';
    return '<figure class="aa-bloc"><div class="aa"><img src="' . h(media($avant)) . '" alt="Avant les travaux" draggable="false"><img class="apres" src="' . h(media($apres)) . '" alt="Après les travaux" draggable="false">'
        . '<span class="barre"></span><span class="poignee" role="slider" tabindex="0" aria-label="Comparer avant et après" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50"><svg width="22" height="14" viewBox="0 0 22 14" aria-hidden="true"><path d="M7 1L1 7l6 6M15 1l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>'
        . '<span class="tag av">Avant</span><span class="tag ap">Après</span></div><figcaption>Faites glisser la ligne pour comparer.</figcaption></figure>';
}

function cta(string $titre = 'Parlons de votre pharmacie', string $texte = 'Envoyez-nous une photo de votre local : nous revenons vers vous avec une première idée et un devis.'): string {
    $r = reglages();
    $defile = '<div class="phrase-projet" aria-hidden="true"><div class="pp-in"><span class="ft"><i>Quel</i> <i>est</i> <i>votre</i></span> <span class="ft"><b><i>prochain</i> <i>projet</i></b>&nbsp;<i>?</i></span></div></div>';
    return $defile . '<section class="cta"><div><h2>' . h($titre) . '</h2><p>' . h($texte) . '</p></div><div class="c">'
        . '<a class="tel" href="' . h(tel_lien()) . '">' . h($r['telephone'] ?? '') . '</a>'
        . '<a href="mailto:' . h($r['email'] ?? '') . '" style="color:#DDEBE5">' . h($r['email'] ?? '') . '</a>'
        . '<div class="btns" style="margin-top:12px"><a class="btn plein" href="' . url('contact') . '">Demander un devis</a><a class="btn ligne" href="' . h(lien_wa()) . '" target="_blank" rel="noopener">WhatsApp</a></div></div></section>';
}

function chemin(): string {
    $e = [['Visite du local', 'Nous venons écouter vos besoins sur place.'], ['Relevé et étude', 'Mesures, plan, organisation des espaces.'], ['Projet en image', 'Vous découvrez votre future pharmacie.'], ['Validation', 'Vous ajustez, puis vous validez.'], ['Fabrication et pose', 'Nos équipes réalisent, vous ouvrez.']];
    $o = '<ol class="chemin">';
    foreach ($e as $i => [$t, $p]) $o .= '<li><div class="pt">' . ($i + 1) . '</div><h3>' . h($t) . '</h3><p>' . h($p) . '</p></li>';
    return $o . '</ol>';
}

function galerie(array $images, string $alt): string {
    $o = '<div class="galerie">';
    foreach ($images as $i => $im) $o .= '<button type="button" data-src="' . h(media($im, 'd')) . '" aria-label="Agrandir la photo"><img src="' . h(media(miniature($im))) . '" alt="' . h($alt) . ', photo ' . ($i + 1) . '" loading="lazy"></button>';
    return $o . '</div>';
}

function temoignages_bloc(array $liste): string {
    if (!$liste) return '';
    $o = '<section class="sec avis"><h2>Ils nous font confiance</h2><div class="avis-liste">';
    foreach ($liste as $t) {
        $r = !empty($t['realisation']) ? trouver(realisations(), 'id', $t['realisation']) : null;
        $lien = $r ? '<a class="avis-lien" href="' . h(url('realisations/' . $r['slug'])) . '">Voir le projet</a>' : '';
        $img = !empty($t['image']) ? '<img src="' . h(media($t['image'])) . '" alt="" loading="lazy">' : '';
        $o .= '<figure class="avis-carte"><blockquote>' . paragraphes($t['texte'] ?? '') . '</blockquote><figcaption>' . $img . '<span><b>' . h($t['nom'] ?? '') . '</b>' . h($t['etablissement'] ?? '') . $lien . '</span></figcaption></figure>';
    }
    return $o . '</div></section>';
}
