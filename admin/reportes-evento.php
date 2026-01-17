<?php
require_once __DIR__ . '/../config/config.php';

// Verificar autenticación
Auth::requireLogin();

// Verificar que tenga rol de admin o super_admin o usuario
if (!Auth::checkRole(['superadmin', 'admin', 'super_admin', 'usuario'])) {
    header('HTTP/1.0 403 Forbidden');
    die('Acceso denegado. No tienes permisos para acceder a esta página.');
}

// Obtener ID del evento
$eventoId = isset($_GET['evento']) ? intval($_GET['evento']) : 0;

if ($eventoId <= 0) {
    die('Evento no especificado');
}

$db = getDB();
$eventosManager = new EventosManager($db);
$inscripcionesEvento = new InscripcionesEvento($db, $eventoId);

// Verificar acceso al evento (simplificado por ahora)
// TODO: Implementar verificación de acceso por evento

// Obtener evento y estadisticas
$evento = $eventosManager->getById($eventoId);
$stats = $inscripcionesEvento->getStats();
$inscripciones = $inscripcionesEvento->getAll();

// Obtener reportes especificos
function getInscripcionesPorSexo($inscripciones)
{
    $hombres = array_filter($inscripciones, function ($i) {
        return $i['sexo'] == 'Masculino';
    });
    $mujeres = array_filter($inscripciones, function ($i) {
        return $i['sexo'] == 'Femenino';
    });
    return ['hombres' => $hombres, 'mujeres' => $mujeres];
}

function getInscripcionesPorTipo($inscripciones)
{
    $tipos = [];
    foreach ($inscripciones as $inscrito) {
        $tipo = $inscrito['tipo_inscripcion'];
        if (!isset($tipos[$tipo])) {
            $tipos[$tipo] = [];
        }
        $tipos[$tipo][] = $inscrito;
    }
    return $tipos;
}

function getDeudores($inscripciones)
{
    return array_filter($inscripciones, function ($i) {
        return $i['estado_pago'] == 'pendiente' || $i['estado_pago'] == 'parcial';
    });
}

function getBecados($inscripciones)
{
    return array_filter($inscripciones, function ($i) {
        return $i['tipo_inscripcion'] == 'Beca';
    });
}

function getInscripcionesOnline($inscripciones)
{
    return array_filter($inscripciones, function ($i) {
        return !empty($i['codigo_pago']);
    });
}

function getPorGrupos($inscripciones)
{
    $grupos = [];
    foreach ($inscripciones as $inscrito) {
        $grupo = $inscrito['grupo'] ?? 0;
        if (!isset($grupos[$grupo])) {
            $grupos[$grupo] = [];
        }
        $grupos[$grupo][] = $inscrito;
    }
    ksort($grupos);
    return $grupos;
}

$porSexo = getInscripcionesPorSexo($inscripciones);
$porTipo = getInscripcionesPorTipo($inscripciones);
$deudores = getDeudores($inscripciones);
$becados = getBecados($inscripciones);
$online = getInscripcionesOnline($inscripciones);
$porGrupos = getPorGrupos($inscripciones);

