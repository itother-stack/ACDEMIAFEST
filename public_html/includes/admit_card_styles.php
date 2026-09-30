<?php // Registration form + confirmation + admit card styles (AcademiaFest theme, same as the main website) ?>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
    :root { --navy: #1a2a4a; --navy-dark: #0f1f3d; --blue: #2d4a8a; --gold: #c9a227; --gold-light: #e6bf46; --muted: #6b7a99; }
    * { box-sizing: border-box; }
    body { font-family: 'Source Sans 3', system-ui, sans-serif; background-color: #f8f9fc; color: #1a2a4a; margin: 0; padding: 0; }
    .display { font-family: 'Playfair Display', Georgia, serif; }

    /* ── Page hero (same gradient as the main website) ── */
    .reg-hero { background: linear-gradient(135deg, #0f1f3d 0%, #1a2a4a 40%, #1e3a6e 100%); padding: 44px 24px 96px; text-align: center; }
    .reg-hero-badge { display: inline-block; background: rgba(201,162,39,.15); border: 1px solid rgba(201,162,39,.4); border-radius: 20px; padding: 5px 16px; color: #e6bf46; font-size: 12px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 14px; }
    .reg-hero h1 { font-family: 'Playfair Display', Georgia, serif; color: #fff; font-size: clamp(26px, 4vw, 40px); font-weight: 700; line-height: 1.2; margin: 0 0 10px; }
    .reg-hero h1 span { color: #e6bf46; }
    .reg-hero p { color: #b0c0d8; font-size: 16px; margin: 0; }
    .reg-wrap { max-width: 760px; margin: -64px auto 0; padding: 0 16px; position: relative; }

    .premium-card { background: #fff; border-radius: 14px; box-shadow: 0 8px 32px rgba(26,42,74,.10); border: 1px solid #eef1f8; padding: 24px; }
    @media (min-width: 768px) { .premium-card { padding: 40px; } }
    .form-head { text-align: center; margin-bottom: 28px; }
    .form-head svg, .form-head img { display: block; margin: 0 auto; }
    .form-brand-tag { font-family: 'Source Sans 3', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #c9a227; margin: 10px 0 2px; }
    .form-head h2 { font-family: 'Playfair Display', Georgia, serif; font-size: 24px; font-weight: 700; color: #1a2a4a; margin: 6px 0 4px; }
    .form-head p { color: #6b7a99; font-size: 14px; margin: 0; }
    .form-section { font-family: 'Playfair Display', Georgia, serif; font-size: 16px; font-weight: 700; color: #1a2a4a; border-left: 3px solid #c9a227; padding-left: 10px; margin: 6px 0 16px; }

    label { font-size: 13px; font-weight: 600; color: #4a5568; letter-spacing: .3px; margin-bottom: 6px; display: block; }
    input, select, textarea { width: 100%; padding: 11px 14px; border: 1.5px solid #dde3ef; border-radius: 8px; font-size: 15px; background: #fff; color: #1a2a4a; transition: border-color .2s, box-shadow .2s; box-sizing: border-box; font-family: 'Source Sans 3', sans-serif; }
    input:focus, select:focus, textarea:focus { outline: none; border-color: #2d4a8a; box-shadow: 0 0 0 3px rgba(45,74,138,.10); }
    input[type=checkbox], input[type=radio] { width: 16px; height: 16px; padding: 0; accent-color: #c9a227; }
    .choice { display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 14px; color: #1a2a4a; cursor: pointer; margin: 0; }
    .course-chip { display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 13px; color: #1a2a4a; background: #f8f9fc; border: 1.5px solid #e8edf8; border-radius: 8px; padding: 10px 12px; cursor: pointer; margin: 0; transition: border-color .2s, background .2s; }
    .course-chip:hover { border-color: #c9a227; background: #fffbeb; }
    .submit-btn { width: 100%; background: linear-gradient(135deg, #c9a227 0%, #e6bf46 100%); color: #1a2a4a; border: none; padding: 15px 20px; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; box-shadow: 0 6px 20px rgba(201,162,39,.35); transition: opacity .2s, transform .1s; font-family: 'Source Sans 3', sans-serif; }
    .submit-btn:hover { opacity: .92; transform: translateY(-1px); }

    .download-btn { background: linear-gradient(135deg, #1a2a4a 0%, #2d4a8a 100%); color: #fff; border: none; padding: 14px 24px; font-size: 16px; font-weight: 700; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; margin: 20px auto; width: 100%; max-width: 320px; box-shadow: 0 8px 20px rgba(26,42,74,.25); font-family: 'Source Sans 3', sans-serif; }

    /* WhatsApp delivery status box */
    .wa-status { max-width: 480px; margin: 0 auto 8px; padding: 12px 16px; border-radius: 10px; font-size: 14px; font-weight: 600; text-align: center; }
    .wa-status.pending { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
    .wa-status.success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .wa-status.error   { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .wa-status button  { margin-left: 8px; padding: 4px 12px; border-radius: 8px; border: 1px solid currentColor; background: #fff; color: inherit; font-weight: 700; cursor: pointer; }

    /* Confirmation summary card */
    .confirm-card { max-width: 460px; margin: 0 auto 20px; background: #fff; border: 1px solid #eef1f8; border-top: 4px solid #c9a227; border-radius: 14px; padding: 28px 24px 22px; box-shadow: 0 8px 32px rgba(26,42,74,.10); text-align: center; }
    .confirm-icon { width: 54px; height: 54px; margin: 0 auto 12px; border-radius: 50%; background: linear-gradient(135deg, #c9a227, #e6bf46); display: flex; align-items: center; justify-content: center; color: #1a2a4a; font-size: 26px; font-weight: 800; }
    .confirm-title { font-family: 'Playfair Display', Georgia, serif; font-size: 22px; font-weight: 700; color: #1a2a4a; margin: 0 0 16px; }
    .confirm-details { text-align: left; border-top: 1px dashed #dde3ef; padding-top: 14px; }
    .confirm-row { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; padding: 7px 0; }
    .confirm-row .c-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #8a99b8; white-space: nowrap; }
    .confirm-row .c-value { font-size: 15px; font-weight: 700; color: #1a2a4a; text-align: right; }
    .confirm-row .c-value.accent { color: #b8860b; }

    /* Confirmation card showing the real entry pass instead of a text summary */
    .confirm-card-pass { max-width: 560px; padding: 28px 20px 24px; }
    .preview-box { width: 100%; overflow: hidden; border-radius: 12px; margin-top: 4px; }
    .preview-box .image-admit-card-wrapper { transform-origin: top left; border-radius: 12px; }
    @media (max-width: 640px) {
        .confirm-card-pass { max-width: 100%; padding: 22px 14px 18px; }
    }

    @media (max-width: 768px) {
        .reg-hero { padding: 30px 16px 80px; }
        .reg-hero p { font-size: 14px; }
        .reg-wrap { margin-top: -58px; padding: 0 12px; }
        .premium-card { padding: 20px 16px; border-radius: 12px; }
        .form-head { margin-bottom: 20px; }
        .form-head h2 { font-size: 20px; }
        label { font-size: 12.5px; }
        input, select, textarea { font-size: 16px; padding: 10px 12px; } /* 16px stops iPhone auto-zoom */
        .course-chip { padding: 9px 10px; font-size: 12.5px; }
        .confirm-card { padding: 22px 16px 18px; }
        .confirm-title { font-size: 19px; }
        .confirm-row .c-value { font-size: 14px; }
    }

    /* ══════════════════════════════════════════════════════════════════
       ADMIT CARD — rendered at a fixed 800px width into the PDF
       ══════════════════════════════════════════════════════════════════ */
    .image-admit-card-wrapper { position: relative; width: 800px; max-width: 100%; margin: 0 auto; background: #fff; overflow: hidden; font-family: 'Source Sans 3', Arial, sans-serif; color: #1a2a4a; border: 1px solid #e8edf8; }
    /* Hidden source — only its 800px clone is rendered into the PDF */
    .admit-source-hidden { position: absolute; left: -99999px; top: 0; }

    .ac-head { background: linear-gradient(135deg, #0f1f3d 0%, #1a2a4a 45%, #1e3a6e 100%); padding: 26px 34px; display: flex; align-items: center; justify-content: space-between; }
    .ac-brand { display: flex; align-items: center; gap: 14px; }
    .ac-logo { width: 62px; height: 62px; background: #fff; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .ac-brand-name { font-family: 'Playfair Display', Georgia, serif; font-size: 28px; font-weight: 700; color: #fff; line-height: 1; }
    .ac-brand-tag { font-size: 11px; color: #b0c0d8; letter-spacing: 2px; text-transform: uppercase; margin-top: 5px; }
    .ac-title { text-align: right; }
    .ac-pill { display: inline-block; background: linear-gradient(135deg, #c9a227, #e6bf46); color: #1a2a4a; font-weight: 800; font-size: 15px; letter-spacing: 3px; padding: 8px 18px; border-radius: 30px; }
    .ac-sub { color: #b0c0d8; font-size: 12px; letter-spacing: 1.5px; text-transform: uppercase; margin-top: 7px; }
    .ac-goldbar { height: 6px; background: linear-gradient(90deg, #1a2a4a, #c9a227, #e6bf46); }

    .ac-event { padding: 26px 34px 6px; }
    .ac-kicker { font-size: 12px; font-weight: 700; color: #b8860b; letter-spacing: 2.5px; text-transform: uppercase; }
    .ac-event-name { font-family: 'Playfair Display', Georgia, serif; font-size: 30px; font-weight: 700; color: #1a2a4a; line-height: 1.2; margin-top: 6px; }

    .ac-body { display: flex; gap: 28px; padding: 18px 34px 26px; align-items: stretch; }
    .ac-fields { flex: 1; min-width: 0; }
    .ac-row { display: flex; align-items: center; gap: 14px; padding: 12px 0; border-bottom: 1px dashed #dde3ef; }
    .ac-row:last-child { border-bottom: none; }
    .ac-ico { width: 38px; height: 38px; flex: 0 0 38px; border-radius: 10px; background: #f1f4fb; display: flex; align-items: center; justify-content: center; }
    .ac-label { font-size: 11px; font-weight: 700; color: #8a99b8; letter-spacing: 1.5px; text-transform: uppercase; }
    .ac-value { font-size: 18px; font-weight: 700; color: #1a2a4a; line-height: 1.3; word-break: break-word; }
    .ac-value.name { font-family: 'Playfair Display', Georgia, serif; font-size: 22px; }
    .ac-value.reg { color: #b8860b; letter-spacing: 1px; }

    .ac-qr { flex: 0 0 230px; background: #f8f9fc; border: 1.5px solid #e8edf8; border-radius: 14px; padding: 18px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .ac-qr img { width: 190px; height: 190px; display: block; background: #fff; padding: 8px; border-radius: 8px; border: 1px solid #e8edf8; }
    .ac-qr-text { margin-top: 12px; font-size: 12px; font-weight: 800; letter-spacing: 1.5px; color: #1a2a4a; text-transform: uppercase; line-height: 1.5; }

    .ac-notes { margin: 0 34px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 12px 16px; display: flex; justify-content: space-between; gap: 10px; font-size: 13px; font-weight: 600; color: #92400e; }
    .ac-foot { margin-top: 22px; background: #0f1f3d; color: #a0b0cc; padding: 14px 34px; display: flex; justify-content: space-between; font-size: 12.5px; }
    .ac-foot b { color: #e6bf46; font-weight: 700; }

    /* ── Custom Entry Pass (per-event uploaded template, admin > Create/Edit Event) ── */
    .ac-custom { position: relative; width: 100%; line-height: 0; }
    .ac-custom-bg { width: 100%; display: block; }
    .ac-custom-name, .ac-custom-reg {
        position: absolute; transform: translate(-50%, -50%); max-width: 92%;
        font-family: 'Playfair Display', Georgia, serif; font-weight: 700; color: #1a2a4a;
        background: rgba(255,255,255,.82); padding: 4px 14px; border-radius: 6px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.3;
    }
    .ac-custom-name { font-size: 26px; }
    .ac-custom-reg { font-size: 19px; color: #b8860b; letter-spacing: .5px; }
    .ac-custom-qr { position: absolute; transform: translate(-50%, -50%); aspect-ratio: 1 / 1; background: #fff; padding: 4px; box-sizing: border-box; }
</style>
