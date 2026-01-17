<?php
require_once __DIR__ . '/../config/config.php';

// Verificar autenticación
Auth::requireLogin();

// Verificar que tenga rol de usuario
if (!Auth::checkRole(['usuario'])) {
    header('HTTP/1.0 403 Forbidden');
    die('Acceso denegado. Solo usuarios pueden acceder al manual.');
}

require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Configurar DomPDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);

// Contenido HTML del manual
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Manual de Usuario - Sistema MJB Bolivia</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #8B7EC8; border-bottom: 2px solid #8B7EC8; padding-bottom: 10px; }
        h2 { color: #6B5B95; margin-top: 30px; }
        h3 { color: #B8B3D8; }
        .section { margin-bottom: 20px; }
        .highlight { background-color: #f0f0f0; padding: 10px; border-left: 4px solid #8B7EC8; }
        ul { margin-left: 20px; }
        li { margin-bottom: 5px; }
        .code { background-color: #f8f8f8; padding: 5px; font-family: monospace; }
    </style>
</head>
<body>
    <h1>Manual de Usuario - Sistema de Gestión de Eventos MJB Bolivia</h1>

    <div class="section">
        <h2>1. Introducción</h2>
        <p>Este manual le guiará a través de todas las funciones disponibles en el sistema de gestión de eventos de MJB Bolivia. Como usuario, tendrá acceso a herramientas para gestionar inscripciones, visualizar reportes y acceder a información relevante de los eventos.</p>
    </div>

    <div class="section">
        <h2>2. Acceso al Sistema</h2>
        <p>Para acceder al sistema, utilice sus credenciales proporcionadas por el administrador. Una vez logueado, será redirigido a la página "Mis Eventos".</p>
    </div>

    <div class="section">
        <h2>3. Gestión de Inscripciones</h2>
        <h3>3.1 Crear Inscripciones</h3>
        <p>Desde "Mis Eventos", puede crear nuevas inscripciones para los eventos asignados:</p>
        <ul>
            <li>Haga clic en "Inscribir" en cualquier evento con inscripciones abiertas</li>
            <li>Complete el formulario con los datos del participante</li>
            <li>Seleccione opciones de alojamiento si están disponibles</li>
            <li>Revise los descuentos aplicables según edad o fecha</li>
            <li>Confirme el pago y genere el código de pago</li>
        </ul>

        <h3>3.2 Panel de Administración de Inscripciones</h3>
        <p>Acceda al panel completo de inscripciones desde "Inscripciones" en cada evento:</p>

        <h4>3.2.1 Vista General de Inscripciones</h4>
        <ul>
            <li><strong>Lista de Participantes:</strong> Visualice todos los inscritos con filtros por nombre, estado de pago, grupo asignado</li>
            <li><strong>Estados de Inscripción:</strong> Pendiente, Confirmada, Pagada, Cancelada</li>
            <li><strong>Búsqueda Avanzada:</strong> Filtre por fecha de inscripción, rango de edad, género, código de pago</li>
            <li><strong>Exportación de Datos:</strong> Exporte listas completas en formato Excel o PDF</li>
        </ul>

        <h4>3.2.2 Gestión Individual de Inscripciones</h4>
        <ul>
            <li><strong>Ver Detalles:</strong> Información completa del participante, documentos adjuntos, historial de pagos</li>
            <li><strong>Editar Información:</strong> Modifique datos personales, preferencias de alojamiento, información de contacto</li>
            <li><strong>Cambiar Estado:</strong> Actualice estado de pago, confirme asistencia, marque como becado</li>
            <li><strong>Historial de Cambios:</strong> Registro completo de todas las modificaciones realizadas</li>
        </ul>

        <h4>3.2.3 Procesos Masivos</h4>
        <ul>
            <li><strong>Importación de Datos:</strong> Cargue listas de participantes desde archivos Excel</li>
            <li><strong>Actualizaciones Masivas:</strong> Cambie estados de pago para múltiples inscripciones</li>
            <li><strong>Asignación de Grupos:</strong> Reasigne participantes entre grupos disponibles</li>
            <li><strong>Envío de Comunicaciones:</strong> Envíe emails masivos con códigos de pago o confirmaciones</li>
        </ul>

        <h3>3.3 Gestionar Pagos</h3>
        <p>El sistema permite gestionar pagos de inscripciones:</p>
        <ul>
            <li>Marque pagos como completados</li>
            <li>Genere códigos de pago únicos</li>
            <li>Aplique descuentos automáticos</li>
            <li>Visualice el estado de pagos por evento</li>
        </ul>

        <h4>3.3.1 Sistema de Códigos de Pago</h4>
        <ul>
            <li><strong>Generación Automática:</strong> Códigos únicos de 8-10 caracteres alfanuméricos</li>
            <li><strong>Validación de Pagos:</strong> Verificación en tiempo real del estado de pagos</li>
            <li><strong>Integración Bancaria:</strong> Conexión con sistemas de pago electrónico</li>
            <li><strong>Reportes de Cobranza:</strong> Seguimiento detallado de pagos pendientes y completados</li>
        </ul>

        <h4>3.3.2 Descuentos y Becas</h4>
        <ul>
            <li><strong>Descuentos por Edad:</strong> Rangos configurables (ej: menores de 18 años, adultos mayores)</li>
            <li><strong>Descuentos por Fecha:</strong> Descuentos tempranos, promociones especiales por fechas</li>
            <li><strong>Becas Especiales:</strong> Asignación manual de becas completas o parciales</li>
            <li><strong>Códigos Promocionales:</strong> Descuentos aplicables con códigos específicos</li>
        </ul>

        <h3>3.4 Eliminar Inscripciones</h3>
        <p>Solo el administrador puede eliminar inscripciones. Como usuario, puede marcar inscripciones como canceladas contactando al administrador.</p>
    </div>

    <div class="section">
        <h2>4. Formación de Grupos</h2>
        <h3>4.1 Sistema Automático de Grupos</h3>
        <p>El sistema facilita la formación automática de grupos con algoritmos inteligentes:</p>
        <ul>
            <li><strong>Asignación Automática:</strong> Los grupos se forman automáticamente al completar inscripciones</li>
            <li><strong>Criterios de Agrupación:</strong> Se asignan participantes según edad, género, ubicación geográfica</li>
            <li><strong>Balanceo Inteligente:</strong> Mantiene equilibrio en tamaño y composición de grupos</li>
            <li><strong>Ajustes Dinámicos:</strong> Los grupos se reestructuran automáticamente con nuevas inscripciones</li>
        </ul>

        <h3>4.2 Gestión Manual de Grupos</h3>
        <p>Como administrador, puede intervenir en la formación de grupos:</p>

        <h4>4.2.1 Creación de Grupos Personalizados</h4>
        <ul>
            <li><strong>Definir Parámetros:</strong> Establezca nombre, capacidad máxima, criterios específicos</li>
            <li><strong>Asignación Manual:</strong> Mueva participantes entre grupos según necesidades</li>
            <li><strong>Grupos Especiales:</strong> Cree grupos para becados, VIP, o categorías especiales</li>
            <li><strong>Restricciones:</strong> Configure reglas como "no mezclar géneros" o "mantener edades similares"</li>
        </ul>

        <h4>4.2.2 Reorganización de Grupos</h4>
        <ul>
            <li><strong>Rebalanceo:</strong> Redistribuya participantes para optimizar composición</li>
            <li><strong>Fusión de Grupos:</strong> Combine grupos pequeños en unidades más grandes</li>
            <li><strong>División de Grupos:</strong> Separe grupos grandes en subgrupos más manejables</li>
            <li><strong>Historial de Cambios:</strong> Registro completo de todas las reorganizaciones</li>
        </ul>

        <h3>4.3 Monitoreo y Reportes de Grupos</h3>
        <p>Visualice y analice la composición de grupos en tiempo real:</p>

        <h4>4.3.1 Dashboard de Grupos</h4>
        <ul>
            <li><strong>Visión General:</strong> Estado actual de todos los grupos del evento</li>
            <li><strong>Métricas por Grupo:</strong> Tamaño, distribución por edad/género, ocupación porcentual</li>
            <li><strong>Alertas:</strong> Notificaciones cuando grupos alcanzan capacidad máxima o necesitan reorganización</li>
            <li><strong>Comparativas:</strong> Análisis comparativo entre diferentes grupos</li>
        </ul>

        <h4>4.3.2 Reportes Detallados</h4>
        <ul>
            <li><strong>Listados por Grupo:</strong> Nombres completos, datos de contacto, información de pago</li>
            <li><strong>Estadísticas Demográficas:</strong> Distribuciones por edad, género, ubicación</li>
            <li><strong>Mapas de Asientos:</strong> Visualización gráfica de la distribución física</li>
            <li><strong>Exportación de Datos:</strong> Informes completos en PDF y Excel para coordinadores</li>
        </ul>

        <h3>4.4 Configuración Avanzada de Grupos</h3>
        <p>Personalice el comportamiento del sistema de grupos según sus necesidades:</p>
        <ul>
            <li><strong>Reglas de Prioridad:</strong> Defina qué criterios son más importantes (edad, género, ubicación)</li>
            <li><strong>Límites y Restricciones:</strong> Configure capacidades mínimas/máximas por grupo</li>
            <li><strong>Grupos Pre-asignados:</strong> Reserve espacios para participantes especiales</li>
            <li><strong>Grupos Temáticos:</strong> Cree agrupaciones por intereses, habilidades o categorías especiales</li>
        </ul>
    </div>

    <div class="section">
        <h2>5. Reportes y Estadísticas</h2>
        <h3>5.1 Panel de Reportes del Evento</h3>
        <p>Acceda al módulo completo de reportes desde "Reportes" en cada evento:</p>

        <h4>5.1.1 Pestaña General</h4>
        <ul>
            <li><strong>Resumen Ejecutivo:</strong> Visión general del estado del evento con KPIs principales</li>
            <li><strong>Información del Evento:</strong> Detalles completos, fechas, ubicación, capacidad</li>
            <li><strong>Estado de Inscripciones:</strong> Progreso vs objetivos, fechas límite</li>
            <li><strong>Métricas Financieras:</strong> Presupuesto, recaudación actual, proyecciones</li>
        </ul>

        <h4>5.1.2 Pestaña Inscripciones</h4>
        <ul>
            <li><strong>Lista Completa de Participantes:</strong> Datos personales, contacto, estado de pago</li>
            <li><strong>Filtros Avanzados:</strong> Por fecha, grupo, estado, rango de edad, género</li>
            <li><strong>Estadísticas de Inscripción:</strong> Tasa de conversión, tiempo promedio de registro</li>
            <li><strong>Historial de Cambios:</strong> Registro de todas las modificaciones realizadas</li>
            <li><strong>Exportación Personalizada:</strong> PDF, Excel con campos seleccionables</li>
        </ul>

        <h4>5.1.3 Pestaña Grupos</h4>
        <ul>
            <li><strong>Distribución por Grupos:</strong> Composición detallada de cada grupo formado</li>
            <li><strong>Estadísticas por Grupo:</strong> Tamaño, distribución demográfica, ocupación</li>
            <li><strong>Balance de Grupos:</strong> Comparativa entre grupos, alertas de desequilibrio</li>
            <li><strong>Reportes de Coordinadores:</strong> Información específica para líderes de grupo</li>
            <li><strong>Mapas Visuales:</strong> Representación gráfica de la distribución física</li>
        </ul>

        <h4>5.1.4 Pestaña Pagos</h4>
        <ul>
            <li><strong>Estado de Cobranza:</strong> Pagos completados, pendientes, parciales</li>
            <li><strong>Análisis Financiero:</strong> Recaudación por conceptos, descuentos aplicados</li>
            <li><strong>Reportes de Morosidad:</strong> Seguimiento de pagos atrasados</li>
            <li><strong>Proyecciones de Ingresos:</strong> Estimaciones basadas en inscripciones actuales</li>
            <li><strong>Integración Contable:</strong> Exportación de datos para sistemas contables</li>
        </ul>

        <h4>5.1.5 Pestaña Estadísticas</h4>
        <ul>
            <li><strong>Gráficos Interactivos:</strong> Evolución temporal, distribuciones demográficas</li>
            <li><strong>Métricas de Rendimiento:</strong> Tasa de ocupación, conversión de inscripciones</li>
            <li><strong>Análisis Comparativo:</strong> Comparación con eventos anteriores</li>
            <li><strong>Indicadores de Éxito:</strong> Cumplimiento de objetivos, satisfacción estimada</li>
            <li><strong>Exportación de Gráficos:</strong> Imágenes de alta resolución para presentaciones</li>
        </ul>

        <h3>5.2 Reportes Globales del Sistema</h3>
        <p>Reportes que abarcan múltiples eventos y períodos:</p>

        <h4>5.2.1 Dashboard Ejecutivo</h4>
        <ul>
            <li><strong>Visión General:</strong> Estado de todos los eventos activos</li>
            <li><strong>Métricas Consolidadas:</strong> Inscripciones totales, recaudación global</li>
            <li><strong>Tendencias:</strong> Comparativas por período, crecimiento proyectado</li>
            <li><strong>Alertas del Sistema:</strong> Eventos que requieren atención inmediata</li>
        </ul>

        <h4>5.2.2 Reportes Financieros</h4>
        <ul>
            <li><strong>Estados de Resultados:</strong> Ingresos, costos, márgenes por evento</li>
            <li><strong>Análisis de Rentabilidad:</strong> ROI por tipo de evento, segmento de participantes</li>
            <li><strong>Presupuestos vs Real:</strong> Comparativa de proyecciones vs resultados reales</li>
            <li><strong>Reportes Tributarios:</strong> Información para declaraciones fiscales</li>
        </ul>

        <h3>5.3 Herramientas de Análisis Avanzado</h3>
        <p>Funcionalidades para análisis profundo de datos:</p>
        <ul>
            <li><strong>Segmentación de Participantes:</strong> Análisis por edad, género, ubicación, frecuencia</li>
            <li><strong>Análisis de Tendencias:</strong> Patrones de inscripción, preferencias por temporada</li>
            <li><strong>Modelos Predictivos:</strong> Proyecciones de asistencia, estimaciones de recaudación</li>
            <li><strong>Business Intelligence:</strong> Dashboards personalizables con KPIs específicos</li>
        </ul>

        <h3>5.4 Exportación y Compartir Reportes</h3>
        <p>Opciones para compartir información con stakeholders:</p>
        <ul>
            <li><strong>Formatos Múltiples:</strong> PDF, Excel, CSV, imágenes de gráficos</li>
            <li><strong>Programación Automática:</strong> Envío periódico de reportes por email</li>
            <li><strong>Enlaces Públicos:</strong> Compartir reportes con permisos de solo lectura</li>
            <li><strong>Integración con Herramientas:</strong> Conexión con Google Sheets, Power BI, etc.</li>
        </ul>
    </div>

    <div class="section">
        <h2>6. Formulario Público</h2>
        <h3>6.1 Acceso al Formulario</h3>
        <p>Los participantes pueden inscribirse directamente a través del formulario público accesible desde el sitio web principal:</p>
        <ul>
            <li><strong>URL Pública:</strong> Enlace directo en la página principal del sitio web</li>
            <li><strong>Selección de Evento:</strong> Lista desplegable con eventos disponibles para inscripción</li>
            <li><strong>Información Previa:</strong> Detalles del evento, costos, fechas importantes</li>
            <li><strong>Disponibilidad:</strong> Solo visible cuando las inscripciones están abiertas</li>
        </ul>

        <h3>6.2 Proceso de Inscripción</h3>
        <p>El formulario guía al usuario a través de un proceso paso a paso:</p>

        <h4>6.2.1 Paso 1: Información Personal</h4>
        <ul>
            <li><strong>Datos Básicos:</strong> Nombre completo, fecha de nacimiento, género</li>
            <li><strong>Información de Contacto:</strong> Email, teléfono, dirección</li>
            <li><strong>Documentación:</strong> CI, expedido, fecha de emisión</li>
            <li><strong>Información Médica:</strong> Alergias, medicamentos, contacto de emergencia</li>
        </ul>

        <h4>6.2.2 Paso 2: Preferencias del Evento</h4>
        <ul>
            <li><strong>Opciones de Alojamiento:</strong> Selección de tipo de hospedaje disponible</li>
            <li><strong>Preferencias Alimentarias:</strong> Restricciones dietéticas, alergias alimentarias</li>
            <li><strong>Información Adicional:</strong> Comentarios especiales, peticiones específicas</li>
            <li><strong>Autorizaciones:</strong> Consentimiento para uso de imagen, políticas de privacidad</li>
        </ul>

        <h4>6.2.3 Paso 3: Revisión y Pago</h4>
        <ul>
            <li><strong>Resumen de Inscripción:</strong> Verificación de todos los datos ingresados</li>
            <li><strong>Cálculo de Costos:</strong> Total con descuentos aplicables automáticamente</li>
            <li><strong>Métodos de Pago:</strong> Transferencia bancaria, tarjetas de crédito, otros</li>
            <li><strong>Confirmación Final:</strong> Envío de comprobante por email</li>
        </ul>

        <h3>6.3 Características Técnicas</h3>
        <p>El formulario incluye múltiples validaciones y funcionalidades avanzadas:</p>

        <h4>6.3.1 Validación de Datos</h4>
        <ul>
            <li><strong>Validación en Tiempo Real:</strong> Verificación inmediata de campos obligatorios</li>
            <li><strong>Formato de Datos:</strong> Validación de emails, teléfonos, fechas</li>
            <li><strong>Verificación de Duplicados:</strong> Prevención de inscripciones múltiples</li>
            <li><strong>Integridad de Datos:</strong> Verificación de consistencia de información</li>
        </ul>

        <h4>6.3.2 Cálculos Automáticos</h4>
        <ul>
            <li><strong>Descuentos por Edad:</strong> Aplicación automática según rangos configurados</li>
            <li><strong>Descuentos por Fecha:</strong> Descuentos tempranos o promociones especiales</li>
            <li><strong>Códigos Promocionales:</strong> Validación y aplicación de descuentos</li>
            <li><strong>Recálculos Dinámicos:</strong> Actualización automática al cambiar opciones</li>
        </ul>

        <h4>6.3.3 Generación de Códigos</h4>
        <ul>
            <li><strong>Código de Inscripción:</strong> Identificador único para seguimiento</li>
            <li><strong>Código de Pago:</strong> Referencia para pagos bancarios</li>
            <li><strong>Códigos QR:</strong> Para verificación rápida en el evento</li>
            <li><strong>Comprobantes Digitales:</strong> PDF descargable con toda la información</li>
        </ul>

        <h3>6.4 Comunicación y Seguimiento</h3>
        <p>Sistema completo de comunicación con los participantes:</p>

        <h4>6.4.1 Confirmaciones Automáticas</h4>
        <ul>
            <li><strong>Email de Confirmación:</strong> Envío inmediato tras completar la inscripción</li>
            <li><strong>Detalles de Pago:</strong> Instrucciones completas para realizar el pago</li>
            <li><strong>Información del Evento:</strong> Itinerario, requisitos, información útil</li>
            <li><strong>Recordatorios:</strong> Notificaciones previas al evento</li>
        </ul>

        <h4>6.4.2 Estado de Inscripción</h4>
        <ul>
            <li><strong>Portal de Participante:</strong> Acceso personal para verificar estado</li>
            <li><strong>Actualizaciones en Tiempo Real:</strong> Cambios de estado automáticamente</li>
            <li><strong>Historial Completo:</strong> Registro de todas las comunicaciones</li>
            <li><strong>Soporte Técnico:</strong> Canales de contacto para consultas</li>
        </ul>

        <h3>6.5 Seguridad y Privacidad</h3>
        <p>El formulario implementa múltiples medidas de seguridad:</p>
        <ul>
            <li><strong>Encriptación SSL:</strong> Toda la comunicación encriptada</li>
            <li><strong>Protección CSRF:</strong> Prevención de ataques cross-site</li>
            <li><strong>Validación de Origen:</strong> Verificación de solicitudes legítimas</li>
            <li><strong>Compliance GDPR:</strong> Cumplimiento con regulaciones de privacidad</li>
            <li><strong>Auditoría Completa:</strong> Registro de todas las acciones realizadas</li>
        </ul>

        <h3>6.6 Integraciones Externas</h3>
        <p>Conexiones con sistemas externos para mayor funcionalidad:</p>
        <ul>
            <li><strong>Pasarelas de Pago:</strong> Integración con bancos y procesadores</li>
            <li><strong>Sistemas de Email:</strong> Envío masivo de confirmaciones</li>
            <li><strong>Redes Sociales:</strong> Compartir en Facebook, Twitter, etc.</li>
            <li><strong>Google Analytics:</strong> Seguimiento de conversiones y comportamiento</li>
            <li><strong>CRM Integration:</strong> Sincronización con sistemas de gestión de clientes</li>
        </ul>
    </div>

    <div class="section">
        <h2>7. Inscripción Manual desde Panel Principal</h2>
        <p>Como usuario autorizado, puede crear inscripciones directamente desde el panel:</p>
        <ul>
            <li>Acceso rápido desde "Mis Eventos"</li>
            <li>Formulario simplificado para inscripciones rápidas</li>
            <li>Asignación automática a grupos disponibles</li>
            <li>Registro de pagos en el momento</li>
            <li>Generación de comprobantes inmediatos</li>
        </ul>
    </div>

    <div class="section">
        <h2>8. Funciones Adicionales</h2>
        <h3>8.1 Galería de Eventos</h3>
        <p>Visualice imágenes y fotos de eventos anteriores:</p>
        <ul>
            <li>Galería organizada por eventos</li>
            <li>Imágenes de alta resolución</li>
            <li>Descripciones detalladas</li>
        </ul>

        <h3>8.2 Configuración Personal</h3>
        <ul>
            <li>Cambio de contraseña</li>
            <li>Actualización de datos de contacto</li>
            <li>Preferencias de visualización</li>
        </ul>
    </div>

    <div class="section">
        <h2>9. Solución de Problemas</h2>
        <div class="highlight">
            <strong>Problema:</strong> No puedo acceder a un evento<br>
            <strong>Solución:</strong> Verifique que el evento esté activo y que tenga permisos asignados por el administrador.
        </div>

        <div class="highlight">
            <strong>Problema:</strong> Error al guardar inscripción<br>
            <strong>Solución:</strong> Asegúrese de completar todos los campos requeridos y verificar la conexión a internet.
        </div>

        <div class="highlight">
            <strong>Problema:</strong> Reportes no se generan<br>
            <strong>Solución:</strong> Contacte al administrador del sistema para verificar permisos.
        </div>
    </div>

    <div class="section">
        <h2>10. Contacto y Soporte</h2>
        <p>Para soporte técnico o consultas adicionales, contacte al administrador del sistema o al equipo de soporte de MJB Bolivia.</p>
        <p><strong>Versión del Manual:</strong> 1.0<br>
        <strong>Fecha de Actualización:</strong> ' . date('d/m/Y') . '</p>
    </div>
</body>
</html>';

// Cargar HTML en DomPDF
$dompdf->loadHtml($html);

// Configurar tamaño de papel
$dompdf->setPaper('A4', 'portrait');

// Renderizar PDF
$dompdf->render();

// Enviar PDF al navegador
$dompdf->stream('manual_usuario_mjb_bolivia.pdf', array('Attachment' => true));
