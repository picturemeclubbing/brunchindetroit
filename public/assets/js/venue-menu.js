/* venue-menu.js — progressive enhancement for the allergy-aware menu filter.
 *
 * When JS is available, changing the allergen <select> updates only the
 * #venue-menu-section via fetch() to menu-fragment.php (HTML fragment) and
 * updates the browser URL with history.pushState so the filter is shareable.
 *
 * Without JS the <form> still submits normally (GET to venue.php), so the
 * page degrades gracefully. No jQuery / no external dependencies.
 *
 * Robustness notes:
 *   - Runs after DOMContentLoaded (or immediately if the DOM is already ready).
 *   - Finds the form via document.querySelector('[data-venue-menu-filter]').
 *   - Finds the select via form.querySelector('select[name="allergen"]').
 *   - The change listener is attached to the FORM (change events bubble up
 *     from the <select>), so `this` inside the handler is always the form —
 *     this is what makes data-slug and data-venue-menu-filter resolve
 *     correctly. (Previous bug: the listener was on the <select>, so
 *     event.currentTarget was the <select>, which has no data-slug, and the
 *     handler silently returned.)
 *   - After each AJAX replacement the new form is re-bound.
 *   - If the form, select, or section is missing, the script does nothing
 *     safely (no thrown errors).
 *   - On fetch failure it falls back to normal GET form submission.
 */
(function () {
    "use strict";

    var SECTION_ID = "venue-menu-section";
    var LOADING_CLASS = "is-loading";
    var FALLBACK_TIMEOUT_MS = 8000;

    /* ------------------------------------------------------------------ */
    /* Small DOM helpers (all null-safe)                                  */
    /* ------------------------------------------------------------------ */

    function getSection() {
        return document.getElementById(SECTION_ID);
    }

    function getForm(scope) {
        if (!scope || typeof scope.querySelector !== "function") return null;
        return scope.querySelector("[data-venue-menu-filter]");
    }

    function getSelect(form) {
        if (!form) return null;
        return form.querySelector('select[name="allergen"]');
    }

    function getSlug(form) {
        if (!form) return "";
        return form.getAttribute("data-slug") || "";
    }

    /* ------------------------------------------------------------------ */
    /* URL builders (relative to the current document directory)          */
    /* ------------------------------------------------------------------ */

    function buildFragmentUrl(slug, allergen) {
        var url = "menu-fragment.php?slug=" + encodeURIComponent(slug);
        if (allergen) {
            url += "&allergen=" + encodeURIComponent(allergen);
        }
        return url;
    }

    function buildPageUrl(slug, allergen) {
        var url = "venue.php?slug=" + encodeURIComponent(slug);
        if (allergen) {
            url += "&allergen=" + encodeURIComponent(allergen);
        }
        return url;
    }

    /* ------------------------------------------------------------------ */
    /* Core operations                                                    */
    /* ------------------------------------------------------------------ */

    /* Replace #venue-menu-section with the fetched fragment, then re-bind
     * the change listener on the newly inserted form. */
    function applyFragment(htmlText) {
        var section = getSection();
        if (!section) return;

        var wrapper = document.createElement("div");
        wrapper.innerHTML = htmlText;
        var newSection = wrapper.querySelector("#" + SECTION_ID);

        if (!newSection) {
            // Unexpected payload — bail so the caller can fall back.
            throw new Error("Fragment missing #" + SECTION_ID);
        }

        section.replaceWith(newSection);
        bindForm(newSection);
    }

    function updateHistory(slug, allergen) {
        try {
            window.history.pushState(
                { slug: slug, allergen: allergen },
                "",
                buildPageUrl(slug, allergen)
            );
        } catch (e) {
            // Some browsers throw on pushState with certain URLs; ignore.
        }
    }

    function setLoading(isLoading) {
        var section = getSection();
        if (!section) return;
        if (isLoading) {
            section.classList.add(LOADING_CLASS);
        } else {
            section.classList.remove(LOADING_CLASS);
        }
    }

    /* The change handler. Attached to the FORM (change bubbles from the
     * <select>), so `this` is always the form element. */
    function handleChange(event) {
        var form = this;
        if (!form) return;

        var select = getSelect(form);
        if (!select) return;

        var slug = getSlug(form);
        var allergen = select.value || "";

        if (!slug) return; // nothing we can do without a slug

        // Prevent any native form submission triggered by the change.
        if (event && typeof event.preventDefault === "function") {
            event.preventDefault();
        }

        setLoading(true);

        var done = false;
        var fragmentUrl = buildFragmentUrl(slug, allergen);

        fetch(fragmentUrl, { headers: { "X-Requested-With": "venue-menu-ajax" } })
            .then(function (resp) {
                if (!resp.ok) {
                    throw new Error("HTTP " + resp.status);
                }
                return resp.text();
            })
            .then(function (html) {
                applyFragment(html);
                updateHistory(slug, allergen);
            })
            .catch(function (err) {
                // Network/server error: warn once, then fall back to a normal
                // full-page GET submission so the user still gets a result.
                if (window.console && typeof window.console.warn === "function") {
                    console.warn("[venue-menu] AJAX filter failed, falling back to normal submit:", err);
                }
                try { form.submit(); } catch (e) { /* noop */ }
            })
            .finally(function () {
                done = true;
                setLoading(false);
            });

        // Safety net: if fetch hangs abnormally long, fall back to submit.
        window.setTimeout(function () {
            if (!done) {
                try { form.submit(); } catch (e) { /* noop */ }
            }
        }, FALLBACK_TIMEOUT_MS);
    }

    /* Attach the change listener to the form. Called on init and after each
     * AJAX replacement (the old form's listeners die with the removed node). */
    function bindForm(section) {
        var form = getForm(section);
        if (!form) return;
        form.addEventListener("change", handleChange);
    }

    /* ------------------------------------------------------------------ */
    /* Init                                                               */
    /* ------------------------------------------------------------------ */

    function init() {
        // Defensive: if there's no menu section on this page, do nothing.
        var section = getSection();
        if (!section) return;

        var form = getForm(section);
        if (!form) return; // no filter form — nothing to enhance

        bindForm(section);

        // Support back/forward navigation between filter states.
        window.addEventListener("popstate", function (event) {
            var state = event.state || {};
            var slug = state.slug;
            var allergen = state.allergen || "";
            if (!slug) {
                // No recorded state: reload the current URL to be safe.
                window.location.reload();
                return;
            }
            setLoading(true);
            fetch(buildFragmentUrl(slug, allergen))
                .then(function (r) {
                    if (!r.ok) throw new Error("HTTP " + r.status);
                    return r.text();
                })
                .then(function (html) { applyFragment(html); })
                .catch(function () { window.location.reload(); })
                .finally(function () { setLoading(false); });
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        // DOM already parsed (script loaded with defer or at end of body).
        init();
    }
})();