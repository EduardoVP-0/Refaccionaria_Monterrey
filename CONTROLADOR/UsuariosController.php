<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: /Refaccionaria_Monterrey/index.php?p=login');
    exit();
}

require_once __DIR__ . '/../MODELO/UsuariosModel.php';

$model = new UsuariosModel();
$respuesta = ['ok' => false, 'msg' => 'Acción no válida.'];

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

switch ($accion) {

    // ============ AGREGAR USUARIO ============
    case 'agregar':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre   = trim($_POST['nombre'] ?? '');
            $apaterno = trim($_POST['apaterno'] ?? '');
            $amaterno = trim($_POST['amaterno'] ?? '');
            $correo   = trim($_POST['correo'] ?? '');
            $password = $_POST['password'] ?? '';

            // Validaciones del lado del servidor
            if (empty($nombre) || empty($apaterno) || empty($correo) || empty($password)) {
                $_SESSION['error'] = 'Todos los campos obligatorios deben estar completos.';
            } elseif (strlen($nombre) > 35) {
                $_SESSION['error'] = 'El nombre no puede superar los 35 caracteres.';
            } elseif (strlen($apaterno) > 35) {
                $_SESSION['error'] = 'El apellido paterno no puede superar los 35 caracteres.';
            } elseif (strlen($amaterno) > 35) {
                $_SESSION['error'] = 'El apellido materno no puede superar los 35 caracteres.';
            } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'El correo electrónico no tiene un formato válido.';
            } elseif (strlen($correo) > 50) {
                $_SESSION['error'] = 'El correo no puede superar los 50 caracteres.';
            } elseif (strlen($password) < 6) {
                $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres.';
            } else {
                $resultado = $model->insertarUsuario([
                    'nombre'   => $nombre,
                    'apaterno' => $apaterno,
                    'amaterno' => $amaterno,
                    'correo'   => $correo,
                    'password' => $password
                ]);

                if ($resultado['ok']) {
                    $_SESSION['success'] = $resultado['msg'];
                } else {
                    $_SESSION['error'] = $resultado['msg'];
                }
            }
        }
        header('Location: /Refaccionaria_Monterrey/index.php?p=usuarios');
        exit();

    // ============ CAMBIAR ESTADO ============
    case 'cambiar_estado':
        $id_usuario   = $_GET['id'] ?? '';
        $nuevo_estado = ($_GET['estado'] ?? '0') == '1';

        if (empty($id_usuario)) {
            $_SESSION['error'] = 'No se pudo identificar al usuario.';
        } else {
            $resultado = $model->cambiarEstado($id_usuario, $nuevo_estado);
            if ($resultado) {
                $_SESSION['success'] = $nuevo_estado ? 'Usuario habilitado correctamente.' : 'Usuario deshabilitado correctamente.';
            } else {
                $_SESSION['error'] = 'Error al cambiar el estado del usuario.';
            }
        }
        header('Location: /Refaccionaria_Monterrey/index.php?p=usuarios');
        exit();

    // ============ EDITAR USUARIO ============
    case 'editar':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_usuario = trim($_POST['id_usuario'] ?? '');
            $nombre     = trim($_POST['nombre'] ?? '');
            $apaterno   = trim($_POST['apaterno'] ?? '');
            $amaterno   = trim($_POST['amaterno'] ?? '');
            $correo     = trim($_POST['correo'] ?? '');
            $password   = $_POST['password'] ?? '';

            if (empty($id_usuario) || empty($nombre) || empty($apaterno) || empty($correo)) {
                $_SESSION['error'] = 'Todos los campos obligatorios deben estar completos.';
            } elseif (strlen($nombre) > 35) {
                $_SESSION['error'] = 'El nombre no puede superar los 35 caracteres.';
            } elseif (strlen($apaterno) > 35) {
                $_SESSION['error'] = 'El apellido paterno no puede superar los 35 caracteres.';
            } elseif (strlen($amaterno) > 35) {
                $_SESSION['error'] = 'El apellido materno no puede superar los 35 caracteres.';
            } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'El correo electrónico no tiene un formato válido.';
            } elseif (strlen($correo) > 50) {
                $_SESSION['error'] = 'El correo no puede superar los 50 caracteres.';
            } elseif (!empty($password) && strlen($password) < 6) {
                $_SESSION['error'] = 'Si cambias la contraseña, debe tener al menos 6 caracteres.';
            } else {
                $resultado = $model->actualizarUsuario([
                    'id_usuario' => $id_usuario,
                    'nombre'     => $nombre,
                    'apaterno'   => $apaterno,
                    'amaterno'   => $amaterno,
                    'correo'     => $correo,
                    'password'   => $password  // vacío = no cambiar
                ]);

                if ($resultado['ok']) {
                    $_SESSION['success'] = $resultado['msg'];
                } else {
                    $_SESSION['error'] = $resultado['msg'];
                }
            }
        }
        header('Location: /Refaccionaria_Monterrey/index.php?p=usuarios');
        exit();

    // ============ OBTENER DATOS DE UN USUARIO (AJAX para rellenar modal) ============
    case 'get_usuario':
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['ok' => false, 'msg' => 'ID no válido.']);
            exit();
        }
        $usuario = $model->getUsuarioById($id);
        if ($usuario) {
            echo json_encode(['ok' => true, 'usuario' => $usuario]);
        } else {
            echo json_encode(['ok' => false, 'msg' => 'Usuario no encontrado.']);
        }
        exit();

    default:
        header('Location: /Refaccionaria_Monterrey/index.php?p=usuarios');
        exit();
}
?>
