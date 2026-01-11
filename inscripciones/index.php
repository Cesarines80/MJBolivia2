<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

// Procesar formulario
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Token de seguridad inválido';
        $messageType = 'danger';
    } else {
        // Sanitizar datos
        $data = [
            'nombres' => cleanInput($_POST['nombres']),
            'apellidos' => cleanInput($_POST['apellidos']),
            'fecha_nacimiento' => cleanInput($_POST['fecha_nacimiento']),
            'iglesia' => cleanInput($_POST['iglesia']),
            'departamento' => cleanInput($_POST['departamento']),
            'sexo' => cleanInput($_POST['sexo']),
            'tipo_inscripcion' => cleanInput($_POST['tipo_inscripcion']),
            'alojamiento' => cleanInput($_POST['alojamiento'])
        ];

        // Validar campos requeridos
        $errors = [];
        if (empty($data['nombres'])) $errors[] = 'Los nombres son requeridos';
        if (empty($data['apellidos'])) $errors[] = 'Los apellidos son requeridos';
        if (empty($data['fecha_nacimiento'])) $errors[] = 'La fecha de nacimiento es requerida';
        if (empty($data['sexo'])) $errors[] = 'El sexo es requerido';
        if (empty($data['tipo_inscripcion'])) $errors[] = 'El tipo de inscripción es requerido';
        if (empty($data['alojamiento'])) $errors[] = 'Debe indicar si requiere alojamiento';

        // Verificar límite de inscripciones
        $config = Inscripciones::getConfig();
        if ($config['limite_inscripciones']) {
            $totalInscritos = $db->query("SELECT COUNT(*) FROM inscripciones WHERE estado = 'activo'")->fetchColumn();
            if ($totalInscritos >= $config['limite_inscripciones']) {
                $errors[] = 'Se ha alcanzado el límite de inscripciones';
            }
        }

        // Verificar fechas de inscripción
        $hoy = date('Y-m-d');
        if ($hoy < $config['fecha_inicio'] || $hoy > $config['fecha_fin']) {
            $errors[] = 'El período de inscripción no está activo';
        }

        if (empty($errors)) {
            if (Inscripciones::create($data)) {
                $message = '¡Inscripción realizada exitosamente! Te contactaremos pronto.';
                $messageType = 'success';
                $_POST = []; // Limpiar formulario
            } else {
                $message = 'Error al procesar la inscripción. Por favor, intenta de nuevo.';
                $messageType = 'danger';
            }
        } else {
            $message = implode('<br>', $errors);
            $messageType = 'danger';
        }
    }
}

// Obtener configuración
$config = Inscripciones::getConfig();
$stats = Inscripciones::getStats();

// Obtener configuración del sitio
$siteConfig = SiteConfig::get();

