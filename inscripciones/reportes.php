<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

// Obtener tipo de reporte
$tipoReporte = $_GET['reporte'] ?? 'todos';
$formato = $_GET['formato'] ?? 'web';

// Obtener datos según el reporte
switch ($tipoReporte) {
    case 'sexo':
        $masculinos = Inscripciones::getAll(['sexo' => 'Masculino']);
        $femeninos = Inscripciones::getAll(['sexo' => 'Femenino']);
        $titulo = 'Lista de Inscritos por Sexo';
        break;
    case 'deudores':
        $inscritos = Inscripciones::getDeudores();
        $titulo = 'Lista de Deudores';
        break;
    case 'becas':
        $inscritos = Inscripciones::getBecados();
        $titulo = 'Lista de Becados';
        break;
    case 'grupos':
        $grupos = Inscripciones::getGroups();
        $titulo = 'Lista de Grupos';
        break;
    default:
        $inscritos = Inscripciones::getAll();
        $titulo = 'Lista de Inscritos';
        break;
}

// Si se solicita formato PDF
if ($formato === 'pdf') {
    // Generar PDF simple (en producción usar una librería como DomPDF)
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="reporte_' . $tipoReporte . '.txt"');
    echo "REPORTE: $titulo\n";
    echo "Fecha: " . date('d/m/Y H:i:s') . "\n";
    echo "=====================================\n\n";
    
    if ($tipoReporte === 'sexo') {
        echo "MASCULINOS:\n";
        foreach ($masculinos as $inscrito) {
            echo "- {$inscrito['nombres']} {$inscrito['apellidos']}\n";
        }
        echo "\nFEMENINOS:\n";
        foreach ($femeninos as $inscrito) {
            echo "- {$inscrito['nombres']} {$inscrito['apellidos']}\n";
        }
    } elseif ($tipoReporte === 'grupos') {
        foreach ($grupos as $grupo) {
            echo "\n{$grupo['nombre_grupo']}:\n";
            $miembros = Inscripciones::getInscritosByGroup($grupo['numero_grupo']);
            foreach ($miembros as $inscrito) {
                echo "- {$inscrito['nombres']} {$inscrito['apellidos']}\n";
            }
        }
    } else {
        foreach ($inscritos as $inscrito) {
            echo "- {$inscrito['nombres']} {$inscrito['apellidos']}\n";
        }
    }
    exit;
}

