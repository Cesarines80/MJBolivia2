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
        $_SESSION['error'] = 'Token CSRF inválido';
        header('Location: usuarios.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'crear_usuario':
            $data = [
                'username' => cleanInput($_POST['username']),
                'email' => cleanInput($_POST['email']),
                'password' => $_POST['password'],
                'nombre_completo' => cleanInput($_POST['nombre_completo']),
                'rol' => $_POST['rol'] ?? 'admin',
                'activo' => isset($_POST['activo']) ? 1 : 0
            ];

            $auth = new Auth($db);
            $result = $auth->register($data);

            if ($result['success']) {
                $_SESSION['success'] = 'Usuario creado exitosamente';
            } else {
                $_SESSION['error'] = $result['message'] ?? 'Error al crear el usuario';
            }
            break;

        case 'asignar_evento':
            $eventoId = intval($_POST['evento_id']);
            $usuarioId = intval($_POST['usuario_id']);
            $currentUserId = $_SESSION['admin_id'] ?? $_SESSION['user_id'];

            $result = $eventosManager->assignAdmin($eventoId, $usuarioId, $currentUserId);

            if ($result) {
                $_SESSION['success'] = 'Usuario asignado al evento exitosamente';
            } else {
                $_SESSION['error'] = 'Error al asignar el usuario al evento';
            }
            break;

        case 'remover_evento':
            $eventoId = intval($_POST['evento_id']);
            $usuarioId = intval($_POST['usuario_id']);

            $result = $eventosManager->removeAdmin($eventoId, $usuarioId);

            if ($result) {
                $_SESSION['success'] = 'Usuario removido del evento exitosamente';
            } else {
                $_SESSION['error'] = 'Error al remover el usuario del evento';
            }
            break;

        case 'editar_usuario':
            $usuarioId = intval($_POST['usuario_id']);
            $stmt = $db->prepare("
                UPDATE usuarios 
                SET nombre_completo = ?, email = ?, rol = ?, activo = ?
                WHERE id = ?
            ");

            $result = $stmt->execute([
                cleanInput($_POST['nombre_completo']),
                cleanInput($_POST['email']),
                $_POST['rol'],
                isset($_POST['activo']) ? 1 : 0,
                $usuarioId
            ]);

            if ($result) {
                $_SESSION['success'] = 'Usuario actualizado exitosamente';
            } else {
                $_SESSION['error'] = 'Error al actualizar el usuario';
            }
            break;

        case 'cambiar_password':
            $usuarioId = intval($_POST['usuario_id']);
            $newPassword = $_POST['new_password'];

            $auth = new Auth($db);
            $result = $auth->resetPassword($usuarioId, $newPassword);

            if ($result['success']) {
                $_SESSION['success'] = 'Contraseña actualizada exitosamente';
            } else {
                $_SESSION['error'] = 'Error al actualizar la contraseña';
            }
            break;
    }

    header('Location: usuarios.php');
    exit;
}

// Obtener todos los usuarios
$stmt = $db->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM eventos_administradores ea WHERE ea.usuario_id = u.id AND ea.activo = 1) as eventos_asignados
    FROM usuarios u
    ORDER BY u.fecha_creacion DESC
