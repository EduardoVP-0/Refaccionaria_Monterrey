<?php
require_once __DIR__ . '/../MODELO/Conexion.php';

$conexion = new Conexion();
$db = $conexion->getConnection();

try {
    // 1. Agregar columna rol si no existe
    $sql = "ALTER TABLE tblusuarios ADD COLUMN IF NOT EXISTS rol VARCHAR(50) DEFAULT 'Administrador'";
    $db->exec($sql);
    echo "Columna 'rol' agregada correctamente.\n";

    // 2. Opcional: Asegurarnos que todos tengan un rol (por si acaso)
    $sql = "UPDATE tblusuarios SET rol = 'Administrador' WHERE rol IS NULL";
    $db->exec($sql);
    echo "Roles actualizados.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
