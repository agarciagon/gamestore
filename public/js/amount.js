// public/js/amount.js

// ─── DETALLE: incrementar / decrementar cantidad antes de añadir al carrito ──
function incrementQty(max) {
    const input = document.getElementById('cantidad');
    if (parseInt(input.value) < max) {
        input.value = parseInt(input.value) + 1;
    }
}

function decrementQty() {
    const input = document.getElementById('cantidad');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

// ─── CARRITO: aumentar / disminuir cantidad de un item ───────────────────────
function updateQty(jid, action) {
    fetch('/gamestore/public/carrito/update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_videojuego=${jid}&action=${action}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.ok) {
            const span     = document.getElementById('qty-' + jid);
            const subtotal = document.getElementById('subtotal-' + jid);
            const precio   = parseFloat(document.getElementById('precio-' + jid).dataset.precio);
            const current  = parseInt(span.textContent);
            const newQty   = action === 'increase' ? current + 1 : current - 1;

            if (action === 'decrease' && current <= 1) {
                location.reload();
                return;
            }

            span.textContent     = newQty;
            subtotal.textContent = (newQty * precio).toFixed(2) + '€';
            recalcularSummary();

        } else {
            // ── Sin stock: toast en lugar de alert() ────────────────────────
            showCartToast('error', data.error ?? 'No more stock available.');
        }
    })
    .catch(() => showCartToast('error', 'Connection error. Please try again.'));
}

// ─── Recalcular totales del resumen ──────────────────────────────────────────
function recalcularSummary() {
    let subtotalTotal = 0;
    document.querySelectorAll('[id^="subtotal-"]').forEach(el => {
        subtotalTotal += parseFloat(el.textContent.replace('€', ''));
    });

    const vat   = subtotalTotal * 0.21;
    const total = subtotalTotal + vat;

    document.getElementById('summary-subtotal').textContent = subtotalTotal.toFixed(2) + '€';
    document.getElementById('summary-vat').textContent      = vat.toFixed(2) + '€';
    document.getElementById('summary-total').textContent    = total.toFixed(2) + '€';
}

// ─── Toast para el carrito (sin stock u otros errores AJAX) ──────────────────
function showCartToast(type, message) {
    const existing = document.getElementById('cart-toast');
    if (existing) existing.remove();

    const isError = type === 'error';
    const bg      = isError ? '#dc2626' : '#16a34a';
    const icon    = isError ? 'fa-circle-xmark' : 'fa-circle-check';

    const toast = document.createElement('div');
    toast.id = 'cart-toast';
    toast.style.cssText = [
        'position:fixed',
        'bottom:1.5rem',
        'left:50%',
        'transform:translateX(-50%) translateY(0)',
        'z-index:9999',
        'display:flex',
        'align-items:center',
        'gap:.75rem',
        `background:${bg}`,
        'color:#fff',
        'padding:1rem 1.25rem',
        'border-radius:.75rem',
        'box-shadow:0 10px 25px rgba(0,0,0,.45)',
        'white-space:nowrap',
        'opacity:1',
        'transition:opacity .4s ease, transform .4s ease',
        'max-width:90vw',
    ].join(';');

    toast.innerHTML = `
        <i class="fa-solid ${icon}" style="font-size:1.2rem;flex-shrink:0;"></i>
        <span style="font-weight:600;font-size:.9rem;flex:1;white-space:normal;">${message}</span>
        <button onclick="this.closest('#cart-toast').remove()"
            style="background:none;border:none;color:rgba(255,255,255,.75);cursor:pointer;padding:0 0 0 .5rem;flex-shrink:0;">
            <i class="fa-solid fa-times"></i>
        </button>`;

    document.body.appendChild(toast);

    // Auto-dismiss a los 4 segundos
    setTimeout(() => {
        if (!toast.isConnected) return;
        toast.style.opacity  = '0';
        toast.style.transform = 'translateX(-50%) translateY(16px)';
        setTimeout(() => toast.remove(), 420);
    }, 4000);
}