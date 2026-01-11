<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Requerir autenticación
Auth::requireLogin();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Token de seguridad inválido';
        redirect('mision-vision.php');
    }

    // Actualizar misión, visión, valores o historia
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $id = (int)$_POST['id'];
        $data = [
            'titulo' => cleanInput($_POST['titulo']),
            'contenido' => cleanInput($_POST['contenido']),
            'estado' => cleanInput($_POST['estado'] ?? 'activo')
        ];

        // Subir imagen si se seleccionó
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            // Eliminar imagen anterior
            $oldItem = MisionVision::getById($id);
            if ($oldItem && $oldItem['imagen']) {
                $oldPath = UPLOADS_DIR . $oldItem['imagen'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $upload = uploadFile($_FILES['imagen']);
            if ($upload['success']) {
                $data['imagen'] = $upload['filename'];
            } else {
                $_SESSION['error'] = $upload['error'];
                redirect('mision-vision.php');
            }
        }

        if (MisionVision::update($id, $data)) {
            logActivity('UPDATE_MISION_VISION', "ID: $id, Tipo: {$_POST['tipo']}");
            $_SESSION['success'] = ucfirst($_POST['tipo']) . ' actualizada correctamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar';
        }
    }

    redirect('mision-vision.php');
}

// Obtener todos los elementos
$misionVision = MisionVision::getAll();

// Organizar por tipo
$items = [];
foreach ($misionVision as $item) {
    $items[$item['tipo']] = $item;
}

// Obtener usuario actual
$currentUser = Auth::getUser();

