import { useState, useEffect } from "react";

// ─── BACKEND (PHP) ────────────────────────────────────────────────────────────
// Events and gallery photos are managed in the admin panel (/admin/)
const API_URL = "/api/home.php";
const REGISTER_URL = "/register/";
const ADMIN_URL = "/admin/";

// ─── UTILS ───────────────────────────────────────────────────────────────────
const genId = () => "AF" + Date.now().toString(36).toUpperCase() + Math.random().toString(36).slice(2,5).toUpperCase();
const fmtDate = d => { if (!d || d === "TBD") return "TBD"; const dt = new Date(d); return isNaN(dt) ? d : dt.toLocaleDateString("en-IN", { day: "numeric", month: "short", year: "numeric" }); };

function AcademiaLogo({ size = 72 }) {
  const w = size;
  const h = Math.round(size * (141 / 158)); // logo-icon.png is 158x141
  return <img src="/logo-icon.png" width={w} height={h} alt="AcademiaFest logo" style={{ display: "inline-block", width: w, height: h, objectFit: "contain" }} />;
}
function AcademiaLogoOld({ size = 72, color = "#c9a227" }) {
  const w = size;
  const h = size * 1.1;
  // Viewbox: 0 0 100 110
  // The logo is an interlocked geometric "A" made of overlapping angular paths.
  // Drawn layer-by-layer to simulate the woven over/under crossing effect.
  const s = color;
  const sw = 5.5; // strokeWidth
  const bg = "white"; // mask color for under-crossing effect
  const mw = sw + 2; // mask width (slightly thicker to cover cleanly)
  return (
    <svg viewBox="0 0 100 110" width={w} height={h} fill="none" strokeLinecap="square" strokeLinejoin="miter">
      {/* ── OUTER TRIANGLE (large A frame) ── */}
      {/* Left leg: top-center → bottom-left */}
      <line x1="50" y1="5" x2="6" y2="86" stroke={s} strokeWidth={sw}/>
      {/* Right leg: top-center → bottom-right */}
      <line x1="50" y1="5" x2="94" y2="86" stroke={s} strokeWidth={sw}/>
      {/* Bottom bar */}
      <line x1="6" y1="86" x2="94" y2="86" stroke={s} strokeWidth={sw}/>

      {/* ── INNER A (smaller, sits inside, offset lower) ── */}
      {/* Mask the crossings so inner lines appear to go UNDER outer lines */}
      {/* Left inner leg bottom section (below crossing) */}
      <line x1="22" y1="86" x2="36" y2="62" stroke={s} strokeWidth={sw}/>
      {/* Right inner leg bottom section (below crossing) */}
      <line x1="78" y1="86" x2="64" y2="62" stroke={s} strokeWidth={sw}/>

      {/* White masks at the cross-over points (simulate "under" weave) */}
      {/* Left crossing: inner-left leg crosses outer-left leg at ~(32,68) */}
      <line x1="26" y1="76" x2="40" y2="58" stroke={bg} strokeWidth={mw}/>
      {/* Right crossing: inner-right leg crosses outer-right leg at ~(68,68) */}
      <line x1="74" y1="76" x2="60" y2="58" stroke={bg} strokeWidth={mw}/>

      {/* Left inner leg top section (above crossing → goes OVER outer) */}
      <line x1="36" y1="62" x2="50" y2="38" stroke={s} strokeWidth={sw}/>
      {/* Right inner leg top section */}
      <line x1="64" y1="62" x2="50" y2="38" stroke={s} strokeWidth={sw}/>

      {/* ── HORIZONTAL CROSSBAR of inner A ── */}
      {/* Mask where crossbar goes under legs */}
      <line x1="29" y1="74" x2="71" y2="74" stroke={bg} strokeWidth={mw}/>
      {/* Crossbar itself */}
      <line x1="33" y1="74" x2="67" y2="74" stroke={s} strokeWidth={sw}/>

      {/* ── BOTTOM INNER BAR (floor of inner A) ── */}
      <line x1="22" y1="86" x2="78" y2="86" stroke={s} strokeWidth={sw}/>

      {/* Re-draw outer bottom bar on top so it appears over inner bottom */}
      {/* White mask over inner bottom center */}
      <line x1="38" y1="86" x2="62" y2="86" stroke={bg} strokeWidth={mw}/>
      {/* Outer bottom re-draw */}
      <line x1="6" y1="86" x2="38" y2="86" stroke={s} strokeWidth={sw}/>
      <line x1="62" y1="86" x2="94" y2="86" stroke={s} strokeWidth={sw}/>
    </svg>
  );
}

// ─── APP STATE ────────────────────────────────────────────────────────────────
// Pages that can be opened with a link such as /#events (used by the registration page menu)
const PUBLIC_PAGES = ["home", "events", "about", "founder", "contact"];
const pageFromHash = () => {
  const h = (window.location.hash || "").replace("#", "").toLowerCase();
  return PUBLIC_PAGES.includes(h) ? h : "home";
};

