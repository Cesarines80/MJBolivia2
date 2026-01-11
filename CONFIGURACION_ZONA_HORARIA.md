# 🌍 Configuración de Zona Horaria - Guía Completa

## Problema Común
El servidor PHP puede tener una zona horaria diferente a la de tu PC, causando problemas con fechas y horas en el sistema.

---

## 📍 Paso 1: Identificar tu Zona Horaria

### Para Bolivia:
```
Zona Horaria: America/La_Paz
UTC Offset: UTC-4 (sin cambio de horario de verano)
```

### Otras zonas horarias comunes en Latinoamérica:
```
Argentina:     America/Argentina/Buenos_Aires
Chile:         America/Santiago
Perú:          America/Lima
Colombia:      America/Bogota
México:        America/Mexico_City
España:        Europe/Madrid
```

---

## 🔧 Paso 2: Configurar Zona Horaria en PHP

### Opción 1: Configurar en php.ini (Recomendado para Producción)

**Ubicación del archivo php.ini en XAMPP:**
```
Windows: C:\xampp\php\php.ini
Linux: /opt/lampp/etc/php.ini
Mac: /Applications/XAMPP/xamppfiles/etc/php.ini
```

**Pasos:**
1. Abrir `php.ini` con un editor de texto
2. Buscar la línea: `date.timezone`
3. Descomentar (quitar el `;` al inicio) y configurar:

```ini
; Antes (comentado):
;date.timezone =

; Después (para Bolivia):
date.timezone = America/La_Paz
```

4. Guardar el archivo
5. Reiniciar Apache desde el Panel de Control de XAMPP

---

### Opción 2: Configurar en config.php (Recomendado para este Proyecto)

**Archivo:** `config/config.php`

Agregar al inicio del archivo (después de `<?php`):

```php
<?php
// Configurar zona horaria para Bolivia
date_default_timezone_set('America/La_Paz');

// Resto del código...
```

**Ventajas:**
- No requiere reiniciar Apache
- Específico para este proyecto
- Fácil de cambiar

---

## 🔍 Paso 3: Verificar Configuración Actual

### Script de Verificación

Crear archivo: `verificar_zona_horaria.php`

```php
<?php
echo "<h1>Verificación de Zona Horaria</h1>";
echo "<hr>";

// Zona horaria configurada en PHP
echo "<h2>Configuración de PHP</h2>";
echo "<p><strong>Zona Horaria Actual:</strong> " . date_default_timezone_get() . "</p>";

// Fecha y hora actual del servidor
echo "<h2>Fecha y Hora del Servidor</h2>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d') . "</p>";
echo "<p><strong>Hora:</strong> " . date('H:i:s') . "</p>";
echo "<p><strong>Fecha y Hora Completa:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Día de la Semana:</strong> " . date('l, d F Y') . "</p>";

// Timestamp
echo "<h2>Timestamp Unix</h2>";
echo "<p><strong>Timestamp:</strong> " . time() . "</p>";

// Información de zona horaria
echo "<h2>Información Detallada</h2>";
$timezone = new DateTimeZone(date_default_timezone_get());
$datetime = new DateTime('now', $timezone);
echo "<p><strong>Offset UTC:</strong> " . $datetime->format('P') . "</p>";
echo "<p><strong>Nombre de Zona:</strong> " . $timezone->getName() . "</p>";

// Comparación con otras zonas
echo "<h2>Comparación con Otras Zonas</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Zona Horaria</th><th>Fecha y Hora</th></tr>";

$zonas = [
    'America/La_Paz' => 'Bolivia',
    'America/Lima' => 'Perú',
    'America/Bogota' => 'Colombia',
    'America/Argentina/Buenos_Aires' => 'Argentina',
    'America/Santiago' => 'Chile',
    'America/Mexico_City' => 'México',
    'UTC' => 'UTC (Universal)'
];

foreach ($zonas as $zona => $nombre) {
    $tz = new DateTimeZone($zona);
    $dt = new DateTime('now', $tz);
    echo "<tr>";
    echo "<td>{$nombre} ({$zona})</td>";
    echo "<td>" . $dt->format('Y-m-d H:i:s P') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h2>🔧 Recomendación</h2>";
if (date_default_timezone_get() === 'America/La_Paz') {
    echo "<p style='color: green;'><strong>✅ Zona horaria configurada correctamente para Bolivia</strong></p>";
} else {
    echo "<p style='color: orange;'><strong>⚠️ Zona horaria actual: " . date_default_timezone_get() . "</strong></p>";
    echo "<p>Para Bolivia, se recomienda configurar: <strong>America/La_Paz</strong></p>";
}
?>
```

---

## 💻 Paso 4: Sincronizar Hora de tu PC

### Windows:

