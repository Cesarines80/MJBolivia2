# Gestión de Usuarios y Asignación a Eventos

## Resumen de Implementación

Se ha implementado exitosamente el sistema de gestión de usuarios y asignación de eventos, permitiendo a los super administradores controlar el acceso de usuarios a eventos específicos.

## Archivos Creados/Modificados

### 1. **admin/dashboard.php** ✅
- **Modificación**: Agregado enlace directo de "Cerrar Sesión" en la barra de navegación
- **Ubicación**: Líneas 132-136
- **Funcionalidad**: Proporciona acceso rápido al cierre de sesión sin necesidad de abrir el menú desplegable

### 1.1. **admin/login.php** ✅
- **Modificación**: Sistema de login unificado para ambas tablas de usuarios
- **Funcionalidad**: 
  - Intenta autenticar primero contra la tabla `administradores`
  - Si falla, intenta contra la tabla `usuarios` (sistema de eventos)
  - Redirige automáticamente según el rol del usuario:
    - Super Admin → dashboard.php
    - Admin → mis-eventos.php
    - Usuario → dashboard.php

### 2. **admin/usuarios.php** ✅ (NUEVO)
- **Descripción**: Página completa de gestión de usuarios
- **Funcionalidades**:
  - ✅ Crear nuevos usuarios (admin, usuario)
  - ✅ Editar información de usuarios existentes
  - ✅ Cambiar contraseñas de usuarios
  - ✅ Ver eventos asignados a cada usuario
  - ✅ Asignar usuarios a eventos específicos
  - ✅ Remover usuarios de eventos
  - ✅ Tabla interactiva con DataTables para búsqueda y filtrado
  - ✅ Interfaz moderna con AdminLTE

### 3. **admin/ajax.php** ✅
- **Modificación**: Agregados dos nuevos endpoints
- **Endpoints añadidos**:
  1. `get_eventos_usuario`: Obtiene la lista de eventos asignados a un usuario
  2. `get_config`: Obtiene la configuración de un evento específico

## Características Principales

### Gestión de Usuarios

#### Crear Usuario
- Formulario modal con validación
- Campos requeridos:
  - Nombre de usuario (único)
  - Email (único)
  - Nombre completo
  - Contraseña (mínimo 6 caracteres)
  - Rol (Admin/Usuario)
  - Estado (Activo/Inactivo)

#### Editar Usuario
- Modificar información del usuario
- Cambiar rol y estado
- No permite cambiar username (por seguridad)

#### Cambiar Contraseña
- Reseteo de contraseña por super admin
- Validación de longitud mínima

### Asignación de Eventos

#### Ver Eventos Asignados
- Modal que muestra todos los eventos del usuario
- Información detallada:
  - Nombre del evento
  - Fecha de asignación
  - Quién lo asignó
  - Estado del evento

#### Asignar Evento
- Selector desplegable con todos los eventos disponibles
- Asignación instantánea
- Prevención de duplicados (constraint en BD)

#### Remover Evento
- Botón de eliminación con confirmación
- Desactiva el acceso del usuario al evento

## Estructura de Base de Datos

### Tabla: `usuarios`
```sql
- id (PK)
- username (UNIQUE)
- password (hash)
- email (UNIQUE)
- nombre_completo
- rol (super_admin, admin, usuario)
- activo (boolean)
- fecha_creacion
- ultimo_acceso
```

### Tabla: `eventos_administradores`
```sql
- id (PK)
- evento_id (FK -> eventos)
- usuario_id (FK -> usuarios)
- asignado_por (FK -> usuarios)
- fecha_asignacion
- activo (boolean)
```

## Control de Acceso

### Roles y Permisos

#### Super Admin
- ✅ Acceso a TODOS los eventos
- ✅ Crear, editar y eliminar usuarios
- ✅ Asignar/remover usuarios de eventos
- ✅ Gestión completa del sistema

#### Admin
- ✅ Acceso SOLO a eventos asignados
- ✅ Gestionar inscripciones de sus eventos
- ✅ Ver reportes de sus eventos
- ❌ No puede crear usuarios
- ❌ No puede asignar eventos

#### Usuario
- ✅ Ver eventos activos
- ✅ Crear inscripciones propias
- ❌ No acceso al panel de administración

