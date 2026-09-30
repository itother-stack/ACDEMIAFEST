<?php
// Public site footer — matches the main AcademiaFest website's animated footer
$h = function ($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };
$waDigits = preg_replace('/[^0-9]/', '', BRAND_WHATSAPP);
$telDigits = '+' . preg_replace('/[^0-9]/', '', BRAND_PHONE);
$mapUrl = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(BRAND_ADDRESS);
?>
<style>
    @keyframes afWave { 0%, 100% { transform: translateX(0); } 50% { transform: translateX(-40px); } }
    @keyframes afGrad { 0% { background-position: 0% 50%; } 100% { background-position: 300% 50%; } }
    @keyframes afShine { 0% { left: -70%; } 60%, 100% { left: 140%; } }
    @keyframes afGlow { 0%, 100% { box-shadow: 0 4px 18px rgba(201,162,39,.35); } 50% { box-shadow: 0 6px 30px rgba(230,191,70,.75); } }
    @keyframes afSpin { to { transform: rotate(360deg); } }
    @keyframes afRing { 0% { transform: scale(1); opacity: .7; } 100% { transform: scale(1.7); opacity: 0; } }

    .af-footer { position: relative; overflow: hidden; margin-top: 60px; color: #a9b8d3; font-family: 'Source Sans 3', sans-serif;
        background: radial-gradient(ellipse at 15% 20%, rgba(201,162,39,.14), transparent 45%),
                    radial-gradient(ellipse at 90% 80%, rgba(61,98,178,.35), transparent 50%),
                    linear-gradient(180deg, #0f1f3d 0%, #0a1630 100%); }
    .af-footer::before { content: ""; position: absolute; inset: 0; pointer-events: none; opacity: .5;
        background-image: radial-gradient(rgba(230,191,70,.18) 1px, transparent 1px); background-size: 26px 26px;
        -webkit-mask-image: linear-gradient(180deg, transparent, #000 40%, transparent); mask-image: linear-gradient(180deg, transparent, #000 40%, transparent); }
    .af-footer-wave { position: relative; z-index: 1; display: block; width: calc(100% + 80px); height: 70px; margin-left: -40px; }
    .af-footer-wave .af-w1 { fill: #f8f9fc; animation: afWave 9s ease-in-out infinite; }
    .af-footer-wave .af-w2 { fill: #c9a227; opacity: .55; animation: afWave 12s ease-in-out infinite reverse; }
    .af-footer-inner { position: relative; z-index: 1; max-width: 1200px; margin: 0 auto; padding: 20px 24px 26px; }

    .af-footer-cta { display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-bottom: 52px;
        padding: 26px 32px; border-radius: 18px; position: relative; overflow: hidden;
        background: linear-gradient(135deg, rgba(255,255,255,.08), rgba(255,255,255,.02)); border: 1px solid rgba(230,191,70,.35);
        backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); box-shadow: 0 18px 40px rgba(0,0,0,.25), inset 0 1px 0 rgba(255,255,255,.08); }
    .af-footer-cta::after { content: ""; position: absolute; top: 0; left: -70%; width: 40%; height: 100%; pointer-events: none;
        background: linear-gradient(100deg, transparent, rgba(230,191,70,.18), transparent); transform: skewX(-18deg); animation: afShine 7s ease-in-out infinite; }
    .af-footer-cta-title { font-family: 'Playfair Display', Georgia, serif; font-size: 24px; font-weight: 700; color: #fff; }
    .af-footer-cta-title::before { content: "\2728  "; }
    .af-footer-cta-sub { color: #a9b8d3; font-size: 15px; margin-top: 4px; }
    .af-footer-cta-btn { position: relative; z-index: 1; display: inline-flex; align-items: center; gap: 8px; padding: 13px 28px; border-radius: 40px;
        background: linear-gradient(135deg, #c9a227, #e6bf46); color: #1a2a4a; font-weight: 700; font-size: 15px; text-decoration: none;
        box-shadow: 0 8px 22px rgba(201,162,39,.35); transition: transform .3s, box-shadow .3s; animation: afGlow 2.6s ease-in-out infinite; }
    .af-footer-cta-btn span { transition: transform .3s; }
    .af-footer-cta-btn:hover { transform: translateY(-3px); box-shadow: 0 14px 30px rgba(201,162,39,.55); }
    .af-footer-cta-btn:hover span { transform: translateX(5px); }

    .af-footer-grid { display: grid; grid-template-columns: 1.4fr 1fr 1fr 1.3fr; gap: 36px; margin-bottom: 40px; }
    .af-footer-col h4 { color: #fff; font-size: 16px; font-weight: 700; margin-bottom: 18px; padding-bottom: 10px; position: relative; font-family: 'Playfair Display', Georgia, serif; }
    .af-footer-col h4::after { content: ""; position: absolute; left: 0; bottom: 0; width: 36px; height: 3px; border-radius: 3px; background: linear-gradient(90deg, #c9a227, #e6bf46); transition: width .4s; }
    .af-footer-col:hover h4::after { width: 64px; }
    .af-footer-logo { display: inline-flex; align-items: center; gap: 12px; text-decoration: none; margin-bottom: 16px; }
    .af-footer-logo svg, .af-footer-logo img { width: 46px; height: 46px; object-fit: contain; transition: transform .8s cubic-bezier(.2,.8,.2,1); }
    .af-footer-logo:hover svg, .af-footer-logo:hover img { transform: rotate(360deg); }
    .af-footer-logo b { display: block; font-family: 'Playfair Display', Georgia, serif; font-size: 24px; color: #fff; line-height: 1; }
    .af-footer-logo small { display: block; font-size: 10.5px; letter-spacing: 2px; text-transform: uppercase; color: #e6bf46; margin-top: 4px; }
    .af-footer-brand p { font-size: 14.5px; line-height: 1.75; margin-bottom: 20px; max-width: 320px; }
    .af-footer-social { display: flex; gap: 10px; }
    .af-footer-social a { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #e6bf46;
        background: rgba(255,255,255,.06); border: 1px solid rgba(230,191,70,.25); transition: transform .3s, background .3s, color .3s, box-shadow .3s; text-decoration: none; }
    .af-footer-social a:hover { transform: translateY(-5px) rotate(-6deg); color: #fff; }
    .af-footer-social .af-s-wa:hover { background: #25d366; box-shadow: 0 10px 22px rgba(37,211,102,.4); }
    .af-footer-social .af-s-call:hover { background: #2d4a8a; box-shadow: 0 10px 22px rgba(45,74,138,.5); }
    .af-footer-social .af-s-mail:hover { background: #c9a227; color: #1a2a4a; box-shadow: 0 10px 22px rgba(201,162,39,.45); }
    .af-footer-social .af-s-map:hover { background: #e0533d; box-shadow: 0 10px 22px rgba(224,83,61,.4); }
    .af-footer-link { display: flex; align-items: center; gap: 6px; color: #a9b8d3; text-decoration: none; font-size: 14.5px; padding: 5px 0; transition: color .25s; }
    .af-footer-link svg { color: #c9a227; opacity: 0; margin-left: -18px; transition: opacity .25s, margin .25s; }
    .af-footer-link:hover { color: #fff; }
    .af-footer-link:hover svg { opacity: 1; margin-left: 0; }
    .af-footer-contact { display: flex; align-items: center; gap: 12px; color: #d6deec; text-decoration: none; font-size: 14px; margin-bottom: 14px; line-height: 1.35; }
    .af-footer-contact i { flex-shrink: 0; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-style: normal;
        color: #e6bf46; background: rgba(230,191,70,.1); border: 1px solid rgba(230,191,70,.3); transition: transform .3s, background .3s, color .3s; }
    .af-footer-contact small { display: block; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; color: #7f8fb0; }
    .af-footer-contact:hover { color: #fff; }
    .af-footer-contact:hover i { transform: scale(1.12); background: linear-gradient(135deg, #c9a227, #e6bf46); color: #1a2a4a; }

    .af-footer-bottom { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; padding-top: 20px; font-size: 13px;
        border-top: 1px solid transparent; border-image: linear-gradient(90deg, transparent, rgba(230,191,70,.45), transparent) 1; }
    .af-footer-tag b { display: inline-block; color: #e6bf46; animation: afSpin 6s linear infinite; }

    .af-wa-fab { position: fixed; bottom: 24px; right: 24px; width: 56px; height: 56px; border-radius: 50%; background: #25d366; color: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 26px; box-shadow: 0 4px 20px rgba(37,211,102,.4); z-index: 200;
        text-decoration: none; transition: transform .3s; }
    .af-wa-fab:hover { transform: scale(1.12) rotate(-8deg); }
    .af-wa-fab::before, .af-wa-fab::after { content: ""; position: absolute; inset: 0; border-radius: 50%; background: #25d366; z-index: -1; animation: afRing 2s ease-out infinite; }
    .af-wa-fab::after { animation-delay: 1s; }

    @media (max-width: 900px) {
        .af-footer-grid { grid-template-columns: 1fr 1fr; gap: 30px; }
        .af-footer-brand { grid-column: 1 / -1; }
        .af-footer-col:last-child { grid-column: 1 / -1; display: grid; grid-template-columns: 1fr 1fr; column-gap: 18px; }
        .af-footer-col:last-child h4 { grid-column: 1 / -1; justify-self: start; }
        .af-footer-bottom { justify-content: center; text-align: center; flex-direction: column; }
    }
    @media (max-width: 560px) {
        .af-footer-wave { height: 44px; }
        .af-footer-inner { padding: 12px 16px 90px; }
        .af-footer-cta { padding: 22px 20px; text-align: center; justify-content: center; margin-bottom: 36px; }
        .af-footer-cta-title { font-size: 20px; }
        .af-footer-grid { gap: 26px 18px; }
        .af-footer-col:last-child { grid-template-columns: 1fr; }
        .af-wa-fab { width: 44px; height: 44px; bottom: 14px; right: 14px; font-size: 20px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .af-footer *, .af-footer *::before, .af-footer *::after, .af-wa-fab::before, .af-wa-fab::after { animation: none !important; }
    }
</style>

<footer class="af-footer">
    <svg class="af-footer-wave" viewBox="0 0 1440 90" preserveAspectRatio="none" aria-hidden="true">
        <path class="af-w2" d="M0,52 C300,95 520,18 760,50 C1000,82 1220,15 1440,48 L1440,0 L0,0 Z"></path>
        <path class="af-w1" d="M0,40 C240,85 480,5 720,38 C960,72 1200,8 1440,36 L1440,0 L0,0 Z"></path>
    </svg>
    <div class="af-footer-inner">
        <div class="af-footer-cta">
            <div>
                <div class="af-footer-cta-title">Didn't finish your registration?</div>
                <div class="af-footer-cta-sub">It only takes a minute — get your digital admit card on WhatsApp instantly.</div>
            </div>
            <a class="af-footer-cta-btn" href="/register/">Register Free <span>&rarr;</span></a>
        </div>

        <div class="af-footer-grid">
            <div class="af-footer-col af-footer-brand">
                <a class="af-footer-logo" href="/">
                    <?= brand_logo_svg(46) ?>
                    <span><b><?= $h(BRAND_NAME) ?></b><small><?= $h(BRAND_TAGLINE) ?></small></span>
                </a>
                <p>India's trusted offline education fair connecting students, parents, colleges and universities — always free for students.</p>
                <div class="af-footer-social">
                    <a href="https://wa.me/<?= $h($waDigits) ?>" target="_blank" rel="noopener" aria-label="WhatsApp" class="af-s-wa"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5z"/></svg></a>
                    <a href="tel:<?= $h($telDigits) ?>" aria-label="Call" class="af-s-call"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.18 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.1 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg></a>
                    <a href="mailto:<?= $h(BRAND_EMAIL) ?>" aria-label="Email" class="af-s-mail"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></a>
                    <a href="<?= $h($mapUrl) ?>" target="_blank" rel="noopener" aria-label="Location" class="af-s-map"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></a>
                </div>
            </div>
            <div class="af-footer-col">
                <h4>Quick Links</h4>
                <a class="af-footer-link" href="/"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg><span>Home</span></a>
                <a class="af-footer-link" href="/#events"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg><span>Upcoming Events</span></a>
                <a class="af-footer-link" href="/#about"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg><span>About Us</span></a>
                <a class="af-footer-link" href="/#founder"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg><span>Our Founder</span></a>
                <a class="af-footer-link" href="/#contact"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg><span>Contact</span></a>
            </div>
            <div class="af-footer-col">
                <h4>For Students</h4>
                <a class="af-footer-link" href="/register/"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg><span>Register Free</span></a>
                <a class="af-footer-link" href="/register/"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg><span>Get Admit Card</span></a>
                <a class="af-footer-link" href="/#events"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg><span>Find a Fair Near You</span></a>
                <a class="af-footer-link" href="/#contact"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg><span>College Partnership</span></a>
            </div>
            <div class="af-footer-col">
                <h4>Get in Touch</h4>
                <a class="af-footer-contact" href="tel:<?= $h($telDigits) ?>"><i><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.18 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.1 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg></i><span><small>Call us</small><?= $h(BRAND_PHONE) ?></span></a>
                <a class="af-footer-contact" href="https://wa.me/<?= $h($waDigits) ?>" target="_blank" rel="noopener"><i><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5z"/></svg></i><span><small>WhatsApp</small><?= $h(BRAND_PHONE) ?></span></a>
                <a class="af-footer-contact" href="mailto:<?= $h(BRAND_EMAIL) ?>"><i><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></i><span><small>Email</small><?= $h(BRAND_EMAIL) ?></span></a>
                <a class="af-footer-contact" href="<?= $h($mapUrl) ?>" target="_blank" rel="noopener"><i><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></i><span><small>Visit us</small><?= $h(BRAND_ADDRESS) ?></span></a>
            </div>
        </div>

        <div class="af-footer-bottom">
            <span>&copy; <?= date('Y') ?> <?= $h(BRAND_NAME) ?>. All rights reserved.</span>
            <span class="af-footer-tag">India's Trusted Education Event Platform <b>&#9733;</b></span>
        </div>
    </div>
</footer>
<a class="af-wa-fab" href="https://wa.me/<?= $h($waDigits) ?>" target="_blank" rel="noreferrer" aria-label="Chat on WhatsApp">💬</a>
</body>
</html>
