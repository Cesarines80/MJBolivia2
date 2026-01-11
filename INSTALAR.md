# Guía de Instalación - Sitio Web Institucional

## Requisitos del Sistema

- **Servidor Web**: Apache o Nginx
- **PHP**: Versión 8.0 o superior
- **MySQL/MariaDB**: Versión 5.7 o superior
- **Extensiones PHP Requeridas**:
  - PDO
  - PDO_MySQL
  - GD (para manejo de imágenes)
  - Fileinfo
  - Session

## Pasos de Instalación

### 1. Descarga y Descompresión

1. Descomprime el archivo en el directorio de tu servidor web
2. El proyecto debe estar en la raíz del servidor o en un subdirectorio

### 2. Configuración de la Base de Datos

#### Opción A: Usar el script SQL incluido

1. Accede a tu servidor MySQL
2. Crea una base de datos:
   ```sql
   CREATE DATABASE web_institucional CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
   ```
3. Importa el archivo `config/database.sql`:
   ```bash
   mysql -u tu_usuario -p web_institucional < config/database.sql
   ```

#### Opción B: Configuración manual

1. Crea la base de datos como en el paso anterior
2. Copia el contenido del archivo `config/database.sql` y ejecútalo en tu gestor de base de datos

### 3. Configuración del Sistema

1. Abre el archivo `config/config.php`
2. Modifica las constantes de configuración de la base de datos:

```php
define('DB_HOST', 'localhost');        // Servidor de base de datos
define('DB_USER', 'tu_usuario');       // Usuario de MySQL
define('DB_PASS', 'tu_contraseña');    // Contraseña de MySQL
define('DB_NAME', 'web_institucional'); // Nombre de la base de datos
```

3. Modifica la URL del sitio si es necesario:

```php
define('SITE_URL', 'http://localhost/web_institucional/');
```

### 4. Permisos de Archivos

Asegúrate de que el servidor tenga permisos de escritura en los siguientes directorios:

```bash
# En Linux/Mac
chmod 755 /ruta/al/proyecto/
chmod 755 /ruta/al/proyecto/assets/uploads/
chmod 755 /ruta/al/proyecto/logs/

# Si tienes problemas, puedes usar 777 (no recomendado en producción)
chmod 777 /ruta/al/proyecto/assets/uploads/
chmod 777 /ruta/al/proyecto/logs/
```

### 5. Crear Directorios Necesarios

Si no existen, crea los siguientes directorios:

```bash
mkdir -p assets/uploads
mkdir -p logs
```

### 6. Acceso al Sistema

#### Sitio Web Público
- URL: `http://tu-servidor/web_institucional/`

#### Panel Administrativo
- URL: `http://tu-servidor/web_institucional/admin/`
- Usuario por defecto: `admin@institucion.com`
- Contraseña por defecto: `admin123`

**⚠️ IMPORTANTE: Cambia la contraseña del administrador después del primer inicio de sesión.**

## Configuración Adicional

### Configuración de Apache

Si usas Apache, asegúrate de que el módulo `mod_rewrite` esté habilitado:

```bash
sudo a2enmod rewrite
sudo service apache2 restart
```

### Configuración de Nginx

Si usas Nginx, asegúrate de configurar correctamente el `try_files`:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Configuración de PHP

Asegúrate de que la directiva `upload_max_filesize` en `php.ini` sea al menos 10M:

```ini
upload_max_filesize = 10M
post_max_size = 10M
```

## Personalización

### Cambiar Contraseña de Administrador

1. Inicia sesión en el panel administrativo
2. Ve a "Mi perfil" (en el menú superior derecho)
3. Selecciona "Cambiar contraseña"
4. Ingresa tu contraseña actual y la nueva contraseña

### Configuración del Sitio

1. Accede al panel administrativo
2. Ve a "Configuración" en el menú lateral
3. Personaliza:
   - Nombre de la institución
   - Descripción
   - Información de contacto
   - Redes sociales
   - Colores del tema
   - Logo y favicon

## Estructura del Proyecto

```
web_institucional/
├── admin/                    # Panel administrativo
│   ├── ajax.php             # Peticiones AJAX
│   ├── carrusel.php         # Gestión de carrusel
│   ├── configuracion.php    # Configuración general
│   ├── contactos.php        # Mensajes de contacto
│   ├── dashboard.php        # Dashboard principal
│   ├── eventos.php          # Gestión de eventos
│   ├── galeria.php          # Gestión de galería
│   ├── login.php            # Inicio de sesión
│   ├── logout.php           # Cierre de sesión
│   └── mision-vision.php    # Misión y visión
├── assets/                   # Recursos públicos
│   ├── css/                 # Estilos CSS
│   ├── js/                  # Scripts JavaScript
│   ├── images/              # Imágenes del sistema
│   └── uploads/             # Archivos subidos por usuarios
├── config/                   # Configuración
│   ├── config.php           # Configuración principal
│   └── database.sql         # Estructura de base de datos
├── includes/                 # Archivos incluidos
│   ├── auth.php             # Sistema de autenticación
│   └── functions.php        # Funciones del sistema
├── logs/                     # Registros del sistema
├── index.php                 # Página principal
└── INSTALAR.md              # Este archivo
```

## Solución de Problemas

### Error de conexión a base de datos

1. Verifica que las credenciales en `config/config.php` sean correctas
2. Asegúrate de que el servidor MySQL esté ejecutándose
3. Comprueba que la base de datos haya sido creada correctamente

### Error 404 al acceder al sitio

1. Verifica que el archivo `.htaccess` exista (si usas Apache)
2. Asegúrate de que `mod_rewrite` esté habilitado
3. Comprueba que la URL en `config/config.php` sea correcta

### Error al subir imágenes

1. Verifica los permisos del directorio `assets/uploads/`
2. Asegúrate de que la extensión GD de PHP esté habilitada
3. Comprueba que el tamaño del archivo no exceda los límites de PHP

### Página en blanco

1. Activa la visualización de errores en `config/config.php`:
   ```php
   ini_set('display_errors', 1);
   ```
2. Revisa los logs de error de PHP
3. Verifica los logs del sistema en `logs/error.log`

## Seguridad

- Cambia las contraseñas por defecto inmediatamente
- Usa HTTPS en producción
- Mantén el sistema actualizado
- Realiza respaldos regulares de la base de datos
- No compartas credenciales de administrador

## Soporte

Para reportar problemas o solicitar ayuda:

1. Revisa primero esta guía de instalación
2. Verifica los logs del sistema
3. Consulta la documentación del código

## Licencia

Este proyecto es de código abierto y está disponible para uso educativo y comercial.

---

**Última actualización: <?php echo date('Y-m-d'); ?>**