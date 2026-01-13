<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Requerir autenticación
Auth::requireLogin();

// Procesar eliminación (GET request)
if (isset($_GET['delete'])) {
    // Verificar que sea super_admin o admin
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['is_admin'])) {
        $_SESSION['error'] = 'Debes iniciar sesión para eliminar imágenes';
        redirect('galeria.php');
    }

    $isAdmin = isset($_SESSION['is_admin']);
    $userRole = $_SESSION['rol'] ?? $_SESSION['admin_rol'] ?? '';
    $canDelete = $isAdmin || in_array($userRole, ['super_admin', 'superadmin', 'admin']);

    if (!$canDelete) {
        $_SESSION['error'] = 'No tienes permisos para eliminar imágenes. Rol actual: ' . $userRole . ', is_admin: ' . ($isAdmin ? 'sí' : 'no');
        redirect('galeria.php');
    }

    $id = (int)$_GET['delete'];
    if (Galeria::delete($id)) {
        logActivity('DELETE_GALERIA', "ID: $id");
        $_SESSION['success'] = 'Imagen eliminada correctamente';
    } else {
        $_SESSION['error'] = 'Error al eliminar la imagen';
    }

    redirect('galeria.php');
}

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Token de seguridad inválido';
        redirect('galeria.php');
    }

    // Agregar imagen
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $data = [
            'titulo' => cleanInput($_POST['titulo']),
            'descripcion' => cleanInput($_POST['descripcion'] ?? ''),
            'categoria' => cleanInput($_POST['categoria'] ?? 'general')
        ];

        // Subir imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($_FILES['imagen']);
            if ($upload['success']) {
                $data['imagen'] = $upload['filename'];
            } else {
                $_SESSION['error'] = $upload['error'];
                redirect('galeria.php');
            }
        } else {
            // Si no se subió imagen, usar un placeholder o dejar vacío
            $data['imagen'] = '';
        }

        if (Galeria::create($data)) {
            logActivity('CREATE_GALERIA', "Título: {$data['titulo']}");
            $_SESSION['success'] = 'Imagen agregada a la galería correctamente';
        } else {
            $_SESSION['error'] = 'Error al agregar la imagen';
        }
    }

    // Editar imagen
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = (int)$_POST['id'];
        $data = [
            'titulo' => cleanInput($_POST['titulo']),
            'descripcion' => cleanInput($_POST['descripcion'] ?? ''),
            'categoria' => cleanInput($_POST['categoria'] ?? 'general')
        ];

        // Obtener item actual para preservar la imagen si no se cambia
        $oldItem = Galeria::getById($id);

        // Subir nueva imagen si se seleccionó
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            // Eliminar imagen anterior
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
                redirect('galeria.php');
            }
        } else {
            // Si no se subió nueva imagen, mantener la imagen existente
            $data['imagen'] = $oldItem['imagen'] ?? '';
        }

        if (Galeria::update($id, $data)) {
            logActivity('UPDATE_GALERIA', "ID: $id, Título: {$data['titulo']}");
            $_SESSION['success'] = 'Imagen actualizada correctamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar la imagen';
        }
    }

    redirect('galeria.php');
}

// Obtener todas las imágenes de la galería
$galeria = Galeria::getAll();

