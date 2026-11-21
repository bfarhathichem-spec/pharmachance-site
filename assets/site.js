(function () {
  var top = document.querySelector('.top');
  var mnav = document.getElementById('mnav');
  var mbtn = document.querySelector('.menu-btn');

  if (top) window.addEventListener('scroll', function () { top.classList.toggle('scrolled', window.scrollY > 10); }, { passive: true });

  // menu mobile
  function fermerMenu() {
    if (!mnav || mnav.hidden) return;
    mnav.hidden = true; document.documentElement.classList.remove('menu-ouvert');
    mbtn.setAttribute('aria-expanded', 'false');
  }
  if (mbtn && mnav) {
    mbtn.addEventListener('click', function () {
      mnav.hidden = false; document.documentElement.classList.add('menu-ouvert');
      mbtn.setAttribute('aria-expanded', 'true');
      var f = mnav.querySelector('.mnav-fermer'); if (f) f.focus();
    });
    mnav.querySelector('.mnav-fermer').addEventListener('click', function () { fermerMenu(); mbtn.focus(); });
  }

  // le menu Services se referme après un clic, se rouvre au prochain survol
  document.querySelectorAll('.sous').forEach(function (s) {
    s.addEventListener('click', function (e) { if (e.target.closest('.sous-menu a')) s.classList.add('ferme'); });
    s.addEventListener('mouseleave', function () { s.classList.remove('ferme'); });
  });

  // avant / après : la ligne suit le doigt ou la souris
  document.querySelectorAll('.aa').forEach(function (aa) {
    var ap = aa.querySelector('.apres'), barre = aa.querySelector('.barre'), h = aa.querySelector('.poignee');
    var pos = 50, glisse = false, sx = 0, sy = 0, decide = false;
    function set(p) {
      pos = Math.max(0, Math.min(100, p));
      ap.style.clipPath = 'inset(0 0 0 ' + pos + '%)';
      barre.style.left = pos + '%'; h.style.left = pos + '%';
      h.setAttribute('aria-valuenow', Math.round(pos));
    }
    function depuis(e) { var r = aa.getBoundingClientRect(); set((e.clientX - r.left) / r.width * 100); }
    aa.addEventListener('pointerdown', function (e) {
      if (e.pointerType === 'mouse' && e.button !== 0) return;
      glisse = true; decide = e.pointerType === 'mouse'; sx = e.clientX; sy = e.clientY;
      aa.classList.add('actif');
      if (decide) { aa.setPointerCapture(e.pointerId); depuis(e); }
    });
    aa.addEventListener('pointermove', function (e) {
      if (!glisse) return;
      if (!decide) {
        var dx = Math.abs(e.clientX - sx), dy = Math.abs(e.clientY - sy);
        if (dx < 4 && dy < 4) return;
        if (dy > dx) { glisse = false; aa.classList.remove('actif'); return; }
        decide = true; aa.setPointerCapture(e.pointerId);
      }
      e.preventDefault(); depuis(e);
    });
    function fin(e) { if (glisse && !decide && e.type === 'pointerup') depuis(e); glisse = false; aa.classList.remove('actif'); }
    aa.addEventListener('pointerup', fin);
    aa.addEventListener('pointercancel', fin);
    h.addEventListener('keydown', function (e) {
      var k = e.key, step = e.shiftKey ? 10 : 2;
      if (k === 'ArrowLeft' || k === 'ArrowDown') { set(pos - step); e.preventDefault(); }
      else if (k === 'ArrowRight' || k === 'ArrowUp') { set(pos + step); e.preventDefault(); }
      else if (k === 'Home') { set(0); e.preventDefault(); }
      else if (k === 'End') { set(100); e.preventDefault(); }
    });
    set(50);
  });

  // filtres des réalisations
  var fbox = document.querySelector('.filtres');
  if (fbox) {
    var cartes = document.querySelectorAll('.grille > [data-cat]'), vide = document.querySelector('.vide');
    var filtrer = function (k) {
      var n = 0;
      cartes.forEach(function (c) { var ok = k === 'tous' || c.dataset.cat.split(' ').indexOf(k) > -1; c.hidden = !ok; if (ok) n++; });
      fbox.querySelectorAll('button').forEach(function (x) { x.setAttribute('aria-pressed', x.dataset.k === k ? 'true' : 'false'); });
      if (vide) vide.style.display = n ? 'none' : 'block';
    };
    fbox.addEventListener('click', function (e) {
      var x = e.target.closest('button'); if (!x) return;
      filtrer(x.dataset.k);
      // garder les résultats sous les yeux : la barre de filtres revient en haut de l'écran
      var haut = document.querySelector('header.top');
      var dec = (haut ? haut.getBoundingClientRect().height : 0) + 12;
      var ancre = document.querySelector('.ancre-filtres') || fbox;
      var y = ancre.getBoundingClientRect().top + window.pageYOffset - dec;
      if (Math.abs(window.pageYOffset - y) > 4) window.scrollTo({ top: y, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
      if (fbox.scrollWidth > fbox.clientWidth) fbox.scrollTo({ left: x.offsetLeft - (fbox.clientWidth - x.offsetWidth) / 2, behavior: 'smooth' });
      history.replaceState(null, '', x.dataset.k === 'tous' ? location.pathname : '?service=' + x.dataset.k);
    });
    filtrer(fbox.dataset.initial || 'tous');
  }

  // visionneuse photo : flèches, clavier et glissement du doigt pour passer d'une photo à l'autre
  var vis = document.querySelector('.visionneuse');
  if (vis) {
    var vi = vis.querySelector('img'), fermer = vis.querySelector('.vis-fermer') || vis.querySelector('button');
    var bPrec = vis.querySelector('.vis-prec'), bSuiv = vis.querySelector('.vis-suiv'), compte = vis.querySelector('.vis-compte');
    var liste = [], pos = 0;
    function montrer(k) {
      if (!liste.length) return;
      pos = (k + liste.length) % liste.length;
      var g = liste[pos]; vi.src = g.dataset.src; vi.alt = (g.querySelector('img') || {}).alt || '';
      var plusieurs = liste.length > 1;
      if (bPrec) bPrec.hidden = bSuiv.hidden = !plusieurs;
      if (compte) { compte.hidden = !plusieurs; compte.textContent = (pos + 1) + ' / ' + liste.length; }
    }
    document.querySelectorAll('.galerie button').forEach(function (g) {
      g.addEventListener('click', function () {
        liste = Array.prototype.slice.call(g.closest('.galerie').querySelectorAll('button[data-src]'));
        montrer(liste.indexOf(g)); vis.classList.add('ouverte'); fermer.focus();
      });
    });
    var fermerVis = function () { vis.classList.remove('ouverte'); };
    fermer.addEventListener('click', fermerVis);
    if (bPrec) bPrec.addEventListener('click', function (e) { e.stopPropagation(); montrer(pos - 1); });
    if (bSuiv) bSuiv.addEventListener('click', function (e) { e.stopPropagation(); montrer(pos + 1); });
    vis.addEventListener('click', function (e) { if (e.target === vis) fermerVis(); });
    document.addEventListener('keydown', function (e) {
      if (!vis.classList.contains('ouverte')) return;
      if (e.key === 'ArrowLeft') montrer(pos - 1); else if (e.key === 'ArrowRight') montrer(pos + 1);
    });
    var x0 = null;
    vi.addEventListener('pointerdown', function (e) { x0 = e.clientX; });
    vi.addEventListener('pointerup', function (e) { if (x0 === null) return; var d = e.clientX - x0; x0 = null; if (Math.abs(d) > 40) montrer(pos + (d < 0 ? 1 : -1)); });
    vi.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    vi.addEventListener('touchend', function (e) { if (x0 === null) return; var d = e.changedTouches[0].clientX - x0; x0 = null; if (Math.abs(d) > 40) montrer(pos + (d < 0 ? 1 : -1)); });
  }
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { if (vis) vis.classList.remove('ouverte'); fermerMenu(); } });
})();

