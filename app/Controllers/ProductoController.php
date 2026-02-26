<?php
// app/Controllers/ProductoController.php

require_once BASE_PATH . '/app/Models/Videojuego.php';
require_once BASE_PATH . '/app/Models/Carrito.php';
require_once BASE_PATH . '/app/Helpers/Auth.php';
require_once BASE_PATH . '/app/Helpers/Csrf.php';

class ProductoController
{
    private Videojuego $juegos;
    private Carrito    $carrito;

    public function __construct(private PDO $pdo)
    {
        $this->juegos  = new Videojuego($pdo);
        $this->carrito = new Carrito($pdo);
    }

    public function catalogo(): array
    {
        $filtros = [
            'q'          => trim($_GET['q']          ?? ''),
            'genero'     => trim($_GET['genero']      ?? ''),
            'plataforma' => trim($_GET['plataforma']  ?? ''),
            'orden'      => trim($_GET['orden']       ?? ''),
            'precio_max' => trim($_GET['precio_max']  ?? ''),
        ];

        return [
            'juegos'      => $this->juegos->getCatalogo($filtros),
            'generos'     => $this->juegos->getGeneros(),
            'plataformas' => $this->juegos->getPlataformas(),
            'filtros'     => $filtros,
            'cartCount'   => $this->getCartCount(),
            'rolActual'   => $_SESSION['rol'] ?? 'invitado',
        ];
    }

    public function detalle(): array
    {
        $id    = (int) ($_GET['id'] ?? 0);
        $juego = $this->juegos->findById($id);

        if (!$juego) {
            header('Location: /gamestore/public/');
            exit;
        }

        $stockDisponible = (int) $juego['stock'];
        $toastSuccess    = $_SESSION['success'] ?? null;
        $toastError      = $_SESSION['error']   ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        return [
            'juego'           => $juego,
            'screenshots'     => $this->juegos->getScreenshots($id),
            'relacionados'    => $this->juegos->getRelacionados($id, $juego['genero']),
            'stockDisponible' => $stockDisponible,
            'sinStock'        => $stockDisponible <= 0,
            'cartCount'       => $this->getCartCount(),
            'rolActual'       => $_SESSION['rol'] ?? 'invitado',
            'toastSuccess'    => $toastSuccess,
            'toastError'      => $toastError,
        ];
    }

    public function buscar(): array
    {
        $q = trim($_GET['q'] ?? '');
        return [
            'juegos'    => $q ? $this->juegos->buscar($q) : [],
            'q'         => $q,
            'cartCount' => $this->getCartCount(),
            'rolActual' => $_SESSION['rol'] ?? 'invitado',
        ];
    }

    public function adminIndex(): array
    {
        Auth::requireAdmin();
        $search  = trim($_GET['q'] ?? '');
        $success = $_SESSION['success'] ?? null;
        $error   = $_SESSION['error']   ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        return [
            'adminName'  => $_SESSION['user_name'] ?? 'Admin',
            'activePage' => 'productos',
            'juegos'     => $this->juegos->getAll($search),
            'search'     => $search,
            'success'    => $success,
            'error'      => $error,
        ];
    }

    public function adminForm(): array
    {
        Auth::requireAdmin();
        $id    = (int) ($_GET['id'] ?? 0);
        $juego = $id > 0 ? $this->juegos->findById($id) : null;

        return [
            'adminName'   => $_SESSION['user_name'] ?? 'Admin',
            'activePage'  => 'productos',
            'juego'       => $juego,
            'screenshots' => $id > 0 ? $this->juegos->getScreenshots($id) : [],
        ];
    }

    public function adminSave(): void
    {
        Auth::requireAdmin();
        Csrf::verify();

        $id   = (int) ($_POST['id'] ?? 0);
        $data = [
            'titulo'            => trim($_POST['titulo']            ?? ''),
            'descripcion'       => trim($_POST['descripcion']       ?? ''),
            'desarrollador'     => trim($_POST['desarrollador']     ?? ''),
            'genero'            => trim($_POST['genero']            ?? ''),
            'plataforma'        => trim($_POST['plataforma']        ?? ''),
            'fecha_lanzamiento' => trim($_POST['fecha_lanzamiento'] ?? '') ?: null,
            'precio'            => (float) ($_POST['precio']        ?? 0),
            'stock'             => (int)   ($_POST['stock']         ?? 0),
            'feature'           => trim($_POST['feature']           ?? ''),
            'imagen_portada'    => trim($_POST['imagen_portada']    ?? ''),
        ];

        if (empty($data['titulo'])) {
            $_SESSION['error'] = 'Title is required.';
            header('Location: /gamestore/public/admin/productos/form' . ($id ? "?id=$id" : ''));
            exit;
        }

        // Subida de portada
        if (!empty($_FILES['imagen']['name'])) {
            $ext     = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowed)) {
                $_SESSION['error'] = 'Image must be JPG, PNG or WEBP.';
                header('Location: /gamestore/public/admin/productos/form' . ($id ? "?id=$id" : ''));
                exit;
            }
            $filename = uniqid('game_') . '.' . $ext;
            $dest     = BASE_PATH . '/public/assets/img/caratulas/' . $filename;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dest)) {
                $data['imagen_portada'] = $filename;
            } else {
                $_SESSION['error'] = 'Could not upload image. Check folder permissions.';
                header('Location: /gamestore/public/admin/productos/form' . ($id ? "?id=$id" : ''));
                exit;
            }
        }

        // Guardar producto
        if ($id > 0) {
            $this->juegos->update($id, $data);
            $_SESSION['success'] = 'Product updated successfully.';
            $savedId = $id;
        } else {
            $savedId = $this->juegos->create($data);
            $_SESSION['success'] = 'Product created successfully.';
        }

        // Subida de screenshots (hasta 4)
        if (!empty($_FILES['screenshots']['name'][0])) {
            $allowed  = ['jpg', 'jpeg', 'png', 'webp'];
            $uploaded = [];
            $destDir  = BASE_PATH . '/public/assets/img/screenshots/';

            foreach ($_FILES['screenshots']['tmp_name'] as $i => $tmp) {
                if (empty($_FILES['screenshots']['name'][$i])) continue;
                if (count($uploaded) >= 4) break;
                $ext = strtolower(pathinfo($_FILES['screenshots']['name'][$i], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) continue;
                $filename = uniqid('shot_') . '.' . $ext;
                if (move_uploaded_file($tmp, $destDir . $filename)) {
                    $uploaded[] = $filename;
                }
            }

            if (!empty($uploaded)) {
                $this->juegos->saveScreenshots($savedId, $uploaded);
            }
        }

        header('Location: /gamestore/public/admin/productos');
        exit;
    }

    public function adminDelete(): void
    {
        Auth::requireAdmin();
        Csrf::verify();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->juegos->delete($id);
            $_SESSION['success'] = 'Product deleted.';
        }
        header('Location: /gamestore/public/admin/productos');
        exit;
    }

    private function getCartCount(): int
    {
        $uid = $_SESSION['user_id'] ?? null;
        if ($uid) {
            return $this->carrito->countItems($uid);
        }
        return array_sum($_SESSION['carrito_guest'] ?? []);
    }
}
