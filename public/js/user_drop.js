document.addEventListener('DOMContentLoaded', function () {

    function setupDropdown(buttonId, menuId) {
        const button = document.getElementById(buttonId);
        const menu   = document.getElementById(menuId);
        if (!button || !menu) return;

        button.addEventListener('click', function (e) {
            e.stopPropagation();
            menu.classList.toggle('hidden');
        });

        menu.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    setupDropdown('user-menu-button', 'user-menu');
    setupDropdown('user-menu-button-mobile', 'user-menu-mobile');

    // Cerrar ambos menús al hacer clic fuera
    document.addEventListener('click', function () {
        const desktop = document.getElementById('user-menu');
        const mobile  = document.getElementById('user-menu-mobile');
        if (desktop) desktop.classList.add('hidden');
        if (mobile)  mobile.classList.add('hidden');
    });
});