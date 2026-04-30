document.addEventListener("DOMContentLoaded", () => {
    const navBar = document.querySelector("nav");
    const menuBtns = document.querySelectorAll(".menu-icon, .icon-menu");
    const overlay = document.querySelector(".overlay");

    function openSidebar() {
        navBar.classList.add("open");
        overlay.classList.add("active");
        document.body.style.overflow = "hidden";
    }

    function closeSidebar() {
        navBar.classList.remove("open");
        overlay.classList.remove("active");
        document.body.style.overflow = "";
    }

    menuBtns.forEach((menuBtn) => {
        menuBtn.addEventListener("click", () => {
            if (navBar.classList.contains("open")) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    });

    if (overlay) {
        overlay.addEventListener("click", closeSidebar);
    }

    // Submenu logic
    document.querySelectorAll(".arrow").forEach(arrow => {
        arrow.addEventListener("click", (e) => {
            e.stopPropagation();
            arrow.closest('.list').classList.toggle("showMenu");
        });
    });
});
