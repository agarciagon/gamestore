function showTab(tab) {
    ['profile', 'orders'].forEach(t => {
        document.getElementById('tab-content-' + t).classList.add('hidden');
        const btn = document.getElementById('tab-' + t);
        btn.classList.remove('border-sky-500', 'text-sky-400');
        btn.classList.add('border-transparent', 'text-gray-400');
    });
    document.getElementById('tab-content-' + tab).classList.remove('hidden');
    const active = document.getElementById('tab-' + tab);
    active.classList.add('border-sky-500', 'text-sky-400');
    active.classList.remove('border-transparent', 'text-gray-400');
}

function toggleOrder(id) {
    const el = document.getElementById('order-' + id);
    const icon = document.getElementById('icon-' + id);
    el.classList.toggle('hidden');
    icon.classList.toggle('fa-chevron-down');
    icon.classList.toggle('fa-chevron-up');
}

if (window.location.hash === '#orders') showTab('orders');