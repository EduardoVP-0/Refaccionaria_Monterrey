<?php

require_once __DIR__ . '/Conexion.php';
require_once __DIR__ . '/Encriptacion.php';

class UsuariosModel
{
    private $conn;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    // ============================================
    // MÉTRICAS PARA TARJETAS
    // ============================================

    public function getTotalUsuarios()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM tblusuarios");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getTotalActivos()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM tblusuarios WHERE estado = true");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getTotalInactivos()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM tblusuarios WHERE estado = false");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // ============================================
    // LISTAR USUARIOS (con búsqueda y paginación)
    // ============================================

    public function getUsuariosPaginados($limite, $offset, $busqueda = '')
    {
        $sql = "SELECT id_usuario, nombre, apaterno, amaterno, correo, estado
                FROM tblusuarios";

        if (!empty($busqueda)) {
            // Solo busca por nombre y apellidos (el correo está cifrado, no es buscable parcialmente)
            $sql .= " WHERE (nombre ILIKE :busqueda OR apaterno ILIKE :busqueda OR amaterno ILIKE :busqueda)";
        }

        $sql .= " ORDER BY id_usuario ASC LIMIT :limite OFFSET :offset";

        $stmt = $this->conn->prepare($sql);

        if (!empty($busqueda)) {
            $termino = '%' . $busqueda . '%';
            $stmt->bindValue(':busqueda', $termino, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Descifrar correo para mostrarlo legible en la vista
        foreach ($rows as &$row) {
            $row['correo'] = Encriptacion::desencriptar($row['correo']);
        }
        return $rows;
    }

    public function getTotalUsuariosFiltrado($busqueda = '')
    {
        $sql = "SELECT COUNT(*) AS total FROM tblusuarios";

        if (!empty($busqueda)) {
            $sql .= " WHERE (nombre ILIKE :busqueda OR apaterno ILIKE :busqueda OR amaterno ILIKE :busqueda)";
        }

        $stmt = $this->conn->prepare($sql);

        if (!empty($busqueda)) {
            $termino = '%' . $busqueda . '%';
            $stmt->bindValue(':busqueda', $termino, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // ============================================
    // OBTENER UN USUARIO POR ID
    // ============================================

    public function getUsuarioById($id_usuario)
    {
        $stmt = $this->conn->prepare(
            "SELECT id_usuario, nombre, apaterno, amaterno, correo, estado 
             FROM tblusuarios WHERE id_usuario = :id"
        );
        $stmt->execute([':id' => $id_usuario]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Descifrar correo para mostrar en formulario de edición
        if ($row) {
            $row['correo'] = Encriptacion::desencriptar($row['correo']);
        }
        return $row;
    }

    // ============================================
    // AGREGAR USUARIO
    // ============================================

    public function insertarUsuario($data)
    {
        // Cifrar el correo antes de buscar duplicados
        $correo_cifrado = Encriptacion::encriptar($data['correo']);

        // Verificar que el correo no exista ya (comparando en forma cifrada)
        $stmtCheck = $this->conn->prepare("SELECT COUNT(*) FROM tblusuarios WHERE correo = :correo");
        $stmtCheck->execute([':correo' => $correo_cifrado]);
        if ($stmtCheck->fetchColumn() > 0) {
            return ['ok' => false, 'msg' => 'El correo electrónico ya está registrado.'];
        }

        // Generar ID secuencial: USE-XXXXX
        $stmtMax = $this->conn->prepare("SELECT id_usuario FROM tblusuarios WHERE id_usuario LIKE 'USE-%' ORDER BY id_usuario DESC LIMIT 1");
        $stmtMax->execute();
        $ultimo_id = $stmtMax->fetchColumn();

        if ($ultimo_id) {
            // Extraer el número del último ID (ej. USE-00005 -> 5)
            $numero = (int)str_replace('USE-', '', $ultimo_id);
            $numero++;
        } else {
            // Si no hay usuarios con este formato, empezar desde 1
            $numero = 1;
        }
        
        // Formatear el nuevo ID con 5 dígitos, rellenando con ceros a la izquierda (ej. USE-00006)
        $id_usuario = 'USE-' . str_pad($numero, 5, '0', STR_PAD_LEFT);

        // Hashear contraseña (bcrypt, irreversible)
        $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);

        $stmt = $this->conn->prepare(
            "INSERT INTO tblusuarios (id_usuario, correo, password, nombre, apaterno, amaterno, estado)
             VALUES (:id, :correo, :password, :nombre, :apaterno, :amaterno, true)"
        );

        $result = $stmt->execute([
            ':id'       => $id_usuario,
            ':correo'   => $correo_cifrado,       // Guardado cifrado
            ':password' => $password_hash,         // Guardado hasheado (bcrypt)
            ':nombre'   => $data['nombre'],
            ':apaterno' => $data['apaterno'],
            ':amaterno' => $data['amaterno'] ?? null
        ]);

        return ['ok' => $result, 'msg' => $result ? 'Usuario registrado correctamente.' : 'Error al registrar el usuario.'];
    }

    // ============================================
    // CAMBIAR ESTADO (Habilitar / Inhabilitar)
    // ============================================

    public function cambiarEstado($id_usuario, $nuevo_estado)
    {
        $stmt = $this->conn->prepare(
            "UPDATE tblusuarios SET estado = :estado WHERE id_usuario = :id"
        );
        return $stmt->execute([
            ':estado' => $nuevo_estado ? 'true' : 'false',
            ':id'     => $id_usuario
        ]);
    }

    // ============================================
    // ACTUALIZAR USUARIO (Editar)
    // ============================================

    public function actualizarUsuario($data)
    {
        // Cifrar el correo nuevo para comparar en la BD
        $correo_cifrado = Encriptacion::encriptar($data['correo']);

        // Verificar que el correo cifrado no lo use otro usuario diferente
        $stmtCheck = $this->conn->prepare(
            "SELECT COUNT(*) FROM tblusuarios WHERE correo = :correo AND id_usuario != :id"
        );
        $stmtCheck->execute([':correo' => $correo_cifrado, ':id' => $data['id_usuario']]);
        if ($stmtCheck->fetchColumn() > 0) {
            return ['ok' => false, 'msg' => 'Ese correo ya pertenece a otro usuario.'];
        }

        // Si viene contraseña nueva, hashearla; si no, no tocarla
        if (!empty($data['password'])) {
            $stmt = $this->conn->prepare(
                "UPDATE tblusuarios
                 SET nombre = :nombre, apaterno = :apaterno, amaterno = :amaterno,
                     correo = :correo, password = :password
                 WHERE id_usuario = :id"
            );
            $params = [
                ':nombre'   => $data['nombre'],
                ':apaterno' => $data['apaterno'],
                ':amaterno' => $data['amaterno'] ?? null,
                ':correo'   => $correo_cifrado,                              // Guardado cifrado
                ':password' => password_hash($data['password'], PASSWORD_BCRYPT), // Nuevo hash bcrypt
                ':id'       => $data['id_usuario']
            ];
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE tblusuarios
                 SET nombre = :nombre, apaterno = :apaterno, amaterno = :amaterno, correo = :correo
                 WHERE id_usuario = :id"
            );
            $params = [
                ':nombre'   => $data['nombre'],
                ':apaterno' => $data['apaterno'],
                ':amaterno' => $data['amaterno'] ?? null,
                ':correo'   => $correo_cifrado,   // Guardado cifrado
                ':id'       => $data['id_usuario']
            ];
        }

        $result = $stmt->execute($params);
        return ['ok' => $result, 'msg' => $result ? 'Usuario actualizado correctamente.' : 'Error al actualizar el usuario.'];
    }
}
?>
