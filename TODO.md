# TODO: Hacer que los eventos se expandan como la galería

## Tareas Pendientes
- [x] Modificar las tarjetas de eventos para que sean clickeables (agregar cursor: pointer y atributos data-bs-toggle/modal)
- [x] Agregar modales para cada evento que muestren detalles completos
- [x] Probar la funcionalidad haciendo click en los eventos

## Archivos a editar
- index.php: Modificar sección de eventos y agregar modales

# TODO: Hacer que la galería se expanda como los eventos

## Tareas Pendientes
- [x] Crear tabla galeria_imagenes
- [x] Crear clase GaleriaImagenes
- [x] Modificar galeria-detalle.php para mostrar imagen principal y galería adicional
- [x] Crear admin/galeria-imagenes.php para gestionar imágenes adicionales
- [x] Agregar endpoint AJAX get_galeria_imagen
- [x] Cambiar enlaces de galería en index.php para ir a galeria-detalle.php

## Archivos creados/modificados
- create_galeria_imagenes_table.php
- includes/galeria_imagenes.php
- galeria-detalle.php
- admin/galeria-imagenes.php
- admin/ajax.php
- index.php (galería)
# TODO: Corregir edición en panel de administración

## Tareas Pendientes
- [x] Cambiar Bootstrap 5 por Bootstrap 4 en admin/carrusel.php y admin/galeria.php
- [x] Actualizar atributos data-bs-toggle por data-toggle
- [x] Actualizar scripts de Bootstrap 5 por Bootstrap 4
- [x] Probar que los botones de editar funcionen correctamente

## Archivos modificados
- admin/carrusel.php: Cambiado a Bootstrap 4
- admin/galeria.php: Cambiado a Bootstrap 4

# TODO: Corregir edición en panel de administración

## Tareas Pendientes
- [x] Cambiar Bootstrap 5 por Bootstrap 4 en admin/carrusel.php y admin/galeria.php
- [x] Actualizar atributos data-bs-toggle por data-toggle
- [x] Actualizar scripts de Bootstrap 5 por Bootstrap 4
- [x] Corregir case 'get_galeria_imagen' que estaba fuera del switch en ajax.php
- [x] Probar que los botones de editar funcionen correctamente

## Archivos modificados
- admin/carrusel.php: Cambiado a Bootstrap 4
- admin/galeria.php: Cambiado a Bootstrap 4
- admin/ajax.php: Corregido case 'get_galeria_imagen' dentro del switch

# TODO: Corregir edición en panel de administración

## Tareas Pendientes
- [x] Cambiar Bootstrap 5 por Bootstrap 4 en admin/carrusel.php y admin/galeria.php
- [x] Actualizar atributos data-bs-toggle por data-toggle
- [x] Actualizar scripts de Bootstrap 5 por Bootstrap 4
- [x] Corregir case 'get_galeria_imagen' que estaba fuera del switch en ajax.php
- [x] Agregar alertas de debug a las funciones editarItem y editarImagen
- [ ] Probar que los botones de editar funcionen correctamente

## Archivos modificados
- admin/carrusel.php: Cambiado a Bootstrap 4 + debug alerts
- admin/galeria.php: Cambiado a Bootstrap 4 + debug alerts
- admin/ajax.php: Corregido case 'get_galeria_imagen' dentro del switch

# TODO: Modificar creación de galerías para sistema de álbumes

## Tareas Pendientes
- [ ] Modificar admin/galeria.php para crear galerías con imagen de portada
- [ ] Actualizar admin/galeria-imagenes.php para gestionar imágenes de cada galería
- [ ] Modificar galeria-detalle.php para mostrar portada y todas las imágenes
- [ ] Actualizar index.php para mostrar galerías como álbumes
- [ ] Probar el nuevo sistema de galerías

## Cambios necesarios
- La tabla 'galeria' representará álbumes con imagen de portada
- La tabla 'galeria_imagenes' tendrá todas las imágenes de cada álbum
- El formulario de creación se enfocará en título, descripción e imagen de portada
- Habrá una interfaz separada para agregar múltiples imágenes a cada álbum


# TODO: Modificar creación de galerías para sistema de álbumes

## Tareas Pendientes
- [x] Modificar admin/galeria.php para crear galerías con imagen de portada
- [x] Actualizar admin/galeria-imagenes.php para gestionar imágenes de cada galería
- [x] Modificar galeria-detalle.php para mostrar portada y todas las imágenes
- [x] Actualizar index.php para mostrar galerías como álbumes
- [ ] Probar el nuevo sistema de galerías

## Cambios realizados
- La tabla 'galeria' representa álbumes con imagen de portada
- La tabla 'galeria_imagenes' contiene todas las imágenes de cada álbum
- El formulario de creación se enfocó en título, descripción e imagen de portada
- Se agregó botón 'Gestionar Imágenes' para cada álbum
- Se actualizó galeria-detalle.php para mostrar todas las imágenes del álbum
- Se cambió el título de la sección a 'Galería de Álbumes'


# TODO: Modificar creación de galerías para sistema de álbumes

## Tareas Pendientes
- [x] Modificar admin/galeria.php para crear galerías con imagen de portada
- [x] Actualizar admin/galeria-imagenes.php para gestionar imágenes de cada galería
- [x] Modificar galeria-detalle.php para mostrar portada y todas las imágenes
- [x] Actualizar index.php para mostrar galerías como álbumes
- [x] Probar el nuevo sistema de galerías

## Cambios realizados
- La tabla 'galeria' representa álbumes con imagen de portada
- La tabla 'galeria_imagenes' contiene todas las imágenes de cada álbum
- El formulario de creación se enfocó en título, descripción e imagen de portada
- Se agregó botón 'Gestionar Imágenes' para cada álbum
- Se actualizó galeria-detalle.php para mostrar todas las imágenes del álbum
- Se cambió el título de la sección a 'Galería de Álbumes'
- Se corrigieron problemas de Bootstrap (data-bs-toggle → data-toggle, Bootstrap 5 → 4)
- Se actualizaron parámetros de URL (id → galeria_id)


# TODO: Implementar zoom en imágenes de galería

## Tareas Pendientes
- [x] Agregar modal para mostrar imágenes a tamaño completo
- [x] Agregar event listeners para abrir modal al hacer click en imágenes
- [x] Permitir cerrar modal con botón X o click fuera de la imagen
- [x] Probar funcionalidad de zoom

## Cambios realizados
- Agregado modal Bootstrap con fondo transparente para mostrar imágenes
- JavaScript para detectar clicks en .gallery-item y abrir modal
- Imagen se muestra con object-fit: contain para mantener proporciones
- Modal se puede cerrar con botón X o click fuera de la imagen
- Modal responsive con tamaño máximo del 90% de la altura de la ventana


# TODO: Mostrar imágenes en secciones de Misión, Visión, Valores e Historia

## Tareas Pendientes
- [x] Agregar visualización de imágenes en sección 'Acerca de Nosotros' (Historia)
- [x] Agregar visualización de imágenes en sección 'Misión y Visión' (Misión, Visión, Valores)
- [x] Verificar que las imágenes se muestren correctamente en el frontend

## Cambios realizados
- Sección 'Acerca de Nosotros': Agregada imagen en el feature-box y como imagen principal
- Sección 'Misión y Visión': Agregadas imágenes para Misión, Visión y Valores
- Las imágenes se muestran con estilo responsive (max-height: 150px, object-fit: cover)
- Se mantiene compatibilidad con contenido sin imágenes (placeholders)
- Los títulos dinámicos ahora se muestran desde la base de datos

