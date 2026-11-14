<?php $r = reglages(); $v = $valeurs ?? []; $err = $erreurs ?? []; $pre = $v['projet'] ?? preg_replace('/[^a-z-]/', '', (string)($_GET['projet'] ?? '')); ?>
<section class="entete"><div class="t"><p class="fil"><a href="<?= url() ?>">Accueil</a> / Contact</p><h1>Parlons de votre pharmacie</h1></div><p class="intro">Décrivez votre projet en quelques lignes et joignez une photo du local : nous vous rappelons.</p></section>
<section class="wrap ct-etapes"><h2 class="h-moy">Comment se passe votre demande</h2><ol><?php foreach (texte_site('contact_etapes') as $k => [$t, $d]): ?><li><span class="n"><?= $k + 1 ?></span><b><?= h($t) ?></b><span><?= h($d) ?></span></li><?php endforeach; ?></ol></section>
<section class="wrap deux" style="padding-bottom:120px;align-items:flex-start">
<div style="flex:1 1 320px"><ul class="coords">
  <li><b>Téléphone</b><a href="<?= h(tel_lien()) ?>"><?= h($r['telephone'] ?? '') ?></a></li>
  <li><b>WhatsApp</b><a href="<?= h(lien_wa()) ?>" target="_blank" rel="noopener">Écrire sur WhatsApp</a></li>
  <li><b>E-mail</b><a href="mailto:<?= h($r['email'] ?? '') ?>"><?= h($r['email'] ?? '') ?></a></li>
  <li><b>Adresse</b><span class="txt-coord"><?= h($r['adresse'] ?? '') ?></span></li>
  <li><b>Zone d'intervention</b><span class="txt-coord"><?= h($r['zone'] ?? '') ?></span></li></ul></div>
<?php if (!empty($envoye)): ?>
<div class="form" style="flex:2 1 560px;display:block"><h2 class="h-moy">Merci, votre demande est bien partie.</h2><p style="font-size:18px">Nous vous rappelons rapidement au numéro indiqué. Pour aller plus vite, vous pouvez aussi nous écrire sur <a class="lien" href="<?= h(lien_wa()) ?>">WhatsApp</a>.</p></div>
<?php else: ?>
<form class="form" style="flex:2 1 560px" method="post" action="<?= url('contact') ?>" enctype="multipart/form-data">
<?= champ_csrf() ?><input type="hidden" name="t" value="<?= time() ?>">
<div class="hp" aria-hidden="true"><label>Site web<input name="site_web" tabindex="-1" autocomplete="off"></label></div>
<?php if ($err): ?><div class="erreurs full" role="alert"><b>Vérifiez le formulaire :</b><ul><?php foreach ($err as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<label>Nom et prénom<input name="nom" required maxlength="120" autocomplete="name" value="<?= h($v['nom'] ?? '') ?>"></label>
<label>Téléphone<input name="tel" type="tel" required maxlength="30" autocomplete="tel" value="<?= h($v['tel'] ?? '') ?>"></label>
<label>E-mail <small>facultatif</small><input name="email" type="email" maxlength="160" autocomplete="email" value="<?= h($v['email'] ?? '') ?>"></label>
<label>Ville<input name="ville" required maxlength="80" value="<?= h($v['ville'] ?? '') ?>"></label>
<label class="full">Type de projet<select name="projet"><option value=""<?= $pre === '' ? ' selected' : '' ?>>Choisissez</option><?php foreach (services() as $s): ?><option value="<?= h($s['cle']) ?>"<?= $pre === $s['cle'] ? ' selected' : '' ?>><?= h($s['nom']) ?></option><?php endforeach; ?><option value="autre"<?= $pre === 'autre' ? ' selected' : '' ?>>Autre</option></select></label>
<label class="full">Votre besoin<textarea name="message" rows="5" required maxlength="4000"><?= h($v['message'] ?? '') ?></textarea></label>
<label>Budget indicatif <small>facultatif</small><input name="budget" maxlength="80" placeholder="Ex. : à définir ensemble" value="<?= h($v['budget'] ?? '') ?>"></label>
<label>Photos ou plans <small>facultatif, 5 fichiers, 10 Mo max. chacun</small><input name="fichiers[]" type="file" multiple accept="image/jpeg,image/png,image/webp,application/pdf"></label>
<label class="full case"><input type="checkbox" name="accord" value="1" required<?= !empty($v['accord']) ? ' checked' : '' ?>> J'accepte que PharmaChance utilise ces informations pour me recontacter au sujet de ma demande (<a class="lien" href="<?= url('confidentialite') ?>" target="_blank">politique de confidentialité</a>).</label>
<div class="full"><button class="btn plein" type="submit">Envoyer ma demande</button></div>
</form>
<?php endif; ?>
</section>
<?= faq_bloc(texte_site('contact_faq'), 'Avant de nous écrire') ?>
<div style="height:100px"></div>
