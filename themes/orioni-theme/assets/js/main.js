/*== nút bấm thanh menu ở giao diện mobile == */
document.addEventListener("DOMContentLoaded", function () {
  const menuToggle = document.querySelector(".menu-toggle");
  const mainNav = document.querySelector(".main-navigation");

  if (!menuToggle || !mainNav) return;

  menuToggle.addEventListener("click", function () {
    if (mainNav.classList.contains("active")) {
      // đang mở → đóng
      mainNav.classList.remove("active");
      mainNav.classList.add("closing");
      menuToggle.classList.remove("active");

      setTimeout(() => {
        mainNav.classList.remove("closing");
      }, 400); // bằng thời gian animation slideOut
    } else {
      // mở
      mainNav.classList.add("active");
      menuToggle.classList.add("active");
    }
  });
});




document.addEventListener("DOMContentLoaded", function() {
  const elements = document.querySelectorAll(".reveal");

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("active");
        observer.unobserve(entry.target); // chỉ chạy 1 lần
      }
    });
  }, { threshold: 0.5 });

  elements.forEach(el => observer.observe(el));
});


