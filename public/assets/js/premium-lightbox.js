/**
 * File: public/assets/js/premium-lightbox.js
 * Purpose: Simple vanilla-JS image lightbox for the premium venue profile's
 *          About/photo collage. Opens any [data-lightbox-trigger] element's
 *          data-photo-src / data-photo-alt in a full-screen overlay.
 * Batch: B2 repair pass.
 *
 * Notes:
 *   - No external libraries. Builds its own overlay markup on first use, so
 *     pages with no [data-lightbox-trigger] elements (e.g. the free profile,
 *     directory, blog) load this script safely and it does nothing.
 *   - Closes via close button, Escape key, or click on the dark backdrop.
 *   - Loaded from app/views/partials/footer.php alongside rsvp.js.
 */
(function () {
    "use strict";

    function ready(fn) {
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", fn);
        } else {
            fn();
        }
    }

    ready(function () {
        var triggers = document.querySelectorAll("[data-lightbox-trigger]");
        if (!triggers.length) {
            return;
        }

        var lastFocusedElement = null;
        var lightbox = null;
        var image = null;
        var caption = null;

        function buildLightbox() {
            lightbox = document.createElement("div");
            lightbox.className = "premium-lightbox";
            lightbox.id = "premiumLightbox";
            lightbox.hidden = true;
            lightbox.setAttribute("role", "dialog");
            lightbox.setAttribute("aria-modal", "true");
            lightbox.setAttribute("aria-label", "Photo viewer");

            lightbox.innerHTML =
                '<button type="button" class="premium-lightbox__close" data-lightbox-close aria-label="Close">' +
                '<i class="fas fa-xmark" aria-hidden="true"></i>' +
                "</button>" +
                '<figure class="premium-lightbox__figure">' +
                '<img src="" alt="">' +
                '<figcaption class="premium-lightbox__caption"></figcaption>' +
                "</figure>";

            document.body.appendChild(lightbox);

            image = lightbox.querySelector("img");
            caption = lightbox.querySelector(".premium-lightbox__caption");

            // Click anywhere on the dark area (not the image/figure itself) closes it.
            lightbox.addEventListener("click", function (event) {
                if (event.target === lightbox) {
                    closeLightbox();
                }
            });

            var closeButton = lightbox.querySelector("[data-lightbox-close]");
            if (closeButton) {
                closeButton.addEventListener("click", closeLightbox);
            }
        }

        function openLightbox(src, alt) {
            if (!lightbox) {
                buildLightbox();
            }

            lastFocusedElement = document.activeElement;

            image.src = src;
            image.alt = alt || "";
            caption.textContent = alt || "";

            lightbox.hidden = false;
            lightbox.setAttribute("aria-hidden", "false");

            window.setTimeout(function () {
                var closeButton = lightbox.querySelector("[data-lightbox-close]");
                if (closeButton) {
                    closeButton.focus();
                }
            }, 0);
        }

        function closeLightbox() {
            if (!lightbox || lightbox.hidden) {
                return;
            }

            lightbox.hidden = true;
            lightbox.setAttribute("aria-hidden", "true");
            image.src = "";

            if (lastFocusedElement && typeof lastFocusedElement.focus === "function") {
                lastFocusedElement.focus();
            }
        }

        Array.prototype.forEach.call(triggers, function (trigger) {
            trigger.addEventListener("click", function () {
                var src = trigger.getAttribute("data-photo-src");
                var alt = trigger.getAttribute("data-photo-alt");
                if (src) {
                    openLightbox(src, alt);
                }
            });
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape" && lightbox && !lightbox.hidden) {
                closeLightbox();
            }
        });
    });
})();
