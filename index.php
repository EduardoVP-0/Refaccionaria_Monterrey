<?php
// =====================================================================
// index.php — Enrutador Centralizado (Front Controller)
// Todas las vistas pasan por aquí. Las URLs serán limpias: /index.php?p=reportes
// =====================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mapa de rutas permitidas
$rutas_publicas = [
    'login' => 'VISTA/Login.php'
];

$rutas_protegidas = [
    'reportes' => 'VISTA/Reportes.php',
    'usuarios' => 'VISTA/Usuarios.php'
];

$p = $_GET['p'] ?? 'reportes'; // Por defecto, si entra a la raíz, va a reportes (que lo redirigirá a login si no hay sesión)

// 1. Verificar si es ruta pública
if (array_key_exists($p, $rutas_publicas)) {
    // Si ya tiene sesión, no debe ver el login de nuevo
    if ($p === 'login' && isset($_SESSION['usuario'])) {
        header('Location: /Refaccionaria_Monterrey/index.php?p=reportes');
        exit();
    }
    define('ACCESO_PROTEGIDO', true);
    require __DIR__ . '/' . $rutas_publicas[$p];
    exit();
}

// 2. Verificar si es ruta protegida
if (array_key_exists($p, $rutas_protegidas)) {
    // Control de acceso unificado: Si no hay sesión, al login
    if (!isset($_SESSION['usuario'])) {
        header('Location: /Refaccionaria_Monterrey/index.php?p=login');
        exit();
    }

    define('ACCESO_PROTEGIDO', true); // Constante para que las vistas sepan que pasaron por el router
    require __DIR__ . '/' . $rutas_protegidas[$p];
    exit();
}

// 3. Ruta no encontrada (404)
header('Location: /Refaccionaria_Monterrey/index.php?p=reportes');
exit();
?>