/*== nút bấm thanh menu ở giao diện mobile == */
document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.querySelector(".menu-toggle");
    const mainNav = document.querySelector(".main-navigation");

    if (menuToggle) {
        menuToggle.addEventListener("click", function () {
            menuToggle.classList.toggle("active");
            mainNav.classList.toggle("active"); // mở/đóng menu
        });
    }
});


