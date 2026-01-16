<?php

/**
 * Funciones principales del sistema
 */
require_once __DIR__ . '/../config/config.php';

/**
 * Clase para manejar la configuración del sitio
 */
class SiteConfig
{
    private static $config = null;

    public static function get()
    {
        if (self::$config === null) {
            $db = getDB();
            $stmt = $db->query("SELECT * FROM configuracion WHERE id = 1");
            self::$config = $stmt->fetch();
        }
        return self::$config;
    }

    public static function update($data)
    {
        $db = getDB();
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) return false;

        $sql = "UPDATE configuracion SET " . implode(', ', $fields) . " WHERE id = 1";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }
}

/**
 * Clase para manejar el carrusel
 */
class Carrusel
{
    public static function getAll($onlyActive = true)
    {
        $db = getDB();
        $sql = "SELECT * FROM carrusel";
        if ($onlyActive) {
            $sql .= " WHERE estado = 'activo'";
        }
        $sql .= " ORDER BY orden ASC";

        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    public static function getById($id)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM carrusel WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data)
    {
        $db = getDB();
        $sql = "INSERT INTO carrusel (titulo, descripcion, imagen, tipo, url, orden, estado) 
                VALUES (:titulo, :descripcion, :imagen, :tipo, :url, :orden, :estado)";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':titulo' => $data['titulo'],
            ':descripcion' => $data['descripcion'] ?? null,
            ':imagen' => $data['imagen'],
            ':tipo' => $data['tipo'] ?? 'imagen',
            ':url' => $data['url'] ?? null,
            ':orden' => $data['orden'] ?? 0,
            ':estado' => $data['estado'] ?? 'activo'
        ]);
    }

    public static function update($id, $data)
    {
        $db = getDB();
        $sql = "UPDATE carrusel SET 
                titulo = :titulo, 
                descripcion = :descripcion, 
                imagen = :imagen, 
                tipo = :tipo, 
                url = :url, 
                orden = :orden, 
                estado = :estado 
                WHERE id = :id";

        $data['id'] = $id;
        $stmt = $db->prepare($sql);
        return $stmt->execute($data);
    }

    public static function delete($id)
    {
        error_log("CARRUSEL DELETE: Starting deletion of ID $id");

        $db = getDB();
        $item = self::getById($id);

        if (!$item) {
            error_log("CARRUSEL DELETE: Item not found for ID $id");
            return false;
        }

        error_log("CARRUSEL DELETE: Found item: " . json_encode($item));

        if ($item && $item['imagen']) {
            $filePath = UPLOADS_DIR . $item['imagen'];
            error_log("CARRUSEL DELETE: Checking file: $filePath");
            if (file_exists($filePath)) {
                $deleted = unlink($filePath);
                error_log("CARRUSEL DELETE: File deletion result: " . ($deleted ? 'SUCCESS' : 'FAILED'));
            } else {
                error_log("CARRUSEL DELETE: File does not exist: $filePath");
            }
        }

        $stmt = $db->prepare("DELETE FROM carrusel WHERE id = ?");
        $result = $stmt->execute([$id]);
        error_log("CARRUSEL DELETE: Database deletion result: " . ($result ? 'SUCCESS' : 'FAILED'));

        if (!$result) {
            $error = $stmt->errorInfo();
            error_log("CARRUSEL DELETE: Database error: " . json_encode($error));
        }

        return $result;
    }

    public static function updateOrder($orders)
    {
        $db = getDB();
        $stmt = $db->prepare("UPDATE carrusel SET orden = ? WHERE id = ?");

        foreach ($orders as $id => $orden) {
            $stmt->execute([$orden, $id]);
        }
        return true;
    }
}

/**
 * Clase para manejar eventos
 */