");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los eventos
$eventos = $eventosManager->getAll();

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
    <title>Gestión de Usuarios | <?php echo SITE_NAME; ?></title>

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

        .modal-header {
            background-color: var(--color-primario);
            color: white;
        }

        .badge-super_admin {
            background-color: #dc3545;
        }

        .badge-admin {
            background-color: #ffc107;
        }

        .badge-usuario {
            background-color: #6c757d;
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
                    <a href="dashboard.php" class="nav-link">Inicio</a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="usuarios.php" class="nav-link">Usuarios</a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php" role="button">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="dashboard.php" class="brand-link">
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
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="eventos.php" class="nav-link">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <p>Gestión de Eventos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="usuarios.php" class="nav-link active">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Gestión de Usuarios</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="mis-eventos.php" class="nav-link">
                                <i class="nav-icon fas fa-calendar-check"></i>
                                <p>Mis Eventos</p>
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
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Gestión de Usuarios</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="dashboard.php">Inicio</a></li>
                                <li class="breadcrumb-item active">Usuarios</li>
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
                                data-target="#modalCrearUsuario">
                                <i class="fas fa-user-plus"></i> Crear Nuevo Usuario
                            </button>
                        </div>
                    </div>

                    <!-- Lista de Usuarios -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Lista de Usuarios</h3>
                        </div>
                        <div class="card-body">
                            <table id="tablaUsuarios" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Nombre Completo</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Eventos Asignados</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td><?php echo $usuario['id']; ?></td>
                                            <td><?php echo htmlspecialchars($usuario['username']); ?></td>
                                            <td><?php echo htmlspecialchars($usuario['nombre_completo']); ?></td>
                                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $usuario['rol']; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $usuario['rol'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-<?php echo $usuario['activo'] ? 'success' : 'danger'; ?>">
                                                    <?php echo $usuario['activo'] ? 'Activo' : 'Inactivo'; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge badge-info"><?php echo $usuario['eventos_asignados']; ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-info"
                                                        onclick="verEventosUsuario(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['nombre_completo']); ?>')">
                                                        <i class="fas fa-calendar-alt"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                        onclick="editarUsuario(<?php echo htmlspecialchars(json_encode($usuario)); ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-secondary"
                                                        onclick="cambiarPassword(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['username']); ?>')">
                                                        <i class="fas fa-key"></i>
                                                    </button>
                                                </div>
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

        <!-- Footer -->
        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Versión</b> 2.0.0
            </div>
            <strong>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?></strong>
        </footer>
    </div>

    <!-- Modal Crear Usuario -->
    <div class="modal fade" id="modalCrearUsuario" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Nuevo Usuario</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="crear_usuario">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nombre de Usuario *</label>
                                    <input type="text" class="form-control" name="username" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email *</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Nombre Completo *</label>
                            <input type="text" class="form-control" name="nombre_completo" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Contraseña *</label>
                                    <input type="password" class="form-control" name="password" required minlength="6">
                                    <small class="form-text text-muted">Mínimo 6 caracteres</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Rol *</label>
                                    <select class="form-control" name="rol" required>
                                        <option value="admin">Administrador</option>
                                        <option value="usuario">Usuario</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="activo" id="activoCrear" checked>
                            <label class="form-check-label" for="activoCrear">
                                Usuario Activo
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Usuario -->
    <div class="modal fade" id="modalEditarUsuario" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Usuario</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="editar_usuario">
                    <input type="hidden" name="usuario_id" id="editUsuarioId">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nombre Completo *</label>
                            <input type="text" class="form-control" name="nombre_completo" id="editNombreCompleto"
                                required>
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" class="form-control" name="email" id="editEmail" required>
                        </div>
                        <div class="form-group">
                            <label>Rol *</label>
                            <select class="form-control" name="rol" id="editRol" required>
                                <option value="super_admin">Super Administrador</option>
                                <option value="admin">Administrador</option>
                                <option value="usuario">Usuario</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="activo" id="editActivo">
                            <label class="form-check-label" for="editActivo">
                                Usuario Activo
                            </label>
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

    <!-- Modal Cambiar Contraseña -->
    <div class="modal fade" id="modalCambiarPassword" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cambiar Contraseña</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="cambiar_password">
                    <input type="hidden" name="usuario_id" id="passwordUsuarioId">
                    <div class="modal-body">
                        <p>Usuario: <strong id="passwordUsername"></strong></p>
                        <div class="form-group">
                            <label>Nueva Contraseña *</label>
                            <input type="password" class="form-control" name="new_password" required minlength="6">
                            <small class="form-text text-muted">Mínimo 6 caracteres</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Ver Eventos del Usuario -->
    <div class="modal fade" id="modalEventosUsuario" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Eventos Asignados - <span id="eventosUsuarioNombre"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="eventosUsuarioId">

                    <!-- Asignar Nuevo Evento -->
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">Asignar Nuevo Evento</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="form-inline">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="action" value="asignar_evento">
                                <input type="hidden" name="usuario_id" id="asignarUsuarioId">
                                <div class="form-group mr-2">
                                    <select class="form-control" name="evento_id" required>
                                        <option value="">Seleccionar Evento...</option>
                                        <?php foreach ($eventos as $evento): ?>
                                            <option value="<?php echo $evento['id']; ?>">
                                                <?php echo htmlspecialchars($evento['titulo']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Asignar
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Lista de Eventos Asignados -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Eventos Actualmente Asignados</h6>
                        </div>
                        <div class="card-body">
                            <div id="listaEventosAsignados">
                                <p class="text-center text-muted">Cargando...</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tablaUsuarios').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.4/i18n/es_es.json"
                },
                "order": [
                    [0, "desc"]
                ]
            });
        });

        function editarUsuario(usuario) {
            $('#editUsuarioId').val(usuario.id);
            $('#editNombreCompleto').val(usuario.nombre_completo);
            $('#editEmail').val(usuario.email);
            $('#editRol').val(usuario.rol);
            $('#editActivo').prop('checked', usuario.activo == 1);
            $('#modalEditarUsuario').modal('show');
        }

        function cambiarPassword(usuarioId, username) {
            $('#passwordUsuarioId').val(usuarioId);
            $('#passwordUsername').text(username);
            $('#modalCambiarPassword').modal('show');
        }

        function verEventosUsuario(usuarioId, nombreCompleto) {
            $('#eventosUsuarioId').val(usuarioId);
            $('#eventosUsuarioNombre').text(nombreCompleto);
            $('#asignarUsuarioId').val(usuarioId);

            // Cargar eventos asignados via AJAX
            $.ajax({
                url: 'ajax.php',
                type: 'GET',
                data: {
                    action: 'get_eventos_usuario',
                    usuario_id: usuarioId
                },
                success: function(response) {
                    if (response.success) {
                        mostrarEventosAsignados(response.eventos);
                    } else {
                        $('#listaEventosAsignados').html('<p class="text-danger">Error al cargar eventos</p>');
                    }
                },
                error: function() {
                    $('#listaEventosAsignados').html('<p class="text-danger">Error de conexión</p>');
                }
            });

            $('#modalEventosUsuario').modal('show');
        }

        function mostrarEventosAsignados(eventos) {
            if (eventos.length === 0) {
                $('#listaEventosAsignados').html('<p class="text-muted text-center">No hay eventos asignados</p>');
                return;
            }

            let html = '<div class="table-responsive"><table class="table table-sm table-striped">';
            html +=
                '<thead><tr><th>Evento</th><th>Fecha Asignación</th><th>Asignado Por</th><th>Acción</th></tr></thead><tbody>';

            eventos.forEach(function(evento) {
                html += '<tr>';
                html += '<td>' + evento.titulo + '</td>';
                html += '<td>' + evento.fecha_asignacion + '</td>';
                html += '<td>' + (evento.asignado_por_nombre || 'Sistema') + '</td>';
                html += '<td>';
                html +=
                    '<form method="POST" style="display:inline;" onsubmit="return confirm(\'¿Está seguro de remover este evento?\')">';
                html += '<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">';
                html += '<input type="hidden" name="action" value="remover_evento">';
                html += '<input type="hidden" name="evento_id" value="' + evento.evento_id + '">';
                html += '<input type="hidden" name="usuario_id" value="' + evento.usuario_id + '">';
                html += '<button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>';
                html += '</form>';
                html += '</td>';
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            $('#listaEventosAsignados').html(html);
        }
    </script>
</body>

</html>