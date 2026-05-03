<?php
session_start();
require_once __DIR__ . '/../MODELO/LoginModel.php';

$error = '';
$modelo = new LoginModel();

// 1. Verificar si hay un token de "Recordar sesión" al abrir la página y no hay sesión activa
if (!isset($_SESSION['usuario']) && isset($_COOKIE['remember_token_refac'])) {
    $token = $_COOKIE['remember_token_refac'];
    $usuarioCookie = $modelo->getUsuarioByToken($token);
    
    if ($usuarioCookie && ($usuarioCookie['estado'] == 't' || $usuarioCookie['estado'] == true)) {
        iniciarSesion($usuarioCookie);
        header('Location: /Refaccionaria_Monterrey/VISTA/Reportes.php');
        exit();
    } else {
        // Token inválido o usuario desactivado, eliminar cookie
        setcookie('remember_token_refac', '', time() - 3600, '/');
    }
}

// 2. Si ya hay sesión iniciada manualmente, redirigir
if (isset($_SESSION['usuario'])) {
    header('Location: /Refaccionaria_Monterrey/VISTA/Reportes.php');
    exit();
}

// 3. Procesar formulario de Login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];
    $recordar = isset($_POST['recordar']) ? true : false;

    if (empty($correo) || empty($password)) {
        $_SESSION['login_error'] = 'Por favor, complete todos los campos.';
        header('Location: /Refaccionaria_Monterrey/VISTA/Login.php');
        exit();
    } else {
        $usuario = $modelo->getUsuarioByCorreo($correo);

        if ($usuario) {
            if ($usuario['estado'] == 't' || $usuario['estado'] == true) {
                // --- Validación de Contraseña ---
                // Paso 1: Intentar validar como contraseña encriptada (Recomendado)
                $acceso_valido = password_verify($password, $usuario['password']);

                // Paso 2: Fallback para contraseñas en texto plano
                if (!$acceso_valido && $password === $usuario['password']) {
                    $acceso_valido = true;
                }

                if ($acceso_valido) {
                    // Si el usuario marcó "Recordar sesión"
                    if ($recordar) {
                        // Generar token seguro de 64 caracteres
                        $token = bin2hex(random_bytes(32));
                        $modelo->updateRememberToken($usuario['id_usuario'], $token);
                        // Crear cookie por 30 días
                        setcookie('remember_token_refac', $token, time() + (86400 * 30), '/');
                    }

                    // Iniciar sesión
                    iniciarSesion($usuario);

                    // Redireccionar a dashboard
                    header('Location: /Refaccionaria_Monterrey/VISTA/Reportes.php');
                    exit();
                } else {
                    $_SESSION['login_error'] = 'Correo o contraseña incorrectos.';
                    header('Location: /Refaccionaria_Monterrey/VISTA/Login.php');
                    exit();
                }
            } else {
                $_SESSION['login_error'] = 'Su cuenta se encuentra desactivada. Contacte al administrador.';
                header('Location: /Refaccionaria_Monterrey/VISTA/Login.php');
                exit();
            }
        } else {
            $_SESSION['login_error'] = 'Correo o contraseña incorrectos.';
            header('Location: /Refaccionaria_Monterrey/VISTA/Login.php');
            exit();
        }
    }
}

function iniciarSesion($usuario) {
    $_SESSION['usuario'] = [
        'id_usuario' => $usuario['id_usuario'],
        'correo' => $usuario['correo'],
        'nombre' => $usuario['nombre'],
        'apaterno' => $usuario['apaterno'],
        'amaterno' => $usuario['amaterno']
    ];
}
?>
