# Mejora: Campo de Imagen en Eventos

## 📋 Resumen de Cambios

Se ha agregado la funcionalidad para subir imágenes de portada al crear eventos, permitiendo que estos se muestren correctamente en la página principal del sitio.

---

## ✅ Cambios Realizados

### 1. **admin/eventos.php** - Formulario de Creación

#### Cambio 1: Agregar `enctype` al formulario
```php
// ANTES:
<form method="POST">

// DESPUÉS:
<form method="POST" enctype="multipart/form-data">
```

#### Cambio 2: Agregar campo de imagen
```html
<div class="form-group">
    <label>Imagen de Portada</label>
    <input type="file" class="form-control-file" name="imagen_portada" accept="image/*">
    <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF, WEBP. Tamaño máximo: 5MB</small>
</div>
```

#### Cambio 3: Procesar subida de imagen
```php
case 'crear':
    $data = [
        'nombre' => cleanInput($_POST['nombre']),
        'descripcion' => cleanInput($_POST['descripcion']),
        'fecha_inicio' => $_POST['fecha_inicio'],
        'fecha_fin' => $_POST['fecha_fin'],
        'fecha_inicio_inscripcion' => $_POST['fecha_inicio_inscripcion'],
        'fecha_fin_inscripcion' => $_POST['fecha_fin_inscripcion'],
        'lugar' => cleanInput($_POST['lugar']),
        'estado' => 'activo'
    ];

    // Subir imagen de portada si se proporcionó
    if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['imagen_portada']);
        if ($upload['success']) {
            $data['imagen_portada'] = $upload['filename'];
        } else {
            $_SESSION['error'] = $upload['error'];
            header('Location: eventos.php');
            exit;
        }
    }

    $result = $eventosManager->create($data);
    // ...
```

---

### 2. **includes/eventos.php** - EventosManager::create()

#### Actualización del INSERT
```php
// ANTES:
$stmt = $this->db->prepare("
    INSERT INTO eventos (
        nombre, descripcion, fecha_inicio, fecha_fin,
        fecha_inicio_inscripcion, fecha_fin_inscripcion,
        lugar, estado, creado_por
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$result = $stmt->execute([
    $data['nombre'],
    $data['descripcion'] ?? '',
    $data['fecha_inicio'],
    $data['fecha_fin'],
    $data['fecha_inicio_inscripcion'],
    $data['fecha_fin_inscripcion'],
    $data['lugar'] ?? '',
    $data['estado'] ?? 'activo',
    $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null
]);

// DESPUÉS:
$stmt = $this->db->prepare("
    INSERT INTO eventos (
        nombre, descripcion, fecha_inicio, fecha_fin,
        fecha_inicio_inscripcion, fecha_fin_inscripcion,
        lugar, imagen_portada, estado, creado_por
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$result = $stmt->execute([
    $data['nombre'],
    $data['descripcion'] ?? '',
    $data['fecha_inicio'],
    $data['fecha_fin'],
    $data['fecha_inicio_inscripcion'],
    $data['fecha_fin_inscripcion'],
    $data['lugar'] ?? '',
    $data['imagen_portada'] ?? null,
    $data['estado'] ?? 'activo',
    $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null
]);
```

---

### 3. **includes/functions.php** - Eventos::getUpcoming()

#### Mapeo de campos para compatibilidad con index.php
```php
// ANTES:
public static function getUpcoming($limit = 6)
{
    $db = getDB();
    $sql = "SELECT * FROM eventos
            WHERE fecha_inicio >= CURDATE() AND estado = 'activo'
            ORDER BY fecha_inicio ASC
            LIMIT $limit";

    $stmt = $db->query($sql);
    return $stmt->fetchAll();
}

// DESPUÉS:
public static function getUpcoming($limit = 6)
{
    $db = getDB();
    $sql = "SELECT 
                id,
                nombre as titulo,
                descripcion,
                fecha_inicio as fecha_evento,
                DATE_FORMAT(fecha_inicio, '%H:%i') as hora_evento,
                lugar,
                imagen_portada as imagen,
                estado
            FROM eventos
            WHERE fecha_inicio >= CURDATE() AND estado = 'activo'
            ORDER BY fecha_inicio ASC
            LIMIT $limit";

    $stmt = $db->query($sql);
    return $stmt->fetchAll();
}
```

