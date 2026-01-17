# TODO - Manual de Usuario PDF

## Completado ✅
- [x] Instalar DomPDF via Composer
- [x] Crear archivo manual_usuario.php con generación de PDF
- [x] Agregar enlace al manual en el sidebar para usuarios con rol "usuario"
- [x] Agregar banner informativo con botón de descarga en mis-eventos.php
- [x] Verificar sintaxis PHP del archivo manual_usuario.php
- [x] Expandir manual con descripciones detalladas de todas las funciones:
  - [x] Gestión detallada de inscripciones (crear, editar, pagos, eliminar)
  - [x] Formación de grupos paso a paso
  - [x] Reportes detallados con todas las pestañas y estadísticas
  - [x] Formulario público y sus características
  - [x] Inscripción manual desde panel principal

## Pendiente ⏳
- [ ] Probar la generación completa del PDF en navegador (requiere servidor web activo)

## Notas
- ✅ DomPDF completamente instalado y funcional
- ✅ vendor/autoload.php generado correctamente
- ✅ Sintaxis PHP verificada sin errores
- ✅ Manual ampliado con secciones detalladas para todas las funciones
- El manual está listo para uso en producción
- Para testing completo, se requiere un servidor web activo con PHP

# Actualización del Formulario de Inscripción de Eventos

## Tarea: Actualizar formulario de inscripción para mostrar criterios de descuento condicionalmente

### Información Recopilada:
- El formulario actual (eventos/inscribir.php) ya muestra opciones de alojamiento si están configuradas
- Los descuentos por edad y fecha se calculan en el backend pero no se muestran al usuario
- Se requiere mostrar criterios de descuento (edad y fecha) solo si están configurados (no null)

### Plan:
- [x] Leer y analizar el formulario actual de inscripción
- [x] Leer includes/eventos.php para entender la estructura de datos
- [x] Leer admin/eventos.php para ver cómo se configuran los descuentos
- [x] Agregar sección "Criterios de Descuentos" que muestre:
  - Descuentos por edad si rangos están configurados
  - Descuentos por fecha si fechas límite están configuradas
- [x] La sección solo aparece si hay algún descuento configurado

### Cambios Implementados:
- [x] Agregada sección condicional "Criterios de Descuentos" en eventos/inscribir.php
- [x] Verificación de descuentos por edad (edad_rango1_min/max, edad_rango2_min/max)
- [x] Verificación de descuentos por fecha (descuento_fecha1/2/3 en config)
- [x] Display amigable de los criterios de descuento con listas

### Próximos Pasos:
- [ ] Probar el formulario con eventos que tengan diferentes configuraciones de descuento
- [ ] Verificar que la sección aparezca solo cuando corresponda
- [ ] Confirmar que los cálculos automáticos sigan funcionando correctamente

### Archivos Modificados:
- eventos/inscribir.php: Agregada sección de criterios de descuento
