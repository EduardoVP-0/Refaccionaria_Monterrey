<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Refaccionaria Monterrey</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- BoxIcons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Hoja de estilos de Login -->
    <link rel="stylesheet" href="/Refaccionaria_Monterrey/VISTA/CSS/Login.css">
</head>
<body>

    <div class="login-container">
        <div class="login-card">
            <!-- Icono Superior y Títulos de la Empresa -->
            <div class="brand-icon-wrapper">
                <i class='bx bx-wrench'></i>
            </div>
            <h1 class="brand-title">Refaccionaria Monterrey</h1>
            <p class="brand-subtitle">Sistema de Gestión Empresarial</p>

            <!-- Títulos del Formulario -->
            <h2 class="login-title">Bienvenido de vuelta</h2>
            <p class="login-subtitle">Ingresa tus credenciales para acceder al sistema</p>

            <!-- Manejo de Errores -->
            <?php 
            // Iniciar sesión solo para obtener posibles errores si venimos del controlador con problemas de redisección o usando la variable global
            // Como este archivo puede ser incluido directo o llamado, verificamos si hay variable $error.
            // Para mantener MVC puro, el controlador carga la vista, pero si el usuario entra directo a Login.php...
            // Lo ideal es que el form apunte a LoginController.php.
            // Si hay error en sesión, lo mostramos.
            session_start();
            if (isset($_SESSION['login_error'])) {
                echo '<div style="background: #FEE2E2; color: #991B1B; padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 13px;">';
                echo htmlspecialchars($_SESSION['login_error']);
                echo '</div>';
                unset($_SESSION['login_error']); // Limpiar error
            }
            ?>

            <!-- Formulario apuntando al Controlador -->
            <form class="login-form" action="/Refaccionaria_Monterrey/CONTROLADOR/LoginController.php" method="POST">
                
                <div class="form-group">
                    <label for="correo">Correo electrónico</label>
                    <div class="input-icon">
                        <i class='bx bx-envelope'></i>
                        <input type="email" id="correo" name="correo" placeholder="usuario@empresa.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-icon">
                        <i class='bx bx-lock-alt'></i>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <i class='bx bx-show toggle-password' id="togglePassword"></i>
                    </div>
                </div>

                <div class="options-row">
                    <div class="checkbox-group">
                        <input type="checkbox" id="recordar" name="recordar">
                        <label for="recordar">Recordar sesión</label>
                    </div>
                    <div class="forgot-password">
                        <a href="#">¿Olvidaste tu contraseña?</a>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    Iniciar sesión
                </button>

                <div class="security-text">
                    <i class='bx bx-shield-quarter'></i> Conexión segura y encriptada
                </div>
            </form>
        </div>
    </div>

    <!-- Script para mostrar/ocultar contraseña -->
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            // Cambiar icono
            this.classList.toggle('bx-show');
            this.classList.toggle('bx-hide');
        });
    </script>
</body>
</html>
