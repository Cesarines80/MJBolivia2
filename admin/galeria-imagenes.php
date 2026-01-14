<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/galeria_imagenes.php';

// Requerir autenticación
Auth::requireLogin();

// Obtener ID de la galería
$id = isset($_GET['galeria_id']) ? (int)$_GET['galeria_id'] : 0;

if (!$id) {
    header('Location: galeria.php');
    exit;
}

// Obtener el elemento de galería
$galeria = Galeria::getById($id);

if (!$galeria) {
    header('Location: galeria.php');
    exit;
}

// Procesar eliminación de imagen
if (isset($_GET['delete'])) {
    $imageId = (int)$_GET['delete'];
    if (GaleriaImagenes::deleteGalleryImage($imageId)) {
        $_SESSION['success'] = 'Imagen eliminada correctamente';
    } else {
        $_SESSION['error'] = 'Error al eliminar la imagen';
    }
    header('Location: galeria-imagenes.php?id=' . $id);
    exit;
}

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Token de seguridad inválido';
        header('Location: galeria-imagenes.php?id=' . $id);
        exit;
    }

    // Agregar imagen
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $data = [
            'titulo' => cleanInput($_POST['titulo'] ?? ''),
            'descripcion' => cleanInput($_POST['descripcion'] ?? ''),
            'orden' => (int)($_POST['orden'] ?? 0)
        ];

        // Subir imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($_FILES['imagen']);
            if ($upload['success']) {
                $data['imagen'] = $upload['filename'];
                if (GaleriaImagenes::addGalleryImage($id, $data)) {
                    $_SESSION['success'] = 'Imagen agregada correctamente';
                } else {
                    $_SESSION['error'] = 'Error al agregar la imagen';
                }
            } else {
                $_SESSION['error'] = $upload['error'];
            }
        } else {
            $_SESSION['error'] = 'Debe seleccionar una imagen';
        }
    }

    // Editar imagen
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $imageId = (int)$_POST['image_id'];
        $data = [
            'titulo' => cleanInput($_POST['titulo'] ?? ''),
            'descripcion' => cleanInput($_POST['descripcion'] ?? ''),
            'orden' => (int)($_POST['orden'] ?? 0)
        ];

        // Subir nueva imagen si se seleccionó
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $oldImage = GaleriaImagenes::getGalleryImageById($imageId);
            if ($oldImage && $oldImage['imagen']) {
                $oldPath = UPLOADS_DIR . $oldImage['imagen'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $upload = uploadFile($_FILES['imagen']);
            if ($upload['success']) {
                $data['imagen'] = $upload['filename'];
            } else {
                $_SESSION['error'] = $upload['error'];
                header('Location: galeria-imagenes.php?id=' . $id);
                exit;
            }
        }

        if (GaleriaImagenes::updateGalleryImage($imageId, $data)) {
            $_SESSION['success'] = 'Imagen actualizada correctamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar la imagen';
        }
    }

    header('Location: galeria-imagenes.php?id=' . $id);
    exit;
}