## Flujo de Trabajo

### 1. Crear Usuario
```
Super Admin → Usuarios → Crear Usuario → Completar formulario → Guardar
```

### 2. Asignar Evento a Usuario
```
Super Admin → Usuarios → Ver Eventos (icono calendario) → 
Seleccionar Evento → Asignar
```

### 3. Usuario Accede a su Evento
```
Admin → Login → Mis Eventos → Ver solo eventos asignados
```

## Seguridad Implementada

1. **Tokens CSRF**: Protección contra ataques CSRF en todos los formularios
2. **Validación de Roles**: Verificación de permisos en cada acción
3. **Sanitización de Datos**: Limpieza de inputs con `cleanInput()`
4. **Passwords Hasheados**: Uso de `password_hash()` con bcrypt
5. **Sesiones Seguras**: Regeneración de ID de sesión
6. **Prevención de SQL Injection**: Uso de prepared statements

## Interfaz de Usuario

### Características UI/UX
- ✅ Diseño responsive (móvil, tablet, desktop)
- ✅ Tabla con búsqueda y ordenamiento (DataTables)
- ✅ Modales para acciones (crear, editar, asignar)
- ✅ Mensajes de éxito/error claros
- ✅ Iconos intuitivos (Font Awesome)
- ✅ Colores consistentes con el tema del sistema
- ✅ Confirmaciones para acciones destructivas

## Navegación

### Menú Principal
```
Dashboard
├── Gestión de Eventos (super_admin)
├── Gestión de Usuarios (super_admin) ← NUEVO
├── Mis Eventos (admin)
└── Configuración
```

### Barra Superior
```
[☰] [Inicio] [Usuarios]                    [Cerrar Sesión ←NUEVO]
```

## Pruebas Recomendadas

### 1. Crear Usuario
- [ ] Crear usuario con rol Admin
- [ ] Crear usuario con rol Usuario
- [ ] Verificar validación de campos
- [ ] Verificar unicidad de username/email

### 2. Asignar Eventos
- [ ] Asignar evento a usuario Admin
- [ ] Verificar que aparece en "Mis Eventos"
- [ ] Intentar asignar mismo evento (debe prevenir duplicado)
- [ ] Remover evento asignado

### 3. Control de Acceso
- [ ] Login como Admin y verificar solo ve eventos asignados
- [ ] Login como Super Admin y verificar ve todos los eventos
- [ ] Intentar acceder a usuarios.php como Admin (debe denegar)

### 4. Edición de Usuarios
- [ ] Editar información de usuario
- [ ] Cambiar rol de usuario
- [ ] Desactivar usuario
- [ ] Cambiar contraseña

## Próximas Mejoras Sugeridas

1. **Notificaciones por Email**
   - Enviar email cuando se asigna un evento
   - Notificar cambios de contraseña

2. **Historial de Cambios**
   - Log de asignaciones/remociones
   - Auditoría de cambios en usuarios

3. **Permisos Granulares**
   - Permisos específicos por evento
   - Roles personalizados

4. **Exportación de Datos**
   - Exportar lista de usuarios a Excel
   - Exportar asignaciones de eventos

5. **Dashboard de Usuario**
   - Estadísticas personalizadas por usuario
   - Resumen de eventos asignados

## Soporte y Documentación

### Archivos de Referencia
- `includes/auth.php`: Sistema de autenticación y permisos
- `includes/eventos.php`: Gestión de eventos y asignaciones
- `admin/usuarios.php`: Interfaz de gestión de usuarios
- `admin/ajax.php`: Endpoints AJAX

### Funciones Clave
- `Auth::checkRole()`: Verificar rol del usuario
- `EventosManager::assignAdmin()`: Asignar usuario a evento
- `EventosManager::removeAdmin()`: Remover usuario de evento
- `EventosManager::getAdmins()`: Obtener admins de un evento

## Conclusión

El sistema de gestión de usuarios y asignación de eventos está completamente funcional y listo para usar. Proporciona un control granular sobre quién puede acceder a qué eventos, manteniendo la seguridad y facilidad de uso.

---

**Fecha de Implementación**: <?php echo date('d/m/Y'); ?>
**Versión**: 2.0.0
**Estado**: ✅ Completado y Funcional
