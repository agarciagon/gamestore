let active = null;

function toggleScreen(img) {
    const main = document.getElementById('mainImage');

    if (active === img) {
        img.style.height = active === main ? '380px' : '64px';
        main.style.height = '380px';
        active = null;
        return;
    }

    if (active) active.style.height = active === main ? '380px' : '64px';

    main.style.height = img === main ? '380px' : '64px';
    img.style.height = '380px';
    active = img;
}