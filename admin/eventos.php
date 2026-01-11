<?php
require_once __DIR__ . '/../config/config.php';

// Verificar autenticación
Auth::requireLogin();

// Verificar que tenga rol de super_admin o superadmin
if (!Auth::checkRole(['superadmin', 'super_admin'])) {
    header('HTTP/1.0 403 Forbidden');
    die('Acceso denegado. Solo super administradores pueden acceder a esta página.');
}

$db = getDB();
$eventosManager = new EventosManager($db);

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Token CSRF invalido';
        header('Location: eventos.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'crear':
            $data = [
                'nombre' => cleanInput($_POST['nombre']),
                'descripcion' => cleanInput($_POST['descripcion']),
                'fecha_inicio' => $_POST['fecha_inicio'],
                'fecha_fin' => $_POST['fecha_fin'],
                'fecha_inicio_inscripcion' => $_POST['fecha_inicio_inscripcion'],
                'fecha_fin_inscripcion' => $_POST['fecha_fin_inscripcion'],
                'lugar' => cleanInput($_POST['lugar']),
                'costo_inscripcion' => floatval($_POST['costo_inscripcion']),
                'costo_alojamiento' => floatval($_POST['costo_alojamiento']),
                'estado' => 'activo'
            ];

            // Subir imagen de portada si se proporcionó
            if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] === UPLOAD_ERR_OK) {
                $upload = uploadFile($_FILES['imagen_portada']);
                if ($upload['success']) {
                    $data['imagen_portada'] = $upload['filename'];
                } else {
                    $_SESSION['error'] = $upload['error'];
                    header('Location: eventos.php');
                    exit;
                }
            }

            $result = $eventosManager->create($data);
            if ($result['success']) {
                $_SESSION['success'] = 'Evento creado exitosamente';
            } else {
                $_SESSION['error'] = $result['message'] ?? 'Error al crear el evento';
            }
            break;

        case 'actualizar':
            $eventoId = intval($_POST['evento_id']);
            $data = [
                'nombre' => cleanInput($_POST['nombre']),
                'descripcion' => cleanInput($_POST['descripcion']),
                'fecha_inicio' => $_POST['fecha_inicio'],
                'fecha_fin' => $_POST['fecha_fin'],
                'fecha_inicio_inscripcion' => $_POST['fecha_inicio_inscripcion'],
                'fecha_fin_inscripcion' => $_POST['fecha_fin_inscripcion'],
                'lugar' => cleanInput($_POST['lugar']),
                'costo_inscripcion' => floatval($_POST['costo_inscripcion']),
                'costo_alojamiento' => floatval($_POST['costo_alojamiento']),
                'estado' => $_POST['estado']
            ];

            $result = $eventosManager->update($eventoId, $data);
            if ($result['success']) {
                $_SESSION['success'] = 'Evento actualizado exitosamente';
            } else {
                $_SESSION['error'] = $result['message'] ?? 'Error al actualizar el evento';
            }
            break;

        case 'eliminar':
            $eventoId = intval($_POST['evento_id']);
            $result = $eventosManager->delete($eventoId);
            if ($result['success']) {
                $_SESSION['success'] = 'Evento eliminado exitosamente';
            } else {
                $_SESSION['error'] = $result['message'] ?? 'Error al eliminar el evento';
            }
            break;

        case 'configurar':
            $eventoId = intval($_POST['evento_id']);
            $config = [
                'precio_base' => floatval($_POST['precio_base']),
                'precio_alojamiento' => floatval($_POST['precio_alojamiento']),
                'max_participantes' => intval($_POST['max_participantes']),
                'requiere_aprovacion' => isset($_POST['requiere_aprovacion']),
                'instrucciones_pago' => cleanInput($_POST['instrucciones_pago'])
            ];

            $result = $eventosManager->configure($eventoId, $config);
            if ($result['success']) {
                $_SESSION['success'] = 'Configuracion actualizada exitosamente';
            } else {
                $_SESSION['error'] = $result['message'] ?? 'Error al actualizar la configuracion';
            }
            break;

        case 'asignar_admin':
            $eventoId = intval($_POST['evento_id']);
            $usuarioId = intval($_POST['usuario_id']);
            $result = $eventosManager->assignAdmin($eventoId, $usuarioId, $_SESSION['user_id']);
            if ($result) {
                $_SESSION['success'] = 'Administrador asignado exitosamente';
            } else {
                $_SESSION['error'] = 'Error al asignar el administrador';
            }
            break;
    }

    header('Location: eventos.php');
    exit;
}

