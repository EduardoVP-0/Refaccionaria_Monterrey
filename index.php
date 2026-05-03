<?php
// Autoload o inclusión de archivos necesarios
require_once 'MODELO/Conexion.php';

// Redirigir temporalmente a la vista de Login en lugar de Reportes
header('Location: VISTA/Login.php');
exit;

// Instanciar la conexión para probar (opcional)
$db = new Conexion();
$conn = $db->getConnection();

if ($conn) {
    echo "<h1>Bienvenido a Refaccionaria Monterrey</h1>";
    echo "<p>Estructura MVC configurada correctamente y conexión a la base de datos establecida.</p>";
} else {
    echo "<h1>Error de configuración</h1>";
    echo "<p>No se pudo establecer la conexión a la base de datos. Verifica los parámetros en Config/Conexion.php.</p>";
}
?>