/* Mouvements : révélations, zoom au défilement, parallaxe, texte défilant, curseur */
(function () {
  var doc = document.documentElement;
  doc.classList.add('js');
  var reduit = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // 1. Révélations au défilement, décalées dans un même groupe
  var cibles = document.querySelectorAll('.xn > div, .xs-liste li, .chemin li, .carte, .theme, .art-carte, .galerie button, .cartes-serv > *, .h-moy, .liste li, .faq details, .texte h2, .fig, .encadre, .tableau');
  var io = 'IntersectionObserver' in window ? new IntersectionObserver(function (es) {
    es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('vu'); io.unobserve(e.target); } });
  }, { rootMargin: '0px 0px -8% 0px' }) : null;
  cibles.forEach(function (el) {
    if (reduit || !io) return;
    var ox = el.parentElement && getComputedStyle(el.parentElement).overflowX;
    if (ox === 'auto' || ox === 'scroll') return;
    var freres = el.parentElement ? Array.prototype.indexOf.call(el.parentElement.children, el) : 0;
    el.style.setProperty('--d', Math.min(freres % 4, 3) * 0.09 + 's');
    el.classList.add(el.matches('.galerie button') ? 'rv-img' : 'rv');
    var r = el.getBoundingClientRect();
    if (r.top < innerHeight * 0.92) el.classList.add('vu'); else io.observe(el);
  });
  if (reduit) return;

  // 2. Zoom au défilement (image qui s'agrandit jusqu'à pleine largeur), parallaxe et texte défilant
  var zooms = document.querySelectorAll('[data-zoom]');
  [].forEach.call(document.querySelectorAll('.mos .carte'), function (c, i) { c.dataset.par = [0.05, -0.07, 0.09, -0.05, 0.07, -0.08][i % 6]; });
  [].forEach.call(document.querySelectorAll('.trefle .petale'), function (c, i) { c.dataset.par = [-0.04, 0.05, 0.06, -0.05][i % 4]; });
  var pars = document.querySelectorAll('[data-par]');
  var mots = document.querySelectorAll('.defile-mot');
  var phrases = document.querySelectorAll('.phrase-projet, .allume');
  var tick = false;
  function maj() {
    tick = false;
    var vh = innerHeight;
    zooms.forEach(function (z) {
      var r = z.getBoundingClientRect();
      var p = Math.min(1, Math.max(0, (vh - r.top) / (vh * 0.9)));
      z.style.setProperty('--p', p.toFixed(3));
    });
    if (innerWidth > 760) pars.forEach(function (el) {
      var r = el.getBoundingClientRect(); if (r.bottom < -200 || r.top > vh + 200) return;
      var c = (r.top + r.height / 2 - vh / 2) / vh;
      el.style.transform = 'translate3d(0,' + (c * parseFloat(el.dataset.par) * 100).toFixed(1) + 'px,0)';
    });
    phrasesMaj(vh);
    mots.forEach(function (m) {
      var r = m.getBoundingClientRect(); if (r.bottom < 0 || r.top > vh) return;
      if (m.classList.contains('defile-mot')) { m.style.transform = 'translate3d(' + (-window.pageYOffset * 0.12).toFixed(1) + 'px,0,0)'; return; }
      var c = (r.top - vh) / (vh + r.height);
      m.style.transform = 'translate3d(' + (c * 18).toFixed(2) + '%,0,0)';
    });
  }
  function phrasesMaj(vh) {
    phrases.forEach(function (ph) {
      var r = ph.getBoundingClientRect(); if (r.bottom < 0 || r.top > vh) return;
      var p = Math.min(1, Math.max(0, (vh - r.top) / (vh * 0.75)));
      var ws = ph.querySelectorAll('i'), n = ws.length;
      ws.forEach(function (w, k) { var v = Math.min(1, Math.max(0, p * (n + 1) - k)); w.style.opacity = (0.14 + 0.86 * v).toFixed(2); });
    });
  }
  function demande() { if (!tick) { tick = true; requestAnimationFrame(maj); } }
  addEventListener('scroll', demande, { passive: true }); addEventListener('resize', demande); maj();

  // 3. Curseur bulle « Voir » / « Lire » sur les cartes (souris uniquement)
  if (!matchMedia('(hover: hover) and (pointer: fine)').matches) return;
  var cur = document.createElement('div'); cur.className = 'curseur'; cur.setAttribute('aria-hidden', 'true'); document.body.appendChild(cur);
  var x = -200, y = -200, tx = -200, ty = -200, s = 0, ts = 0, actif = false;
  addEventListener('mousemove', function (e) {
    tx = e.clientX; ty = e.clientY;
    var c = e.target.closest && e.target.closest('.carte, .art-carte, .theme, .galerie button');
    ts = c ? 1 : 0;
    if (c) cur.textContent = c.matches('.art-carte') ? 'Lire' : (c.matches('.galerie button') ? 'Agrandir' : 'Voir');
    if (!actif) { actif = true; requestAnimationFrame(anim); }
  }, { passive: true });
  document.addEventListener('mouseleave', function () { ts = 0; if (!actif) { actif = true; requestAnimationFrame(anim); } });
  addEventListener('scroll', function () {
    var el = document.elementFromPoint(tx, ty);
    var c = el && el.closest && el.closest('.carte, .art-carte, .theme, .galerie button');
    ts = c ? 1 : 0;
    if (!actif) { actif = true; requestAnimationFrame(anim); }
  }, { passive: true });
  addEventListener('blur', function () { ts = 0; s = 0; cur.style.transform = 'translate3d(-200px,-200px,0) scale(0)'; });
  function anim() {
    x += (tx - x) * 0.2; y += (ty - y) * 0.2; s += (ts - s) * 0.18; if (Math.abs(ts - s) < 0.02) s = ts;
    cur.style.transform = 'translate3d(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px,0) scale(' + s.toFixed(3) + ')';
    if (Math.abs(tx - x) + Math.abs(ty - y) + Math.abs(ts - s) > 0.05) requestAnimationFrame(anim); else actif = false;
  }
})();

