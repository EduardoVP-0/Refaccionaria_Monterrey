<?php
require_once __DIR__ . '/../MODELO/Conexion.php';

$conexion = new Conexion();
$db = $conexion->getConnection();

try {
    // Eliminar la columna rol de la tabla tblusuarios
    $sql = "ALTER TABLE tblusuarios DROP COLUMN IF EXISTS rol";
    $db->exec($sql);
    echo "Columna 'rol' eliminada correctamente de la base de datos.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
