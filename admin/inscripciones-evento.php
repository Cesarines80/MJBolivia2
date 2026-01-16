<?php
require_once __DIR__ . '/../config/config.php';

// Verificar autenticación
Auth::requireLogin();

// Verificar que tenga rol de admin o super_admin o usuario
if (!Auth::checkRole(['superadmin', 'admin', 'super_admin', 'usuario'])) {
    header('HTTP/1.0 403 Forbidden');
    die('Acceso denegado. No tienes permisos para acceder a esta página.');
}

// Obtener ID del evento
$eventoId = isset($_GET['evento']) ? intval($_GET['evento']) : 0;

if ($eventoId <= 0) {
    $_SESSION['error'] = 'Evento no especificado';
    header('Location: mis-eventos.php');
    exit;
}

$db = getDB();
$eventosManager = new EventosManager($db);
$inscripcionesEvento = new InscripcionesEvento($db, $eventoId);

// Verificar acceso al evento (simplificado por ahora)
// TODO: Implementar verificación de acceso por evento

// Obtener evento y sus estadisticas
$evento = $eventosManager->getById($eventoId);
$config = $eventosManager->getConfig($eventoId);
$stats = $inscripcionesEvento->getStats();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Token CSRF invalido';
        header('Location: inscripciones-evento.php?evento=' . $eventoId);
        exit;
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'crear_inscripcion':
            $data = [
                'nombres' => cleanInput($_POST['nombres']),
                'apellidos' => cleanInput($_POST['apellidos']),
                'email' => cleanInput($_POST['email']),
                'telefono' => cleanInput($_POST['telefono']),
                'fecha_nacimiento' => $_POST['fecha_nacimiento'],
                'iglesia' => cleanInput($_POST['iglesia']),
                'departamento' => cleanInput($_POST['departamento']),
                'sexo' => $_POST['sexo'],
                'tipo_inscripcion' => $_POST['tipo_inscripcion'],
                'monto_pagado' => floatval($_POST['monto_pagado']),
                'alojamiento' => $_POST['alojamiento'],
                'codigo_pago' => isset($_POST['codigo_pago']) ? cleanInput($_POST['codigo_pago']) : null
            ];

            $result = $inscripcionesEvento->create($data);
            if ($result['success']) {
                $_SESSION['success'] = 'Inscripcion creada exitosamente. Codigo: ' . $result['codigo'];
            } else {
                $_SESSION['error'] = $result['message'] ?? 'Error al crear la inscripcion';
            }
            break;

        case 'formar_grupos':
            $numeroGrupos = intval($_POST['numero_grupos']);
            if ($numeroGrupos > 0) {
                $result = $inscripcionesEvento->formGroups($numeroGrupos);
                if ($result['success']) {
                    $_SESSION['success'] = $result['message'];
                } else {
                    $_SESSION['error'] = $result['message'];
                }
            } else {
                $_SESSION['error'] = 'Numero de grupos invalido';
            }
            break;
    }

    header('Location: inscripciones-evento.php?evento=' . $eventoId);
    exit;
}

// Obtener inscripciones
$inscripciones = $inscripcionesEvento->getAll();

