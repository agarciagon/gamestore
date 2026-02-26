<?php
// app/Models/Videojuego.php

class Videojuego
{
    public function __construct(private PDO $pdo) {}

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM videojuego WHERE id=? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getCatalogo(array $filtros = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['genero'])) {
            $where[] = 'genero=?';
            $params[] = $filtros['genero'];
        }
        if (!empty($filtros['plataforma'])) {
            $where[] = 'plataforma=?';
            $params[] = $filtros['plataforma'];
        }
        if (isset($filtros['precio_max']) && $filtros['precio_max'] !== '') {
            $where[] = 'precio<=?';
            $params[] = (float) $filtros['precio_max'];
        }
        if (!empty($filtros['q'])) {
            $where[] = '(titulo LIKE ? OR descripcion LIKE ? OR desarrollador LIKE ?)';
            $like     = '%' . $filtros['q'] . '%';
            $params   = array_merge($params, [$like, $like, $like]);
        }

        $orden = match ($filtros['orden'] ?? '') {
            'precio_asc'  => 'precio ASC',
            'precio_desc' => 'precio DESC',
            'nombre'      => 'titulo ASC',
            default       => 'fecha_creacion DESC',
        };

        $stmt = $this->pdo->prepare(
            'SELECT * FROM videojuego WHERE ' . implode(' AND ', $where) . " ORDER BY $orden"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscar(string $q): array
    {
        $like = '%' . $q . '%';
        $stmt = $this->pdo->prepare(
            'SELECT * FROM videojuego WHERE titulo LIKE ? OR descripcion LIKE ? OR desarrollador LIKE ? OR genero LIKE ?
             ORDER BY titulo ASC'
        );
        $stmt->execute([$like, $like, $like, $like]);
        return $stmt->fetchAll();
    }

    public function getScreenshots(int $id): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT nombre_archivo FROM screenshots WHERE id_videojuego=? ORDER BY orden ASC'
        );
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function saveScreenshots(int $id, array $filenames): void
    {
        $this->pdo->prepare('DELETE FROM screenshots WHERE id_videojuego=?')->execute([$id]);

        $stmt = $this->pdo->prepare(
            'INSERT INTO screenshots (id_videojuego, nombre_archivo, orden) VALUES (?, ?, ?)'
        );
        foreach ($filenames as $orden => $filename) {
            $stmt->execute([$id, $filename, $orden + 1]);
        }
    }

    public function getRelacionados(int $id, string $genero, int $limit = 4): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM videojuego WHERE genero=? AND id!=? LIMIT ?'
        );
        $stmt->execute([$genero, $id, $limit]);
        return $stmt->fetchAll();
    }

    public function getDestacados(int $limit = 4): array
    {
        return $this->pdo->query(
            "SELECT * FROM videojuego ORDER BY fecha_creacion DESC LIMIT $limit"
        )->fetchAll();
    }

    public function getRecientes(int $limit = 4): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM videojuego ORDER BY fecha_creacion DESC LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getOfertas(int $limit = 4): array
    {
        return $this->pdo->query(
            "SELECT * FROM videojuego WHERE precio>0 ORDER BY precio ASC LIMIT $limit"
        )->fetchAll();
    }

    public function getGeneros(): array
    {
        return $this->pdo->query(
            'SELECT DISTINCT genero FROM videojuego WHERE genero IS NOT NULL ORDER BY genero'
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getPlataformas(): array
    {
        return $this->pdo->query(
            'SELECT DISTINCT plataforma FROM videojuego WHERE plataforma IS NOT NULL ORDER BY plataforma'
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAll(string $search = ''): array
    {
        if ($search !== '') {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM videojuego WHERE titulo LIKE ? OR genero LIKE ? OR desarrollador LIKE ?
                 ORDER BY fecha_creacion DESC'
            );
            $stmt->execute(["%$search%", "%$search%", "%$search%"]);
        } else {
            $stmt = $this->pdo->query('SELECT * FROM videojuego ORDER BY fecha_creacion DESC');
        }
        return $stmt->fetchAll();
    }

    public function getStats(): array
    {
        return $this->pdo->query(
            'SELECT COUNT(*) AS total_productos, SUM(stock) AS total_stock,
                    SUM(precio*stock) AS valor_inventario,
                    MIN(precio) AS precio_min, MAX(precio) AS precio_max, AVG(precio) AS precio_avg
             FROM videojuego'
        )->fetch();
    }

    public function getPorGenero(): array
    {
        return $this->pdo->query(
            'SELECT genero, COUNT(*) AS qty, SUM(stock) AS stock_total
             FROM videojuego GROUP BY genero ORDER BY qty DESC'
        )->fetchAll();
    }

    public function getPorPlataforma(): array
    {
        return $this->pdo->query(
            'SELECT plataforma, COUNT(*) AS qty FROM videojuego GROUP BY plataforma ORDER BY qty DESC'
        )->fetchAll();
    }

    public function getStockBajo(int $limit = 6): array
    {
        return $this->pdo->query(
            "SELECT titulo, stock, precio FROM videojuego ORDER BY stock ASC LIMIT $limit"
        )->fetchAll();
    }

    public function create(array $d): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO videojuego
               (titulo,descripcion,desarrollador,genero,plataforma,fecha_lanzamiento,precio,stock,feature,imagen_portada)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $d['titulo'],
            $d['descripcion'],
            $d['desarrollador'],
            $d['genero'],
            $d['plataforma'],
            $d['fecha_lanzamiento'],
            $d['precio'],
            $d['stock'],
            $d['feature'],
            $d['imagen_portada'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $d): void
    {
        $this->pdo->prepare(
            'UPDATE videojuego SET titulo=?,descripcion=?,desarrollador=?,genero=?,plataforma=?,
             fecha_lanzamiento=?,precio=?,stock=?,feature=?,imagen_portada=? WHERE id=?'
        )->execute([
            $d['titulo'],
            $d['descripcion'],
            $d['desarrollador'],
            $d['genero'],
            $d['plataforma'],
            $d['fecha_lanzamiento'],
            $d['precio'],
            $d['stock'],
            $d['feature'],
            $d['imagen_portada'],
            $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM videojuego WHERE id=?')->execute([$id]);
    }

    public function getTopVentas(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT v.*, COALESCE(SUM(pi.cantidad), 0) AS total_vendidos
             FROM videojuego v
             LEFT JOIN pedido_items pi ON pi.id_videojuego = v.id
             GROUP BY v.id
             ORDER BY total_vendidos DESC, v.fecha_creacion DESC
             LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function decrementStock(int $id, int $qty): void
    {
        $this->pdo->prepare(
            'UPDATE videojuego SET stock=stock-? WHERE id=? AND stock>=?'
        )->execute([$qty, $id, $qty]);
    }

    public function incrementStock(int $id, int $qty): void
    {
        $this->pdo->prepare('UPDATE videojuego SET stock=stock+? WHERE id=?')->execute([$qty, $id]);
    }
}