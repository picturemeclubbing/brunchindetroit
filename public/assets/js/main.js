(function () {
  "use strict";

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

  var sliderTrack = document.querySelector(".slider__track");
  var slides = document.querySelectorAll(".slider__slide");
  var prevButton = document.querySelector(".slider-prev");
  var nextButton = document.querySelector(".slider-next");

  if (!sliderTrack || !slides.length) {
    return;
  }

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
    sliderTrack.style.transform = "translateX(-" + offset + "px)";
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

  sliderTrack.addEventListener("mouseenter", function () {
    if (autoTimer) {
      window.clearInterval(autoTimer);
      autoTimer = null;
    }
  });

  sliderTrack.addEventListener("mouseleave", function () {
    if (!autoTimer) {
      autoTimer = window.setInterval(goNext, 5000);
    }
  });
})();
