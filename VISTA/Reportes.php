<?php
// Evitar acceso directo por URL (solo a través del Front Controller index.php)
if (!defined('ACCESO_PROTEGIDO')) {
    header("Location: /Refaccionaria_Monterrey/index.php");
    exit();
}

// Validar si existe sesión activa
if (!isset($_SESSION['usuario'])) {
    header("Location: /Refaccionaria_Monterrey/index.php?p=login");
    exit();
}

// Vista principal de Reportes (Nueva ruta)

// Incluir el modelo para obtener las estadísticas generales
require_once __DIR__ . '/../MODELO/ReportesModel.php';

$modelo = new ReportesModel();
$total_empleados = $modelo->getTotalEmpleados();
$total_departamentos = $modelo->getTotalDepartamentos();
$total_sucursales = $modelo->getTotalSucursales();
$gasto_mensual = number_format($modelo->getGastoMensualTotal(), 2);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Dinámicos | Refaccionaria Monterrey</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- BoxIcons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Estilos Nuevos (Tema Claro) -->
    <link rel="stylesheet" href="/Refaccionaria_Monterrey/VISTA/CSS/Dashboard.css">
    <link rel="stylesheet" href="/Refaccionaria_Monterrey/VISTA/CSS/Reportes.css">

    <!-- Librerías para Exportar -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body>

    <!-- Incluir Sidebar -->
    <?php include_once 'Menu.php'; ?>

    <section class="main-content">
        <!-- Tarjetas Generales (Estilo Dashboard WECSTEP) -->
        <div class="cards-container">
            <div class="card-info">
                <div class="card-icon" >
                    <i class='bx bx-group'></i>
                </div>
                <div class="card-content" style="text-align: right;">
                    <h3>Total Empleados</h3>
                    <p class="card-number"><?php echo $total_empleados; ?></p>
                </div>
            </div>

            <div class="card-info">
                <div class="card-icon" >
                    <i class='bx bx-buildings'></i>
                </div>
                <div class="card-content" style="text-align: right;">
                    <h3>Departamentos</h3>
                    <p class="card-number"><?php echo $total_departamentos; ?></p>
                </div>
            </div>

            <div class="card-info">
                <div class="card-icon" >
                    <i class='bx bx-map-pin'></i>
                </div>
                <div class="card-content" style="text-align: right;">
                    <h3>Sucursales Activas</h3>
                    <p class="card-number"><?php echo $total_sucursales; ?></p>
                </div>
            </div>

            <div class="card-info">
                <div class="card-icon" >
                    <i class='bx bx-dollar-circle'></i>
                </div>
                <div class="card-content" style="text-align: right;">
                    <h3>Gasto Nómina</h3>
                    <p class="card-number" style="font-size: 20px;">$<?php echo $gasto_mensual; ?></p>
                </div>
            </div>
        </div>

        <div class="fichas-section">
            <h4>Fichas de Consultas Específicas</h4>
            <!-- Grid de 10 Fichas (2 filas de 5 columnas) -->
            <div class="fichas-grid" id="fichas-container">
                <div class="ficha-card" data-id="1">
                    <i class='bx bx-building-house ficha-icon'></i>
                    <div class="ficha-info">
                        <h4>Promedio Salarial</h4>
                        <p>Por Departamento</p>
                    </div>
                </div>
                
                <div class="ficha-card" data-id="2">
                    <i class='bx bx-time-five ficha-icon'></i>
                    <div class="ficha-info">
                        <h4>Reporte de Antigüedad</h4>
                        <p>Departamento 20</p>
                    </div>
                </div>

                <div class="ficha-card" data-id="3">
                    <i class='bx bx-money ficha-icon'></i>
                    <div class="ficha-info">
                        <h4>Gasto Departamental</h4>
                        <p>Suma > $15,000</p>
                    </div>
                </div>

                <div class="ficha-card" data-id="4">
                    <i class='bx bx-briefcase-alt-2 ficha-icon'></i>
                    <div class="ficha-info">
                        <h4>Gasto por Puesto</h4>
                        <p>Suma > $2,000</p>
                    </div>
                </div>

                <div class="ficha-card" data-id="5">
                    <i class='bx bx-user-pin ficha-icon'></i>
                    <div class="ficha-info">
                        <h4>Estructura Personal</h4>
                        <p>Empleados y Jefes</p>
                    </div>
                </div>

                <div class="ficha-card" data-id="6">
                    <i class='bx bx-network-chart ficha-icon'></i>
                    <div class="ficha-info">
                        <h4>Carga de Mando</h4>
                        <p>Subordinados</p>
                    </div>
                </div>

                <div class="ficha-card" data-id="7">
                    <i class='bx bx-map-alt ficha-icon'></i>
                    <div class="ficha-info">
                        <h4>Geografía</h4>
                        <p>Distribución</p>
                    </div>
                </div>

                <div class="ficha-card" data-id="8">
                    <i class='bx bx-id-card ficha-icon'></i>
                    <div class="ficha-info">
                        <h4>Fichas Generales</h4>
                        <p>Todos los empleados</p>
                    </div>
                </div>

                <div class="ficha-card" data-id="9">
                    <i class='bx bx-trending-down ficha-icon'></i>
                    <div class="ficha-info">
                        <h4>Alerta Salarial</h4>
                        <p>Menores al mín.</p>
                    </div>
                </div>

                <div class="ficha-card" data-id="10">
                    <i class='bx bx-user-x ficha-icon'></i>
                    <div class="ficha-info">
                        <h4>Operativos</h4>
                        <p>Sin Subordinados</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Controles Externos a la tabla -->
        <div class="controles-externos">
            <div class="search-wrapper">
                <i class='bx bx-search'></i>
                <input type="text" id="searchInput" placeholder="Seleccione un reporte para buscar..." disabled>
            </div>
            <div class="action-buttons">
                <button class="btn-export btn-pdf" id="btnExportPDF" disabled title="Exportar a PDF">
                    <i class='bx bxs-file-pdf'></i> PDF
                </button>
                <button class="btn-export btn-excel" id="btnExportExcel" disabled title="Exportar a Excel">
                    <i class='bx bxs-file-export'></i> Excel
                </button>
            </div>
        </div>

        <!-- Panel de la Tabla (Clara) -->
        <div class="panel-tabla">
            <div class="panel-header">
                <h3 id="titulo-tabla">Seleccione un reporte</h3>
            </div>

            <div class="table-responsive">
                <table class="tabla-dinamica" id="mainTable">
                    <thead id="tableHead">
                        <!-- Columnas dinámicas -->
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td colspan="100%">
                                <div class="empty-message">
                                    <i class='bx bx-bar-chart-alt-2'></i>
                                    <p>Haga clic en una de las tarjetas de arriba para visualizar los datos.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Contenedor Paginación -->
            <div class="paginacion-container" id="paginationContainer" style="display: none;">
                <div class="paginacion-info">
                    Mostrando <span id="pagStart">0</span> - <span id="pagEnd">0</span> de <span id="pagTotal">0</span> registros
                </div>
                <div class="paginacion-buttons" id="paginationControls">
                    <!-- Botones generados en JS -->
                </div>
            </div>
        </div>
    </section>

    <!-- Script del Sidebar -->
    <script src="/Refaccionaria_Monterrey/VISTA/JS/Dashboard.js"></script>

    <!-- Lógica de Reportes -->
    <script src="/Refaccionaria_Monterrey/VISTA/JS/Reportes.js"></script>
</body>
</html>
