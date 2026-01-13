<?php
require_once __DIR__ . '/../config/config.php';

// Si ya esta logueado, redirigir
if (isLoggedIn()) {
    if (hasRole('super_admin')) {
        header('Location: eventos.php');
    } else {
        header('Location: mis-eventos.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Token CSRF invalido';
    } else {
        $identifier = cleanInput($_POST['username']);
        $password = $_POST['password'];

        // Intentar login primero como administrador (tabla administradores)
        $result = $auth->loginAdmin($identifier, $password);

        // Si falla, intentar como usuario del sistema de eventos (tabla usuarios)
        if (!$result['success']) {
            $result = $auth->login($identifier, $password);
        }

        if ($result['success']) {
            // Redirigir según el rol
            $user = Auth::getUser();

            if ($user['rol'] === 'superadmin' || $user['rol'] === 'super_admin') {
                header('Location: dashboard.php');
            } elseif ($user['rol'] === 'admin' || $user['rol'] === 'usuario') {
                header('Location: mis-eventos.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesion | <?php echo SITE_NAME; ?></title>

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

        .login-box {
            width: 420px;
        }

        .login-logo {
            color: var(--color-acento);
        }

        .login-card-body {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background-color: var(--color-primario);
            border-color: var(--color-primario);
        }

        .btn-primary:hover {
            background-color: var(--color-acento);
            border-color: var(--color-acento);
        }

        body {
            background: linear-gradient(135deg, var(--color-secundario) 0%, #f8f9fa 100%);
        }
    </style>
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo">
            <h1><b><?php echo SITE_NAME; ?></b></h1>
            <p class="text-muted">Sistema de Gestion de Eventos</p>
        </div>

        <div class="card login-card-body">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Inicia sesion para continuar</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="username" placeholder="Usuario o Email" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" name="password" placeholder="Contraseña" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">Iniciar Sesion</button>
                        </div>
                    </div>
                </form>

                <div class="mt-3 text-center">
                    <p class="text-muted">Usuario por defecto: <strong>admin</strong> / Contraseña:
                        <strong>admin123</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>

</html>