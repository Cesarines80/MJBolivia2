<?php
require_once __DIR__ . '/../config/config.php';

// Verificar autenticación
Auth::requireLogin();

// Verificar que tenga rol de super_admin
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
        header('Location: eventos-desactivados.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'reactivar':
            $eventoId = intval($_POST['evento_id']);
            $result = $eventosManager->reactivate($eventoId);
            if ($result['success']) {
                $_SESSION['success'] = 'Evento reactivado exitosamente';
            } else {
                $_SESSION['error'] = $result['message'] ?? 'Error al reactivar el evento';
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
    }

    header('Location: eventos-desactivados.php');
    exit;
}

// Obtener eventos desactivados
$eventos = $eventosManager->getDeactivatedEvents();

// Obtener mensajes de sesion
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$csrf_token = generateCSRFToken();

function getContrastColor($hexColor)
{
    // Remove # if present
    $hexColor = ltrim($hexColor, '#');
    // Convert to RGB
    $r = hexdec(substr($hexColor, 0, 2));
    $g = hexdec(substr($hexColor, 2, 2));
    $b = hexdec(substr($hexColor, 4, 2));
    // Calculate luminance
    $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
    return $luminance > 0.5 ? '#000000' : '#FFFFFF';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Eventos Desactivados | <?php echo SITE_NAME; ?></title>

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

        .evento-inactivo {
            border-left: 4px solid #dc3545;
        }

        .evento-thumbnail {
            float: right;
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-left: 10px;
            border: 2px solid #e9ecef;
        }

        .evento-color-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
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
                                <a href="eventos.php" class="nav-link">
                                    <i class="nav-icon fas fa-calendar-alt"></i>
                                    <p>Gestion de Eventos</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="eventos-desactivados.php" class="nav-link active">
                                    <i class="nav-icon fas fa-calendar-times"></i>
                                    <p>Eventos Desactivados</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="usuarios.php" class="nav-link">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Gestion de Usuarios</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (Auth::checkRole(['super_admin', 'superadmin', 'admin', 'usuario'])): ?>
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
                            <h1 class="m-0">Eventos Desactivados</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                                <li class="breadcrumb-item"><a href="eventos.php">Eventos</a></li>
                                <li class="breadcrumb-item active">Desactivados</li>
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

                    <!-- Lista de Eventos Desactivados -->
                    <div class="row">
                        <?php foreach ($eventos as $evento): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card evento-card evento-inactivo">
                                    <div class="card-header"
                                        style="background-color: <?php echo $evento['color'] ?? '#B8B3D8'; ?>; color: <?php echo getContrastColor($evento['color'] ?? '#B8B3D8'); ?>;">
                                        <h3 class="card-title"><?php echo htmlspecialchars($evento['titulo']); ?></h3>
                                        <div class="card-tools">
                                            <span class="badge badge-danger">
                                                Inactivo
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
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-user-shield"></i>
                                            Administradores: <?php echo htmlspecialchars($evento['admin_nombres']); ?>
                                        </p>

                                        <?php if ($evento['imagen_portada']): ?>
                                            <img src="<?php echo UPLOADS_URL . $evento['imagen_portada']; ?>" alt="Portada"
                                                class="evento-thumbnail">
                                        <?php endif; ?>

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
                                            <button type="button" class="btn btn-sm btn-success"
                                                onclick="reactivarEvento(<?php echo $evento['id']; ?>)">
                                                <i class="fas fa-play"></i> Reactivar
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
                            <i class="fas fa-calendar-check fa-5x text-success mb-3"></i>
                            <h3 class="text-muted">No hay eventos desactivados</h3>
                            <p class="text-muted">Todos los eventos están activos</p>
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
        function eliminarEvento(eventoId) {
            $('#deleteEventoId').val(eventoId);
            $('#modalEliminarEvento').modal('show');
        }

        function reactivarEvento(eventoId) {
            if (confirm('¿Está seguro de que desea reactivar este evento?')) {
                // Crear formulario y enviar
                var form = $('<form method="POST">');
                form.append('<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">');
                form.append('<input type="hidden" name="action" value="reactivar">');
                form.append('<input type="hidden" name="evento_id" value="' + eventoId + '">');
                $('body').append(form);
                form.submit();
            }
        }
    </script>
</body>

</html>