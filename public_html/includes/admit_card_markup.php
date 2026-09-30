<?php
// Hidden admit card source (AcademiaFest design) — the PDF is generated from this.
// Requires: $passData
$e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$icon = function ($path) { return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1a2a4a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>'; };
?>
            <div class="image-admit-card-wrapper admit-source-hidden" id="eventPass">
            <?php if (!empty($passData['entry_bg'])): $p = $passData['entry_pos']; ?>
                <div class="ac-custom">
                    <img class="ac-custom-bg" src="<?= $e($passData['entry_bg']) ?>" alt="Entry Pass" crossorigin="anonymous">
                    <div class="ac-custom-name" style="left:<?= (int)$p['name_x'] ?>%; top:<?= (int)$p['name_y'] ?>%;"><?= $e($passData['student_name']) ?></div>
                    <div class="ac-custom-reg" style="left:<?= (int)$p['reg_x'] ?>%; top:<?= (int)$p['reg_y'] ?>%;"><?= $e($passData['pass_id']) ?></div>
                    <img class="ac-custom-qr" style="left:<?= (int)$p['qr_x'] ?>%; top:<?= (int)$p['qr_y'] ?>%; width:<?= (int)$p['qr_size'] ?>%;" src="<?= $e($passData['qr_image_url']) ?>" alt="QR Code" crossorigin="anonymous">
                </div>
            <?php else: ?>
                <div class="ac-head">
                    <div class="ac-brand">
                        <div class="ac-logo"><?= brand_logo_svg(46) ?></div>
                        <div>
                            <div class="ac-brand-name"><?= $e(BRAND_NAME) ?></div>
                            <div class="ac-brand-tag"><?= $e(BRAND_TAGLINE) ?></div>
                        </div>
                    </div>
                    <div class="ac-title">
                        <div class="ac-pill">ADMIT CARD</div>
                        <div class="ac-sub">Free Entry Pass</div>
                    </div>
                </div>
                <div class="ac-goldbar"></div>

                <div class="ac-event">
                    <div class="ac-kicker">Education Fair</div>
                    <div class="ac-event-name"><?= $e($passData['event_name']) ?></div>
                </div>

                <div class="ac-body">
                    <div class="ac-fields">
                        <div class="ac-row">
                            <div class="ac-ico"><?= $icon('<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>') ?></div>
                            <div><div class="ac-label">Student Name</div><div class="ac-value name"><?= $e($passData['student_name']) ?></div></div>
                        </div>
                        <div class="ac-row">
                            <div class="ac-ico"><?= $icon('<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line>') ?></div>
                            <div><div class="ac-label">Registration No.</div><div class="ac-value reg"><?= $e($passData['pass_id']) ?></div></div>
                        </div>
                        <div class="ac-row">
                            <div class="ac-ico"><?= $icon('<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>') ?></div>
                            <div><div class="ac-label">Date</div><div class="ac-value"><?= $e($passData['event_date']) ?></div></div>
                        </div>
                        <div class="ac-row">
                            <div class="ac-ico"><?= $icon('<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>') ?></div>
                            <div><div class="ac-label">Time</div><div class="ac-value"><?= $e($passData['event_time'] ?? EVENT_TIME_TEXT) ?></div></div>
                        </div>
                        <div class="ac-row">
                            <div class="ac-ico"><?= $icon('<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle>') ?></div>
                            <div><div class="ac-label">Venue</div><div class="ac-value"><?= $e($passData['event_venue']) ?></div></div>
                        </div>
                    </div>
                    <div class="ac-qr">
                        <img src="<?= $e($passData['qr_image_url']) ?>" alt="QR Code" crossorigin="anonymous">
                        <div class="ac-qr-text">Show this card<br>at the entry</div>
                    </div>
                </div>

                <div class="ac-notes">
                    <span>✓ Entry is free</span>
                    <span>✓ Carry a valid photo ID</span>
                    <span>✓ Reach 15 minutes early</span>
                </div>
                <div class="ac-foot">
                    <span><b><?= $e(BRAND_NAME) ?></b> — India's Trusted Education Event Platform</span>
                    <span><?= $e(BRAND_EMAIL) ?> · <?= $e(BRAND_PHONE) ?></span>
                </div>
            <?php endif; ?>
            </div>
