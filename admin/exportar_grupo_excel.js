// Función para exportar grupo a Excel (formato CSV mejorado)
function exportarGrupoExcel(numGrupo, eventoId) {
    const grupo = document.getElementById(`grupo-${numGrupo}`);
    const tabla = grupo.querySelector('table');
    
    let csv = [];
    const filas = tabla.querySelectorAll('tr');
    
    filas.forEach(fila => {
        const cols = fila.querySelectorAll('td, th');
        const csvRow = [];
        cols.forEach(col => {
            // Limpiar el texto y escapar comillas
            let texto = col.innerText.trim();
            // Remover saltos de línea y espacios extra
            texto = texto.replace(/\s+/g, ' ');
            // Escapar comillas dobles
            texto = texto.replace(/"/g, '""');
            // Agregar comillas si contiene coma, salto de línea o comillas
            if (texto.includes(',') || texto.includes('\n') || texto.includes('"') || texto.includes(';')) {
                csvRow.push('"' + texto + '"');
            } else {
                csvRow.push(texto);
            }
        });
        csv.push(csvRow.join(';')); // Usar punto y coma como separador para Excel
    });
    
    const csvString = csv.join('\r\n'); // Usar CRLF para Windows/Excel
    const blob = new Blob(['\ufeff' + csvString], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `grupo_${numGrupo}_evento_${eventoId}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Función para imprimir un grupo específico
function imprimirGrupo(numGrupo) {
    // Ocultar todos los grupos excepto el seleccionado
    const grupos = document.querySelectorAll('[id^="grupo-"]');
    grupos.forEach(grupo => {
        if (grupo.id !== `grupo-${numGrupo}`) {
            grupo.style.display = 'none';
        }
    });
    
    // Imprimir
    window.print();
    
    // Restaurar visibilidad
    grupos.forEach(grupo => {
        grupo.style.display = 'block';
    });
}
