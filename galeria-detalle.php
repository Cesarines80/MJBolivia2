<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/galeria_imagenes.php';

// Obtener configuración del sitio
$config = SiteConfig::get();

// Obtener ID del elemento seleccionado
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: index.php');
    exit;
}

// Obtener el elemento seleccionado
$item = Galeria::getById($id);

if (!$item) {
    header('Location: index.php');
    exit;
}

// Obtener imágenes adicionales de la galería
$galeria_imagenes = GaleriaImagenes::getGalleryImages($id);

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
        content="<?php echo htmlspecialchars($item['titulo']); ?> - <?php echo htmlspecialchars($config['nombre_institucion']); ?>">
    <meta name="keywords" content="galería, fotos, <?php echo htmlspecialchars($item['categoria']); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($config['nombre_institucion']); ?>">

    <title><?php echo htmlspecialchars($item['titulo']); ?> -
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
        .gallery-header {
            background: linear-gradient(135deg, var(--color-primario), var(--color-secundario));
            color: white;
            padding: 2rem 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .gallery-header .container {
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

        .gallery-title {
            text-align: center;
            margin-bottom: 1rem;
        }

        .gallery-title h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .gallery-category {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Gallery Details */
        .gallery-details {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin: 2rem 0;
        }

        .main-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        .gallery-description {
            font-size: 1.1rem;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        /* Gallery Images */
        .gallery-images {
            margin-top: 3rem;
        }

        .gallery-images-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .gallery-images-title h2 {
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

        .gallery-details,
        .gallery-item {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1rem;
            }

            .gallery-header {
                padding: 1.5rem 0;
            }

            .gallery-title h1 {
                font-size: 2rem;
            }

            .main-image {
                max-height: 300px;
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

            .gallery-title {
                text-align: left;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="gallery-header">
        <div class="container">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Volver al Inicio
            </a>
            <div class="gallery-title">
                <h1><?php echo htmlspecialchars($item['titulo']); ?></h1>
                <span class="gallery-category"><?php echo htmlspecialchars($item['categoria']); ?></span>
            </div>
        </div>
    </header>

    <!-- Gallery Details -->
    <section class="container">
        <div class="gallery-details">
            <div class="gallery-description">
                <?php echo nl2br(htmlspecialchars($item['descripcion'])); ?>
            </div>
        </div>

        <!-- All Gallery Images -->
        <div class="gallery-images">
            <div class="gallery-images-title">
                <h2>Imágenes de la Galería</h2>
            </div>
            <div class="gallery-grid">
                <!-- Cover Image -->
                <div class="gallery-item">
                    <img src="<?php echo UPLOADS_URL . $item['imagen']; ?>"
                        alt="<?php echo htmlspecialchars($item['titulo']); ?>">
                    <div class="gallery-overlay">
                        <h5><?php echo htmlspecialchars($item['titulo']); ?></h5>
                        <p>Imagen Principal</p>
                    </div>
                </div>

                <!-- Additional Gallery Images -->
                <?php foreach ($galeria_imagenes as $imagen): ?>
                    <div class="gallery-item">
                        <img src="<?php echo UPLOADS_URL . $imagen['imagen']; ?>"
                            alt="<?php echo htmlspecialchars($imagen['titulo'] ?? 'Imagen de galería'); ?>">
                        <div class="gallery-overlay">
                            <?php if ($imagen['titulo']): ?>
                                <h5><?php echo htmlspecialchars($imagen['titulo']); ?></h5>
                            <?php endif; ?>
                            <?php if ($imagen['descripcion']): ?>
                                <p><?php echo htmlspecialchars($imagen['descripcion']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content" style="background: transparent; border: none;">
                <div class="modal-body p-0">
                    <img id="modalImage" src="" alt="" class="img-fluid"
                        style="width: 100%; max-height: 90vh; object-fit: contain;">
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                    data-bs-dismiss="modal" aria-label="Close" style="z-index: 1050;"></button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animación de entrada
            const items = document.querySelectorAll('.gallery-item');
            items.forEach(function(item, index) {
                item.style.animationDelay = (index * 0.1) + 's';
            });

            // Modal functionality for gallery images
            const galleryItems = document.querySelectorAll('.gallery-item');
            const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
            const modalImage = document.getElementById('modalImage');

            galleryItems.forEach(function(item) {
                item.addEventListener('click', function() {
                    const img = this.querySelector('img');
                    modalImage.src = img.src;
                    modalImage.alt = img.alt;
                    imageModal.show();
                });
            });

            // Close modal when clicking outside the image
            document.getElementById('imageModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    imageModal.hide();
                }
            });
        });
    </script>
</body>

</html>