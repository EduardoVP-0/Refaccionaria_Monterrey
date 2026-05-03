<?php
session_start();
require_once __DIR__ . '/../MODELO/LoginModel.php';

// Limpiar token en DB si existe la cookie
if (isset($_COOKIE['remember_token_refac'])) {
    if (isset($_SESSION['usuario'])) {
        $modelo = new LoginModel();
        // Le pasamos un token nulo o vacío para invalidarlo en la BD
        $modelo->updateRememberToken($_SESSION['usuario']['id_usuario'], null);
    }
    // Borrar la cookie
    setcookie('remember_token_refac', '', time() - 3600, '/');
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la sesión final
session_destroy();

// Redirigir al inicio de sesión
header("Location: /Refaccionaria_Monterrey/VISTA/Login.php");
exit();
?>
