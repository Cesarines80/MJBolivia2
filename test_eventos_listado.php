<?php
session_start();
$_SESSION['is_admin'] = true;
$_SESSION['admin_id'] = 1;
$_SESSION['admin_rol'] = 'superadmin';

require 'config/config.php';

echo "<h1>Prueba de Listado de Eventos</h1><hr>";

// 1. Verificar sesión
echo "<h2>1. Sesión Actual</h2>";
echo "<pre>";
print_r([
    'is_admin' => $_SESSION['is_admin'] ?? false,
    'admin_id' => $_SESSION['admin_id'] ?? null,
    'admin_rol' => $_SESSION['admin_rol'] ?? null
]);
echo "</pre>";

// 2. Verificar getCurrentUser
echo "<h2>2. Usuario Actual (getCurrentUser)</h2>";
global $auth;
if ($auth) {
    $user = $auth->getCurrentUser();
    echo "<pre>";
    print_r($user);
    echo "</pre>";
} else {
    echo "<p style='color:red;'>Auth no está inicializado</p>";
}

// 3. Verificar getAccessibleEvents
echo "<h2>3. Eventos Accesibles (getAccessibleEvents)</h2>";
if ($auth) {
    $events = $auth->getAccessibleEvents();
    echo "<p>Total eventos: " . count($events) . "</p>";
    echo "<pre>";
    print_r($events);
    echo "</pre>";
} else {
    echo "<p style='color:red;'>Auth no está inicializado</p>";
}

// 4. Verificar EventosManager->getAll()
echo "<h2>4. Eventos desde EventosManager</h2>";
try {
    $eventosManager = new EventosManager(getDB());
    $eventos = $eventosManager->getAll();
    echo "<p>Total eventos: " . count($eventos) . "</p>";

    if (count($eventos) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Fecha Inicio</th><th>Estado</th><th>Creador</th></tr>";
        foreach ($eventos as $e) {
            echo "<tr>";
            echo "<td>" . $e['id'] . "</td>";
            echo "<td>" . $e['nombre'] . "</td>";
            echo "<td>" . $e['fecha_inicio'] . "</td>";
            echo "<td>" . $e['estado'] . "</td>";
            echo "<td>" . ($e['creador_nombre'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:orange;'>No se encontraron eventos</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}

// 5. Verificar eventos en BD directamente
echo "<h2>5. Eventos en Base de Datos (Consulta Directa)</h2>";
$db = getDB();
$stmt = $db->query("SELECT id, nombre, fecha_inicio, fecha_fin, estado, creado_por FROM eventos");
$eventosDB = $stmt->fetchAll();
echo "<p>Total en BD: " . count($eventosDB) . "</p>";
echo "<pre>";
print_r($eventosDB);
echo "</pre>";
