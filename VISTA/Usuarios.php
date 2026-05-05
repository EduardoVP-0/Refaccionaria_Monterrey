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

require_once __DIR__ . '/../MODELO/UsuariosModel.php';

$model = new UsuariosModel();

// Mensajes flash
$mensaje_success = $_SESSION['success'] ?? '';
$mensaje_error   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Búsqueda
$busqueda = trim($_GET['busqueda'] ?? '');

// Paginación
$por_pagina    = 10;
$pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
$offset        = ($pagina_actual - 1) * $por_pagina;

// Datos
$total_usuarios  = $model->getTotalUsuarios();
$total_activos   = $model->getTotalActivos();
$total_inactivos = $model->getTotalInactivos();

$total_filtrado = $model->getTotalUsuariosFiltrado($busqueda);
$total_paginas  = $total_filtrado > 0 ? ceil($total_filtrado / $por_pagina) : 1;
$usuarios       = $model->getUsuariosPaginados($por_pagina, $offset, $busqueda);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios | Refaccionaria Monterrey</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="/Refaccionaria_Monterrey/VISTA/CSS/Dashboard.css">
    <link rel="stylesheet" href="/Refaccionaria_Monterrey/VISTA/CSS/Usuarios.css">
</head>
<body>

    <?php include __DIR__ . '/Menu.php'; ?>

    <div class="main-content">

        <!-- Encabezado del módulo -->
        <div class="module-header">
            <div>
                <h1 class="module-title"><i class='bx bx-group'></i> Gestión de Usuarios</h1>
                <p class="module-subtitle">Administra los accesos y datos de los usuarios del sistema</p>
            </div>
            <button class="btn-add-usuario" onclick="document.getElementById('modalAgregarUsuario').classList.add('show')">
                <i class='bx bx-plus'></i> Agregar Usuario
            </button>
        </div>

        <!-- Tarjetas informativas -->
        <div class="cards-container-usuarios">
            <div class="card-info">
                <div class="card-icon" style="background:#eff6ff; color:#3b82f6;">
                    <i class='bx bx-group'></i>
                </div>
                <div class="card-content" style="text-align:right;">
                    <h3>Total Usuarios</h3>
                    <p class="card-number"><?php echo $total_usuarios; ?></p>
                </div>
            </div>
            <div class="card-info">
                <div class="card-icon" style="background:#ecfdf5; color:#10b981;">
                    <i class='bx bx-user-check'></i>
                </div>
                <div class="card-content" style="text-align:right;">
                    <h3>Activos</h3>
                    <p class="card-number"><?php echo $total_activos; ?></p>
                </div>
            </div>
            <div class="card-info">
                <div class="card-icon" style="background:#fef2f2; color:#ef4444;">
                    <i class='bx bx-user-x'></i>
                </div>
                <div class="card-content" style="text-align:right;">
                    <h3>Inactivos</h3>
                    <p class="card-number"><?php echo $total_inactivos; ?></p>
                </div>
            </div>
        </div>

        <!-- Mensajes de éxito/error -->
        <?php if ($mensaje_success): ?>
        <div class="alert-msg success" id="alertMsg">
            <i class='bx bx-check-circle'></i>
            <?php echo htmlspecialchars($mensaje_success); ?>
            <button onclick="this.parentElement.style.display='none'"><i class='bx bx-x'></i></button>
        </div>
        <?php endif; ?>
        <?php if ($mensaje_error): ?>
        <div class="alert-msg error" id="alertMsg">
            <i class='bx bx-error-circle'></i>
            <?php echo htmlspecialchars($mensaje_error); ?>
            <button onclick="this.parentElement.style.display='none'"><i class='bx bx-x'></i></button>
        </div>
        <?php endif; ?>

        <!-- Buscador externo -->
        <div class="controles-externos">
            <form method="GET" action="" id="formBusqueda" style="display:contents;">
                <div class="search-wrapper-usuarios">
                    <i class='bx bx-search'></i>
                    <input
                        type="text"
                        id="searchInput"
                        name="busqueda"
                        placeholder="Buscar por nombre o apellidos..."
                        value="<?php echo htmlspecialchars($busqueda); ?>"
                        autocomplete="off">
                    <input type="hidden" name="p" value="usuarios">
                    <input type="hidden" name="pagina" value="1">
                </div>
            </form>
        </div>

        <!-- Panel de tabla -->
        <div class="panel-tabla">
            <div class="panel-header">
                <h3><i class='bx bx-list-ul'></i> Listado de Usuarios</h3>
                <span class="badge-count"><?php echo $total_filtrado; ?> registros</span>
            </div>

            <div class="table-responsive">
                <table class="tabla-usuarios" id="tablaUsuarios">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Correo</th>
                            <th style="text-align:center;">Estado</th>
                            <th style="text-align:center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($usuarios)): ?>
                            <?php foreach ($usuarios as $i => $u): ?>
                            <tr class="fade-in-row" style="animation-delay: <?php echo $i * 0.04; ?>s;">
                                <td><strong><?php echo htmlspecialchars($u['id_usuario']); ?></strong></td>
                                <td>
                                    <?php
                                    $nombre_completo = trim(
                                        htmlspecialchars($u['nombre'] ?? '') . ' ' .
                                        htmlspecialchars($u['apaterno'] ?? '') . ' ' .
                                        htmlspecialchars($u['amaterno'] ?? '')
                                    );
                                    ?>
                                    <div class="usuario-nombre">
                                        <div class="usuario-avatar">
                                            <?php echo strtoupper(substr($u['nombre'] ?? 'U', 0, 1) . substr($u['apaterno'] ?? 'S', 0, 1)); ?>
                                        </div>
                                        <?php echo $nombre_completo; ?>
                                    </div>
                                </td>
                                <td style="font-size:13px; color:#6b7280;"><?php echo htmlspecialchars($u['correo']); ?></td>
                                <td style="text-align:center;">
                                    <?php if ($u['estado'] == true || $u['estado'] == 't'): ?>
                                        <span class="estado-badge activo">Activo</span>
                                    <?php else: ?>
                                        <span class="estado-badge inactivo">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center;">
                                    <?php
                                    $es_activo = ($u['estado'] == true || $u['estado'] == 't');
                                    $nuevo_estado = $es_activo ? '0' : '1';
                                    $titulo_btn = $es_activo ? 'Deshabilitar acceso' : 'Habilitar acceso';
                                    ?>
                                    <!-- Botón Editar -->
                                    <button class="btn-accion-edit"
                                        title="Editar usuario"
                                        onclick="abrirModalEditar('<?php echo $u['id_usuario']; ?>')">
                                        <i class='bx bx-edit-alt'></i>
                                    </button>
                                    <!-- Botón Habilitar/Deshabilitar -->
                                    <button class="btn-estado <?php echo $es_activo ? 'deshabilitar' : 'habilitar'; ?>"
                                        title="<?php echo $titulo_btn; ?>"
                                        onclick="confirmarCambioEstado('<?php echo $u['id_usuario']; ?>', '<?php echo $nuevo_estado; ?>', '<?php echo addslashes($nombre_completo); ?>', <?php echo $es_activo ? 'true' : 'false'; ?>)">
                                        <i class='bx <?php echo $es_activo ? 'bx-user-minus' : 'bx-user-plus'; ?>'></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-message">
                                    <i class='bx bx-user-x'></i>
                                    <span>No se encontraron usuarios<?php echo !empty($busqueda) ? ' para "' . htmlspecialchars($busqueda) . '"' : ''; ?></span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="paginacion-container">
                <div class="paginacion-info">
                    Mostrando <span><?php echo count($usuarios); ?></span> de <span><?php echo $total_filtrado; ?></span> usuarios
                </div>
                <div class="paginacion-buttons">
                    <?php
                    $params = !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : '';
                    ?>
                    <button class="page-btn" <?php echo $pagina_actual <= 1 ? 'disabled' : ''; ?>
                        onclick="window.location='?p=usuarios&pagina=<?php echo $pagina_actual - 1 . $params; ?>'">
                        <i class='bx bx-chevron-left'></i>
                    </button>
                    <?php for ($p = 1; $p <= $total_paginas; $p++): ?>
                        <button class="page-btn <?php echo $p == $pagina_actual ? 'active' : ''; ?>"
                            onclick="window.location='?p=usuarios&pagina=<?php echo $p . $params; ?>'">
                            <?php echo $p; ?>
                        </button>
                    <?php endfor; ?>
                    <button class="page-btn" <?php echo $pagina_actual >= $total_paginas ? 'disabled' : ''; ?>
                        onclick="window.location='?p=usuarios&pagina=<?php echo $pagina_actual + 1 . $params; ?>'">
                        <i class='bx bx-chevron-right'></i>
                    </button>
                </div>
            </div>
        </div>
    </div><!-- /main-content -->


    <!-- ==================== MODAL: AGREGAR USUARIO ==================== -->
    <div class="modal-overlay" id="modalAgregarUsuario">
        <div class="modal-box">
            <div class="modal-header-box">
                <div class="modal-icon-wrap" style="background:#eff6ff; color:#3b82f6;">
                    <i class='bx bx-user-plus'></i>
                </div>
                <div>
                    <h2>Agregar Usuario</h2>
                    <p>Completa los datos del nuevo usuario del sistema</p>
                </div>
                <button class="modal-close-btn" onclick="document.getElementById('modalAgregarUsuario').classList.remove('show')">
                    <i class='bx bx-x'></i>
                </button>
            </div>

            <form action="/Refaccionaria_Monterrey/CONTROLADOR/UsuariosController.php" method="POST" id="formAgregarUsuario" novalidate>
                <input type="hidden" name="accion" value="agregar">

                <div class="form-grid-2">
                    <div class="form-group-modal">
                        <label>Nombre <span class="required">*</span></label>
                        <input type="text" name="nombre" id="inp_nombre" placeholder="Ej: Juan" maxlength="35" required>
                        <span class="field-error" id="err_nombre"></span>
                    </div>
                    <div class="form-group-modal">
                        <label>Apellido Paterno <span class="required">*</span></label>
                        <input type="text" name="apaterno" id="inp_apaterno" placeholder="Ej: García" maxlength="35" required>
                        <span class="field-error" id="err_apaterno"></span>
                    </div>
                </div>

                <div class="form-group-modal">
                    <label>Apellido Materno</label>
                    <input type="text" name="amaterno" id="inp_amaterno" placeholder="Ej: López (opcional)" maxlength="35">
                    <span class="field-error" id="err_amaterno"></span>
                </div>

                <div class="form-group-modal">
                    <label>Correo Electrónico <span class="required">*</span></label>
                    <input type="email" name="correo" id="inp_correo" placeholder="usuario@empresa.com" maxlength="50" required>
                    <span class="field-error" id="err_correo"></span>
                </div>

                <div class="form-group-modal">
                    <label>Contraseña <span class="required">*</span></label>
                    <div class="input-password-wrap">
                        <input type="password" name="password" id="inp_password" placeholder="Mínimo 6 caracteres" required>
                        <i class='bx bx-show toggle-modal-password' id="toggleModalPass"></i>
                    </div>
                    <span class="field-hint">Al menos 6 caracteres.</span>
                    <span class="field-error" id="err_password"></span>
                </div>

                <div class="modal-footer-box">
                    <button type="button" class="btn-cancelar" onclick="document.getElementById('modalAgregarUsuario').classList.remove('show')">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-guardar" id="btnGuardarUsuario">
                        <i class='bx bx-check'></i> Guardar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- ==================== MODAL: CONFIRMAR CAMBIO ESTADO ==================== -->
    <div class="modal-overlay" id="modalConfirmarEstado">
        <div class="modal-box modal-sm">
            <div class="modal-confirm-icon" id="iconConfirm">
                <i class='bx bx-user-minus' id="iconEstadoModal"></i>
            </div>
            <h2 id="tituloConfirmar" style="text-align:center; font-size:18px; color:#1f2937; margin-bottom:8px;">Confirmar Acción</h2>
            <p id="mensajeConfirmar" style="text-align:center; font-size:14px; color:#6b7280; margin-bottom:24px;"></p>
            <div style="display:flex; gap:12px; justify-content:center;">
                <button class="btn-cancelar" onclick="document.getElementById('modalConfirmarEstado').classList.remove('show')">
                    Cancelar
                </button>
                <a href="#" id="btnConfirmarEstado" class="btn-guardar" id="btnConfirmarAccion">
                    Confirmar
                </a>
            </div>
        </div>
    </div>


    <!-- ==================== MODAL: EDITAR USUARIO ==================== -->
    <div class="modal-overlay" id="modalEditarUsuario">
        <div class="modal-box">
            <div class="modal-header-box">
                <div class="modal-icon-wrap" style="background:#fef3c7; color:#d97706;">
                    <i class='bx bx-edit-alt'></i>
                </div>
                <div>
                    <h2>Editar Usuario</h2>
                    <p>Modifica los datos del usuario seleccionado</p>
                </div>
                <button class="modal-close-btn" onclick="cerrarModalEditar()">
                    <i class='bx bx-x'></i>
                </button>
            </div>

            <!-- Estado de carga mientras se obtienen los datos -->
            <div id="editLoadingState" style="text-align:center; padding: 40px 0; color:#9ca3af;">
                <i class='bx bx-loader-alt' style="font-size:32px; animation: spin 1s linear infinite;"></i>
                <p style="margin-top:10px; font-size:14px;">Cargando datos...</p>
            </div>

            <form action="/Refaccionaria_Monterrey/CONTROLADOR/UsuariosController.php" method="POST"
                  id="formEditarUsuario" novalidate style="display:none;">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id_usuario" id="edit_id_usuario">

                <div class="form-grid-2">
                    <div class="form-group-modal">
                        <label>Nombre <span class="required">*</span></label>
                        <input type="text" name="nombre" id="edit_nombre" placeholder="Ej: Juan" maxlength="35" required>
                        <span class="field-error" id="edit_err_nombre"></span>
                    </div>
                    <div class="form-group-modal">
                        <label>Apellido Paterno <span class="required">*</span></label>
                        <input type="text" name="apaterno" id="edit_apaterno" placeholder="Ej: García" maxlength="35" required>
                        <span class="field-error" id="edit_err_apaterno"></span>
                    </div>
                </div>

                <div class="form-group-modal">
                    <label>Apellido Materno</label>
                    <input type="text" name="amaterno" id="edit_amaterno" placeholder="Opcional" maxlength="35">
                    <span class="field-error" id="edit_err_amaterno"></span>
                </div>

                <div class="form-group-modal">
                    <label>Correo Electrónico <span class="required">*</span></label>
                    <input type="email" name="correo" id="edit_correo" placeholder="usuario@empresa.com" maxlength="50" required>
                    <span class="field-error" id="edit_err_correo"></span>
                </div>

                <div class="form-group-modal">
                    <label>Nueva Contraseña <span style="color:#9ca3af; font-size:11px;">(dejar vacío para no cambiar)</span></label>
                    <div class="input-password-wrap">
                        <input type="password" name="password" id="edit_password" placeholder="Mínimo 6 caracteres (opcional)">
                        <i class='bx bx-show toggle-modal-password' id="toggleEditPass"></i>
                    </div>
                    <span class="field-error" id="edit_err_password"></span>
                </div>

                <div class="modal-footer-box">
                    <button type="button" class="btn-cancelar" onclick="cerrarModalEditar()">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-guardar" style="background:#d97706; box-shadow: 0 4px 12px rgba(217,119,6,0.25);">
                        <i class='bx bx-save'></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JS del Sidebar (hamburguesa) -->
    <script src="/Refaccionaria_Monterrey/VISTA/JS/Dashboard.js"></script>
    <script src="/Refaccionaria_Monterrey/VISTA/JS/Usuarios.js"></script>
</body>
</html>
