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
        $_SESSION['error'] = 'Debes iniciar sesión para eliminar elementos del carrusel';
        redirect('carrusel.php');
    }

    $isAdmin = isset($_SESSION['is_admin']);
    $userRole = $_SESSION['rol'] ?? $_SESSION['admin_rol'] ?? '';
    $canDelete = $isAdmin || in_array($userRole, ['super_admin', 'superadmin', 'admin']);

    if (!$canDelete) {
        $_SESSION['error'] = 'No tienes permisos para eliminar elementos del carrusel. Rol actual: ' . $userRole . ', is_admin: ' . ($isAdmin ? 'sí' : 'no');
        redirect('carrusel.php');
    }

    $id = (int)$_GET['delete'];
    if (Carrusel::delete($id)) {
        logActivity('DELETE_CARRUSEL', "ID: $id");
        $_SESSION['success'] = 'Elemento del carrusel eliminado correctamente';
    } else {
        $_SESSION['error'] = 'Error al eliminar el elemento';
    }

    redirect('carrusel.php');
}

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Token de seguridad inválido';
        redirect('carrusel.php');
    }

    // Agregar nuevo item
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $data = [
            'titulo' => cleanInput($_POST['titulo']),
            'descripcion' => cleanInput($_POST['descripcion'] ?? ''),
            'tipo' => cleanInput($_POST['tipo'] ?? 'imagen'),
            'url' => cleanInput($_POST['url'] ?? ''),
            'orden' => (int)($_POST['orden'] ?? 0),
            'estado' => cleanInput($_POST['estado'] ?? 'activo')
        ];

        // Subir imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($_FILES['imagen']);
            if ($upload['success']) {
                $data['imagen'] = $upload['filename'];
            } else {
                $_SESSION['error'] = $upload['error'];
                redirect('carrusel.php');
            }
        } else {
            // Si no se subió imagen, usar un placeholder o dejar vacío
            $data['imagen'] = '';
        }

        if (Carrusel::create($data)) {
            logActivity('CREATE_CARRUSEL', "Título: {$data['titulo']}");
            $_SESSION['success'] = 'Elemento del carrusel agregado correctamente';
        } else {
            $_SESSION['error'] = 'Error al agregar el elemento';
        }
    }

    // Editar item
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = (int)$_POST['id'];
        $data = [
            'titulo' => cleanInput($_POST['titulo']),
            'descripcion' => cleanInput($_POST['descripcion'] ?? ''),
            'tipo' => cleanInput($_POST['tipo'] ?? 'imagen'),
            'url' => cleanInput($_POST['url'] ?? ''),
            'orden' => (int)($_POST['orden'] ?? 0),
            'estado' => cleanInput($_POST['estado'] ?? 'activo')
        ];

        // Obtener item actual para preservar la imagen si no se cambia
        $oldItem = Carrusel::getById($id);

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
                redirect('carrusel.php');
            }
        } else {
            // Si no se subió nueva imagen, mantener la imagen existente
            $data['imagen'] = $oldItem['imagen'] ?? '';
        }

        if (Carrusel::update($id, $data)) {
            logActivity('UPDATE_CARRUSEL', "ID: $id, Título: {$data['titulo']}");
            $_SESSION['success'] = 'Elemento del carrusel actualizado correctamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar el elemento';
        }
    }

    // Actualizar orden
    if (isset($_POST['action']) && $_POST['action'] === 'update_order') {
        $orders = $_POST['order'] ?? [];
        if (Carrusel::updateOrder($orders)) {
            logActivity('UPDATE_CARRUSEL_ORDER', 'Orden actualizado');
            $_SESSION['success'] = 'Orden actualizado correctamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar el orden';
        }
    }

    redirect('carrusel.php');
}

