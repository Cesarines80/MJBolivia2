<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/inscripciones.php';
require_once __DIR__ . '/../includes/auth.php';

// Verificar autenticación
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$inscripciones = new Inscripciones();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Token CSRF inválido';
        header('Location: inscripciones.php');
        exit;
    }

    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'crear':
            $data = [
                'nombres' => sanitize($_POST['nombres']),
                'apellidos' => sanitize($_POST['apellidos']),
                'fecha_nacimiento' => sanitize($_POST['fecha_nacimiento']),
                'iglesia' => sanitize($_POST['iglesia']),
                'departamento' => sanitize($_POST['departamento']),
                'sexo' => sanitize($_POST['sexo']),
                'tipo_inscripcion' => sanitize($_POST['tipo_inscripcion']),
                'monto_pagado' => floatval($_POST['monto_pagado']),
                'alojamiento' => sanitize($_POST['alojamiento']),
                'grupo' => null
            ];
            
            // Calcular monto total
            $config = $inscripciones->getConfig();
            $monto_base = $config['precio_base'] ?? 100;
            $monto_alojamiento = ($_POST['alojamiento'] === 'Si') ? ($config['precio_alojamiento'] ?? 50) : 0;
            $data['monto_total'] = $monto_base + $monto_alojamiento;
            
            if ($inscripciones->create($data)) {
                $_SESSION['success'] = 'Inscripción creada exitosamente';
            } else {
                $_SESSION['error'] = 'Error al crear la inscripción';
            }
            break;
            
        case 'actualizar':
            $id = intval($_POST['id']);
            $data = [
                'nombres' => sanitize($_POST['nombres']),
                'apellidos' => sanitize($_POST['apellidos']),
                'fecha_nacimiento' => sanitize($_POST['fecha_nacimiento']),
                'iglesia' => sanitize($_POST['iglesia']),
                'departamento' => sanitize($_POST['departamento']),
                'sexo' => sanitize($_POST['sexo']),
                'tipo_inscripcion' => sanitize($_POST['tipo_inscripcion']),
                'monto_pagado' => floatval($_POST['monto_pagado']),
                'alojamiento' => sanitize($_POST['alojamiento'])
            ];
            
            // Recalcular monto total
            $config = $inscripciones->getConfig();
            $monto_base = $config['precio_base'] ?? 100;
            $monto_alojamiento = ($_POST['alojamiento'] === 'Si') ? ($config['precio_alojamiento'] ?? 50) : 0;
            $data['monto_total'] = $monto_base + $monto_alojamiento;
            
            if ($inscripciones->update($id, $data)) {
                $_SESSION['success'] = 'Inscripción actualizada exitosamente';
            } else {
                $_SESSION['error'] = 'Error al actualizar la inscripción';
            }
            break;
            
        case 'eliminar':
            $id = intval($_POST['id']);
            if ($inscripciones->delete($id)) {
                $_SESSION['success'] = 'Inscripción eliminada exitosamente';
            } else {
                $_SESSION['error'] = 'Error al eliminar la inscripción';
            }
            break;
            
        case 'formar_grupos':
            $numero_grupos = intval($_POST['numero_grupos']);
            if ($numero_grupos > 0) {
                if ($inscripciones->formGroups($numero_grupos)) {
                    $_SESSION['success'] = "Grupos formados exitosamente ($numero_grupos grupos)";
                } else {
                    $_SESSION['error'] = 'Error al formar los grupos';
                }
            } else {
                $_SESSION['error'] = 'Número de grupos inválido';
            }
            break;
            
        case 'configurar':
            $config = [
                'precio_base' => floatval($_POST['precio_base']),
                'precio_alojamiento' => floatval($_POST['precio_alojamiento']),
                'fecha_inicio' => sanitize($_POST['fecha_inicio']),
                'fecha_fin' => sanitize($_POST['fecha_fin']),
                'max_participantes' => intval($_POST['max_participantes']),
                'instrucciones_pago' => sanitize($_POST['instrucciones_pago'])
            ];
            
            if ($inscripciones->updateConfig($config)) {
                $_SESSION['success'] = 'Configuración actualizada exitosamente';
            } else {
                $_SESSION['error'] = 'Error al actualizar la configuración';
            }
            break;
            
        case 'limpiar_grupos':
            if ($inscripciones->clearGroups()) {
                $_SESSION['success'] = 'Grupos eliminados exitosamente';
            } else {
                $_SESSION['error'] = 'Error al eliminar los grupos';
            }
            break;
    }
    
    header('Location: inscripciones.php');
    exit;
}

