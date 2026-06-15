/**
 * Article share (Phase 4A).
 *
 * Vanilla JS — no libraries, no tracking. Finds any element with a
 * `data-copy-link` attribute (the "Copy Link" button on article detail)
 * and copies that URL to the clipboard, showing brief "Copied!" feedback.
 *
 * Falls back gracefully when the async Clipboard API is unavailable
 * (e.g. older browsers, non-secure contexts) using a hidden textarea +
 * document.execCommand('copy').
 */
(function () {
    'use strict';

    function setButtonText(btn, text) {
        var label = btn.querySelector('span');
        if (label) {
            label.textContent = text;
        } else {
            btn.textContent = text;
        }
    }

    function flashCopied(btn) {
        var original = btn.querySelector('span')
            ? btn.querySelector('span').textContent
            : btn.textContent;
        setButtonText(btn, 'Copied!');
        btn.classList.add('is-copied');
        window.setTimeout(function () {
            setButtonText(btn, original);
            btn.classList.remove('is-copied');
        }, 1800);
    }

    function legacyCopy(text) {
        try {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', '');
            ta.style.position = 'absolute';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            var ok = document.execCommand('copy');
            document.body.removeChild(ta);
            return ok;
        } catch (e) {
            return false;
        }
    }

    function init() {
        var buttons = document.querySelectorAll('[data-copy-link]');
        if (!buttons.length) {
            return;
        }

        Array.prototype.forEach.call(buttons, function (btn) {
            btn.addEventListener('click', function () {
                var url = btn.getAttribute('data-copy-link') || window.location.href;

                if (
                    navigator.clipboard &&
                    typeof navigator.clipboard.writeText === 'function'
                ) {
                    navigator.clipboard.writeText(url).then(
                        function () { flashCopied(btn); },
                        function () {
                            if (legacyCopy(url)) { flashCopied(btn); }
                        }
                    );
                } else if (legacyCopy(url)) {
                    flashCopied(btn);
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();