// AcademiaFest — site-wide animations. Adds classes to the React-rendered page (styles in site-fx.css).
(function () {
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var each = function (list, fn) { Array.prototype.forEach.call(list || [], fn); };

  // ---------- Scroll reveal ----------
  function show(el) {
    if (el.dataset.fxShown) return;
    el.dataset.fxShown = '1';
    if (io) io.unobserve(el);
    el.classList.add('fx-in');
    if (el.classList.contains('fx-line')) el.classList.add('fx-lined');
    if (el.dataset.fxCount) countUp(el);
    // Hand the element back to its own hover styles once it has appeared
    if (el.classList.contains('fx-reveal')) {
      var wait = (parseFloat(el.style.transitionDelay) || 0) * 1000 + 950;
      setTimeout(function () { el.classList.remove('fx-reveal', 'fx-left', 'fx-zoom', 'fx-in'); el.style.transitionDelay = ''; }, wait);
    }
  }
  var io = 'IntersectionObserver' in window && !reduce ? new IntersectionObserver(function (entries) {
    entries.forEach(function (en) { if (en.isIntersecting) show(en.target); });
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }) : null;
  var watched = [];
  // Fast scrolling can jump over an element without it ever being "visible": show everything above the fold line
  function catchUp() {
    if (!watched.length) return;
    var limit = window.innerHeight;
    watched = watched.filter(function (el) {
      if (el.dataset.fxShown || !document.body.contains(el)) return false;
      if (el.getBoundingClientRect().top < limit) { show(el); return false; }
      return true;
    });
  }

  function watch(el) {
    if (io) { io.observe(el); watched.push(el); }
    else show(el);
  }
  function reveal(el, delay, variant) {
    if (!el || el.dataset.fxR) return;
    el.dataset.fxR = '1';
    if (reduce) return;
    el.classList.add('fx-reveal');
    if (variant) el.classList.add(variant);
    if (delay) el.style.transitionDelay = delay + 's';
    watch(el);
  }
  function revealGroup(list, step, variant) {
    each(list, function (el, i) { reveal(el, Math.min(i, 8) * (step || 0.1), variant); });
  }

  // ---------- Number count-up (50,000+ / 10+ / 100%) ----------
  function countUp(el) {
    if (el.dataset.fxDone) return;
    el.dataset.fxDone = '1';
    var m = el.textContent.trim().match(/^([\d,]+)(.*)$/);
    if (!m || reduce) return;
    var end = parseInt(m[1].replace(/,/g, ''), 10), comma = m[1].indexOf(',') > -1, suffix = m[2], t0 = null;
    var fmt = function (n) { return (comma ? n.toLocaleString('en-IN') : String(n)) + suffix; };
    function step(t) {
      if (!t0) t0 = t;
      var p = Math.min((t - t0) / 1800, 1);
      el.textContent = fmt(Math.round(end * (1 - Math.pow(1 - p, 3))));
      if (p < 1) requestAnimationFrame(step);
    }
    el.textContent = fmt(0);
    setTimeout(function () { requestAnimationFrame(step); }, 500);
  }
  function counter(el) {
    if (!el || el.dataset.fxCount) return;
    el.dataset.fxCount = '1';
    watch(el);
  }

  // ---------- Global chrome: progress bar, back-to-top, sticky nav ----------
  var bar = document.createElement('div'); bar.className = 'fx-progress';
  var top = document.createElement('button'); top.className = 'fx-top'; top.type = 'button'; top.setAttribute('aria-label', 'Back to top'); top.innerHTML = '&#8593;';
  top.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' }); });
  function onScroll() {
    var h = document.documentElement.scrollHeight - window.innerHeight;
    bar.style.transform = 'scaleX(' + (h > 0 ? Math.min(window.scrollY / h, 1) : 0) + ')';
    top.classList.toggle('fx-show', window.scrollY > 600);
    var nav = document.querySelector('nav.fx-nav');
    if (nav) nav.classList.toggle('fx-scrolled', window.scrollY > 10);
    catchUp();
  }

  // ---------- Heroes ----------
  function enhanceHero(hero) {
    if (hero.dataset.fx) return;
    hero.dataset.fx = '1';
    if (!reduce) for (var i = 0; i < 16; i++) {
      var s = document.createElement('span');
      s.className = 'fx-star-dot';
      s.style.left = (Math.random() * 100) + '%';
      s.style.top = (Math.random() * 100) + '%';
      s.style.animationDelay = (Math.random() * 3) + 's';
      hero.appendChild(s);
    }
    var h1 = hero.querySelector('h1');
    if (h1 && h1.parentElement && !reduce) h1.parentElement.classList.add('fx-info');
    each(hero.querySelectorAll('.stat-n'), counter);
  }

  // ---------- Founder page ----------
  function enhanceFounder(page) {
    var photo = page.querySelector('.founder-photo');
    if (!photo || photo.dataset.fx) return;
    photo.dataset.fx = '1';
    page.classList.add('fx-founder');
    photo.parentElement.classList.add('fx-photo-wrap');
    var info = photo.parentElement.nextElementSibling;
    if (info) {
      var h1 = info.querySelector('h1');
      if (h1) h1.classList.add('fx-name');
      var stats = info.lastElementChild;
      if (stats) each(stats.children, function (st) { st.classList.add('fx-stat'); counter(st.firstElementChild); });
    }
    var quote = page.querySelector('.founder-quote');
    if (quote) reveal(quote.parentElement);
    var chips = page.querySelectorAll('.stack-mobile span');
    each(chips, function (c) { c.classList.add('fx-chip'); });
    revealGroup(chips, 0.05, 'fx-zoom');
    var journey = Array.prototype.filter.call(page.querySelectorAll('h2'), function (h) { return /Professional Journey/i.test(h.textContent); })[0];
    var list = journey && journey.nextElementSibling;
    if (list) {
      revealGroup(list.children, 0.12, 'fx-left');
      var dot = list.firstElementChild && list.firstElementChild.firstElementChild && list.firstElementChild.firstElementChild.firstElementChild;
      if (dot) dot.classList.add('fx-star');
    }
  }

  // ---------- Everything else on the page ----------
  function enhance() {
    var root = document.getElementById('root');
    if (!root) return;
    if (!document.body.contains(bar)) { document.body.appendChild(bar); document.body.appendChild(top); }

    var nav = root.querySelector('nav');
    if (nav && !nav.classList.contains('fx-nav')) nav.classList.add('fx-nav');

    // Page transition when the page (the element after the navbar) is swapped
    var page = nav ? nav.nextElementSibling : null;
    if (page && page.tagName !== 'FOOTER' && !page.dataset.fxPage) {
      page.dataset.fxPage = '1';
      if (!reduce) page.classList.add('fx-page');
    }

    each(root.querySelectorAll('.hero'), enhanceHero);
    each(root.querySelectorAll('.hero'), buildHeroSlider);
    if (page) enhanceFounder(page);

    // Section titles with growing gold underline
    each(root.querySelectorAll('.section-title'), function (t) {
      if (t.dataset.fxT) return;
      t.dataset.fxT = '1';
      t.classList.add('fx-line');
      if (getComputedStyle(t).textAlign === 'center') t.classList.add('fx-center');
      reveal(t);
      if (t.dataset.fxR && reduce) t.classList.add('fx-lined');
      if (!t.classList.contains('fx-reveal')) watch(t);
    });
    each(root.querySelectorAll('.section-sub, .h2-sec'), function (el) { reveal(el, 0.1); });

    // Card grids: staggered by position inside their grid
    each(root.querySelectorAll('.grid-3, .grid-2, .grid-4, .events-grid, .gallery-grid, .streams-grid'), function (g) {
      if (g.classList.contains('features-grid')) return;
      revealGroup(g.children, 0.1, g.classList.contains('gallery-grid') ? 'fx-zoom' : null);
    });
    each(root.querySelectorAll('.features-grid'), buildMarquee);
    each(root.querySelectorAll('.stack-mobile'), function (g) {
      if (g.closest('.hero')) return;
      revealGroup(g.children, 0.15);
    });
    each(root.querySelectorAll('.stack-mobile .card'), function (c, i) { reveal(c, (i % 4) * 0.1); });
    each(root.querySelectorAll('.sec li'), function (li, i) { reveal(li, (i % 6) * 0.08, 'fx-left'); });
    each(root.querySelectorAll('.box-pad'), function (b) { if (!b.closest('.stack-mobile')) reveal(b); });
    each(root.querySelectorAll('.hw-tabs, .hw-visual'), function (el) { reveal(el, 0.1); });
    each(root.querySelectorAll('.stat-n'), counter);

    // Testimonial stars
    each(root.querySelectorAll('.card div'), function (d) {
      if (!d.dataset.fxS && d.childElementCount === 0 && /^★+$/.test(d.textContent.trim())) { d.dataset.fxS = '1'; d.classList.add('fx-stars'); }
    });

    // Gold call-to-action band
    each(root.querySelectorAll('.sec'), function (s) {
      if (s.dataset.fxB) return;
      s.dataset.fxB = '1';
      if (/201,\s*162,\s*39/.test(getComputedStyle(s).backgroundImage)) s.classList.add('fx-gold-band');
    });

    // Footer
    var footer = root.querySelector('footer');
    if (footer) buildFooter(footer);
  }

  // ---------- "Why AcademiaFest" cards: one scrolling line ----------
  function buildMarquee(grid) {
    if (grid.dataset.fxM) return;
    grid.dataset.fxM = '1';
    var cards = Array.prototype.slice.call(grid.children);
    if (cards.length < 2) return;
    reveal(grid, 0, 'fx-zoom');

    var track = document.createElement('div');
    track.className = 'fx-mq-track';
    // Two copies back to back, so scrolling one full width loops seamlessly
    cards.concat(cards).forEach(function (c) {
      var clone = c.cloneNode(true);
      clone.classList.add('fx-mq-item');
      track.appendChild(clone);
    });
    grid.innerHTML = '';
    grid.classList.add('fx-mq');
    grid.appendChild(track);

    if (reduce) return;
    var paused = false;
    grid.addEventListener('mouseenter', function () { paused = true; });
    grid.addEventListener('mouseleave', function () { paused = false; });
    grid.addEventListener('touchstart', function () { paused = true; }, { passive: true });
    grid.addEventListener('touchend', function () { setTimeout(function () { paused = false; }, 1500); }, { passive: true });

    var x = 0, last = null, half = 0;
    function measure() { half = track.scrollWidth / 2 || 1; }
    measure();
    window.addEventListener('resize', measure);
    function step(t) {
      if (!document.body.contains(grid)) return;
      if (last === null) last = t;
      var dt = t - last; last = t;
      if (!paused) {
        x -= dt * 0.045;                 // px per ms ≈ 45px/s
        if (-x >= half) x += half;
        track.style.transform = 'translateX(' + x + 'px)';
      }
      requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }

  // ---------- Home hero: sliding background images ----------
  // Images come from Admin > Homepage Banners. Until the admin adds some,
  // these stock photos are shown instead.
  var STOCK_SLIDES = [
    'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=1920&q=70',
    'https://images.unsplash.com/photo-1427504494785-3a9ca7044f45?auto=format&fit=crop&w=1920&q=70',
    'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=1920&q=70'
  ];
  var SLIDE_MS = 5000;
  var bannersPromise = null;
  function getBanners() {
    if (!bannersPromise) {
      bannersPromise = fetch('/api/home.php').then(function (r) { return r.json(); })
        .then(function (d) { return (d && d.banners && d.banners.length) ? d.banners : STOCK_SLIDES; })
        .catch(function () { return STOCK_SLIDES; });
    }
    return bannersPromise;
  }

  function buildHeroSlider(hero) {
    if (hero.dataset.fxSlider || !hero.querySelector('.stats-grid')) return;
    hero.dataset.fxSlider = '1';
    getBanners().then(function (slides) { renderHeroSlider(hero, slides); });
  }

  function renderHeroSlider(hero, slides) {
    if (!document.body.contains(hero) || hero.dataset.fxSliderBuilt) return;
    hero.dataset.fxSliderBuilt = '1';
    hero.classList.add('fx-has-slider');

    var slider = document.createElement('div');
    slider.className = 'fx-slider';
    slider.setAttribute('aria-hidden', 'true');
    var track = document.createElement('div');
    track.className = 'fx-track';
    // Last slide is a copy of the first so the loop slides on seamlessly
    slides.concat([slides[0]]).forEach(function (src) {
      var slide = document.createElement('div');
      slide.className = 'fx-slide';
      var img = document.createElement('img');
      img.alt = '';
      img.decoding = 'async';
      img.src = src;
      slide.appendChild(img);
      track.appendChild(slide);
    });
    slider.appendChild(track);
    var shade = document.createElement('div');
    shade.className = 'fx-slider-shade';
    slider.appendChild(shade);
    hero.insertBefore(slider, hero.firstChild);

    var dots = document.createElement('div');
    dots.className = 'fx-dots';
    slides.forEach(function (_, i) {
      var d = document.createElement('button');
      d.type = 'button';
      d.setAttribute('aria-label', 'Show image ' + (i + 1));
      d.addEventListener('click', function () { go(i); restart(); });
      dots.appendChild(d);
    });
    hero.appendChild(dots);

    var n = slides.length, idx = 0, timer = null;
    function paint() {
      each(track.children, function (s, i) { s.classList.toggle('fx-active', i === idx || (idx === n && i === 0)); });
      each(dots.children, function (d, i) { d.classList.toggle('fx-on', i === idx % n); });
    }
    function go(i, instant) {
      idx = i;
      track.style.transition = instant ? 'none' : '';
      track.style.transform = 'translateX(' + (-100 * idx) + '%)';
      paint();
    }
    track.addEventListener('transitionend', function (e) {
      if (e.target !== track || idx !== n) return;
      go(0, true);                      // jump from the copy back to the real first slide
      void track.offsetWidth;           // flush so the next slide animates again
      track.style.transition = '';
    });
    function next() { if (document.body.contains(hero)) go(idx + 1); else clearInterval(timer); }
    function restart() { clearInterval(timer); if (!reduce && n > 1) timer = setInterval(next, SLIDE_MS); }
    go(0, true);
    restart();
  }

  // ---------- Footer (replaces the plain footer with a richer one) ----------
  var BRAND = {
    email: 'info@academiafest.com',
    phone: '+91 90153 91313',
    tel: '+919015391313',
    whatsapp: '919015391313',
    address: 'Laxmi Niwas, Baily Road, Patna',
    map: 'https://www.google.com/maps/search/?api=1&query=Laxmi+Niwas+Baily+Road+Patna'
  };
  var ICON = {
    mail: '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
    phone: '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.18 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.1 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>',
    pin: '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
    chat: '<path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5z"/>',
    arrow: '<polyline points="9 18 15 12 9 6"/>'
  };
  function svg(name, size) {
    return '<svg width="' + (size || 18) + '" height="' + (size || 18) + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' + ICON[name] + '</svg>';
  }
  function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }

  function buildFooter(footer) {
    if (footer.dataset.fxFt) return;
    footer.dataset.fxFt = '1';
    footer.classList.add('fx-ft');
    var logo = document.querySelector('nav svg, nav img');
    var logoHtml = logo ? logo.outerHTML : '';
    var link = function (href, text, ext) {
      return '<a class="fx-ft-link" href="' + href + '"' + (ext ? ' target="_blank" rel="noopener"' : '') + '>' + svg('arrow', 14) + '<span>' + esc(text) + '</span></a>';
    };
    var wrap = document.createElement('div');
    wrap.className = 'fx-ft-wrap';
    wrap.innerHTML =
      '<svg class="fx-ft-wave" viewBox="0 0 1440 90" preserveAspectRatio="none" aria-hidden="true">' +
        '<path class="fx-w2" d="M0,52 C300,95 520,18 760,50 C1000,82 1220,15 1440,48 L1440,0 L0,0 Z"/>' +
        '<path class="fx-w1" d="M0,40 C240,85 480,5 720,38 C960,72 1200,8 1440,36 L1440,0 L0,0 Z"/>' +
      '</svg>' +
      '<div class="fx-ft-inner">' +
        '<div class="fx-ft-cta">' +
          '<div><div class="fx-ft-cta-title">Your next education fair is waiting</div>' +
          '<div class="fx-ft-cta-sub">Register free, get your digital admit card on WhatsApp in seconds.</div></div>' +
          '<a class="fx-ft-cta-btn" href="/register/">Register Free <span>&rarr;</span></a>' +
        '</div>' +
        '<div class="fx-ft-grid">' +
          '<div class="fx-ft-col fx-ft-brand">' +
            '<a class="fx-ft-logo" href="#home">' + logoHtml + '<span><b>AcademiaFest</b><small>Education Events</small></span></a>' +
            '<p>India\'s trusted offline education fair connecting students, parents, colleges and universities &mdash; always free for students.</p>' +
            '<div class="fx-ft-social">' +
              '<a href="https://wa.me/' + BRAND.whatsapp + '" target="_blank" rel="noopener" aria-label="WhatsApp" class="fx-s-wa">' + svg('chat') + '</a>' +
              '<a href="tel:' + BRAND.tel + '" aria-label="Call" class="fx-s-call">' + svg('phone') + '</a>' +
              '<a href="mailto:' + BRAND.email + '" aria-label="Email" class="fx-s-mail">' + svg('mail') + '</a>' +
              '<a href="' + BRAND.map + '" target="_blank" rel="noopener" aria-label="Location" class="fx-s-map">' + svg('pin') + '</a>' +
            '</div>' +
          '</div>' +
          '<div class="fx-ft-col"><h4>Quick Links</h4>' +
            link('#home', 'Home') + link('#events', 'Upcoming Events') + link('#about', 'About Us') + link('#founder', 'Our Founder') + link('#contact', 'Contact') +
          '</div>' +
          '<div class="fx-ft-col"><h4>For Students</h4>' +
            link('/register/', 'Register Free') + link('/register/', 'Get Admit Card') + link('#events', 'Find a Fair Near You') + link('#contact', 'College Partnership') +
          '</div>' +
          '<div class="fx-ft-col"><h4>Get in Touch</h4>' +
            '<a class="fx-ft-contact" href="tel:' + BRAND.tel + '"><i>' + svg('phone', 16) + '</i><span><small>Call us</small>' + esc(BRAND.phone) + '</span></a>' +
            '<a class="fx-ft-contact" href="https://wa.me/' + BRAND.whatsapp + '" target="_blank" rel="noopener"><i>' + svg('chat', 16) + '</i><span><small>WhatsApp</small>' + esc(BRAND.phone) + '</span></a>' +
            '<a class="fx-ft-contact" href="mailto:' + BRAND.email + '"><i>' + svg('mail', 16) + '</i><span><small>Email</small>' + esc(BRAND.email) + '</span></a>' +
            '<a class="fx-ft-contact" href="' + BRAND.map + '" target="_blank" rel="noopener"><i>' + svg('pin', 16) + '</i><span><small>Visit us</small>' + esc(BRAND.address) + '</span></a>' +
          '</div>' +
        '</div>' +
        '<div class="fx-ft-bottom">' +
          '<span>&copy; ' + new Date().getFullYear() + ' AcademiaFest. All rights reserved.</span>' +
          '<span class="fx-ft-tag">India\'s Trusted Education Event Platform <b>&#9733;</b></span>' +
        '</div>' +
      '</div>';
    footer.appendChild(wrap);
    reveal(wrap.querySelector('.fx-ft-cta'), 0, 'fx-zoom');
    revealGroup(wrap.querySelectorAll('.fx-ft-col'), 0.12);
    reveal(wrap.querySelector('.fx-ft-bottom'), 0.3);
  }

  var queued = false;
  function schedule() {
    if (queued) return;
    queued = true;
    requestAnimationFrame(function () { queued = false; enhance(); onScroll(); });
  }

  function start() {
    enhance();
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    var root = document.getElementById('root');
    if (root) new MutationObserver(schedule).observe(root, { childList: true, subtree: true });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
})();
