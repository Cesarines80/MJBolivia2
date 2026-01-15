<?php

/**
 * Clase para gestion de Eventos
 * Permite crear, editar, configurar y administrar eventos independientes
 */

class EventosManager
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Crear nuevo evento
     */
    public function create($data)
    {
        global $auth;

        // Generar codigo unico para el evento
        $codigoEvento = $this->generateEventCode();

        $stmt = $this->db->prepare("
            INSERT INTO eventos (
                titulo, descripcion, fecha_inicio, fecha_fin,
                fecha_inicio_inscripcion, fecha_fin_inscripcion,
                lugar, imagen_portada, estado, creado_por,
                costo_inscripcion, costo_alojamiento
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $result = $stmt->execute([
            $data['titulo'],
            $data['descripcion'] ?? '',
            $data['fecha_inicio'],
            $data['fecha_fin'],
            $data['fecha_inicio_inscripcion'],
            $data['fecha_fin_inscripcion'],
            $data['lugar'] ?? '',
            $data['imagen_portada'] ?? null,
            $data['estado'] ?? 'activo',
            $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null,
            $data['costo_inscripcion'] ?? 0,
            $data['costo_alojamiento'] ?? 0
        ]);

        if ($result) {
            $eventoId = $this->db->lastInsertId();

            // Configuracion por defecto
            $this->createDefaultConfig($eventoId);

            // Asignar creador como administrador del evento
            $this->assignAdmin($eventoId, $_SESSION['admin_id'] ?? $_SESSION['user_id'], $_SESSION['admin_id'] ?? $_SESSION['user_id']);

            // Log de actividad
            if ($auth) {
                $auth->logActivity($_SESSION['admin_id'] ?? $_SESSION['user_id'], $eventoId, 'evento_creado', 'Evento creado: ' . $data['titulo']);
            }

            return ['success' => true, 'evento_id' => $eventoId];
        }

        return ['success' => false, 'message' => 'Error al crear el evento'];
    }

    /**
     * Actualizar evento
     */
    public function update($eventoId, $data)
    {
        global $auth;

        // Verificar acceso al evento
        if (!$auth || !$auth->canAccessEvent($eventoId)) {
            return ['success' => false, 'message' => 'No tiene permisos para editar este evento'];
        }

        $stmt = $this->db->prepare("
            UPDATE eventos SET
                titulo = ?,
                descripcion = ?,
                fecha_inicio = ?,
                fecha_fin = ?,
                fecha_inicio_inscripcion = ?,
                fecha_fin_inscripcion = ?,
                lugar = ?,
                estado = ?,
                costo_inscripcion = ?,
                costo_alojamiento = ?
            WHERE id = ?
        ");

        $result = $stmt->execute([
            $data['titulo'],
            $data['descripcion'] ?? '',
            $data['fecha_inicio'],
            $data['fecha_fin'],
            $data['fecha_inicio_inscripcion'],
            $data['fecha_fin_inscripcion'],
            $data['lugar'] ?? '',
            $data['estado'] ?? 'activo',
            $data['costo_inscripcion'] ?? 0,
            $data['costo_alojamiento'] ?? 0,
            $eventoId
        ]);

        if ($result) {
            if ($auth) {
                $auth->logActivity($_SESSION['admin_id'] ?? $_SESSION['user_id'], $eventoId, 'evento_actualizado', 'Evento actualizado: ' . $data['titulo']);
            }
            return ['success' => true];
        }

        return ['success' => false, 'message' => 'Error al actualizar el evento'];
    }

    /**
     * Eliminar evento (solo super admin)
     */
    public function delete($eventoId)
    {
        global $auth;

        if (!$auth || !$auth->hasRole('super_admin')) {
            return ['success' => false, 'message' => 'Solo el Super Administrador puede eliminar eventos'];
        }

        // Verificar si hay inscripciones
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM inscripciones_eventos WHERE evento_id = ?");
        $stmt->execute([$eventoId]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            return ['success' => false, 'message' => 'No se puede eliminar el evento porque tiene inscripciones'];
        }

        // Log before delete to avoid foreign key issues
        $auth->logActivity($_SESSION['admin_id'] ?? $_SESSION['user_id'], $eventoId, 'evento_eliminado', 'Evento eliminado ID: ' . $eventoId);

        $stmt = $this->db->prepare("DELETE FROM eventos WHERE id = ?");
        $result = $stmt->execute([$eventoId]);

        if ($result) {
            return ['success' => true];
        }

        return ['success' => false, 'message' => 'Error al eliminar el evento'];
    }

    /**
     * Obtener evento por ID
     */
    public function getById($eventoId)
    {
        global $auth;

        if (!$auth || !$auth->canAccessEvent($eventoId)) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT e.*, u.nombre_completo as creador_nombre,
                   (SELECT COUNT(*) FROM inscripciones_eventos ie WHERE ie.evento_id = e.id) as total_inscritos,
                   (SELECT COUNT(*) FROM inscripciones_eventos ie WHERE ie.evento_id = e.id AND ie.sexo = 'Masculino') as total_hombres,
                   (SELECT COUNT(*) FROM inscripciones_eventos ie WHERE ie.evento_id = e.id AND ie.sexo = 'Femenino') as total_mujeres,
                   (SELECT COUNT(*) FROM inscripciones_eventos ie WHERE ie.evento_id = e.id AND ie.estado_pago = 'completo') as pagos_completos,
                   (SELECT COUNT(*) FROM inscripciones_eventos ie WHERE ie.evento_id = e.id AND ie.tipo_inscripcion = 'Beca') as total_becados,
                   (SELECT COUNT(*) FROM inscripciones_eventos ie WHERE ie.evento_id = e.id AND ie.alojamiento = 'Si') as con_alojamiento,
                   (SELECT SUM(ie.monto_pagado) FROM inscripciones_eventos ie WHERE ie.evento_id = e.id) as total_recaudado
            FROM eventos e
            LEFT JOIN usuarios u ON e.creado_por = u.id
            WHERE e.id = ?
            LIMIT 1
        ");
        $stmt->execute([$eventoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener todos los eventos accesibles
     */
    public function getAll()
    {
        global $auth;

        if (!$auth) {
            return [];
        }

        $events = $auth->getAccessibleEvents();

        // Agregar estadisticas adicionales
        foreach ($events as &$event) {
            $stats = $this->getEventStats($event['id']);
            $event = array_merge($event, $stats);
        }

        return $events;
    }

    /**
     * Obtener estadisticas de un evento
     */
    public function getEventStats($eventoId)
    {
        global $auth;

        if (!$auth || !$auth->canAccessEvent($eventoId)) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_inscritos,
                SUM(CASE WHEN sexo = 'Masculino' THEN 1 ELSE 0 END) as hombres,
                SUM(CASE WHEN sexo = 'Femenino' THEN 1 ELSE 0 END) as mujeres,
                SUM(CASE WHEN estado_pago = 'completo' THEN 1 ELSE 0 END) as pagos_completos,
                SUM(CASE WHEN estado_pago IN ('pendiente', 'parcial') THEN 1 ELSE 0 END) as deudores,
                SUM(CASE WHEN tipo_inscripcion = 'Beca' THEN 1 ELSE 0 END) as becados,
                SUM(CASE WHEN alojamiento = 'Si' THEN 1 ELSE 0 END) as con_alojamiento,
                SUM(monto_pagado) as total_recaudado,
                COUNT(DISTINCT grupo) as grupos_formados
            FROM inscripciones_eventos
            WHERE evento_id = ?
        ");
        $stmt->execute([$eventoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Configurar evento
     */
    public function configure($eventoId, $config)
    {
        global $auth;

        if (!$auth || !$auth->canAccessEvent($eventoId)) {
            return ['success' => false, 'message' => 'No tiene permisos para configurar este evento'];
        }

        $stmt = $this->db->prepare("
            INSERT INTO configuracion_eventos (
                evento_id, precio_base, precio_alojamiento, max_participantes,
                requiere_aprobacion, instrucciones_pago, campos_extra
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                precio_base = VALUES(precio_base),
                precio_alojamiento = VALUES(precio_alojamiento),
                max_participantes = VALUES(max_participantes),
                requiere_aprobacion = VALUES(requiere_aprobacion),
                instrucciones_pago = VALUES(instrucciones_pago),
                campos_extra = VALUES(campos_extra),
                fecha_actualizacion = NOW()
        ");

        $result = $stmt->execute([
            $eventoId,
            $config['precio_base'] ?? 0,
            $config['precio_alojamiento'] ?? 0,
            $config['max_participantes'] ?? 200,
            $config['requiere_aprobacion'] ?? false,
            $config['instrucciones_pago'] ?? '',
            isset($config['campos_extra']) ? json_encode($config['campos_extra']) : null
        ]);

        if ($result) {
            if ($auth) {
                $auth->logActivity($_SESSION['admin_id'] ?? $_SESSION['user_id'], $eventoId, 'evento_configurado', 'Evento configurado');
            }
            return ['success' => true];
        }

        return ['success' => false, 'message' => 'Error al configurar el evento'];
    }

    /**
     * Obtener configuracion de evento
     */
    public function getConfig($eventoId)
    {
        global $auth;

        if (!$auth || !$auth->canAccessEvent($eventoId)) {
            return $this->getDefaultConfig();
        }

        $stmt = $this->db->prepare("
            SELECT * FROM configuracion_eventos 
            WHERE evento_id = ? 
            LIMIT 1
        ");
        $stmt->execute([$eventoId]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$config) {
            return $this->getDefaultConfig();
        }

        // Decodificar campos extra
        if ($config['campos_extra']) {
            $config['campos_extra'] = json_decode($config['campos_extra'], true);
        }

        return $config;
    }

    /**
     * Asignar administrador a evento
     */
    public function assignAdmin($eventoId, $usuarioId, $asignadoPor)
    {
        $stmt = $this->db->prepare("
            INSERT INTO eventos_administradores (evento_id, usuario_id, asignado_por) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                activo = 1,
                fecha_asignacion = NOW()
        ");

        return $stmt->execute([$eventoId, $usuarioId, $asignadoPor]);
    }

    /**
     * Eliminar administrador de evento
     */
    public function removeAdmin($eventoId, $usuarioId)
    {
        $stmt = $this->db->prepare("
            UPDATE eventos_administradores 
            SET activo = 0 
            WHERE evento_id = ? AND usuario_id = ?
        ");

        return $stmt->execute([$eventoId, $usuarioId]);
    }

    /**
     * Obtener administradores de evento
     */
    public function getAdmins($eventoId)
    {
        global $auth;

        if (!$auth || !$auth->canAccessEvent($eventoId)) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT u.id, u.username, u.nombre_completo, u.email,
                   ea.fecha_asignacion, ua.nombre_completo as asignado_por_nombre
            FROM eventos_administradores ea
            INNER JOIN usuarios u ON ea.usuario_id = u.id
            LEFT JOIN usuarios ua ON ea.asignado_por = ua.id
            WHERE ea.evento_id = ? AND ea.activo = 1
            ORDER BY ea.fecha_asignacion ASC
        ");
        $stmt->execute([$eventoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verificar si el evento esta en periodo de inscripcion
     */
    public function isRegistrationOpen($eventoId)
    {
        $evento = $this->getById($eventoId);

        if (!$evento) {
            return false;
        }

        $today = date('Y-m-d');
        return ($today >= $evento['fecha_inicio_inscripcion'] &&
            $today <= $evento['fecha_fin_inscripcion'] &&
            $evento['estado'] === 'activo');
    }

    /**
     * Verificar si el evento ha alcanzado el maximo de participantes
     */
    public function isFull($eventoId)
    {
        $config = $this->getConfig($eventoId);
        $stats = $this->getEventStats($eventoId);

        return ($stats['total_inscritos'] >= $config['max_participantes']);
    }

    /**
     * Generar codigo unico de evento
     */
    private function generateEventCode()
    {
        return 'EVT' . date('Y') . strtoupper(substr(uniqid(), -6));
    }

    /**
     * Crear configuracion por defecto
     */
    private function createDefaultConfig($eventoId)
    {
        $defaultConfig = $this->getDefaultConfig();

        $stmt = $this->db->prepare("
            INSERT INTO configuracion_eventos (
                evento_id, precio_base, precio_alojamiento, max_participantes,
                requiere_aprobacion, instrucciones_pago
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $eventoId,
            $defaultConfig['precio_base'],
            $defaultConfig['precio_alojamiento'],
            $defaultConfig['max_participantes'],
            $defaultConfig['requiere_aprobacion'],
            $defaultConfig['instrucciones_pago']
        ]);
    }

    /**
     * Obtener configuracion por defecto
     */
    private function getDefaultConfig()
    {
        return [
            'precio_base' => 100.00,
            'precio_alojamiento' => 50.00,
            'max_participantes' => 200,
            'requiere_aprobacion' => false,
            'instrucciones_pago' => 'Realizar el pago en la cuenta bancaria proporcionada y enviar el comprobante.',
            'campos_extra' => []
        ];
    }

    /**
     * Obtener log de actividades del evento
     */
    public function getActivityLog($eventoId, $limit = 50)
    {
        global $auth;

        if (!$auth || !$auth->canAccessEvent($eventoId)) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT la.*, u.username, u.nombre_completo
            FROM log_actividades la
            LEFT JOIN usuarios u ON la.usuario_id = u.id
            WHERE la.evento_id = ?
            ORDER BY la.fecha_hora DESC
            LIMIT ?
        ");
        $stmt->execute([$eventoId, $limit]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

/**
 * Clase para gestionar inscripciones de eventos especificos
 */
class InscripcionesEvento
{
    private $db;
    private $eventoId;

    public function __construct($db, $eventoId)
    {
        $this->db = $db;
        $this->eventoId = $eventoId;
    }

    /**
     * Crear inscripcion
     */
    public function create($data)
    {
        global $auth;

        // Generar codigo de inscripcion
        $codigoInscripcion = $this->generateRegistrationCode();

        // Calcular montos
        $eventoManager = new EventosManager($this->db);
        $config = $eventoManager->getConfig($this->eventoId);

        $montoBase = $config['precio_base'];
        $montoAlojamiento = ($data['alojamiento'] === 'Si') ? $config['precio_alojamiento'] : 0;
        $montoTotal = $montoBase + $montoAlojamiento;

        // Determinar estado de pago
        $estadoPago = ($data['tipo_inscripcion'] === 'Beca') ? 'beca' : (
            ($data['monto_pagado'] >= $montoTotal) ? 'completo' : (
                ($data['monto_pagado'] > 0) ? 'parcial' : 'pendiente'));

        $stmt = $this->db->prepare("
            INSERT INTO inscripciones_eventos (
                evento_id, codigo_inscripcion, nombres, apellidos, email, telefono,
                fecha_nacimiento, iglesia, departamento, sexo, tipo_inscripcion,
                monto_pagado, codigo_pago, monto_total, alojamiento, estado_pago, aprobado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $aprobado = !$config['requiere_aprobacion'] || $data['tipo_inscripcion'] === 'Beca';

        $result = $stmt->execute([
            $this->eventoId,
            $codigoInscripcion,
            $data['nombres'],
            $data['apellidos'],
            $data['email'] ?? '',
            $data['telefono'] ?? '',
            $data['fecha_nacimiento'],
            $data['iglesia'] ?? '',
            $data['departamento'] ?? '',
            $data['sexo'],
            $data['tipo_inscripcion'],
            $data['monto_pagado'] ?? 0,
            $data['codigo_pago'] ?? null,
            $montoTotal,
            $data['alojamiento'] ?? 'No',
            $estadoPago,
            $aprobado
        ]);

        if ($result) {
            $inscripcionId = $this->db->lastInsertId();

            // Log de actividad
            if ($auth) {
                $auth->logActivity(
                    $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null,
                    $this->eventoId,
                    'inscripcion_creada',
                    'Inscripcion creada: ' . $codigoInscripcion
                );
            }

            return ['success' => true, 'inscripcion_id' => $inscripcionId, 'codigo' => $codigoInscripcion];
        }

        return ['success' => false, 'message' => 'Error al crear la inscripcion'];
    }

    /**
     * Obtener todas las inscripciones del evento
     */
    public function getAll()
    {
        global $auth;

        if (!$auth || !$auth->canAccessEvent($this->eventoId)) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT * FROM inscripciones_eventos 
            WHERE evento_id = ? 
            ORDER BY fecha_inscripcion DESC
        ");
        $stmt->execute([$this->eventoId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generar codigo de inscripcion unico
     */
    private function generateRegistrationCode()
    {
        return 'INS' . date('Y') . strtoupper(substr(uniqid(), -6));
    }

    /**
     * Obtener estadisticas del evento
     */
    public function getStats()
    {
        global $auth;

        if (!$auth || !$auth->canAccessEvent($this->eventoId)) {
            return [];
        }

        $eventoManager = new EventosManager($this->db);
        return $eventoManager->getEventStats($this->eventoId);
    }

    /**
     * Formar grupos aleatoriamente
     */
    public function formGroups($numeroGrupos)
    {
        global $auth;

        if (!$auth || !$auth->canAccessEvent($this->eventoId)) {
            return ['success' => false, 'message' => 'No tiene permisos para formar grupos'];
        }

        if ($numeroGrupos <= 0) {
            return ['success' => false, 'message' => 'Numero de grupos invalido'];
        }

        // Limpiar grupos anteriores de este evento
        $stmt = $this->db->prepare("UPDATE inscripciones_eventos SET grupo = NULL WHERE evento_id = ?");
        $stmt->execute([$this->eventoId]);

        // Obtener todos los inscritos del evento ordenados aleatoriamente
        $stmt = $this->db->prepare("
            SELECT id FROM inscripciones_eventos 
            WHERE evento_id = ? AND aprobado = 1
            ORDER BY RAND()
        ");
        $stmt->execute([$this->eventoId]);
        $inscritos = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $totalInscritos = count($inscritos);
        if ($totalInscritos == 0) {
            return ['success' => false, 'message' => 'No hay inscritos para formar grupos'];
        }

        // Distribuir inscritos en grupos de forma equitativa
        $grupoActual = 1;
        foreach ($inscritos as $inscritoId) {
            $stmt = $this->db->prepare("UPDATE inscripciones_eventos SET grupo = ? WHERE id = ?");
            $stmt->execute([$grupoActual, $inscritoId]);

            $grupoActual++;
            if ($grupoActual > $numeroGrupos) {
                $grupoActual = 1;
            }
        }

        // Log de actividad
        if ($auth) {
            $auth->logActivity(
                $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null,
                $this->eventoId,
                'grupos_formados',
                "Se formaron $numeroGrupos grupos con $totalInscritos participantes"
            );
        }

        return ['success' => true, 'message' => "Grupos formados exitosamente ($numeroGrupos grupos con $totalInscritos participantes)"];
    }

    /**
     * Obtener grupos formados
     */
    public function getGroups()
    {
        global $auth;

        if (!$auth || !$auth->canAccessEvent($this->eventoId)) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT 
                grupo,
                COUNT(*) as total_participantes,
                SUM(CASE WHEN sexo = 'Masculino' THEN 1 ELSE 0 END) as hombres,
                SUM(CASE WHEN sexo = 'Femenino' THEN 1 ELSE 0 END) as mujeres
            FROM inscripciones_eventos
            WHERE evento_id = ? AND grupo IS NOT NULL
            GROUP BY grupo
            ORDER BY grupo
        ");
        $stmt->execute([$this->eventoId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener inscritos por grupo
     */
    public function getInscritosByGroup($grupo)
    {
        global $auth;

        if (!$auth || !$auth->canAccessEvent($this->eventoId)) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT * FROM inscripciones_eventos
            WHERE evento_id = ? AND grupo = ?
            ORDER BY apellidos, nombres
        ");
        $stmt->execute([$this->eventoId, $grupo]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