// Obtener mensajes de sesion
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscripciones - <?php echo htmlspecialchars($evento['nombre']); ?> | <?php echo SITE_NAME; ?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">

    <style>
        :root {
            --color-primario: #8B7EC8;
            --color-secundario: #B8B3D8;
            --color-acento: #6B5B95;
        }

        .btn-primary {
            background-color: var(--color-primario);
            border-color: var(--color-primario);
        }

        .btn-primary:hover {
            background-color: var(--color-acento);
            border-color: var(--color-acento);
        }

        .card-header {
            background-color: var(--color-secundario);
            color: var(--color-acento);
        }

        .info-box {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .inscripcion-pendiente {
            background-color: #fff3cd;
        }

        .inscripcion-completa {
            background-color: #d4edda;
        }

        .inscripcion-beca {
            background-color: #cce5ff;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="mis-eventos.php" class="nav-link">Mis Eventos</a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="inscripciones-evento.php?evento=<?php echo $eventoId; ?>" class="nav-link">
                        <?php echo htmlspecialchars($evento['nombre']); ?>
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php" role="button">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesion
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="index.php" class="brand-link">
                <span class="brand-text font-weight-light"><?php echo SITE_NAME; ?></span>
            </a>

            <div class="sidebar">
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="info">
                        <?php $currentUser = Auth::getUser(); ?>
                        <a href="#" class="d-block">
                            <i
                                class="fas fa-user-<?php echo Auth::checkRole(['super_admin', 'superadmin']) ? 'shield text-success' : 'cog text-warning'; ?>"></i>
                            <?php echo htmlspecialchars($currentUser['nombre'] ?? 'Usuario'); ?>
                        </a>
                        <small class="text-muted"><?php echo ucfirst($currentUser['rol'] ?? 'Usuario'); ?></small>
                    </div>
                </div>

                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <?php if (Auth::checkRole(['super_admin', 'superadmin'])): ?>
                            <li class="nav-item">
                                <a href="dashboard.php" class="nav-link">
                                    <i class="nav-icon fas fa-tachometer-alt"></i>
                                    <p>Dashboard</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="eventos.php" class="nav-link">
                                    <i class="nav-icon fas fa-calendar-alt"></i>
                                    <p>Gestion de Eventos</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="usuarios.php" class="nav-link">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Gestion de Usuarios</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (Auth::checkRole(['super_admin', 'superadmin', 'admin'])): ?>
                            <li class="nav-item">
                                <a href="mis-eventos.php" class="nav-link">
                                    <i class="nav-icon fas fa-calendar-check"></i>
                                    <p>Mis Eventos</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (Auth::checkRole(['super_admin', 'superadmin', 'admin'])): ?>
                            <li class="nav-item">
                                <a href="configuracion.php" class="nav-link">
                                    <i class="nav-icon fas fa-cogs"></i>
                                    <p>Configuracion</p>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">
                                <small class="text-muted">Evento:</small>
                                <?php echo htmlspecialchars($evento['nombre']); ?>
                            </h1>
                            <p class="text-muted">
                                <i class="fas fa-calendar"></i>
                                Inscripciones: <?php echo formatDate($evento['fecha_inicio_inscripcion']); ?> -
                                <?php echo formatDate($evento['fecha_fin_inscripcion']); ?>
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="mis-eventos.php">Mis Eventos</a></li>
                                <li class="breadcrumb-item active"><?php echo htmlspecialchars($evento['nombre']); ?>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="container-fluid">
                    <!-- Mensajes -->
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Estadisticas del Evento -->
                    <div class="row mb-4">
                        <div class="col-lg-2 col-6">
                            <div class="info-box bg-gradient-primary">
                                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Inscritos</span>
                                    <span class="info-box-number"><?php echo $stats['total_inscritos'] ?? 0; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="info-box bg-gradient-success">
                                <span class="info-box-icon"><i class="fas fa-male"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Hombres</span>
                                    <span class="info-box-number"><?php echo $stats['hombres'] ?? 0; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="info-box bg-gradient-warning">
                                <span class="info-box-icon"><i class="fas fa-female"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Mujeres</span>
                                    <span class="info-box-number"><?php echo $stats['mujeres'] ?? 0; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="info-box bg-gradient-info">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Pagos Completos</span>
                                    <span class="info-box-number"><?php echo $stats['pagos_completos'] ?? 0; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="info-box bg-gradient-danger">
                                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Deudores</span>
                                    <span class="info-box-number"><?php echo $stats['deudores'] ?? 0; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="info-box bg-gradient-dark">
                                <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Recaudado</span>
                                    <span class="info-box-number">Bs.
                                        <?php echo number_format($stats['total_recaudado'] ?? 0, 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Acciones</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <button type="button" class="btn btn-primary btn-block" data-toggle="modal"
                                        data-target="#modalCrearInscripcion">
                                        <i class="fas fa-plus"></i> Nueva Inscripcion
                                    </button>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <button type="button" class="btn btn-warning btn-block" data-toggle="modal"
                                        data-target="#modalFormarGrupos">
                                        <i class="fas fa-users-cog"></i> Formar Grupos
                                    </button>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="reportes-evento.php?evento=<?php echo $eventoId; ?>" target="_blank"
                                        class="btn btn-info btn-block">
                                        <i class="fas fa-file-pdf"></i> Ver Reportes
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="<?php echo EVENTOS_URL; ?>inscribir.php?evento=<?php echo $eventoId; ?>"
                                        target="_blank" class="btn btn-success btn-block">
                                        <i class="fas fa-external-link-alt"></i> Formulario Publico
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Inscripciones -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Lista de Inscritos</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tablaInscripciones" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Codigo</th>
                                            <th>Nombres</th>
                                            <th>Apellidos</th>
                                            <th>Email</th>
                                            <th>Sexo</th>
                                            <th>Tipo</th>
                                            <th>Monto Pagado</th>
                                            <th>Alojamiento</th>
                                            <th>Estado Pago</th>
                                            <th>Grupo</th>
                                            <th>Fecha</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inscripciones as $inscrito): ?>
                                            <tr class="inscripcion-<?php echo $inscrito['estado_pago']; ?>">
                                                <td><code><?php echo $inscrito['codigo_inscripcion']; ?></code></td>
                                                <td><?php echo htmlspecialchars($inscrito['nombres']); ?></td>
                                                <td><?php echo htmlspecialchars($inscrito['apellidos']); ?></td>
                                                <td><?php echo htmlspecialchars($inscrito['email']); ?></td>
                                                <td><?php echo $inscrito['sexo']; ?></td>
                                                <td><?php echo $inscrito['tipo_inscripcion']; ?></td>
                                                <td>Bs. <?php echo number_format($inscrito['monto_pagado'], 2); ?></td>
                                                <td><?php echo $inscrito['alojamiento']; ?></td>
                                                <td>
                                                    <span
                                                        class="badge badge-<?php
                                                                            echo $inscrito['estado_pago'] == 'completo' ? 'success' : ($inscrito['estado_pago'] == 'beca' ? 'info' : ($inscrito['estado_pago'] == 'parcial' ? 'warning' : 'danger')); ?>">
                                                        <?php echo ucfirst($inscrito['estado_pago']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $inscrito['grupo'] ? 'Grupo ' . $inscrito['grupo'] : '-'; ?>
                                                </td>
                                                <td><?php echo formatDate($inscrito['fecha_inscripcion'], 'd/m/Y H:i'); ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning"
                                                        onclick="editarInscripcion(<?php echo $inscrito['id']; ?>)"
                                                        title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-info"
                                                        onclick="registrarPago(<?php echo $inscrito['id']; ?>)"
                                                        title="Registrar Pago">
                                                        <i class="fas fa-money-bill"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger"
                                                        onclick="eliminarInscripcion(<?php echo $inscrito['id']; ?>)"
                                                        title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Version</b> 2.0.0
            </div>
            <strong>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?></strong>
        </footer>
    </div>

    <!-- Modal Crear Inscripcion -->
    <div class="modal fade" id="modalCrearInscripcion" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Inscripcion - <?php echo htmlspecialchars($evento['nombre']); ?></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="crear_inscripcion">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nombres *</label>
                                    <input type="text" class="form-control" name="nombres" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Apellidos *</label>
                                    <input type="text" class="form-control" name="apellidos" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>
                                        Email
                                        <small class="text-muted">(Opcional)</small>
                                    </label>
                                    <input type="email" class="form-control" name="email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>
                                        Teléfono
                                        <small class="text-muted">(Opcional)</small>
                                    </label>
                                    <input type="tel" class="form-control" name="telefono">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha de Nacimiento *</label>
                                    <input type="date" class="form-control" name="fecha_nacimiento" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sexo *</label>
                                    <select class="form-control" name="sexo" required>
                                        <option value="">Seleccione</option>
                                        <option value="Masculino">Masculino</option>
                                        <option value="Femenino">Femenino</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Iglesia</label>
                                    <input type="text" class="form-control" name="iglesia">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Departamento</label>
                                    <input type="text" class="form-control" name="departamento">
                                </div>
                            </div>
                        </div>

                        <!-- Criterios de Descuentos -->
                        <?php
                        $tieneDescuentosEdad = (!empty($evento['edad_rango1_min']) && !empty($evento['edad_rango1_max'])) ||
                            (!empty($evento['edad_rango2_min']) && !empty($evento['edad_rango2_max']));
                        $tieneDescuentosFecha = (!empty($config['descuento_fecha1']) || !empty($config['descuento_fecha2']) || !empty($config['descuento_fecha3']));
                        $tieneDescuentos = $tieneDescuentosEdad || $tieneDescuentosFecha;
                        ?>
                        <?php if ($tieneDescuentos): ?>
                            <div class="alert alert-info">
                                <h6 class="mb-2"><i class="fas fa-tags"></i> Criterios de Descuentos Aplicables</h6>

                                <?php if ($tieneDescuentosEdad): ?>
                                    <div class="mb-2">
                                        <strong>Descuentos por Edad:</strong>
                                        <ul class="mb-1">
                                            <?php if (!empty($evento['edad_rango1_min']) && !empty($evento['edad_rango1_max'])): ?>
                                                <li>Edad <?php echo $evento['edad_rango1_min']; ?> -
                                                    <?php echo $evento['edad_rango1_max']; ?> años: Bs.
                                                    <?php echo number_format($evento['costo_rango1'], 2); ?></li>
                                            <?php endif; ?>
                                            <?php if (!empty($evento['edad_rango2_min']) && !empty($evento['edad_rango2_max'])): ?>
                                                <li>Edad <?php echo $evento['edad_rango2_min']; ?> -
                                                    <?php echo $evento['edad_rango2_max']; ?> años: Bs.
                                                    <?php echo number_format($evento['costo_rango2'], 2); ?></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <?php if ($tieneDescuentosFecha): ?>
                                    <div class="mb-2">
                                        <strong>Descuentos por Fecha de Inscripción:</strong>
                                        <ul class="mb-1">
                                            <?php if (!empty($config['descuento_fecha1'])): ?>
                                                <li>Hasta el <?php echo formatDate($config['descuento_fecha1']); ?>: Bs.
                                                    <?php echo number_format($config['descuento_costo1'], 2); ?></li>
                                            <?php endif; ?>
                                            <?php if (!empty($config['descuento_fecha2'])): ?>
                                                <li>Hasta el <?php echo formatDate($config['descuento_fecha2']); ?>: Bs.
                                                    <?php echo number_format($config['descuento_costo2'], 2); ?></li>
                                            <?php endif; ?>
                                            <?php if (!empty($config['descuento_fecha3'])): ?>
                                                <li>Hasta el <?php echo formatDate($config['descuento_fecha3']); ?>: Bs.
                                                    <?php echo number_format($config['descuento_costo3'], 2); ?></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <small class="text-muted">Los descuentos se aplican automáticamente según
                                    corresponda.</small>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipo de Pago *</label>
                                    <select class="form-control" id="tipo_inscripcion_modal" name="tipo_inscripcion"
                                        required onchange="calcularMontoModal()">
                                        <option value="">Seleccione</option>
                                        <option value="Efectivo">Efectivo</option>
                                        <option value="QR">QR</option>
                                        <option value="Deposito">Depósito</option>
                                        <option value="Beca">Beca</option>
                                    </select>
                                </div>
                            </div>
                            <?php
                            $tieneAlojamiento = !empty($evento['alojamiento_opcion1_desc']) ||
                                !empty($evento['alojamiento_opcion2_desc']) ||
                                !empty($evento['alojamiento_opcion3_desc']);
                            ?>
                            <?php if ($tieneAlojamiento): ?>
                                <div class="col-md-6">
                                    <label class="form-label">Opciones de Alojamiento *</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="alojamiento"
                                            id="alojamiento_modal_no" value="No" checked onchange="calcularMontoModal()">
                                        <label class="form-check-label" for="alojamiento_modal_no">
                                            No requiere alojamiento
                                        </label>
                                    </div>
                                    <?php if (!empty($evento['alojamiento_opcion1_desc'])): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="alojamiento"
                                                id="alojamiento_modal_opcion1"
                                                value="<?php echo htmlspecialchars($evento['alojamiento_opcion1_desc']); ?>"
                                                onchange="calcularMontoModal()">
                                            <label class="form-check-label" for="alojamiento_modal_opcion1">
                                                <?php echo htmlspecialchars($evento['alojamiento_opcion1_desc']); ?> (+Bs.
                                                <?php echo number_format($evento['alojamiento_opcion1_costo'] ?? 0, 2); ?>)
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($evento['alojamiento_opcion2_desc'])): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="alojamiento"
                                                id="alojamiento_modal_opcion2"
                                                value="<?php echo htmlspecialchars($evento['alojamiento_opcion2_desc']); ?>"
                                                onchange="calcularMontoModal()">
                                            <label class="form-check-label" for="alojamiento_modal_opcion2">
                                                <?php echo htmlspecialchars($evento['alojamiento_opcion2_desc']); ?> (+Bs.
                                                <?php echo number_format($evento['alojamiento_opcion2_costo'] ?? 0, 2); ?>)
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($evento['alojamiento_opcion3_desc'])): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="alojamiento"
                                                id="alojamiento_modal_opcion3"
                                                value="<?php echo htmlspecialchars($evento['alojamiento_opcion3_desc']); ?>"
                                                onchange="calcularMontoModal()">
                                            <label class="form-check-label" for="alojamiento_modal_opcion3">
                                                <?php echo htmlspecialchars($evento['alojamiento_opcion3_desc']); ?> (+Bs.
                                                <?php echo number_format($evento['alojamiento_opcion3_costo'] ?? 0, 2); ?>)
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>
                                            ¿Requiere Alojamiento? *
                                            <small class="text-muted">(+Bs.
                                                <?php echo number_format($evento['costo_alojamiento'] ?? 0, 2); ?>)</small>
                                        </label>
                                        <select class="form-control" id="alojamiento_modal" name="alojamiento" required
                                            onchange="calcularMontoModal()">
                                            <option value="No">No</option>
                                            <option value="Si">Sí (+Bs.
                                                <?php echo number_format($evento['costo_alojamiento'] ?? 0, 2); ?>)</option>
                                        </select>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Campo para Código de Pago (QR/Depósito) -->
                        <div class="row" id="campo_codigo_pago_modal" style="display: none;">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>
                                        <span id="label_codigo_pago_modal">Código de Pago</span> *
                                    </label>
                                    <input type="text" class="form-control" id="codigo_pago_modal" name="codigo_pago"
                                        placeholder="Ingrese el código de transacción">
                                    <small class="form-text text-muted" id="help_codigo_pago_modal">
                                        Ingrese el código de la transacción realizada
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen de Costos -->
                        <div class="alert alert-info">
                            <h6 class="mb-2"><i class="fas fa-calculator"></i> Resumen de Costos</h6>
                            <div class="row">
                                <div class="col-6">
                                    <strong>Costo de Inscripción:</strong>
                                </div>
                                <div class="col-6 text-right">
                                    <span id="costo_inscripcion_display_modal">Bs.
                                        <?php echo number_format($evento['costo_inscripcion'] ?? 0, 2); ?></span>
                                </div>
                            </div>
                            <div class="row" id="row_alojamiento_modal" style="display: none;">
                                <div class="col-6">
                                    <strong>Costo de Alojamiento:</strong>
                                </div>
                                <div class="col-6 text-right">
                                    <span id="costo_alojamiento_display_modal">Bs.
                                        <?php echo number_format($evento['costo_alojamiento'] ?? 0, 2); ?></span>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-6">
                                    <strong>TOTAL A PAGAR:</strong>
                                </div>
                                <div class="col-6 text-right">
                                    <strong><span id="total_display_modal" style="font-size: 1.2em; color: #6B5B95;">Bs.
                                            <?php echo number_format($evento['costo_inscripcion'] ?? 0, 2); ?></span></strong>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>
                                        Monto que Pagará *
                                        <small class="text-muted">(Se calcula automáticamente)</small>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Bs.</span>
                                        </div>
                                        <input type="number" step="0.01" class="form-control" id="monto_pagado_modal"
                                            name="monto_pagado" required readonly
                                            style="background-color: #f8f9fa; font-weight: bold;">
                                    </div>
                                    <small class="form-text text-muted">
                                        Este monto se calcula automáticamente según la inscripción y alojamiento
                                        seleccionado
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Inscripcion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Formar Grupos -->
    <div class="modal fade" id="modalFormarGrupos" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Formar Grupos Aleatoriamente</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="formar_grupos">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Numero de Grupos *</label>
                            <input type="number" class="form-control" name="numero_grupos" min="1" max="20" required>
                            <small class="form-text text-muted">
                                Total de inscritos: <?php echo $stats['total_inscritos'] ?? 0; ?><br>
                                Se distribuiran aleatoriamente entre los grupos especificados.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning"
                            onclick="return confirm('¿Esta seguro de formar los grupos? Esto sobrescribira los grupos existentes.');">Formar
                            Grupos</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tablaInscripciones').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.4/i18n/es_es.json"
                },
                "dom": 'Bfrtip',
                "buttons": [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });

            // Calcular monto al abrir el modal
            $('#modalCrearInscripcion').on('shown.bs.modal', function() {
                calcularMontoModal();
            });
        });

        function calcularMontoModal() {
            var tipoInscripcion = document.getElementById('tipo_inscripcion_modal').value;
            var alojamientoRadios = document.getElementsByName('alojamiento');
            var alojamientoSeleccionado = '';
            for (var i = 0; i < alojamientoRadios.length; i++) {
                if (alojamientoRadios[i].checked) {
                    alojamientoSeleccionado = alojamientoRadios[i].value;
                    break;
                }
            }
            // Fallback para el caso donde no hay radios (select dropdown)
            if (alojamientoSeleccionado === '' && document.getElementById('alojamiento_modal')) {
                alojamientoSeleccionado = document.getElementById('alojamiento_modal').value;
            }

            var montoPagado = document.getElementById('monto_pagado_modal');
            var totalDisplay = document.getElementById('total_display_modal');
            var rowAlojamiento = document.getElementById('row_alojamiento_modal');
            var costoInscripcionDisplay = document.getElementById('costo_inscripcion_display_modal');
            var campoCodigo = document.getElementById('campo_codigo_pago_modal');
            var inputCodigo = document.getElementById('codigo_pago_modal');
            var labelCodigo = document.getElementById('label_codigo_pago_modal');
            var helpCodigo = document.getElementById('help_codigo_pago_modal');

            // Usar los costos del evento específico
            var costoInscripcion = <?php echo $evento['costo_inscripcion'] ?? 0; ?>;
            var costoAlojamiento = 0;

            // Determinar costo de alojamiento basado en la opción seleccionada
            if (alojamientoSeleccionado === '<?php echo addslashes($evento['alojamiento_opcion1_desc'] ?? ''); ?>') {
                costoAlojamiento = <?php echo $evento['alojamiento_opcion1_costo'] ?? 0; ?>;
            } else if (alojamientoSeleccionado === '<?php echo addslashes($evento['alojamiento_opcion2_desc'] ?? ''); ?>') {
                costoAlojamiento = <?php echo $evento['alojamiento_opcion2_costo'] ?? 0; ?>;
            } else if (alojamientoSeleccionado === '<?php echo addslashes($evento['alojamiento_opcion3_desc'] ?? ''); ?>') {
                costoAlojamiento = <?php echo $evento['alojamiento_opcion3_costo'] ?? 0; ?>;
            } else if (alojamientoSeleccionado === 'Si') {
                costoAlojamiento = <?php echo $evento['costo_alojamiento'] ?? 0; ?>;
            }

            var total = costoInscripcion;
            var costoInscripcionActual = costoInscripcion;

            // Mostrar/ocultar campo de código según tipo de pago
            if (tipoInscripcion === 'QR') {
                campoCodigo.style.display = '';
                inputCodigo.required = true;
                labelCodigo.textContent = 'Código de Transacción QR';
                helpCodigo.textContent = 'Ingrese el código de la transacción QR realizada';
            } else if (tipoInscripcion === 'Deposito') {
                campoCodigo.style.display = '';
                inputCodigo.required = true;
                labelCodigo.textContent = 'Código de Depósito';
                helpCodigo.textContent = 'Ingrese el número de comprobante del depósito bancario';
            } else {
                campoCodigo.style.display = 'none';
                inputCodigo.required = false;
                inputCodigo.value = '';
            }

            // Si es beca, el costo de inscripción es 0 y el alojamiento también es gratis
            if (tipoInscripcion === 'Beca') {
                costoInscripcionActual = 0;
                total = 0;
                costoAlojamiento = 0;
                if (alojamientoSeleccionado !== 'No') {
                    rowAlojamiento.style.display = '';
                    document.getElementById('costo_alojamiento_display_modal').textContent = 'Bs. 0.00';
                } else {
                    rowAlojamiento.style.display = 'none';
                }
            } else {
                // Para pagos normales, sumar el costo de alojamiento si aplica
                if (alojamientoSeleccionado !== 'No') {
                    total += costoAlojamiento;
                    rowAlojamiento.style.display = '';
                    document.getElementById('costo_alojamiento_display_modal').textContent = 'Bs. ' + costoAlojamiento
                        .toFixed(2);
                } else {
                    rowAlojamiento.style.display = 'none';
                }
            }

            // Actualizar el costo de inscripción en el resumen
            costoInscripcionDisplay.textContent = 'Bs. ' + costoInscripcionActual.toFixed(2);

            // Actualizar el total en el resumen
            totalDisplay.textContent = 'Bs. ' + total.toFixed(2);

            // Establecer el monto pagado automáticamente
            montoPagado.value = total.toFixed(2);

            // Cambiar color de fondo según el tipo
            if (tipoInscripcion === 'Beca') {
                montoPagado.style.backgroundColor = '#fff3cd';
            } else {
                montoPagado.style.backgroundColor = '#f8f9fa';
            }
        }

        function editarInscripcion(id) {
            alert('Funcion de edicion en desarrollo');
        }

        function registrarPago(id) {
            alert('Funcion de registro de pago en desarrollo');
        }

        function eliminarInscripcion(id) {
            if (confirm('¿Esta seguro de eliminar esta inscripcion?')) {
                alert('Funcion de eliminacion en desarrollo');
            }
        }
    </script>
</body>

</html>