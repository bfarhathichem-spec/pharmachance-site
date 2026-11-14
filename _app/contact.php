<?php
declare(strict_types=1);

function traiter_contact(): array {
    verifier_csrf();
    $v = array_map(fn($x) => is_string($x) ? trim($x) : '', $_POST);
    $erreurs = [];
    // anti-robots : champ piège et délai minimal
    if (($v['site_web'] ?? '') !== '' || (time() - (int)($v['t'] ?? 0)) < 3) return ['envoye' => true];
    if (mb_strlen($v['nom'] ?? '') < 2) $erreurs[] = 'Indiquez votre nom et prénom.';
    if (!preg_match('/^[\d\s+().-]{8,30}$/', $v['tel'] ?? '')) $erreurs[] = 'Indiquez un numéro de téléphone valide.';
    if (($v['email'] ?? '') !== '' && !filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $erreurs[] = "L'adresse e-mail n'est pas valide.";
    if (mb_strlen($v['ville'] ?? '') < 2) $erreurs[] = 'Indiquez votre ville.';
    if (mb_strlen($v['message'] ?? '') < 10) $erreurs[] = 'Décrivez votre besoin en quelques mots.';
    if (empty($v['accord'])) $erreurs[] = "Cochez la case d'accord pour que nous puissions vous recontacter.";

    $pj = [];
    $id = date('Ymd-His') . '-' . bin2hex(random_bytes(3));
    foreach (fichiers_multiples('fichiers') as $f) {
        if ($f['error'] === UPLOAD_ERR_NO_FILE) continue;
        if (count($pj) >= 5) { $erreurs[] = 'Cinq fichiers au maximum.'; break; }
        if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > 10 * 1024 * 1024) { $erreurs[] = 'Un fichier est trop lourd (10 Mo maximum).'; continue; }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf'][$mime] ?? null;
        if (!$ext) { $erreurs[] = 'Formats acceptés : photos (JPG, PNG, WebP) et PDF.'; continue; }
        if (!$erreurs) {
            @mkdir(FICHIERS_DEMANDES . "/$id", 0750, true);
            $nom = (count($pj) + 1) . '.' . $ext;
            if (move_uploaded_file($f['tmp_name'], FICHIERS_DEMANDES . "/$id/$nom")) $pj[] = $nom;
        }
    }
    if ($erreurs) return ['erreurs' => $erreurs, 'valeurs' => $v];

    $d = ['id' => $id, 'date' => date('c'), 'nom' => $v['nom'], 'tel' => $v['tel'], 'email' => $v['email'] ?? '', 'ville' => $v['ville'],
          'projet' => $v['projet'] ?? '', 'message' => $v['message'], 'budget' => $v['budget'] ?? '', 'fichiers' => $pj, 'statut' => 'nouvelle'];
    $toutes = charger('demandes', []); array_unshift($toutes, $d); enregistrer('demandes', $toutes);

    $r = reglages();
    $dest = $r['email_notifications'] ?? ($r['email'] ?? '');
    if ($dest) {
        $hote = preg_replace('/[^a-z0-9.-]/i', '', $_SERVER['HTTP_HOST'] ?? 'pharmachance.com');
        $sujet = '=?UTF-8?B?' . base64_encode('Nouvelle demande de devis : ' . $d['nom'] . ' (' . $d['ville'] . ')') . '?=';
        $corps = "Nouvelle demande reçue sur le site.\n\nNom : {$d['nom']}\nTéléphone : {$d['tel']}\nE-mail : {$d['email']}\nVille : {$d['ville']}\nProjet : " . nom_service($d['projet']) .
                 "\nBudget : {$d['budget']}\nFichiers joints : " . count($pj) . "\n\nMessage :\n{$d['message']}\n\nVoir la demande : https://$hote" . url('admin/?p=demandes');
        $ent = "From: PharmaChance <no-reply@$hote>\r\nContent-Type: text/plain; charset=UTF-8\r\n";
        if ($d['email'] && filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $ent .= 'Reply-To: ' . $d['email'] . "\r\n";
        @mail($dest, $sujet, $corps, $ent);
    }
    return ['envoye' => true];
}
