<?php

require_once __DIR__ . '/../MODELO/ReportesModel.php';

// Limpiar cualquier salida previa para evitar corromper el JSON
if (ob_get_length()) ob_clean();

header('Content-Type: application/json');

try {
    $modelo = new ReportesModel();
    
    // Obtener el ID del reporte solicitado
    $reporte_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    $datos = [];
    
    switch ($reporte_id) {
        case 1:
            $datos = $modelo->getPromedioSalarialPorDepto();
            break;
        case 2:
            $datos = $modelo->getAntiguedadEmpleados();
            break;
        case 3:
            $datos = $modelo->getGastoTotalPorDepto();
            break;
        case 4:
            $datos = $modelo->getGastoSalarialPorPuesto();
            break;
        case 5:
            $datos = $modelo->getEmpleadosYJefes();
            break;
        case 6:
            $datos = $modelo->getJefesYSubordinados();
            break;
        case 7:
            $datos = $modelo->getReporteGeografico();
            break;
        case 8:
            $datos = $modelo->getFichasEmpleados();
            break;
        case 9:
            $datos = $modelo->getSalariosMenoresIT();
            break;
        case 10:
            $datos = $modelo->getEmpleadosSinSubordinados();
            break;
        default:
            throw new Exception("ID de reporte no válido.");
    }
    
    // Retornar éxito y los datos
    echo json_encode([
        'status' => 'success',
        'data' => $datos
    ]);

} catch (Exception $e) {
    // Retornar error en formato JSON
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
