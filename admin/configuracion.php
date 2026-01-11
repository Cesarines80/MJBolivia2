<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Requerir autenticación
Auth::requireLogin();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Token de seguridad inválido';
        redirect('configuracion.php');
    }
    
    $data = [
        'nombre_institucion' => cleanInput($_POST['nombre_institucion']),
        'descripcion' => cleanInput($_POST['descripcion'] ?? ''),
        'email_contacto' => cleanInput($_POST['email_contacto'] ?? ''),
        'telefono' => cleanInput($_POST['telefono'] ?? ''),
        'direccion' => cleanInput($_POST['direccion'] ?? ''),
        'facebook' => cleanInput($_POST['facebook'] ?? ''),
        'twitter' => cleanInput($_POST['twitter'] ?? ''),
        'instagram' => cleanInput($_POST['instagram'] ?? ''),
        'youtube' => cleanInput($_POST['youtube'] ?? ''),
        'color_primario' => cleanInput($_POST['color_primario'] ?? '#8B7EC8'),
        'color_secundario' => cleanInput($_POST['color_secundario'] ?? '#B8B3D8'),
        'color_acento' => cleanInput($_POST['color_acento'] ?? '#6B5B95'),
        'metadescription' => cleanInput($_POST['metadescription'] ?? ''),
        'metakeywords' => cleanInput($_POST['metakeywords'] ?? ''),
        'analytics_id' => cleanInput($_POST['analytics_id'] ?? '')
    ];
    
    // Subir logo
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['logo']);
        if ($upload['success']) {
            $data['logo'] = $upload['filename'];
        } else {
            $_SESSION['error'] = 'Error al subir logo: ' . $upload['error'];
        }
    }
    
    // Subir favicon
    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['favicon']);
        if ($upload['success']) {
            $data['favicon'] = $upload['filename'];
        } else {
            $_SESSION['error'] = 'Error al subir favicon: ' . $upload['error'];
        }
    }
    
    if (SiteConfig::update($data)) {
        logActivity('UPDATE_CONFIG', 'Configuración general actualizada');
        $_SESSION['success'] = 'Configuración actualizada correctamente';
    } else {
        $_SESSION['error'] = 'Error al actualizar la configuración';
    }
    
    redirect('configuracion.php');
}

