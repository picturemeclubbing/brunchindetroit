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
      // Skip the spotlight track - it is handled separately.
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

  /* ---------------------------------------------------------------------- */
  /* Phase 5F: Blog featured article slider                                  */
  /* One featured article visible at a time on the Blog page.                */
  /* ---------------------------------------------------------------------- */
  function initBlogFeaturedSlider() {
    var section = document.querySelector(".blog-featured-slider");
    if (!section) {
      return;
    }

    var track = section.querySelector(".blog-featured-slider__track");
    var slides = section.querySelectorAll(".blog-featured-slider__slide");
    var prevButton = section.querySelector(".blog-featured-slider__arrow--prev");
    var nextButton = section.querySelector(".blog-featured-slider__arrow--next");

    if (!track || slides.length <= 1) {
      return;
    }

    var currentIndex = 0;

    function maxIndex() {
      return slides.length - 1;
    }

    function updateSlider() {
      track.style.transform = "translateX(-" + (currentIndex * 100) + "%)";
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

    window.addEventListener("resize", updateSlider);
    updateSlider();
  }

  initBlogFeaturedSlider();

  /* ---------------------------------------------------------------------- */
  /* Mobile: reveal spotlight arrows only while the carousel is being touched */
  /* Desktop is unaffected (arrows stay visible via CSS).                    */
  /* ---------------------------------------------------------------------- */
  function initSpotlightTouchArrows() {
    var slider = document.querySelector(
      ".home-spotlight-section .home-spotlight__slider"
    );
    if (!slider) {
      return;
    }

    var hideTimer = null;

    function reveal() {
      slider.classList.add("is-touched");
      if (hideTimer) {
        window.clearTimeout(hideTimer);
      }
      hideTimer = window.setTimeout(function () {
        slider.classList.remove("is-touched");
        hideTimer = null;
      }, 3000);
    }

    slider.addEventListener("touchstart", reveal, { passive: true });
    slider.addEventListener("pointerdown", function (e) {
      if (e.pointerType === "touch" || e.pointerType === "pen") {
        reveal();
      }
    });
  }

  initSpotlightTouchArrows();
})();

/* Directory grid/list view toggle */
(function () {
    const container = document.querySelector('[data-directory-view-container]');
    const buttons = document.querySelectorAll('[data-directory-view]');

    if (!container || buttons.length === 0) {
        return;
    }

    const storageKey = 'directoryViewPreference';

    function setDirectoryView(view) {
        const normalized = view === 'list' ? 'list' : 'grid';
        container.classList.toggle('is-list-view', normalized === 'list');

        buttons.forEach((button) => {
            const isActive = button.getAttribute('data-directory-view') === normalized;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        try {
            window.localStorage.setItem(storageKey, normalized);
        } catch (error) {
            // Ignore storage failures.
        }
    }

    let savedView = 'grid';
    try {
        savedView = window.localStorage.getItem(storageKey) || 'grid';
    } catch (error) {
        savedView = 'grid';
    }

    setDirectoryView(savedView);

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            setDirectoryView(button.getAttribute('data-directory-view'));
        });
    });
})();
