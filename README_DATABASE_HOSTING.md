# Preparación de Base de Datos para Hosting Externo

## Archivo de Base de Datos
El archivo `database_completa_actualizada.sql` contiene la base de datos completa con todas las modificaciones realizadas hasta la fecha, incluyendo:
- Estructura de todas las tablas (25 tablas)
- Datos actuales de la aplicación
- Configuraciones y usuarios

## Pasos para Importar en Hosting Externo

1. **Crear la Base de Datos:**
   - En tu panel de control del hosting, crea una nueva base de datos MySQL/MariaDB
   - Anota el nombre de la base de datos, usuario y contraseña

2. **Importar el Archivo SQL:**
   - Sube el archivo `database_completa_actualizada.sql` a tu hosting
   - Usa phpMyAdmin o la herramienta de importación de tu hosting para importar el archivo
   - Asegúrate de que la base de datos esté vacía antes de importar

3. **Configurar Conexión:**
   - Edita el archivo `config/config.php` con las credenciales de tu hosting:
     ```php
     define('DB_HOST', 'tu_host_mysql');
     define('DB_NAME', 'tu_base_datos');
     define('DB_USER', 'tu_usuario');
     define('DB_PASS', 'tu_contraseña');
     ```

4. **Verificar Instalación:**
   - Sube todos los archivos del proyecto al hosting
   - Accede a la URL de tu sitio
   - Verifica que el login funcione con los usuarios existentes

## Usuarios por Defecto
- **Super Admin:** superadmin / [contraseña en password_hash.txt]
- Otros usuarios administradores están incluidos en el dump

## Notas Importantes
- El archivo incluye datos de prueba y configuraciones actuales
- Asegúrate de que el hosting soporte PHP 7.4+ y MySQL 5.7+
- Verifica permisos de escritura en la carpeta `assets/uploads/` para subir imágenes
- Configura la zona horaria en `config/config.php` si es necesario

## Tamaño del Archivo
- database_completa_actualizada.sql: ~86KB
- Exportado el: 2026-01-16 07:43:52
