<?php
/**
 * Clase para manejar inscripciones
 */
class Inscripciones {
    public static function getAll($filters = []) {
        $db = getDB();
        $sql = "SELECT * FROM inscripciones WHERE 1=1";
        $params = [];
        
        if (isset($filters['sexo']) && $filters['sexo']) {
            $sql .= " AND sexo = ?";
            $params[] = $filters['sexo'];
        }
        
        if (isset($filters['tipo_inscripcion']) && $filters['tipo_inscripcion']) {
            $sql .= " AND tipo_inscripcion = ?";
            $params[] = $filters['tipo_inscripcion'];
        }
        
        if (isset($filters['estado_pago']) && $filters['estado_pago']) {
            $sql .= " AND estado_pago = ?";
            $params[] = $filters['estado_pago'];
        }
        
        if (isset($filters['grupo']) && $filters['grupo']) {
            $sql .= " AND grupo = ?";
            $params[] = $filters['grupo'];
        }
        
        $sql .= " ORDER BY fecha_inscripcion DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public static function getById($id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM inscripciones WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public static function create($data) {
        $db = getDB();
        
        // Obtener configuración de precios
        $config = self::getConfig();
        
        // Calcular montos
        $montoTotal = $config['monto_inscripcion'];
        if ($data['alojamiento'] === 'Si') {
            $montoTotal += $config['monto_alojamiento'];
        }
        
        // Determinar estado de pago
        $estadoPago = 'pendiente';
        if ($data['tipo_inscripcion'] === 'beca') {
            $estadoPago = 'beca';
            $data['monto_pagado'] = 0;
        } else {
            $data['monto_pagado'] = $montoTotal;
        }
        
        $sql = "INSERT INTO inscripciones (nombres, apellidos, fecha_nacimiento, iglesia, departamento, sexo, tipo_inscripcion, monto_pagado, monto_total, alojamiento, estado_pago) 
                VALUES (:nombres, :apellidos, :fecha_nacimiento, :iglesia, :departamento, :sexo, :tipo_inscripcion, :monto_pagado, :monto_total, :alojamiento, :estado_pago)";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            ':nombres' => $data['nombres'],
            ':apellidos' => $data['apellidos'],
            ':fecha_nacimiento' => $data['fecha_nacimiento'],
            ':iglesia' => $data['iglesia'] ?? null,
            ':departamento' => $data['departamento'] ?? null,
            ':sexo' => $data['sexo'],
            ':tipo_inscripcion' => $data['tipo_inscripcion'],
            ':monto_pagado' => $data['monto_pagado'] ?? $montoPagado,
            ':monto_total' => $montoTotal,
            ':alojamiento' => $data['alojamiento'],
            ':estado_pago' => $estadoPago
        ]);
        
        return $result ? $db->lastInsertId() : false;
    }
    
    public static function update($id, $data) {
        $db = getDB();
        $sql = "UPDATE inscripciones SET 
                nombres = :nombres, 
                apellidos = :apellidos, 
                fecha_nacimiento = :fecha_nacimiento, 
                iglesia = :iglesia, 
                departamento = :departamento, 
                sexo = :sexo, 
                tipo_inscripcion = :tipo_inscripcion, 
                monto_pagado = :monto_pagado, 
                monto_total = :monto_total, 
                alojamiento = :alojamiento, 
                estado_pago = :estado_pago,
                observaciones = :observaciones
                WHERE id = :id";
        
        $data['id'] = $id;
        $stmt = $db->prepare($sql);
        return $stmt->execute($data);
    }
    
    public static function delete($id) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM inscripciones WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public static function updatePaymentStatus($id, $estado, $monto = null) {
        $db = getDB();
        $sql = "UPDATE inscripciones SET estado_pago = ?";
        $params = [$estado];
        
        if ($monto !== null) {
            $sql .= ", monto_pagado = ?";
            $params[] = $monto;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public static function assignToGroup($id, $grupo) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE inscripciones SET grupo = ? WHERE id = ?");
        return $stmt->execute([$grupo, $id]);
    }
    
    public static function getStats() {
        $db = getDB();
        
        $stats = [];
        
        // Total inscritos
        $stats['total'] = $db->query("SELECT COUNT(*) FROM inscripciones WHERE estado = 'activo'")->fetchColumn();
        
        // Por sexo
        $stats['masculino'] = $db->query("SELECT COUNT(*) FROM inscripciones WHERE sexo = 'Masculino' AND estado = 'activo'")->fetchColumn();
        $stats['femenino'] = $db->query("SELECT COUNT(*) FROM inscripciones WHERE sexo = 'Femenino' AND estado = 'activo'")->fetchColumn();
        
        // Por tipo de inscripción
        $stmt = $db->query("SELECT tipo_inscripcion, COUNT(*) as total FROM inscripciones WHERE estado = 'activo' GROUP BY tipo_inscripcion");
        $stats['tipos'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Por estado de pago
        $stmt = $db->query("SELECT estado_pago, COUNT(*) as total FROM inscripciones WHERE estado = 'activo' GROUP BY estado_pago");
        $stats['estados_pago'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Por alojamiento
        $stats['con_alojamiento'] = $db->query("SELECT COUNT(*) FROM inscripciones WHERE alojamiento = 'Si' AND estado = 'activo'")->fetchColumn();
        $stats['sin_alojamiento'] = $db->query("SELECT COUNT(*) FROM inscripciones WHERE alojamiento = 'No' AND estado = 'activo'")->fetchColumn();
        
        // Recaudación total
        $stats['recaudacion_total'] = $db->query("SELECT SUM(monto_pagado) FROM inscripciones WHERE estado = 'activo'")->fetchColumn();
        
        // Deudores
        $stats['deudores'] = $db->query("SELECT COUNT(*) FROM inscripciones WHERE estado_pago = 'pendiente' AND estado = 'activo'")->fetchColumn();
        
        // Becas
        $stats['becas'] = $db->query("SELECT COUNT(*) FROM inscripciones WHERE tipo_inscripcion = 'beca' AND estado = 'activo'")->fetchColumn();
        
        return $stats;
    }
    
    public static function getConfig() {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM configuracion_inscripciones WHERE id = 1");
        return $stmt->fetch();
    }
    
    public static function updateConfig($data) {
        $db = getDB();
        $sql = "UPDATE configuracion_inscripciones SET 
                monto_inscripcion = :monto_inscripcion, 
                monto_alojamiento = :monto_alojamiento, 
                fecha_inicio = :fecha_inicio, 
                fecha_fin = :fecha_fin, 
                limite_inscripciones = :limite_inscripciones, 
                instrucciones_pago = :instrucciones_pago 
                WHERE id = 1";
        
        $data['id'] = 1;
        $stmt = $db->prepare($sql);
        return $stmt->execute($data);
    }
    
    public static function formGroups($numeroGrupos) {
        $db = getDB();
        
        // Limpiar grupos anteriores
        $db->query("UPDATE inscripciones SET grupo = NULL WHERE estado = 'activo'");
        $db->query("DELETE FROM grupos_inscripcion");
        
        // Obtener todos los inscritos
        $inscritos = $db->query("SELECT id FROM inscripciones WHERE estado = 'activo' ORDER BY RAND()");
        $inscritos = $inscritos->fetchAll(PDO::FETCH_COLUMN);
        
        $totalInscritos = count($inscritos);
        if ($totalInscritos == 0) return false;
        
        // Crear grupos
        $colores = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E2'];
        $gruposCreados = [];
        
        for ($i = 1; $i <= $numeroGrupos; $i++) {
            $color = $colores[($i - 1) % count($colores)];
            $stmt = $db->prepare("INSERT INTO grupos_inscripcion (numero_grupo, nombre_grupo, color) VALUES (?, ?, ?)");
            $stmt->execute([$i, "Grupo $i", $color]);
            $gruposCreados[$i] = [];
        }
        
        // Distribuir inscritos en grupos
        $grupoActual = 1;
        foreach ($inscritos as $inscritoId) {
            self::assignToGroup($inscritoId, $grupoActual);
            $gruposCreados[$grupoActual][] = $inscritoId;
            
            $grupoActual++;
            if ($grupoActual > $numeroGrupos) {
                $grupoActual = 1;
            }
        }
        
        // Actualizar contadores de grupos
        foreach ($gruposCreados as $grupoId => $miembros) {
            $total = count($miembros);
            $stmt = $db->prepare("UPDATE grupos_inscripcion SET total_participantes = ? WHERE numero_grupo = ?");
            $stmt->execute([$total, $grupoId]);
        }
        
        return true;
    }
    
    public static function getGroups() {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM grupos_inscripcion ORDER BY numero_grupo");
        return $stmt->fetchAll();
    }
    
    public static function getInscritosByGroup($grupo) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM inscripciones WHERE grupo = ? AND estado = 'activo' ORDER BY apellidos, nombres");
        $stmt->execute([$grupo]);
        return $stmt->fetchAll();
    }
    
    public static function getDeudores() {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM inscripciones WHERE estado_pago = 'pendiente' AND estado = 'activo' ORDER BY apellidos, nombres");
        return $stmt->fetchAll();
    }
    
    public static function getBecados() {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM inscripciones WHERE tipo_inscripcion = 'beca' AND estado = 'activo' ORDER BY apellidos, nombres");
        return $stmt->fetchAll();
    }
}
?>