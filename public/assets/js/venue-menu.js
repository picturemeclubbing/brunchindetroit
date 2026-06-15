/* venue-menu.js — progressive enhancement for the allergy-aware menu filter.
 *
 * When JS is available, changing the allergen <select> updates only the
 * #venue-menu-section via fetch() to /menu-fragment.php (HTML fragment) and
 * updates the browser URL with history.pushState so the filter is shareable.
 *
 * Without JS the <form> still submits normally (GET to venue.php), so the
 * page degrades gracefully. No jQuery / no external dependencies.
 */
(function () {
    "use strict";

    var SECTION_ID = "venue-menu-section";
    var LOADING_CLASS = "is-loading";
    var POLL_MAX_MS = 8000;
    var POLL_INTERVAL_MS = 50;

    function getSection() {
        return document.getElementById(SECTION_ID);
    }

    function getForm(section) {
        if (!section) return null;
        return section.querySelector("[data-venue-menu-filter]");
    }

    function getSelect(form) {
        if (!form) return null;
        return form.querySelector('select[name="allergen"]');
    }

    function buildFragmentUrl(slug, allergen) {
        var url = "/menu-fragment.php?slug=" + encodeURIComponent(slug);
        if (allergen) {
            url += "&allergen=" + encodeURIComponent(allergen);
        }
        return url;
    }

    function buildPageUrl(slug, allergen) {
        var url = "/venue.php?slug=" + encodeURIComponent(slug);
        if (allergen) {
            url += "&allergen=" + encodeURIComponent(allergen);
        }
        return url;
    }

    /* Replace the section's outerHTML with the fetched fragment, then
     * re-bind the change listener on the newly inserted form. */
    function applyFragment(htmlText) {
        var section = getSection();
        if (!section) return;

        var wrapper = document.createElement("div");
        wrapper.innerHTML = htmlText;
        var newSection = wrapper.querySelector("#" + SECTION_ID);

        if (!newSection) {
            // Unexpected payload — bail out so the caller can fall back.
            throw new Error("Fragment missing #" + SECTION_ID);
        }

        section.replaceWith(newSection);
        bindForm(newSection);
    }

    function updateHistory(slug, allergen) {
        var url = buildPageUrl(slug, allergen);
        try {
            window.history.pushState({ slug: slug, allergen: allergen }, "", url);
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

    function onChange(event) {
        var form = event.currentTarget;
        if (!form) return;

        var select = getSelect(form);
        var slug = form.getAttribute("data-slug") || "";
        var allergen = select ? select.value : "";

        if (!slug) return; // nothing we can do without a slug

        // Prevent the native GET navigation from also firing.
        event.preventDefault();

        setLoading(true);

        var finished = false;
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
                // Network/server error: fall back to a normal full-page GET.
                form.removeEventListener("change", onChange);
                form.submit();
            })
            .finally(function () {
                finished = true;
                setLoading(false);
            });

        // Safety net: if fetch hangs abnormally, fall back to form submit.
        window.setTimeout(function () {
            if (!finished) {
                try {
                    form.removeEventListener("change", onChange);
                    form.submit();
                } catch (e) { /* noop */ }
            }
        }, POLL_MAX_MS);
    }

    function bindForm(section) {
        var form = getForm(section);
        if (!form) return;

        // The <select> drives it on change; the <form> is only submitted
        // explicitly when JS is unavailable (noscript button) or on fallback.
        var select = getSelect(form);
        if (select) {
            select.addEventListener("change", function (e) {
                onChange.call(form, e);
            });
        }
    }

    function init() {
        var section = getSection();
        if (!section) return;
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
                .then(function (r) { return r.text(); })
                .then(function (html) { applyFragment(html); })
                .catch(function () { window.location.reload(); })
                .finally(function () { setLoading(false); });
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();