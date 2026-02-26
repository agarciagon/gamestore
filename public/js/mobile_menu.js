document.addEventListener("DOMContentLoaded", () => {
    const mobileBtn = document.querySelector('#mobile-menu-btn');
        const mobileMenu = document.querySelector('#mobile-menu');

    mobileBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
    });
});