// Obtener configuración del sitio
$siteConfig = SiteConfig::get();
$stats = Inscripciones::getStats();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de Inscripción - <?php echo htmlspecialchars($siteConfig['nombre_institucion']); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --color-primario: <?php echo $siteConfig['color_primario'] ?? '#8B7EC8'; ?>;
            --color-secundario: <?php echo $siteConfig['color_secundario'] ?? '#B8B3D8'; ?>;
            --color-acento: <?php echo $siteConfig['color_acento'] ?? '#6B5B95'; ?>;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--color-primario), var(--color-secundario));
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .content-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: linear-gradient(135deg, var(--color-primario), var(--color-acento));
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .header h1 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .content-body {
            padding: 2rem;
        }
        
        .nav-tabs .nav-link {
            color: #495057;
            border: none;
            border-radius: 10px 10px 0 0;
            margin-right: 5px;
        }
        
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, var(--color-primario), var(--color-acento));
            color: white;
            border: none;
        }
        
        .tab-content {
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 10px 10px;
            padding: 2rem;
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
        
        .table {
            font-size: 0.9rem;
        }
        
        .table th {
            background: var(--color-primario);
            color: white;
            border: none;
            font-weight: 600;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .badge-sexo-m {
            background: linear-gradient(135deg, #007bff, #0056b3);
        }
        
        .badge-sexo-f {
            background: linear-gradient(135deg, #e83e8c, #c2185b);
        }
        
        .badge-tipo {
            background: linear-gradient(135deg, #28a745, #1e7e34);
        }
        
        .badge-beca {
            background: linear-gradient(135deg, #ffc107, #e0a800);
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
        
        .btn-export {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            border: none;
            color: white;
        }
        
        .btn-export:hover {
            background: linear-gradient(135deg, #1e7e34, #28a745);
            color: white;
        }
        
        .grupo-card {
            border-radius: 15px;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .grupo-header {
            padding: 1rem;
            color: white;
            font-weight: 600;
        }
        
        .grupo-body {
            padding: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content-container">
            <div class="header">
                <h1><i class="fas fa-chart-bar"></i> Reportes de Inscripción</h1>
                <p><?php echo htmlspecialchars($siteConfig['nombre_institucion']); ?></p>
            </div>
            
            <div class="content-body">
                <!-- Estadísticas Generales -->
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
                            <div class="stats-number">Bs. <?php echo number_format($stats['recaudacion_total'] ?? 0, 2); ?></div>
                            <div class="stats-label">Recaudado</div>
                        </div>
                    </div>
                </div>
                
                <!-- Navegación de reportes -->
                <ul class="nav nav-tabs" id="reportesTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="todos-tab" data-bs-toggle="tab" data-bs-target="#todos" type="button" role="tab">
                            <i class="fas fa-users"></i> Todos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sexo-tab" data-bs-toggle="tab" data-bs-target="#sexo" type="button" role="tab">
                            <i class="fas fa-venus-mars"></i> Por Sexo
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="deudores-tab" data-bs-toggle="tab" data-bs-target="#deudores" type="button" role="tab">
                            <i class="fas fa-money-bill-wave"></i> Deudores
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="becas-tab" data-bs-toggle="tab" data-bs-target="#becas" type="button" role="tab">
                            <i class="fas fa-award"></i> Becas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="grupos-tab" data-bs-toggle="tab" data-bs-target="#grupos" type="button" role="tab">
                            <i class="fas fa-layer-group"></i> Grupos
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="reportesTabContent">
                    <!-- Reporte: Todos los inscritos -->
                    <div class="tab-pane fade show active" id="todos" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4><i class="fas fa-list"></i> Lista de Inscritos</h4>
                            <a href="reportes.php?reporte=todos&formato=pdf" class="btn btn-export">
                                <i class="fas fa-download"></i> Descargar PDF
                            </a>
                        </div>
                        
                        <?php
                        $inscritos = Inscripciones::getAll();
                        if (empty($inscritos)):
                        ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No hay inscripciones registradas aún.
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombres</th>
                                        <th>Apellidos</th>
                                        <th>Fecha Nac.</th>
                                        <th>Sexo</th>
                                        <th>Iglesia</th>
                                        <th>Departamento</th>
                                        <th>Tipo</th>
                                        <th>Alojamiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inscritos as $index => $inscrito): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($inscrito['nombres']); ?></td>
                                        <td><?php echo htmlspecialchars($inscrito['apellidos']); ?></td>
                                        <td><?php echo formatDate($inscrito['fecha_nacimiento']); ?></td>
                                        <td>
                                            <span class="badge badge-sexo-<?php echo strtolower($inscrito['sexo']); ?>">
                                                <?php echo $inscrito['sexo']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($inscrito['iglesia'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($inscrito['departamento'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge badge-tipo">
                                                <?php echo ucfirst($inscrito['tipo_inscripcion']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $inscrito['alojamiento'] === 'Si' ? 'success' : 'secondary'; ?>">
                                                <?php echo $inscrito['alojamiento']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Reporte: Por sexo -->
                    <div class="tab-pane fade" id="sexo" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4><i class="fas fa-venus-mars"></i> Inscritos por Sexo</h4>
                            <a href="reportes.php?reporte=sexo&formato=pdf" class="btn btn-export">
                                <i class="fas fa-download"></i> Descargar PDF
                            </a>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="text-primary"><i class="fas fa-male"></i> Hombres (<?php echo $stats['masculino'] ?? 0; ?>)</h5>
                                <?php
                                $masculinos = Inscripciones::getAll(['sexo' => 'Masculino']);
                                if (empty($masculinos)):
                                ?>
                                <p class="text-muted">No hay inscritos masculinos.</p>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Iglesia</th>
                                                <th>Departamento</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($masculinos as $inscrito): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($inscrito['nombres'] . ' ' . $inscrito['apellidos']); ?></td>
                                                <td><?php echo htmlspecialchars($inscrito['iglesia'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($inscrito['departamento'] ?? 'N/A'); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h5 class="text-danger"><i class="fas fa-female"></i> Mujeres (<?php echo $stats['femenino'] ?? 0; ?>)</h5>
                                <?php
                                $femeninos = Inscripciones::getAll(['sexo' => 'Femenino']);
                                if (empty($femeninos)):
                                ?>
                                <p class="text-muted">No hay inscritos femeninos.</p>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Iglesia</th>
                                                <th>Departamento</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($femeninos as $inscrito): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($inscrito['nombres'] . ' ' . $inscrito['apellidos']); ?></td>
                                                <td><?php echo htmlspecialchars($inscrito['iglesia'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($inscrito['departamento'] ?? 'N/A'); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reporte: Deudores -->
                    <div class="tab-pane fade" id="deudores" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4><i class="fas fa-money-bill-wave"></i> Lista de Deudores</h4>
                            <a href="reportes.php?reporte=deudores&formato=pdf" class="btn btn-export">
                                <i class="fas fa-download"></i> Descargar PDF
                            </a>
                        </div>
                        
                        <?php
                        $deudores = Inscripciones::getDeudores();
                        if (empty($deudores)):
                        ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> ¡No hay deudores! Todos han pagado.
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Iglesia</th>
                                        <th>Tipo</th>
                                        <th>Alojamiento</th>
                                        <th>Monto Total</th>
                                        <th>Fecha Inscripción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($deudores as $index => $deudor): ?>
                                    <tr class="table-warning">
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($deudor['nombres'] . ' ' . $deudor['apellidos']); ?></td>
                                        <td><?php echo htmlspecialchars($deudor['iglesia'] ?? 'N/A'); ?></td>
                                        <td><?php echo ucfirst($deudor['tipo_inscripcion']); ?></td>
                                        <td><?php echo $deudor['alojamiento']; ?></td>
                                        <td><strong>Bs. <?php echo number_format($deudor['monto_total'], 2); ?></strong></td>
                                        <td><?php echo formatDate($deudor['fecha_inscripcion'], 'd/m/Y H:i'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Reporte: Becas -->
                    <div class="tab-pane fade" id="becas" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4><i class="fas fa-award"></i> Lista de Becados</h4>
                            <a href="reportes.php?reporte=becas&formato=pdf" class="btn btn-export">
                                <i class="fas fa-download"></i> Descargar PDF
                            </a>
                        </div>
                        
                        <?php
                        $becados = Inscripciones::getBecados();
                        if (empty($becados)):
                        ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No hay becados registrados.
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Iglesia</th>
                                        <th>Departamento</th>
                                        <th>Sexo</th>
                                        <th>Alojamiento</th>
                                        <th>Fecha Inscripción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($becados as $index => $becado): ?>
                                    <tr class="table-success">
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($becado['nombres'] . ' ' . $becado['apellidos']); ?></td>
                                        <td><?php echo htmlspecialchars($becado['iglesia'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($becado['departamento'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge badge-sexo-<?php echo strtolower($becado['sexo']); ?>">
                                                <?php echo $becado['sexo']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $becado['alojamiento']; ?></td>
                                        <td><?php echo formatDate($becado['fecha_inscripcion'], 'd/m/Y H:i'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Reporte: Grupos -->
                    <div class="tab-pane fade" id="grupos" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4><i class="fas fa-layer-group"></i> Lista de Grupos</h4>
                            <a href="reportes.php?reporte=grupos&formato=pdf" class="btn btn-export">
                                <i class="fas fa-download"></i> Descargar PDF
                            </a>
                        </div>
                        
                        <?php
                        $grupos = Inscripciones::getGroups();
                        if (empty($grupos)):
                        ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> No hay grupos formados. Ve al panel administrativo para formar grupos.
                        </div>
                        <?php else: ?>
                        <div class="row">
                            <?php foreach ($grupos as $grupo): ?>
                            <div class="col-md-6">
                                <div class="grupo-card">
                                    <div class="grupo-header" style="background-color: <?php echo $grupo['color']; ?>">
                                        <h5 class="mb-0">
                                            <i class="fas fa-users"></i> <?php echo htmlspecialchars($grupo['nombre_grupo']); ?>
                                            <span class="badge bg-light text-dark float-end"><?php echo $grupo['total_participantes']; ?> personas</span>
                                        </h5>
                                    </div>
                                    <div class="grupo-body">
                                        <?php
                                        $miembros = Inscripciones::getInscritosByGroup($grupo['numero_grupo']);
                                        if (empty($miembros)):
                                        ?>
                                        <p class="text-muted mb-0">No hay miembros en este grupo.</p>
                                        <?php else: ?>
                                        <ul class="list-unstyled mb-0">
                                            <?php foreach ($miembros as $miembro): ?>
                                            <li>
                                                <i class="fas fa-user text-muted"></i>
                                                <?php echo htmlspecialchars($miembro['nombres'] . ' ' . $miembro['apellidos']); ?>
                                                <span class="text-muted">(<?php echo $miembro['sexo']; ?>)</span>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Enlaces de pie de página -->
                <div class="footer-links">
                    <a href="index.php"><i class="fas fa-user-plus"></i> Nuevo Inscrito</a>
                    <a href="../index.php"><i class="fas fa-home"></i> Inicio</a>
                    <a href="../admin/login.php"><i class="fas fa-lock"></i> Administración</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Recordar pestaña activa
        document.addEventListener('DOMContentLoaded', function() {
            const triggerTabList = document.querySelectorAll('#reportesTab button');
            triggerTabList.forEach(triggerEl => {
                triggerEl.addEventListener('shown.bs.tab', function (event) {
                    // Guardar pestaña activa en localStorage
                    localStorage.setItem('activeTab', event.target.id);
                });
            });
            
            // Restaurar pestaña activa
            const activeTab = localStorage.getItem('activeTab');
            if (activeTab) {
                const tabEl = document.getElementById(activeTab);
                if (tabEl) {
                    const tab = new bootstrap.Tab(tabEl);
                    tab.show();
                }
            }
        });
    </script>
</body>
</html>