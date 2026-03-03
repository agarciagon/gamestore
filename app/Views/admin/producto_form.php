<?php
// app/Views/admin/producto_form.php
$isEdit      = !empty($juego);
$pageTitle   = $isEdit ? 'Edit Product' : 'Add Product';
$activePage  = 'productos';
$j           = $juego ?? [];
$screenshots = $screenshots ?? [];
ob_start();
?>

<div style="max-width:760px;margin:0 auto;">

    <!-- Back + title -->
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="/gamestore/public/admin/productos"
            style="width:34px;height:34px;background:var(--surface);border:1px solid var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--muted);text-decoration:none;transition:background .15s;"
            onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='var(--surface)'">
            <i class="fa-solid fa-arrow-left" style="font-size:13px;"></i>
        </a>
        <div>
            <h1 style="font-size:18px;font-weight:700;color:var(--text);">
                <?= $isEdit ? 'Edit Product' : 'Add New Product' ?>
            </h1>
            <?php if ($isEdit): ?>
                <p style="font-size:12px;color:var(--muted);margin-top:1px;">ID #<?= (int)$j['id'] ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <div style="margin-bottom:16px;padding:12px 16px;background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;color:#c0392b;font-size:13px;display:flex;align-items:center;gap:8px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" action="/gamestore/public/admin/productos/save"
        enctype="multipart/form-data">
        <?= \Csrf::field() ?>
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int)$j['id'] ?>">
        <?php endif; ?>

        <!-- Section: Basic info -->
        <div class="panel" style="margin-bottom:16px;">
            <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border);">
                <i class="fa-solid fa-circle-info" style="color:#635bff;margin-right:6px;"></i> Basic Information
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div style="grid-column:1/-1;">
                    <label class="form-label">Title *</label>
                    <input type="text" name="titulo" required
                        value="<?= htmlspecialchars($j['titulo'] ?? '') ?>"
                        class="form-input" placeholder="Game title">
                </div>
                <div>
                    <label class="form-label">Developer</label>
                    <input type="text" name="desarrollador"
                        value="<?= htmlspecialchars($j['desarrollador'] ?? '') ?>"
                        class="form-input" placeholder="Developer name">
                </div>
                <div>
                    <label class="form-label">Genre</label>
                    <input type="text" name="genero"
                        value="<?= htmlspecialchars($j['genero'] ?? '') ?>"
                        class="form-input" placeholder="Action, RPG…">
                </div>
                <div>
                    <label class="form-label">Platform</label>
                    <input type="text" name="plataforma"
                        value="<?= htmlspecialchars($j['plataforma'] ?? '') ?>"
                        class="form-input" placeholder="PC, PS5, Xbox…">
                </div>
                <div>
                    <label class="form-label">Release Date</label>
                    <input type="date" name="fecha_lanzamiento"
                        value="<?= htmlspecialchars($j['fecha_lanzamiento'] ?? '') ?>"
                        class="form-input">
                </div>
                <div style="grid-column:1/-1;">
                    <label class="form-label">Description</label>
                    <textarea name="descripcion" rows="3" class="form-input"
                        style="resize:vertical;" placeholder="Game description…"><?= htmlspecialchars($j['descripcion'] ?? '') ?></textarea>
                </div>
                <div style="grid-column:1/-1;">
                    <label class="form-label">Features</label>
                    <input type="text" name="feature"
                        value="<?= htmlspecialchars($j['feature'] ?? '') ?>"
                        class="form-input" placeholder="Single-player, Steam Achievements…">
                </div>
            </div>
        </div>

        <!-- Section: Pricing & Stock -->
        <div class="panel" style="margin-bottom:16px;">
            <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border);">
                <i class="fa-solid fa-euro-sign" style="color:#1a9e6e;margin-right:6px;"></i> Pricing & Stock
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <label class="form-label">Price (€)</label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:13px;">€</span>
                        <input type="number" name="precio" step="0.01" min="0"
                            value="<?= number_format((float)($j['precio'] ?? 0), 2, '.', '') ?>"
                            class="form-input" style="padding-left:24px;">
                    </div>
                </div>
                <div>
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" min="0"
                        value="<?= (int)($j['stock'] ?? 0) ?>"
                        class="form-input">
                </div>
            </div>
        </div>

        <!-- Section: Cover image -->
        <div class="panel" style="margin-bottom:16px;">
            <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border); display:flex; align-items:center; gap:6px;">
                <i class="fa-solid fa-image" style="color:#1a56db;"></i> Cover Image
            </div>

            
            <!-- Styled File Input -->
            <label style="display:inline-block; padding:8px 16px; background-color:#1a56db; color:white; font-size:13px; font-weight:500; border-radius:6px; cursor:pointer; transition:background .2s;">
                Select File
                <input type="file" name="imagen_portada" accept="image/*" style="display:none;" onchange="previewCoverImage(this)">
            </label>
            <span id="cover-file-name" style="margin-left:12px; font-size:12px; color:var(--muted);">No file selected</span>
            
            <!-- Preview actual o seleccionada -->
            <div id="cover-preview" style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;">
                <?php if (!empty($j['imagen_portada'])): ?>
                    <img src="/gamestore/public/assets/img/caratulas/<?= htmlspecialchars($j['imagen_portada']) ?>"
                        style="width:120px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--border);" alt="Cover Image">
                    <input type="hidden" name="imagen_portada" value="<?= htmlspecialchars($j['imagen_portada']) ?>">
                <?php endif; ?>
            </div>

            <p style="font-size:11px;color:var(--muted);margin-top:6px;">JPG, PNG, or WEBP. Leave blank to keep current image.</p>
        </div>


        <!-- Section: Screenshots -->
        <div class="panel" style="margin-bottom:24px;">
            <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border); display:flex; align-items:center; gap:6px;">
                <i class="fa-solid fa-images" style="color:#9333ea;"></i> Screenshots (max 4)
            </div>

            <?php if (!empty($screenshots)): ?>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
                    <?php foreach ($screenshots as $s): ?>
                        <img src="/gamestore/public/assets/img/screenshots/<?= htmlspecialchars($s['nombre_archivo']) ?>"
                            style="width:100px;height:70px;object-fit:cover;border-radius:6px;border:1px solid var(--border);" alt="">
                    <?php endforeach; ?>
                </div>
                <p style="font-size:11px;color:var(--muted);margin-bottom:10px;">Uploading new screenshots will replace the current ones.</p>
            <?php endif; ?>

            <!-- Styled multiple file input -->
            <label style="display:inline-block; padding:8px 16px; background-color:#9333ea; color:white; font-size:13px; font-weight:500; border-radius:6px; cursor:pointer; transition:background .2s;">
                Select Files
                <input type="file" name="screenshots[]" accept="image/*" multiple style="display:none;" onchange="previewScreenshots(this)">
            </label>
            <span id="screenshots-file-name" style="margin-left:12px; font-size:12px; color:var(--muted);">No files selected</span>

            <!-- Preview container -->
            <div id="screenshots-preview" style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;"></div>

            <p style="font-size:11px;color:var(--muted);margin-top:6px;">Select up to 4 images. JPG, PNG, or WEBP.</p>
        </div>

        <!-- Actions -->
        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-<?= $isEdit ? 'floppy-disk' : 'plus' ?>"></i>
                <?= $isEdit ? 'Save Changes' : 'Add Product' ?>
            </button>
            <a href="/gamestore/public/admin/productos" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
    function previewCoverImage(input) {
        const file = input.files[0];
        const fileNameSpan = document.getElementById('cover-file-name');
        const previewDiv = document.getElementById('cover-preview');

        fileNameSpan.textContent = file ? file.name : 'No file selected';

        // Mostrar miniatura
        previewDiv.innerHTML = '';
        if (file) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.width = '100px';
            img.style.height = '70px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '8px';
            img.style.border = '1px solid var(--border)';
            previewDiv.appendChild(img);
        }
    }

    function previewScreenshots(input) {
        const files = Array.from(input.files).slice(0, 4); // limitar a 4
        const fileNameSpan = document.getElementById('screenshots-file-name');
        const previewDiv = document.getElementById('screenshots-preview');

        if (files.length === 0) {
            fileNameSpan.textContent = 'No files selected';
        } else {
            fileNameSpan.textContent = files.length + ' file(s) selected';
        }

        // Limpiar preview anterior
        previewDiv.innerHTML = '';

        files.forEach(file => {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.width = '100px';
            img.style.height = '70px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '6px';
            img.style.border = '1px solid var(--border)';
            previewDiv.appendChild(img);
        });
    }
</script>
<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layouts/admin.php';