/* Phrase « Quel est votre prochain projet ? » ajustée à la largeur de l'écran */
(function () {
  function ajuster() {
    document.querySelectorAll('.phrase-projet').forEach(function (ph) {
      var inner = ph.querySelector('.pp-in'), lignes = ph.querySelectorAll('.ft');
      var dispo = ph.clientWidth - parseFloat(getComputedStyle(ph).paddingLeft) - parseFloat(getComputedStyle(ph).paddingRight);
      inner.style.fontSize = '100px';
      var deux = innerWidth < 700;
      ph.classList.toggle('deux-lignes', deux);
      var w = deux ? Math.max.apply(null, [].map.call(lignes, function (l) { return l.getBoundingClientRect().width; })) : inner.scrollWidth;
      inner.style.fontSize = Math.max(28, Math.min(64, Math.floor(100 * dispo * 0.98 / w))) + 'px';
    });
  }
  ajuster();
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(ajuster);
  var t; addEventListener('resize', function () { clearTimeout(t); t = setTimeout(ajuster, 120); });
})();

/* Accueil : aperçu des expertises, galerie horizontale, compteurs */
(function () {
  var reduit = matchMedia('(prefers-reduced-motion: reduce)').matches;
  // expertises : l'image suit la ligne survolée ou ciblée au clavier
  var liens = document.querySelectorAll('.xs-liste a'), imgs = document.querySelectorAll('.xs-apercu img');
  function activer(k) { liens.forEach(function (l) { l.classList.toggle('actif', l.dataset.k === k); }); imgs.forEach(function (i) { i.classList.toggle('actif', i.dataset.k === k); }); }
  liens.forEach(function (l) { l.addEventListener('mouseenter', function () { activer(l.dataset.k); }); l.addEventListener('focus', function () { activer(l.dataset.k); }); });

  // réalisations : défilement horizontal libre (flèches, glisser, molette horizontale, doigt)
  var piste = document.querySelector('.xr-piste');
  if (piste) {
    var prec = document.querySelector('.xr-prec'), suiv = document.querySelector('.xr-suiv'), jauge = document.querySelector('.xr-jauge i');
    function pas() { var c = piste.querySelector('.carte'); return c ? c.getBoundingClientRect().width + 22 : 400; }
    function etat() {
      var max = piste.scrollWidth - piste.clientWidth;
      if (prec) prec.disabled = piste.scrollLeft < 4;
      if (suiv) suiv.disabled = piste.scrollLeft > max - 4;
      if (jauge) jauge.style.transform = 'scaleX(' + (max > 0 ? (0.12 + 0.88 * piste.scrollLeft / max) : 1).toFixed(3) + ')';
    }
    if (prec) prec.addEventListener('click', function () { piste.scrollBy({ left: -pas(), behavior: reduit ? 'auto' : 'smooth' }); });
    if (suiv) suiv.addEventListener('click', function () { piste.scrollBy({ left: pas(), behavior: reduit ? 'auto' : 'smooth' }); });
    piste.addEventListener('scroll', function () { requestAnimationFrame(etat); }, { passive: true });
    addEventListener('resize', etat); etat();
    // glisser à la souris (ordinateur), sans bloquer les clics
    var bas = false, x0 = 0, l0 = 0, bouge = false;
    piste.addEventListener('pointerdown', function (e) { if (e.pointerType !== 'mouse' || e.button !== 0) return; bas = true; bouge = false; x0 = e.clientX; l0 = piste.scrollLeft; });
    addEventListener('pointermove', function (e) { if (!bas) return; var d = e.clientX - x0; if (Math.abs(d) > 6) { bouge = true; piste.classList.add('glisse'); } if (bouge) piste.scrollLeft = l0 - d; });
    addEventListener('pointerup', function () { if (!bas) return; bas = false; setTimeout(function () { piste.classList.remove('glisse'); }, 0); });
    piste.addEventListener('click', function (e) { if (bouge) { e.preventDefault(); e.stopPropagation(); bouge = false; } }, true);
    piste.addEventListener('dragstart', function (e) { e.preventDefault(); });
  }

  // compteurs
  var nombres = document.querySelectorAll('[data-compte]');
  if (!reduit && 'IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (es) { es.forEach(function (e) {
      if (!e.isIntersecting) return; io.unobserve(e.target);
      var el = e.target, n = +el.dataset.compte, t0 = null;
      if (n < 2) return;
      (function pas(t) { if (!t0) t0 = t; var p = Math.min(1, (t - t0) / 1100); el.textContent = Math.round(n * (1 - Math.pow(1 - p, 3))) + (el.dataset.suite || ''); if (p < 1) requestAnimationFrame(pas); })(performance.now());
    }); }, { threshold: 0.6 });
    nombres.forEach(function (el) { io.observe(el); });
  }
})();

