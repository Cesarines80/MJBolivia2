# 💰 Costos Personalizados por Evento

## ✅ Funcionalidad Implementada

Se ha agregado la capacidad de definir costos específicos de inscripción y alojamiento para cada evento individual.

---

## 📋 Cambios Realizados

### 1. Base de Datos

**Tabla `eventos` - Nuevos Campos:**
- `costo_inscripcion` - DECIMAL(10,2) - Costo base de inscripción al evento
- `costo_alojamiento` - DECIMAL(10,2) - Costo del alojamiento

**Script de Actualización:**
- `agregar_costos_eventos.php` - Agrega los campos a la tabla eventos

---

### 2. Formulario de Creación de Eventos

**Archivo:** `admin/eventos.php`

**Campos Agregados:**
```html
<div class="form-group">
    <label>Costo de Inscripción (Bs.) *</label>
    <input type="number" step="0.01" class="form-control" 
           name="costo_inscripcion" value="0.00" required>
    <small class="form-text text-muted">Costo base de inscripción al evento</small>
</div>

<div class="form-group">
    <label>Costo de Alojamiento (Bs.) *</label>
    <input type="number" step="0.01" class="form-control" 
           name="costo_alojamiento" value="0.00" required>
    <small class="form-text text-muted">Costo del alojamiento (opcional para el inscrito)</small>
</div>
```

---

### 3. Formulario de Edición de Eventos

**Los mismos campos fueron agregados al modal de edición:**
- Campo: Costo de Inscripción (Bs.)
- Campo: Costo de Alojamiento (Bs.)
- JavaScript actualizado para cargar valores existentes

---

### 4. Procesamiento de Datos

**Código PHP Actualizado:**

```php
// Al crear evento
case 'crear':
    $data = [
        // ... otros campos ...
        'costo_inscripcion' => floatval($_POST['costo_inscripcion']),
        'costo_alojamiento' => floatval($_POST['costo_alojamiento']),
        'estado' => 'activo'
    ];

// Al actualizar evento
case 'actualizar':
    $data = [
        // ... otros campos ...
        'costo_inscripcion' => floatval($_POST['costo_inscripcion']),
        'costo_alojamiento' => floatval($_POST['costo_alojamiento']),
        'estado' => $_POST['estado']
    ];
```

---

## 🎯 Cómo Funciona

### Al Crear un Evento:

1. **Acceder a Gestión de Eventos**
   - Ir a: `admin/eventos.php`
   - Clic en "Crear Nuevo Evento"

2. **Completar Formulario**
   - Nombre del evento
   - Descripción
   - Fechas (inicio, fin, inscripciones)
   - Lugar
   - **Costo de Inscripción (Bs.)** - Ejemplo: 150.00
   - **Costo de Alojamiento (Bs.)** - Ejemplo: 80.00

3. **Guardar Evento**
   - Los costos quedan asociados al evento específico
   - Cada evento puede tener costos diferentes

### Al Editar un Evento:

1. **Seleccionar Evento**
   - Clic en "Editar" en la tarjeta del evento

2. **Modificar Costos**
   - Los campos muestran los valores actuales
   - Se pueden actualizar según sea necesario

3. **Guardar Cambios**
   - Los nuevos costos se aplican inmediatamente

---

## 💡 Ventajas de esta Implementación

### 1. **Flexibilidad Total**
- Cada evento puede tener precios diferentes
- No hay un precio fijo global
- Adaptable a diferentes tipos de eventos

### 2. **Gestión Independiente**
- Los costos se definen al crear el evento
- Se pueden modificar en cualquier momento
- No afecta a otros eventos

### 3. **Claridad para Usuarios**
- Los inscritos ven el costo específico del evento
- No hay confusión con precios genéricos
- Transparencia total en los costos

### 4. **Control Administrativo**
- Super administradores definen los costos
- Fácil actualización de precios
- Historial de cambios en la base de datos

---

## 📊 Ejemplos de Uso

### Ejemplo 1: Retiro Espiritual
```
Evento: Retiro de Jóvenes 2026
Costo de Inscripción: Bs. 200.00
Costo de Alojamiento: Bs. 100.00
Total (con alojamiento): Bs. 300.00
```

### Ejemplo 2: Conferencia
```
Evento: Conferencia Anual
Costo de Inscripción: Bs. 150.00
Costo de Alojamiento: Bs. 80.00
Total (con alojamiento): Bs. 230.00
```

### Ejemplo 3: Evento Gratuito
```
Evento: Reunión Comunitaria
Costo de Inscripción: Bs. 0.00
Costo de Alojamiento: Bs. 0.00
Total: Bs. 0.00 (Gratuito)
```

---

## 🔄 Integración con Sistema de Inscripciones

### Próximos Pasos (Pendientes):

1. **Actualizar Formulario de Inscripción**
   - Leer costos desde la tabla `eventos`
   - Mostrar costos específicos del evento
   - Calcular total basado en costos del evento

2. **Actualizar Cálculos**
   - Usar `evento.costo_inscripcion` en lugar de valor fijo
   - Usar `evento.costo_alojamiento` en lugar de valor fijo

3. **Actualizar Reportes**
   - Mostrar costos específicos en reportes
   - Calcular recaudación basada en costos del evento

---

## 📝 Estructura de Base de Datos

```sql
ALTER TABLE eventos 
ADD COLUMN costo_inscripcion DECIMAL(10,2) DEFAULT 0.00 AFTER lugar,
ADD COLUMN costo_alojamiento DECIMAL(10,2) DEFAULT 0.00 AFTER costo_inscripcion;
```

**Campos:**
- `costo_inscripcion`: Costo base de inscripción (en Bolivianos)
- `costo_alojamiento`: Costo del alojamiento (en Bolivianos)
- Ambos campos permiten decimales (ej: 150.50)
- Valor por defecto: 0.00

---

## ✅ Estado de Implementación

- ✅ Campos agregados a la base de datos
- ✅ Formulario de creación actualizado
- ✅ Formulario de edición actualizado
- ✅ Procesamiento PHP implementado
- ✅ JavaScript actualizado
- ⏳ Integración con sistema de inscripciones (pendiente)
- ⏳ Actualización de reportes (pendiente)

---

## 🎯 Resultado

**Ahora cada evento puede tener:**
- Su propio costo de inscripción
- Su propio costo de alojamiento
- Precios independientes de otros eventos
- Flexibilidad total en la gestión de costos

**Moneda:** Todos los costos se manejan en **Bolivianos (Bs.)**

---

**Fecha de implementación**: 10 de Enero de 2026  
**Estado**: ✅ COMPLETADO (Formularios)  
**Pendiente**: Integración con inscripciones