// Obtener imágenes de la galería
$imagenes = GaleriaImagenes::getGalleryImages($id);

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
    <title>Imágenes de Galería - Administración</title>

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

        .gallery-image-item {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            background: white;
        }

        .gallery-image-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .gallery-image-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .gallery-image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gallery-image-item:hover .gallery-image-overlay {
            opacity: 1;
        }

        .main-gallery-image {
            border: 3px solid #8B7EC8;
            box-shadow: 0 0 0 3px rgba(139, 126, 200, 0.3);
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
                            <a href="galeria.php" class="nav-link active">
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
                            <h1 class="m-0">Imágenes de: <?php echo htmlspecialchars($galeria['titulo']); ?></h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="galeria.php">Galería</a></li>
                                <li class="breadcrumb-item active">Imágenes</li>
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

                    <!-- Imagen principal de la galería -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Imagen Principal</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="gallery-image-item main-gallery-image">
                                                <img src="<?php echo UPLOADS_URL . $galeria['imagen']; ?>"
                                                    alt="<?php echo htmlspecialchars($galeria['titulo']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <h4><?php echo htmlspecialchars($galeria['titulo']); ?></h4>
                                            <p><?php echo htmlspecialchars($galeria['descripcion']); ?></p>
                                            <p><strong>Categoría:</strong>
                                                <?php echo htmlspecialchars($galeria['categoria']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botón agregar -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregar">
                            <i class="fas fa-plus"></i> Agregar Imagen
                        </button>
                        <a href="galeria.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver a Galería
                        </a>
                    </div>

                    <!-- Galería de imágenes -->
                    <div class="row">
                        <?php foreach ($imagenes as $imagen): ?>
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                <div class="gallery-image-item">
                                    <img src="<?php echo UPLOADS_URL . $imagen['imagen']; ?>"
                                        alt="<?php echo htmlspecialchars($imagen['titulo'] ?? 'Imagen de galería'); ?>">
                                    <div class="gallery-image-overlay">
                                        <div class="text-center text-white">
                                            <?php if ($imagen['titulo']): ?>
                                                <h6><?php echo htmlspecialchars($imagen['titulo']); ?></h6>
                                            <?php endif; ?>
                                            <div class="btn-group mt-2">
                                                <button type="button" class="btn btn-warning btn-sm"
                                                    onclick="editarImagen(<?php echo $imagen['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="confirmarEliminar(<?php echo $imagen['id']; ?>, '<?php echo htmlspecialchars($imagen['titulo'] ?? 'imagen'); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <?php echo formatDate($imagen['fecha_creacion']); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (empty($imagenes)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-images fa-5x text-muted mb-3"></i>
                            <h4 class="text-muted">No hay imágenes adicionales</h4>
                            <p class="text-muted">Agrega imágenes adicionales para mostrar en la página de detalle</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- Modal Agregar -->
        <div class="modal fade" id="modalAgregar" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="galeria-imagenes.php?galeria_id=<?php echo $id; ?>" method="post"
                        enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title">Agregar Imagen a la Galería</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="action" value="add">

                            <div class="form-group">
                                <label>Título</label>
                                <input type="text" name="titulo" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Imagen *</label>
                                <input type="file" name="imagen" class="form-control" accept="image/*" required>
                                <small class="text-muted">Máximo 5MB</small>
                            </div>

                            <div class="form-group">
                                <label>Orden</label>
                                <input type="number" name="orden" class="form-control" value="0" min="0">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Editar -->
        <div class="modal fade" id="modalEditar" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="galeria-imagenes.php?id=<?php echo $id; ?>" method="post"
                        enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title">Editar Imagen</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="image_id" id="edit_image_id">

                            <div class="form-group">
                                <label>Título</label>
                                <input type="text" name="titulo" id="edit_titulo" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea name="descripcion" id="edit_descripcion" class="form-control"
                                    rows="3"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Imagen actual</label>
                                <div id="edit_imagen_preview" class="mb-2"></div>
                                <label>Nueva imagen (dejar vacío para mantener la actual)</label>
                                <input type="file" name="imagen" class="form-control" accept="image/*">
                            </div>

                            <div class="form-group">
                                <label>Orden</label>
                                <input type="number" name="orden" id="edit_orden" class="form-control" min="0">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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
                        <p>¿Estás seguro de que deseas eliminar la imagen <strong id="eliminar_titulo"></strong>?</p>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <script>
        // Función para editar imagen
        function editarImagen(id) {
            $.get('ajax.php?action=get_galeria_imagen&id=' + id, function(data) {
                if (data.success) {
                    $('#edit_image_id').val(data.data.id);
                    $('#edit_titulo').val(data.data.titulo);
                    $('#edit_descripcion').val(data.data.descripcion);
                    $('#edit_orden').val(data.data.orden);

                    // Mostrar imagen actual
                    $('#edit_imagen_preview').html(
                        '<img src="<?php echo UPLOADS_URL; ?>' + data.data.imagen +
                        '" style="max-width: 200px; max-height: 100px; border-radius: 5px;">'
                    );

                    $('#modalEditar').modal('show');
                }
            }, 'json');
        }

        // Función para confirmar eliminación
        function confirmarEliminar(id, titulo) {
            $('#eliminar_titulo').text(titulo);
            $('#eliminar_link').attr('href', 'galeria-imagenes.php?id=<?php echo $id; ?>&delete=' + id);
            $('#modalEliminar').modal('show');
        }
    </script>
</body>

</html>