/* Boutons Appeler : sur téléphone l'appel part directement ; sur ordinateur (ou dans un aperçu
   qui bloque les appels), une fenêtre affiche le numéro, permet de le copier ou d'écrire sur WhatsApp. */
(function () {
  var tactile = matchMedia('(hover: none) and (pointer: coarse)').matches;
  var bloque = window.pcAppelsBloques === true;
  var fen = null;
  function ouvrir(tel, lien) {
    var wa = document.querySelector('a[href^="https://wa.me/"]');
    if (!fen) {
      fen = document.createElement('div'); fen.className = 'appel'; fen.setAttribute('role', 'dialog'); fen.setAttribute('aria-modal', 'true'); fen.setAttribute('aria-labelledby', 'appel-t');
      fen.innerHTML = '<div class="appel-b"><button class="appel-x" type="button" aria-label="Fermer">×</button><p class="appel-s">Appelez-nous</p><a class="appel-n" id="appel-t"></a>' +
        '<p class="appel-h">Si nous sommes en chantier, laissez un message ou écrivez-nous sur WhatsApp : nous vous rappelons rapidement.</p>' +
        '<div class="appel-a"><button class="btn plein appel-c" type="button">Copier le numéro</button><a class="btn ligne appel-w" target="_blank" rel="noopener">WhatsApp</a><a class="btn ligne appel-d">Appeler depuis cet appareil</a></div></div>';
      document.body.appendChild(fen);
      fen.addEventListener('click', function (e) { if (e.target === fen || e.target.closest('.appel-x')) fermer(); });
      fen.querySelector('.appel-c').addEventListener('click', function () {
        var b = this, n = fen.dataset.num;
        function ok() { b.textContent = 'Numéro copié'; setTimeout(function () { b.textContent = 'Copier le numéro'; }, 2200); }
        try { navigator.clipboard.writeText(n).then(ok, function () { secours(n); ok(); }); } catch (e) { secours(n); ok(); }
      });
      document.addEventListener('keydown', function (e) { if (e.key === 'Escape') fermer(); });
    }
    fen.dataset.num = tel;
    var n = fen.querySelector('.appel-n'); n.textContent = tel; n.href = lien;
    fen.querySelector('.appel-d').href = lien;
    var w = fen.querySelector('.appel-w'); if (wa) { w.href = wa.href; w.style.display = ''; } else w.style.display = 'none';
    fen.classList.add('ouvert'); document.documentElement.classList.add('appel-ouvert');
    fen.querySelector('.appel-c').focus();
  }
  function secours(n) { var t = document.createElement('textarea'); t.value = n; t.style.position = 'fixed'; t.style.opacity = '0'; document.body.appendChild(t); t.select(); try { document.execCommand('copy'); } catch (e) {} t.remove(); }
  function fermer() { if (fen) { fen.classList.remove('ouvert'); document.documentElement.classList.remove('appel-ouvert'); } }
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href^="tel:"]');
    if (!a || a.closest('.appel')) return;
    if (tactile && !bloque) return; // le téléphone compose le numéro
    e.preventDefault();
    var num = a.getAttribute('href').replace('tel:', '').replace(/^\+216(\d{2})(\d{3})(\d{3})$/, '+216 $1 $2 $3');
    ouvrir(num, a.getAttribute('href'));
  });
})();