1. **Verificar Zona Horaria:**
   - Clic derecho en el reloj (barra de tareas)
   - "Ajustar fecha y hora"
   - Verificar que esté en la zona correcta

2. **Sincronizar con Internet:**
   - En "Ajustar fecha y hora"
   - Activar "Establecer la hora automáticamente"
   - Clic en "Sincronizar ahora"

3. **Configuración Manual:**
   ```
   Panel de Control → Reloj y región → Fecha y hora
   → Cambiar zona horaria → Seleccionar tu zona
   ```

### Linux:

```bash
# Ver zona horaria actual
timedatectl

# Listar zonas disponibles
timedatectl list-timezones | grep America

# Configurar zona horaria (Bolivia)
sudo timedatectl set-timezone America/La_Paz

# Sincronizar con servidor NTP
sudo timedatectl set-ntp true
```

### Mac:

```
Preferencias del Sistema → Fecha y Hora
→ Zona Horaria → Seleccionar tu ubicación
→ Activar "Ajustar fecha y hora automáticamente"
```

---

## 🔄 Paso 5: Aplicar Configuración al Proyecto

### Modificar config/config.php

Agregar al inicio del archivo:

```php
<?php
/**
 * Configuración del Sistema
 */

// ============================================
// CONFIGURACIÓN DE ZONA HORARIA
// ============================================
// Configurar zona horaria para Bolivia
// Cambiar según tu ubicación si es necesario
date_default_timezone_set('America/La_Paz');

// Resto de la configuración...
define('DB_HOST', 'localhost');
// ...
```

---

## 🧪 Paso 6: Probar la Configuración

### 1. Ejecutar script de verificación:
```
http://localhost/proyectos/MJBolivia2/verificar_zona_horaria.php
```

### 2. Verificar en el sistema:
```php
// En cualquier archivo PHP
echo date('Y-m-d H:i:s'); // Debe mostrar hora correcta
```

### 3. Verificar en base de datos:
```sql
-- En MySQL/MariaDB
SELECT NOW(); -- Debe mostrar hora correcta
```

---

## 📊 Comparación de Formatos de Fecha

```php
// Diferentes formatos de fecha en PHP
date('Y-m-d');           // 2026-01-10 (ISO 8601)
date('d/m/Y');           // 10/01/2026 (Formato Latino)
date('m/d/Y');           // 01/10/2026 (Formato USA)
date('Y-m-d H:i:s');     // 2026-01-10 23:30:45 (Con hora)
date('l, d F Y');        // Friday, 10 January 2026
date('d/m/Y H:i:s');     // 10/01/2026 23:30:45
```

---

## ⚠️ Problemas Comunes y Soluciones

### Problema 1: "It is not safe to rely on the system's timezone settings"

**Solución:**
```php
// Agregar al inicio de config.php
date_default_timezone_set('America/La_Paz');
```

### Problema 2: Fechas con diferencia de horas

**Causa:** Zona horaria no configurada o incorrecta

**Solución:**
1. Verificar zona horaria en PHP: `date_default_timezone_get()`
2. Configurar correctamente en `config.php`
3. Reiniciar Apache

### Problema 3: Base de datos con hora diferente

**Solución:**
```sql
-- Verificar zona horaria de MySQL
SELECT @@global.time_zone, @@session.time_zone;

-- Configurar zona horaria de MySQL (en my.ini o my.cnf)
[mysqld]
default-time-zone = '-04:00'  # Para Bolivia (UTC-4)
```

---

## 🎯 Configuración Recomendada para Bolivia

### 1. En config/config.php:
```php
<?php
date_default_timezone_set('America/La_Paz');
```

### 2. En php.ini:
```ini
date.timezone = America/La_Paz
```

### 3. En MySQL (my.ini):
```ini
[mysqld]
default-time-zone = '-04:00'
```

### 4. En tu PC:
```
Zona Horaria: (UTC-04:00) La Paz
Sincronización automática: Activada
```

---

## 📝 Lista de Verificación

- [ ] Zona horaria configurada en `config/config.php`
- [ ] Zona horaria configurada en `php.ini` (opcional)
- [ ] Apache reiniciado (si se modificó php.ini)
- [ ] Script de verificación ejecutado
- [ ] Hora del servidor coincide con hora de tu PC
- [ ] Fechas en el sistema se muestran correctamente
- [ ] Base de datos con zona horaria correcta

---

## 🔗 Referencias

- Lista completa de zonas horarias PHP: https://www.php.net/manual/es/timezones.php
- Documentación date_default_timezone_set: https://www.php.net/manual/es/function.date-default-timezone-set.php
- Formatos de fecha en PHP: https://www.php.net/manual/es/function.date.php

---

**Última actualización:** 10 de Enero de 2026  
**Zona horaria recomendada para Bolivia:** America/La_Paz (UTC-4)
