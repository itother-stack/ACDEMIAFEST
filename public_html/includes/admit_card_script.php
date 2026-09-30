<?php // PDF generation + WhatsApp send script. Requires: $passData, $waReady, $waMasked, $waNonce, $waEndpoint, $waCsrf, $waAutoSend ?>
            <script>
            const PASS_FILE = <?= json_encode('AdmitCard_' . $passData['pass_id'] . '.pdf') ?>;
            const WA_CFG = {
                enabled: <?= $waReady ? 'true' : 'false' ?>,
                autoSend: <?= (isset($waAutoSend) ? $waAutoSend : true) ? 'true' : 'false' ?>,
                endpoint: <?= json_encode(isset($waEndpoint) ? $waEndpoint : '?send_admit_wa=1') ?>,
                csrf: <?= json_encode(isset($waCsrf) ? $waCsrf : $_SESSION['csrf_token']) ?>,
                nonce: <?= json_encode(isset($waNonce) ? $waNonce : '') ?>,
                phone: <?= json_encode($waMasked) ?>
            };

            function waitForImages(root) {
                const imgs = Array.from(root.querySelectorAll('img'));
                return Promise.all(imgs.map(function (img) {
                    return img.complete ? Promise.resolve() : new Promise(function (resolve) {
                        img.onload = img.onerror = resolve;
                    });
                }));
            }

            // Always renders the card at a fixed 800px width (so the PDF is identical on mobile too)
            async function makePassPdf(mode, quality) {
                const src = document.getElementById('eventPass');
                const holder = document.createElement('div');
                holder.style.cssText = 'position:absolute;left:-10000px;top:0;width:800px;';
                const clone = src.cloneNode(true);
                clone.removeAttribute('id');
                clone.classList.remove('admit-source-hidden');
                clone.style.position = 'static';
                clone.style.left = 'auto';
                clone.style.width = '800px';
                clone.style.maxWidth = 'none';
                clone.style.boxShadow = 'none';
                holder.appendChild(clone);
                document.body.appendChild(holder);

                try {
                    await waitForImages(clone);
                    const opt = {
                        margin: 0,
                        filename: PASS_FILE,
                        image: { type: 'jpeg', quality: quality },
                        html2canvas: { scale: 2, useCORS: true, logging: false, scrollX: 0, scrollY: 0, windowWidth: 800 },
                        // Page size in points (1px = 0.75pt) so the 800px card fills the page exactly on every device
                        jsPDF: { unit: 'pt', format: [600, Math.ceil(clone.offsetHeight * 0.75) + 4], orientation: clone.offsetHeight * 0.75 + 4 > 600 ? 'portrait' : 'landscape' },
                        pagebreak: { mode: [] }
                    };
                    const worker = html2pdf().set(opt).from(clone);
                    if (mode === 'blob') {
                        return await worker.outputPdf('blob');
                    }
                    await worker.save();
                    return null;
                } finally {
                    document.body.removeChild(holder);
                }
            }

            function downloadPDF() {
                makePassPdf('save', 1);
            }

            function escHtml(str) {
                return String(str).replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            }

            function setWaStatus(type, html) {
                const box = document.getElementById('waStatus');
                if (!box) return;
                box.className = 'wa-status ' + type;
                box.innerHTML = html;
            }

            async function sendAdmitToWhatsApp() {
                if (!WA_CFG.enabled) return;
                setWaStatus('pending', 'Sending your admit card to WhatsApp ' + WA_CFG.phone + '…');
                try {
                    const blob = await makePassPdf('blob', 0.92);
                    const fd = new FormData();
                    fd.append('csrf_token', WA_CFG.csrf);
                    fd.append('nonce', WA_CFG.nonce);
                    fd.append('pdf', blob, PASS_FILE);

                    const res = await fetch(WA_CFG.endpoint, { method: 'POST', body: fd, credentials: 'same-origin' });
                    const raw = await res.text();
                    let data = null;
                    try { data = JSON.parse(raw); } catch (_) {}

                    if (data && data.ok) {
                        if (data.pdf === false) {
                            setWaStatus('success', '⚠️ WhatsApp message sent to ' + WA_CFG.phone + ' but WITHOUT the PDF (test template has no document header).');
                        } else {
                            setWaStatus('success', '✅ Admit card sent to your WhatsApp ' + WA_CFG.phone);
                        }
                    } else if (data) {
                        throw new Error(data.message || 'Send failed');
                    } else {
                        throw new Error('Server replied HTTP ' + res.status + ': ' + raw.replace(/<[^>]*>/g, ' ').trim().slice(0, 200));
                    }
                } catch (e) {
                    console.error('WhatsApp send error:', e);
                    setWaStatus('error', 'Could not send on WhatsApp. You can still download the card below.'
                        + '<div style="font-size:12px;font-weight:500;margin-top:6px;word-break:break-word;">' + escHtml(e.message || String(e)) + '</div>'
                        + '<button type="button" style="margin:8px 0 0;" onclick="sendAdmitToWhatsApp()">Retry</button>');
                }
            }

            if (WA_CFG.autoSend) {
                if (document.readyState === 'complete') {
                    sendAdmitToWhatsApp();
                } else {
                    window.addEventListener('load', sendAdmitToWhatsApp);
                }
            }
            </script>