// Obtener configuración actual
$config = SiteConfig::get();

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
    <title>Configuración - Administración</title>

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
        
        .color-picker {
            width: 50px;
            height: 38px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .preview-logo {
            max-width: 200px;
            max-height: 100px;
            border-radius: 5px;
            border: 2px solid #e9ecef;
        }
        
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #8B7EC8, #6B5B95);
            color: white;
            border-color: #8B7EC8;
        }
        
        .form-group label {
            font-weight: 600;
            color: #495057;
        }
        
        .card-primary.card-outline {
            border-top: 3px solid #8B7EC8;
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
                        <a href="configuracion.php" class="nav-link active">
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
                        <h1 class="m-0">Configuración General</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Configuración</li>
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

                <form action="configuracion.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Información General</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="nombre_institucion">Nombre de la Institución *</label>
                                        <input type="text" name="nombre_institucion" id="nombre_institucion" 
                                               class="form-control" value="<?php echo htmlspecialchars($config['nombre_institucion']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="descripcion">Descripción</label>
                                        <textarea name="descripcion" id="descripcion" class="form-control" 
                                                  rows="3"><?php echo htmlspecialchars($config['descripcion'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email_contacto">Email de Contacto</label>
                                                <input type="email" name="email_contacto" id="email_contacto" 
                                                       class="form-control" value="<?php echo htmlspecialchars($config['email_contacto'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="telefono">Teléfono</label>
                                                <input type="tel" name="telefono" id="telefono" 
                                                       class="form-control" value="<?php echo htmlspecialchars($config['telefono'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="direccion">Dirección</label>
                                        <textarea name="direccion" id="direccion" class="form-control" 
                                                  rows="2"><?php echo htmlspecialchars($config['direccion'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card card-info card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Redes Sociales</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="facebook"><i class="fab fa-facebook text-primary"></i> Facebook</label>
                                        <input type="url" name="facebook" id="facebook" class="form-control" 
                                               placeholder="https://facebook.com/tu-pagina"
                                               value="<?php echo htmlspecialchars($config['facebook'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="twitter"><i class="fab fa-twitter text-info"></i> Twitter</label>
                                        <input type="url" name="twitter" id="twitter" class="form-control" 
                                               placeholder="https://twitter.com/tu-usuario"
                                               value="<?php echo htmlspecialchars($config['twitter'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="instagram"><i class="fab fa-instagram text-danger"></i> Instagram</label>
                                        <input type="url" name="instagram" id="instagram" class="form-control" 
                                               placeholder="https://instagram.com/tu-usuario"
                                               value="<?php echo htmlspecialchars($config['instagram'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="youtube"><i class="fab fa-youtube text-danger"></i> YouTube</label>
                                        <input type="url" name="youtube" id="youtube" class="form-control" 
                                               placeholder="https://youtube.com/tu-canal"
                                               value="<?php echo htmlspecialchars($config['youtube'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card card-success card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">SEO y Analytics</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="metadescription">Meta Descripción</label>
                                        <textarea name="metadescription" id="metadescription" class="form-control" 
                                                  rows="3" maxlength="160"><?php echo htmlspecialchars($config['metadescription'] ?? ''); ?></textarea>
                                        <small class="text-muted">Máximo 160 caracteres para SEO</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="metakeywords">Meta Keywords</label>
                                        <input type="text" name="metakeywords" id="metakeywords" class="form-control" 
                                               placeholder="palabra1, palabra2, palabra3"
                                               value="<?php echo htmlspecialchars($config['metakeywords'] ?? ''); ?>">
                                        <small class="text-muted">Separadas por comas</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="analytics_id">Google Analytics ID</label>
                                        <input type="text" name="analytics_id" id="analytics_id" class="form-control" 
                                               placeholder="G-XXXXXXXXXX"
                                               value="<?php echo htmlspecialchars($config['analytics_id'] ?? ''); ?>">
                                        <small class="text-muted">Ejemplo: G-XXXXXXXXXX</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card card-warning card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Imágenes y Diseño</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="logo">Logo de la Institución</label>
                                        <input type="file" name="logo" id="logo" class="form-control" accept="image/*">
                                        <?php if ($config['logo']): ?>
                                        <div class="mt-2">
                                            <img src="<?php echo UPLOADS_URL . $config['logo']; ?>" 
                                                 alt="Logo actual" class="preview-logo">
                                            <p class="text-muted mt-1">Logo actual</p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="favicon">Favicon</label>
                                        <input type="file" name="favicon" id="favicon" class="form-control" accept="image/x-icon,image/png">
                                        <?php if ($config['favicon']): ?>
                                        <div class="mt-2">
                                            <img src="<?php echo UPLOADS_URL . $config['favicon']; ?>" 
                                                 alt="Favicon actual" style="max-width: 32px; max-height: 32px;">
                                            <p class="text-muted mt-1">Favicon actual</p>
                                        </div>
                                        <?php endif; ?>
                                        <small class="text-muted">Formato .ico o .png, tamaño 32x32px</small>
                                    </div>
                                    
                                    <hr>
                                    
                                    <h5 class="text-center mb-3">Colores del Tema</h5>
                                    
                                    <div class="form-group">
                                        <label>Color Primario</label>
                                        <div class="input-group">
                                            <input type="color" name="color_primario" id="color_primario" 
                                                   class="color-picker" value="<?php echo $config['color_primario'] ?? '#8B7EC8'; ?>">
                                            <input type="text" class="form-control ml-2" 
                                                   value="<?php echo $config['color_primario'] ?? '#8B7EC8'; ?>" 
                                                   onchange="document.getElementById('color_primario').value = this.value">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Color Secundario</label>
                                        <div class="input-group">
                                            <input type="color" name="color_secundario" id="color_secundario" 
                                                   class="color-picker" value="<?php echo $config['color_secundario'] ?? '#B8B3D8'; ?>">
                                            <input type="text" class="form-control ml-2" 
                                                   value="<?php echo $config['color_secundario'] ?? '#B8B3D8'; ?>" 
                                                   onchange="document.getElementById('color_secundario').value = this.value">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Color de Acento</label>
                                        <div class="input-group">
                                            <input type="color" name="color_acento" id="color_acento" 
                                                   class="color-picker" value="<?php echo $config['color_acento'] ?? '#6B5B95'; ?>">
                                            <input type="text" class="form-control ml-2" 
                                                   value="<?php echo $config['color_acento'] ?? '#6B5B95'; ?>" 
                                                   onchange="document.getElementById('color_acento').value = this.value">
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 p-3 rounded" style="background: <?php echo $config['color_primario'] ?? '#8B7EC8'; ?>;">
                                        <h6 class="text-white mb-2">Vista previa del color primario</h6>
                                        <p class="text-white small mb-0">Este es el color principal que se utilizará en el sitio web</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save"></i> Guardar Configuración
                                    </button>
                                    <a href="../index.php" target="_blank" class="btn btn-info btn-lg ml-2">
                                        <i class="fas fa-eye"></i> Ver Cambios
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
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
    // Sincronizar color pickers con inputs de texto
    document.getElementById('color_primario').addEventListener('change', function() {
        this.nextElementSibling.value = this.value;
        actualizarPreview();
    });
    
    document.getElementById('color_secundario').addEventListener('change', function() {
        this.nextElementSibling.value = this.value;
    });
    
    document.getElementById('color_acento').addEventListener('change', function() {
        this.nextElementSibling.value = this.value;
    });
    
    function actualizarPreview() {
        const color = document.getElementById('color_primario').value;
        const preview = document.querySelector('.mt-4.p-3.rounded');
        preview.style.background = color;
    }
    
    // Previsualización de imágenes
    document.getElementById('logo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                let preview = document.querySelector('.preview-logo');
                if (!preview) {
                    const container = e.target.parentNode;
                    preview = document.createElement('img');
                    preview.className = 'preview-logo mt-2';
                    container.appendChild(preview);
                }
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });
    
    document.getElementById('favicon').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                let preview = document.querySelector('[style*=\"32px\"]');
                if (!preview) {
                    const container = e.target.parentNode;
                    preview = document.createElement('img');
                    preview.style.maxWidth = '32px';
                    preview.style.maxHeight = '32px';
                    preview.className = 'mt-2';
                    container.appendChild(preview);
                }
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });
</script>
</body>
</html>