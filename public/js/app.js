// ─── TOAST ────────────────────────────────────────────────────────────────────
function dismissToast(id) {
    const toast = document.getElementById(id);
    if (!toast) return;
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(-50%) translateY(20px)';
    setTimeout(() => toast.remove(), 400);
}

function showToast(type, message) {
    const id   = type === 'success' ? 'toast-success' : 'toast-error';
    const bg   = type === 'success' ? '#16a34a' : '#dc2626';
    const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark';

    const prev = document.getElementById(id);
    if (prev) prev.remove();

    const toast = document.createElement('div');
    toast.id = id;
    toast.style.cssText = 'position:fixed;bottom:1.5rem;left:1rem;right:1rem;z-index:9999;display:flex;align-items:center;gap:.75rem;background:' + bg + ';color:#fff;padding:1rem 1.25rem;border-radius:.75rem;box-shadow:0 10px 25px rgba(0,0,0,.4);transition:opacity .4s,transform .4s;';
    toast.innerHTML = `
        <i class="fa-solid ${icon}" style="font-size:1.25rem;flex-shrink:0;"></i>
        <span style="font-weight:600;flex:1;">${message}</span>
        <button onclick="dismissToast('${id}')" style="background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;flex-shrink:0;">
            <i class="fa-solid fa-times"></i>
        </button>`;
    document.body.appendChild(toast);
    setTimeout(() => dismissToast(id), 4000);
}

// Auto-dismiss toasts estáticos del HTML (errores de sesión PHP)
document.addEventListener('DOMContentLoaded', () => {
    ['toast-success', 'toast-error'].forEach(id => {
        const toast = document.getElementById(id);
        if (toast) setTimeout(() => dismissToast(id), 4000);
    });
});

// ─── MODAL CONFIRMACIÓN ────────────────────────────────────────────────────────
function showConfirmModal(message, onConfirm) {
    const overlay = document.createElement('div');
    overlay.id = 'modal-overlay';
    overlay.className = 'fixed inset-0 bg-black/70 z-[100] flex items-center justify-center p-4 backdrop-blur-sm';
    overlay.style.animation = 'fadeIn 0.2s ease-out';

    const modal = document.createElement('div');
    modal.className = 'bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden';
    modal.style.animation = 'slideIn 0.3s ease-out';
    modal.innerHTML = `
        <div class="p-6">
            <div class="flex justify-center mb-4">
                <div class="bg-red-500/20 rounded-full p-4">
                    <i class="fa-solid fa-trash text-red-500 text-4xl"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-white text-center mb-3">Confirm Removal</h3>
            <p class="text-gray-300 text-center mb-6">${message}</p>
            <div class="flex gap-3">
                <button id="modal-cancel" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-3 px-6 rounded-lg font-semibold transition">
                    <i class="fa-solid fa-times mr-2"></i>Cancel
                </button>
                <button id="modal-confirm" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-3 px-6 rounded-lg font-semibold transition">
                    <i class="fa-solid fa-trash mr-2"></i>Remove
                </button>
            </div>
        </div>`;

    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    document.body.style.overflow = 'hidden';

    function closeModal() {
        overlay.style.animation = 'fadeOut 0.2s ease-out';
        setTimeout(() => { overlay.remove(); document.body.style.overflow = 'auto'; }, 200);
    }

    document.getElementById('modal-cancel').addEventListener('click', closeModal);
    document.getElementById('modal-confirm').addEventListener('click', () => { closeModal(); if (onConfirm) onConfirm(); });
    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', function esc(e) {
        if (e.key === 'Escape') { closeModal(); document.removeEventListener('keydown', esc); }
    });
}

// ─── MODAL ERROR/WARNING ──────────────────────────────────────────────────────
function showErrorModal(message) {
    const overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 bg-black/70 z-[100] flex items-center justify-center p-4 backdrop-blur-sm';
    overlay.style.animation = 'fadeIn 0.2s ease-out';

    const modal = document.createElement('div');
    modal.className = 'bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden';
    modal.style.animation = 'slideIn 0.3s ease-out';
    modal.innerHTML = `
        <div class="p-6">
            <div class="flex justify-center mb-4">
                <div class="bg-yellow-500/20 rounded-full p-4">
                    <i class="fa-solid fa-triangle-exclamation text-yellow-400 text-4xl"></i>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-white text-center mb-3">Warning</h3>
            <p class="text-gray-300 text-center mb-6">${message}</p>
            <button id="error-ok" class="w-full bg-yellow-500 hover:bg-yellow-400 text-gray-900 py-3 px-6 rounded-lg font-semibold transition">
                <i class="fa-solid fa-check mr-2"></i>OK
            </button>
        </div>`;

    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    document.body.style.overflow = 'hidden';

    function closeModal() {
        overlay.style.animation = 'fadeOut 0.2s ease-out';
        setTimeout(() => { overlay.remove(); document.body.style.overflow = 'auto'; }, 200);
    }

    document.getElementById('error-ok').addEventListener('click', closeModal);
    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });
}

// ─── ALIASES ──────────────────────────────────────────────────────────────────
function showSuccessModal(message) { showToast('success', message); }

// ─── ELIMINAR CUENTA ──────────────────────────────────────────────────────────
function confirmDeleteAccount() {
    showConfirmModal("Are you sure you want to delete your account? This action is irreversible.", () => {
        document.getElementById('deleteAccountForm').submit();
    });
}

// ─── ELIMINAR DEL CARRITO ─────────────────────────────────────────────────────
function confirmRemove(idVideojuego, titulo) {
    showConfirmModal(`Are you sure you want to remove "${titulo}" from your cart?`, () => {
        const form = document.getElementById('remove-form-' + idVideojuego);
        if (form) form.submit();
    });
}

// ─── ANIMACIONES CSS ──────────────────────────────────────────────────────────
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn  { from { opacity: 0; } to { opacity: 1; } }
    @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
    @keyframes slideIn {
        from { transform: scale(0.9) translateY(-20px); opacity: 0; }
        to   { transform: scale(1)   translateY(0);     opacity: 1; }
    }`;
document.head.appendChild(style);

// ─── PASSWORD TOGGLE ──────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', () => {
            const input     = button.parentElement.querySelector('input');
            const eyeOpen   = button.querySelector('.eye-open');
            const eyeClosed = button.querySelector('.eye-closed');
            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        });
    });
});