// Determinar tipo de reporte
$tipoReporte = $_GET['tipo'] ?? 'todos';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reportes - <?php echo htmlspecialchars($evento['nombre']); ?> | <?php echo SITE_NAME; ?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- html2pdf -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        :root {
            --color-primario: #8B7EC8;
            --color-secundario: #B8B3D8;
            --color-acento: #6B5B95;
        }

        body {
            background: #f8f9fa;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .card-header {
            background: linear-gradient(135deg, var(--color-primario) 0%, var(--color-acento) 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
        }

        .table {
            font-size: 0.9rem;
        }

        .badge {
            font-size: 0.8rem;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .card {
                box-shadow: none;
                border: 1px solid #dee2e6;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid py-4">
        <!-- Encabezado -->
        <div class="card mb-4 no-print">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h1 class="h3 mb-0">Reportes - <?php echo htmlspecialchars($evento['nombre']); ?></h1>
                        <p class="mb-0 opacity-75">Generado el <?php echo formatDate(date('Y-m-d'), 'd/m/Y'); ?> a las
                            <?php echo date('H:i'); ?></p>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-light" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                        <a href="inscripciones-evento.php?evento=<?php echo $eventoId; ?>" class="btn btn-light">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Navegacion de reportes -->
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $tipoReporte == 'todos' ? 'active' : ''; ?>"
                            href="?evento=<?php echo $eventoId; ?>&tipo=todos">Todos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $tipoReporte == 'sexo' ? 'active' : ''; ?>"
                            href="?evento=<?php echo $eventoId; ?>&tipo=sexo">Por Sexo</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $tipoReporte == 'tipo' ? 'active' : ''; ?>"
                            href="?evento=<?php echo $eventoId; ?>&tipo=tipo">Por Tipo</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $tipoReporte == 'deudores' ? 'active' : ''; ?>"
                            href="?evento=<?php echo $eventoId; ?>&tipo=deudores">Deudores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $tipoReporte == 'becados' ? 'active' : ''; ?>"
                            href="?evento=<?php echo $eventoId; ?>&tipo=becados">Becados</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $tipoReporte == 'online' ? 'active' : ''; ?>"
                            href="?evento=<?php echo $eventoId; ?>&tipo=online">Inscripciones Online</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $tipoReporte == 'grupos' ? 'active' : ''; ?>"
                            href="?evento=<?php echo $eventoId; ?>&tipo=grupos">Grupos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $tipoReporte == 'egresos' ? 'active' : ''; ?>"
                            href="?evento=<?php echo $eventoId; ?>&tipo=egresos">Egresos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $tipoReporte == 'financiero' ? 'active' : ''; ?>"
                            href="?evento=<?php echo $eventoId; ?>&tipo=financiero">Informe Financiero</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Resumen General -->
        <div class="row mb-4">
            <div class="col-lg-2 col-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary"><?php echo $stats['total_inscritos'] ?? 0; ?></h3>
                        <p class="mb-0">Total Inscritos</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success"><?php echo $stats['hombres'] ?? 0; ?></h3>
                        <p class="mb-0">Hombres</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-warning"><?php echo $stats['mujeres'] ?? 0; ?></h3>
                        <p class="mb-0">Mujeres</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-info">Bs. <?php echo number_format($stats['total_recaudado'] ?? 0, 2); ?></h3>
                        <p class="mb-0">Recaudado</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-danger"><?php echo $stats['deudores'] ?? 0; ?></h3>
                        <p class="mb-0">Deudores</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-dark"><?php echo $stats['grupos_formados'] ?? 0; ?></h3>
                        <p class="mb-0">Grupos</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-secondary"><?php echo $stats['inscripciones_online'] ?? 0; ?></h3>
                        <p class="mb-0">Online</p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($tipoReporte == 'todos'): ?>
            <!-- Reporte: Todos los Inscritos -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Lista Completa de Inscritos</h3>
                    <div class="no-print">
                        <button class="btn btn-sm btn-success" onclick="exportarTodosExcel(<?php echo $eventoId; ?>)">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="tabla-todos">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>Nombres</th>
                                    <th>Apellidos</th>
                                    <th>Email</th>
                                    <th>Sexo</th>
                                    <th>Tipo</th>
                                    <th>Monto Pagado</th>
                                    <th>Alojamiento</th>
                                    <th>Estado</th>
                                    <th>Grupo</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inscripciones as $inscrito): ?>
                                    <tr>
                                        <td><code><?php echo $inscrito['codigo_inscripcion']; ?></code></td>
                                        <td><?php echo htmlspecialchars($inscrito['nombres']); ?></td>
                                        <td><?php echo htmlspecialchars($inscrito['apellidos']); ?></td>
                                        <td><?php echo htmlspecialchars($inscrito['email']); ?></td>
                                        <td><?php echo $inscrito['sexo']; ?></td>
                                        <td><?php echo $inscrito['tipo_inscripcion']; ?></td>
                                        <td>Bs. <?php echo number_format($inscrito['monto_pagado'], 2); ?></td>
                                        <td><?php echo $inscrito['alojamiento']; ?></td>
                                        <td>
                                            <span
                                                class="badge bg-<?php
                                                                echo $inscrito['estado_pago'] == 'completo' ? 'success' : ($inscrito['estado_pago'] == 'beca' ? 'info' : ($inscrito['estado_pago'] == 'parcial' ? 'warning' : 'danger')); ?>">
                                                <?php echo ucfirst($inscrito['estado_pago']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $inscrito['grupo'] ? 'Grupo ' . $inscrito['grupo'] : '-'; ?></td>
                                        <td><?php echo formatDate($inscrito['fecha_inscripcion'], 'd/m/Y H:i'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($tipoReporte == 'sexo'): ?>
            <!-- Reporte por Sexo -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Hombres (<?php echo count($porSexo['hombres']); ?>)</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Codigo</th>
                                            <th>Nombre</th>
                                            <th>Estado Pago</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($porSexo['hombres'] as $inscrito): ?>
                                            <tr>
                                                <td><?php echo $inscrito['codigo_inscripcion']; ?></td>
                                                <td><?php echo htmlspecialchars($inscrito['nombres'] . ' ' . $inscrito['apellidos']); ?>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-<?php
                                                                        echo $inscrito['estado_pago'] == 'completo' ? 'success' : ($inscrito['estado_pago'] == 'beca' ? 'info' : 'warning'); ?>">
                                                        <?php echo ucfirst($inscrito['estado_pago']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Mujeres (<?php echo count($porSexo['mujeres']); ?>)</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Codigo</th>
                                            <th>Nombre</th>
                                            <th>Estado Pago</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($porSexo['mujeres'] as $inscrito): ?>
                                            <tr>
                                                <td><?php echo $inscrito['codigo_inscripcion']; ?></td>
                                                <td><?php echo htmlspecialchars($inscrito['nombres'] . ' ' . $inscrito['apellidos']); ?>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-<?php
                                                                        echo $inscrito['estado_pago'] == 'completo' ? 'success' : ($inscrito['estado_pago'] == 'beca' ? 'info' : 'warning'); ?>">
                                                        <?php echo ucfirst($inscrito['estado_pago']); ?>
                                                    </span>
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
        <?php endif; ?>

        <?php if ($tipoReporte == 'tipo'): ?>
            <!-- Reporte por Tipo de Inscripcion -->
            <?php foreach ($porTipo as $tipo => $inscritos): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title"><?php echo $tipo; ?> (<?php echo count($inscritos); ?>)</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Nombre</th>
                                        <th>Monto Pagado</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inscritos as $inscrito): ?>
                                        <tr>
                                            <td><?php echo $inscrito['codigo_inscripcion']; ?></td>
                                            <td><?php echo htmlspecialchars($inscrito['nombres'] . ' ' . $inscrito['apellidos']); ?>
                                            </td>
                                            <td>Bs. <?php echo number_format($inscrito['monto_pagado'], 2); ?></td>
                                            <td>
                                                <span
                                                    class="badge bg-<?php
                                                                    echo $inscrito['estado_pago'] == 'completo' ? 'success' : ($inscrito['estado_pago'] == 'beca' ? 'info' : 'warning'); ?>">
                                                    <?php echo ucfirst($inscrito['estado_pago']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($tipoReporte == 'deudores'): ?>
            <!-- Reporte: Deudores -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Lista de Deudores (<?php echo count($deudores); ?>)</h3>
                    <div class="no-print">
                        <button class="btn btn-sm btn-success" onclick="exportarDeudoresExcel(<?php echo $eventoId; ?>)">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="tabla-deudores">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Monto Total</th>
                                    <th>Monto Pagado</th>
                                    <th>Deuda</th>
                                    <th>Tipo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($deudores as $inscrito): ?>
                                    <?php $deuda = $inscrito['monto_total'] - $inscrito['monto_pagado']; ?>
                                    <tr>
                                        <td><?php echo $inscrito['codigo_inscripcion']; ?></td>
                                        <td><?php echo htmlspecialchars($inscrito['nombres'] . ' ' . $inscrito['apellidos']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($inscrito['email']); ?></td>
                                        <td>Bs. <?php echo number_format($inscrito['monto_total'], 2); ?></td>
                                        <td>Bs. <?php echo number_format($inscrito['monto_pagado'], 2); ?></td>
                                        <td><strong class="text-danger">Bs. <?php echo number_format($deuda, 2); ?></strong>
                                        </td>
                                        <td><?php echo $inscrito['tipo_inscripcion']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($tipoReporte == 'becados'): ?>
            <!-- Reporte: Becados -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Lista de Becados (<?php echo count($becados); ?>)</h3>
                    <div class="no-print">
                        <button class="btn btn-sm btn-success" onclick="exportarBecadosExcel(<?php echo $eventoId; ?>)">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="tabla-becados">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Telefono</th>
                                    <th>Iglesia</th>
                                    <th>Fecha Inscripcion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($becados as $inscrito): ?>
                                    <tr>
                                        <td><?php echo $inscrito['codigo_inscripcion']; ?></td>
                                        <td><?php echo htmlspecialchars($inscrito['nombres'] . ' ' . $inscrito['apellidos']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($inscrito['email']); ?></td>
                                        <td><?php echo htmlspecialchars($inscrito['telefono']); ?></td>
                                        <td><?php echo htmlspecialchars($inscrito['iglesia']); ?></td>
                                        <td><?php echo formatDate($inscrito['fecha_inscripcion'], 'd/m/Y H:i'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($tipoReporte == 'online'): ?>
            <!-- Reporte: Inscripciones Online -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Inscripciones Online (<?php echo count($online); ?>)</h3>
                    <div class="no-print">
                        <button class="btn btn-sm btn-success" onclick="exportarOnlineExcel(<?php echo $eventoId; ?>)">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="tabla-online">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>Nombres</th>
                                    <th>Apellidos</th>
                                    <th>Email</th>
                                    <th>Sexo</th>
                                    <th>Tipo</th>
                                    <th>Monto Pagado</th>
                                    <th>Alojamiento</th>
                                    <th>Estado</th>
                                    <th>Codigo Pago</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($online as $inscrito): ?>
                                    <tr>
                                        <td><code><?php echo $inscrito['codigo_inscripcion']; ?></code></td>
                                        <td><?php echo htmlspecialchars($inscrito['nombres']); ?></td>
                                        <td><?php echo htmlspecialchars($inscrito['apellidos']); ?></td>
                                        <td><?php echo htmlspecialchars($inscrito['email']); ?></td>
                                        <td><?php echo $inscrito['sexo']; ?></td>
                                        <td><?php echo $inscrito['tipo_inscripcion']; ?></td>
                                        <td>Bs. <?php echo number_format($inscrito['monto_pagado'], 2); ?></td>
                                        <td><?php echo $inscrito['alojamiento']; ?></td>
                                        <td>
                                            <span
                                                class="badge bg-<?php echo $inscrito['estado_pago'] == 'completo' ? 'success' : ($inscrito['estado_pago'] == 'beca' ? 'info' : ($inscrito['estado_pago'] == 'parcial' ? 'warning' : 'danger')); ?>">
                                                <?php echo ucfirst($inscrito['estado_pago']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($inscrito['codigo_pago']); ?></td>
                                        <td><?php echo formatDate($inscrito['fecha_inscripcion'], 'd/m/Y H:i'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($tipoReporte == 'grupos'): ?>
            <!-- Reporte: Grupos -->
            <?php
            // Filtrar solo grupos válidos (grupo > 0)
            $gruposValidos = array_filter($porGrupos, function ($key) {
                return $key > 0;
            }, ARRAY_FILTER_USE_KEY);
            ?>
            <?php if (!empty($gruposValidos)): ?>
                <?php foreach ($gruposValidos as $numGrupo => $inscritos): ?>
                    <div class="card mb-4" id="grupo-<?php echo $numGrupo; ?>">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Grupo <?php echo $numGrupo; ?> (<?php echo count($inscritos); ?> personas)
                            </h3>
                            <div class="no-print">
                                <button class="btn btn-sm btn-light" onclick="imprimirGrupo(<?php echo $numGrupo; ?>)">
                                    <i class="fas fa-print"></i> Imprimir
                                </button>
                                <button class="btn btn-sm btn-success"
                                    onclick="exportarGrupoExcel(<?php echo $numGrupo; ?>, <?php echo $eventoId; ?>)">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Codigo</th>
                                            <th>Nombre</th>
                                            <th>Sexo</th>
                                            <th>Iglesia</th>
                                            <th>Estado Pago</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inscritos as $inscrito): ?>
                                            <tr>
                                                <td><?php echo $inscrito['codigo_inscripcion']; ?></td>
                                                <td><?php echo htmlspecialchars($inscrito['nombres'] . ' ' . $inscrito['apellidos']); ?>
                                                </td>
                                                <td><?php echo $inscrito['sexo']; ?></td>
                                                <td><?php echo htmlspecialchars($inscrito['iglesia']); ?></td>
                                                <td>
                                                    <span
                                                        class="badge bg-<?php
                                                                        echo $inscrito['estado_pago'] == 'completo' ? 'success' : ($inscrito['estado_pago'] == 'beca' ? 'info' : 'warning'); ?>">
                                                        <?php echo ucfirst($inscrito['estado_pago']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-5x text-muted mb-3"></i>
                    <h3 class="text-muted">No hay grupos formados</h3>
                    <p class="text-muted">Forma grupos desde el panel de administracion</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($tipoReporte == 'egresos'): ?>
            <!-- Reporte: Egresos -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Control de Egresos</h3>
                </div>
                <div class="card-body">
                    <!-- Formulario para agregar egreso -->
                    <form id="form-egreso" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="cantidad" class="form-label">Cantidad</label>
                                <input type="number" class="form-control" id="cantidad" min="1" required>
                            </div>
                            <div class="col-md-5">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <input type="text" class="form-control" id="descripcion" required>
                            </div>
                            <div class="col-md-3">
                                <label for="monto" class="form-label">Monto (Bs.)</label>
                                <input type="number" class="form-control" id="monto" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Agregar</button>
                            </div>
                        </div>
                    </form>

                    <!-- Lista de egresos -->
                    <div class="table-responsive">
                        <table class="table table-striped" id="tabla-egresos">
                            <thead>
                                <tr>
                                    <th>Cantidad</th>
                                    <th>Descripción</th>
                                    <th>Monto Unitario</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="lista-egresos">
                                <!-- Aquí se agregarán los egresos dinámicamente -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th id="total-egresos">Bs. 0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($tipoReporte == 'financiero'): ?>
            <!-- Reporte Financiero -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Informe Financiero</h3>
                    <button class="btn btn-success" onclick="exportarFinancieroPDF()">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </button>
                </div>
                <div class="card-body" id="financiero-content">
                    <!-- Encabezado del Reporte -->
                    <div class="text-center mb-4">
                        <h2><?php echo SITE_NAME; ?></h2>
                        <h3>Informe Financiero</h3>
                        <h4><?php echo htmlspecialchars($evento['nombre']); ?></h4>
                        <p>Generado el <?php echo formatDate(date('Y-m-d'), 'd/m/Y'); ?> a las <?php echo date('H:i'); ?>
                        </p>
                    </div>
                    <!-- Ingresos -->
                    <h4>Ingresos</h4>
                    <h5>Inscripciones</h5>
                    <p>Total recaudado por inscripciones: Bs.
                        <?php echo number_format($stats['total_recaudado'] ?? 0, 2); ?></p>

                    <!-- Otros Ingresos -->
                    <h5>Otros Ingresos</h5>
                    <form id="form-ingreso" class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="descripcion-ingreso" class="form-label">Descripción</label>
                                <input type="text" class="form-control" id="descripcion-ingreso" required>
                            </div>
                            <div class="col-md-4">
                                <label for="monto-ingreso" class="form-label">Monto (Bs.)</label>
                                <input type="number" class="form-control" id="monto-ingreso" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Agregar</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped" id="tabla-ingresos">
                            <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th>Monto</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="lista-ingresos">
                                <!-- Aquí se agregarán los ingresos dinámicamente -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-end">Total Otros Ingresos:</th>
                                    <th id="total-otros-ingresos">Bs. 0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <p><strong>Total Ingresos: Bs. <span id="total-ingresos">0.00</span></strong></p>

                    <!-- Egresos -->
                    <h4>Egresos</h4>
                    <form id="form-egreso-fin" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="cantidad-eg" class="form-label">Cantidad</label>
                                <input type="number" class="form-control" id="cantidad-eg" min="1" required>
                            </div>
                            <div class="col-md-5">
                                <label for="descripcion-eg" class="form-label">Descripción</label>
                                <input type="text" class="form-control" id="descripcion-eg" required>
                            </div>
                            <div class="col-md-3">
                                <label for="monto-eg" class="form-label">Monto (Bs.)</label>
                                <input type="number" class="form-control" id="monto-eg" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Agregar</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped" id="tabla-egresos-fin">
                            <thead>
                                <tr>
                                    <th>Cantidad</th>
                                    <th>Descripción</th>
                                    <th>Monto Unitario</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="lista-egresos-fin">
                                <!-- Aquí se agregarán los egresos dinámicamente -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total Egresos:</th>
                                    <th id="total-egresos-fin">Bs. 0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <p><strong>Total Egresos: Bs. <span id="total-egresos-display">0.00</span></strong></p>

                    <!-- Total -->
                    <h4>Total de la Actividad</h4>
                    <p><strong>Ingresos Totales: Bs. <span id="total-final-ingresos">0.00</span></strong></p>
                    <p><strong>Egresos Totales: Bs. <span id="total-final-egresos">0.00</span></strong></p>
                    <p><strong>Balance: Bs. <span id="balance">0.00</span></strong></p>
                </div>
            </div>

            <!-- Contenido oculto para PDF -->
            <div id="pdf-content" style="display: none;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <h2><?php echo SITE_NAME; ?></h2>
                    <h3>Informe Financiero</h3>
                    <h4><?php echo htmlspecialchars($evento['nombre']); ?></h4>
                    <p>Generado el <?php echo formatDate(date('Y-m-d'), 'd/m/Y'); ?> a las <?php echo date('H:i'); ?></p>
                </div>

                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <thead>
                        <tr style="background-color: #f8f9fa;">
                            <th style="border: 1px solid #dee2e6; padding: 8px; text-align: left;">Descripción</th>
                            <th style="border: 1px solid #dee2e6; padding: 8px; text-align: right;">Monto (Bs.)</th>
                            <th style="border: 1px solid #dee2e6; padding: 8px; text-align: center;">Tipo</th>
                        </tr>
                    </thead>
                    <tbody id="pdf-table-body">
                        <!-- Se llenará dinámicamente -->
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold;">
                            <td style="border: 1px solid #dee2e6; padding: 8px;">Balance</td>
                            <td style="border: 1px solid #dee2e6; padding: 8px; text-align: right;" id="pdf-balance">0.00
                            </td>
                            <td style="border: 1px solid #dee2e6; padding: 8px; text-align: center;"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para imprimir un grupo específico
        function imprimirGrupo(numGrupo) {
            // Ocultar todos los grupos excepto el seleccionado
            const grupos = document.querySelectorAll('[id^="grupo-"]');
            grupos.forEach(grupo => {
                if (grupo.id !== `grupo-${numGrupo}`) {
                    grupo.style.display = 'none';
                }
            });

            // Imprimir
            window.print();

            // Restaurar visibilidad
            grupos.forEach(grupo => {
                grupo.style.display = 'block';
            });
        }

        // Función para exportar grupo a Excel (formato CSV)
        function exportarGrupoExcel(numGrupo, eventoId) {
            const grupo = document.getElementById(`grupo-${numGrupo}`);
            const tabla = grupo.querySelector('table');

            let csv = [];
            const filas = tabla.querySelectorAll('tr');

            filas.forEach(fila => {
                const cols = fila.querySelectorAll('td, th');
                const csvRow = [];
                cols.forEach(col => {
                    // Limpiar el texto y escapar comillas
                    let texto = col.innerText.trim();
                    // Remover saltos de línea y espacios extra
                    texto = texto.replace(/\s+/g, ' ');
                    // Escapar comillas dobles
                    texto = texto.replace(/"/g, '""');
                    // Agregar comillas si contiene separadores
                    if (texto.includes(',') || texto.includes('\n') || texto.includes('"') || texto
                        .includes(';')) {
                        csvRow.push('"' + texto + '"');
                    } else {
                        csvRow.push(texto);
                    }
                });
                csv.push(csvRow.join(';')); // Usar punto y coma como separador para Excel
            });

            const csvString = csv.join('\r\n'); // Usar CRLF para Windows/Excel
            const blob = new Blob(['\ufeff' + csvString], {
                type: 'text/csv;charset=utf-8;'
            });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);

            link.setAttribute('href', url);
            link.setAttribute('download', `grupo_${numGrupo}_evento_${eventoId}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Función para exportar todos los inscritos a Excel (formato CSV)
        function exportarTodosExcel(eventoId) {
            const tabla = document.getElementById('tabla-todos');

            let csv = [];
            const filas = tabla.querySelectorAll('tr');

            filas.forEach(fila => {
                const cols = fila.querySelectorAll('td, th');
                const csvRow = [];
                cols.forEach(col => {
                    let texto = col.innerText.trim();
                    texto = texto.replace(/\s+/g, ' ');
                    texto = texto.replace(/"/g, '""');
                    if (texto.includes(',') || texto.includes('\n') || texto.includes('"') || texto
                        .includes(';')) {
                        csvRow.push('"' + texto + '"');
                    } else {
                        csvRow.push(texto);
                    }
                });
                csv.push(csvRow.join(';'));
            });

            const csvString = csv.join('\r\n');
            const blob = new Blob(['\ufeff' + csvString], {
                type: 'text/csv;charset=utf-8;'
            });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);

            link.setAttribute('href', url);
            link.setAttribute('download', `todos_inscritos_evento_${eventoId}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Función para exportar deudores a Excel (formato CSV)
        function exportarDeudoresExcel(eventoId) {
            const tabla = document.getElementById('tabla-deudores');

            let csv = [];
            const filas = tabla.querySelectorAll('tr');

            filas.forEach(fila => {
                const cols = fila.querySelectorAll('td, th');
                const csvRow = [];
                cols.forEach(col => {
                    let texto = col.innerText.trim();
                    texto = texto.replace(/\s+/g, ' ');
                    texto = texto.replace(/"/g, '""');
                    if (texto.includes(',') || texto.includes('\n') || texto.includes('"') || texto
                        .includes(';')) {
                        csvRow.push('"' + texto + '"');
                    } else {
                        csvRow.push(texto);
                    }
                });
                csv.push(csvRow.join(';'));
            });

            const csvString = csv.join('\r\n');
            const blob = new Blob(['\ufeff' + csvString], {
                type: 'text/csv;charset=utf-8;'
            });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);

            link.setAttribute('href', url);
            link.setAttribute('download', `deudores_evento_${eventoId}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Función para exportar becados a Excel (formato CSV)
        function exportarBecadosExcel(eventoId) {
            const tabla = document.getElementById('tabla-becados');

            let csv = [];
            const filas = tabla.querySelectorAll('tr');

            filas.forEach(fila => {
                const cols = fila.querySelectorAll('td, th');
                const csvRow = [];
                cols.forEach(col => {
                    let texto = col.innerText.trim();
                    texto = texto.replace(/\s+/g, ' ');
                    texto = texto.replace(/"/g, '""');
                    if (texto.includes(',') || texto.includes('\n') || texto.includes('"') || texto
                        .includes(';')) {
                        csvRow.push('"' + texto + '"');
                    } else {
                        csvRow.push(texto);
                    }
                });
                csv.push(csvRow.join(';'));
            });

            const csvString = csv.join('\r\n');
            const blob = new Blob(['\ufeff' + csvString], {
                type: 'text/csv;charset=utf-8;'
            });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);

            link.setAttribute('href', url);
            link.setAttribute('download', `becados_evento_${eventoId}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Función para exportar inscripciones online a Excel (formato CSV)
        function exportarOnlineExcel(eventoId) {
            const tabla = document.getElementById('tabla-online');

            let csv = [];
            const filas = tabla.querySelectorAll('tr');

            filas.forEach(fila => {
                const cols = fila.querySelectorAll('td, th');
                const csvRow = [];
                cols.forEach(col => {
                    let texto = col.innerText.trim();
                    texto = texto.replace(/\s+/g, ' ');
                    texto = texto.replace(/"/g, '""');
                    if (texto.includes(',') || texto.includes('\n') || texto.includes('"') || texto
                        .includes(';')) {
                        csvRow.push('"' + texto + '"');
                    } else {
                        csvRow.push(texto);
                    }
                });
                csv.push(csvRow.join(';'));
            });

            const csvString = csv.join('\r\n');
            const blob = new Blob(['\ufeff' + csvString], {
                type: 'text/csv;charset=utf-8;'
            });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);

            link.setAttribute('href', url);
            link.setAttribute('download', `inscripciones_online_evento_${eventoId}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Control de egresos
        let egresos = JSON.parse(localStorage.getItem('egresos_evento_<?php echo $eventoId; ?>')) || [];

        function saveEgresos() {
            localStorage.setItem('egresos_evento_<?php echo $eventoId; ?>', JSON.stringify(egresos));
        }

        document.getElementById('form-egreso')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const cantidad = parseInt(document.getElementById('cantidad').value);
            const descripcion = document.getElementById('descripcion').value.trim();
            const monto = parseFloat(document.getElementById('monto').value);
            if (cantidad > 0 && descripcion && monto >= 0) {
                const subtotal = cantidad * monto;
                egresos.push({
                    cantidad,
                    descripcion,
                    monto,
                    subtotal
                });
                saveEgresos();
                actualizarTablaEgresos();
                this.reset();
            }
        });

        function actualizarTablaEgresos() {
            const tbody = document.getElementById('lista-egresos');
            if (!tbody) return;
            tbody.innerHTML = '';
            let total = 0;
            egresos.forEach((egreso, index) => {
                total += egreso.subtotal;
                const row = `
                    <tr>
                        <td>${egreso.cantidad}</td>
                        <td>${egreso.descripcion}</td>
                        <td>Bs. ${egreso.monto.toFixed(2)}</td>
                        <td>Bs. ${egreso.subtotal.toFixed(2)}</td>
                        <td><button class="btn btn-sm btn-danger" onclick="eliminarEgreso(${index})">Eliminar</button></td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
            const totalEl = document.getElementById('total-egresos');
            if (totalEl) totalEl.textContent = `Bs. ${total.toFixed(2)}`;
        }

        function eliminarEgreso(index) {
            egresos.splice(index, 1);
            saveEgresos();
            actualizarTablaEgresos();
        }

        // Inicializar tabla de egresos si existe
        actualizarTablaEgresos();

        // Control de ingresos adicionales
        let ingresos = JSON.parse(localStorage.getItem('ingresos_evento_<?php echo $eventoId; ?>')) || [];

        function saveIngresos() {
            localStorage.setItem('ingresos_evento_<?php echo $eventoId; ?>', JSON.stringify(ingresos));
        }

        document.getElementById('form-ingreso')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const descripcion = document.getElementById('descripcion-ingreso').value.trim();
            const monto = parseFloat(document.getElementById('monto-ingreso').value);
            if (descripcion && monto >= 0) {
                ingresos.push({
                    descripcion,
                    monto
                });
                saveIngresos();
                actualizarTablaIngresos();
                actualizarTotalesFinanciero();
                this.reset();
            }
        });

        function actualizarTablaIngresos() {
            const tbody = document.getElementById('lista-ingresos');
            if (!tbody) return;
            tbody.innerHTML = '';
            let total = 0;
            ingresos.forEach((ingreso, index) => {
                total += ingreso.monto;
                const row = `
                    <tr>
                        <td>${ingreso.descripcion}</td>
                        <td>Bs. ${ingreso.monto.toFixed(2)}</td>
                        <td><button class="btn btn-sm btn-danger" onclick="eliminarIngreso(${index})">Eliminar</button></td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
            const totalEl = document.getElementById('total-otros-ingresos');
            if (totalEl) totalEl.textContent = `Bs. ${total.toFixed(2)}`;
        }

        function eliminarIngreso(index) {
            ingresos.splice(index, 1);
            saveIngresos();
            actualizarTablaIngresos();
            actualizarTotalesFinanciero();
        }

        // Control de egresos en financiero
        document.getElementById('form-egreso-fin')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const cantidad = parseInt(document.getElementById('cantidad-eg').value);
            const descripcion = document.getElementById('descripcion-eg').value.trim();
            const monto = parseFloat(document.getElementById('monto-eg').value);
            if (cantidad > 0 && descripcion && monto >= 0) {
                const subtotal = cantidad * monto;
                egresos.push({
                    cantidad,
                    descripcion,
                    monto,
                    subtotal
                });
                saveEgresos();
                actualizarTablaEgresosFin();
                actualizarTotalesFinanciero();
                this.reset();
            }
        });

        function actualizarTablaEgresosFin() {
            const tbody = document.getElementById('lista-egresos-fin');
            if (!tbody) return;
            tbody.innerHTML = '';
            let total = 0;
            egresos.forEach((egreso, index) => {
                total += egreso.subtotal;
                const row = `
                    <tr>
                        <td>${egreso.cantidad}</td>
                        <td>${egreso.descripcion}</td>
                        <td>Bs. ${egreso.monto.toFixed(2)}</td>
                        <td>Bs. ${egreso.subtotal.toFixed(2)}</td>
                        <td><button class="btn btn-sm btn-danger" onclick="eliminarEgresoFin(${index})">Eliminar</button></td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
            const totalEl = document.getElementById('total-egresos-fin');
            if (totalEl) totalEl.textContent = `Bs. ${total.toFixed(2)}`;
        }

        function eliminarEgresoFin(index) {
            egresos.splice(index, 1);
            saveEgresos();
            actualizarTablaEgresosFin();
            actualizarTotalesFinanciero();
        }

        function actualizarTotalesFinanciero() {
            const totalInscripciones = <?php echo $stats['total_recaudado'] ?? 0; ?>;
            const totalOtrosIngresos = ingresos.reduce((sum, i) => sum + i.monto, 0);
            const totalIngresos = totalInscripciones + totalOtrosIngresos;
            const totalEgresos = egresos.reduce((sum, e) => sum + e.subtotal, 0);
            const balance = totalIngresos - totalEgresos;

            document.getElementById('total-ingresos').textContent = totalIngresos.toFixed(2);
            document.getElementById('total-egresos-display').textContent = totalEgresos.toFixed(2);
            document.getElementById('total-final-ingresos').textContent = totalIngresos.toFixed(2);
            document.getElementById('total-final-egresos').textContent = totalEgresos.toFixed(2);
            document.getElementById('balance').textContent = balance.toFixed(2);

            // Actualizar tabla PDF
            actualizarTablaPDF(totalInscripciones, ingresos, egresos, balance);
        }

        function actualizarTablaPDF(totalInscripciones, ingresos, egresos, balance) {
            const tbody = document.getElementById('pdf-table-body');
            if (!tbody) return;
            tbody.innerHTML = '';

            // Título de Ingresos
            const ingresosTitleRow = `
                <tr>
                    <td colspan="3" style="border: 1px solid #dee2e6; padding: 8px; text-align: center; font-weight: bold; background-color: #f8f9fa;">INGRESOS</td>
                </tr>
            `;
            tbody.innerHTML += ingresosTitleRow;

            // Ingreso de inscripciones
            if (totalInscripciones > 0) {
                const row = `
                    <tr>
                        <td style="border: 1px solid #dee2e6; padding: 8px;">Inscripciones</td>
                        <td style="border: 1px solid #dee2e6; padding: 8px; text-align: right;">${totalInscripciones.toFixed(2)}</td>
                        <td style="border: 1px solid #dee2e6; padding: 8px; text-align: center;">Ingreso</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            }

            // Otros ingresos
            ingresos.forEach(ingreso => {
                const row = `
                    <tr>
                        <td style="border: 1px solid #dee2e6; padding: 8px;">${ingreso.descripcion}</td>
                        <td style="border: 1px solid #dee2e6; padding: 8px; text-align: right;">${ingreso.monto.toFixed(2)}</td>
                        <td style="border: 1px solid #dee2e6; padding: 8px; text-align: center;">Ingreso</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            // Título de Egresos
            const egresosTitleRow = `
                <tr>
                    <td colspan="3" style="border: 1px solid #dee2e6; padding: 8px; text-align: center; font-weight: bold; background-color: #f8f9fa;">EGRESOS</td>
                </tr>
            `;
            tbody.innerHTML += egresosTitleRow;

            // Egresos
            egresos.forEach(egreso => {
                const row = `
                    <tr>
                        <td style="border: 1px solid #dee2e6; padding: 8px;">${egreso.descripcion} (Cant: ${egreso.cantidad})</td>
                        <td style="border: 1px solid #dee2e6; padding: 8px; text-align: right;">${egreso.subtotal.toFixed(2)}</td>
                        <td style="border: 1px solid #dee2e6; padding: 8px; text-align: center;">Egreso</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            // Balance
            document.getElementById('pdf-balance').textContent = balance.toFixed(2);
        }

        // Inicializar tablas y totales en financiero
        actualizarTablaIngresos();
        actualizarTablaEgresosFin();
        actualizarTotalesFinanciero();

        function exportarFinancieroPDF() {
            const element = document.getElementById('pdf-content');
            element.style.display = 'block'; // Temporarily show the element
            const opt = {
                margin: 1,
                filename: `informe_financiero_<?php echo $eventoId; ?>.pdf`,
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'in',
                    format: 'letter',
                    orientation: 'portrait'
                }
            };
            html2pdf().set(opt).from(element).save().then(() => {
                element.style.display = 'none'; // Hide it back after PDF generation
            });
        }
    </script>
</body>

</html>