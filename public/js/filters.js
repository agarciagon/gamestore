document.addEventListener("DOMContentLoaded", function () {

    const toggleBtn = document.getElementById("filterToggle");
    const dropdown = document.getElementById("filterDropdown");

    toggleBtn.addEventListener("click", function (e) {
        e.stopPropagation();
        dropdown.classList.toggle("hidden");
    });

    dropdown.addEventListener("click", function (e) {
        e.stopPropagation();
    });

    document.addEventListener("click", function () {
        dropdown.classList.add("hidden");
    });
});