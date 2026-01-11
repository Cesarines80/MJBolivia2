<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Requerir autenticación
Auth::requireLogin();

// Procesar acciones
if (isset($_GET['action'])) {
    // Marcar como leído
    if ($_GET['action'] === 'mark_read' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        if (Contactos::updateStatus($id, 'leido')) {
            logActivity('MARK_CONTACT_READ', "ID: $id");
            $_SESSION['success'] = 'Mensaje marcado como leído';
        } else {
            $_SESSION['error'] = 'Error al actualizar el mensaje';
        }
    }
    
    // Marcar como respondido
    if ($_GET['action'] === 'mark_responded' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        if (Contactos::updateStatus($id, 'respondido')) {
            logActivity('MARK_CONTACT_RESPONDED', "ID: $id");
            $_SESSION['success'] = 'Mensaje marcado como respondido';
        } else {
            $_SESSION['error'] = 'Error al actualizar el mensaje';
        }
    }
    
    // Eliminar mensaje
    if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        if (Contactos::delete($id)) {
            logActivity('DELETE_CONTACT', "ID: $id");
            $_SESSION['success'] = 'Mensaje eliminado correctamente';
        } else {
            $_SESSION['error'] = 'Error al eliminar el mensaje';
        }
    }
    
    redirect('contactos.php');
}

// Obtener filtros
$estadoFiltro = $_GET['estado'] ?? '';
$contactos = Contactos::getAll($estadoFiltro);

// Contar mensajes por estado
$conteoEstados = Contactos::getCountByStatus();

