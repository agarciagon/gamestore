<?php
// app/Views/404.php — página de error 404
// Se include directamente desde el router, sin layout (para evitar doble cabecera).
// Sí carga los assets base de forma autónoma.
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="/gamestore/public/dist/output.css">
    <title>404 — GameStore</title>
</head>

<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center">
    <div class="text-center px-6">
        <p class="text-8xl font-black text-sky-500 mb-2">404</p>
        <h1 class="text-3xl font-bold mb-4">Page not found</h1>
        <p class="text-gray-400 mb-8 max-w-sm mx-auto">
            The page you're looking for doesn't exist or has been moved.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/gamestore/public/" class="px-6 py-3 bg-sky-600 hover:bg-sky-500 text-white font-semibold rounded-lg transition">
                <i class="fa-solid fa-house mr-2"></i>Go Home
            </a>
            <a href="/gamestore/public/catalogo" class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition">
                <i class="fa-solid fa-gamepad mr-2"></i>Browse Catalog
            </a>
        </div>
    </div>
</body>

</html>