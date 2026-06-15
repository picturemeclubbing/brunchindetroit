/* venue-tabs.js — accessible tab component for the venue profile page.
 *
 * Progressive enhancement: if JS fails to load/run, every tab panel stays
 * visible (server HTML has no `hidden` attributes — JS adds them on init),
 * so all content remains reachable without JavaScript.
 *
 * Behavior:
 *   - Runs on DOMContentLoaded (or immediately if the DOM is already ready).
 *   - Finds the tab container via [data-venue-tabs].
 *   - Tab buttons:   [data-venue-tab="<id>"]
 *   - Tab panels:    [data-venue-tab-panel="<id>"]
 *   - Default active tab: the first tab button (Overview).
 *   - Clicking a tab shows only that panel; no page reload.
 *   - Sets aria-selected on buttons, role handling on panels.
 *   - Does NOT touch venue-menu.js or the AJAX allergy filter — the Menu
 *     tab panel keeps the #venue-menu-section and its enhancement intact.
 *   - Supports URL deep-linking via an optional #tab-photos / #tab-menu
 *     hash so the page can land on a specific tab (used by the "View Menu"
 *     affordances). If the hash matches a tab id, it wins on load.
 */
(function () {
    "use strict";

    var ACTIVE_CLASS = "is-active";

    function getContainer() {
        return document.querySelector("[data-venue-tabs]");
    }

    function getButtons(container) {
        if (!container) return [];
        return Array.prototype.slice.call(container.querySelectorAll("[data-venue-tab]"));
    }

    function getPanels(container) {
        if (!container) return [];
        return Array.prototype.slice.call(container.querySelectorAll("[data-venue-tab-panel]"));
    }

    function activate(container, id) {
        if (!container || !id) return;
        var buttons = getButtons(container);
        var panels = getPanels(container);

        buttons.forEach(function (btn) {
            var isActive = btn.getAttribute("data-venue-tab") === id;
            btn.classList.toggle(ACTIVE_CLASS, isActive);
            btn.setAttribute("aria-selected", isActive ? "true" : "false");
            if (isActive) {
                btn.setAttribute("tabindex", "0");
            } else {
                btn.setAttribute("tabindex", "-1");
            }
        });

        panels.forEach(function (panel) {
            var isActive = panel.getAttribute("data-venue-tab-panel") === id;
            panel.classList.toggle(ACTIVE_CLASS, isActive);
            if (isActive) {
                panel.removeAttribute("hidden");
            } else {
                panel.setAttribute("hidden", "");
            }
        });
    }

    function getHashTab() {
        var hash = window.location.hash || "";
        if (hash.indexOf("#tab-") === 0) {
            return hash.slice(5); // strip "#tab-"
        }
        return null;
    }

    function init() {
        var container = getContainer();
        if (!container) return; // no tabs on this page — do nothing safely

        var buttons = getButtons(container);
        if (buttons.length === 0) return; // nothing to enhance

        // Wire up click handling.
        buttons.forEach(function (btn) {
            btn.addEventListener("click", function () {
                var id = btn.getAttribute("data-venue-tab");
                activate(container, id);
                // Keep the URL in sync (non-blocking) so the tab is shareable
                // and back/forward roughly tracks tab changes.
                try {
                    history.replaceState(null, "", "#tab-" + id);
                } catch (e) {
                    /* ignore */
                }
            });

            // Keyboard arrow navigation between tabs (WAI-ARIA pattern).
            btn.addEventListener("keydown", function (e) {
                var key = e.key;
                if (key !== "ArrowLeft" && key !== "ArrowRight") return;
                var idx = buttons.indexOf(btn);
                var nextIdx = key === "ArrowRight" ? idx + 1 : idx - 1;
                if (nextIdx < 0) nextIdx = buttons.length - 1;
                if (nextIdx >= buttons.length) nextIdx = 0;
                var nextBtn = buttons[nextIdx];
                if (nextBtn) {
                    e.preventDefault();
                    var nextId = nextBtn.getAttribute("data-venue-tab");
                    activate(container, nextId);
                    try { nextBtn.focus(); } catch (err) { /* noop */ }
                    try {
                        history.replaceState(null, "", "#tab-" + nextId);
                    } catch (err2) { /* ignore */ }
                }
            });
        });

        // Decide the initially-active tab: #hash wins, else first button.
        var initialId = getHashTab();
        if (!initialId) {
            initialId = buttons[0].getAttribute("data-venue-tab");
        }
        activate(container, initialId);
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        // DOM already parsed (script loaded at end of body).
        init();
    }
})();