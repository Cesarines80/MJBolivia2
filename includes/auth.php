<?php

/**
 * Sistema de Autenticacion con Roles
 * Permite gestionar usuarios con diferentes niveles de permisos
 */

class Auth
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Registrar un nuevo usuario
     */
    public function register($data)
    {
        // Verificar si el usuario ya existe
        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$data['username'], $data['email']]);

        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'El usuario o email ya existe'];
        }

        // Hash de la contraseña
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]);

        // Insertar usuario
        $stmt = $this->db->prepare("
            INSERT INTO usuarios (username, password, email, nombre_completo, rol, activo) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $result = $stmt->execute([
            $data['username'],
            $passwordHash,
            $data['email'],
            $data['nombre_completo'],
            $data['rol'] ?? 'usuario',
            $data['activo'] ?? true
        ]);

        if ($result) {
            $userId = $this->db->lastInsertId();
            $this->logActivity($userId, null, 'usuario_creado', 'Usuario creado: ' . $data['username']);
            return ['success' => true, 'user_id' => $userId];
        }

        return ['success' => false, 'message' => 'Error al crear el usuario'];
    }

    /**
     * Autenticar usuario
     */
    public function login($username, $password)
    {
        // Verificar intentos fallidos por IP
        if (!$this->checkLoginAttempts($_SERVER['REMOTE_ADDR'])) {
            return ['success' => false, 'message' => 'Demasiados intentos fallidos. Intente mas tarde.'];
        }

        // Buscar usuario
        $stmt = $this->db->prepare("
            SELECT id, username, password, email, nombre_completo, rol, activo, bloqueado_hasta 
            FROM usuarios 
            WHERE username = ? OR email = ? 
            LIMIT 1
        ");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $this->recordFailedLogin($_SERVER['REMOTE_ADDR'], $username);
            return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
        }

        // Limpiar bloqueos expirados automáticamente
        if ($user['bloqueado_hasta'] && strtotime($user['bloqueado_hasta']) <= time()) {
            $stmt = $this->db->prepare("
                UPDATE usuarios 
                SET bloqueado_hasta = NULL, intentos_fallidos = 0 
                WHERE id = ?
            ");
            $stmt->execute([$user['id']]);
            $user['bloqueado_hasta'] = null;
        }

        // Verificar si esta activo
        if (!$user['activo']) {
            return ['success' => false, 'message' => 'Usuario desactivado'];
        }

        // Verificar contraseña
        if (!password_verify($password, $user['password'])) {
            $this->recordFailedLogin($_SERVER['REMOTE_ADDR'], $username);
            return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
        }

        // Verificar si esta bloqueado DESPUÉS de validar la contraseña
        // Esto evita que usuarios con contraseña correcta sean bloqueados permanentemente
        if ($user['bloqueado_hasta'] && strtotime($user['bloqueado_hasta']) > time()) {
            // Excepción para usuario 'andres', 'superadmin' y rol 'usuario' - nunca bloquear
            if ($user['username'] !== 'andres' && $user['email'] !== 'andres@andres.com' && $user['username'] !== 'superadmin' && $user['email'] !== 'superadmin@sistema.com' && $user['rol'] !== 'usuario') {
                return ['success' => false, 'message' => 'Usuario bloqueado temporalmente'];
            }
        }

        // Actualizar ultimo acceso y limpiar bloqueos
        $stmt = $this->db->prepare("
            UPDATE usuarios 
            SET ultimo_acceso = NOW(), bloqueado_hasta = NULL, intentos_fallidos = 0 
            WHERE id = ?
        ");
        $stmt->execute([$user['id']]);

        // Limpiar intentos fallidos
        $this->clearFailedAttempts($_SERVER['REMOTE_ADDR'], $username);

        // Crear sesion
        $this->createSession($user);

        // Log de actividad
        $this->logActivity($user['id'], null, 'login', 'Inicio de sesion exitoso');

        return ['success' => true, 'user' => $user];
    }

    /**
     * Autenticar administrador (tabla administradores)
     */
    public function loginAdmin($email, $password)
    {
        // Verificar intentos fallidos
        if (!$this->checkLoginAttempts($_SERVER['REMOTE_ADDR'])) {
            return ['success' => false, 'message' => 'Demasiados intentos fallidos. Intente mas tarde.'];
        }

        // Buscar administrador
        $stmt = $this->db->prepare("
            SELECT id, nombre, email, password, rol, estado, ultimo_acceso, intentos_fallidos
            FROM administradores
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            $this->recordFailedLogin($_SERVER['REMOTE_ADDR'], $email);
            return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
        }

        // Verificar si esta activo
        if ($admin['estado'] !== 'activo') {
            return ['success' => false, 'message' => 'Usuario desactivado'];
        }

        // Verificar contraseña
        if (!password_verify($password, $admin['password'])) {
            $this->recordFailedLogin($_SERVER['REMOTE_ADDR'], $email);
            return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
        }

        // Actualizar ultimo acceso
        $stmt = $this->db->prepare("
            UPDATE administradores
            SET ultimo_acceso = NOW(), intentos_fallidos = 0
            WHERE id = ?
        ");
        $stmt->execute([$admin['id']]);

        // Limpiar intentos fallidos
        $this->clearFailedAttempts($_SERVER['REMOTE_ADDR'], $email);

        // Crear sesion (adaptado para administradores)
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nombre'] = $admin['nombre'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_rol'] = $admin['rol'];
        $_SESSION['is_admin'] = true;
        $_SESSION['last_activity'] = time();

        return ['success' => true, 'admin' => $admin];
    }

    /**
     * Cerrar sesion
     */
    public function logout()
    {
        if (isset($_SESSION['user_id'])) {
            $this->logActivity($_SESSION['user_id'], null, 'logout', 'Cierre de sesion');
        }

        session_destroy();

        // Limpiar cookie de sesion
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"]);
        }
    }

    /**
     * Verificar si el usuario esta autenticado
     */
    public function isLoggedIn()
    {
        // Verificar si es administrador
        if (isset($_SESSION['is_admin']) && isset($_SESSION['admin_id'])) {
            $stmt = $this->db->prepare("
                SELECT id FROM administradores
                WHERE id = ? AND estado = 'activo'
                LIMIT 1
            ");
            $stmt->execute([$_SESSION['admin_id']]);
            return $stmt->fetch() !== false;
        }

        // Verificar si es usuario del sistema de eventos
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        // Verificar que la sesion sea valida
        $stmt = $this->db->prepare("
            SELECT id, username, email, nombre_completo, rol, activo
            FROM usuarios
            WHERE id = ? AND activo = 1
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $this->logout();
            return false;
        }

        return true;
    }

    /**
     * Obtener usuario actual
     */
    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        // Si es administrador (tabla administradores)
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] && isset($_SESSION['admin_id'])) {
            $stmt = $this->db->prepare("
                SELECT id, nombre as nombre_completo, email, rol, estado as activo, fecha_creacion
                FROM administradores
                WHERE id = ?
                LIMIT 1
            ");
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin) {
                // Normalizar el campo activo
                $admin['activo'] = ($admin['activo'] === 'activo') ? 1 : 0;
                return $admin;
            }
        }

        // Si es usuario del sistema de eventos (tabla usuarios)
        if (isset($_SESSION['user_id'])) {
            $stmt = $this->db->prepare("
                SELECT id, username, email, nombre_completo, rol, activo, fecha_creacion
                FROM usuarios
                WHERE id = ?
                LIMIT 1
            ");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return null;
    }

    /**
     * Verificar si el usuario tiene un rol especifico
     */
    public function hasRole($role)
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $user = $this->getCurrentUser();
        return $user && $user['rol'] === $role;
    }

    /**
     * Verificar si el usuario tiene al menos uno de los roles especificados
     */
    public function hasAnyRole($roles)
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $user = $this->getCurrentUser();
        if (!$user) return false;

        return in_array($user['rol'], $roles);
    }

    /**
     * Verificar permisos especificos
     */
    public function hasPermission($resource, $action)
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $user = $this->getCurrentUser();
        if (!$user) return false;

        // Super admin tiene todos los permisos
        if ($user['rol'] === 'super_admin') {
            return true;
        }

        // Verificar permisos especificos
        $stmt = $this->db->prepare("
            SELECT id FROM permisos 
            WHERE rol = ? AND recurso = ? AND accion = ? 
            LIMIT 1
        ");
        $stmt->execute([$user['rol'], $resource, $action]);

        return $stmt->fetch() !== false;
    }

    /**
     * Verificar si el usuario puede acceder a un evento especifico
     */
    public function canAccessEvent($eventoId)
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $user = $this->getCurrentUser();
        if (!$user) return false;

        // Super admin puede acceder a todos los eventos
        if ($user['rol'] === 'super_admin') {
            return true;
        }

        // Admin y usuario pueden acceder solo a eventos asignados
        if ($user['rol'] === 'admin' || $user['rol'] === 'usuario') {
            $stmt = $this->db->prepare("
                SELECT id FROM eventos_administradores
                WHERE evento_id = ? AND usuario_id = ? AND activo = 1
                LIMIT 1
            ");
            $stmt->execute([$eventoId, $user['id']]);
            return $stmt->fetch() !== false;
        }

        // Usuario regular puede ver eventos activos
        $stmt = $this->db->prepare("
            SELECT id FROM eventos 
            WHERE id = ? AND estado = 'activo' 
            LIMIT 1
        ");
        $stmt->execute([$eventoId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Obtener eventos accesibles para el usuario actual
     */
    public function getAccessibleEvents()
    {
        if (!$this->isLoggedIn()) {
            return [];
        }

        $user = $this->getCurrentUser();
        if (!$user) return [];

        // Si es administrador del sistema (tabla administradores)
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
            // Los administradores ven todos los eventos
            $stmt = $this->db->query("
                SELECT e.*, 
                       COALESCE(u.nombre_completo, a.nombre, 'Sistema') as creador_nombre,
                       (SELECT COUNT(*) FROM inscripciones_eventos ie WHERE ie.evento_id = e.id) as total_inscritos
                FROM eventos e
                LEFT JOIN usuarios u ON e.creado_por = u.id
                LEFT JOIN administradores a ON e.creado_por = a.id
                ORDER BY e.fecha_creacion DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Para usuarios del sistema de eventos
        if ($user['rol'] === 'super_admin') {
            // Todos los eventos
            $stmt = $this->db->query("
                SELECT e.*, u.nombre_completo as creador_nombre,
                       (SELECT COUNT(*) FROM inscripciones_eventos ie WHERE ie.evento_id = e.id) as total_inscritos
                FROM eventos e
                LEFT JOIN usuarios u ON e.creado_por = u.id
                ORDER BY e.fecha_creacion DESC
            ");
        } elseif ($user['rol'] === 'admin' || $user['rol'] === 'usuario') {
            // Solo eventos asignados
            $stmt = $this->db->prepare("
                SELECT e.*, u.nombre_completo as creador_nombre,
                       (SELECT COUNT(*) FROM inscripciones_eventos ie WHERE ie.evento_id = e.id) as total_inscritos
                FROM eventos e
                LEFT JOIN usuarios u ON e.creado_por = u.id
                INNER JOIN eventos_administradores ea ON e.id = ea.evento_id
                WHERE ea.usuario_id = ? AND ea.activo = 1
                ORDER BY e.fecha_creacion DESC
            ");
            $stmt->execute([$user['id']]);
        } else {
            // Solo eventos activos
            $stmt = $this->db->query("
                SELECT e.*, u.nombre_completo as creador_nombre,
                       (SELECT COUNT(*) FROM inscripciones_eventos ie WHERE ie.evento_id = e.id) as total_inscritos
                FROM eventos e
                LEFT JOIN usuarios u ON e.creado_por = u.id
                WHERE e.estado = 'activo'
                ORDER BY e.fecha_creacion DESC
            ");
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crear sesion segura
     */
    private function createSession($user)
    {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['rol'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['nombre_completo'] = $user['nombre_completo'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['login_time'] = time();

        // Guardar sesion en base de datos
        $sessionId = session_id();
        $stmt = $this->db->prepare("
            INSERT INTO sesiones (id, usuario_id, ip_address, user_agent, datos) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                usuario_id = VALUES(usuario_id),
                ip_address = VALUES(ip_address),
                user_agent = VALUES(user_agent),
                datos = VALUES(datos),
                ultima_actividad = NOW()
        ");

        $sessionData = json_encode([
            'login_time' => time(),
            'role' => $user['rol']
        ]);

        $stmt->execute([
            $sessionId,
            $user['id'],
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $sessionData
        ]);
    }

    /**
     * Verificar intentos de login fallidos
     */
    private function checkLoginAttempts($ipAddress)
    {
        $stmt = $this->db->prepare("
            SELECT intentos, ultimo_intento, bloqueado_hasta
            FROM intentos_login
            WHERE ip_address = ? 
            AND bloqueado_hasta IS NOT NULL 
            AND bloqueado_hasta > NOW()
            LIMIT 1
        ");
        $stmt->execute([$ipAddress]);

        if ($attempt = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return false; // Esta bloqueado
        }

        return true;
    }

    /**
     * Registrar intento de login fallido
     */
    private function recordFailedLogin($ipAddress, $identifier)
    {
        // NO bloquear al usuario 'andres' ni 'superadmin' ni a usuarios con email 'andres@andres.com' o 'superadmin@sistema.com'
        if ($identifier === 'andres' || $identifier === 'andres@andres.com' || $identifier === 'superadmin' || $identifier === 'superadmin@sistema.com') {
            return; // Salir sin registrar el intento fallido
        }

        $stmt = $this->db->prepare("
            INSERT INTO intentos_login (ip_address, email, intentos)
            VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE
                intentos = intentos + 1,
                email = VALUES(email),
                ultimo_intento = NOW()
        ");
        $stmt->execute([$ipAddress, $identifier]);

        // Bloquear despues de 10 intentos (aumentado de 5 a 10)
        $stmt = $this->db->prepare("
            UPDATE intentos_login
            SET bloqueado_hasta = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
            WHERE ip_address = ? AND intentos >= 10
        ");
        $stmt->execute([$ipAddress]);

        // Tambien bloquear el usuario (tabla usuarios) - solo si no es admin ni usuario
        $stmt = $this->db->prepare("
            UPDATE usuarios
            SET bloqueado_hasta = DATE_ADD(NOW(), INTERVAL 15 MINUTE),
                intentos_fallidos = intentos_fallidos + 1
            WHERE (username = ? OR email = ?)
            AND rol NOT IN ('admin', 'usuario')
            AND username != 'andres'
            AND email != 'andres@andres.com'
        ");
        $stmt->execute([$identifier, $identifier]);

        // Tambien bloquear el administrador (tabla administradores)
        $stmt = $this->db->prepare("
            UPDATE administradores
            SET intentos_fallidos = intentos_fallidos + 1
            WHERE email = ?
        ");
        $stmt->execute([$identifier]);
    }

    /**
     * Limpiar intentos fallidos despues de login exitoso
     */
    private function clearFailedAttempts($ipAddress, $email)
    {
        $stmt = $this->db->prepare("
            DELETE FROM intentos_login
            WHERE ip_address = ? OR email = ?
        ");
        $stmt->execute([$ipAddress, $email]);
    }

    /**
     * Registrar actividad en el log
     */
    public function logActivity($userId, $eventoId, $action, $description = '')
    {
        $stmt = $this->db->prepare("
            INSERT INTO log_actividades (usuario_id, evento_id, accion, descripcion, ip_address) 
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $eventoId,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword($userId, $currentPassword, $newPassword)
    {
        // Verificar contraseña actual
        $stmt = $this->db->prepare("SELECT password FROM usuarios WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Contraseña actual incorrecta'];
        }

        // Actualizar contraseña
        $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);
        $stmt = $this->db->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $result = $stmt->execute([$newHash, $userId]);

        if ($result) {
            $this->logActivity($userId, null, 'cambio_password', 'Cambio de contraseña exitoso');
            return ['success' => true, 'message' => 'Contraseña actualizada exitosamente'];
        }

        return ['success' => false, 'message' => 'Error al actualizar la contraseña'];
    }

    /**
     * Resetear contraseña (solo super admin)
     */
    public function resetPassword($userId, $newPassword)
    {
        $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);
        $stmt = $this->db->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $result = $stmt->execute([$newHash, $userId]);

        if ($result) {
            $this->logActivity($userId, null, 'reset_password', 'Reseteo de contraseña por administrador');
            return ['success' => true];
        }

        return ['success' => false];
    }

    /**
     * Método estático para requerir login (redirige si no está autenticado)
     */
    public static function requireLogin()
    {
        global $auth;

        if (!$auth || !$auth->isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }

    /**
     * Método estático para obtener usuario actual (wrapper estático)
     */
    public static function getUser()
    {
        // Si es administrador
        if (isset($_SESSION['is_admin']) && isset($_SESSION['admin_id'])) {
            return [
                'id' => $_SESSION['admin_id'],
                'nombre' => $_SESSION['admin_nombre'] ?? 'Administrador',
                'email' => $_SESSION['admin_email'] ?? '',
                'rol' => $_SESSION['admin_rol'] ?? 'admin'
            ];
        }

        // Si es usuario del sistema de eventos
        if (isset($_SESSION['user_id'])) {
            return [
                'id' => $_SESSION['user_id'],
                'nombre' => $_SESSION['nombre_completo'] ?? 'Usuario',
                'email' => $_SESSION['email'] ?? '',
                'rol' => $_SESSION['rol'] ?? 'usuario'
            ];
        }

        return null;
    }

    /**
     * Método estático para verificar roles (wrapper estático)
     */
    public static function checkRole($roles)
    {
        $currentUser = self::getUser();

        if (!$currentUser) {
            return false;
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        return in_array($currentUser['rol'], $roles);
    }
}

/**
 * Funciones globales para facilitar el uso
 */

function initializeAuth($db)
{
    global $auth;
    $auth = new Auth($db);
}

function isLoggedIn()
{
    global $auth;
    return $auth ? $auth->isLoggedIn() : false;
}

function getCurrentUser()
{
    global $auth;
    return $auth ? $auth->getCurrentUser() : null;
}

function hasRole($role)
{
    global $auth;
    return $auth ? $auth->hasRole($role) : false;
}

function hasAnyRole($roles)
{
    global $auth;
    return $auth ? $auth->hasAnyRole($roles) : false;
}

function hasPermission($resource, $action)
{
    global $auth;
    return $auth ? $auth->hasPermission($resource, $action) : false;
}

function canAccessEvent($eventoId)
{
    global $auth;
    return $auth ? $auth->canAccessEvent($eventoId) : false;
}

function getAccessibleEvents()
{
    global $auth;
    return $auth ? $auth->getAccessibleEvents() : [];
}

function requireAuth()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireRole($role)
{
    requireAuth();
    if (!hasRole($role)) {
        header('HTTP/1.1 403 Forbidden');
        include('403.php');
        exit;
    }
}

function requireAnyRole($roles)
{
    requireAuth();
    if (!hasAnyRole($roles)) {
        header('HTTP/1.1 403 Forbidden');
        include('403.php');
        exit;
    }
}

function requirePermission($resource, $action)
{
    requireAuth();
    if (!hasPermission($resource, $action)) {
        header('HTTP/1.1 403 Forbidden');
        include('403.php');
        exit;
    }
}

function logout()
{
    global $auth;
    if ($auth) {
        $auth->logout();
    }
}
