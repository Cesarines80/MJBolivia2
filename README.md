# Sitio Web Institucional con Panel Administrativo

Un sistema completo de gestión de contenido para instituciones educativas, empresas u organizaciones, desarrollado en PHP con MySQL y un panel administrativo moderno basado en AdminLTE.

## Características Principales

### 🌐 Sitio Web Público
- **Diseño Responsivo**: Compatible con todos los dispositivos
- **Tema Morado Pastel**: Diseño institucional elegante y profesional
- **Carrusel de Imágenes**: Gestión dinámica de imágenes y videos en el inicio
- **Secciones Principales**:
  - Inicio con carrusel
  - Acerca de
  - Misión, Visión y Valores
  - Eventos y Actividades
  - Galería de Imágenes
  - Contacto con formulario

### 🔐 Panel Administrativo
- **Autenticación Segura**: Sistema de login con protección contra fuerza bruta
- **Interfaz AdminLTE**: Panel moderno y profesional
- **Gestión Completa**:
  - Configuración general del sitio
  - Administración del carrusel principal
  - Gestión de eventos y actividades
  - Galería de imágenes con categorías
  - Misión, visión y valores
  - Mensajes de contacto

### 🛡️ Seguridad
- **Protección CSRF**: Tokens de seguridad en todos los formularios
- **Bloqueo por Intentos Fallidos**: Protección contra ataques de fuerza bruta
- **Hash de Contraseñas**: Encriptación segura con password_hash()
- **Validación de Entradas**: Sanitización de todos los datos de entrada
- **Sesiones Seguras**: Configuración avanzada de cookies y sesiones

### 🎨 Personalización
- **Colores Personalizables**: Panel para cambiar colores del tema
- **Logo y Favicon**: Subida de imagenes institucionales
- **Metadatos SEO**: Configuración de descripción y keywords
- **Google Analytics**: Integración con ID de seguimiento

## Tecnologías Utilizadas

- **Backend**: PHP 8.0+
- **Base de Datos**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 5
- **Panel Admin**: AdminLTE 3
- **Iconos**: Font Awesome 6
- **Fuentes**: Google Fonts (Poppins)

## Estructura de Base de Datos

El sistema incluye las siguientes tablas principales:

- `administradores`: Usuarios del sistema
- `configuracion`: Configuración general del sitio
- `carrusel`: Elementos del carrusel principal
- `eventos`: Eventos y actividades
- `galeria`: Imágenes de la galería
- `mision_vision`: Misión, visión, valores e historia
- `contactos`: Mensajes de contacto
- `paginas`: Páginas adicionales
- `sesiones_admin`: Control de sesiones
- `intentos_login`: Prevención de ataques

## Instalación

Sigue los pasos detallados en [INSTALAR.md](INSTALAR.md)

### Resumen Rápido

1. Descarga y descomprime el proyecto
2. Crea la base de datos e importa `config/database.sql`
3. Configura `config/config.php` con tus credenciales
4. Establece permisos de escritura en `assets/uploads/` y `logs/`
5. Accede al panel admin con:
   - Usuario: `admin@institucion.com`
   - Contraseña: `admin123`

## Uso del Sistema

### Panel Administrativo

1. **Dashboard**: Vista general con estadísticas y accesos rápidos
2. **Carrusel**: Gestión de imágenes y videos del inicio
3. **Eventos**: CRUD completo con fechas, horas y lugares
4. **Galería**: Organización por categorías con vista previa
5. **Misión y Visión**: Edición de contenido institucional
6. **Configuración**: Personalización completa del sitio
7. **Mensajes**: Gestión de contactos con estados

### Cambio de Contraseña

Es **obligatorio** cambiar la contraseña del administrador después del primer inicio de sesión:

1. Inicia sesión en el panel administrativo
2. Haz clic en tu nombre (esquina superior derecha)
3. Selecciona "Cambiar contraseña"
4. Ingresa tu contraseña actual y la nueva contraseña

## Capturas de Pantalla

### Página Principal
![Home Page](https://via.placeholder.com/800x400/8B7EC8/ffffff?text=Página+Principal)

### Panel Administrativo
![Admin Dashboard](https://via.placeholder.com/800x400/6B5B95/ffffff?text=Panel+Administrativo)

### Gestión de Eventos
![Event Management](https://via.placeholder.com/800x400/B8B3D8/333333?text=Gestión+de+Eventos)

## Seguridad Implementada

- ✅ Autenticación segura con intentos limitados
- ✅ Protección CSRF en todos los formularios
- ✅ Validación y sanitización de datos
- ✅ Encriptación de contraseñas con bcrypt
- ✅ Registro de actividad (logs)
- ✅ Sesiones seguras con cookies HttpOnly
- ✅ Bloqueo temporal por intentos fallidos

## Personalización de Colores

El sistema permite personalizar los colores del tema desde el panel administrativo:

- **Color Primario**: Por defecto #8B7EC8 (morado)
- **Color Secundario**: Por defecto #B8B3D8 (lavanda)
- **Color de Acento**: Por defecto #6B5B95 (morado oscuro)

## Soporte Técnico

### Requisitos Mínimos

- PHP 8.0+
- MySQL 5.7+
- Servidor Web (Apache/Nginx)
- 100MB de espacio en disco
- PHP Extensions: PDO, PDO_MySQL, GD, Fileinfo, Session

### Solución de Problemas

Consulta la sección de solución de problemas en [INSTALAR.md](INSTALAR.md)

## Contribuciones

Este proyecto es de código abierto. Las contribuciones son bienvenidas:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT. Ver archivo `LICENSE` para más detalles.

## Autor

**Desarrollado por:** Tu Nombre/Institución  
**Versión:** 1.0.0  
**Fecha:** <?php echo date('Y'); ?>

---

## Demo en Vivo

Pronto estará disponible una demo en línea.

## Documentación Adicional

- [Guía de Instalación](INSTALAR.md)
- [Documentación del Código](docs/)
- [Changelog](CHANGELOG.md)

---

**Sitio Web Institucional** - Potenciando la presencia digital de tu institución con tecnología moderna y segura.