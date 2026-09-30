<?php
// Public site header — matches the main AcademiaFest website (navbar, fonts, colours)
$h = function ($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $h(isset($pageTitle) ? $pageTitle : ('Event Registration — ' . BRAND_NAME)) ?></title>
    <meta name="description" content="Register free for <?= $h(BRAND_NAME) ?> education fairs and get your digital admit card instantly.">
    <link rel="icon" href="<?= brand_favicon_uri() ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        .af-nav { background: #fff; border-bottom: 1.5px solid #e8edf8; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 12px rgba(26,42,74,.06); }
        .af-nav-inner { max-width: 1200px; margin: 0 auto; padding: 0 24px; display: flex; align-items: center; justify-content: space-between; height: 64px; }
        .af-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .af-brand-name { font-family: 'Playfair Display', Georgia, serif; font-size: 20px; font-weight: 700; color: #1a2a4a; line-height: 1; }
        .af-brand-tag { font-size: 10px; color: #8a99b8; letter-spacing: 1px; text-transform: uppercase; font-family: sans-serif; }
        .af-links { display: flex; gap: 28px; align-items: center; }
        .af-link { color: #4a5568; text-decoration: none; font-size: 15px; font-weight: 600; padding: 6px 2px; border-bottom: 2px solid transparent; transition: color .2s, border-color .2s; font-family: 'Source Sans 3', sans-serif; }
        .af-link:hover, .af-link.active { color: #1a2a4a; border-bottom-color: #c9a227; }
        .af-btn-gold { background: linear-gradient(135deg, #c9a227 0%, #e6bf46 100%); color: #1a2a4a; border: none; padding: 7px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; font-family: 'Source Sans 3', sans-serif; display: inline-block; }
        .af-menu-btn { display: none; background: none; border: none; font-size: 24px; cursor: pointer; color: #1a2a4a; }
        .af-mobile { display: none; border-top: 1px solid #e8edf8; padding: 12px 24px 18px; flex-direction: column; gap: 14px; background: #fff; }
        .af-mobile.open { display: flex; }
        .af-mobile .af-link { align-self: flex-start; }
        @media (max-width: 768px) {
            .af-links { display: none; }
            .af-menu-btn { display: block; }
            .af-nav-inner { padding: 0 16px; height: 58px; }
            .af-brand-name { font-size: 18px; }
        }
    </style>
</head>
<body>
<nav class="af-nav">
    <div class="af-nav-inner">
        <a href="/" class="af-brand">
            <?= brand_logo_svg(36) ?>
            <div>
                <div class="af-brand-name"><?= $h(BRAND_NAME) ?></div>
                <div class="af-brand-tag"><?= $h(BRAND_TAGLINE) ?></div>
            </div>
        </a>
        <div class="af-links">
            <a class="af-link" href="/">Home</a>
            <a class="af-link" href="/#events">Events</a>
            <a class="af-link" href="/#about">About</a>
            <a class="af-link" href="/#founder">Founder</a>
            <a class="af-link" href="/#contact">Contact</a>
            <a class="af-btn-gold" href="/register/">Register Free</a>
            <a class="af-link" href="/admin/" style="font-size:13px">Admin</a>
        </div>
        <button class="af-menu-btn" type="button" aria-label="Open menu" onclick="document.getElementById('afMobileMenu').classList.toggle('open')">☰</button>
    </div>
    <div class="af-mobile" id="afMobileMenu">
        <a class="af-link" href="/">Home</a>
        <a class="af-link" href="/#events">Events</a>
        <a class="af-link" href="/#about">About</a>
        <a class="af-link" href="/#founder">Founder</a>
        <a class="af-link" href="/#contact">Contact</a>
        <a class="af-btn-gold" href="/register/" style="align-self:flex-start">Register Free</a>
    </div>
</nav>
