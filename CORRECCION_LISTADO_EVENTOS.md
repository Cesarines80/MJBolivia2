# Corrección del Listado de Eventos - MJBolivia2

## Problema Identificado

Los eventos se creaban correctamente en la base de datos, pero NO aparecían en el panel de administración (`admin/eventos.php`).

## Causa Raíz

El sistema tiene **dos esquemas de autenticación mezclados**:

1. **Tabla `administradores`**: Para el panel administrativo del sitio institucional
   - Campos: `id`, `nombre`, `email`, `password`, `rol`, `estado`
   - Sesión: `$_SESSION['admin_id']`, `$_SESSION['is_admin']`

2. **Tabla `usuarios`**: Para el sistema de gestión de eventos
   - Campos: `id`, `username`, `email`, `nombre_completo`, `rol`, `activo`
   - Sesión: `$_SESSION['user_id']`

### Problemas Específicos:

1. **`getCurrentUser()` en `includes/auth.php`**:
   - Solo buscaba en la tabla `usuarios`
   - No consideraba la tabla `administradores`
   - Retornaba `null` para administradores

2. **`getAccessibleEvents()` en `includes/auth.php`**:
   - Dependía de `getCurrentUser()`
   - Como retornaba `null`, no mostraba eventos

3. **Referencias a `$_SESSION['user_id']` en `includes/eventos.php`**:
   - Usaba `$_SESSION['user_id']` que no existe para administradores
   - Debía usar `$_SESSION['admin_id']`

## Soluciones Aplicadas

### 1. Corrección de `getCurrentUser()` (includes/auth.php)

```php
public function getCurrentUser()
{
    if (!$this->isLoggedIn()) {
        return null;
    }

    // Si es administrador (tabla administradores)
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] && isset($_SESSION['admin_id'])) {
        $stmt = $this->db->prepare("
            SELECT id, nombre as nombre_completo, email, rol, estado as activo, fecha_creacion
            FROM administradores
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            // Normalizar el campo activo
            $admin['activo'] = ($admin['activo'] === 'activo') ? 1 : 0;
            return $admin;
        }
    }

    // Si es usuario del sistema de eventos (tabla usuarios)
    if (isset($_SESSION['user_id'])) {
        $stmt = $this->db->prepare("
            SELECT id, username, email, nombre_completo, rol, activo, fecha_creacion
            FROM usuarios
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    return null;
}
```

**Cambios**:
- ✅ Verifica primero si es administrador (`$_SESSION['is_admin']`)
- ✅ Busca en tabla `administradores` con `$_SESSION['admin_id']`
- ✅ Normaliza el campo `estado` → `activo`
- ✅ Mantiene compatibilidad con tabla `usuarios`

### 2. Corrección de `getAccessibleEvents()` (includes/auth.php)

```php
public function getAccessibleEvents()
{
    if (!$this->isLoggedIn()) {
        return [];
    }

    $user = $this->getCurrentUser();
    if (!$user) return [];

    // Si es administrador del sistema (tabla administradores)
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
        // Los administradores ven todos los eventos
        $stmt = $this->db->query("
            SELECT e.*, 
                   COALESCE(u.nombre_completo, a.nombre, 'Sistema') as creador_nombre,
                   (SELECT COUNT(*) FROM inscripciones_eventos ie WHERE ie.evento_id = e.id) as total_inscritos
            FROM eventos e
            LEFT JOIN usuarios u ON e.creado_por = u.id
            LEFT JOIN administradores a ON e.creado_por = a.id
            ORDER BY e.fecha_creacion DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Para usuarios del sistema de eventos...
    // (resto del código)
}
```

**Cambios**:
- ✅ Detecta si es administrador antes de consultar
- ✅ Hace JOIN con ambas tablas (`usuarios` y `administradores`)
- ✅ Usa `COALESCE` para obtener el nombre del creador de cualquier tabla
- ✅ Los administradores ven TODOS los eventos

