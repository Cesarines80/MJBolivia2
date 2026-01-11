# 🔐 Usuario Super Administrador - Documentación

## ✅ Usuario Creado Exitosamente

Se ha creado un usuario con rol de **Super Administrador** que tiene permisos completos en el sistema, incluyendo la capacidad de **eliminar eventos**.

---

## 🔑 Credenciales de Acceso

```
Usuario:    superadmin
Contraseña: superadmin123
Email:      superadmin@sistema.com
Rol:        super_admin
```

---

## 👥 Usuarios con Rol Super Administrador

El sistema ahora tiene **2 usuarios** con rol Super Administrador:

| ID | Username | Email | Nombre | Estado |
|----|----------|-------|--------|--------|
| 1 | admin | admin@example.com | Administrador Principal | ✅ Activo |
| 5 | superadmin | superadmin@sistema.com | Super Administrador | ✅ Activo |

---

## 🎯 Permisos del Super Administrador

El usuario con rol `super_admin` tiene acceso completo al sistema:

### ✅ Gestión de Eventos:
- ✅ **Crear eventos**
- ✅ **Editar eventos**
- ✅ **Eliminar eventos** (permiso exclusivo)
- ✅ Ver todos los eventos del sistema
- ✅ Cambiar estado de eventos
- ✅ Asignar eventos a otros usuarios

### ✅ Gestión de Usuarios:
- ✅ Crear nuevos usuarios
- ✅ Editar usuarios existentes
- ✅ Cambiar roles de usuarios
- ✅ Activar/desactivar usuarios
- ✅ Ver todos los usuarios del sistema

### ✅ Gestión de Inscripciones:
- ✅ Ver todas las inscripciones
- ✅ Editar inscripciones
- ✅ Eliminar inscripciones
- ✅ Exportar reportes
- ✅ Gestionar pagos

### ✅ Configuración del Sistema:
- ✅ Acceso a todas las secciones
- ✅ Configuración general
- ✅ Gestión de contenido
- ✅ Acceso completo al panel de administración

---

## 🚀 Cómo Usar el Usuario Super Administrador

### Paso 1: Iniciar Sesión
1. Ir a: `http://localhost/proyectos/MJBolivia2/admin/login.php`
2. Ingresar credenciales:
   - **Usuario:** `superadmin`
   - **Contraseña:** `superadmin123`
3. Hacer clic en "Iniciar Sesión"

### Paso 2: Acceder a Eventos
1. Una vez dentro del panel de administración
2. Ir a la sección **"Eventos"** en el menú lateral
3. Verás todos los eventos del sistema

### Paso 3: Eliminar un Evento
1. En la lista de eventos, buscar el evento que deseas eliminar
2. Hacer clic en el botón **"Eliminar"** (ícono de papelera) 🗑️
3. Confirmar la eliminación en el diálogo que aparece
4. El evento será eliminado permanentemente

---

## ⚠️ Diferencias entre Roles

### Super Administrador (`super_admin`):
- ✅ Puede eliminar eventos
- ✅ Ve todos los eventos del sistema
- ✅ Gestiona todos los usuarios
- ✅ Acceso completo sin restricciones

### Administrador (`admin`):
- ❌ **NO puede eliminar eventos**
- ⚠️ Solo ve eventos asignados
- ⚠️ Permisos limitados de gestión
- ⚠️ Acceso restringido a ciertas secciones

### Usuario (`usuario`):
- ❌ Sin acceso al panel de administración
- ⚠️ Solo puede inscribirse en eventos
- ⚠️ Permisos muy limitados

---

## 🔒 Seguridad y Mejores Prácticas

### 1. Cambiar Contraseña Después del Primer Uso
Es **altamente recomendable** cambiar la contraseña después del primer inicio de sesión:

1. Iniciar sesión con `superadmin` / `superadmin123`
2. Ir a **Perfil** o **Configuración**
3. Cambiar la contraseña a una más segura
4. Usar una contraseña con:
   - Mínimo 8 caracteres
   - Letras mayúsculas y minúsculas
   - Números
   - Caracteres especiales

### 2. No Compartir Credenciales
- ❌ No compartir las credenciales de superadmin
- ❌ No usar la misma contraseña en otros sistemas
- ✅ Crear usuarios individuales para cada persona
- ✅ Asignar roles según necesidades

### 3. Uso Responsable
- ⚠️ La eliminación de eventos es **permanente**
- ⚠️ Eliminar un evento también elimina sus inscripciones
- ✅ Hacer respaldo antes de eliminar datos importantes
- ✅ Verificar dos veces antes de eliminar

---

## 🛠️ Solución de Problemas

### Problema: "Solo el Super Administrador puede eliminar eventos"

**Causa:** Estás usando un usuario con rol `admin` en lugar de `super_admin`

**Solución:**
1. Cerrar sesión del usuario actual
2. Iniciar sesión con: `superadmin` / `superadmin123`
3. Ahora podrás eliminar eventos

### Problema: No puedo iniciar sesión con superadmin

**Solución 1:** Verificar credenciales
- Usuario: `superadmin` (todo en minúsculas)
- Contraseña: `superadmin123` (todo en minúsculas)

**Solución 2:** Ejecutar script de verificación
```bash
php crear_superadmin.php
```
Este script verificará si el usuario existe y lo creará si es necesario.

### Problema: El usuario existe pero no tiene permisos

**Solución:** Ejecutar el script `crear_superadmin.php` nuevamente
- El script detectará que el usuario existe
- Actualizará automáticamente el rol a `super_admin`
- Activará el usuario si estaba desactivado

---

## 📊 Verificar Usuarios Super Admin

Para ver todos los usuarios con rol Super Administrador:

### Opción 1: Ejecutar script
```bash
php crear_superadmin.php
```

### Opción 2: Consulta SQL directa
```sql
SELECT id, username, email, nombre_completo, rol, activo 
FROM usuarios 
WHERE rol = 'super_admin';
```

---

## 🔄 Crear Más Super Administradores

Si necesitas crear más usuarios con rol Super Administrador:

### Método 1: Desde el Panel de Administración
1. Iniciar sesión como superadmin
2. Ir a **Usuarios** → **Crear Usuario**
3. Llenar el formulario
4. Seleccionar rol: **Super Administrador**
5. Guardar

### Método 2: Modificar el script
Editar `crear_superadmin.php` y cambiar:
```php
$username = 'superadmin2';  // Cambiar nombre
$password = 'password123';   // Cambiar contraseña
$email = 'superadmin2@sistema.com';  // Cambiar email
```

---

## 📝 Registro de Cambios

| Fecha | Acción | Usuario | Detalles |
|-------|--------|---------|----------|
| 11/01/2026 01:31 | Creado | superadmin | Usuario Super Administrador creado con ID 5 |
| 10/01/2026 18:33 | Existente | admin | Usuario admin ya existía con rol super_admin |

---

## 🎓 Resumen Rápido

**Para eliminar eventos:**
1. Ir a: `admin/login.php`
2. Usuario: `superadmin`
3. Contraseña: `superadmin123`
4. Ir a sección Eventos
5. Clic en botón Eliminar del evento deseado
6. Confirmar eliminación

**Importante:**
- ✅ Solo `super_admin` puede eliminar eventos
- ⚠️ La eliminación es permanente
- 🔒 Cambiar contraseña después del primer uso

---

**Fecha de creación:** 11 de Enero de 2026  
**Script utilizado:** `crear_superadmin.php`  
**Estado:** ✅ Usuario activo y funcional
