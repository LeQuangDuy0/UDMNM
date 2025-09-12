document.addEventListener("DOMContentLoaded", function () {
  // ========== 1. Toggle menu mobile ==========
  const menuToggle = document.querySelector(".menu-toggle");
  const mainNav = document.querySelector(".main-navigation");

  if (menuToggle && mainNav) {
    menuToggle.addEventListener("click", function () {
      if (mainNav.classList.contains("active")) {
        mainNav.classList.remove("active");
        mainNav.classList.add("closing");
        menuToggle.classList.remove("active");

        setTimeout(() => {
          mainNav.classList.remove("closing");
        }, 400); // thời gian animation
      } else {
        mainNav.classList.add("active");
        menuToggle.classList.add("active");
      }
    });
  }

  // ========== 2. Reveal on scroll ==========
  const elements = document.querySelectorAll(".reveal");
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("active");
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });

  elements.forEach(el => observer.observe(el));
  
});