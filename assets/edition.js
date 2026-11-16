/* PharmaChance : édition du site en direct (visible uniquement par les personnes connectées à l'administration) */
(function () {
  var conf = JSON.parse(document.getElementById('pc-ed-conf').textContent);
  var ADMIN = conf.admin;
  var ICI = location.pathname + location.search.replace(/[?&]edition=[01]/, '').replace(/^&/, '?');

  function el(tag, cls, html) { var e = document.createElement(tag); if (cls) e.className = cls; if (html != null) e.innerHTML = html; return e; }
  function cookie(v) { document.cookie = 'pc_edition=' + v + '; path=/; SameSite=Lax'; }
  function norm(s) { return String(s).replace(/[\s ]+/g, ' ').trim(); }

  var toastEl = null, toastT = 0;
  function toast(msg, type) {
    if (!toastEl) { toastEl = el('div', 'pc-ui pc-toast'); toastEl.setAttribute('role', 'status'); document.body.appendChild(toastEl); }
    toastEl.textContent = msg; toastEl.className = 'pc-ui pc-toast visible' + (type ? ' ' + type : '');
    clearTimeout(toastT); toastT = setTimeout(function () { toastEl.classList.remove('visible'); }, 4200);
  }

  /* ---------- Hors mode édition : un simple bouton ---------- */
  if (!conf.edition) {
    var b = el('div', 'pc-ui pc-barre pc-mini');
    b.innerHTML = '<a class="pc-btn pc-plein" href="#">Modifier cette page</a><a class="pc-btn" href="' + ADMIN + '">Administration</a><a class="pc-btn pc-sortir" href="' + ADMIN + '?p=deconnexion">Se déconnecter</a>';
    b.firstChild.addEventListener('click', function (e) { e.preventDefault(); cookie('1'); location.reload(); });
    document.body.appendChild(b);
    return;
  }

  /* ---------- Mode édition ---------- */
  var modifs = { textes: {}, images: {}, listes: {}, champs: {} }, blocsModifies = false, nb = 0;
  document.querySelectorAll('details').forEach(function (d) { d.open = true; });

  // barre d'outils
  var barre = el('div', 'pc-ui pc-barre');
  barre.innerHTML = '<div class="pc-titre"><b>Mode édition</b><span class="pc-etat">Aucune modification</span></div>'
    + '<div class="pc-actions"><button type="button" class="pc-btn pc-plein pc-enr" disabled>Enregistrer</button>'
    + '<button type="button" class="pc-btn pc-annuler" disabled>Annuler</button>'
    + '<button type="button" class="pc-btn pc-aide-b" aria-expanded="false">Aide</button>'
    + '<button type="button" class="pc-btn pc-quitter">Quitter</button>'
    + '<a class="pc-btn pc-sortir" href="' + ADMIN + '?p=deconnexion">Se déconnecter</a></div>'
    + '<div class="pc-aide" hidden><p><b>Textes :</b> cliquez sur un texte, écrivez, puis cliquez ailleurs. Un texte identique est modifié partout sur le site (un bouton « Demander un devis », par exemple).</p>'
    + '<p><b>Mots qui défilent (accueil) :</b> écrivez les mots séparés par des virgules, par exemple « Conseils, Agencement, Vitrines ».</p>'
    + '<p><b>Photos :</b> survolez une photo puis cliquez sur « Changer la photo », ou glissez directement une photo de votre ordinateur sur l\'image.</p>'
    + '<p><b>Sections :</b> survolez une section : sa barre permet de la déplacer (glisser ⠿ ou flèches) ou de la masquer.</p>'
    + '<p><b>Projets et services :</b> sur les pages Réalisations et Services, glissez une vignette par sa poignée ⠿ pour changer l\'ordre.</p>'
    + '<p>Les liens sont désactivés pendant l\'édition : Ctrl + clic (⌘ + clic sur Mac) pour changer de page. Rien n\'est visible des visiteurs avant « Enregistrer ».</p>'
    + '<p><a href="' + ADMIN + '">Ouvrir l\'administration</a> pour les projets, articles, coordonnées et pages légales.</p></div>';
  document.body.appendChild(barre);
  var etat = barre.querySelector('.pc-etat'), bEnr = barre.querySelector('.pc-enr'), bAnn = barre.querySelector('.pc-annuler');
  function compter() {
    nb = Object.keys(modifs.textes).length + Object.keys(modifs.images).length + Object.keys(modifs.listes).length + Object.keys(modifs.champs).length + (blocsModifies ? 1 : 0);
    etat.textContent = nb ? nb + ' modification' + (nb > 1 ? 's' : '') + ' non enregistrée' + (nb > 1 ? 's' : '') : 'Aucune modification';
    bEnr.disabled = bAnn.disabled = !nb; barre.classList.toggle('pc-sale', !!nb);
  }
  barre.querySelector('.pc-aide-b').addEventListener('click', function () {
    var a = barre.querySelector('.pc-aide'); a.hidden = !a.hidden; this.setAttribute('aria-expanded', String(!a.hidden));
  });
  barre.querySelector('.pc-sortir').addEventListener('click', function (e) {
    if (nb && !confirm('Se déconnecter sans enregistrer les ' + nb + ' modification(s) ?')) { e.preventDefault(); return; }
    nb = 0; cookie('0');
  });
  barre.querySelector('.pc-quitter').addEventListener('click', function () {
    if (nb && !confirm('Quitter sans enregistrer les ' + nb + ' modification(s) ?')) return;
    nb = 0; cookie('0'); location.href = ICI || '/';
  });
  bAnn.addEventListener('click', function () { if (confirm('Annuler toutes les modifications non enregistrées ?')) { nb = 0; location.reload(); } });
  window.addEventListener('beforeunload', function (e) { if (nb) { e.preventDefault(); e.returnValue = ''; } });

  /* ---------- Textes ---------- */
  var EXCLUS = 'script,style,noscript,textarea,select,option,svg,title,.pc-ui,[data-compte],.phrase-projet,.xf-mot,.visionneuse,.appel,.evitement,[aria-hidden="true"],.barre-bas,.wa';
  var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, { acceptNode: function (n) {
    if (!n.data.trim()) return NodeFilter.FILTER_REJECT;
    var p = n.parentElement; if (!p || p.closest(EXCLUS)) return NodeFilter.FILTER_REJECT;
    return NodeFilter.FILTER_ACCEPT;
  } });
  var noeuds = []; while (walker.nextNode()) noeuds.push(walker.currentNode);
  noeuds.forEach(function (n) {
    var t = document.createElement('pc-t'), v = norm(n.data);
    t.dataset.k = Object.prototype.hasOwnProperty.call(conf.inverse, v) ? conf.inverse[v] : v;
    t.dataset.v = v; t.textContent = n.data;
    n.parentNode.replaceChild(t, n);
  });
  function editer(t) {
    if (t.isContentEditable) return;
    t.contentEditable = 'true'; t.spellcheck = true; t.focus();
  }
  function valider(t) {
    t.contentEditable = 'false';
    var v = norm(t.textContent);
    if (v === t.dataset.v) { t.textContent = t.dataset.v; return; }
    if (v === '') { t.textContent = t.dataset.v; toast('Un texte ne peut pas être vide. Pour retirer tout un passage, masquez sa section.', 'pc-attention'); return; }
    var k = t.dataset.k;
    modifs.textes[k] = v;
    document.querySelectorAll('pc-t').forEach(function (o) { if (o.dataset.k === k) { o.textContent = v; o.dataset.v = v; o.classList.add('pc-modifie'); } });
    var n = document.querySelectorAll('pc-t.pc-modifie[data-k="' + CSS.escape(k) + '"]').length;
    if (n > 1) toast('Texte modifié à ' + n + ' endroits de cette page (et partout ailleurs sur le site).');
    compter();
  }
  document.addEventListener('focusout', function (e) { if (e.target.tagName === 'PC-T') valider(e.target); });
  document.addEventListener('keydown', function (e) {
    var t = e.target; if (t.tagName !== 'PC-T') return;
    if (e.key === 'Enter') { e.preventDefault(); t.blur(); }
    if (e.key === 'Escape') { t.textContent = t.dataset.v; t.blur(); }
  });
  document.addEventListener('paste', function (e) {
    if (e.target.tagName !== 'PC-T' && !(e.target.closest && e.target.closest('pc-t'))) return;
    e.preventDefault(); document.execCommand('insertText', false, norm((e.clipboardData || window.clipboardData).getData('text')));
  });

  // les liens ne naviguent plus (sauf Ctrl/⌘ + clic) ; un clic sur un texte l'ouvre à l'édition
  document.addEventListener('click', function (e) {
    if (e.target.closest('.pc-ui')) return;
    var t = e.target.closest('pc-t');
    var a = e.target.closest('a[href]');
    if (t) { e.preventDefault(); e.stopPropagation(); editer(t); return; }
    if (e.target.closest('summary')) { e.preventDefault(); return; }
    if (a) {
      e.preventDefault(); e.stopPropagation();
      if (e.ctrlKey || e.metaKey || e.shiftKey) {
        var h = a.getAttribute('href'); if (h.charAt(0) === '#') return;
        if (a.target === '_blank') window.open(a.href, '_blank'); else location.href = a.href;
      } else toast('Mode édition : les liens sont désactivés. Ctrl + clic (⌘ + clic sur Mac) pour ouvrir la page.');
    }
  }, true);

  /* ---------- Photos ---------- */
  function origine(img) { var s = img.getAttribute('src') || '', i = s.indexOf('#o='); return i < 0 ? null : decodeURIComponent(s.slice(i + 3)); }
  function visible(img) { var cs = getComputedStyle(img); return cs.visibility !== 'hidden' && +cs.opacity > 0.15 && img.offsetWidth > 30; }
  function imageSous(x, y) {
    var l = document.elementsFromPoint(x, y);
    for (var i = 0; i < l.length; i++) {
      if (l[i].closest && l[i].closest('.pc-ui')) return null;
      if (l[i].tagName === 'IMG' && origine(l[i]) && visible(l[i])) return l[i];
    }
    return null;
  }
  var bImg = el('button', 'pc-ui pc-img-btn', 'Changer la photo'); bImg.type = 'button'; bImg.hidden = true; document.body.appendChild(bImg);
  var fichier = el('input', 'pc-ui'); fichier.type = 'file'; fichier.accept = 'image/jpeg,image/png,image/webp'; fichier.hidden = true; document.body.appendChild(fichier);
  var imgCible = null;
  function placerImg(img) {
    imgCible = img; if (!img) { bImg.hidden = true; return; }
    var r = img.getBoundingClientRect();
    bImg.hidden = false;
    bImg.style.top = Math.max(8, Math.min(window.innerHeight - 50, Math.min(r.bottom, window.innerHeight) - 50)) + 'px';
    bImg.style.left = Math.max(8, r.left + 12) + 'px';
  }
  bImg.addEventListener('click', function () { if (imgCible) { fichier.dataset.cible = origine(imgCible); fichier.click(); } });
  fichier.addEventListener('change', function () { if (fichier.files[0]) envoyerPhoto(fichier.files[0], fichier.dataset.cible); fichier.value = ''; });
  function envoyerPhoto(f, cle) {
    if (!cle) return;
    if (!/^image\/(jpeg|png|webp)$/.test(f.type)) { toast('Format non pris en charge : utilisez une photo JPEG, PNG ou WebP.', 'pc-attention'); return; }
    var imgs = Array.prototype.filter.call(document.querySelectorAll('img'), function (i) { return origine(i) === cle; });
    imgs.forEach(function (i) { i.classList.add('pc-envoi'); });
    toast('Envoi de la photo…');
    var fd = new FormData(); fd.append('csrf', conf.csrf); fd.append('photo', f);
    fetch(ADMIN + '?p=edition-image', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        imgs.forEach(function (i) { i.classList.remove('pc-envoi'); });
        if (!j.ok) { toast(j.message || 'La photo n\'a pas pu être envoyée.', 'pc-attention'); return; }
        imgs.forEach(function (i) { i.removeAttribute('srcset'); i.src = j.url + '#o=' + encodeURIComponent(cle); i.classList.add('pc-modifie'); var bt = i.closest('[data-src]'); if (bt) bt.dataset.src = j.url; });
        modifs.images[cle] = j.chemin; compter(); toast('Photo remplacée. Pensez à enregistrer.');
      })
      .catch(function () { imgs.forEach(function (i) { i.classList.remove('pc-envoi'); }); toast('Connexion perdue pendant l\'envoi. Réessayez.', 'pc-attention'); });
  }
  // glisser une photo depuis l'ordinateur
  var survolDepot = null;
  document.addEventListener('dragover', function (e) {
    if (!e.dataTransfer || Array.prototype.indexOf.call(e.dataTransfer.types, 'Files') < 0) return;
    e.preventDefault();
    var img = imageSous(e.clientX, e.clientY);
    if (survolDepot !== img) { if (survolDepot) survolDepot.classList.remove('pc-depot'); survolDepot = img; if (img) img.classList.add('pc-depot'); }
    e.dataTransfer.dropEffect = img ? 'copy' : 'none';
  });
  document.addEventListener('dragleave', function (e) { if (!e.relatedTarget && survolDepot) { survolDepot.classList.remove('pc-depot'); survolDepot = null; } });
  document.addEventListener('drop', function (e) {
    if (!e.dataTransfer || !e.dataTransfer.files.length) return;
    e.preventDefault();
    var img = imageSous(e.clientX, e.clientY);
    if (survolDepot) { survolDepot.classList.remove('pc-depot'); survolDepot = null; }
    if (img) envoyerPhoto(e.dataTransfer.files[0], origine(img));
    else toast('Déposez la photo directement sur l\'image à remplacer.');
  });

  /* ---------- Cadres photo d'un projet (avant / après) ---------- */
  var fichierChamp = el('input', 'pc-ui'); fichierChamp.type = 'file'; fichierChamp.accept = 'image/jpeg,image/png,image/webp'; fichierChamp.hidden = true; document.body.appendChild(fichierChamp);
  var slotCible = null;
  function envoyerChamp(f, slot) {
    if (!/^image\/(jpeg|png|webp)$/.test(f.type)) { toast('Format non pris en charge : utilisez une photo JPEG, PNG ou WebP.', 'pc-attention'); return; }
    slot.classList.add('pc-envoi'); toast('Envoi de la photo…');
    var fd = new FormData(); fd.append('csrf', conf.csrf); fd.append('photo', f);
    fetch(ADMIN + '?p=edition-image', { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (j) {
      slot.classList.remove('pc-envoi');
      if (!j.ok) { toast(j.message || 'La photo n\'a pas pu être envoyée.', 'pc-attention'); return; }
      var im = slot.querySelector('img'); if (!im) { im = document.createElement('img'); im.alt = ''; slot.insertBefore(im, slot.firstChild); }
      im.src = j.url; slot.querySelector('em').textContent = 'Changer la photo'; slot.classList.add('pc-modifie');
      modifs.champs[slot.dataset.champ] = j.chemin; compter(); toast('Photo ajoutée. Cliquez sur « Enregistrer » quand les deux photos sont là.');
    }).catch(function () { slot.classList.remove('pc-envoi'); toast('Connexion perdue pendant l\'envoi. Réessayez.', 'pc-attention'); });
  }
  document.querySelectorAll('[data-champ]').forEach(function (slot) {
    slot.addEventListener('click', function () { slotCible = slot; fichierChamp.click(); });
    slot.addEventListener('dragover', function (e) { e.preventDefault(); e.stopPropagation(); slot.classList.add('pc-depot'); });
    slot.addEventListener('dragleave', function () { slot.classList.remove('pc-depot'); });
    slot.addEventListener('drop', function (e) { e.preventDefault(); e.stopPropagation(); slot.classList.remove('pc-depot'); if (e.dataTransfer.files[0]) envoyerChamp(e.dataTransfer.files[0], slot); });
  });
  fichierChamp.addEventListener('change', function () { if (fichierChamp.files[0] && slotCible) envoyerChamp(fichierChamp.files[0], slotCible); fichierChamp.value = ''; });
  if (location.hash === '#avant-apres') { var ancre = document.getElementById('avant-apres'); if (ancre) setTimeout(function () { ancre.scrollIntoView({ block: 'center' }); }, 300); }

  /* ---------- Sections (blocs) ---------- */
  var blocs = function () { return Array.prototype.slice.call(document.querySelectorAll('[data-bloc]')); };
  var outils = el('div', 'pc-ui pc-bloc-outils');
  outils.innerHTML = '<span class="pc-poignee" title="Glisser pour déplacer la section">⠿</span><span class="pc-nom"></span>'
    + '<button type="button" class="pc-haut" title="Monter">↑</button><button type="button" class="pc-bas" title="Descendre">↓</button>'
    + '<button type="button" class="pc-masq"></button>';
  outils.hidden = true; document.body.appendChild(outils);
  var blocCible = null, glisse = null;
  function placerOutils(b) {
    if (glisse) return;
    if (blocCible && blocCible !== b) blocCible.classList.remove('pc-survol');
    blocCible = b;
    if (!b) { outils.hidden = true; return; }
    b.classList.add('pc-survol');
    var m = b.hasAttribute('data-bloc-masque');
    outils.querySelector('.pc-nom').textContent = (m ? 'Masquée · ' : '') + (b.dataset.blocNom || 'Section');
    outils.querySelector('.pc-masq').textContent = m ? 'Afficher' : 'Masquer';
    outils.hidden = false;
    var r = b.getBoundingClientRect();
    outils.style.top = Math.max(70, Math.min(window.innerHeight - 50, r.top + 8)) + 'px';
    outils.style.left = Math.max(8, Math.min(window.innerWidth - outils.offsetWidth - 8, r.right - outils.offsetWidth - 8)) + 'px';
  }
  function deplacer(b, sens) {
    var l = blocs(), i = l.indexOf(b), j = i + sens; if (j < 0 || j >= l.length) return;
    if (sens < 0) l[j].parentNode.insertBefore(b, l[j]); else l[j].parentNode.insertBefore(b, l[j].nextSibling);
    blocsModifies = true; compter(); b.scrollIntoView({ block: 'nearest', behavior: 'smooth' }); setTimeout(function () { placerOutils(b); }, 350);
  }
  outils.querySelector('.pc-haut').addEventListener('click', function () { if (blocCible) deplacer(blocCible, -1); });
  outils.querySelector('.pc-bas').addEventListener('click', function () { if (blocCible) deplacer(blocCible, 1); });
  outils.querySelector('.pc-masq').addEventListener('click', function () {
    if (!blocCible) return;
    if (blocCible.hasAttribute('data-bloc-masque')) blocCible.removeAttribute('data-bloc-masque'); else blocCible.setAttribute('data-bloc-masque', '');
    blocsModifies = true; compter(); placerOutils(blocCible);
    toast(blocCible.hasAttribute('data-bloc-masque') ? 'Section masquée : les visiteurs ne la verront plus après enregistrement.' : 'Section de nouveau affichée.');
  });
  var ligne = el('div', 'pc-ui pc-ligne'); ligne.hidden = true; document.body.appendChild(ligne);
  function cibleGlisse(x, y, type) {
    var l = document.elementsFromPoint(x, y);
    for (var i = 0; i < l.length; i++) {
      var c = type === 'bloc' ? l[i].closest && l[i].closest('[data-bloc]') : l[i].closest && l[i].closest('[data-item]');
      if (c && c !== glisse.el && (type === 'bloc' || c.parentNode === glisse.el.parentNode)) return c;
    }
    return null;
  }
  function demarrerGlisse(e, elt, type) {
    e.preventDefault();
    glisse = { el: elt, type: type, cible: null, apres: false };
    elt.classList.add('pc-glisse'); document.documentElement.classList.add('pc-glisse-en-cours');
    e.target.setPointerCapture && e.target.setPointerCapture(e.pointerId);
  }
  var defil = 0;
  document.addEventListener('pointermove', function (e) {
    if (glisse) {
      var c = cibleGlisse(e.clientX, e.clientY, glisse.type);
      glisse.cible = c;
      if (c) {
        var r = c.getBoundingClientRect(); ligne.hidden = false;
        if (glisse.type === 'bloc') {
          glisse.apres = e.clientY > r.top + r.height / 2;
          ligne.style.cssText = 'top:' + ((glisse.apres ? r.bottom : r.top) - 3) + 'px;left:' + Math.max(0, r.left) + 'px;width:' + Math.min(window.innerWidth, r.width) + 'px;height:6px';
        } else {
          glisse.apres = e.clientX > r.left + r.width / 2;
          ligne.style.cssText = 'left:' + ((glisse.apres ? r.right : r.left) - 3) + 'px;top:' + r.top + 'px;height:' + r.height + 'px;width:6px';
        }
      } else ligne.hidden = true;
      var bord = 70; defil = e.clientY < bord ? -14 : e.clientY > window.innerHeight - bord ? 14 : 0;
      return;
    }
    if (e.target.closest && e.target.closest('.pc-ui')) return;
    var b = e.target.closest && e.target.closest('[data-bloc]'); if (b !== blocCible) placerOutils(b);
    var img = imageSous(e.clientX, e.clientY); if (img !== imgCible) placerImg(img);
    var it = e.target.closest && e.target.closest('[data-liste] > [data-item]'); if (it !== itemCible) placerItem(it);
  });
  // sur écran tactile, un appui fait apparaître les mêmes outils que le survol
  document.addEventListener('pointerdown', function (e) {
    if (e.pointerType === 'mouse' || glisse || (e.target.closest && e.target.closest('.pc-ui'))) return;
    placerOutils(e.target.closest('[data-bloc]')); placerImg(imageSous(e.clientX, e.clientY)); placerItem(e.target.closest('[data-liste] > [data-item]'));
  }, true);
  (function boucle() { if (glisse && defil) window.scrollBy(0, defil); requestAnimationFrame(boucle); })();
  document.addEventListener('pointerup', function () {
    if (!glisse) return;
    var g = glisse; glisse = null; ligne.hidden = true; defil = 0;
    g.el.classList.remove('pc-glisse'); document.documentElement.classList.remove('pc-glisse-en-cours');
    if (g.cible) {
      g.cible.parentNode.insertBefore(g.el, g.apres ? g.cible.nextSibling : g.cible);
      if (g.type === 'bloc') blocsModifies = true;
      else { var p = g.el.parentNode; modifs.listes[p.dataset.liste] = Array.prototype.map.call(p.querySelectorAll(':scope > [data-item]'), function (x) { return x.dataset.item; }); }
      compter();
    }
    placerOutils(null); placerItem(null);
  });
  outils.querySelector('.pc-poignee').addEventListener('pointerdown', function (e) { if (blocCible) demarrerGlisse(e, blocCible, 'bloc'); });
  window.addEventListener('scroll', function () { if (!glisse) { placerOutils(blocCible); placerImg(imgCible && visible(imgCible) ? imgCible : null); placerItem(itemCible); } }, { passive: true });

  /* ---------- Ordre des projets / services ---------- */
  var bItem = el('span', 'pc-ui pc-item-poignee', '⠿'); bItem.title = 'Glisser pour changer l\'ordre'; bItem.hidden = true; document.body.appendChild(bItem);
  var itemCible = null;
  function placerItem(it) {
    if (glisse) return;
    itemCible = it; if (!it) { bItem.hidden = true; return; }
    var r = it.getBoundingClientRect(); bItem.hidden = false;
    bItem.style.top = (r.top + 10) + 'px'; bItem.style.left = (r.left + 10) + 'px';
  }
  bItem.addEventListener('pointerdown', function (e) { if (itemCible) demarrerGlisse(e, itemCible, 'item'); });

  /* ---------- Enregistrer ---------- */
  bEnr.addEventListener('click', function () {
    var d = { textes: modifs.textes, images: modifs.images, listes: modifs.listes, champs: modifs.champs, blocs: {} };
    if (blocsModifies) {
      var l = blocs();
      d.blocs[conf.page] = { ordre: l.map(function (b) { return b.dataset.bloc; }), masques: l.filter(function (b) { return b.hasAttribute('data-bloc-masque'); }).map(function (b) { return b.dataset.bloc; }) };
    }
    bEnr.disabled = true; bEnr.textContent = 'Enregistrement…';
    var fd = new FormData(); fd.append('csrf', conf.csrf); fd.append('donnees', JSON.stringify(d));
    fetch(ADMIN + '?p=edition-enregistrer', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        bEnr.textContent = 'Enregistrer';
        if (!j.ok) { bEnr.disabled = false; toast(j.message || 'Enregistrement impossible.', 'pc-attention'); return; }
        nb = 0; toast('Modifications enregistrées : elles sont en ligne.', 'pc-ok');
        setTimeout(function () { location.reload(); }, 900);
      })
      .catch(function () { bEnr.disabled = false; bEnr.textContent = 'Enregistrer'; toast('Connexion perdue : rien n\'a été enregistré. Réessayez.', 'pc-attention'); });
  });
  compter();
})();
