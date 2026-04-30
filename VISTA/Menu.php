<!-- ========== SIDEBAR REFACCIONARIA ========== -->
<nav>
    <div class="logo">
        <i class="bx bx-menu menu-icon"></i>
        <span class="tittle"><b>Refaccionaria MTY</b> | Panel</span>
    </div>

    <!-- Header superior -->
    <div class="nav-right">
        <div class="nav-item date-period" style="display: flex; gap: 15px; align-items: center; color: #a1a1aa;">
            <span class="current-date">
                <?php 
                // Configurar zona horaria de CDMX
                date_default_timezone_set('America/Mexico_City');
                
                // Arreglos en español para forzar el idioma sin depender del servidor
                $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                
                $diaSemana = $dias[date('w')];
                $diaMes = date('d');
                $mes = $meses[date('n') - 1];
                $año = date('Y');
                $hora = date('h:i A'); // Agregado también la hora como pediste dinámico
                
                echo "$diaSemana, $diaMes de $mes de $año | $hora"; 
                ?>
            </span>
        </div>

        <div class="nav-item profile">
            <a href="#" class="profile-link" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
                <div style="text-align: right; line-height: 1.2;">
                    <span style="display: block; color: #fff; font-weight: 500; font-size: 14px;">Administrador</span>
                    <span style="display: block; color: #a1a1aa; font-size: 12px;">admin@refacmty.com</span>
                </div>
                <div class="profile-avatar" style="width: 35px; height: 35px; border-radius: 50%; background-color: #3b82f6; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                    AD
                </div>
            </a>
        </div>
    </div>

    <!-- Menú Lateral -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-sidebar" style="color: white; font-size: 18px; padding-left: 20px;">
                <i class='bx bx-wrench icon-menu' style="color: #3b82f6; margin-right: 10px;"></i>
                <span class="logo-name"><b>Refaccionaria MTY</b></span>
            </div>
        </div>

        <div class="sidebar-content">
            <ul class="lists">
                <!-- Reportes (Activo) -->
                <li class="list">
                    <a href="/Refaccionaria_Monterrey/VISTA/Reportes.php" class="nav-link active">
                        <i class='bx bx-bar-chart-alt-2 icon'></i>
                        <span class="link">Reportes</span>
                    </a>
                </li>

                <!-- Usuarios -->
                <li class="list">
                    <a href="#" class="nav-link">
                        <i class='bx bx-group icon'></i>
                        <span class="link">Usuarios</span>
                    </a>
                </li>
            </ul>

            <div class="bottom-content">
                <li class="list">
                    <a href="#" class="nav-link logout" style="color: #ef4444;">
                        <i class='bx bx-log-out iconi' style="color: #ef4444;"></i>
                        <span class="linki">Cerrar sesión</span>
                    </a>
                </li>
            </div>
        </div>
    </div>
</nav>

<section class="overlay"></section>