// Obtener todos los elementos del carrusel
$carrusel = Carrusel::getAll(false);

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
    <title>Carrusel - Administración</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .carrusel-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .carrusel-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .carrusel-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .carrusel-item .overlay {
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

        .carrusel-item:hover .overlay {
            opacity: 1;
        }

        .order-input {
            width: 60px;
            text-align: center;
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
                            <a href="carrusel.php" class="nav-link active">
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
                            <h1 class="m-0">Administración del Carrusel</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Carrusel</li>
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
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregar">
                            <i class="fas fa-plus"></i> Agregar Elemento
                        </button>
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalOrdenar">
                            <i class="fas fa-sort"></i> Ordenar Elementos
                        </button>
                    </div>

                    <!-- Listado de elementos -->
                    <div class="row">
                        <?php foreach ($carrusel as $item): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="carrusel-item">
                                    <img src="<?php echo UPLOADS_URL . $item['imagen']; ?>"
                                        alt="<?php echo htmlspecialchars($item['titulo']); ?>">
                                    <div class="p-3">
                                        <h5 class="mb-2"><?php echo htmlspecialchars($item['titulo']); ?></h5>
                                        <p class="text-muted mb-2">
                                            <?php echo htmlspecialchars($item['descripcion'] ?? ''); ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span
                                                class="badge badge-<?php echo $item['estado'] === 'activo' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($item['estado']); ?>
                                            </span>
                                            <small class="text-muted">Orden: <?php echo $item['orden']; ?></small>
                                        </div>
                                    </div>
                                    <div class="overlay">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-warning btn-sm"
                                                onclick="editarItem(<?php echo $item['id']; ?>)">
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
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (empty($carrusel)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-images fa-5x text-muted mb-3"></i>
                            <h4 class="text-muted">No hay elementos en el carrusel</h4>
                            <p class="text-muted">Agrega tu primer elemento haciendo clic en el botón "Agregar Elemento"</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- Modal Agregar -->
        <div class="modal fade" id="modalAgregar" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="carrusel.php" method="post" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title">Agregar Elemento al Carrusel</h5>
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
                                <small class="text-muted">Recomendado: 1920x600px, máximo 5MB</small>
                            </div>

                            <div class="form-group">
                                <label>URL (opcional)</label>
                                <input type="url" name="url" class="form-control" placeholder="https://ejemplo.com">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Orden</label>
                                        <input type="number" name="orden" class="form-control" value="0" min="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Estado</label>
                                        <select name="estado" class="form-control">
                                            <option value="activo">Activo</option>
                                            <option value="inactivo">Inactivo</option>
                                        </select>
                                    </div>
                                </div>
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
                    <form action="carrusel.php" method="post" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title">Editar Elemento del Carrusel</h5>
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
                                <label> Nueva imagen (dejar vacío para mantener la actual)</label>
                                <input type="file" name="imagen" class="form-control" accept="image/*">
                            </div>

                            <div class="form-group">
                                <label>URL (opcional)</label>
                                <input type="url" name="url" id="edit_url" class="form-control"
                                    placeholder="https://ejemplo.com">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Orden</label>
                                        <input type="number" name="orden" id="edit_orden" class="form-control" min="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Estado</label>
                                        <select name="estado" id="edit_estado" class="form-control">
                                            <option value="activo">Activo</option>
                                            <option value="inactivo">Inactivo</option>
                                        </select>
                                    </div>
                                </div>
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

        <!-- Modal Ordenar -->
        <div class="modal fade" id="modalOrdenar" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="carrusel.php" method="post">
                        <div class="modal-header">
                            <h5 class="modal-title">Ordenar Elementos del Carrusel</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="action" value="update_order">

                            <div class="list-group">
                                <?php foreach ($carrusel as $item): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <img src="<?php echo UPLOADS_URL . $item['imagen']; ?>"
                                                alt="<?php echo htmlspecialchars($item['titulo']); ?>"
                                                style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px; margin-right: 10px;">
                                            <?php echo htmlspecialchars($item['titulo']); ?>
                                        </div>
                                        <input type="number" name="order[<?php echo $item['id']; ?>]"
                                            class="form-control order-input" value="<?php echo $item['orden']; ?>" min="0">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Orden
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
                        <p>¿Estás seguro de que deseas eliminar el elemento <strong id="eliminar_titulo"></strong>?</p>
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
        // Función para editar item
        function editarItem(id) {
            alert("Función editarItem llamada con ID: " + id);
            // Obtener datos del item
            $.get('ajax.php?action=get_carrusel&id=' + id, function(data) {
                if (data.success) {
                    $('#edit_id').val(data.data.id);
                    $('#edit_titulo').val(data.data.titulo);
                    $('#edit_descripcion').val(data.data.descripcion);
                    $('#edit_url').val(data.data.url);
                    $('#edit_orden').val(data.data.orden);
                    $('#edit_estado').val(data.data.estado);

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
            $('#eliminar_link').attr('href', 'carrusel.php?delete=' + id);
            $('#modalEliminar').modal('show');
        }

        // Previsualización de imagen al cargar
        $('input[type="file"]').on('change', function() {
            const input = this;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = $(input).parent().find('.image-preview');
                    if (preview.length) {
                        preview.html('<img src="' + e.target.result +
                            '" style="max-width: 200px; max-height: 100px; border-radius: 5px; margin-top: 10px;">'
                        );
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        });
    </script>
</body>

</html>