<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/eventos_galeria.php';

// Obtener configuración del sitio
$config = SiteConfig::get();

// Obtener ID del evento
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: index.php');
    exit;
}

// Obtener el evento
$evento = Eventos::getById($id);

if (!$evento) {
    header('Location: index.php');
    exit;
}

// Obtener imágenes de la galería del evento
$galeria = EventosGaleria::getGalleryImages($id);

$colorPrimario = $config['color_primario'] ?? '#8B7EC8';
$colorSecundario = $config['color_secundario'] ?? '#B8B3D8';
$colorAcento = $config['color_acento'] ?? '#6B5B95';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="<?php echo htmlspecialchars($evento['titulo']); ?> - <?php echo htmlspecialchars($config['nombre_institucion']); ?>">
    <meta name="keywords" content="evento, <?php echo htmlspecialchars($evento['titulo']); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($config['nombre_institucion']); ?>">

    <title><?php echo htmlspecialchars($evento['titulo']); ?> -
        <?php echo htmlspecialchars($config['nombre_institucion']); ?></title>

    <?php if ($config['favicon']): ?>
        <link rel="icon" href="<?php echo UPLOADS_URL . $config['favicon']; ?>">
    <?php endif; ?>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        :root {
            --color-primario: <?php echo $colorPrimario;
                                ?>;
            --color-secundario: <?php echo $colorSecundario;
                                ?>;
            --color-acento: <?php echo $colorAcento;
                            ?>;
            --color-primario-light: <?php echo $colorPrimario;
                                    ?>20;
            --color-secundario-light: <?php echo $colorSecundario;
                                        ?>20;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Header */
        .event-header {
            background: linear-gradient(135deg, var(--color-primario), var(--color-secundario));
            color: white;
            padding: 2rem 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .event-header .container {
            position: relative;
        }

        .back-btn {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-50%) translateX(-5px);
            color: white;
            text-decoration: none;
        }

        .event-title {
            text-align: center;
            margin-bottom: 1rem;
        }

        .event-title h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        /* Event Details */
        .event-details {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin: 2rem 0;
        }

        .event-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        .event-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            background: var(--color-primario-light);
            padding: 0.75rem 1rem;
            border-radius: 25px;
            font-weight: 500;
        }

        .meta-item i {
            color: var(--color-primario);
            margin-right: 0.5rem;
        }

        .event-description {
            font-size: 1.1rem;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: var(--color-primario);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--color-secundario);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Gallery */
        .event-gallery {
            margin-top: 3rem;
        }

        .gallery-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .gallery-title h2 {
            color: var(--color-primario);
            font-weight: 600;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .gallery-item {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            background: white;
        }

        .gallery-item:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }

        .gallery-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
            color: white;
            padding: 2rem 1.5rem 1.5rem;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .gallery-item:hover .gallery-overlay {
            transform: translateY(0);
        }

        .gallery-overlay h5 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .gallery-overlay p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .event-details,
        .gallery-item {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1rem;
            }

            .event-header {
                padding: 1.5rem 0;
            }

            .event-title h1 {
                font-size: 2rem;
            }

            .event-meta {
                flex-direction: column;
                align-items: stretch;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons .btn {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .gallery-grid {
                grid-template-columns: 1fr;
            }

            .back-btn {
                position: static;
                transform: none;
                display: inline-block;
                margin-bottom: 1rem;
            }

            .event-title {
                text-align: left;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="event-header">
        <div class="container">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Volver al Inicio
            </a>
            <div class="event-title">
                <h1><?php echo htmlspecialchars($evento['titulo']); ?></h1>
            </div>
        </div>
    </header>

    <!-- Event Details -->
    <section class="container">
        <div class="event-details">
            <?php if ($evento['imagen']): ?>
                <img src="<?php echo UPLOADS_URL . $evento['imagen']; ?>"
                    alt="<?php echo htmlspecialchars($evento['titulo']); ?>" class="event-image">
            <?php endif; ?>

            <div class="event-meta">
                <div class="meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    <?php echo formatDate($evento['fecha_evento'], 'd/m/Y'); ?>
                    <?php if ($evento['hora_evento']): ?>
                        - <?php echo date('H:i', strtotime($evento['hora_evento'])); ?>
                    <?php endif; ?>
                </div>

                <?php if ($evento['lugar']): ?>
                    <div class="meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($evento['lugar']); ?>
                    </div>
                <?php endif; ?>

                <?php if ($evento['costo']): ?>
                    <div class="meta-item">
                        <i class="fas fa-dollar-sign"></i>
                        Bs. <?php echo number_format($evento['costo'], 2); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="event-description">
                <?php echo nl2br(htmlspecialchars($evento['descripcion'])); ?>
            </div>

            <div class="action-buttons">
                <a href="eventos/inscribir.php?evento=<?php echo $evento['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Inscribirse al Evento
                </a>
            </div>
        </div>

        <!-- Event Gallery -->
        <?php if (!empty($galeria)): ?>
            <div class="event-gallery">
                <div class="gallery-title">
                    <h2>Galería del Evento</h2>
                </div>
                <div class="gallery-grid">
                    <?php foreach ($galeria as $item): ?>
                        <div class="gallery-item">
                            <img src="<?php echo UPLOADS_URL . $item['imagen']; ?>"
                                alt="<?php echo htmlspecialchars($item['titulo'] ?? 'Imagen del evento'); ?>">
                            <div class="gallery-overlay">
                                <?php if ($item['titulo']): ?>
                                    <h5><?php echo htmlspecialchars($item['titulo']); ?></h5>
                                <?php endif; ?>
                                <?php if ($item['descripcion']): ?>
                                    <p><?php echo htmlspecialchars($item['descripcion']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animación de entrada
            const items = document.querySelectorAll('.gallery-item');
            items.forEach((item, index) => {
                item.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>

</html>