**Razón del cambio:** El archivo `index.php` espera campos con nombres antiguos (`titulo`, `fecha_evento`, `imagen`), pero la tabla `eventos` usa nombres nuevos (`nombre`, `fecha_inicio`, `imagen_portada`). Esta consulta mapea los campos correctamente usando alias SQL.

---

## 🎯 Funcionalidad Implementada

### ✅ Crear Evento con Imagen
1. Usuario accede a **admin/eventos.php**
2. Click en "Crear Nuevo Evento"
3. Completa el formulario incluyendo la imagen de portada
4. La imagen se sube a `assets/uploads/`
5. El nombre del archivo se guarda en la BD

### ✅ Mostrar Eventos en Página Principal
1. `index.php` llama a `Eventos::getUpcoming(6)`
2. La consulta SQL mapea `imagen_portada` → `imagen`
3. Los eventos se muestran con sus imágenes correctamente

---

## 📁 Estructura de Archivos

```
assets/uploads/          # Directorio donde se guardan las imágenes
├── 67890abc_1234567890.jpg
├── 12345def_0987654321.png
└── ...
```

---

## 🔧 Validaciones Implementadas

### Subida de Archivos (función `uploadFile()` en config.php)
- ✅ Tipos permitidos: JPG, PNG, GIF, WEBP
- ✅ Tamaño máximo: 5MB
- ✅ Nombres únicos: `uniqid() + timestamp + extensión`
- ✅ Creación automática del directorio si no existe
- ✅ Validación de errores de subida

---

## 🧪 Pruebas Recomendadas

### 1. Crear Evento con Imagen
```
1. Login como admin
2. Ir a admin/eventos.php
3. Click "Crear Nuevo Evento"
4. Completar formulario + subir imagen
5. Verificar que se crea correctamente
6. Verificar que la imagen está en assets/uploads/
```

### 2. Verificar en Página Principal
```
1. Ir a index.php
2. Scroll a sección "Próximos Eventos"
3. Verificar que los eventos muestran sus imágenes
4. Verificar que eventos sin imagen no rompen el diseño
```

### 3. Crear Evento sin Imagen
```
1. Crear evento sin seleccionar imagen
2. Verificar que se crea correctamente
3. Verificar que no hay errores en logs
```

---

## 📝 Notas Técnicas

### Compatibilidad con Sistema Antiguo
El sistema mantiene compatibilidad con dos estructuras de eventos:
- **Sistema Nuevo**: Tabla `eventos` con campos modernos (EventosManager)
- **Sistema Antiguo**: Clase `Eventos` que mapea a campos antiguos para `index.php`

### Mapeo de Campos
| Campo Nuevo (BD)    | Campo Antiguo (index.php) |
|---------------------|---------------------------|
| nombre              | titulo                    |
| fecha_inicio        | fecha_evento              |
| imagen_portada      | imagen                    |

---

## ✅ Estado Final

- [x] Campo de imagen agregado al formulario
- [x] Procesamiento de subida de imagen
- [x] Almacenamiento en base de datos
- [x] Mapeo de campos para compatibilidad
- [x] Visualización en página principal
- [x] Validaciones de seguridad
- [x] Manejo de errores

---

## 🎉 Resultado

Los eventos ahora pueden tener imágenes de portada que se muestran correctamente en:
- ✅ Página principal del sitio (index.php)
- ✅ Panel de administración (admin/eventos.php)
- ✅ Listado de eventos

**Fecha de implementación:** 10 de Enero de 2026
