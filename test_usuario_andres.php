<?php
require_once 'config/config.php';

echo "<h1>Prueba de Sistema de Gestión de Usuarios - Usuario: Andres</h1>";
echo "<hr>";

$db = getDB();
$auth = new Auth($db);
$eventosManager = new EventosManager($db);

// Paso 1: Verificar si existe el usuario andres
echo "<h2>Paso 1: Verificar/Crear Usuario 'andres'</h2>";

$stmt = $db->prepare("SELECT * FROM usuarios WHERE username = 'andres' LIMIT 1");
$stmt->execute();
$usuarioAndres = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuarioAndres) {
    echo "✅ Usuario 'andres' ya existe<br>";
    echo "- ID: " . $usuarioAndres['id'] . "<br>";
    echo "- Email: " . $usuarioAndres['email'] . "<br>";
    echo "- Rol: " . $usuarioAndres['rol'] . "<br>";
    echo "- Estado: " . ($usuarioAndres['activo'] ? 'Activo' : 'Inactivo') . "<br>";
} else {
    echo "⚠️ Usuario 'andres' no existe. Creando...<br>";

    $result = $auth->register([
        'username' => 'andres',
        'email' => 'andres@ejemplo.com',
        'password' => 'andres123',
        'nombre_completo' => 'Andrés García',
        'rol' => 'admin',
        'activo' => 1
    ]);

    if ($result['success']) {
        echo "✅ Usuario 'andres' creado exitosamente<br>";
        echo "- ID: " . $result['user_id'] . "<br>";
        echo "- Username: andres<br>";
        echo "- Password: andres123<br>";
        echo "- Email: andres@ejemplo.com<br>";
        echo "- Rol: admin<br>";

        // Obtener el usuario recién creado
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ? LIMIT 1");
        $stmt->execute([$result['user_id']]);
        $usuarioAndres = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        echo "❌ Error al crear usuario: " . $result['message'] . "<br>";
        exit;
    }
}

echo "<hr>";

// Paso 2: Verificar eventos existentes
echo "<h2>Paso 2: Verificar Eventos Disponibles</h2>";

$stmt = $db->query("SELECT * FROM eventos ORDER BY fecha_creacion DESC LIMIT 5");
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($eventos)) {
    echo "⚠️ No hay eventos en el sistema. Creando evento de prueba...<br>";

    // Crear evento de prueba
    $eventoData = [
        'nombre' => 'Evento de Prueba para Andres',
        'descripcion' => 'Este es un evento de prueba para verificar la asignación de usuarios',
        'fecha_inicio' => date('Y-m-d', strtotime('+30 days')),
        'fecha_fin' => date('Y-m-d', strtotime('+32 days')),
        'fecha_inicio_inscripcion' => date('Y-m-d'),
        'fecha_fin_inscripcion' => date('Y-m-d', strtotime('+25 days')),
        'lugar' => 'Centro de Convenciones',
        'estado' => 'activo'
    ];

    $resultEvento = $eventosManager->create($eventoData);

    if ($resultEvento['success']) {
        echo "✅ Evento de prueba creado exitosamente<br>";
        $eventoId = $resultEvento['evento_id'];

        // Obtener el evento
        $stmt = $db->prepare("SELECT * FROM eventos WHERE id = ?");
        $stmt->execute([$eventoId]);
        $eventos = [$stmt->fetch(PDO::FETCH_ASSOC)];
    } else {
        echo "❌ Error al crear evento de prueba<br>";
        exit;
    }
}

echo "📋 Eventos disponibles:<br>";
foreach ($eventos as $evento) {
    echo "- ID: {$evento['id']} | {$evento['nombre']} | Estado: {$evento['estado']}<br>";
}

$eventoParaPrueba = $eventos[0];
echo "<br>✅ Usando evento: <strong>{$eventoParaPrueba['nombre']}</strong> (ID: {$eventoParaPrueba['id']})<br>";

echo "<hr>";

// Paso 3: Verificar asignaciones actuales del usuario
echo "<h2>Paso 3: Verificar Asignaciones Actuales</h2>";

$stmt = $db->prepare("
    SELECT ea.*, e.nombre as evento_nombre
    FROM eventos_administradores ea
    INNER JOIN eventos e ON ea.evento_id = e.id
    WHERE ea.usuario_id = ? AND ea.activo = 1
");
$stmt->execute([$usuarioAndres['id']]);
$asignacionesActuales = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($asignacionesActuales)) {
    echo "⚠️ El usuario 'andres' no tiene eventos asignados<br>";
} else {
    echo "📋 Eventos asignados actualmente:<br>";
    foreach ($asignacionesActuales as $asignacion) {
        echo "- {$asignacion['evento_nombre']} (ID: {$asignacion['evento_id']})<br>";
    }
}

echo "<hr>";

// Paso 4: Asignar evento al usuario
echo "<h2>Paso 4: Asignar Evento al Usuario</h2>";

