# Correcciones para que funcione en el servidor

## Problemas identificados:
1. **Galería no muestra imágenes**: UPLOADS_URL estaba hardcoded a localhost.
2. **Error 500 al guardar evento**: Esquema de base de datos incompleto.

## Cambios realizados en local:

### 1. Configuración dinámica de URLs (config/config.php)
- SITE_URL ahora se detecta automáticamente según el dominio actual.
- UPLOADS_URL cambiado a '/assets/uploads/' (relativo) para que funcione en cualquier dominio.

### 2. Base de datos
- Ejecutado alter_eventos_table.php para agregar columnas faltantes a la tabla eventos.
- Ejecutado create_eventos_galeria_table.php
- Ejecutado create_galeria_imagenes_table.php
- Ejecutado create_missing_tables.php para crear tablas faltantes:
  - configuracion_eventos
  - log_actividades
  - eventos_administradores
  - sesiones
  - intentos_login
  - permisos
  - configuracion_global
  - inscripciones_eventos

### 3. Renombrado columna
- Cambiado 'nombre' a 'titulo' en tabla eventos para coincidir con el código.

## Para que funcione en el servidor:

### Base de datos:
- Asegurarse de que la base de datos del servidor tenga las mismas tablas y columnas que la local.
- Si es posible, exportar la base de datos local y importarla en el servidor.
- Si no, ejecutar los mismos scripts en el servidor.

### Configuración:
- En config/config.php del servidor, cambiar DB_HOST, DB_USER, DB_PASS, DB_NAME a los valores del servidor.
- SITE_URL se detectará automáticamente.
- UPLOADS_URL es relativo, funcionará.

### Archivos:
- Subir todos los archivos actualizados al servidor.
- Asegurarse de que la carpeta assets/uploads/ tenga permisos de escritura.

## Variables de configuración que deben ser iguales en local y servidor:
- DB_HOST: Host de la base de datos
- DB_USER: Usuario de la base de datos
- DB_PASS: Contraseña de la base de datos
- DB_NAME: Nombre de la base de datos
- UPLOADS_DIR: Ruta absoluta al directorio de uploads (se ajusta automáticamente)
- UPLOADS_URL: Ahora relativo, no necesita cambio

## Pruebas:
- Probar crear un evento en admin/eventos.php
- Verificar que la galería muestre imágenes en index.php
