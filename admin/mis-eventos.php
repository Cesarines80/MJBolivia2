<?php
require_once __DIR__ . '/../config/config.php';

// Verificar autenticación
Auth::requireLogin();

// Verificar que tenga rol de admin o super_admin
if (!Auth::checkRole(['superadmin', 'admin', 'super_admin', 'usuario'])) {
    header('HTTP/1.0 403 Forbidden');
    die('Acceso denegado. No tienes permisos para acceder a esta página.');
}

$db = getDB();
$eventosManager = new EventosManager($db);

// Obtener eventos accesibles para el usuario actual
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
    <title>Mis Eventos | <?php echo SITE_NAME; ?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

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

        .evento-card {
            transition: transform 0.3s ease;
        }

        .evento-card:hover {
            transform: translateY(-5px);
        }

        .evento-activo {
            border-left: 4px solid #28a745;
        }

        .inscripcion-abierta {
            border-left: 4px solid #17a2b8;
        }

        .inscripcion-cerrada {
            border-left: 4px solid #dc3545;
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
                    <a href="mis-eventos.php" class="nav-link">Mis Eventos</a>
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
                                <a href="mis-eventos.php" class="nav-link active">
                                    <i class="nav-icon fas fa-calendar-check"></i>
                                    <p>Mis Eventos</p>
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
                            <h1 class="m-0">Mis Eventos</h1>
                            <p class="text-muted">Administra las inscripciones de tus eventos asignados</p>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                                <li class="breadcrumb-item active">Mis Eventos</li>
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

                    <!-- Lista de Eventos -->
                    <div class="row">
                        <?php foreach ($eventos as $evento): ?>
                            <?php
                            // Determinar el estado de inscripcion
                            $hoy = date('Y-m-d');
                            $estadoInscripcion = '';
                            $badgeClass = '';

                            if ($hoy < $evento['fecha_inicio_inscripcion']) {
                                $estadoInscripcion = 'Próximamente';
                                $badgeClass = 'badge-secondary';
                            } elseif ($hoy > $evento['fecha_fin_inscripcion']) {
                                $estadoInscripcion = 'Inscripciones Cerradas';
                                $badgeClass = 'badge-danger';
                            } else {
                                $estadoInscripcion = 'Inscripciones Abiertas';
                                $badgeClass = 'badge-success';
                            }

                            $inscripcionAbierta = ($hoy >= $evento['fecha_inicio_inscripcion'] && $hoy <= $evento['fecha_fin_inscripcion']);
                            $puedeInscribir = $inscripcionAbierta || Auth::checkRole(['usuario']);
                            ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card evento-card evento-<?php echo $evento['estado']; ?>">
                                    <div class="card-header">
                                        <h3 class="card-title"><?php echo htmlspecialchars($evento['nombre']); ?></h3>
                                        <div class="card-tools">
                                            <span class="badge <?php echo $badgeClass; ?>">
                                                <?php echo $estadoInscripcion; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-calendar"></i>
                                            Evento: <?php echo formatDate($evento['fecha_inicio']); ?> -
                                            <?php echo formatDate($evento['fecha_fin']); ?>
                                        </p>
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-user-clock"></i>
                                            Inscripciones: <?php echo formatDate($evento['fecha_inicio_inscripcion']); ?> -
                                            <?php echo formatDate($evento['fecha_fin_inscripcion']); ?>
                                        </p>
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?php echo htmlspecialchars($evento['lugar'] ?? 'No especificado'); ?>
                                        </p>

                                        <!-- Estadisticas -->
                                        <div class="row text-center mb-3">
                                            <div class="col-3">
                                                <strong><?php echo $evento['total_inscritos'] ?? 0; ?></strong><br>
                                                <small class="text-muted">Inscritos</small>
                                            </div>
                                            <div class="col-3">
                                                <strong>Bs.
                                                    <?php echo number_format($evento['total_recaudado'] ?? 0, 2); ?></strong><br>
                                                <small class="text-muted">Recaudado</small>
                                            </div>
                                            <div class="col-3">
                                                <strong><?php echo $evento['grupos_formados'] ?? 0; ?></strong><br>
                                                <small class="text-muted">Grupos</small>
                                            </div>
                                            <div class="col-3">
                                                <strong><?php echo $evento['total_becados'] ?? 0; ?></strong><br>
                                                <small class="text-muted">Becados</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="btn-group" role="group">
                                            <a href="inscripciones-evento.php?evento=<?php echo $evento['id']; ?>"
                                                class="btn btn-sm btn-primary">
                                                <i class="fas fa-users"></i> Inscripciones
                                            </a>
                                            <a href="reportes-evento.php?evento=<?php echo $evento['id']; ?>"
                                                target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-file-alt"></i> Reportes
                                            </a>
                                            <?php if ($puedeInscribir): ?>
                                                <a href="<?php echo EVENTOS_URL; ?>inscribir.php?evento=<?php echo $evento['id']; ?>"
                                                    target="_blank" class="btn btn-sm btn-success">
                                                    <i class="fas fa-user-plus"></i> Inscribir
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (empty($eventos)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-5x text-muted mb-3"></i>
                            <h3 class="text-muted">No tienes eventos asignados</h3>
                            <p class="text-muted">Contacta al Super Administrador para que te asigne eventos</p>
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

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <script>
        $(document).ready(function() {
            // Actualizar contadores en tiempo real (opcional)
            setInterval(function() {
                // Podriamos hacer AJAX para actualizar estadisticas
            }, 30000); // Cada 30 segundos
        });
    </script>
</body>

</html>