export default function App() {
  const [page, setPage] = useState(pageFromHash);
  const [pageParams, setPageParams] = useState({});
  const [events, setEvents] = useState([]);
  const [gallery, setGallery] = useState([]);
  const [loaded, setLoaded] = useState(false);
  const [mobileMenu, setMobileMenu] = useState(false);

  useEffect(() => {
    fetch(API_URL, { headers: { Accept: "application/json" } })
      .then(r => r.ok ? r.json() : Promise.reject(r.status))
      .then(d => { setEvents(d.events || []); setGallery(d.gallery || []); })
      .catch(err => console.error("Could not load events/gallery:", err))
      .finally(() => setLoaded(true));
  }, []);

  // Register -> PHP registration form (/register/), Admin -> PHP admin panel (/admin/)
  const nav = (p, params = {}) => {
    if (p === "register") {
      window.location.href = REGISTER_URL + (params.eventId ? "?event=" + encodeURIComponent(params.eventId) : "");
      return;
    }
    if (["admin", "admin-login", "agent", "agent-login"].includes(p)) {
      window.location.href = ADMIN_URL;
      return;
    }
    setPage(p); setPageParams(params); setMobileMenu(false); window.scrollTo(0, 0);
    const hash = p === "home" ? "" : "#" + p;
    if (window.location.hash !== hash) window.history.pushState(null, "", window.location.pathname + hash);
  };

  // Browser back/forward buttons and /#page links
  useEffect(() => {
    const onHash = () => { setPage(pageFromHash()); setMobileMenu(false); window.scrollTo(0, 0); };
    window.addEventListener("popstate", onHash);
    window.addEventListener("hashchange", onHash);
    return () => { window.removeEventListener("popstate", onHash); window.removeEventListener("hashchange", onHash); };
  }, []);

  const ctx = { page, nav, events, gallery, loaded, pageParams };

  const isAdmin = false;
  const isAgent = false;

  return (
    <div style={{ fontFamily: "'Georgia', 'Times New Roman', serif", minHeight: "100vh", background: "#f8f9fc", color: "#1a2a4a" }}>
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Source+Sans+3:wght@300;400;600&display=swap');
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Source Sans 3', sans-serif; }
        .display { font-family: 'Playfair Display', Georgia, serif; }
        .btn-primary { background: linear-gradient(135deg, #1a2a4a 0%, #2d4a8a 100%); color: white; border: none; padding: 12px 28px; border-radius: 6px; font-size: 15px; font-weight: 600; cursor: pointer; transition: opacity .2s, transform .1s; font-family: 'Source Sans 3', sans-serif; }
        .btn-primary:hover { opacity: .9; transform: translateY(-1px); }
        .btn-gold { background: linear-gradient(135deg, #c9a227 0%, #e6bf46 100%); color: #1a2a4a; border: none; padding: 12px 28px; border-radius: 6px; font-size: 15px; font-weight: 600; cursor: pointer; transition: opacity .2s, transform .1s; font-family: 'Source Sans 3', sans-serif; }
        .btn-gold:hover { opacity: .9; transform: translateY(-1px); }
        .btn-outline { background: transparent; color: #1a2a4a; border: 2px solid #1a2a4a; padding: 10px 24px; border-radius: 6px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all .2s; font-family: 'Source Sans 3', sans-serif; }
        .btn-outline:hover { background: #1a2a4a; color: white; }
        .btn-sm { padding: 7px 16px; font-size: 13px; }
        .btn-danger { background: #c0392b; color: white; border: none; padding: 7px 14px; border-radius: 5px; cursor: pointer; font-size: 13px; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 16px rgba(26,42,74,.08); overflow: hidden; }
        .input-field { width: 100%; padding: 10px 14px; border: 1.5px solid #dde3ef; border-radius: 7px; font-size: 15px; font-family: 'Source Sans 3', sans-serif; outline: none; transition: border-color .2s; background: white; color: #1a2a4a; }
        .input-field:focus { border-color: #2d4a8a; }
        .label { display: block; font-size: 13px; font-weight: 600; color: #4a5568; margin-bottom: 5px; letter-spacing: .3px; }
        .section-title { font-family: 'Playfair Display', serif; font-size: clamp(24px,3vw,32px); color: #1a2a4a; font-weight: 700; }
        .section-sub { color: #6b7a99; font-size: 17px; margin-top: 10px; font-family: 'Source Sans 3', sans-serif; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-gold { background: #fef3c7; color: #92400e; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-family: 'Source Sans 3', sans-serif; font-size: 14px; }
        th { background: #f1f4fb; color: #4a5568; font-weight: 600; padding: 10px 12px; text-align: left; border-bottom: 1.5px solid #e2e8f0; }
        td { padding: 10px 12px; border-bottom: 1px solid #f0f2f8; color: #1a2a4a; }
        tr:hover td { background: #f8faff; }
        .nav-link { color: #4a5568; text-decoration: none; font-size: 15px; font-weight: 600; padding: 6px 2px; border-bottom: 2px solid transparent; transition: color .2s, border-color .2s; cursor: pointer; font-family: 'Source Sans 3', sans-serif; }
        .nav-link:hover, .nav-link.active { color: #1a2a4a; border-bottom-color: #c9a227; }
        .stat-card { background: white; border-radius: 10px; padding: 20px 24px; box-shadow: 0 2px 12px rgba(26,42,74,.07); }
        .form-group { margin-bottom: 18px; }
        .event-card { background: white; border-radius: 12px; box-shadow: 0 2px 16px rgba(26,42,74,.08); overflow: hidden; transition: transform .2s, box-shadow .2s; }
        .event-card:hover { transform: translateY(-4px); box-shadow: 0 8px 32px rgba(26,42,74,.13); }
        .grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }
        .grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
        .grid-4 { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; }
        .sidebar { width: 240px; flex-shrink: 0; background: #1a2a4a; min-height: 100vh; padding: 0 0 40px; }
        .sidebar-item { display: flex; align-items: center; gap: 10px; padding: 12px 20px; color: #a0b0cc; font-size: 14px; font-weight: 600; cursor: pointer; transition: all .2s; border-left: 3px solid transparent; font-family: 'Source Sans 3', sans-serif; }
        .sidebar-item:hover { color: white; background: rgba(255,255,255,.06); }
        .sidebar-item.active { color: #e6bf46; border-left-color: #e6bf46; background: rgba(230,191,70,.08); }
        .hero-gradient { background: linear-gradient(135deg, #0f1f3d 0%, #1a2a4a 40%, #1e3a6e 100%); }
        .gold-text { color: #c9a227; }
        .tab { padding: 8px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; border: none; background: transparent; color: #6b7a99; font-family: 'Source Sans 3', sans-serif; }
        .tab.active { background: #1a2a4a; color: white; }
        /* ── Event cards: 4 per row, centered when fewer ───────────────────── */
        .events-grid { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; }
        .events-grid > .event-card { flex: 0 0 calc((100% - 60px) / 4); max-width: calc((100% - 60px) / 4); }
        .event-card { display: flex; flex-direction: column; }
        .ev-media { position: relative; aspect-ratio: 16 / 10; background: #1a2a4a; overflow: hidden; }
        .ev-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .ev-ph { background: linear-gradient(135deg, #0f1f3d 0%, #1a2a4a 45%, #2d4a8a 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; }
        .ev-ph-logo { position: relative; line-height: 0; }
        .ev-ph-city { position: relative; color: #e6bf46; font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; font-family: sans-serif; }
        .ev-bar { position: absolute; left: 0; right: 0; bottom: 0; height: 5px; background: linear-gradient(90deg, #1a2a4a, #c9a227); }
        .ev-body { padding: 14px 16px 16px; display: flex; flex-direction: column; flex: 1; font-family: sans-serif; }
        .ev-top { display: flex; justify-content: space-between; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 8px; }
        .ev-badge { font-size: 10.5px; padding: 2px 8px; }
        .ev-date { font-size: 11.5px; color: #8a99b8; }
        .ev-title { font-size: 17px; color: #1a2a4a; line-height: 1.25; margin-bottom: 6px; }
        .ev-meta { display: flex; flex-direction: column; gap: 3px; margin-bottom: 8px; font-size: 12.5px; color: #6b7a99; }
        .ev-desc { font-size: 12.5px; color: #6b7a99; line-height: 1.5; margin-bottom: 8px; white-space: pre-line; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .ev-offer { font-size: 11.5px; color: #92400e; background: #fef3c7; border-radius: 6px; padding: 4px 8px; margin-bottom: 8px; font-weight: 600; }
        .ev-foot { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: auto; padding-top: 6px; }
        .ev-seats { font-size: 12px; color: #8a99b8; }
        .ev-btn { padding: 7px 12px; font-size: 12.5px; white-space: nowrap; }
        @media (max-width: 1080px) {
          .events-grid > .event-card { flex-basis: calc((100% - 40px) / 3); max-width: calc((100% - 40px) / 3); }
        }
        @media (max-width: 820px) {
          .events-grid { gap: 14px; }
          .events-grid > .event-card { flex-basis: calc((100% - 14px) / 2); max-width: calc((100% - 14px) / 2); }
        }

        /* ── Tablet / mobile ───────────────────────────────────────────────── */
        @media (max-width: 768px) {
          .sidebar { display: none; }
          .hide-mobile { display: none !important; }
          .mobile-menu-btn { display: block !important; }
          .gallery-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 8px !important; }
          .g-tall { min-height: 200px !important; }
          .g-tile:not(.g-tall) { min-height: 96px !important; }

          body { font-size: 14px; }
          .section-title { font-size: 22px !important; }
          .section-sub { font-size: 14px !important; margin-top: 6px !important; }
          .h2-sec { font-size: 22px !important; }
          .hero { padding: 44px 16px 48px !important; }
          .hero-title { font-size: 28px !important; margin-bottom: 12px !important; }
          .hero-sub { font-size: 14.5px !important; margin-bottom: 22px !important; }
          .stats-grid { grid-template-columns: repeat(2, 1fr) !important; margin-top: 30px !important; }
          .stats-grid > div { padding: 12px 8px !important; border-right: none !important; }
          .stat-n { font-size: 20px !important; }

          .sec { padding: 40px 14px !important; }
          .sec-events { margin-top: 36px !important; padding: 0 12px !important; }
          .sec-events > div:first-child { margin-bottom: 20px !important; }
          .sec-x { margin: 36px auto !important; padding: 0 14px !important; }
          .sec-top { padding-left: 14px !important; padding-right: 14px !important; padding-top: 32px !important; }
          .box-pad { padding: 26px 18px !important; }
          .stack-mobile { grid-template-columns: 1fr !important; gap: 22px !important; }
          .streams-grid { grid-template-columns: repeat(3, 1fr) !important; gap: 8px !important; margin-top: 24px !important; }
          .streams-grid > div { padding: 10px 6px !important; }
          .founder-photo { width: 150px !important; height: 185px !important; }

          .grid-3 { grid-template-columns: repeat(2, 1fr); gap: 12px; }
          .grid-2 { grid-template-columns: 1fr; gap: 14px; }
          .one-col-mobile { grid-template-columns: 1fr !important; }
          .pad-card { padding: 16px 14px !important; }
          .pad-card > div:first-child { font-size: 26px !important; margin-bottom: 8px !important; }
          .pad-card h3 { font-size: 15px !important; margin-bottom: 4px !important; }
          .pad-card p { font-size: 12.5px !important; line-height: 1.55 !important; }

          .btn-primary, .btn-gold, .btn-outline { padding: 10px 18px; font-size: 14px; }
          .btn-sm { padding: 6px 12px; font-size: 12.5px; }
          .input-field { font-size: 14px; padding: 9px 12px; }
          .filters { padding: 12px !important; gap: 8px !important; margin-bottom: 20px !important; }
          .filters > * { flex: 1 1 140px !important; }
          .site-footer { padding: 32px 16px 20px !important; margin-top: 36px !important; }
          .site-footer .grid-3 { margin-bottom: 20px !important; gap: 18px; }

          /* Scale every large inline font down on mobile (all pages) */
          [style*="font-size: 15px"] { font-size: 14px !important; }
          [style*="font-size: 16px"] { font-size: 14px !important; }
          [style*="font-size: 17px"] { font-size: 14.5px !important; }
          [style*="font-size: 18px"] { font-size: 15px !important; }
          [style*="font-size: 20px"] { font-size: 16px !important; }
          [style*="font-size: 22px"] { font-size: 17px !important; }
          [style*="font-size: 24px"] { font-size: 18px !important; }
          [style*="font-size: 26px"] { font-size: 18px !important; }
          [style*="font-size: 28px"] { font-size: 20px !important; }
          [style*="font-size: 32px"] { font-size: 22px !important; }
          [style*="font-size: 34px"] { font-size: 22px !important; }
          [style*="font-size: 36px"] { font-size: 24px !important; }
          [style*="font-size: 44px"] { font-size: 28px !important; }
          [style*="font-size: 48px"] { font-size: 30px !important; }
          [style*="font-size: 52px"] { font-size: 32px !important; }
          [style*="font-size: 64px"] { font-size: 36px !important; }
          [style*="font-size: 72px"] { font-size: 40px !important; }
          [style*="line-height: 1.8"], [style*="line-height: 1.75"] { line-height: 1.6 !important; }

          /* How We Help: compact tabs, hide the duplicate icon strip, smaller visual card */
          .hw-tabs { gap: 6px !important; margin-bottom: 22px !important; }
          .hw-tabs button { padding: 6px 10px !important; font-size: 12px !important; gap: 4px !important; }
          .streams-grid { display: none !important; }
          .hw-visual { min-height: 0 !important; padding: 20px 18px !important; gap: 16px; border-radius: 14px !important; }
          .founder-photo-home { width: 130px !important; height: 160px !important; }

          /* Footer: brand on top, Quick Links + Contact side by side */
          .footer-grid { grid-template-columns: 1fr 1fr !important; }
          .footer-grid > div:first-child { grid-column: 1 / -1; }
          .site-footer p, .site-footer div { font-size: 13px; }

          /* Event cards on mobile: 2 per row, compact */
          .events-grid { gap: 10px; }
          .events-grid > .event-card { flex-basis: calc((100% - 10px) / 2); max-width: calc((100% - 10px) / 2); }
          .event-card { border-radius: 10px; }
          .event-card:hover { transform: none; }
          .ev-body { padding: 9px 10px 11px; }
          .ev-top { margin-bottom: 5px; gap: 4px; }
          .ev-badge { font-size: 9px; padding: 1px 6px; }
          .ev-date { font-size: 10px; }
          .ev-title { font-size: 13.5px; margin-bottom: 4px; }
          .ev-meta { font-size: 11px; gap: 2px; margin-bottom: 6px; }
          .ev-desc { font-size: 11px; -webkit-line-clamp: 2; margin-bottom: 6px; }
          .ev-offer { font-size: 10px; padding: 3px 6px; margin-bottom: 6px; }
          .ev-foot { flex-direction: column; align-items: stretch; gap: 6px; padding-top: 4px; }
          .ev-seats { font-size: 10.5px; }
          .ev-btn { width: 100%; padding: 7px 6px; font-size: 11.5px; }
          .ev-ph-logo svg { width: 32px; height: 35px; }
          .ev-ph-city { font-size: 9px; letter-spacing: 1.5px; }
          .founder-quote { font-size: 16px !important; overflow-wrap: break-word; }
          .pad-card-lg { padding: 20px 16px !important; }
          .wa-fab { width: 44px !important; height: 44px !important; bottom: 14px !important; right: 14px !important; font-size: 20px !important; }
        }
      `}</style>

      {/* NAVBAR */}
      {!isAdmin && !isAgent && (
        <nav style={{ background: "white", borderBottom: "1.5px solid #e8edf8", position: "sticky", top: 0, zIndex: 100, boxShadow: "0 2px 12px rgba(26,42,74,.06)" }}>
          <div style={{ maxWidth: 1200, margin: "0 auto", padding: "0 24px", display: "flex", alignItems: "center", justifyContent: "space-between", height: 64 }}>
            <div onClick={() => nav("home")} style={{ cursor: "pointer", display: "flex", alignItems: "center", gap: 10 }}>
              <AcademiaLogo size={36} />
              <div>
                <div className="display" style={{ fontSize: 20, fontWeight: 700, color: "#1a2a4a", lineHeight: 1 }}>AcademiaFest</div>
                <div style={{ fontSize: 10, color: "#8a99b8", letterSpacing: "1px", textTransform: "uppercase", fontFamily: "sans-serif" }}>Education Events</div>
              </div>
            </div>
            <div style={{ display: "flex", gap: 28, alignItems: "center" }} className="hide-mobile">
              {[["home","Home"],["events","Events"],["about","About"],["founder","Founder"],["contact","Contact"]].map(([p,l]) =>
                <span key={p} className={`nav-link ${page === p ? "active" : ""}`} onClick={() => nav(p)}>{l}</span>
              )}
              <button className="btn-gold btn-sm" onClick={() => nav("register")}>Register Free</button>
              <span className="nav-link" style={{ fontSize: 13 }} onClick={() => nav("admin-login")}>Admin</span>
            </div>
            <button onClick={() => setMobileMenu(!mobileMenu)} style={{ display: "none", background: "none", border: "none", fontSize: 24, cursor: "pointer" }} className="mobile-menu-btn">☰</button>
          </div>
          {mobileMenu && (
            <div style={{ borderTop: "1px solid #e8edf8", padding: "12px 24px 18px", display: "flex", flexDirection: "column", gap: 14, background: "white" }}>
              {[["home","Home"],["events","Events"],["about","About"],["founder","Founder"],["contact","Contact"]].map(([p,l]) =>
                <span key={p} className={`nav-link ${page === p ? "active" : ""}`} onClick={() => nav(p)}>{l}</span>
              )}
              <button className="btn-gold btn-sm" style={{ alignSelf: "flex-start" }} onClick={() => nav("register")}>Register Free</button>
            </div>
          )}
        </nav>
      )}

      {/* PAGES */}
      {page === "home" && <HomePage ctx={ctx} />}
      {page === "events" && <EventsPage ctx={ctx} />}
      {page === "about" && <AboutPage ctx={ctx} />}
      {page === "founder" && <FounderPage ctx={ctx} />}
      {page === "contact" && <ContactPage ctx={ctx} />}

      {/* FOOTER */}
      {!isAdmin && !isAgent && (
        <footer className="site-footer" style={{ background: "#0f1f3d", color: "#a0b0cc", padding: "48px 24px 24px", marginTop: 60 }}>
          <div style={{ maxWidth: 1200, margin: "0 auto" }}>
            <div className="grid-3 footer-grid" style={{ marginBottom: 40 }}>
              <div>
                <div className="display" style={{ fontSize: 22, color: "white", marginBottom: 10 }}>AcademiaFest</div>
                <p style={{ fontSize: 14, lineHeight: 1.7 }}>India's trusted offline education fair connecting students, parents, colleges, and universities.</p>
              </div>
              <div>
                <div style={{ fontWeight: 700, color: "white", marginBottom: 12, fontSize: 14 }}>Quick Links</div>
                {[["home","Home"],["events","Events"],["about","About"],["founder","Founder"],["contact","Contact"]].map(([p,l]) =>
                  <div key={p} onClick={() => nav(p)} style={{ color: "#a0b0cc", fontSize: 14, marginBottom: 6, cursor: "pointer" }}>{l}</div>
                )}
              </div>
              <div>
                <div style={{ fontWeight: 700, color: "white", marginBottom: 12, fontSize: 14 }}>Contact</div>
                <p style={{ fontSize: 14, lineHeight: 1.8 }}>📧 info@academiafest.com<br />📞 +91 90153 91313<br />📍 Laxmi Niwas, Baily Road, Patna</p>
              </div>
            </div>
            <div style={{ borderTop: "1px solid #1e3a6e", paddingTop: 20, textAlign: "center", fontSize: 13 }}>
              © 2025 AcademiaFest. All rights reserved. | India's Trusted Education Event Platform
            </div>
          </div>
        </footer>
      )}

      {/* WhatsApp CTA */}
      {!isAdmin && !isAgent && (
        <a className="wa-fab" href="https://wa.me/919015391313" target="_blank" rel="noreferrer" style={{ position: "fixed", bottom: 24, right: 24, width: 52, height: 52, background: "#25d366", borderRadius: "50%", display: "flex", alignItems: "center", justifyContent: "center", fontSize: 24, textDecoration: "none", boxShadow: "0 4px 16px rgba(37,211,102,.4)", zIndex: 200 }}>
          💬
        </a>
      )}
    </div>
  );
}

// ─── GALLERY SECTION ──────────────────────────────────────────────────────────
function GallerySection({ photos: adminPhotos = [], nav }) {
  const [active, setActive] = useState(null);
  const [lightbox, setLightbox] = useState(null);
  // Photos come from the admin panel (/admin/gallery.php); when there are none, the default design tiles are shown
  const fallbackPhotos = [
    { id: 1, label: "Patna Fair 2024", city: "Patna", tag: "MBA Fair", color: ["#1a2a4a","#2d4a8a"], icon: "🎓", scene: "500+ students, 30 colleges" },
    { id: 2, label: "Lucknow Expo", city: "Lucknow", tag: "UG Fair", color: ["#14532d","#166534"], icon: "🏛️", scene: "One-on-one counselling" },
    { id: 3, label: "Agra Edition", city: "Agra", tag: "MBA Fair", color: ["#7c2d12","#9a3412"], icon: "🏆", scene: "Scholarship distribution" },
    { id: 4, label: "Jaipur Summit", city: "Jaipur", tag: "PG Fair", color: ["#4c1d95","#6d28d9"], icon: "📚", scene: "Expert panel discussion" },
    { id: 5, label: "Jamshedpur Fair", city: "Jamshedpur", tag: "UG Fair", color: ["#0c4a6e","#075985"], icon: "🎤", scene: "Career guidance seminar" },
    { id: 6, label: "Kanpur 2026", city: "Kanpur", tag: "General", color: ["#713f12","#92400e"], icon: "🌟", scene: "Award & recognition night" },
    { id: 7, label: "Muzaffarpur", city: "Muzaffarpur", tag: "General", color: ["#1e3a5f","#1a4a7a"], icon: "🤝", scene: "College–student interaction" },
    { id: 8, label: "Gaya Session", city: "Gaya", tag: "UG Fair", color: ["#134e4a","#0f766e"], icon: "📋", scene: "Registration & pass distribution" },
  ];
  const tileColors = [["#1a2a4a","#2d4a8a"],["#14532d","#166534"],["#7c2d12","#9a3412"],["#4c1d95","#6d28d9"],["#0c4a6e","#075985"],["#713f12","#92400e"],["#1e3a5f","#1a4a7a"],["#134e4a","#0f766e"]];
  const photos = adminPhotos.length
    ? adminPhotos.map((p, i) => ({ ...p, color: tileColors[i % tileColors.length] }))
    : fallbackPhotos;

  useEffect(() => {
    if (!lightbox) return;
    const onKey = e => { if (e.key === "Escape") setLightbox(null); };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, [lightbox]);

  return (
    <div className="sec" style={{ padding: "70px 24px", background: "#0f1f3d" }}>
      <div style={{ maxWidth: 1200, margin: "0 auto" }}>
        <div style={{ textAlign: "center", marginBottom: 48 }}>
          <div style={{ display: "inline-block", background: "rgba(201,162,39,.15)", border: "1px solid rgba(201,162,39,.35)", borderRadius: 20, padding: "5px 16px", marginBottom: 14 }}>
            <span style={{ color: "#e6bf46", fontSize: 12, fontWeight: 600, letterSpacing: "1.5px", textTransform: "uppercase", fontFamily: "sans-serif" }}>Event Gallery</span>
          </div>
          <h2 className="display h2-sec" style={{ fontSize: "clamp(26px,4vw,38px)", color: "white", marginBottom: 12 }}>Moments from Our Events</h2>
          <p style={{ color: "#7a8faa", fontSize: 16, fontFamily: "sans-serif" }}>Thousands of students. Hundreds of colleges. One incredible experience.</p>
        </div>

        {/* Masonry-style grid */}
        <div className="gallery-grid" style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gridTemplateRows: "auto", gap: 12 }}>
          {photos.map((p, i) => {
            const tall = i === 0 || i === 5;
            return (
              <div
                key={p.id}
                onClick={() => p.image ? setLightbox(p) : setActive(active === p.id ? null : p.id)}
                className={tall ? "g-tile g-tall" : "g-tile"}
                style={{
                  gridRow: tall ? "span 2" : "span 1",
                  borderRadius: 12,
                  overflow: "hidden",
                  cursor: "pointer",
                  position: "relative",
                  minHeight: tall ? 280 : 130,
                  background: `linear-gradient(145deg, ${p.color[0]} 0%, ${p.color[1]} 100%)`,
                  transition: "transform .2s, box-shadow .2s",
                  boxShadow: active === p.id ? "0 0 0 3px #e6bf46" : "0 4px 16px rgba(0,0,0,.3)",
                  transform: active === p.id ? "scale(1.02)" : "scale(1)",
                }}
              >
                {/* Admin photo */}
                {p.image && (
                  <img src={p.image} alt={p.label || "Event photo"} loading="lazy" style={{ position: "absolute", inset: 0, width: "100%", height: "100%", objectFit: "cover" }} />
                )}
                {/* Scene illustration (default tiles) */}
                {!p.image && <div style={{ position: "absolute", inset: 0, display: "flex", flexDirection: "column", alignItems: "center", justifyContent: "center", padding: 16 }}>
                  <div style={{ fontSize: tall ? 52 : 32, marginBottom: 8, opacity: .9 }}>{p.icon}</div>
                  {/* Decorative dots pattern */}
                  <div style={{ position: "absolute", top: 10, right: 10, display: "grid", gridTemplateColumns: "repeat(4,6px)", gap: 3, opacity: .2 }}>
                    {Array(12).fill(0).map((_,j) => <div key={j} style={{ width: 6, height: 6, borderRadius: "50%", background: "white" }} />)}
                  </div>
                </div>}
                {/* Overlay */}
                <div style={{ position: "absolute", inset: 0, background: "linear-gradient(to top, rgba(0,0,0,.75) 0%, transparent 55%)", display: "flex", flexDirection: "column", justifyContent: "flex-end", padding: "14px 14px 16px" }}>
                  <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-end" }}>
                    <div>
                      <div style={{ color: "white", fontWeight: 700, fontSize: tall ? 16 : 13, fontFamily: "sans-serif", lineHeight: 1.2 }}>{p.label}</div>
                      <div style={{ color: "#b0c0d8", fontSize: 11, fontFamily: "sans-serif", marginTop: 2 }}>{p.scene}</div>
                    </div>
                    {p.tag && <span style={{ background: "rgba(201,162,39,.25)", color: "#e6bf46", border: "1px solid rgba(201,162,39,.4)", borderRadius: 10, padding: "2px 8px", fontSize: 10, fontWeight: 700, fontFamily: "sans-serif", whiteSpace: "nowrap" }}>{p.tag}</span>}
                  </div>
                </div>
              </div>
            );
          })}
        </div>

        {/* CTA below gallery */}
        <div style={{ textAlign: "center", marginTop: 36 }}>
          <p style={{ color: "#7a8faa", fontSize: 15, marginBottom: 18, fontFamily: "sans-serif" }}>Be part of the next edition. Registration is completely free.</p>
          <button className="btn-gold" style={{ padding: "12px 32px" }} onClick={() => nav("register")}>Register for an Event →</button>
        </div>
      </div>

      {/* Lightbox */}
      {lightbox && (
        <div onClick={() => setLightbox(null)} style={{ position: "fixed", inset: 0, background: "rgba(5,12,28,.92)", zIndex: 300, display: "flex", alignItems: "center", justifyContent: "center", padding: 20, cursor: "zoom-out" }}>
          <div onClick={e => e.stopPropagation()} style={{ maxWidth: 1000, width: "100%", textAlign: "center" }}>
            <img src={lightbox.image} alt={lightbox.label || "Event photo"} style={{ maxWidth: "100%", maxHeight: "78vh", borderRadius: 12, boxShadow: "0 12px 48px rgba(0,0,0,.5)" }} />
            {(lightbox.label || lightbox.scene) && (
              <div style={{ color: "white", marginTop: 14, fontFamily: "sans-serif" }}>
                <div style={{ fontWeight: 700, fontSize: 17 }}>{lightbox.label}</div>
                <div style={{ color: "#b0c0d8", fontSize: 14, marginTop: 4 }}>{lightbox.scene}</div>
              </div>
            )}
            <button onClick={() => setLightbox(null)} className="btn-gold btn-sm" style={{ marginTop: 16 }}>Close ✕</button>
          </div>
        </div>
      )}
    </div>
  );
}

// ─── HOW WE HELP STUDENTS ─────────────────────────────────────────────────────
function HowWeHelpSection({ nav }) {
  const [activeStream, setActiveStream] = useState(0);

  const streams = [
    {
      id: 0,
      icon: "🤝",
      tag: "1-on-1 Counselling",
      title: "Personal 1-to-1 Counselling Sessions",
      color: "#1a2a4a",
      accent: "#2d4a8a",
      highlight: "#e6bf46",
      desc: "Every student deserves personalised attention. At AcademiaFest fairs, our expert counsellors sit with you one-on-one to understand your academic background, interests, and goals — then map you to the right institutions.",
      points: [
        "Dedicated counsellor assigned to each student",
        "Profile evaluation based on marks & exams",
        "Course and college shortlisting tailored to you",
        "Career path clarity for UG, PG & MBA aspirants",
        "Follow-up support after the event",
      ],
      badge: "Free for all registered students",
      stat: { n: "1:1", l: "Dedicated Session" },
    },
    {
      id: 1,
      icon: "🏆",
      tag: "Scholarships",
      title: "Scholarship Discovery & Guidance",
      color: "#7c2d12",
      accent: "#c2410c",
      highlight: "#fbbf24",
      desc: "Thousands of crores in scholarships go unclaimed every year because students don't know where to look. AcademiaFest brings scholarship opportunities directly to you from our partner institutions.",
      points: [
        "Merit-based scholarships from 200+ colleges",
        "Need-based financial aid information",
        "Government scholarship scheme guidance",
        "Management quota & special category seats",
        "On-the-spot scholarship eligibility check",
      ],
      badge: "Up to 100% fee waiver available",
      stat: { n: "₹50Cr+", l: "Scholarships Available" },
    },
    {
      id: 2,
      icon: "📋",
      tag: "Admission Help",
      title: "End-to-End Admission Assistance",
      color: "#14532d",
      accent: "#166534",
      highlight: "#86efac",
      desc: "Navigating admissions can be overwhelming. Our counsellors walk you through every step — from filling forms and understanding cut-offs to preparing for interviews and GD rounds.",
      points: [
        "Application form assistance at the event",
        "Entrance exam score evaluation (CAT, XAT, CUET, JEE)",
        "Cut-off & eligibility analysis",
        "GD/PI preparation tips from B-school experts",
        "Document checklist & verification guidance",
      ],
      badge: "100% free guidance",
      stat: { n: "10,000+", l: "Admissions Facilitated" },
    },
    {
      id: 3,
      icon: "🎯",
      tag: "Career Mapping",
      title: "Career Stream & Course Selection",
      color: "#4c1d95",
      accent: "#6d28d9",
      highlight: "#c4b5fd",
      desc: "Not sure which stream is right for you? Our career experts help you identify your strengths, interests, and ideal career trajectory — then recommend the best courses and institutions to get you there.",
      points: [
        "Aptitude & interest assessment",
        "Stream comparison: BBA vs B.Tech vs BCA vs BA",
        "Industry demand insights for 2025–2030",
        "Specialisation guidance for MBA/PGDM",
        "International education pathways",
      ],
      badge: "Expert faculty from top B-schools",
      stat: { n: "50+", l: "Career Paths Covered" },
    },
    {
      id: 4,
      icon: "🌐",
      tag: "College Network",
      title: "Access to 200+ Top Institutions",
      color: "#0c4a6e",
      accent: "#0369a1",
      highlight: "#7dd3fc",
      desc: "AcademiaFest brings India's leading colleges, universities, and B-schools to your city. Meet representatives, collect brochures, compare programs, and make informed decisions — all under one roof.",
      points: [
        "Top-ranked universities across India",
        "Premier B-schools & MBA institutes",
        "Technical colleges (NIT, deemed, private)",
        "International universities & study-abroad counselling",
        "Online degree & certification programs",
      ],
      badge: "All institutions verified & ranked",
      stat: { n: "200+", l: "Partner Colleges" },
    },
    {
      id: 5,
      icon: "📱",
      tag: "Digital Support",
      title: "Post-Event Digital Follow-Up",
      color: "#134e4a",
      accent: "#0f766e",
      highlight: "#5eead4",
      desc: "Our support doesn't end at the event. After registration, you receive follow-up from our dedicated agent team via WhatsApp, call, and email to help you complete your admission journey.",
      points: [
        "Dedicated agent assigned after registration",
        "WhatsApp updates on event schedule",
        "Reminder calls before the event",
        "Post-event college follow-up coordination",
        "Admission confirmation tracking",
      ],
      badge: "Supported by 100+ field agents",
      stat: { n: "100+", l: "Field Agents" },
    },
  ];

  const s = streams[activeStream];

  return (
    <div className="sec" style={{ padding: "70px 24px", background: "white" }}>
      <div style={{ maxWidth: 1200, margin: "0 auto" }}>
        {/* Header */}
        <div style={{ textAlign: "center", marginBottom: 52 }}>
          <div style={{ display: "inline-block", background: "#f1f4fb", border: "1px solid #dde3ef", borderRadius: 20, padding: "5px 16px", marginBottom: 14 }}>
            <span style={{ color: "#2d4a8a", fontSize: 12, fontWeight: 600, letterSpacing: "1.5px", textTransform: "uppercase", fontFamily: "sans-serif" }}>Student Support Streams</span>
          </div>
          <h2 className="display h2-sec" style={{ fontSize: "clamp(26px,4vw,40px)", color: "#1a2a4a", marginBottom: 12 }}>
            How We Help Every Student
          </h2>
          <p style={{ color: "#6b7a99", fontSize: 17, maxWidth: 600, margin: "0 auto", fontFamily: "sans-serif" }}>
            From personalised counselling to scholarship discovery — we support students at every step of their education journey.
          </p>
        </div>

        {/* Stream Tabs */}
        <div className="hw-tabs" style={{ display: "flex", gap: 10, justifyContent: "center", flexWrap: "wrap", marginBottom: 44 }}>
          {streams.map((st, i) => (
            <button
              key={st.id}
              onClick={() => setActiveStream(i)}
              style={{
                padding: "9px 20px",
                borderRadius: 50,
                border: activeStream === i ? "2px solid #1a2a4a" : "1.5px solid #dde3ef",
                background: activeStream === i ? "#1a2a4a" : "white",
                color: activeStream === i ? "white" : "#4a5568",
                fontWeight: 600,
                fontSize: 14,
                cursor: "pointer",
                fontFamily: "sans-serif",
                display: "flex",
                alignItems: "center",
                gap: 7,
                transition: "all .2s",
              }}
            >
              <span>{st.icon}</span> {st.tag}
            </button>
          ))}
        </div>

        {/* Active Stream Detail */}
        <div className="stack-mobile" style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 40, alignItems: "stretch" }}>
          {/* Left: content */}
          <div style={{ display: "flex", flexDirection: "column", justifyContent: "center" }}>
            <div style={{ display: "flex", alignItems: "center", gap: 14, marginBottom: 20 }}>
              <div style={{ width: 56, height: 56, background: `linear-gradient(135deg, ${s.color}, ${s.accent})`, borderRadius: 14, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 26 }}>
                {s.icon}
              </div>
              <div>
                <span style={{ background: "#f1f4fb", color: "#2d4a8a", fontSize: 11, fontWeight: 700, padding: "3px 10px", borderRadius: 10, fontFamily: "sans-serif", letterSpacing: "0.5px" }}>{s.tag.toUpperCase()}</span>
                <h3 className="display" style={{ fontSize: "clamp(20px,2.5vw,26px)", color: "#1a2a4a", marginTop: 4, lineHeight: 1.2 }}>{s.title}</h3>
              </div>
            </div>
            <p style={{ color: "#4a5568", fontSize: 16, lineHeight: 1.8, marginBottom: 24, fontFamily: "sans-serif" }}>{s.desc}</p>
            <ul style={{ listStyle: "none", padding: 0, marginBottom: 28 }}>
              {s.points.map((pt, i) => (
                <li key={i} style={{ display: "flex", alignItems: "flex-start", gap: 12, marginBottom: 12 }}>
                  <div style={{ width: 22, height: 22, background: `linear-gradient(135deg, ${s.color}, ${s.accent})`, borderRadius: "50%", display: "flex", alignItems: "center", justifyContent: "center", flexShrink: 0, marginTop: 1 }}>
                    <span style={{ color: "white", fontSize: 11, fontWeight: 700 }}>✓</span>
                  </div>
                  <span style={{ color: "#374151", fontSize: 15, lineHeight: 1.5, fontFamily: "sans-serif" }}>{pt}</span>
                </li>
              ))}
            </ul>
            <div style={{ display: "flex", alignItems: "center", gap: 14, padding: "14px 20px", background: "#f8f9fc", borderRadius: 10, border: "1.5px solid #e8edf8" }}>
              <span style={{ fontSize: 18 }}>🎁</span>
              <span style={{ fontSize: 14, color: "#1a2a4a", fontWeight: 600, fontFamily: "sans-serif" }}>{s.badge}</span>
            </div>
          </div>

          {/* Right: visual card */}
          <div style={{ background: `linear-gradient(145deg, ${s.color} 0%, ${s.accent} 100%)`, borderRadius: 20, padding: "36px 32px", color: "white", display: "flex", flexDirection: "column", justifyContent: "space-between", minHeight: 380 }} className="hw-visual">
            {/* Top decoration */}
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start" }}>
              <div style={{ fontSize: 64 }}>{s.icon}</div>
              <div style={{ display: "grid", gridTemplateColumns: "repeat(5,8px)", gap: 5, opacity: .15 }}>
                {Array(25).fill(0).map((_,j) => <div key={j} style={{ width: 8, height: 8, borderRadius: "50%", background: "white" }} />)}
              </div>
            </div>
            {/* Mid content */}
            <div>
              <div style={{ color: "rgba(255,255,255,.6)", fontSize: 13, fontFamily: "sans-serif", marginBottom: 10, textTransform: "uppercase", letterSpacing: "1px" }}>What you get</div>
              {s.points.slice(0, 3).map((pt, i) => (
                <div key={i} style={{ display: "flex", gap: 10, marginBottom: 12, alignItems: "flex-start" }}>
                  <div style={{ width: 6, height: 6, background: s.highlight, borderRadius: "50%", marginTop: 7, flexShrink: 0 }} />
                  <span style={{ color: "rgba(255,255,255,.85)", fontSize: 14, lineHeight: 1.5, fontFamily: "sans-serif" }}>{pt}</span>
                </div>
              ))}
            </div>
            {/* Stat */}
            <div style={{ borderTop: "1px solid rgba(255,255,255,.15)", paddingTop: 20, display: "flex", justifyContent: "space-between", alignItems: "center" }}>
              <div>
                <div className="display" style={{ fontSize: 32, color: s.highlight, fontWeight: 700 }}>{s.stat.n}</div>
                <div style={{ color: "rgba(255,255,255,.6)", fontSize: 13, fontFamily: "sans-serif" }}>{s.stat.l}</div>
              </div>
              <button className="btn-gold" style={{ padding: "10px 22px" }} onClick={() => nav("register")}>Get Started →</button>
            </div>
          </div>
        </div>

        {/* Bottom strip — all 6 at a glance */}
        <div className="streams-grid" style={{ display: "grid", gridTemplateColumns: "repeat(6, 1fr)", gap: 12, marginTop: 40 }}>
          {streams.map((st, i) => (
            <div
              key={st.id}
              onClick={() => setActiveStream(i)}
              style={{
                padding: "16px 12px",
                borderRadius: 10,
                textAlign: "center",
                cursor: "pointer",
                background: activeStream === i ? "#1a2a4a" : "#f8f9fc",
                border: `1.5px solid ${activeStream === i ? "#1a2a4a" : "#e8edf8"}`,
                transition: "all .2s",
              }}
            >
              <div style={{ fontSize: 22, marginBottom: 6 }}>{st.icon}</div>
              <div style={{ fontSize: 11, fontWeight: 700, color: activeStream === i ? "#e6bf46" : "#6b7a99", fontFamily: "sans-serif" }}>{st.tag}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

// ─── HOME PAGE ────────────────────────────────────────────────────────────────
function HomePage({ ctx }) {
  const { nav, events, loaded } = ctx;
  const upcoming = events.slice(0, 4);
  const stats = [
    { n: "50,000+", l: "Students Reached" }, { n: "200+", l: "Colleges & Universities" },
    { n: "25+", l: "Cities Covered" }, { n: "100%", l: "Free Registration" },
  ];
  const features = [
    { icon: "🎓", title: "Meet Top Colleges", desc: "Direct interaction with admission counsellors from India's leading institutions." },
    { icon: "💬", title: "Free Career Guidance", desc: "Expert counsellors guide you through UG, PG, MBA admission processes." },
    { icon: "🏆", title: "Scholarship Info", desc: "Discover scholarships and financial aid directly from institutes." },
    { icon: "📋", title: "Digital Event Pass", desc: "Register online and receive your digital pass instantly via email." },
    { icon: "🌍", title: "Pan India Events", desc: "Events across major cities in India and internationally." },
    { icon: "🆓", title: "Completely Free", desc: "Student registration is 100% free. No hidden charges whatsoever." },
  ];
  return (
    <div>
      {/* HERO */}
      <div className="hero-gradient hero" style={{ padding: "80px 24px 90px", textAlign: "center", position: "relative", overflow: "hidden" }}>
        <div style={{ position: "absolute", inset: 0, backgroundImage: "radial-gradient(circle at 30% 50%, rgba(45,74,138,.3) 0%, transparent 60%), radial-gradient(circle at 70% 30%, rgba(201,162,39,.1) 0%, transparent 50%)" }} />
        <div style={{ position: "relative", maxWidth: 800, margin: "0 auto" }}>
          <h1 className="display hero-title" style={{ fontSize: "clamp(32px,5.5vw,58px)", color: "white", fontWeight: 700, lineHeight: 1.15, marginBottom: 20 }}>
            India's Trusted<br /><span className="gold-text">Education Event Platform</span>
          </h1>
          <p className="hero-sub" style={{ color: "#b0c0d8", fontSize: "clamp(16px,2.5vw,20px)", lineHeight: 1.7, marginBottom: 36, fontFamily: "sans-serif" }}>
            Connect with top colleges, universities, and career experts through AcademiaFest offline education fairs across India.
          </p>
          <div style={{ display: "flex", gap: 16, justifyContent: "center", flexWrap: "wrap" }}>
            <button className="btn-gold" onClick={() => nav("register")}>Register for Free Event</button>
            <button className="btn-outline" style={{ color: "white", borderColor: "rgba(255,255,255,.5)" }} onClick={() => nav("events")}>View Upcoming Events →</button>
          </div>
          {/* Stats */}
          <div className="stats-grid" style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 2, marginTop: 60, background: "rgba(255,255,255,.05)", borderRadius: 12, overflow: "hidden" }}>
            {stats.map((s, i) => (
              <div key={i} style={{ padding: "20px 12px", textAlign: "center", borderRight: i < 3 ? "1px solid rgba(255,255,255,.08)" : "none" }}>
                <div className="display stat-n" style={{ fontSize: 28, color: "#e6bf46", fontWeight: 700 }}>{s.n}</div>
                <div style={{ fontSize: 12, color: "#8a99b8", marginTop: 4, fontFamily: "sans-serif" }}>{s.l}</div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* UPCOMING EVENTS */}
      <div className="sec-events" style={{ maxWidth: 1200, margin: "60px auto 0", padding: "0 24px" }}>
        <div style={{ textAlign: "center", marginBottom: 40 }}>
          <h2 className="section-title">Upcoming Events</h2>
          <p className="section-sub">Register for free and get your digital event pass instantly</p>
        </div>
        <div className="events-grid">
          {upcoming.map(e => <EventCard key={e.id} event={e} ctx={ctx} />)}
        </div>
        {loaded && upcoming.length === 0 && (
          <p style={{ textAlign: "center", color: "#8a99b8", fontFamily: "sans-serif" }}>New events will be announced soon. Stay tuned!</p>
        )}
        <div style={{ textAlign: "center", marginTop: 32 }}>
          <button className="btn-outline" onClick={() => nav("events")}>View All Events →</button>
        </div>
      </div>

      {/* HOW WE HELP STUDENTS — streams */}
      <div style={{ marginTop: 60 }}>
        <HowWeHelpSection nav={nav} />
      </div>

      {/* WHY ACADEMIAFEST */}
      <div className="sec" style={{ background: "#f1f4fb", padding: "60px 24px" }}>
        <div style={{ maxWidth: 1200, margin: "0 auto" }}>
          <div style={{ textAlign: "center", marginBottom: 44 }}>
            <h2 className="section-title">Why AcademiaFest?</h2>
            <p className="section-sub">Everything you need for your education journey, under one roof</p>
          </div>
          <div className="grid-3 features-grid">
            {features.map((f, i) => (
              <div key={i} className="card pad-card" style={{ padding: "28px 24px" }}>
                <div style={{ fontSize: 36, marginBottom: 14 }}>{f.icon}</div>
                <h3 className="display" style={{ fontSize: 18, color: "#1a2a4a", marginBottom: 8 }}>{f.title}</h3>
                <p style={{ color: "#6b7a99", fontSize: 14, lineHeight: 1.7, fontFamily: "sans-serif" }}>{f.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* FOR COLLEGES */}
      <div className="stack-mobile sec-x" style={{ maxWidth: 1200, margin: "60px auto", padding: "0 24px", display: "grid", gridTemplateColumns: "1fr 1fr", gap: 40, alignItems: "center" }}>
        <div>
          <h2 className="section-title">For Colleges &amp; Universities</h2>
          <p style={{ color: "#6b7a99", fontSize: 16, lineHeight: 1.8, margin: "16px 0 24px", fontFamily: "sans-serif" }}>
            Showcase your institution, interact with qualified students, distribute brochures, conduct counselling, and generate admissions through premium offline education fairs.
          </p>
          <ul style={{ listStyle: "none", padding: 0 }}>
            {["Direct student interaction","Qualified lead generation","Premium branding opportunities","Admission counselling setup","Pan-India reach"].map((item, i) => (
              <li key={i} style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 10, fontSize: 15, fontFamily: "sans-serif" }}>
                <span style={{ color: "#c9a227", fontSize: 18 }}>✓</span> {item}
              </li>
            ))}
          </ul>
          <button className="btn-primary" style={{ marginTop: 20 }} onClick={() => nav("contact")}>Partner With Us</button>
        </div>
        <div className="box-pad" style={{ background: "linear-gradient(135deg, #1a2a4a 0%, #2d4a8a 100%)", borderRadius: 16, padding: 40, color: "white", textAlign: "center" }}>
          <div className="display" style={{ fontSize: 32, marginBottom: 12 }}>For Students</div>
          <p style={{ color: "#a0b4cc", lineHeight: 1.8, marginBottom: 24, fontFamily: "sans-serif" }}>Meet college representatives, get career guidance, explore UG/PG options, and register for free education events near your city.</p>
          <button className="btn-gold" onClick={() => nav("register")}>Register Free Now</button>
        </div>
      </div>

      {/* GALLERY */}
      <GallerySection photos={ctx.gallery} nav={nav} />

      {/* MEET THE FOUNDER */}
      <div className="sec" style={{ background: "#f1f4fb", padding: "60px 24px" }}>
        <div style={{ maxWidth: 1100, margin: "0 auto" }}>
          <div style={{ background: "white", borderRadius: 20, boxShadow: "0 4px 32px rgba(26,42,74,.09)", overflow: "hidden", display: "grid", gridTemplateColumns: "320px 1fr" }} className="stack-mobile">
            {/* Photo Panel */}
            <div style={{ background: "linear-gradient(160deg, #0f1f3d 0%, #1a2a4a 60%, #1e3a6e 100%)", display: "flex", flexDirection: "column", alignItems: "center", justifyContent: "center", padding: "48px 32px", gap: 20 }} className="box-pad">
              <div className="founder-photo-home" style={{ width: 180, height: 220, borderRadius: 14, overflow: "hidden", border: "4px solid rgba(201,162,39,.45)", boxShadow: "0 12px 40px rgba(0,0,0,.4)" }}>
                <img
                  src="/founder.webp"
                  alt="Narendra Tiwari"
                  style={{ width: "100%", height: "100%", objectFit: "cover", objectPosition: "center top" }}
                  onError={e => { e.target.style.display = "none"; e.target.parentNode.style.display = "flex"; e.target.parentNode.style.alignItems = "center"; e.target.parentNode.style.justifyContent = "center"; e.target.parentNode.style.background = "linear-gradient(135deg,#1e3a6e,#2d4a8a)"; e.target.parentNode.innerHTML = '<span style="font-size:56px;color:#e6bf46;font-family:Georgia,serif">NT</span>'; }}
                />
              </div>
              <div style={{ textAlign: "center" }}>
                <div className="display" style={{ fontSize: 20, color: "white", fontWeight: 700 }}>Narendra Tiwari</div>
                <div style={{ color: "#c9a227", fontSize: 14, fontWeight: 600, fontFamily: "sans-serif", marginTop: 4 }}>Founder &amp; CEO</div>
                <div style={{ color: "#6b80aa", fontSize: 12, fontFamily: "sans-serif", marginTop: 6 }}>10+ Years in Education Industry</div>
              </div>
            </div>
            {/* Text Panel */}
            <div className="box-pad" style={{ padding: "48px 44px", display: "flex", flexDirection: "column", justifyContent: "center" }}>
              <div style={{ display: "inline-block", background: "#fef3c7", borderRadius: 20, padding: "5px 16px", marginBottom: 16, width: "fit-content" }}>
                <span style={{ color: "#92400e", fontSize: 12, fontWeight: 600, letterSpacing: "0.5px", textTransform: "uppercase", fontFamily: "sans-serif" }}>From the Founder</span>
              </div>
              <h2 className="section-title" style={{ marginBottom: 20 }}>Built for Every Student in India</h2>
              <div style={{ borderLeft: "3px solid #c9a227", paddingLeft: 20, marginBottom: 24 }}>
                <p className="display" style={{ fontSize: 17, color: "#4a5568", lineHeight: 1.75, fontStyle: "italic" }}>
                  "I spent over a decade working inside India's top universities and B-schools. I saw brilliant students miss their dream colleges — not because they weren't good enough, but because they didn't have access to the right information at the right time."
                </p>
              </div>
              <p style={{ color: "#6b7a99", fontSize: 15, lineHeight: 1.8, marginBottom: 28, fontFamily: "sans-serif" }}>
                "AcademiaFest is my answer to that. A free, face-to-face platform where every student — no matter their city or background — gets the same access to India's best institutions."
              </p>
              <div>
                <button className="btn-primary" onClick={() => nav("founder")}>Read Narendra's Full Story →</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* TESTIMONIALS */}
      <div className="sec" style={{ background: "#f1f4fb", padding: "60px 24px" }}>
        <div style={{ maxWidth: 1200, margin: "0 auto" }}>
          <h2 className="section-title" style={{ textAlign: "center", marginBottom: 40 }}>What Students Say</h2>
          <div className="grid-3 one-col-mobile">
            {[
              { name: "Priya Sharma", city: "Patna", text: "AcademiaFest helped me connect with 5 MBA colleges in one day. Got admission to my dream B-school!", course: "MBA Aspirant" },
              { name: "Rahul Singh", city: "Lucknow", text: "The event was superbly organised. I got detailed information about B.Tech admissions and scholarships.", course: "B.Tech Aspirant" },
              { name: "Anjali Verma", city: "Jaipur", text: "Free event, great colleges, amazing guidance. AcademiaFest is the best platform for students.", course: "BBA Student" },
            ].map((t, i) => (
              <div key={i} className="card pad-card" style={{ padding: "28px 24px" }}>
                <div style={{ color: "#c9a227", fontSize: 24, marginBottom: 12 }}>★★★★★</div>
                <p style={{ color: "#4a5568", fontSize: 15, lineHeight: 1.7, marginBottom: 16, fontFamily: "sans-serif" }}>"{t.text}"</p>
                <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
                  <div style={{ width: 40, height: 40, background: "#1a2a4a", borderRadius: "50%", display: "flex", alignItems: "center", justifyContent: "center", color: "#e6bf46", fontWeight: 700, fontSize: 16 }}>{t.name[0]}</div>
                  <div>
                    <div style={{ fontWeight: 700, fontSize: 14, color: "#1a2a4a" }}>{t.name}</div>
                    <div style={{ fontSize: 12, color: "#8a99b8" }}>{t.course} · {t.city}</div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* FINAL CTA BANNER */}
      <div className="sec" style={{ background: "linear-gradient(135deg, #c9a227 0%, #e6bf46 100%)", padding: "56px 24px", textAlign: "center" }}>
        <h2 className="display h2-sec" style={{ fontSize: "clamp(24px,3.5vw,36px)", color: "#1a2a4a", marginBottom: 12 }}>
          Ready to Shape Your Future?
        </h2>
        <p style={{ color: "#4a3500", fontSize: 17, marginBottom: 28, fontFamily: "sans-serif" }}>
          Join 50,000+ students who have transformed their education journey through AcademiaFest.
        </p>
        <div style={{ display: "flex", gap: 16, justifyContent: "center", flexWrap: "wrap" }}>
          <button className="btn-primary" style={{ padding: "14px 36px", fontSize: 16 }} onClick={() => nav("register")}>Register Free — Get Your Pass</button>
          <button style={{ background: "transparent", border: "2px solid #1a2a4a", color: "#1a2a4a", padding: "12px 28px", borderRadius: 6, fontSize: 15, fontWeight: 600, cursor: "pointer", fontFamily: "sans-serif" }} onClick={() => nav("events")}>Browse Events</button>
        </div>
      </div>
    </div>
  );
}

// ─── EVENT CARD ────────────────────────────────────────────────────────────────
function EventCard({ event: e, ctx }) {
  const typeColors = { "MBA Fair": "badge-gold", "UG Fair": "badge-blue", "General Education Fair": "badge-green" };
  const [imgOk, setImgOk] = useState(true);
  const place = [e.venue, e.city].filter(Boolean).join(", ");
  return (
    <div className="event-card">
      {e.photo && imgOk ? (
        <div className="ev-media">
          <img src={e.photo} alt={e.name} loading="lazy" onError={() => setImgOk(false)} />
          <div className="ev-bar" />
        </div>
      ) : (
        <div className="ev-media ev-ph">
          <div style={{ position: "absolute", inset: 0, backgroundImage: "radial-gradient(circle at 25% 40%, rgba(201,162,39,.18) 0%, transparent 55%)" }} />
          <div className="ev-ph-logo"><AcademiaLogo size={48} /></div>
          {e.city && <div className="ev-ph-city">{e.city}</div>}
          <div className="ev-bar" />
        </div>
      )}
      <div className="ev-body">
        <div className="ev-top">
          <span className={`badge ev-badge ${typeColors[e.type] || "badge-blue"}`}>{e.type}</span>
          {e.date && <span className="ev-date">{fmtDate(e.date)}</span>}
        </div>
        <h3 className="display ev-title">{e.name}</h3>
        <div className="ev-meta">
          {place && <span>📍 {place}</span>}
          {e.time && <span>🕙 {e.time}</span>}
        </div>
        {e.desc && <p className="ev-desc">{e.desc}</p>}
        {e.offer && <p className="ev-offer">🎁 {e.offer}</p>}
        <div className="ev-foot">
          <span className="ev-seats">{e.seat_limit ? `Seats: ${e.seat_limit}` : "Free Entry"}</span>
          <button className="btn-gold btn-sm ev-btn" onClick={() => ctx.nav("register", { eventId: e.id })}>Register Free →</button>
        </div>
      </div>
    </div>
  );
}

// ─── EVENTS PAGE ──────────────────────────────────────────────────────────────
function EventsPage({ ctx }) {
  const { events, nav } = ctx;
  const [filter, setFilter] = useState({ city: "", type: "", status: "" });
  const cities = [...new Set(events.map(e => e.city).filter(Boolean))];
  const types = [...new Set(events.map(e => e.type).filter(Boolean))];
  const filtered = events.filter(e =>
    (!filter.city || e.city === filter.city) &&
    (!filter.type || e.type === filter.type) &&
    (!filter.status || e.status === filter.status)
  );
  return (
    <div className="sec-top" style={{ maxWidth: 1200, margin: "0 auto", padding: "40px 24px" }}>
      <h1 className="section-title" style={{ marginBottom: 6 }}>Upcoming Events</h1>
      <p className="section-sub" style={{ marginBottom: 32 }}>Free education fairs across India — find one near you</p>
      {/* Filters */}
      <div className="filters" style={{ display: "flex", gap: 12, flexWrap: "wrap", marginBottom: 32, background: "white", padding: 20, borderRadius: 10, boxShadow: "0 1px 8px rgba(26,42,74,.06)" }}>
        <select className="input-field" style={{ flex: "0 0 180px" }} value={filter.city} onChange={e => setFilter(f => ({ ...f, city: e.target.value }))}>
          <option value="">All Cities</option>
          {cities.map(c => <option key={c}>{c}</option>)}
        </select>
        <select className="input-field" style={{ flex: "0 0 200px" }} value={filter.type} onChange={e => setFilter(f => ({ ...f, type: e.target.value }))}>
          <option value="">All Types</option>
          {types.map(t => <option key={t}>{t}</option>)}
        </select>
        <button className="btn-outline btn-sm" onClick={() => setFilter({ city: "", type: "", status: "" })}>Clear Filters</button>
      </div>
      <div className="events-grid">
        {filtered.map(e => <EventCard key={e.id} event={e} ctx={ctx} />)}
        {filtered.length === 0 && <p style={{ color: "#8a99b8", fontFamily: "sans-serif" }}>No events match your filters.</p>}
      </div>
    </div>
  );
}

// ─── ABOUT PAGE ───────────────────────────────────────────────────────────────
function AboutPage({ ctx }) {
  const { nav } = ctx;
  const timeline = [
    { year: "2019", event: "AcademiaFest founded with the first education fair in Delhi NCR" },
    { year: "2020", event: "Expanded to 5 cities, pioneered hybrid offline-digital fair models" },
    { year: "2021", event: "Reached 10,000 students across 10 cities" },
    { year: "2022", event: "Launched digital pass system, crossed 200+ college partnerships" },
    { year: "2023", event: "Expanded to 20+ cities, 30,000+ students served" },
    { year: "2025", event: "50,000+ students reached across 25+ cities in India" },
  ];
  return (
    <div>
      {/* Hero */}
      <div className="hero-gradient hero" style={{ padding: "70px 24px", textAlign: "center" }}>
        <div style={{ maxWidth: 760, margin: "0 auto" }}>
          <div style={{ display: "inline-block", background: "rgba(201,162,39,.15)", border: "1px solid rgba(201,162,39,.4)", borderRadius: 20, padding: "6px 18px", marginBottom: 20 }}>
            <span style={{ color: "#e6bf46", fontSize: 13, fontWeight: 600, letterSpacing: "1px", textTransform: "uppercase", fontFamily: "sans-serif" }}>Our Story</span>
          </div>
          <h1 className="display hero-title" style={{ fontSize: "clamp(32px,5vw,52px)", color: "white", fontWeight: 700, lineHeight: 1.2, marginBottom: 16 }}>
            About <span className="gold-text">AcademiaFest</span>
          </h1>
          <p style={{ color: "#b0c0d8", fontSize: 18, lineHeight: 1.7, fontFamily: "sans-serif" }}>
            India's fastest-growing offline education event platform — connecting students with their dream institutions since 2019.
          </p>
        </div>
      </div>

      {/* Mission / Vision / Values */}
      <div className="sec-top" style={{ maxWidth: 1200, margin: "0 auto", padding: "64px 24px 0" }}>
        <div style={{ textAlign: "center", marginBottom: 44 }}>
          <h2 className="section-title">What Drives Us</h2>
          <p className="section-sub">Built on a simple belief — every student deserves access to quality education guidance</p>
        </div>
        <div className="grid-3" style={{ marginBottom: 64 }}>
          {[
            { icon: "🎯", title: "Mission", text: "To connect every aspiring student with their ideal institution through face-to-face education fairs across India." },
            { icon: "🔭", title: "Vision", text: "To be India's most trusted offline education event platform, reaching 1 million students annually." },
            { icon: "💎", title: "Values", text: "Transparency, accessibility, student-first approach, and excellence in education guidance define everything we do." },
          ].map((v, i) => (
            <div key={i} className="card pad-card" style={{ padding: "36px 28px", textAlign: "center" }}>
              <div style={{ fontSize: 44, marginBottom: 14 }}>{v.icon}</div>
              <h3 className="display" style={{ fontSize: 22, color: "#1a2a4a", marginBottom: 12 }}>{v.title}</h3>
              <p style={{ color: "#6b7a99", fontSize: 15, lineHeight: 1.7, fontFamily: "sans-serif" }}>{v.text}</p>
            </div>
          ))}
        </div>

        {/* Stats Banner */}
        <div style={{ background: "linear-gradient(135deg, #1a2a4a 0%, #2d4a8a 100%)", borderRadius: 20, padding: "48px 40px", marginBottom: 64 }} className="box-pad">
          <div style={{ textAlign: "center", marginBottom: 36 }}>
            <h2 className="display" style={{ fontSize: 26, color: "white" }}>AcademiaFest by the Numbers</h2>
          </div>
          <div className="stats-grid" style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 2 }}>
            {[["50,000+","Students Reached","🎓"],["200+","Partner Colleges","🏫"],["25+","Cities Covered","🌍"],["5+","Years of Excellence","⭐"]].map(([n, l, icon], i) => (
              <div key={l} style={{ textAlign: "center", padding: "20px 16px", borderRight: i < 3 ? "1px solid rgba(255,255,255,.1)" : "none" }}>
                <div style={{ fontSize: 26, marginBottom: 8 }}>{icon}</div>
                <div className="display stat-n" style={{ fontSize: 34, color: "#e6bf46", fontWeight: 700 }}>{n}</div>
                <div style={{ fontSize: 13, color: "#8a99b8", marginTop: 6, fontFamily: "sans-serif" }}>{l}</div>
              </div>
            ))}
          </div>
        </div>

        {/* Story + Timeline */}
        <div className="stack-mobile" style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 60, alignItems: "start", marginBottom: 64 }}>
          <div>
            <h2 className="section-title" style={{ marginBottom: 20 }}>Our Story</h2>
            <p style={{ color: "#4a5568", fontSize: 16, lineHeight: 1.85, marginBottom: 16, fontFamily: "sans-serif" }}>
              AcademiaFest was born from a simple observation: millions of students across India struggle to find the right college — not because opportunities don't exist, but because they lack access to direct, reliable information.
            </p>
            <p style={{ color: "#4a5568", fontSize: 16, lineHeight: 1.85, marginBottom: 16, fontFamily: "sans-serif" }}>
              Founder Narendra Tiwari spent over a decade inside India's top universities and B-schools and saw this gap firsthand. He created AcademiaFest to bridge it — through high-quality, face-to-face education fairs that are always free for students.
            </p>
            <p style={{ color: "#4a5568", fontSize: 16, lineHeight: 1.85, marginBottom: 28, fontFamily: "sans-serif" }}>
              Today, we operate across 25+ cities, partner with 200+ colleges and universities, and have helped over 50,000 students find their path to higher education.
            </p>
            <button className="btn-gold" onClick={() => nav("founder")}>Meet Our Founder →</button>
          </div>
          <div>
            <h3 className="display" style={{ fontSize: 22, color: "#1a2a4a", marginBottom: 24 }}>Our Journey</h3>
            {timeline.map((t, i) => (
              <div key={i} style={{ display: "flex", gap: 16, marginBottom: 20 }}>
                <div style={{ display: "flex", flexDirection: "column", alignItems: "center", flexShrink: 0 }}>
                  <div style={{ width: 44, height: 44, background: i === timeline.length - 1 ? "#c9a227" : "#1a2a4a", borderRadius: "50%", display: "flex", alignItems: "center", justifyContent: "center", color: i === timeline.length - 1 ? "#1a2a4a" : "#e6bf46", fontSize: 10, fontWeight: 700, fontFamily: "sans-serif" }}>{t.year}</div>
                  {i < timeline.length - 1 && <div style={{ width: 2, flex: 1, background: "#e2e8f0", margin: "6px 0" }} />}
                </div>
                <div style={{ paddingTop: 10, paddingBottom: 16 }}>
                  <p style={{ color: "#4a5568", fontSize: 14, lineHeight: 1.6, fontFamily: "sans-serif" }}>{t.event}</p>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Why Offline */}
        <div style={{ background: "#f1f4fb", borderRadius: 20, padding: "48px 40px", marginBottom: 64 }} className="box-pad">
          <div style={{ textAlign: "center", marginBottom: 40 }}>
            <h2 className="section-title">Why Offline Fairs?</h2>
            <p className="section-sub">In a digital-first world, we believe nothing replaces a real conversation</p>
          </div>
          <div className="grid-3">
            {[
              { icon: "🤝", title: "Real Connections", desc: "Meet actual admission counsellors, not chatbots. Ask real questions and build relationships that open doors." },
              { icon: "📊", title: "Compare Instantly", desc: "Visit 20+ colleges in a single day. Compare courses, fees, scholarships, and campus culture side-by-side." },
              { icon: "🎯", title: "Personalised Guidance", desc: "Get advice tailored to your profile and aspirations — not generic information sheets." },
            ].map((f, i) => (
              <div key={i} style={{ textAlign: "center" }}>
                <div style={{ fontSize: 44, marginBottom: 14 }}>{f.icon}</div>
                <h3 className="display" style={{ fontSize: 18, color: "#1a2a4a", marginBottom: 10 }}>{f.title}</h3>
                <p style={{ color: "#6b7a99", fontSize: 14, lineHeight: 1.7, fontFamily: "sans-serif" }}>{f.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── FOUNDER PAGE ─────────────────────────────────────────────────────────────
function FounderPage({ ctx }) {
  const { nav } = ctx;
  const [imgError, setImgError] = useState(false);
  const exp = [
    { org: "GD Goenka University, Gurgaon", role: "National Head, Marketing & Admission Outreach", period: "2017–2024" },
    { org: "JK Business School, Gurgaon", role: "Senior Manager", period: "2014–2017" },
    { org: "IIMT Group of Colleges, Greater Noida", role: "Manager, Marketing & Admission", period: "2013–2014" },
    { org: "Accurate Group of Institution, Greater Noida", role: "Asst. Manager, Marketing & Admission", period: "2011–2013" },
    { org: "CBA Noida, Chronicle Group Initiative", role: "Manager, Marketing", period: "2009–2011" },
  ];
  return (
    <div>
      {/* Hero */}
      <div className="hero-gradient hero" style={{ padding: "70px 24px 90px" }}>
        <div style={{ maxWidth: 1000, margin: "0 auto", display: "flex", gap: 52, alignItems: "center", flexWrap: "wrap", justifyContent: "center" }}>
          {/* Photo */}
          <div style={{ position: "relative", flexShrink: 0 }}>
            <div className="founder-photo" style={{ width: 220, height: 270, borderRadius: 16, overflow: "hidden", border: "4px solid rgba(201,162,39,.5)", boxShadow: "0 24px 64px rgba(0,0,0,.45)" }}>
              {!imgError ? (
                <img src="/founder.webp" alt="Narendra Tiwari" style={{ width: "100%", height: "100%", objectFit: "cover", objectPosition: "center top" }} onError={() => setImgError(true)} />
              ) : (
                <div style={{ width: "100%", height: "100%", background: "linear-gradient(135deg, #1e3a6e, #2d4a8a)", display: "flex", alignItems: "center", justifyContent: "center" }}>
                  <span className="display" style={{ fontSize: 72, color: "#e6bf46" }}>NT</span>
                </div>
              )}
            </div>
            <div style={{ position: "absolute", bottom: -16, left: "50%", transform: "translateX(-50%)", background: "linear-gradient(135deg, #c9a227, #e6bf46)", borderRadius: 20, padding: "7px 22px", whiteSpace: "nowrap", boxShadow: "0 4px 16px rgba(201,162,39,.4)" }}>
              <span style={{ color: "#1a2a4a", fontSize: 12, fontWeight: 700, letterSpacing: "0.5px", fontFamily: "sans-serif" }}>Founder &amp; Visionary</span>
            </div>
          </div>
          {/* Info */}
          <div style={{ flex: 1, minWidth: 260 }}>
            <div style={{ display: "inline-block", background: "rgba(201,162,39,.15)", border: "1px solid rgba(201,162,39,.4)", borderRadius: 20, padding: "5px 16px", marginBottom: 16 }}>
              <span style={{ color: "#e6bf46", fontSize: 12, fontWeight: 600, letterSpacing: "1px", textTransform: "uppercase", fontFamily: "sans-serif" }}>Meet the Founder</span>
            </div>
            <h1 className="display hero-title" style={{ fontSize: "clamp(32px,4vw,52px)", color: "white", fontWeight: 700, marginBottom: 8 }}>Narendra Tiwari</h1>
            <p style={{ color: "#c9a227", fontSize: 17, fontWeight: 600, marginBottom: 20, fontFamily: "sans-serif" }}>Founder &amp; CEO, AcademiaFest</p>
            <p style={{ color: "#b0c0d8", fontSize: 16, lineHeight: 1.8, marginBottom: 28, fontFamily: "sans-serif" }}>
              An education industry veteran with over a decade of experience in marketing, admission outreach, and institution building across India's leading universities and B-schools.
            </p>
            <div style={{ display: "flex", gap: 32, flexWrap: "wrap" }}>
              {[["10+", "Years Experience"], ["50K+", "Students Impacted"], ["5", "Top Institutions"]].map(([n, l]) => (
                <div key={l}>
                  <div className="display" style={{ fontSize: 26, color: "#e6bf46", fontWeight: 700 }}>{n}</div>
                  <div style={{ fontSize: 12, color: "#8a99b8", fontFamily: "sans-serif", marginTop: 2 }}>{l}</div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* Quote */}
      <div style={{ background: "#fffbf0", borderTop: "1px solid #fde68a", borderBottom: "1px solid #fde68a" }}>
        <div style={{ maxWidth: 800, margin: "0 auto", padding: "44px 24px", textAlign: "center" }}>
          <div style={{ fontSize: 52, color: "#c9a227", lineHeight: 1, marginBottom: 10, fontFamily: "Georgia, serif" }}>"</div>
          <p className="display founder-quote" style={{ fontSize: "clamp(17px,2.5vw,23px)", color: "#1a2a4a", lineHeight: 1.65, marginBottom: 16, fontStyle: "italic" }}>
            Every student in India deserves to sit across from a college counsellor and ask their real questions — without paying anything. That's the promise of AcademiaFest.
          </p>
          <div style={{ color: "#8a99b8", fontSize: 14, fontFamily: "sans-serif" }}>— Narendra Tiwari, Founder</div>
        </div>
      </div>

      {/* Main Content */}
      <div className="sec-top" style={{ maxWidth: 1000, margin: "0 auto", padding: "60px 24px" }}>
        <div className="stack-mobile" style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 48, marginBottom: 56 }}>
          {/* Bio */}
          <div>
            <h2 className="display" style={{ fontSize: 26, color: "#1a2a4a", marginBottom: 20, paddingBottom: 12, borderBottom: "3px solid #c9a227" }}>The Story</h2>
            <p style={{ color: "#4a5568", fontSize: 15, lineHeight: 1.85, marginBottom: 16, fontFamily: "sans-serif" }}>
              Narendra Tiwari began his career in education marketing at CBA Noida, quickly rising through the ranks to take on progressively larger roles at some of India's most respected institutions — including IIMT Group, Accurate Group, JK Business School, and ultimately GD Goenka University as National Head of Marketing &amp; Admission Outreach.
            </p>
            <p style={{ color: "#4a5568", fontSize: 15, lineHeight: 1.85, marginBottom: 16, fontFamily: "sans-serif" }}>
              Through this journey, he witnessed one recurring truth: India's best colleges existed, ambitious students existed, but the connection between them was broken. Information asymmetry and geographic barriers left students confused and institutions unable to reach the right candidates.
            </p>
            <p style={{ color: "#4a5568", fontSize: 15, lineHeight: 1.85, fontFamily: "sans-serif" }}>
              AcademiaFest was his answer — a free, face-to-face platform where students meet colleges and careers begin. Built on the belief that guidance should never be gated behind money or geography.
            </p>
          </div>
          {/* Education & Skills */}
          <div>
            <h2 className="display" style={{ fontSize: 26, color: "#1a2a4a", marginBottom: 20, paddingBottom: 12, borderBottom: "3px solid #c9a227" }}>Education</h2>
            {[{ degree: "MBA", field: "Marketing and Human Resources", icon: "🎓" }, { degree: "BSc", field: "Environmental and Water Management", icon: "🔬" }].map((e, i) => (
              <div key={i} className="card" style={{ padding: "18px 22px", marginBottom: 14, display: "flex", gap: 14, alignItems: "center" }}>
                <div style={{ width: 48, height: 48, background: "#f1f4fb", borderRadius: 12, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 22, flexShrink: 0 }}>{e.icon}</div>
                <div>
                  <div className="display" style={{ fontSize: 17, color: "#1a2a4a", fontWeight: 700 }}>{e.degree}</div>
                  <div style={{ color: "#6b7a99", fontSize: 13, fontFamily: "sans-serif", marginTop: 2 }}>{e.field}</div>
                </div>
              </div>
            ))}
            <h2 className="display" style={{ fontSize: 22, color: "#1a2a4a", marginBottom: 16, paddingBottom: 10, borderBottom: "2px solid #e2e8f0", marginTop: 28 }}>Core Expertise</h2>
            <div style={{ display: "flex", flexWrap: "wrap", gap: 8 }}>
              {["Education Marketing", "Admission Outreach", "Institution Building", "Student Counselling", "Event Management", "College Partnerships", "Brand Strategy", "Team Leadership"].map(skill => (
                <span key={skill} style={{ background: "#f1f4fb", color: "#1a2a4a", padding: "6px 14px", borderRadius: 20, fontSize: 13, fontWeight: 600, fontFamily: "sans-serif" }}>{skill}</span>
              ))}
            </div>
          </div>
        </div>

        {/* Experience Timeline */}
        <h2 className="display" style={{ fontSize: 26, color: "#1a2a4a", marginBottom: 32, textAlign: "center" }}>Professional Journey</h2>
        <div style={{ marginBottom: 56 }}>
          {exp.map((e, i) => (
            <div key={i} style={{ display: "flex", gap: 24, marginBottom: 20 }}>
              <div style={{ display: "flex", flexDirection: "column", alignItems: "center", flexShrink: 0, width: 48 }}>
                <div style={{ width: 48, height: 48, background: i === 0 ? "linear-gradient(135deg, #c9a227, #e6bf46)" : "#f1f4fb", border: `2px solid ${i === 0 ? "#c9a227" : "#dde3ef"}`, borderRadius: "50%", display: "flex", alignItems: "center", justifyContent: "center", color: i === 0 ? "#1a2a4a" : "#8a99b8", fontWeight: 700, fontSize: 16 }}>
                  {i === 0 ? "★" : (exp.length - i)}
                </div>
                {i < exp.length - 1 && <div style={{ width: 2, flex: 1, background: "#e2e8f0", margin: "6px 0" }} />}
              </div>
              <div className="card" style={{ flex: 1, padding: "18px 22px", border: i === 0 ? "2px solid rgba(201,162,39,.35)" : "1.5px solid #f0f2f8" }}>
                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start", flexWrap: "wrap", gap: 8 }}>
                  <div>
                    <div style={{ fontWeight: 700, fontSize: 15, color: "#1a2a4a" }}>{e.role}</div>
                    <div style={{ color: "#6b7a99", fontSize: 13, marginTop: 3, fontFamily: "sans-serif" }}>{e.org}</div>
                  </div>
                  <span className="badge badge-blue">{e.period}</span>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* CTA */}
        <div className="box-pad" style={{ textAlign: "center", padding: "48px 40px", background: "linear-gradient(135deg, #1a2a4a 0%, #2d4a8a 100%)", borderRadius: 20, color: "white" }}>
          <h2 className="display h2-sec" style={{ fontSize: 28, marginBottom: 12 }}>Join Narendra's Vision</h2>
          <p style={{ color: "#b0c0d8", fontSize: 16, marginBottom: 28, fontFamily: "sans-serif" }}>
            Be part of India's largest free education fair network — as a student or as a college partner.
          </p>
          <div style={{ display: "flex", gap: 16, justifyContent: "center", flexWrap: "wrap" }}>
            <button className="btn-gold" onClick={() => nav("register")}>Register for Free Event</button>
            <button className="btn-outline" style={{ color: "white", borderColor: "rgba(255,255,255,.4)" }} onClick={() => nav("contact")}>College Partnership →</button>
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── CONTACT PAGE ─────────────────────────────────────────────────────────────
function ContactPage({ ctx }) {
  const [sent, setSent] = useState(false);
  const [form, setForm] = useState({ name: "", email: "", mobile: "", org: "", msg: "" });
  const handleSubmit = (e) => { e.preventDefault(); setSent(true); };
  return (
    <div className="sec-top" style={{ maxWidth: 900, margin: "0 auto", padding: "50px 24px" }}>
      <h1 className="section-title" style={{ marginBottom: 6 }}>Contact Us</h1>
      <p className="section-sub" style={{ marginBottom: 40 }}>Get in touch for college partnerships, event inquiries, or student support</p>
      <div className="stack-mobile" style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 40 }}>
        <div className="card pad-card-lg" style={{ padding: "32px 28px" }}>
          <h2 className="display" style={{ fontSize: 22, marginBottom: 24, color: "#1a2a4a" }}>Send a Message</h2>
          {sent ? (
            <div style={{ textAlign: "center", padding: "32px 0" }}>
              <div style={{ fontSize: 48, marginBottom: 16 }}>✅</div>
              <h3 className="display" style={{ fontSize: 22, color: "#1a2a4a", marginBottom: 8 }}>Message Sent!</h3>
              <p style={{ color: "#6b7a99", fontFamily: "sans-serif" }}>We'll get back to you within 24 hours.</p>
            </div>
          ) : (
            <form onSubmit={handleSubmit}>
              {[["name","Full Name","text"],["email","Email","email"],["mobile","Mobile Number","tel"],["org","Organisation (if any)","text"]].map(([k, l, t]) => (
                <div className="form-group" key={k}>
                  <label className="label">{l}</label>
                  <input className="input-field" type={t} value={form[k]} onChange={e => setForm(f => ({ ...f, [k]: e.target.value }))} required={k !== "org"} />
                </div>
              ))}
              <div className="form-group">
                <label className="label">Message</label>
                <textarea className="input-field" rows={4} value={form.msg} onChange={e => setForm(f => ({ ...f, msg: e.target.value }))} required style={{ resize: "vertical" }} />
              </div>
              <button className="btn-primary" type="submit" style={{ width: "100%" }}>Send Message</button>
            </form>
          )}
        </div>
        <div>
          <div className="card" style={{ padding: "28px 24px", marginBottom: 20 }}>
            <h2 className="display" style={{ fontSize: 20, marginBottom: 20, color: "#1a2a4a" }}>College Partnership</h2>
            <p style={{ color: "#4a5568", fontSize: 15, lineHeight: 1.7, fontFamily: "sans-serif", marginBottom: 16 }}>Showcase your institution at AcademiaFest education fairs. Reach thousands of qualified students across India.</p>
            <ul style={{ listStyle: "none", padding: 0 }}>
              {["Brand visibility at premium events","Direct student interaction","Qualified lead generation","Nationwide reach"].map((item, i) => (
                <li key={i} style={{ display: "flex", gap: 8, marginBottom: 8, fontSize: 14, color: "#4a5568", fontFamily: "sans-serif" }}>
                  <span style={{ color: "#c9a227" }}>✓</span> {item}
                </li>
              ))}
            </ul>
          </div>
          <div style={{ background: "#1a2a4a", borderRadius: 12, padding: "24px 20px", color: "white" }}>
            <div style={{ fontWeight: 700, marginBottom: 14, fontSize: 16 }}>Direct Contact</div>
            <p style={{ color: "#a0b4cc", fontSize: 14, lineHeight: 2, fontFamily: "sans-serif" }}>
              📧 info@academiafest.com<br />
              📞 +91 90153 91313<br />
              💬 WhatsApp: +91 90153 91313<br />
              📍 Laxmi Niwas, Baily Road, Patna
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
