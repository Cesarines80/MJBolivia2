<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Obtener configuración del sitio
$config = SiteConfig::get();

// Obtener datos para la página
$carrusel = Carrusel::getAll();
$eventos = Eventos::getUpcoming(6);
$mision = MisionVision::getByTipo('mision');
$vision = MisionVision::getByTipo('vision');
$valores = MisionVision::getByTipo('valores');
$historia = MisionVision::getByTipo('historia');

$colorPrimario = $config['color_primario'] ?? '#8B7EC8';
$colorSecundario = $config['color_secundario'] ?? '#B8B3D8';
$colorAcento = $config['color_acento'] ?? '#6B5B95';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($config['metadescription'] ?? $config['nombre_institucion']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($config['metakeywords'] ?? 'institución, educación, servicios'); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($config['nombre_institucion']); ?>">
    
    <title><?php echo htmlspecialchars($config['nombre_institucion']); ?></title>
    
    <?php if ($config['favicon']): ?>
    <link rel="icon" href="<?php echo UPLOADS_URL . $config['favicon']; ?>">
    <?php endif; ?>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --color-primario: <?php echo $colorPrimario; ?>;
            --color-secundario: <?php echo $colorSecundario; ?>;
            --color-acento: <?php echo $colorAcento; ?>;
            --color-primario-light: <?php echo $colorPrimario; ?>20;
            --color-secundario-light: <?php echo $colorSecundario; ?>20;
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
            background-color: #f8f9fa;
        }
        
        /* Navbar */
        .navbar {
            background: linear-gradient(135deg, var(--color-primario), var(--color-secundario));
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .navbar-nav .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            margin: 0 8px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .navbar-nav .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }
        
        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: white;
            transition: width 0.3s ease;
        }
        
        .navbar-nav .nav-link:hover::after {
            width: 100%;
        }
        
        /* Hero Section / Carrusel */
        .hero-carousel {
            position: relative;
            height: 600px;
            overflow: hidden;
        }
        
        .carousel-item {
            height: 600px;
            position: relative;
        }
        
        .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .carousel-caption {
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(5px);
            border-radius: 15px;
            padding: 2rem;
            max-width: 600px;
            left: 50%;
            transform: translateX(-50%);
            bottom: 50px;
        }
        
        .carousel-caption h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
        }
        
        .carousel-caption p {
            font-size: 1.1rem;
            color: rgba(255,255,255,0.9);
        }
        
        .carousel-indicators [data-bs-target] {
            background-color: var(--color-primario);
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        
        .carousel-control-prev,
        .carousel-control-next {
            background: var(--color-primario);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.8;
        }
        
        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            opacity: 1;
        }
        
        /* Section Styles */
        .section {
            padding: 80px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--color-primario);
            margin-bottom: 1rem;
            position: relative;
        }
        
        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--color-primario), var(--color-secundario));
            border-radius: 2px;
        }
        
        .section-title p {
            color: #666;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-title {
            color: var(--color-primario);
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        
        /* Misión, Visión, Historia */
        .feature-box {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .feature-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(180deg, var(--color-primario), var(--color-secundario));
        }
        
        .feature-box .icon {
            width: 60px;
            height: 60px;
            background: var(--color-primario-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .feature-box .icon i {
            font-size: 1.5rem;
            color: var(--color-primario);
        }
        
        .feature-box h3 {
            color: var(--color-primario);
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        /* Eventos */
        .event-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .event-date {
            background: var(--color-primario);
            color: white;
            padding: 1rem;
            text-align: center;
            min-width: 100px;
        }
        
        .event-date .day {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .event-date .month {
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        
        .event-content {
            padding: 1rem;
            flex: 1;
        }
        
        .event-content h4 {
            color: var(--color-primario);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .event-content .event-meta {
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Galería */
        .gallery-item {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .gallery-item:hover {
            transform: scale(1.05);
        }
        
        .gallery-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            color: white;
            padding: 1.5rem;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        
        .gallery-item:hover .gallery-overlay {
            transform: translateY(0);
        }
        
        /* Contacto */
        .contact-info {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        
        .contact-info .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .contact-info .info-item i {
            width: 40px;
            height: 40px;
            background: var(--color-primario-light);
            color: var(--color-primario);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
        
        .contact-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--color-primario);
            box-shadow: 0 0 0 0.2rem var(--color-primario-light);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--color-primario), var(--color-secundario));
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--color-primario), var(--color-acento));
            color: white;
            padding: 3rem 0 1rem;
        }
        
        .footer h5 {
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .footer a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer a:hover {
            color: white;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.2);
            margin-top: 2rem;
            padding-top: 1rem;
            text-align: center;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-carousel {
                height: 400px;
            }
            
            .carousel-item {
                height: 400px;
            }
            
            .carousel-caption {
                padding: 1rem;
                bottom: 20px;
            }
            
            .carousel-caption h2 {
                font-size: 1.8rem;
            }
            
            .section {
                padding: 50px 0;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
        }
        
        /* Animaciones */
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
        
        .animate-on-scroll {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <?php if ($config['logo']): ?>
                <img src="<?php echo UPLOADS_URL . $config['logo']; ?>" alt="Logo" height="40">
                <?php else: ?>
                <i class="fas fa-building"></i>
                <?php endif; ?>
                <?php echo htmlspecialchars($config['nombre_institucion']); ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Acerca de</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#mission">Misión & Visión</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#events">Eventos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#gallery">Galería</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contacto</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">
                            <i class="fas fa-lock"></i> Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section / Carrusel -->
    <section id="home" class="hero-carousel">
        <?php if (empty($carrusel)): ?>
        <!-- Carrusel por defecto -->
        <div class="carousel-item active">
            <img src="https://via.placeholder.com/1920x600/<?php echo substr($colorPrimario, 1); ?>/ffffff?text=Bienvenidos" alt="Bienvenidos">
            <div class="carousel-caption">
                <h2>Bienvenidos a <?php echo htmlspecialchars($config['nombre_institucion']); ?></h2>
                <p><?php echo htmlspecialchars($config['descripcion'] ?? 'Educación de excelencia para el futuro de nuestros estudiantes'); ?></p>
            </div>
        </div>
        <?php else: ?>
        <div id="carouselPrincipal" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <?php foreach ($carrusel as $index => $item): ?>
                <button type="button" data-bs-target="#carouselPrincipal" data-bs-slide-to="<?php echo $index; ?>" 
                        <?php echo $index === 0 ? 'class="active"' : ''; ?>></button>
                <?php endforeach; ?>
            </div>
            
            <div class="carousel-inner">
                <?php foreach ($carrusel as $index => $item): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <img src="<?php echo UPLOADS_URL . $item['imagen']; ?>" alt="<?php echo htmlspecialchars($item['titulo']); ?>">
                    <div class="carousel-caption">
                        <h2><?php echo htmlspecialchars($item['titulo']); ?></h2>
                        <p><?php echo htmlspecialchars($item['descripcion'] ?? ''); ?></p>
                        <?php if ($item['url']): ?>
                        <a href="<?php echo htmlspecialchars($item['url']); ?>" class="btn btn-light mt-3">
                            Saber más <i class="fas fa-arrow-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselPrincipal" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselPrincipal" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
        <?php endif; ?>
    </section>

    <!-- About Section -->
    <section id="about" class="section bg-light">
        <div class="container">
            <div class="section-title">
                <h2>Acerca de Nosotros</h2>
                <p><?php echo htmlspecialchars($config['descripcion'] ?? 'Conoce más sobre nuestra institución'); ?></p>
            </div>
            
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="feature-box">
                        <div class="icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <h3>Nuestra Historia</h3>
                        <p><?php echo nl2br(htmlspecialchars($historia['contenido'] ?? 'Historia de nuestra institución')); ?></p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="https://via.placeholder.com/500x300/<?php echo substr($colorSecundario, 1); ?>/333333?text=Institución" 
                         alt="Acerca de nosotros" class="img-fluid rounded-3 shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision Section -->
    <section id="mission" class="section">
        <div class="container">
            <div class="section-title">
                <h2>Misión y Visión</h2>
                <p>Conoce nuestros principios y objetivos institucionales</p>
            </div>
            
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="feature-box h-100">
                        <div class="icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($mision['titulo'] ?? 'Nuestra Misión'); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($mision['contenido'] ?? 'Misión de la institución')); ?></p>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="feature-box h-100">
                        <div class="icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($vision['titulo'] ?? 'Nuestra Visión'); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($vision['contenido'] ?? 'Visión de la institución')); ?></p>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="feature-box h-100">
                        <div class="icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($valores['titulo'] ?? 'Nuestros Valores'); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($valores['contenido'] ?? 'Valores de la institución')); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Events Section -->
    <section id="events" class="section bg-light">
        <div class="container">
            <div class="section-title">
                <h2>Próximos Eventos</h2>
                <p>Enterate de todas nuestras actividades y eventos</p>
            </div>
            
            <?php if (empty($eventos)): ?>
            <div class="text-center">
                <p class="text-muted">No hay eventos programados en este momento.</p>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($eventos as $evento): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card">
                        <?php if ($evento['imagen']): ?>
                        <img src="<?php echo UPLOADS_URL . $evento['imagen']; ?>" class="card-img-top" 
                             alt="<?php echo htmlspecialchars($evento['titulo']); ?>">
                        <?php else: ?>
                        <img src="https://via.placeholder.com/400x200/<?php echo substr($colorSecundario, 1); ?>/333333?text=Evento" 
                             class="card-img-top" alt="<?php echo htmlspecialchars($evento['titulo']); ?>">
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>
                                <small class="text-muted">
                                    <?php echo formatDate($evento['fecha_evento'], 'd/m/Y'); ?>
                                    <?php if ($evento['hora_evento']): ?>
                                    - <?php echo date('H:i', strtotime($evento['hora_evento'])); ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                            
                            <?php if ($evento['lugar']): ?>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                <small class="text-muted"><?php echo htmlspecialchars($evento['lugar']); ?></small>
                            </div>
                            <?php endif; ?>
                            
                            <h5 class="card-title"><?php echo htmlspecialchars($evento['titulo']); ?></h5>
                            <p class="card-text"><?php echo limitText($evento['descripcion'], 100); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Gallery Section -->
    <section id="gallery" class="section">
        <div class="container">
            <div class="section-title">
                <h2>Galería</h2>
                <p>Explora nuestra galería de imágenes</p>
            </div>
            
            <?php
            $galeria = Galeria::getAll();
            if (empty($galeria)):
            ?>
            <div class="row">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="gallery-item">
                        <img src="https://via.placeholder.com/400x300/<?php echo substr($colorSecundario, 1); ?>/333333?text=Imagen+<?php echo $i; ?>" 
                             alt="Galería <?php echo $i; ?>">
                        <div class="gallery-overlay">
                            <h5>Imagen <?php echo $i; ?></h5>
                            <p>Descripción de la imagen</p>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($galeria as $item): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="gallery-item" data-bs-toggle="modal" data-bs-target="#galleryModal<?php echo $item['id']; ?>">
                        <img src="<?php echo UPLOADS_URL . $item['imagen']; ?>" 
                             alt="<?php echo htmlspecialchars($item['titulo']); ?>">
                        <div class="gallery-overlay">
                            <h5><?php echo htmlspecialchars($item['titulo']); ?></h5>
                            <p><?php echo htmlspecialchars($item['descripcion'] ?? ''); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Modal -->
                <div class="modal fade" id="galleryModal<?php echo $item['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><?php echo htmlspecialchars($item['titulo']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <img src="<?php echo UPLOADS_URL . $item['imagen']; ?>" 
                                     class="img-fluid" alt="<?php echo htmlspecialchars($item['titulo']); ?>">
                                <?php if ($item['descripcion']): ?>
                                <p class="mt-3"><?php echo nl2br(htmlspecialchars($item['descripcion'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section bg-light">
        <div class="container">
            <div class="section-title">
                <h2>Contacto</h2>
                <p>¿Tienes preguntas? No dudes en contactarnos</p>
            </div>
            
            <div class="row">
                <div class="col-lg-4">
                    <div class="contact-info">
                        <h4 class="mb-3">Información de Contacto</h4>
                        
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>Dirección:</strong><br>
                                <?php echo nl2br(htmlspecialchars($config['direccion'] ?? 'Dirección no especificada')); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Teléfono:</strong><br>
                                <?php echo htmlspecialchars($config['telefono'] ?? 'No especificado'); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>Email:</strong><br>
                                <?php echo htmlspecialchars($config['email_contacto'] ?? 'No especificado'); ?>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5>Síguenos</h5>
                            <div class="d-flex gap-2">
                                <?php if ($config['facebook']): ?>
                                <a href="<?php echo htmlspecialchars($config['facebook']); ?>" target="_blank" class="btn btn-outline-primary">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($config['twitter']): ?>
                                <a href="<?php echo htmlspecialchars($config['twitter']); ?>" target="_blank" class="btn btn-outline-info">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($config['instagram']): ?>
                                <a href="<?php echo htmlspecialchars($config['instagram']); ?>" target="_blank" class="btn btn-outline-danger">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($config['youtube']): ?>
                                <a href="<?php echo htmlspecialchars($config['youtube']); ?>" target="_blank" class="btn btn-outline-danger">
                                    <i class="fab fa-youtube"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-8">
                    <div class="contact-form">
                        <h4 class="mb-3">Envíanos un mensaje</h4>
                        
                        <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_form'])) {
                            $data = [
                                'nombre' => cleanInput($_POST['nombre']),
                                'email' => cleanInput($_POST['email']),
                                'telefono' => cleanInput($_POST['telefono'] ?? ''),
                                'asunto' => cleanInput($_POST['asunto']),
                                'mensaje' => cleanInput($_POST['mensaje'])
                            ];
                            
                            if (Contactos::create($data)) {
                                echo '<div class="alert alert-success">Mensaje enviado correctamente. Nos pondremos en contacto contigo pronto.</div>';
                            } else {
                                echo '<div class="alert alert-danger">Error al enviar el mensaje. Por favor, intenta de nuevo.</div>';
                            }
                        }
                        ?>
                        
                        <form method="POST" action="#contact">
                            <input type="hidden" name="contact_form" value="1">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre completo *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="asunto" class="form-label">Asunto *</label>
                                    <input type="text" class="form-control" id="asunto" name="asunto" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mensaje" class="form-label">Mensaje *</label>
                                <textarea class="form-control" id="mensaje" name="mensaje" rows="5" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Enviar mensaje
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5><?php echo htmlspecialchars($config['nombre_institucion']); ?></h5>
                    <p class="text-muted"><?php echo htmlspecialchars($config['descripcion'] ?? ''); ?></p>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Enlaces rápidos</h5>
                    <ul class="list-unstyled">
                        <li><a href="#home">Inicio</a></li>
                        <li><a href="#about">Acerca de</a></li>
                        <li><a href="#mission">Misión y Visión</a></li>
                        <li><a href="#events">Eventos</a></li>
                        <li><a href="#gallery">Galería</a></li>
                        <li><a href="#contact">Contacto</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Contacto</h5>
                    <ul class="list-unstyled text-muted">
                        <?php if ($config['direccion']): ?>
                        <li><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($config['direccion']); ?></li>
                        <?php endif; ?>
                        <?php if ($config['telefono']): ?>
                        <li><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($config['telefono']); ?></li>
                        <?php endif; ?>
                        <?php if ($config['email_contacto']): ?>
                        <li><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($config['email_contacto']); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Síguenos</h5>
                    <div class="d-flex gap-2">
                        <?php if ($config['facebook']): ?>
                        <a href="<?php echo htmlspecialchars($config['facebook']); ?>" target="_blank" class="btn btn-outline-light">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($config['twitter']): ?>
                        <a href="<?php echo htmlspecialchars($config['twitter']); ?>" target="_blank" class="btn btn-outline-light">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($config['instagram']): ?>
                        <a href="<?php echo htmlspecialchars($config['instagram']); ?>" target="_blank" class="btn btn-outline-light">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($config['youtube']): ?>
                        <a href="<?php echo htmlspecialchars($config['youtube']); ?>" target="_blank" class="btn btn-outline-light">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($config['nombre_institucion']); ?>. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 100) {
                navbar.style.background = 'rgba(<?php echo hex2rgb($colorPrimario); ?>, 0.95)';
                navbar.style.backdropFilter = 'blur(10px)';
            } else {
                navbar.style.background = 'linear-gradient(135deg, var(--color-primario), var(--color-secundario))';
                navbar.style.backdropFilter = 'none';
            }
        });
        
        // Animación on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-on-scroll');
                }
            });
        }, observerOptions);
        
        // Observar elementos
        document.querySelectorAll('.card, .feature-box, .event-card').forEach(el => {
            observer.observe(el);
        });
        
        // Actualizar año en footer
        document.addEventListener('DOMContentLoaded', function() {
            // Si hay analytics, cargarlo
            <?php if ($config['analytics_id']): ?>
            // Google Analytics
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?php echo $config['analytics_id']; ?>');
            <?php endif; ?>
        });
    </script>
    
    <?php if ($config['analytics_id']): ?>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $config['analytics_id']; ?>"></script>
    <?php endif; ?>
</body>
</html>

<?php
// Función auxiliar para convertir hex a rgb
function hex2rgb($hex) {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    return "$r, $g, $b";
}
?>