### 3. Corrección de referencias a `$_SESSION['user_id']` (includes/eventos.php)

**Antes**:
```php
$_SESSION['user_id']
```

**Después**:
```php
$_SESSION['admin_id'] ?? $_SESSION['user_id']
```

**Archivos modificados**:
- `includes/eventos.php` (7 ocurrencias corregidas)

### 4. Corrección de nombre de columna (includes/eventos.php)

**Antes**:
```php
requiere_aprovacion  // ❌ Typo
```

**Después**:
```php
requiere_aprobacion  // ✅ Correcto
```

**Ocurrencias corregidas**: 6

## Archivos Modificados

1. ✅ `includes/auth.php`
   - Método `getCurrentUser()` - Líneas 236-276
   - Método `getAccessibleEvents()` - Líneas 349-371

2. ✅ `includes/eventos.php`
   - Todas las referencias a `$_SESSION['user_id']`
   - Todas las referencias a `requiere_aprovacion`

3. ✅ `admin/carrusel.php`
   - Bootstrap 5 syntax: `data-toggle` → `data-bs-toggle`
   - Bootstrap 5 syntax: `data-target` → `data-bs-target`

4. ✅ `admin/galeria.php`
   - Bootstrap 5 syntax: `data-toggle` → `data-bs-toggle`
   - Bootstrap 5 syntax: `data-target` → `data-bs-target`

## Verificación

### Prueba CLI (Exitosa ✅)

```bash
php test_eventos_listado.php
```

**Resultado**:
- ✅ getCurrentUser() retorna datos del administrador
- ✅ getAccessibleEvents() retorna 3 eventos
- ✅ EventosManager->getAll() retorna 3 eventos

### Próxima Prueba (Navegador)

**URL**: `http://localhost/proyectos/MJBolivia2/admin/eventos.php`

**Resultado Esperado**:
- ✅ Debe mostrar los 3 eventos creados
- ✅ Cada evento debe tener botones de Editar/Eliminar
- ✅ El botón "Crear Nuevo Evento" debe funcionar

## Notas Adicionales

### Sobre la Imagen del Evento

El usuario mencionó que los eventos necesitan una imagen para mostrarse en el `index.php` del sitio público.

**Campo en BD**: `eventos.imagen_portada`

**Actualmente**: Todos los eventos tienen `imagen_portada = ''` (vacío)

**Solución Pendiente**:
1. Agregar campo de imagen en el formulario de creación de eventos
2. Implementar subida de imagen en `admin/eventos.php`
3. Mostrar imagen en `index.php`

### Compatibilidad

El sistema ahora es compatible con:
- ✅ Administradores (tabla `administradores`)
- ✅ Usuarios del sistema de eventos (tabla `usuarios`)
- ✅ Ambos pueden coexistir sin conflictos

## Comandos de Verificación

```bash
# Verificar sintaxis
php -l includes/auth.php
php -l includes/eventos.php
php -l admin/carrusel.php
php -l admin/galeria.php

# Verificar eventos en BD
php -r "require 'config/config.php'; \$db = getDB(); \$stmt = \$db->query('SELECT id, nombre, estado FROM eventos'); \$eventos = \$stmt->fetchAll(); echo 'Total: ' . count(\$eventos) . PHP_EOL;"

# Probar listado completo
php test_eventos_listado.php
```

## Estado Final

✅ **CORRECCIÓN COMPLETADA**

- [x] getCurrentUser() funciona para administradores
- [x] getAccessibleEvents() retorna eventos
- [x] EventosManager->getAll() funciona correctamente
- [x] Bootstrap 5 syntax corregida en modales
- [x] Typos de columnas corregidos
- [x] Referencias a sesión corregidas

**Pendiente de prueba del usuario**:
- [ ] Verificar en navegador que los eventos aparezcan
- [ ] Probar creación de evento con imagen
- [ ] Verificar que los eventos se muestren en index.php

---

**Fecha**: 2026-01-10
**Versión**: 1.0
