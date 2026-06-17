(function () {
  "use strict";

  /* ---------------------------------------------------------------------- */
  /* Mobile menu toggle                                                     */
  /* ---------------------------------------------------------------------- */
  var mobileMenuButton = document.getElementById("mobileMenuButton");
  var mobileMenu = document.getElementById("mobileMenu");

  if (mobileMenuButton && mobileMenu) {
    mobileMenuButton.addEventListener("click", function () {
      var isHidden = mobileMenu.hasAttribute("hidden");
      if (isHidden) {
        mobileMenu.removeAttribute("hidden");
        mobileMenuButton.setAttribute("aria-expanded", "true");
        mobileMenuButton.setAttribute("aria-label", "Close menu");
      } else {
        mobileMenu.setAttribute("hidden", "");
        mobileMenuButton.setAttribute("aria-expanded", "false");
        mobileMenuButton.setAttribute("aria-label", "Open menu");
      }
    });
  }

  /* ---------------------------------------------------------------------- */
  /* Generic multi-slide slider (home Latest News, etc.)                    */
  /* Scoped to NOT match the Phase 5D Featured Spotlight, which has its own */
  /* one-slide-at-a-time initializer below.                                 */
  /* ---------------------------------------------------------------------- */
  function initGenericSlider() {
    var allTracks = document.querySelectorAll(".slider__track");
    if (!allTracks.length) {
      return;
    }

    Array.prototype.forEach.call(allTracks, function (track) {
      // Skip the spotlight track — it is handled separately.
      if (track.closest(".home-spotlight-section")) {
        return;
      }

      // Scope slides/buttons to this track's container.
      var scope = track.closest(".section") || track.parentElement;
      var slides = track.querySelectorAll(".slider__slide");
      if (!slides.length) {
        return;
      }

      var prevButton = scope ? scope.querySelector(".slider-prev") : null;
      var nextButton = scope ? scope.querySelector(".slider-next") : null;
      var currentIndex = 0;
      var autoTimer = null;

      function slidesPerView() {
        if (window.matchMedia("(min-width: 1024px)").matches) {
          return 3;
        }
        if (window.matchMedia("(min-width: 768px)").matches) {
          return 2;
        }
        return 1;
      }

      function maxIndex() {
        return Math.max(0, slides.length - slidesPerView());
      }

      function updateSlider() {
        var slide = slides[0];
        if (!slide) {
          return;
        }
        var offset = slide.offsetWidth * currentIndex;
        track.style.transform = "translateX(-" + offset + "px)";
      }

      function goNext() {
        currentIndex = currentIndex >= maxIndex() ? 0 : currentIndex + 1;
        updateSlider();
      }

      function goPrev() {
        currentIndex = currentIndex <= 0 ? maxIndex() : currentIndex - 1;
        updateSlider();
      }

      if (nextButton) {
        nextButton.addEventListener("click", goNext);
      }
      if (prevButton) {
        prevButton.addEventListener("click", goPrev);
      }

      window.addEventListener("resize", function () {
        if (currentIndex > maxIndex()) {
          currentIndex = maxIndex();
        }
        updateSlider();
      });

      updateSlider();

      autoTimer = window.setInterval(goNext, 5000);

      track.addEventListener("mouseenter", function () {
        if (autoTimer) {
          window.clearInterval(autoTimer);
          autoTimer = null;
        }
      });

      track.addEventListener("mouseleave", function () {
        if (!autoTimer) {
          autoTimer = window.setInterval(goNext, 5000);
        }
      });
    });
  }

  initGenericSlider();

  /* ---------------------------------------------------------------------- */
  /* Phase 5D: Featured Spotlight slider                                    */
  /* One slide visible at a time. Auto-advances every 6s, pauses on hover,  */
  /* and can be controlled via the .spotlight-prev / .spotlight-next buttons.*/
  /* ---------------------------------------------------------------------- */
  function initSpotlightSlider() {
    var section = document.querySelector(".home-spotlight-section");
    if (!section) {
      return;
    }

    var track = section.querySelector(".slider__track");
    var slides = section.querySelectorAll(".slider__slide");
    var prevButton = section.querySelector(".spotlight-prev");
    var nextButton = section.querySelector(".spotlight-next");

    if (!track || !slides.length) {
      return;
    }

    // Single-slide carousels (or just the empty fallback) don't need logic.
    if (slides.length <= 1) {
      return;
    }

    var currentIndex = 0;
    var autoTimer = null;
    var AUTO_INTERVAL = 6000;

    function maxIndex() {
      return slides.length - 1;
    }

    function updateSlider() {
      var slide = slides[0];
      if (!slide) {
        return;
      }
      var offset = slide.offsetWidth * currentIndex;
      track.style.transform = "translateX(-" + offset + "px)";
    }

    function goTo(index) {
      currentIndex = Math.max(0, Math.min(index, maxIndex()));
      updateSlider();
    }

    function goNext() {
      currentIndex = currentIndex >= maxIndex() ? 0 : currentIndex + 1;
      updateSlider();
    }

    function goPrev() {
      currentIndex = currentIndex <= 0 ? maxIndex() : currentIndex - 1;
      updateSlider();
    }

    if (nextButton) {
      nextButton.addEventListener("click", function () {
        goNext();
        restartAuto();
      });
    }

    if (prevButton) {
      prevButton.addEventListener("click", function () {
        goPrev();
        restartAuto();
      });
    }

    window.addEventListener("resize", function () {
      if (currentIndex > maxIndex()) {
        currentIndex = maxIndex();
      }
      updateSlider();
    });

    // Basic keyboard support when the spotlight is focused.
    section.addEventListener("keydown", function (e) {
      if (e.key === "ArrowLeft") {
        e.preventDefault();
        goPrev();
        restartAuto();
      } else if (e.key === "ArrowRight") {
        e.preventDefault();
        goNext();
        restartAuto();
      }
    });

    updateSlider();

    function startAuto() {
      autoTimer = window.setInterval(goNext, AUTO_INTERVAL);
    }

    function stopAuto() {
      if (autoTimer) {
        window.clearInterval(autoTimer);
        autoTimer = null;
      }
    }

    function restartAuto() {
      stopAuto();
      startAuto();
    }

    startAuto();

    // Pause on hover; resume on leave.
    track.addEventListener("mouseenter", stopAuto);
    track.addEventListener("mouseleave", startAuto);
  }

  initSpotlightSlider();
})();