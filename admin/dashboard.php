<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Requerir autenticación
Auth::requireLogin();

// Obtener usuario actual
$currentUser = Auth::getUser();

// Obtener estadísticas
$db = getDB();

// Estadísticas generales
$stats = [
    'total_eventos' => $db->query("SELECT COUNT(*) FROM eventos WHERE estado = 'activo'")->fetchColumn(),
    'total_galeria' => $db->query("SELECT COUNT(*) FROM galeria")->fetchColumn(),
    'total_contactos' => $db->query("SELECT COUNT(*) FROM contactos WHERE estado = 'nuevo'")->fetchColumn(),
    'total_carrusel' => $db->query("SELECT COUNT(*) FROM carrusel WHERE estado = 'activo'")->fetchColumn()
];

// Últimos contactos
$ultimosContactos = Contactos::getAll('nuevo');
$ultimosContactos = array_slice($ultimosContactos, 0, 5);

// Próximos eventos
$proximosEventos = Eventos::getUpcoming(5);

// Contar contactos por estado
$contactosPorEstado = Contactos::getCountByStatus();

// Configuración del sitio
$config = SiteConfig::get();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Administración</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/tempusdominus-bootstrap-4@5.39.0/css/tempusdominus-bootstrap-4.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <style>
        .sidebar-dark-primary {
            background: linear-gradient(180deg, #8B7EC8, #6B5B95) !important;
        }

        .brand-link {
            background: rgba(0, 0, 0, 0.1) !important;
        }

        .content-wrapper {
            background: #f8f9fa;
        }

        .small-box {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .small-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .small-box .icon {
            font-size: 3rem;
            opacity: 0.8;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
        </div>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="../index.php" target="_blank" class="nav-link">
                        <i class="fas fa-home"></i> Ver sitio web
                    </a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="fas fa-user-circle"></i>
                        <span class="ml-1"><?php echo htmlspecialchars($currentUser['nombre']); ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user mr-2"></i> Mi perfil
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="change-password.php" class="dropdown-item">
                            <i class="fas fa-key mr-2"></i> Cambiar contraseña
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar sesión
                        </a>
                    </div>
                </li>
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
            <a href="dashboard.php" class="brand-link">
                <i class="fas fa-building brand-image"></i>
                <span class="brand-text font-weight-light">Admin</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link active">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-header">CONTENIDO</li>

                        <li class="nav-item">
                            <a href="carrusel.php" class="nav-link">
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
                            <a href="mision-vision.php" class="nav-link">
                                <i class="nav-icon fas fa-bullseye"></i>
                                <p>Misión y Visión</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="paginas.php" class="nav-link">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>Páginas</p>
                            </a>
                        </li>

                        <li class="nav-header">CONFIGURACIÓN</li>

                        <li class="nav-item">
                            <a href="configuracion.php" class="nav-link">
                                <i class="nav-icon fas fa-cog"></i>
                                <p>Configuración General</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="contactos.php" class="nav-link">
                                <i class="nav-icon fas fa-envelope"></i>
                                <p>
                                    Mensajes de Contacto
                                    <?php if ($stats['total_contactos'] > 0): ?>
                                        <span
                                            class="badge badge-danger right"><?php echo $stats['total_contactos']; ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>

                        <?php if (Auth::checkRole(['superadmin'])): ?>
                            <li class="nav-header">ADMINISTRACIÓN</li>

                            <li class="nav-item">
                                <a href="administradores.php" class="nav-link">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Administradores</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="logs.php" class="nav-link">
                                    <i class="nav-icon fas fa-history"></i>
                                    <p>Registro de Actividad</p>
                                </a>
                            </li>
                        <?php endif; ?>
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
                            <h1 class="m-0">Dashboard</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item active">Dashboard</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Small boxes (Stat box) -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-gradient-primary">
                                <div class="inner">
                                    <h3><?php echo $stats['total_eventos']; ?></h3>
                                    <p>Eventos Activos</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <a href="eventos.php" class="small-box-footer">
                                    Ver todos <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-gradient-success">
                                <div class="inner">
                                    <h3><?php echo $stats['total_galeria']; ?></h3>
                                    <p>Imágenes en Galería</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-photo-video"></i>
                                </div>
                                <a href="galeria.php" class="small-box-footer">
                                    Ver todas <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-gradient-warning">
                                <div class="inner">
                                    <h3><?php echo $stats['total_contactos']; ?></h3>
                                    <p>Mensajes Nuevos</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <a href="contactos.php" class="small-box-footer">
                                    Ver mensajes <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-gradient-info">
                                <div class="inner">
                                    <h3><?php echo $stats['total_carrusel']; ?></h3>
                                    <p>Imágenes en Carrusel</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-images"></i>
                                </div>
                                <a href="carrusel.php" class="small-box-footer">
                                    Ver carrusel <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- /.row -->

                    <!-- Main row -->
                    <div class="row">
                        <!-- Left col -->
                        <section class="col-lg-7 connectedSortable">
                            <!-- Custom tabs (Charts with tabs)-->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chart-pie mr-1"></i>
                                        Mensajes de Contacto por Estado
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="chart-responsive">
                                                <canvas id="contactosChart" height="150"></canvas>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <ul class="chart-legend clearfix">
                                                <li><i class="far fa-circle text-success"></i> Respondidos:
                                                    <?php echo $contactosPorEstado['respondido'] ?? 0; ?></li>
                                                <li><i class="far fa-circle text-warning"></i> Leídos:
                                                    <?php echo $contactosPorEstado['leido'] ?? 0; ?></li>
                                                <li><i class="far fa-circle text-danger"></i> Nuevos:
                                                    <?php echo $contactosPorEstado['nuevo'] ?? 0; ?></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card -->

                            <!-- TO DO List -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="ion ion-clipboard mr-1"></i>
                                        Próximos Eventos
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($proximosEventos)): ?>
                                        <p class="text-muted text-center">No hay eventos próximos</p>
                                    <?php else: ?>
                                        <ul class="todo-list" data-widget="todo-list">
                                            <?php foreach ($proximosEventos as $evento): ?>
                                                <li>
                                                    <span class="handle">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </span>
                                                    <span class="text"><?php echo htmlspecialchars($evento['titulo']); ?></span>
                                                    <small class="badge badge-info">
                                                        <i class="far fa-clock"></i>
                                                        <?php echo formatDate($evento['fecha_evento'], 'd/m'); ?>
                                                    </small>
                                                    <div class="tools">
                                                        <a href="eventos.php?edit=<?php echo $evento['id']; ?>"
                                                            class="text-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer clearfix">
                                    <a href="eventos.php" class="btn btn-primary float-right">
                                        <i class="fas fa-plus"></i> Agregar evento
                                    </a>
                                </div>
                            </div>
                            <!-- /.card -->
                        </section>
                        <!-- /.Left col -->

                        <!-- right col (We are only adding the ID to make the widgets sortable)-->
                        <section class="col-lg-5 connectedSortable">

                            <!-- solid sales graph -->
                            <div class="card bg-gradient-info">
                                <div class="card-header border-0">
                                    <h3 class="card-title">
                                        <i class="fas fa-th mr-1"></i>
                                        Accesos Rápidos
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <a href="carrusel.php" class="btn btn-block btn-outline-light">
                                                <i class="fas fa-images"></i> Carrusel
                                            </a>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <a href="eventos.php" class="btn btn-block btn-outline-light">
                                                <i class="fas fa-calendar-alt"></i> Eventos
                                            </a>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <a href="galeria.php" class="btn btn-block btn-outline-light">
                                                <i class="fas fa-photo-video"></i> Galería
                                            </a>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <a href="mision-vision.php" class="btn btn-block btn-outline-light">
                                                <i class="fas fa-bullseye"></i> Misión y Visión
                                            </a>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <a href="configuracion.php" class="btn btn-block btn-outline-light">
                                                <i class="fas fa-cog"></i> Configuración
                                            </a>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <a href="contactos.php" class="btn btn-block btn-outline-light">
                                                <i class="fas fa-envelope"></i> Mensajes
                                                <?php if ($stats['total_contactos'] > 0): ?>
                                                    <span
                                                        class="badge badge-warning"><?php echo $stats['total_contactos']; ?></span>
                                                <?php endif; ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card -->

                            <!-- Calendar -->
                            <div class="card bg-gradient-success">
                                <div class="card-header border-0">
                                    <h3 class="card-title">
                                        <i class="far fa-calendar-alt"></i>
                                        Calendario
                                    </h3>
                                </div>
                                <div class="card-body pt-0">
                                    <div id="calendar" style="width: 100%"></div>
                                </div>
                            </div>
                            <!-- /.card -->
                        </section>
                        <!-- right col -->
                    </div>
                    <!-- /.row (main row) -->
                </div>
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <footer class="main-footer">
            <strong>&copy; <?php echo date('Y'); ?>
                <?php echo htmlspecialchars($config['nombre_institucion']); ?>.</strong>
            Todos los derechos reservados.
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- ChartJS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <script>
        // Gráfico de contactos
        $(function() {
            const ctx = document.getElementById('contactosChart').getContext('2d');
            const contactosChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Nuevos', 'Leídos', 'Respondidos'],
                    datasets: [{
                        data: [
                            <?php echo $contactosPorEstado['nuevo'] ?? 0; ?>,
                            <?php echo $contactosPorEstado['leido'] ?? 0; ?>,
                            <?php echo $contactosPorEstado['respondido'] ?? 0; ?>
                        ],
                        backgroundColor: ['#dc3545', '#ffc107', '#28a745'],
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                }
            });
        });

        // Calendario simple
        $(function() {
            const today = new Date();
            const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
                "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
            ];

            $('#calendar').html(`
            <div class="text-center text-white">
                <h4>${monthNames[today.getMonth()]}</h4>
                <h1 class="display-4">${today.getDate()}</h1>
                <p>${today.getFullYear()}</p>
            </div>
        `);
        });
    </script>
</body>

</html>