// Verificar si ya está asignado
$stmt = $db->prepare("
    SELECT * FROM eventos_administradores 
    WHERE evento_id = ? AND usuario_id = ? AND activo = 1
");
$stmt->execute([$eventoParaPrueba['id'], $usuarioAndres['id']]);
$yaAsignado = $stmt->fetch();

if ($yaAsignado) {
    echo "ℹ️ El evento ya está asignado al usuario<br>";
} else {
    echo "🔄 Asignando evento al usuario...<br>";

    // Obtener ID del super admin para asignar
    $stmt = $db->query("SELECT id FROM usuarios WHERE rol = 'super_admin' LIMIT 1");
    $superAdmin = $stmt->fetch();

    if (!$superAdmin) {
        // Si no hay super_admin en usuarios, usar el ID del admin de la tabla administradores
        $stmt = $db->query("SELECT id FROM administradores WHERE rol = 'superadmin' LIMIT 1");
        $superAdmin = $stmt->fetch();
    }

    $asignadoPor = $superAdmin ? $superAdmin['id'] : $usuarioAndres['id'];

    $result = $eventosManager->assignAdmin(
        $eventoParaPrueba['id'],
        $usuarioAndres['id'],
        $asignadoPor
    );

    if ($result) {
        echo "✅ Evento asignado exitosamente<br>";
    } else {
        echo "❌ Error al asignar evento<br>";
    }
}

echo "<hr>";

// Paso 5: Verificar la asignación
echo "<h2>Paso 5: Verificar Asignación</h2>";

$stmt = $db->prepare("
    SELECT ea.*, e.nombre as evento_nombre, e.estado,
           u.nombre_completo as asignado_por_nombre
    FROM eventos_administradores ea
    INNER JOIN eventos e ON ea.evento_id = e.id
    LEFT JOIN usuarios u ON ea.asignado_por = u.id
    WHERE ea.usuario_id = ? AND ea.activo = 1
");
$stmt->execute([$usuarioAndres['id']]);
$asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($asignaciones)) {
    echo "❌ No se encontraron asignaciones para el usuario<br>";
} else {
    echo "✅ Asignaciones verificadas:<br>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Evento</th><th>Estado</th><th>Fecha Asignación</th><th>Asignado Por</th></tr>";
    foreach ($asignaciones as $asignacion) {
        echo "<tr>";
        echo "<td>{$asignacion['evento_nombre']}</td>";
        echo "<td>{$asignacion['estado']}</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($asignacion['fecha_asignacion'])) . "</td>";
        echo "<td>" . ($asignacion['asignado_por_nombre'] ?? 'Sistema') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";

// Paso 6: Probar autenticación
echo "<h2>Paso 6: Probar Autenticación del Usuario</h2>";

$loginResult = $auth->login('andres', 'andres123');

if ($loginResult['success']) {
    echo "✅ Login exitoso<br>";
    echo "- Usuario: " . $loginResult['user']['username'] . "<br>";
    echo "- Rol: " . $loginResult['user']['rol'] . "<br>";
    echo "- Email: " . $loginResult['user']['email'] . "<br>";
} else {
    echo "❌ Error en login: " . $loginResult['message'] . "<br>";
}

echo "<hr>";

// Paso 7: Verificar acceso a eventos
echo "<h2>Paso 7: Verificar Acceso a Eventos</h2>";

// Simular sesión del usuario
$_SESSION['user_id'] = $usuarioAndres['id'];
$_SESSION['username'] = $usuarioAndres['username'];
$_SESSION['user_role'] = $usuarioAndres['rol'];

$eventosAccesibles = $auth->getAccessibleEvents();

echo "📋 Eventos accesibles para el usuario 'andres':<br>";
if (empty($eventosAccesibles)) {
    echo "⚠️ El usuario no tiene acceso a ningún evento<br>";
} else {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Estado</th><th>Fecha Inicio</th><th>Inscritos</th></tr>";
    foreach ($eventosAccesibles as $evento) {
        echo "<tr>";
        echo "<td>{$evento['id']}</td>";
        echo "<td>{$evento['nombre']}</td>";
        echo "<td>{$evento['estado']}</td>";
        echo "<td>" . date('d/m/Y', strtotime($evento['fecha_inicio'])) . "</td>";
        echo "<td>" . ($evento['total_inscritos'] ?? 0) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Limpiar sesión
session_destroy();

echo "<hr>";

// Resumen final
echo "<h2>✅ Resumen de la Prueba</h2>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
echo "<h3>Credenciales del Usuario de Prueba:</h3>";
echo "<ul>";
echo "<li><strong>Usuario:</strong> andres</li>";
echo "<li><strong>Contraseña:</strong> andres123</li>";
echo "<li><strong>Email:</strong> andres@ejemplo.com</li>";
echo "<li><strong>Rol:</strong> admin</li>";
echo "<li><strong>Eventos Asignados:</strong> " . count($asignaciones) . "</li>";
echo "</ul>";
echo "</div>";

echo "<br>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; border: 1px solid #bee5eb;'>";
echo "<h3>Próximos Pasos para Probar:</h3>";
echo "<ol>";
echo "<li>Ir a: <a href='admin/login.php' target='_blank'>admin/login.php</a></li>";
echo "<li>Iniciar sesión con: <strong>andres</strong> / <strong>andres123</strong></li>";
echo "<li>Verificar que solo ve los eventos asignados en 'Mis Eventos'</li>";
echo "<li>Intentar acceder a 'Gestión de Usuarios' (debe ser denegado)</li>";
echo "<li>Verificar que puede gestionar inscripciones de sus eventos</li>";
echo "</ol>";
echo "</div>";

echo "<br>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border: 1px solid #ffeaa7;'>";
echo "<h3>⚠️ Nota Importante:</h3>";
echo "<p>Si deseas probar con el super admin, usa las credenciales del administrador principal de la tabla 'administradores'.</p>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>Prueba completada - " . date('d/m/Y H:i:s') . "</p>";