// Generar token CSRF
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Formulario de inscripción - <?php echo htmlspecialchars($siteConfig['nombre_institucion']); ?>">
    <title>Formulario de Inscripción - <?php echo htmlspecialchars($siteConfig['nombre_institucion']); ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --color-primario: <?php echo $siteConfig['color_primario'] ?? '#8B7EC8';
                                ?>;
            --color-secundario: <?php echo $siteConfig['color_secundario'] ?? '#B8B3D8';
                                ?>;
            --color-acento: <?php echo $siteConfig['color_acento'] ?? '#6B5B95';
                            ?>;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--color-primario), var(--color-secundario));
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }

        .form-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-header {
            background: linear-gradient(135deg, var(--color-primario), var(--color-acento));
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .form-header h1 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .form-body {
            padding: 2rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--color-primario);
            box-shadow: 0 0 0 0.2rem rgba(139, 126, 200, 0.25);
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--color-primario), var(--color-acento));
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .stats-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--color-primario);
        }

        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .info-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-section h5 {
            color: var(--color-primario);
            margin-bottom: 1rem;
        }

        .info-section ul {
            padding-left: 1.5rem;
        }

        .info-section li {
            margin-bottom: 0.5rem;
        }

        .footer-links {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e9ecef;
        }

        .footer-links a {
            color: var(--color-primario);
            text-decoration: none;
            margin: 0 1rem;
            font-weight: 500;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <h1><i class="fas fa-user-plus"></i> Formulario de Inscripción</h1>
                <p><?php echo htmlspecialchars($siteConfig['nombre_institucion']); ?></p>
            </div>

            <div class="form-body">
                <!-- Mensaje de respuesta -->
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3 col-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $stats['total'] ?? 0; ?></div>
                            <div class="stats-label">Total Inscritos</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $stats['masculino'] ?? 0; ?></div>
                            <div class="stats-label">Hombres</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $stats['femenino'] ?? 0; ?></div>
                            <div class="stats-label">Mujeres</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stats-card">
                            <div class="stats-number">Bs.
                                <?php echo number_format($stats['recaudacion_total'] ?? 0, 2); ?></div>
                            <div class="stats-label">Recaudado</div>
                        </div>
                    </div>
                </div>

                <!-- Información del evento -->
                <div class="info-section">
                    <h5><i class="fas fa-info-circle"></i> Información del Evento</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul>
                                <li><strong>Inscripción:</strong> Bs.
                                    <?php echo number_format($config['monto_inscripcion'] ?? 0, 2); ?></li>
                                <li><strong>Alojamiento:</strong> Bs.
                                    <?php echo number_format($config['monto_alojamiento'] ?? 0, 2); ?></li>
                                <li><strong>Fecha límite:</strong>
                                    <?php echo formatDate($config['fecha_fin'] ?? '', 'd/m/Y'); ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul>
                                <li><strong>Período:</strong>
                                    <?php echo formatDate($config['fecha_inicio'] ?? '', 'd/m/Y'); ?> -
                                    <?php echo formatDate($config['fecha_fin'] ?? '', 'd/m/Y'); ?></li>
                                <li><strong>Becas disponibles:</strong> Sujetas a evaluación</li>
                                <li><strong>Contacto:</strong>
                                    <?php echo htmlspecialchars($siteConfig['email_contacto'] ?? ''); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Formulario -->
                <form method="POST" action="index.php" id="formInscripcion">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="nombres" class="form-label">Nombres *</label>
                                <input type="text" name="nombres" id="nombres" class="form-control"
                                    placeholder="Ingresa tus nombres" required
                                    value="<?php echo isset($_POST['nombres']) ? htmlspecialchars($_POST['nombres']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="apellidos" class="form-label">Apellidos *</label>
                                <input type="text" name="apellidos" id="apellidos" class="form-control"
                                    placeholder="Ingresa tus apellidos" required
                                    value="<?php echo isset($_POST['apellidos']) ? htmlspecialchars($_POST['apellidos']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento *</label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control"
                                    required
                                    value="<?php echo isset($_POST['fecha_nacimiento']) ? htmlspecialchars($_POST['fecha_nacimiento']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="sexo" class="form-label">Sexo *</label>
                                <select name="sexo" id="sexo" class="form-control" required>
                                    <option value="">Selecciona tu sexo</option>
                                    <option value="Masculino"
                                        <?php echo (isset($_POST['sexo']) && $_POST['sexo'] === 'Masculino') ? 'selected' : ''; ?>>
                                        Masculino</option>
                                    <option value="Femenino"
                                        <?php echo (isset($_POST['sexo']) && $_POST['sexo'] === 'Femenino') ? 'selected' : ''; ?>>
                                        Femenino</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="iglesia" class="form-label">Iglesia</label>
                                <input type="text" name="iglesia" id="iglesia" class="form-control"
                                    placeholder="Nombre de tu iglesia local"
                                    value="<?php echo isset($_POST['iglesia']) ? htmlspecialchars($_POST['iglesia']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="departamento" class="form-label">Departamento</label>
                                <select name="departamento" id="departamento" class="form-control">
                                    <option value="">Selecciona tu departamento</option>
                                    <option value="Guatemala"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Guatemala') ? 'selected' : ''; ?>>
                                        Guatemala</option>
                                    <option value="Alta Verapaz"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Alta Verapaz') ? 'selected' : ''; ?>>
                                        Alta Verapaz</option>
                                    <option value="Baja Verapaz"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Baja Verapaz') ? 'selected' : ''; ?>>
                                        Baja Verapaz</option>
                                    <option value="Chimaltenango"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Chimaltenango') ? 'selected' : ''; ?>>
                                        Chimaltenango</option>
                                    <option value="Chiquimula"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Chiquimula') ? 'selected' : ''; ?>>
                                        Chiquimula</option>
                                    <option value="Petén"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Petén') ? 'selected' : ''; ?>>
                                        Petén</option>
                                    <option value="El Progreso"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'El Progreso') ? 'selected' : ''; ?>>
                                        El Progreso</option>
                                    <option value="Quiché"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Quiché') ? 'selected' : ''; ?>>
                                        Quiché</option>
                                    <option value="Escuintla"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Escuintla') ? 'selected' : ''; ?>>
                                        Escuintla</option>
                                    <option value="Huehuetenango"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Huehuetenango') ? 'selected' : ''; ?>>
                                        Huehuetenango</option>
                                    <option value="Izabal"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Izabal') ? 'selected' : ''; ?>>
                                        Izabal</option>
                                    <option value="Jalapa"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Jalapa') ? 'selected' : ''; ?>>
                                        Jalapa</option>
                                    <option value="Jutiapa"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Jutiapa') ? 'selected' : ''; ?>>
                                        Jutiapa</option>
                                    <option value="Quetzaltenango"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Quetzaltenango') ? 'selected' : ''; ?>>
                                        Quetzaltenango</option>
                                    <option value="Retalhuleu"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Retalhuleu') ? 'selected' : ''; ?>>
                                        Retalhuleu</option>
                                    <option value="Sacatepéquez"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Sacatepéquez') ? 'selected' : ''; ?>>
                                        Sacatepéquez</option>
                                    <option value="San Marcos"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'San Marcos') ? 'selected' : ''; ?>>
                                        San Marcos</option>
                                    <option value="Santa Rosa"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Santa Rosa') ? 'selected' : ''; ?>>
                                        Santa Rosa</option>
                                    <option value="Sololá"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Sololá') ? 'selected' : ''; ?>>
                                        Sololá</option>
                                    <option value="Suchitepéquez"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Suchitepéquez') ? 'selected' : ''; ?>>
                                        Suchitepéquez</option>
                                    <option value="Totonicapán"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Totonicapán') ? 'selected' : ''; ?>>
                                        Totonicapán</option>
                                    <option value="Zacapa"
                                        <?php echo (isset($_POST['departamento']) && $_POST['departamento'] === 'Zacapa') ? 'selected' : ''; ?>>
                                        Zacapa</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="tipo_inscripcion" class="form-label">Tipo de Inscripción *</label>
                                <select name="tipo_inscripcion" id="tipo_inscripcion" class="form-control" required>
                                    <option value="">Selecciona el tipo</option>
                                    <option value="efectivo"
                                        <?php echo (isset($_POST['tipo_inscripcion']) && $_POST['tipo_inscripcion'] === 'efectivo') ? 'selected' : ''; ?>>
                                        Efectivo</option>
                                    <option value="qr"
                                        <?php echo (isset($_POST['tipo_inscripcion']) && $_POST['tipo_inscripcion'] === 'qr') ? 'selected' : ''; ?>>
                                        QR</option>
                                    <option value="deposito"
                                        <?php echo (isset($_POST['tipo_inscripcion']) && $_POST['tipo_inscripcion'] === 'deposito') ? 'selected' : ''; ?>>
                                        Depósito</option>
                                    <option value="beca"
                                        <?php echo (isset($_POST['tipo_inscripcion']) && $_POST['tipo_inscripcion'] === 'beca') ? 'selected' : ''; ?>>
                                        Beca</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="alojamiento" class="form-label">¿Requiere Alojamiento? *</label>
                                <select name="alojamiento" id="alojamiento" class="form-control" required>
                                    <option value="">Selecciona una opción</option>
                                    <option value="Si"
                                        <?php echo (isset($_POST['alojamiento']) && $_POST['alojamiento'] === 'Si') ? 'selected' : ''; ?>>
                                        Sí</option>
                                    <option value="No"
                                        <?php echo (isset($_POST['alojamiento']) && $_POST['alojamiento'] === 'No') ? 'selected' : ''; ?>>
                                        No</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen de pago -->
                    <div class="alert alert-info" id="resumenPago" style="display: none;">
                        <h6><i class="fas fa-calculator"></i> Resumen de Pago</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1">Inscripción: <span id="montoInscripcion">Bs. 0.00</span></p>
                                <p class="mb-1">Alojamiento: <span id="montoAlojamiento">Bs. 0.00</span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Total: <span id="montoTotal">Bs. 0.00</span></strong></p>
                            </div>
                        </div>
                    </div>

                    <!-- Instrucciones de pago -->
                    <div class="info-section" id="instruccionesPago" style="display: none;">
                        <h5><i class="fas fa-info-circle"></i> Instrucciones de Pago</h5>
                        <div id="contenidoInstrucciones">
                            <?php echo nl2br(htmlspecialchars($config['instrucciones_pago'] ?? '')); ?>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus"></i> Realizar Inscripción
                        </button>
                    </div>
                </form>

                <!-- Enlaces de pie de página -->
                <div class="footer-links">
                    <a href="../index.php"><i class="fas fa-home"></i> Inicio</a>
                    <a href="reportes.php"><i class="fas fa-list"></i> Ver Inscritos</a>
                    <a href="../admin/login.php"><i class="fas fa-lock"></i> Administración</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Calcular montos automáticamente
        function calcularMonto() {
            const tipoInscripcion = document.getElementById('tipo_inscripcion').value;
            const alojamiento = document.getElementById('alojamiento').value;

            if (!tipoInscripcion || !alojamiento) {
                document.getElementById('resumenPago').style.display = 'none';
                document.getElementById('instruccionesPago').style.display = 'none';
                return;
            }

            const config = {
                monto_inscripcion: <?php echo $config['monto_inscripcion'] ?? 0; ?>,
                monto_alojamiento: <?php echo $config['monto_alojamiento'] ?? 0; ?>
            };

            let montoInscripcion = config.monto_inscripcion;
            let montoAlojamiento = alojamiento === 'Si' ? config.monto_alojamiento : 0;
            let montoTotal = montoInscripcion + montoAlojamiento;

            // Si es beca, el monto es 0
            if (tipoInscripcion === 'beca') {
                montoInscripcion = 0;
                montoAlojamiento = 0;
                montoTotal = 0;
            }

            document.getElementById('montoInscripcion').textContent = 'Bs. ' + montoInscripcion.toFixed(2);
            document.getElementById('montoAlojamiento').textContent = 'Bs. ' + montoAlojamiento.toFixed(2);
            document.getElementById('montoTotal').textContent = 'Bs. ' + montoTotal.toFixed(2);

            document.getElementById('resumenPago').style.display = 'block';

            // Mostrar instrucciones si no es beca
            if (tipoInscripcion !== 'beca') {
                document.getElementById('instruccionesPago').style.display = 'block';
            } else {
                document.getElementById('instruccionesPago').style.display = 'none';
            }
        }

        // Event listeners
        document.getElementById('tipo_inscripcion').addEventListener('change', calcularMonto);
        document.getElementById('alojamiento').addEventListener('change', calcularMonto);

        // Validar fecha de nacimiento (mayor de 12 años)
        document.getElementById('fecha_nacimiento').addEventListener('change', function() {
            const fechaNacimiento = new Date(this.value);
            const hoy = new Date();
            const edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
            const mes = hoy.getMonth() - fechaNacimiento.getMonth();

            if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
                edad--;
            }

            if (edad < 12) {
                alert('Debes tener al menos 12 años para inscribirte.');
                this.value = '';
            }
        });

        // Prevenir envío si hay campos vacíos
        document.getElementById('formInscripcion').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Por favor, completa todos los campos requeridos.');
            }
        });
    </script>
</body>

</html>