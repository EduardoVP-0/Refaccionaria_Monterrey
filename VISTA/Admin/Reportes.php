<?php
// Vista principal de Reportes
// Por ahora sin comprobación de sesión estricta ya que se integrará después
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
    
    <!-- Estilos -->
    <link rel="stylesheet" href="/Refaccionaria_Monterrey/VISTA/CSS/Admin/Dashboard.css">
    <link rel="stylesheet" href="/Refaccionaria_Monterrey/VISTA/CSS/Reportes.css">

    <!-- Librerías para Exportar (PDF y Excel) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body>

    <!-- Incluir Sidebar -->
    <?php include_once 'MenuAdmin.php'; ?>

    <section class="main-content">
        <div class="page-header">
            <h2>Módulo de Reportes e Inteligencia de Negocio</h2>
        </div>

        <!-- 10 Fichas de Reportes -->
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
                    <h4>Antigüedad</h4>
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
                    <p>Suma > $2,000 (Sin TRAN)</p>
                </div>
            </div>

            <div class="ficha-card" data-id="5">
                <i class='bx bx-user-pin ficha-icon'></i>
                <div class="ficha-info">
                    <h4>Estructura Personal</h4>
                    <p>Empleados y sus Jefes</p>
                </div>
            </div>

            <div class="ficha-card" data-id="6">
                <i class='bx bx-network-chart ficha-icon'></i>
                <div class="ficha-info">
                    <h4>Carga de Mando</h4>
                    <p>Jefes y Subordinados</p>
                </div>
            </div>

            <div class="ficha-card" data-id="7">
                <i class='bx bx-map-alt ficha-icon'></i>
                <div class="ficha-info">
                    <h4>Reporte Geográfico</h4>
                    <p>Distribución de Personal</p>
                </div>
            </div>

            <div class="ficha-card" data-id="8">
                <i class='bx bx-id-card ficha-icon'></i>
                <div class="ficha-info">
                    <h4>Fichas Empleados</h4>
                    <p>Búsqueda Específica</p>
                </div>
            </div>

            <div class="ficha-card" data-id="9">
                <i class='bx bx-trending-down ficha-icon'></i>
                <div class="ficha-info">
                    <h4>Alerta Salarial</h4>
                    <p>Menores al mín. de IT</p>
                </div>
            </div>

            <div class="ficha-card" data-id="10">
                <i class='bx bx-user-x ficha-icon'></i>
                <div class="ficha-info">
                    <h4>Puestos Operativos</h4>
                    <p>Empleados sin Subordinados</p>
                </div>
            </div>
        </div>

        <!-- Panel de la Tabla (Dinámica y Oscura) -->
        <div class="panel-tabla">
            <div class="panel-header">
                <h3 id="titulo-tabla">Seleccione un reporte</h3>
                <div class="controles">
                    <div class="search-box">
                        <i class='bx bx-search'></i>
                        <input type="text" id="searchInput" placeholder="Filtrar resultados..." disabled>
                    </div>
                    <button class="btn-export btn-pdf" id="btnExportPDF" disabled>
                        <i class='bx bxs-file-pdf'></i> PDF
                    </button>
                    <button class="btn-export btn-excel" id="btnExportExcel" disabled>
                        <i class='bx bxs-file-export'></i> Excel
                    </button>
                </div>
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
        </div>
    </section>

    <!-- Script del Sidebar (igual al de WECSTEP) -->
    <script>
        const navBar = document.querySelector("nav");
        const menuBtns = document.querySelectorAll(".menu-icon, .icon-menu");
        const overlay = document.querySelector(".overlay");

        menuBtns.forEach((menuBtn) => {
            menuBtn.addEventListener("click", () => {
                navBar.classList.toggle("open");
                overlay.classList.toggle("active");
            });
        });

        overlay.addEventListener("click", () => {
            navBar.classList.remove("open");
            overlay.classList.remove("active");
        });
    </script>

    <!-- Lógica de Reportes -->
    <script src="/Refaccionaria_Monterrey/VISTA/JS/Reportes.js"></script>
</body>
</html>