/* Mesure d'audience Google Analytics : chargée seulement après accord du visiteur */
(function () {
  var b = document.querySelector('.cookies'); if (!b) return;
  var id = b.dataset.ga, K = 'pc_mesure';
  function lire() { try { return localStorage.getItem(K); } catch (e) { return null; } }
  function ecrire(v) { try { localStorage.setItem(K, v); } catch (e) {} }
  function charger() {
    if (window.pcGa) return; window.pcGa = true;
    window.dataLayer = window.dataLayer || [];
    window.gtag = function () { window.dataLayer.push(arguments); };
    window.gtag('js', new Date());
    window.gtag('config', id, { cookie_expires: 33696000 });
    var s = document.createElement('script'); s.async = true;
    s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(id);
    document.head.appendChild(s);
  }
  function effacer() {
    var dom = location.hostname.replace(/^www\./, '');
    document.cookie.split(';').forEach(function (c) {
      var n = c.split('=')[0].trim(); if (!/^_ga/.test(n)) return;
      ['', '; domain=' + dom, '; domain=.' + dom].forEach(function (d) { document.cookie = n + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/' + d; });
    });
  }
  var choix = lire();
  if (choix === 'oui') charger(); else if (choix !== 'non') b.hidden = false;
  b.addEventListener('click', function (e) {
    var x = e.target.closest('[data-mesure]'); if (!x) return;
    ecrire(x.dataset.mesure); b.hidden = true;
    if (x.dataset.mesure === 'oui') charger(); else { effacer(); if (window.pcGa) location.reload(); }
  });
  document.querySelectorAll('.gerer-cookies').forEach(function (a) {
    a.addEventListener('click', function (e) { e.preventDefault(); b.hidden = false; b.querySelector('[data-mesure="oui"]').focus(); });
  });
  // contacts : appels, WhatsApp, demandes de devis
  function ev(nom, p) { if (window.pcGa && window.gtag) window.gtag('event', nom, p || {}); }
  document.addEventListener('click', function (e) {
    var a = e.target.closest('a[href]'); if (!a) return;
    var h = a.getAttribute('href');
    if (h.indexOf('tel:') === 0) ev('clic_appel');
    else if (h.indexOf('wa.me') > -1) ev('clic_whatsapp');
    else if (h.indexOf('mailto:') === 0) ev('clic_email');
  });
  var f = document.querySelector('form.form'); if (f) f.addEventListener('submit', function () { ev('generate_lead', { formulaire: 'devis' }); });
})();
