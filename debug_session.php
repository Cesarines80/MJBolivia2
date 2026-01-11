<?php
session_start();
require 'config/config.php';

echo "<h1>Debug de Sesión - Admin Eventos</h1><hr>";

// 1. Variables de sesión
echo "<h2>1. Variables de Sesión</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// 2. Verificar isLoggedIn
echo "<h2>2. isLoggedIn()</h2>";
global $auth;
if ($auth) {
    $isLogged = $auth->isLoggedIn();
    echo "<p>Resultado: " . ($isLogged ? "✅ TRUE" : "❌ FALSE") . "</p>";
} else {
    echo "<p style='color:red;'>❌ Auth no inicializado</p>";
}

// 3. Verificar getCurrentUser
echo "<h2>3. getCurrentUser()</h2>";
if ($auth) {
    $user = $auth->getCurrentUser();
    if ($user) {
        echo "<p>✅ Usuario encontrado:</p>";
        echo "<pre>";
        print_r($user);
        echo "</pre>";
    } else {
        echo "<p style='color:red;'>❌ getCurrentUser() retornó NULL</p>";
    }
} else {
    echo "<p style='color:red;'>❌ Auth no inicializado</p>";
}

// 4. Verificar getAccessibleEvents
echo "<h2>4. getAccessibleEvents()</h2>";
if ($auth) {
    $events = $auth->getAccessibleEvents();
    echo "<p>Total eventos: " . count($events) . "</p>";
    if (count($events) > 0) {
        echo "<p>✅ Eventos encontrados:</p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Creador</th></tr>";
        foreach ($events as $e) {
            echo "<tr>";
            echo "<td>" . $e['id'] . "</td>";
            echo "<td>" . $e['nombre'] . "</td>";
            echo "<td>" . ($e['creador_nombre'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:orange;'>⚠️ No se encontraron eventos</p>";
    }
} else {
    echo "<p style='color:red;'>❌ Auth no inicializado</p>";
}

// 5. Verificar EventosManager->getAll()
echo "<h2>5. EventosManager->getAll()</h2>";
try {
    $eventosManager = new EventosManager(getDB());
    $eventos = $eventosManager->getAll();
    echo "<p>Total eventos: " . count($eventos) . "</p>";

    if (count($eventos) > 0) {
        echo "<p>✅ Eventos desde EventosManager:</p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Fecha Inicio</th><th>Estado</th></tr>";
        foreach ($eventos as $e) {
            echo "<tr>";
            echo "<td>" . $e['id'] . "</td>";
            echo "<td>" . $e['nombre'] . "</td>";
            echo "<td>" . $e['fecha_inicio'] . "</td>";
            echo "<td>" . $e['estado'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:orange;'>⚠️ EventosManager->getAll() retornó array vacío</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// 6. Consulta directa a BD
echo "<h2>6. Consulta Directa a BD</h2>";
$db = getDB();
$stmt = $db->query("SELECT id, nombre, fecha_inicio, estado, creado_por FROM eventos");
$eventosDB = $stmt->fetchAll();
echo "<p>Total en BD: " . count($eventosDB) . "</p>";
if (count($eventosDB) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Estado</th><th>Creado Por</th></tr>";
    foreach ($eventosDB as $e) {
        echo "<tr>";
        echo "<td>" . $e['id'] . "</td>";
        echo "<td>" . $e['nombre'] . "</td>";
        echo "<td>" . $e['estado'] . "</td>";
        echo "<td>" . $e['creado_por'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<h2>Instrucciones</h2>";
echo "<p>1. Asegúrate de estar logueado en el panel admin</p>";
echo "<p>2. Si no estás logueado, ve a: <a href='admin/login.php'>admin/login.php</a></p>";
echo "<p>3. Luego vuelve a esta página</p>";
echo "<p>4. Comparte los resultados de las secciones 1-5</p>";