// Generar token CSRF
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Misión y Visión - Administración</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .mission-card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .mission-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .mission-card .card-header {
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
        }

        .mission-card .card-body {
            padding: 2rem;
        }

        .mission-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
        }

        .mission-mision {
            background: linear-gradient(135deg, #8B7EC8, #6B5B95);
        }

        .mission-vision {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .mission-valores {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
        }

        .mission-historia {
            background: linear-gradient(135deg, #17a2b8, #6f42c1);
        }

        .content-preview {
            max-height: 150px;
            overflow: hidden;
            position: relative;
        }

        .content-preview::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            background: linear-gradient(transparent, white);
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
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
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
                            <a href="mision-vision.php" class="nav-link active">
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
                            <a href="contactos.php" class="nav-link">
                                <i class="nav-icon fas fa-envelope"></i>
                                <p>Mensajes</p>
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
                            <h1 class="m-0">Misión, Visión y Valores</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Misión y Visión</li>
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
                            <i class="fas fa-check"></i> <?php echo $_SESSION['success'];
                                                            unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="fas fa-exclamation-triangle"></i>
                            <?php echo $_SESSION['error'];
                            unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Tarjetas de Misión, Visión, Valores e Historia -->
                    <div class="row">
                        <!-- Misión -->
                        <div class="col-lg-6 mb-4">
                            <div class="card mission-card">
                                <div class="card-header text-center bg-gradient-primary text-white">
                                    <div class="mission-icon mission-mision">
                                        <i class="fas fa-bullseye"></i>
                                    </div>
                                    <h3 class="mb-0">
                                        <?php echo htmlspecialchars($items['mision']['titulo'] ?? 'Nuestra Misión'); ?>
                                    </h3>
                                    <span
                                        class="badge badge-light"><?php echo $items['mision']['estado'] === 'activo' ? 'Activo' : 'Inactivo'; ?></span>
                                </div>
                                <div class="card-body">
                                    <?php if ($items['mision']['imagen']): ?>
                                        <img src="<?php echo UPLOADS_URL . $items['mision']['imagen']; ?>" alt="Misión"
                                            class="img-fluid rounded mb-3" style="max-height: 150px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div class="content-preview">
                                        <p><?php echo nl2br(htmlspecialchars($items['mision']['contenido'] ?? 'Contenido no definido')); ?>
                                        </p>
                                    </div>
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-primary btn-sm"
                                            onclick="editarItem('mision', <?php echo $items['mision']['id'] ?? 1; ?>)">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Visión -->
                        <div class="col-lg-6 mb-4">
                            <div class="card mission-card">
                                <div class="card-header text-center bg-gradient-success text-white">
                                    <div class="mission-icon mission-vision">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                    <h3 class="mb-0">
                                        <?php echo htmlspecialchars($items['vision']['titulo'] ?? 'Nuestra Visión'); ?>
                                    </h3>
                                    <span
                                        class="badge badge-light"><?php echo $items['vision']['estado'] === 'activo' ? 'Activo' : 'Inactivo'; ?></span>
                                </div>
                                <div class="card-body">
                                    <?php if ($items['vision']['imagen']): ?>
                                        <img src="<?php echo UPLOADS_URL . $items['vision']['imagen']; ?>" alt="Visión"
                                            class="img-fluid rounded mb-3" style="max-height: 150px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div class="content-preview">
                                        <p><?php echo nl2br(htmlspecialchars($items['vision']['contenido'] ?? 'Contenido no definido')); ?>
                                        </p>
                                    </div>
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-success btn-sm"
                                            onclick="editarItem('vision', <?php echo $items['vision']['id'] ?? 2; ?>)">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Valores -->
                        <div class="col-lg-6 mb-4">
                            <div class="card mission-card">
                                <div class="card-header text-center bg-gradient-warning text-white">
                                    <div class="mission-icon mission-valores">
                                        <i class="fas fa-heart"></i>
                                    </div>
                                    <h3 class="mb-0">
                                        <?php echo htmlspecialchars($items['valores']['titulo'] ?? 'Nuestros Valores'); ?>
                                    </h3>
                                    <span
                                        class="badge badge-light"><?php echo $items['valores']['estado'] === 'activo' ? 'Activo' : 'Inactivo'; ?></span>
                                </div>
                                <div class="card-body">
                                    <?php if ($items['valores']['imagen']): ?>
                                        <img src="<?php echo UPLOADS_URL . $items['valores']['imagen']; ?>" alt="Valores"
                                            class="img-fluid rounded mb-3" style="max-height: 150px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div class="content-preview">
                                        <p><?php echo nl2br(htmlspecialchars($items['valores']['contenido'] ?? 'Contenido no definido')); ?>
                                        </p>
                                    </div>
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-warning btn-sm"
                                            onclick="editarItem('valores', <?php echo $items['valores']['id'] ?? 3; ?>)">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Historia -->
                        <div class="col-lg-6 mb-4">
                            <div class="card mission-card">
                                <div class="card-header text-center bg-gradient-info text-white">
                                    <div class="mission-icon mission-historia">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <h3 class="mb-0">
                                        <?php echo htmlspecialchars($items['historia']['titulo'] ?? 'Nuestra Historia'); ?>
                                    </h3>
                                    <span
                                        class="badge badge-light"><?php echo $items['historia']['estado'] === 'activo' ? 'Activo' : 'Inactivo'; ?></span>
                                </div>
                                <div class="card-body">
                                    <?php if ($items['historia']['imagen']): ?>
                                        <img src="<?php echo UPLOADS_URL . $items['historia']['imagen']; ?>" alt="Historia"
                                            class="img-fluid rounded mb-3" style="max-height: 150px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div class="content-preview">
                                        <p><?php echo nl2br(htmlspecialchars($items['historia']['contenido'] ?? 'Contenido no definido')); ?>
                                        </p>
                                    </div>
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-info btn-sm"
                                            onclick="editarItem('historia', <?php echo $items['historia']['id'] ?? 4; ?>)">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Modal Editar -->
        <div class="modal fade" id="modalEditar" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="mision-vision.php" method="post" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitulo">Editar</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" id="edit_id">
                            <input type="hidden" name="tipo" id="edit_tipo">

                            <div class="form-group">
                                <label>Título *</label>
                                <input type="text" name="titulo" id="edit_titulo" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Contenido *</label>
                                <textarea name="contenido" id="edit_contenido" class="form-control" rows="8"
                                    required></textarea>
                            </div>

                            <div class="form-group">
                                <label>Imagen</label>
                                <div id="edit_imagen_preview" class="mb-2"></div>
                                <input type="file" name="imagen" class="form-control" accept="image/*">
                                <small class="text-muted">Dejar vacío para mantener la imagen actual</small>
                            </div>

                            <div class="form-group">
                                <label>Estado</label>
                                <select name="estado" id="edit_estado" class="form-control">
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <script>
        // Función para editar item
        function editarItem(tipo, id) {
            // Configurar modal según el tipo
            const titulos = {
                'mision': 'Editar Misión',
                'vision': 'Editar Visión',
                'valores': 'Editar Valores',
                'historia': 'Editar Historia'
            };

            $('#modalTitulo').text(titulos[tipo]);
            $('#edit_tipo').val(tipo);

            // Obtener datos del item
            $.get('ajax.php?action=get_mision_vision&id=' + id, function(data) {
                if (data.success) {
                    $('#edit_id').val(data.data.id);
                    $('#edit_titulo').val(data.data.titulo);
                    $('#edit_contenido').val(data.data.contenido);
                    $('#edit_estado').val(data.data.estado);

                    // Mostrar imagen actual
                    if (data.data.imagen) {
                        $('#edit_imagen_preview').html(
                            '<img src="<?php echo UPLOADS_URL; ?>' + data.data.imagen +
                            '" style="max-width: 200px; max-height: 100px; border-radius: 5px;">'
                        );
                    } else {
                        $('#edit_imagen_preview').html('<p class="text-muted">Sin imagen</p>');
                    }

                    $('#modalEditar').modal('show');
                }
            }, 'json').fail(function() {
                // Si falla el AJAX, usar datos locales
                const items = <?php echo json_encode($items); ?>;
                const item = items[tipo];

                if (item) {
                    $('#edit_id').val(item.id);
                    $('#edit_titulo').val(item.titulo);
                    $('#edit_contenido').val(item.contenido);
                    $('#edit_estado').val(item.estado);

                    if (item.imagen) {
                        $('#edit_imagen_preview').html(
                            '<img src="<?php echo UPLOADS_URL; ?>' + item.imagen +
                            '" style="max-width: 200px; max-height: 100px; border-radius: 5px;">'
                        );
                    } else {
                        $('#edit_imagen_preview').html('<p class="text-muted">Sin imagen</p>');
                    }

                    $('#modalEditar').modal('show');
                }
            });
        }
    </script>
</body>

</html>