// Obtener eventos
$eventos = $eventosManager->getAll();

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
    <title>Gestion de Eventos | <?php echo SITE_NAME; ?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css">

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

        .modal-header {
            background-color: var(--color-primario);
            color: white;
        }

        .evento-card {
            transition: transform 0.3s ease;
        }

        .evento-card:hover {
            transform: translateY(-5px);
        }

        .evento-activo {
            border-left: 4px solid #28a745;
        }

        .evento-inactivo {
            border-left: 4px solid #dc3545;
        }

        .evento-finalizado {
            border-left: 4px solid #6c757d;
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
                    <a href="index.php" class="nav-link">Inicio</a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="eventos.php" class="nav-link">Eventos</a>
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
                            <i class="fas fa-user-shield text-success"></i>
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
                                <a href="eventos.php" class="nav-link active">
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

                        <li class="nav-item">
                            <a href="configuracion.php" class="nav-link">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>Configuracion</p>
                            </a>
                        </li>
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
                            <h1 class="m-0">Gestion de Eventos</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                                <li class="breadcrumb-item active">Eventos</li>
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

                    <!-- Acciones -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Acciones</h3>
                        </div>
                        <div class="card-body">
                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                data-target="#modalCrearEvento">
                                <i class="fas fa-plus"></i> Crear Nuevo Evento
                            </button>
                        </div>
                    </div>

                    <!-- Lista de Eventos -->
                    <div class="row">
                        <?php foreach ($eventos as $evento): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card evento-card evento-<?php echo $evento['estado']; ?>">
                                    <div class="card-header">
                                        <h3 class="card-title"><?php echo htmlspecialchars($evento['nombre']); ?></h3>
                                        <div class="card-tools">
                                            <span
                                                class="badge badge-<?php echo $evento['estado'] == 'activo' ? 'success' : ($evento['estado'] == 'inactivo' ? 'danger' : 'secondary'); ?>">
                                                <?php echo ucfirst($evento['estado']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo formatDate($evento['fecha_inicio_inscripcion']); ?> -
                                            <?php echo formatDate($evento['fecha_fin_inscripcion']); ?>
                                        </p>
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?php echo htmlspecialchars($evento['lugar'] ?? 'No especificado'); ?>
                                        </p>
                                        <p class="mb-3">
                                            <?php echo limitText($evento['descripcion'], 100); ?>
                                        </p>

                                        <!-- Estadisticas -->
                                        <div class="row text-center mb-3">
                                            <div class="col-4">
                                                <strong><?php echo $evento['total_inscritos'] ?? 0; ?></strong><br>
                                                <small class="text-muted">Inscritos</small>
                                            </div>
                                            <div class="col-4">
                                                <strong>Bs.
                                                    <?php echo number_format($evento['total_recaudado'] ?? 0, 2); ?></strong><br>
                                                <small class="text-muted">Recaudado</small>
                                            </div>
                                            <div class="col-4">
                                                <strong><?php echo $evento['grupos_formados'] ?? 0; ?></strong><br>
                                                <small class="text-muted">Grupos</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="btn-group" role="group">
                                            <a href="evento-detalle.php?id=<?php echo $evento['id']; ?>"
                                                class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                            <button type="button" class="btn btn-sm btn-warning"
                                                onclick="editarEvento(<?php echo htmlspecialchars(json_encode($evento)); ?>)">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary"
                                                onclick="configurarEvento(<?php echo $evento['id']; ?>)">
                                                <i class="fas fa-cog"></i> Configurar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="eliminarEvento(<?php echo $evento['id']; ?>)">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (empty($eventos)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-alt fa-5x text-muted mb-3"></i>
                            <h3 class="text-muted">No hay eventos creados</h3>
                            <p class="text-muted">Crea tu primer evento para comenzar</p>
                        </div>
                    <?php endif; ?>
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

    <!-- Modal Crear Evento -->
    <div class="modal fade" id="modalCrearEvento" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Nuevo Evento</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="crear">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Nombre del Evento *</label>
                                    <input type="text" class="form-control" name="nombre" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Lugar</label>
                                    <input type="text" class="form-control" name="lugar">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Descripcion</label>
                            <textarea class="form-control" name="descripcion" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Imagen de Portada</label>
                            <input type="file" class="form-control-file" name="imagen_portada" accept="image/*">
                            <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF, WEBP. Tamaño máximo:
                                5MB</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha de Inicio del Evento *</label>
                                    <input type="date" class="form-control" name="fecha_inicio" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha de Fin del Evento *</label>
                                    <input type="date" class="form-control" name="fecha_fin" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Inicio de Inscripciones *</label>
                                    <input type="date" class="form-control" name="fecha_inicio_inscripcion" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cierre de Inscripciones *</label>
                                    <input type="date" class="form-control" name="fecha_fin_inscripcion" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Costo de Inscripción (Bs.) *</label>
                                    <input type="number" step="0.01" class="form-control" name="costo_inscripcion"
                                        value="0.00" required>
                                    <small class="form-text text-muted">Costo base de inscripción al evento</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Costo de Alojamiento (Bs.) *</label>
                                    <input type="number" step="0.01" class="form-control" name="costo_alojamiento"
                                        value="0.00" required>
                                    <small class="form-text text-muted">Costo del alojamiento (opcional para el
                                        inscrito)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Evento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Evento -->
    <div class="modal fade" id="modalEditarEvento" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Evento</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="actualizar">
                    <input type="hidden" name="evento_id" id="editEventoId">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Nombre del Evento *</label>
                                    <input type="text" class="form-control" name="nombre" id="editNombre" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Lugar</label>
                                    <input type="text" class="form-control" name="lugar" id="editLugar">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Descripcion</label>
                            <textarea class="form-control" name="descripcion" id="editDescripcion" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha de Inicio del Evento *</label>
                                    <input type="date" class="form-control" name="fecha_inicio" id="editFechaInicio"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha de Fin del Evento *</label>
                                    <input type="date" class="form-control" name="fecha_fin" id="editFechaFin" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Inicio de Inscripciones *</label>
                                    <input type="date" class="form-control" name="fecha_inicio_inscripcion"
                                        id="editFechaInicioIns" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cierre de Inscripciones *</label>
                                    <input type="date" class="form-control" name="fecha_fin_inscripcion"
                                        id="editFechaFinIns" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Costo de Inscripción (Bs.) *</label>
                                    <input type="number" step="0.01" class="form-control" name="costo_inscripcion"
                                        id="editCostoInscripcion" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Costo de Alojamiento (Bs.) *</label>
                                    <input type="number" step="0.01" class="form-control" name="costo_alojamiento"
                                        id="editCostoAlojamiento" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Estado</label>
                            <select class="form-control" name="estado" id="editEstado">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                                <option value="finalizado">Finalizado</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Configurar Evento -->
    <div class="modal fade" id="modalConfigurarEvento" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Configurar Evento</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="configurar">
                    <input type="hidden" name="evento_id" id="configEventoId">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Precio Base ($)</label>
                                    <input type="number" step="0.01" class="form-control" name="precio_base"
                                        id="configPrecioBase" value="100">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Precio Alojamiento ($)</label>
                                    <input type="number" step="0.01" class="form-control" name="precio_alojamiento"
                                        id="configPrecioAlojamiento" value="50">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Maximo de Participantes</label>
                                    <input type="number" class="form-control" name="max_participantes"
                                        id="configMaxParticipantes" value="200">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input type="checkbox" class="form-check-input" name="requiere_aprovacion"
                                        id="configRequiereAprobacion">
                                    <label class="form-check-label" for="configRequiereAprobacion">
                                        Requiere aprobacion de inscripciones
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Instrucciones de Pago</label>
                            <textarea class="form-control" name="instrucciones_pago" id="configInstrucciones"
                                rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Configuracion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Evento -->
    <div class="modal fade" id="modalEliminarEvento" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminacion</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="eliminar">
                    <input type="hidden" name="evento_id" id="deleteEventoId">
                    <div class="modal-body">
                        <p>¿Esta seguro de que desea eliminar este evento?</p>
                        <p class="text-danger"><strong>Advertencia:</strong> Esta accion no se puede deshacer.</p>
                        <p class="text-muted">Si el evento tiene inscripciones, no podra ser eliminado.</p>
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

    <script>
        function editarEvento(evento) {
            $('#editEventoId').val(evento.id);
            $('#editNombre').val(evento.nombre);
            $('#editDescripcion').val(evento.descripcion);
            $('#editLugar').val(evento.lugar);
            $('#editFechaInicio').val(evento.fecha_inicio);
            $('#editFechaFin').val(evento.fecha_fin);
            $('#editFechaInicioIns').val(evento.fecha_inicio_inscripcion);
            $('#editFechaFinIns').val(evento.fecha_fin_inscripcion);
            $('#editCostoInscripcion').val(evento.costo_inscripcion || 0);
            $('#editCostoAlojamiento').val(evento.costo_alojamiento || 0);
            $('#editEstado').val(evento.estado);
            $('#modalEditarEvento').modal('show');
        }

        function configurarEvento(eventoId) {
            // Cargar configuracion actual via AJAX
            $.ajax({
                url: 'ajax/get-config.php',
                type: 'GET',
                data: {
                    evento_id: eventoId
                },
                success: function(config) {
                    $('#configEventoId').val(eventoId);
                    $('#configPrecioBase').val(config.precio_base || 100);
                    $('#configPrecioAlojamiento').val(config.precio_alojamiento || 50);
                    $('#configMaxParticipantes').val(config.max_participantes || 200);
                    $('#configRequiereAprobacion').prop('checked', config.requiere_aprovacion);
                    $('#configInstrucciones').val(config.instrucciones_pago || '');
                    $('#modalConfigurarEvento').modal('show');
                },
                error: function() {
                    alert('Error al cargar la configuracion');
                }
            });
        }

        function eliminarEvento(eventoId) {
            $('#deleteEventoId').val(eventoId);
            $('#modalEliminarEvento').modal('show');
        }

        // Validar fechas en formulario de creacion
        $(document).ready(function() {
            $('form').submit(function(e) {
                var fechaInicio = new Date($('input[name="fecha_inicio"]').val());
                var fechaFin = new Date($('input[name="fecha_fin"]').val());
                var fechaInicioIns = new Date($('input[name="fecha_inicio_inscripcion"]').val());
                var fechaFinIns = new Date($('input[name="fecha_fin_inscripcion"]').val());

                if (fechaInicio > fechaFin) {
                    alert('La fecha de inicio del evento no puede ser posterior a la fecha de fin');
                    e.preventDefault();
                    return false;
                }

                if (fechaInicioIns > fechaFinIns) {
                    alert(
                        'La fecha de inicio de inscripciones no puede ser posterior a la fecha de cierre'
                    );
                    e.preventDefault();
                    return false;
                }

                if (fechaFinIns > fechaInicio) {
                    alert('El cierre de inscripciones no puede ser posterior al inicio del evento');
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
</body>

</html>