<?php

require_once __DIR__ . '/Conexion.php';

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
     */
    public function getUsuarioByCorreo($correo)
    {
        $query = "SELECT id_usuario, correo, password, estado, nombre, apaterno, amaterno 
                  FROM tblusuarios 
                  WHERE correo = :correo LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':correo' => $correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