// Obtener usuario actual
$currentUser = Auth::getUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mensajes de Contacto - Administración</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <style>
        .sidebar-dark-primary {
            background: linear-gradient(180deg, #8B7EC8, #6B5B95) !important;
        }
        
        .brand-link {
            background: rgba(0,0,0,0.1) !important;
        }
        
        .content-wrapper {
            background: #f8f9fa;
        }
        
        .contact-card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 5px solid #e9ecef;
        }
        
        .contact-card.nuevo {
            border-left-color: #dc3545;
        }
        
        .contact-card.leido {
            border-left-color: #ffc107;
        }
        
        .contact-card.respondido {
            border-left-color: #28a745;
        }
        
        .contact-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .badge-estado {
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        
        .estado-nuevo {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }
        
        .estado-leido {
            background: linear-gradient(135deg, #ffc107, #e0a800);
        }
        
        .estado-respondido {
            background: linear-gradient(135deg, #28a745, #1e7e34);
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
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
        </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="dashboard.php" class="brand-link">
            <i class="fas fa-building brand-image"></i>
            <span class="brand-text font-weight-light">Admin</span>
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    
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
                        <a href="configuracion.php" class="nav-link">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>Configuración</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="contactos.php" class="nav-link active">
                            <i class="nav-icon fas fa-envelope"></i>
                            <p>Mensajes</p>
                            <?php if (($conteoEstados['nuevo'] ?? 0) > 0): ?>
                            <span class="badge badge-danger right"><?php echo $conteoEstados['nuevo']; ?></span>
                            <?php endif; ?>
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
                        <h1 class="m-0">Mensajes de Contacto</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Mensajes</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Mensajes -->
                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-check"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
                <?php endif; ?>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-gradient-danger">
                            <div class="inner">
                                <h3><?php echo $conteoEstados['nuevo'] ?? 0; ?></h3>
                                <p>Mensajes Nuevos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <a href="contactos.php?estado=nuevo" class="small-box-footer">
                                Ver mensajes <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-gradient-warning">
                            <div class="inner">
                                <h3><?php echo $conteoEstados['leido'] ?? 0; ?></h3>
                                <p>Mensajes Leídos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-envelope-open"></i>
                            </div>
                            <a href="contactos.php?estado=leido" class="small-box-footer">
                                Ver mensajes <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-gradient-success">
                            <div class="inner">
                                <h3><?php echo $conteoEstados['respondido'] ?? 0; ?></h3>
                                <p>Mensajes Respondidos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <a href="contactos.php?estado=respondido" class="small-box-footer">
                                Ver mensajes <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Filtros</h3>
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="contactos.php" class="btn btn-outline-secondary <?php echo empty($estadoFiltro) ? 'active' : ''; ?>">
                                Todos
                            </a>
                            <a href="contactos.php?estado=nuevo" class="btn btn-outline-danger <?php echo $estadoFiltro === 'nuevo' ? 'active' : ''; ?>">
                                Nuevos
                            </a>
                            <a href="contactos.php?estado=leido" class="btn btn-outline-warning <?php echo $estadoFiltro === 'leido' ? 'active' : ''; ?>">
                                Leídos
                            </a>
                            <a href="contactos.php?estado=respondido" class="btn btn-outline-success <?php echo $estadoFiltro === 'respondido' ? 'active' : ''; ?>">
                                Respondidos
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Listado de mensajes -->
                <div class="row">
                    <?php foreach ($contactos as $contacto): ?>
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <div class="card contact-card <?php echo $contacto['estado']; ?>">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo htmlspecialchars($contacto['nombre']); ?></h5>
                                <span class="badge badge-estado estado-<?php echo $contacto['estado']; ?>">
                                    <?php echo ucfirst($contacto['estado']); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-envelope text-primary mr-2"></i>
                                    <span><?php echo htmlspecialchars($contacto['email']); ?></span>
                                </div>
                                
                                <?php if ($contacto['telefono']): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-phone text-success mr-2"></i>
                                    <span><?php echo htmlspecialchars($contacto['telefono']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-tag text-info mr-2"></i>
                                    <strong><?php echo htmlspecialchars($contacto['asunto']); ?></strong>
                                </div>
                                
                                <div class="mb-3">
                                    <p class="text-muted mb-0" style="max-height: 80px; overflow: hidden;">
                                        <?php echo nl2br(htmlspecialchars(limitText($contacto['mensaje'], 150))); ?>
                                    </p>
                                </div>
                                
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i> 
                                    <?php echo formatDate($contacto['fecha_creacion'], 'd/m/Y H:i'); ?>
                                </small>
                            </div>
                            <div class="card-footer">
                                <div class="btn-group w-100">
                                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" 
                                            data-target="#modalVer<?php echo $contacto['id']; ?>">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                    
                                    <?php if ($contacto['estado'] === 'nuevo'): ?>
                                    <a href="contactos.php?action=mark_read&id=<?php echo $contacto['id']; ?>" 
                                       class="btn btn-warning btn-sm">
                                        <i class="fas fa-envelope-open"></i> Marcar leído
                                    </a>
                                    <?php elseif ($contacto['estado'] === 'leido'): ?>
                                    <a href="contactos.php?action=mark_responded&id=<?php echo $contacto['id']; ?>" 
                                       class="btn btn-success btn-sm">
                                        <i class="fas fa-check-double"></i> Respondido
                                    </a>
                                    <?php endif; ?>
                                    
                                    <button type="button" class="btn btn-danger btn-sm" 
                                            onclick="confirmarEliminar(<?php echo $contacto['id']; ?>, '<?php echo htmlspecialchars($contacto['nombre']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Ver Mensaje -->
                    <div class="modal fade" id="modalVer<?php echo $contacto['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Detalles del Mensaje</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($contacto['nombre']); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($contacto['email']); ?></p>
                                            <?php if ($contacto['telefono']): ?>
                                            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($contacto['telefono']); ?></p>
                                            <?php endif; ?>
                                            <p><strong>Asunto:</strong> <?php echo htmlspecialchars($contacto['asunto']); ?></p>
                                            <p><strong>Fecha:</strong> <?php echo formatDate($contacto['fecha_creacion'], 'd/m/Y H:i:s'); ?></p>
                                            <p><strong>Estado:</strong> 
                                                <span class="badge badge-estado estado-<?php echo $contacto['estado']; ?>">
                                                    <?php echo ucfirst($contacto['estado']); ?>
                                                </span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Mensaje:</strong></p>
                                            <div class="border p-3 rounded bg-light">
                                                <?php echo nl2br(htmlspecialchars($contacto['mensaje'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                    <a href="mailto:<?php echo htmlspecialchars($contacto['email']); ?>?subject=Re: <?php echo urlencode($contacto['asunto']); ?>" 
                                       class="btn btn-primary">
                                        <i class="fas fa-reply"></i> Responder
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($contactos)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-5x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay mensajes</h4>
                    <p class="text-muted">No hay mensajes que coincidan con los filtros seleccionados.</p>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- Modal Confirmar Eliminar -->
    <div class="modal fade" id="modalEliminar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar el mensaje de <strong id="eliminar_nombre"></strong>?</p>
                    <p class="text-danger">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <a href="#" id="eliminar_link" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <strong>&copy; <?php echo date('Y'); ?> Institución.</strong> Todos los derechos reservados.
    </footer>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
    // Función para confirmar eliminación
    function confirmarEliminar(id, nombre) {
        $('#eliminar_nombre').text(nombre);
        $('#eliminar_link').attr('href', 'contactos.php?action=delete&id=' + id);
        $('#modalEliminar').modal('show');
    }
    
    // Auto-refresh cada 30 segundos si hay mensajes nuevos
    <?php if (($conteoEstados['nuevo'] ?? 0) > 0): ?>
    setTimeout(function() {
        location.reload();
    }, 30000);
    <?php endif; ?>
</script>
</body>
</html>