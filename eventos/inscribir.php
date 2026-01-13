<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/eventos.php';

// Obtener ID del evento
$eventoId = isset($_GET['evento']) ? intval($_GET['evento']) : 0;

if ($eventoId <= 0) {
    die('Evento no especificado');
}

$eventosManager = new EventosManager($db);

// Obtener evento
$evento = $eventosManager->getById($eventoId);

if (!$evento) {
    die('Evento no encontrado');
}

// Verificar si el evento esta activo
if ($evento['estado'] !== 'activo') {
    die('Evento no esta activo');
}

// Verificar si esta en periodo de inscripcion
$hoy = date('Y-m-d');
$inscripcionAbierta = ($hoy >= $evento['fecha_inicio_inscripcion'] && $hoy <= $evento['fecha_fin_inscripcion']);

// Permitir acceso a administradores del evento incluso fuera del periodo de inscripcion
$esAdminEvento = isLoggedIn() && canAccessEvent($eventoId);
$permitirInscripcion = $inscripcionAbierta || $esAdminEvento;

// Obtener configuracion
$config = $eventosManager->getConfig($eventoId);

// Procesar formulario
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Token CSRF invalido';
    } else {
        $inscripcionesEvento = new InscripcionesEvento($db, $eventoId);

        $data = [
            'nombres' => cleanInput($_POST['nombres']),
            'apellidos' => cleanInput($_POST['apellidos']),
            'email' => cleanInput($_POST['email']),
            'telefono' => cleanInput($_POST['telefono']),
            'fecha_nacimiento' => $_POST['fecha_nacimiento'],
            'iglesia' => cleanInput($_POST['iglesia']),
            'departamento' => cleanInput($_POST['departamento']),
            'sexo' => $_POST['sexo'],
            'tipo_inscripcion' => $_POST['tipo_inscripcion'],
            'monto_pagado' => floatval($_POST['monto_pagado']),
            'alojamiento' => $_POST['alojamiento'],
            'codigo_pago' => isset($_POST['codigo_pago']) ? cleanInput($_POST['codigo_pago']) : null
        ];

        $result = $inscripcionesEvento->create($data);

        if ($result['success']) {
            $success = 'Inscripcion realizada exitosamente. Tu codigo de inscripcion es: <strong>' . $result['codigo'] . '</strong>';
        } else {
            $error = $result['message'] ?? 'Error al procesar la inscripcion';
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
    <title>Inscripcion - <?php echo htmlspecialchars($evento['nombre']); ?> | <?php echo SITE_NAME; ?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --color-primario: #8B7EC8;
            --color-secundario: #B8B3D8;
            --color-acento: #6B5B95;
        }

        body {
            background: linear-gradient(135deg, var(--color-secundario) 0%, #f8f9fa 100%);
            min-height: 100vh;
        }

        .card {
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .card-header {
            background: linear-gradient(135deg, var(--color-primario) 0%, var(--color-acento) 100%);
            color: white;
            border-radius: 20px 20px 0 0 !important;
            padding: 2rem;
            text-align: center;
        }

        .btn-primary {
            background-color: var(--color-primario);
            border-color: var(--color-primario);
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: var(--color-acento);
            border-color: var(--color-acento);
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--color-primario);
            box-shadow: 0 0 0 0.2rem rgba(139, 126, 200, 0.25);
        }

        .info-box {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }

        .info-box i {
            color: var(--color-primario);
            font-size: 1.5rem;
            margin-right: 10px;
        }

        .inscripcion-cerrada {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Encabezado del Evento -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h1 class="h3 mb-2"><?php echo htmlspecialchars($evento['nombre']); ?></h1>
                        <p class="mb-0 opacity-75">Formulario de Inscripcion</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <i class="fas fa-calendar"></i>
                                    <strong>Inscripciones:</strong><br>
                                    <?php echo formatDate($evento['fecha_inicio_inscripcion']); ?> -
                                    <?php echo formatDate($evento['fecha_fin_inscripcion']); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <strong>Lugar:</strong><br>
                                    <?php echo htmlspecialchars($evento['lugar'] ?? 'No especificado'); ?>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($evento['descripcion'])): ?>
                            <div class="info-box">
                                <i class="fas fa-info-circle"></i>
                                <strong>Descripcion:</strong><br>
                                <?php echo nl2br(htmlspecialchars($evento['descripcion'])); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Precios -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <i class="fas fa-money-bill"></i>
                                    <strong>Costo de Inscripción:</strong> Bs.
                                    <?php echo number_format($evento['costo_inscripcion'] ?? 0, 2); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <i class="fas fa-bed"></i>
                                    <strong>Costo de Alojamiento:</strong> Bs.
                                    <?php echo number_format($evento['costo_alojamiento'] ?? 0, 2); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!$permitirInscripcion): ?>
                    <!-- Inscripcion Cerrada -->
                    <div class="card inscripcion-cerrada">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-lock fa-5x mb-4"></i>
                            <h3 class="mb-3">Inscripciones Cerradas</h3>
                            <p>El periodo de inscripcion para este evento ha finalizado.</p>
                            <p class="text-muted">Fecha de cierre:
                                <?php echo formatDate($evento['fecha_fin_inscripcion']); ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Formulario de Inscripcion -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="h5 mb-0">Formulario de Inscripcion</h3>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" id="formInscripcion">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="nombres" class="form-label">Nombres *</label>
                                        <input type="text" class="form-control" id="nombres" name="nombres" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="apellidos" class="form-label">Apellidos *</label>
                                        <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">
                                            Email
                                            <small class="text-muted">(Opcional)</small>
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="telefono" class="form-label">
                                            Teléfono
                                            <small class="text-muted">(Opcional)</small>
                                        </label>
                                        <input type="tel" class="form-control" id="telefono" name="telefono">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento *</label>
                                        <input type="date" class="form-control" id="fecha_nacimiento"
                                            name="fecha_nacimiento" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="sexo" class="form-label">Sexo *</label>
                                        <select class="form-select" id="sexo" name="sexo" required>
                                            <option value="">Seleccione</option>
                                            <option value="Masculino">Masculino</option>
                                            <option value="Femenino">Femenino</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="iglesia" class="form-label">Iglesia</label>
                                        <input type="text" class="form-control" id="iglesia" name="iglesia"
                                            placeholder="Nombre de su iglesia">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="departamento" class="form-label">Departamento</label>
                                        <input type="text" class="form-control" id="departamento" name="departamento"
                                            placeholder="Departamento/Estado">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="tipo_inscripcion" class="form-label">Tipo de Pago *</label>
                                        <select class="form-select" id="tipo_inscripcion" name="tipo_inscripcion" required
                                            onchange="calcularMonto()">
                                            <option value="">Seleccione</option>
                                            <option value="Efectivo">Efectivo</option>
                                            <option value="QR">QR</option>
                                            <option value="Deposito">Depósito</option>
                                            <option value="Beca">Beca</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="alojamiento" class="form-label">
                                            ¿Requiere Alojamiento? *
                                            <small class="text-muted">(+Bs.
                                                <?php echo number_format($evento['costo_alojamiento'] ?? 0, 2); ?>)</small>
                                        </label>
                                        <select class="form-select" id="alojamiento" name="alojamiento" required
                                            onchange="calcularMonto()">
                                            <option value="No">No</option>
                                            <option value="Si">Sí (+Bs.
                                                <?php echo number_format($evento['costo_alojamiento'] ?? 0, 2); ?>)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Campo para Código de Pago (QR/Depósito) -->
                                <div class="row mb-3" id="campo_codigo_pago" style="display: none;">
                                    <div class="col-md-12">
                                        <label for="codigo_pago" class="form-label">
                                            <span id="label_codigo_pago">Código de Pago</span> *
                                        </label>
                                        <input type="text" class="form-control" id="codigo_pago" name="codigo_pago"
                                            placeholder="Ingrese el código de transacción">
                                        <small class="form-text text-muted" id="help_codigo_pago">
                                            Ingrese el código de la transacción realizada
                                        </small>
                                    </div>
                                </div>

                                <!-- Resumen de Costos -->
                                <div class="alert alert-info mb-3">
                                    <h6 class="mb-2"><i class="fas fa-calculator"></i> Resumen de Costos</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <strong>Costo de Inscripción:</strong>
                                        </div>
                                        <div class="col-6 text-end">
                                            <span id="costo_inscripcion_display">Bs.
                                                <?php echo number_format($evento['costo_inscripcion'] ?? 0, 2); ?></span>
                                        </div>
                                    </div>
                                    <div class="row" id="row_alojamiento" style="display: none;">
                                        <div class="col-6">
                                            <strong>Costo de Alojamiento:</strong>
                                        </div>
                                        <div class="col-6 text-end">
                                            <span id="costo_alojamiento_display">Bs.
                                                <?php echo number_format($evento['costo_alojamiento'] ?? 0, 2); ?></span>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-6">
                                            <strong>TOTAL A PAGAR:</strong>
                                        </div>
                                        <div class="col-6 text-end">
                                            <strong><span id="total_display" style="font-size: 1.2em; color: #6B5B95;">Bs.
                                                    <?php echo number_format($evento['costo_inscripcion'] ?? 0, 2); ?></span></strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="monto_pagado" class="form-label">
                                            Monto que Pagará *
                                            <small class="text-muted">(Se calcula automáticamente)</small>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Bs.</span>
                                            <input type="number" step="0.01" class="form-control" id="monto_pagado"
                                                name="monto_pagado" required readonly
                                                style="background-color: #f8f9fa; font-weight: bold; font-size: 1.1em;">
                                        </div>
                                        <small class="form-text text-muted">
                                            Este monto se calcula automáticamente según la inscripción y alojamiento
                                            seleccionado
                                        </small>
                                    </div>
                                </div>

                                <?php if (!empty($config['instrucciones_pago'])): ?>
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle"></i> Instrucciones de Pago</h6>
                                        <?php echo nl2br(htmlspecialchars($config['instrucciones_pago'])); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-check-circle"></i> Completar Inscripcion
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function calcularMonto() {
            var tipoInscripcion = document.getElementById('tipo_inscripcion').value;
            var alojamiento = document.getElementById('alojamiento').value;
            var montoPagado = document.getElementById('monto_pagado');
            var totalDisplay = document.getElementById('total_display');
            var rowAlojamiento = document.getElementById('row_alojamiento');
            var costoInscripcionDisplay = document.getElementById('costo_inscripcion_display');
            var campoCodigo = document.getElementById('campo_codigo_pago');
            var inputCodigo = document.getElementById('codigo_pago');
            var labelCodigo = document.getElementById('label_codigo_pago');
            var helpCodigo = document.getElementById('help_codigo_pago');

            // Usar los costos del evento específico
            var costoInscripcion = <?php echo $evento['costo_inscripcion'] ?? 0; ?>;
            var costoAlojamiento = <?php echo $evento['costo_alojamiento'] ?? 0; ?>;

            var total = costoInscripcion;
            var costoInscripcionActual = costoInscripcion;

            // Mostrar/ocultar campo de código según tipo de pago
            if (tipoInscripcion === 'QR') {
                campoCodigo.style.display = '';
                inputCodigo.required = true;
                labelCodigo.textContent = 'Código de Transacción QR';
                helpCodigo.textContent = 'Ingrese el código de la transacción QR realizada';
            } else if (tipoInscripcion === 'Deposito') {
                campoCodigo.style.display = '';
                inputCodigo.required = true;
                labelCodigo.textContent = 'Código de Depósito';
                helpCodigo.textContent = 'Ingrese el número de comprobante del depósito bancario';
            } else {
                campoCodigo.style.display = 'none';
                inputCodigo.required = false;
                inputCodigo.value = '';
            }

            // Si es beca, el costo de inscripción es 0 y el alojamiento también es gratis
            if (tipoInscripcion === 'Beca') {
                costoInscripcionActual = 0;
                total = 0;
                // Para becas, el alojamiento también es gratis
                if (alojamiento === 'Si') {
                    rowAlojamiento.style.display = '';
                    // Actualizar el display del alojamiento para mostrar que es gratis
                    document.getElementById('costo_alojamiento_display').textContent = 'Bs. 0.00';
                } else {
                    rowAlojamiento.style.display = 'none';
                }
            } else {
                // Para pagos normales, sumar el costo de alojamiento si aplica
                if (alojamiento === 'Si') {
                    total += costoAlojamiento;
                    rowAlojamiento.style.display = '';
                    // Restaurar el costo real del alojamiento
                    document.getElementById('costo_alojamiento_display').textContent = 'Bs. ' + costoAlojamiento.toFixed(2);
                } else {
                    rowAlojamiento.style.display = 'none';
                }
            }

            // Actualizar el costo de inscripción en el resumen
            costoInscripcionDisplay.textContent = 'Bs. ' + costoInscripcionActual.toFixed(2);

            // Actualizar el total en el resumen
            totalDisplay.textContent = 'Bs. ' + total.toFixed(2);

            // Establecer el monto pagado automáticamente
            montoPagado.value = total.toFixed(2);

            // Cambiar color de fondo según el tipo
            if (tipoInscripcion === 'Beca') {
                montoPagado.style.backgroundColor = '#fff3cd';
            } else {
                montoPagado.style.backgroundColor = '#f8f9fa';
            }
        }

        // Calcular monto al cargar la pagina
        document.addEventListener('DOMContentLoaded', function() {
            calcularMonto();
        });
    </script>
</body>

</html>