// Obtener datos
$stats = $inscripciones->getStats();
$listaInscripciones = $inscripciones->getAll();
$config = $inscripciones->getConfig();
$grupos = $inscripciones->getGroups();

// Filtrar por grupo si se especifica
$filterGrupo = $_GET['grupo'] ?? '';
if ($filterGrupo !== '') {
    $listaInscripciones = array_filter($listaInscripciones, function($inscrito) use ($filterGrupo) {
        return $inscrito['grupo'] == $filterGrupo;
    });
}

// Obtener mensajes de sesión
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
    <title>Administración de Inscripciones | <?php echo SITE_NAME; ?></title>
    
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
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
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .info-box-icon {
            border-radius: 15px 0 0 15px;
        }
        
        .table-responsive {
            border-radius: 10px;
        }
        
        .modal-header {
            background-color: var(--color-primario);
            color: white;
        }
        
        .modal-footer .btn-primary {
            background-color: var(--color-primario);
            border-color: var(--color-primario);
        }
        
        .modal-footer .btn-primary:hover {
            background-color: var(--color-acento);
            border-color: var(--color-acento);
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="index.php" class="nav-link">Inicio</a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="inscripciones.php" class="nav-link">Inscripciones</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="logout.php" role="button">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="index.php" class="brand-link">
            <span class="brand-text font-weight-light"><?php echo SITE_NAME; ?></span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="info">
                    <a href="#" class="d-block"><?php echo htmlspecialchars($user['username']); ?></a>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="carousel.php" class="nav-link">
                            <i class="nav-icon fas fa-images"></i>
                            <p>Carrusel</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="eventos.php" class="nav-link">
                            <i class="nav-icon fas fa-calendar-alt"></i>
                            <p>Eventos</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="galeria.php" class="nav-link">
                            <i class="nav-icon fas fa-photo-video"></i>
                            <p>Galería</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="paginas.php" class="nav-link">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Páginas</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="contactos.php" class="nav-link">
                            <i class="nav-icon fas fa-envelope"></i>
                            <p>Contactos</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="inscripciones.php" class="nav-link active">
                            <i class="nav-icon fas fa-user-plus"></i>
                            <p>Inscripciones</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="configuracion.php" class="nav-link">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>Configuración</p>
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Administración de Inscripciones</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                            <li class="breadcrumb-item active">Inscripciones</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
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

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="info-box bg-gradient-primary">
                            <span class="info-box-icon"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Inscritos</span>
                                <span class="info-box-number"><?php echo $stats['total']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="info-box bg-gradient-success">
                            <span class="info-box-icon"><i class="fas fa-male"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Hombres</span>
                                <span class="info-box-number"><?php echo $stats['hombres']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="info-box bg-gradient-warning">
                            <span class="info-box-icon"><i class="fas fa-female"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Mujeres</span>
                                <span class="info-box-number"><?php echo $stats['mujeres']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="info-box bg-gradient-danger">
                            <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Deudores</span>
                                <span class="info-box-number"><?php echo $stats['deudores']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="info-box bg-gradient-info">
                            <span class="info-box-icon"><i class="fas fa-graduation-cap"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Becados</span>
                                <span class="info-box-number"><?php echo $stats['becados']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="info-box bg-gradient-secondary">
                            <span class="info-box-icon"><i class="fas fa-bed"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Con Alojamiento</span>
                                <span class="info-box-number"><?php echo $stats['con_alojamiento']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="info-box bg-gradient-dark">
                            <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Recaudado</span>
                                <span class="info-box-number">Bs. <?php echo number_format($stats['total_recaudado'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="info-box bg-gradient-light">
                            <span class="info-box-icon"><i class="fas fa-users-cog"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Grupos Formados</span>
                                <span class="info-box-number"><?php echo count($grupos); ?></span>
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
                                <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#modalCrear">
                                    <i class="fas fa-plus"></i> Nueva Inscripción
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button type="button" class="btn btn-success btn-block" data-toggle="modal" data-target="#modalConfigurar">
                                    <i class="fas fa-cog"></i> Configuración
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button type="button" class="btn btn-warning btn-block" data-toggle="modal" data-target="#modalFormarGrupos">
                                    <i class="fas fa-users-cog"></i> Formar Grupos
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="../inscripciones/reportes.php" target="_blank" class="btn btn-info btn-block">
                                    <i class="fas fa-file-pdf"></i> Ver Reportes
                                </a>
                            </div>
                        </div>
                        <?php if (count($grupos) > 0): ?>
                        <div class="row mt-3">
                            <div class="col-md-3 mb-2">
                                <form method="POST" onsubmit="return confirm('¿Está seguro de eliminar todos los grupos?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="action" value="limpiar_grupos">
                                    <button type="submit" class="btn btn-danger btn-block">
                                        <i class="fas fa-trash"></i> Limpiar Grupos
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Filtros</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="filterGrupo">Filtrar por Grupo:</label>
                                <select id="filterGrupo" class="form-control" onchange="filterByGroup()">
                                    <option value="">Todos los grupos</option>
                                    <?php foreach ($grupos as $grupo): ?>
                                        <option value="<?php echo $grupo['grupo']; ?>" <?php echo ($filterGrupo == $grupo['grupo']) ? 'selected' : ''; ?>>
                                            Grupo <?php echo $grupo['grupo']; ?> (<?php echo $grupo['total']; ?> personas)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterSexo">Filtrar por Sexo:</label>
                                <select id="filterSexo" class="form-control" onchange="filterTable()">
                                    <option value="">Todos</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterTipo">Tipo de Inscripción:</label>
                                <select id="filterTipo" class="form-control" onchange="filterTable()">
                                    <option value="">Todos</option>
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="QR">QR</option>
                                    <option value="Deposito">Depósito</option>
                                    <option value="Beca">Beca</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filterEstado">Estado de Pago:</label>
                                <select id="filterEstado" class="form-control" onchange="filterTable()">
                                    <option value="">Todos</option>
                                    <option value="pagado">Pagado</option>
                                    <option value="debe">Debe</option>
                                </select>
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
                                        <th>ID</th>
                                        <th>Nombres</th>
                                        <th>Apellidos</th>
                                        <th>Fecha Nacimiento</th>
                                        <th>Iglesia</th>
                                        <th>Departamento</th>
                                        <th>Sexo</th>
                                        <th>Tipo</th>
                                        <th>Monto Pagado</th>
                                        <th>Monto Total</th>
                                        <th>Alojamiento</th>
                                        <th>Grupo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($listaInscripciones as $inscrito): ?>
                                        <tr data-sexo="<?php echo $inscrito['sexo']; ?>" 
                                            data-tipo="<?php echo $inscrito['tipo_inscripcion']; ?>"
                                            data-estado="<?php echo ($inscrito['monto_pagado'] >= $inscrito['monto_total']) ? 'pagado' : 'debe'; ?>">
                                            <td><?php echo $inscrito['id']; ?></td>
                                            <td><?php echo htmlspecialchars($inscrito['nombres']); ?></td>
                                            <td><?php echo htmlspecialchars($inscrito['apellidos']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($inscrito['fecha_nacimiento'])); ?></td>
                                            <td><?php echo htmlspecialchars($inscrito['iglesia']); ?></td>
                                            <td><?php echo htmlspecialchars($inscrito['departamento']); ?></td>
                                            <td><?php echo $inscrito['sexo']; ?></td>
                                            <td><?php echo $inscrito['tipo_inscripcion']; ?></td>
                                            <td>Bs. <?php echo number_format($inscrito['monto_pagado'], 2); ?></td>
                                            <td>Bs. <?php echo number_format($inscrito['monto_total'], 2); ?></td>
                                            <td><?php echo $inscrito['alojamiento']; ?></td>
                                            <td><?php echo $inscrito['grupo'] ? 'Grupo ' . $inscrito['grupo'] : '-'; ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" onclick="editarInscripcion(<?php echo htmlspecialchars(json_encode($inscrito)); ?>)" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="eliminarInscripcion(<?php echo $inscrito['id']; ?>)" title="Eliminar">
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
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Footer -->
    <footer class="main-footer">
        <div class="float-right d-none d-sm-block">
            <b>Version</b> 1.0.0
        </div>
        <strong>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?></strong>
    </footer>
</div>
<!-- ./wrapper -->

<!-- Modal Crear Inscripción -->
<div class="modal fade" id="modalCrear" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Inscripción</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" id="formCrear">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="crear">
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
                                <label>Iglesia *</label>
                                <input type="text" class="form-control" name="iglesia" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Departamento *</label>
                                <input type="text" class="form-control" name="departamento" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo de Inscripción *</label>
                                <select class="form-control" name="tipo_inscripcion" required>
                                    <option value="">Seleccione</option>
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="QR">QR</option>
                                    <option value="Deposito">Depósito</option>
                                    <option value="Beca">Beca</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Monto Pagado *</label>
                                <input type="number" step="0.01" class="form-control" name="monto_pagado" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Alojamiento *</label>
                                <select class="form-control" name="alojamiento" required>
                                    <option value="">Seleccione</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Inscripción -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Inscripción</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" id="formEditar">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="actualizar">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nombres *</label>
                                <input type="text" class="form-control" name="nombres" id="editNombres" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Apellidos *</label>
                                <input type="text" class="form-control" name="apellidos" id="editApellidos" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha de Nacimiento *</label>
                                <input type="date" class="form-control" name="fecha_nacimiento" id="editFechaNacimiento" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Sexo *</label>
                                <select class="form-control" name="sexo" id="editSexo" required>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Iglesia *</label>
                                <input type="text" class="form-control" name="iglesia" id="editIglesia" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Departamento *</label>
                                <input type="text" class="form-control" name="departamento" id="editDepartamento" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo de Inscripción *</label>
                                <select class="form-control" name="tipo_inscripcion" id="editTipoInscripcion" required>
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="QR">QR</option>
                                    <option value="Deposito">Depósito</option>
                                    <option value="Beca">Beca</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Monto Pagado *</label>
                                <input type="number" step="0.01" class="form-control" name="monto_pagado" id="editMontoPagado" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Alojamiento *</label>
                                <select class="form-control" name="alojamiento" id="editAlojamiento" required>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Configuración -->
<div class="modal fade" id="modalConfigurar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Configuración del Sistema</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="configurar">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Precio Base ($)</label>
                                <input type="number" step="0.01" class="form-control" name="precio_base" value="<?php echo $config['precio_base'] ?? 100; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Precio Alojamiento ($)</label>
                                <input type="number" step="0.01" class="form-control" name="precio_alojamiento" value="<?php echo $config['precio_alojamiento'] ?? 50; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha Inicio de Inscripciones</label>
                                <input type="date" class="form-control" name="fecha_inicio" value="<?php echo $config['fecha_inicio'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha Fin de Inscripciones</label>
                                <input type="date" class="form-control" name="fecha_fin" value="<?php echo $config['fecha_fin'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Máximo de Participantes</label>
                                <input type="number" class="form-control" name="max_participantes" value="<?php echo $config['max_participantes'] ?? 200; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Instrucciones de Pago</label>
                        <textarea class="form-control" name="instrucciones_pago" rows="4"><?php echo $config['instrucciones_pago'] ?? ''; ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Configuración</button>
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
                        <label>Número de Grupos *</label>
                        <input type="number" class="form-control" name="numero_grupos" min="1" max="20" required>
                        <small class="form-text text-muted">
                            Total de inscritos: <?php echo $stats['total']; ?><br>
                            Se distribuirán aleatoriamente entre los grupos especificados.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning" onclick="return confirm('¿Está seguro de formar los grupos? Esto sobrescribirá los grupos existentes.');">Formar Grupos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Eliminar Inscripción -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" id="formEliminar">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="eliminar">
                <input type="hidden" name="id" id="deleteId">
                <div class="modal-body">
                    <p>¿Está seguro de que desea eliminar esta inscripción?</p>
                    <p class="text-muted">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
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
});

function editarInscripcion(inscrito) {
    $('#editId').val(inscrito.id);
    $('#editNombres').val(inscrito.nombres);
    $('#editApellidos').val(inscrito.apellidos);
    $('#editFechaNacimiento').val(inscrito.fecha_nacimiento);
    $('#editIglesia').val(inscrito.iglesia);
    $('#editDepartamento').val(inscrito.departamento);
    $('#editSexo').val(inscrito.sexo);
    $('#editTipoInscripcion').val(inscrito.tipo_inscripcion);
    $('#editMontoPagado').val(inscrito.monto_pagado);
    $('#editAlojamiento').val(inscrito.alojamiento);
    $('#modalEditar').modal('show');
}

function eliminarInscripcion(id) {
    $('#deleteId').val(id);
    $('#modalEliminar').modal('show');
}

function filterByGroup() {
    var grupo = $('#filterGrupo').val();
    if (grupo === '') {
        window.location.href = 'inscripciones.php';
    } else {
        window.location.href = 'inscripciones.php?grupo=' + grupo;
    }
}

function filterTable() {
    var sexoFilter = $('#filterSexo').val();
    var tipoFilter = $('#filterTipo').val();
    var estadoFilter = $('#filterEstado').val();
    
    $('#tablaInscripciones tbody tr').each(function() {
        var row = $(this);
        var showRow = true;
        
        if (sexoFilter && row.data('sexo') !== sexoFilter) {
            showRow = false;
        }
        
        if (tipoFilter && row.data('tipo') !== tipoFilter) {
            showRow = false;
        }
        
        if (estadoFilter && row.data('estado') !== estadoFilter) {
            showRow = false;
        }
        
        row.toggle(showRow);
    });
}
</script>
</body>
</html>