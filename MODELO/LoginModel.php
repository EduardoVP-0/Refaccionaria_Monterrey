<?php

require_once __DIR__ . '/Conexion.php';
require_once __DIR__ . '/Encriptacion.php';

class LoginModel
{
    private $conn;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    /**
     * Busca un usuario por su correo electrónico.
     * 
     * Intento 1: buscar con el correo CIFRADO (usuarios nuevos).
     */
    public function getUsuarioByCorreo($correo)
    {
        $query = "SELECT id_usuario, correo, password, estado, nombre, apaterno, amaterno 
                  FROM tblusuarios 
                  WHERE correo = :correo LIMIT 1";
        $stmt = $this->conn->prepare($query);

        // --- Intento 1: correo cifrado ---
        $correo_cifrado = Encriptacion::encriptar($correo);
        $stmt->execute([':correo' => $correo_cifrado]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Encontrado cifrado → descifrar para la sesión
            $row['correo'] = Encriptacion::desencriptar($row['correo']);
            return $row;
        }
    }

    /**
     * Actualiza el token de recordar sesión del usuario.
     */
    public function updateRememberToken($id_usuario, $token)
    {
        $query = "UPDATE tblusuarios SET remember_token = :token WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':token' => $token,
            ':id' => $id_usuario
        ]);
    }

    /**
     * Busca un usuario usando su token de recordar sesión.
     */
    public function getUsuarioByToken($token)
    {
        $query = "SELECT id_usuario, correo, password, estado, nombre, apaterno, amaterno 
                  FROM tblusuarios 
                  WHERE remember_token = :token LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Descifrar el correo para que la sesión tenga el valor legible
        if ($row) {
            $row['correo'] = Encriptacion::desencriptar($row['correo']);
        }
        return $row;
    }
}
?>