// Obtener categorías únicas
$categorias = Galeria::getCategorias();

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
    <title>Galería - Administración</title>

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

        .gallery-item {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .gallery-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .gallery-overlay {
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

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        .category-filter {
            margin-bottom: 20px;
        }

        .category-btn {
            margin: 5px;
            border-radius: 20px;
        }

        .masonry-grid {
            column-count: 3;
            column-gap: 20px;
        }

        @media (max-width: 992px) {
            .masonry-grid {
                column-count: 2;
            }
        }

        @media (max-width: 576px) {
            .masonry-grid {
                column-count: 1;
            }
        }

        .masonry-item {
            break-inside: avoid;
            margin-bottom: 20px;
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
                    <a class="nav-link" data-bs-toggle="dropdown" href="#">
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
                            <h1 class="m-0">Administración de Galería</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Galería</li>
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

                    <!-- Botón agregar -->
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#modalAgregar">
                            <i class="fas fa-plus"></i> Agregar Imagen
                        </button>

                        <!-- Filtros de categoría -->
                        <div class="category-filter">
                            <button type="button" class="btn btn-outline-primary category-btn active" data-category="">
                                Todas
                            </button>
                            <?php foreach ($categorias as $categoria): ?>
                                <button type="button" class="btn btn-outline-primary category-btn"
                                    data-category="<?php echo $categoria; ?>">
                                    <?php echo htmlspecialchars($categoria); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Galería -->
                    <div class="masonry-grid" id="galeriaContainer">
                        <?php foreach ($galeria as $item): ?>
                            <div class="masonry-item gallery-item" data-category="<?php echo $item['categoria']; ?>">
                                <img src="<?php echo UPLOADS_URL . $item['imagen']; ?>"
                                    alt="<?php echo htmlspecialchars($item['titulo']); ?>">
                                <div class="gallery-overlay">
                                    <div class="text-center text-white">
                                        <h5><?php echo htmlspecialchars($item['titulo']); ?></h5>
                                        <?php if ($item['descripcion']): ?>
                                            <p class="mb-3"><?php echo limitText($item['descripcion'], 100); ?></p>
                                        <?php endif; ?>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-warning btn-sm"
                                                onclick="editarImagen(<?php echo $item['id']; ?>)">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <?php
                                            $isAdmin = isset($_SESSION['is_admin']);
                                            $userRole = $_SESSION['rol'] ?? $_SESSION['admin_rol'] ?? '';
                                            $canDelete = $isAdmin || in_array($userRole, ['super_admin', 'superadmin', 'admin']);
                                            if ($canDelete):
                                            ?>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="confirmarEliminar(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['titulo']); ?>')">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 bg-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span
                                            class="badge badge-secondary"><?php echo htmlspecialchars($item['categoria']); ?></span>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo formatDate($item['fecha_creacion']); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (empty($galeria)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-photo-video fa-5x text-muted mb-3"></i>
                            <h4 class="text-muted">No hay imágenes en la galería</h4>
                            <p class="text-muted">Agrega tu primera imagen haciendo clic en el botón "Agregar Imagen"</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- Modal Agregar -->
        <div class="modal fade" id="modalAgregar" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="galeria.php" method="post" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title">Agregar Imagen a la Galería</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="action" value="add">

                            <div class="form-group">
                                <label>Título *</label>
                                <input type="text" name="titulo" class="form-control" required>
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
                                <label>Categoría</label>
                                <div class="input-group">
                                    <select name="categoria" id="selectCategoria" class="form-control">
                                        <option value="general">General</option>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?php echo $cat; ?>"><?php echo htmlspecialchars($cat); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                                            data-bs-target="#modalNuevaCategoria">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">O crea una nueva categoría</small>
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
                    <form action="galeria.php" method="post" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title">Editar Imagen</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" id="edit_id">

                            <div class="form-group">
                                <label>Título *</label>
                                <input type="text" name="titulo" id="edit_titulo" class="form-control" required>
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
                                <label>Categoría</label>
                                <select name="categoria" id="edit_categoria" class="form-control">
                                    <option value="general">General</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?php echo $cat; ?>"><?php echo htmlspecialchars($cat); ?></option>
                                    <?php endforeach; ?>
                                </select>
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

        <!-- Modal Nueva Categoría -->
        <div class="modal fade" id="modalNuevaCategoria" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nueva Categoría</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nombre de la categoría</label>
                            <input type="text" id="nuevaCategoriaNombre" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" onclick="agregarNuevaCategoria()">
                            <i class="fas fa-plus"></i> Agregar
                        </button>
                    </div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <script>
        // Función para editar imagen
        function editarImagen(id) {
            $.get('ajax.php?action=get_galeria&id=' + id, function(data) {
                if (data.success) {
                    $('#edit_id').val(data.data.id);
                    $('#edit_titulo').val(data.data.titulo);
                    $('#edit_descripcion').val(data.data.descripcion);
                    $('#edit_categoria').val(data.data.categoria);

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
            $('#eliminar_link').attr('href', 'galeria.php?delete=' + id);
            $('#modalEliminar').modal('show');
        }

        // Filtros por categoría
        $('.category-btn').on('click', function() {
            const category = $(this).data('category');

            // Actualizar botón activo
            $('.category-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
            $(this).removeClass('btn-outline-primary').addClass('active btn-primary');

            // Filtrar elementos
            if (category === '') {
                $('.gallery-item').show();
            } else {
                $('.gallery-item').hide();
                $('.gallery-item[data-category="' + category + '"]').show();
            }
        });

        // Agregar nueva categoría
        function agregarNuevaCategoria() {
            const nombre = $('#nuevaCategoriaNombre').val().trim();
            if (nombre) {
                // Verificar si ya existe
                let existe = false;
                $('#selectCategoria option').each(function() {
                    if ($(this).val().toLowerCase() === nombre.toLowerCase()) {
                        existe = true;
                        return false;
                    }
                });

                if (!existe) {
                    $('#selectCategoria').append('<option value="' + nombre + '">' + nombre + '</option>');
                    $('#selectCategoria').val(nombre);
                    $('#modalNuevaCategoria').modal('hide');
                    $('#nuevaCategoriaNombre').val('');
                } else {
                    alert('La categoría ya existe');
                }
            } else {
                alert('Ingresa un nombre para la categoría');
            }
        }

        // Enter en input de nueva categoría
        $('#nuevaCategoriaNombre').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                agregarNuevaCategoria();
            }
        });
    </script>
</body>

</html>