class Eventos
{
    public static function getAll($limit = null)
    {
        $db = getDB();
        $sql = "SELECT * FROM eventos ORDER BY fecha_evento ASC, hora_evento ASC";
        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    public static function getUpcoming($limit = 6)
    {
        $db = getDB();
        $sql = "SELECT
                    id,
                    titulo,
                    descripcion,
                    fecha_evento,
                    hora_evento,
                    lugar,
                    imagen_portada as imagen,
                    estado
                FROM eventos
                WHERE fecha_evento >= CURDATE() AND estado = 'activo'
                ORDER BY fecha_evento ASC
                LIMIT $limit";

        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    public static function getById($id)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM eventos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data)
    {
        $db = getDB();
        $sql = "INSERT INTO eventos (titulo, descripcion, fecha_evento, hora_evento, lugar, imagen, estado, destacado) 
                VALUES (:titulo, :descripcion, :fecha_evento, :hora_evento, :lugar, :imagen, :estado, :destacado)";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':titulo' => $data['titulo'],
            ':descripcion' => $data['descripcion'],
            ':fecha_evento' => $data['fecha_evento'],
            ':hora_evento' => $data['hora_evento'] ?? null,
            ':lugar' => $data['lugar'] ?? null,
            ':imagen' => $data['imagen'] ?? null,
            ':estado' => $data['estado'] ?? 'activo',
            ':destacado' => $data['destacado'] ?? 'no'
        ]);
    }

    public static function update($id, $data)
    {
        $db = getDB();
        $sql = "UPDATE eventos SET 
                titulo = :titulo, 
                descripcion = :descripcion, 
                fecha_evento = :fecha_evento, 
                hora_evento = :hora_evento, 
                lugar = :lugar, 
                imagen = :imagen, 
                estado = :estado, 
                destacado = :destacado 
                WHERE id = :id";

        $data['id'] = $id;
        $stmt = $db->prepare($sql);
        return $stmt->execute($data);
    }

    public static function delete($id)
    {
        $db = getDB();
        $item = self::getById($id);

        if ($item && $item['imagen']) {
            $filePath = UPLOADS_DIR . $item['imagen'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $stmt = $db->prepare("DELETE FROM eventos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

/**
 * Clase para manejar la galería
 */
class Galeria
{
    public static function getAll($categoria = null)
    {
        $db = getDB();
        $sql = "SELECT * FROM galeria";
        $params = [];

        if ($categoria) {
            $sql .= " WHERE categoria = ?";
            $params[] = $categoria;
        }

        $sql .= " ORDER BY fecha_creacion DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getById($id)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM galeria WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getCategorias()
    {
        $db = getDB();
        $stmt = $db->query("SELECT DISTINCT categoria FROM galeria ORDER BY categoria");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function create($data)
    {
        $db = getDB();
        $sql = "INSERT INTO galeria (titulo, descripcion, imagen, categoria) 
                VALUES (:titulo, :descripcion, :imagen, :categoria)";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':titulo' => $data['titulo'],
            ':descripcion' => $data['descripcion'] ?? null,
            ':imagen' => $data['imagen'],
            ':categoria' => $data['categoria'] ?? 'general'
        ]);
    }

    public static function update($id, $data)
    {
        $db = getDB();
        $sql = "UPDATE galeria SET 
                titulo = :titulo, 
                descripcion = :descripcion, 
                imagen = :imagen, 
                categoria = :categoria 
                WHERE id = :id";

        $data['id'] = $id;
        $stmt = $db->prepare($sql);
        return $stmt->execute($data);
    }

    public static function delete($id)
    {
        error_log("GALERIA DELETE: Starting deletion of ID $id");

        $db = getDB();
        $item = self::getById($id);

        if (!$item) {
            error_log("GALERIA DELETE: Item not found for ID $id");
            return false;
        }

        error_log("GALERIA DELETE: Found item: " . json_encode($item));

        if ($item && $item['imagen']) {
            $filePath = UPLOADS_DIR . $item['imagen'];
            error_log("GALERIA DELETE: Checking file: $filePath");
            if (file_exists($filePath)) {
                $deleted = unlink($filePath);
                error_log("GALERIA DELETE: File deletion result: " . ($deleted ? 'SUCCESS' : 'FAILED'));
            } else {
                error_log("GALERIA DELETE: File does not exist: $filePath");
            }
        }

        $stmt = $db->prepare("DELETE FROM galeria WHERE id = ?");
        $result = $stmt->execute([$id]);
        error_log("GALERIA DELETE: Database deletion result: " . ($result ? 'SUCCESS' : 'FAILED'));

        if (!$result) {
            $error = $stmt->errorInfo();
            error_log("GALERIA DELETE: Database error: " . json_encode($error));
        }

        return $result;
    }
}

/**
 * Clase para manejar Misión y Visión
 */
class MisionVision
{
    public static function getAll()
    {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM mision_vision ORDER BY tipo ASC");
        return $stmt->fetchAll();
    }

    public static function getByTipo($tipo)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM mision_vision WHERE tipo = ? AND estado = 'activo'");
        $stmt->execute([$tipo]);
        return $stmt->fetch();
    }

    public static function getById($id)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM mision_vision WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function update($id, $data)
    {
        $db = getDB();

        // Construir la consulta dinámicamente según los campos presentes
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['titulo'])) {
            $fields[] = "titulo = :titulo";
            $params[':titulo'] = $data['titulo'];
        }

        if (isset($data['contenido'])) {
            $fields[] = "contenido = :contenido";
            $params[':contenido'] = $data['contenido'];
        }

        if (isset($data['imagen'])) {
            $fields[] = "imagen = :imagen";
            $params[':imagen'] = $data['imagen'];
        }

        if (isset($data['estado'])) {
            $fields[] = "estado = :estado";
            $params[':estado'] = $data['estado'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE mision_vision SET " . implode(', ', $fields) . " WHERE id = :id";

        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }
}

/**
 * Clase para manejar contactos
 */
class Contactos
{
    public static function getAll($estado = null)
    {
        $db = getDB();
        $sql = "SELECT * FROM contactos";
        $params = [];

        if ($estado) {
            $sql .= " WHERE estado = ?";
            $params[] = $estado;
        }

        $sql .= " ORDER BY fecha_creacion DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getById($id)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM contactos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data)
    {
        $db = getDB();
        $sql = "INSERT INTO contactos (nombre, email, telefono, asunto, mensaje) 
                VALUES (:nombre, :email, :telefono, :asunto, :mensaje)";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':email' => $data['email'],
            ':telefono' => $data['telefono'] ?? null,
            ':asunto' => $data['asunto'],
            ':mensaje' => $data['mensaje']
        ]);
    }

    public static function updateStatus($id, $estado)
    {
        $db = getDB();
        $stmt = $db->prepare("UPDATE contactos SET estado = ? WHERE id = ?");
        return $stmt->execute([$estado, $id]);
    }

    public static function delete($id)
    {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM contactos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getCountByStatus()
    {
        $db = getDB();
        $stmt = $db->query("SELECT estado, COUNT(*) as total FROM contactos GROUP BY estado");
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}

/**
 * Función global para registrar actividad
 * Wrapper para el método logActivity de la clase Auth
 */
function logActivity($action, $description = '')
{
    global $auth;

    // Obtener ID del usuario actual
    $userId = null;
    if (isset($_SESSION['admin_id'])) {
        $userId = $_SESSION['admin_id'];
    } elseif (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }

    // Si hay instancia de Auth, usar su método
    if ($auth && method_exists($auth, 'logActivity')) {
        $auth->logActivity($userId, null, $action, $description);
    } else {
        // Registrar directamente en la base de datos
        try {
            $db = getDB();
            $stmt = $db->prepare("
                INSERT INTO log_actividades (usuario_id, evento_id, accion, descripcion, fecha)
                VALUES (?, NULL, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $action, $description]);
        } catch (Exception $e) {
            // Silenciosamente fallar si no se puede registrar
            error_log("Error al registrar actividad: " . $e->getMessage());
        }
    }
}
