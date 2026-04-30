document.addEventListener('DOMContentLoaded', () => {
    const fichas = document.querySelectorAll('.ficha-card');
    const tableHead = document.getElementById('tableHead');
    const tableBody = document.getElementById('tableBody');
    const tituloTabla = document.getElementById('titulo-tabla');
    const searchInput = document.getElementById('searchInput');
    const btnPdf = document.getElementById('btnExportPDF');
    const btnExcel = document.getElementById('btnExportExcel');

    // Contenedores de Paginación
    const paginationContainer = document.getElementById('paginationContainer');
    const paginationControls = document.getElementById('paginationControls');
    const pagStart = document.getElementById('pagStart');
    const pagEnd = document.getElementById('pagEnd');
    const pagTotal = document.getElementById('pagTotal');

    let rawData = []; // Datos crudos desde el servidor
    let filteredData = []; // Datos después de buscar
    let currentPage = 1;
    const itemsPerPage = 10;

    fichas.forEach(ficha => {
        ficha.addEventListener('click', async function () {
            // 1. Efecto visual de la ficha
            fichas.forEach(f => f.classList.remove('active'));
            this.classList.add('active');

            // 2. Cambiar Título y Placeholder de búsqueda
            const titulo = this.querySelector('h4').innerText;
            const subtitulo = this.querySelector('p').innerText;
            tituloTabla.innerHTML = `${titulo} <span style="color:#6b7280; font-size:14px; font-weight:normal;">- ${subtitulo}</span>`;

            // Texto dinámico para el buscador
            searchInput.placeholder = `Buscar en ${titulo.toLowerCase()}...`;

            // 3. Preparar estado de carga
            tableHead.innerHTML = '';
            tableBody.innerHTML = `
                <tr>
                    <td colspan="100%">
                        <div class="loading-message">
                            <i class='bx bx-loader-alt'></i>
                            <p>Cargando información desde la base de datos...</p>
                        </div>
                    </td>
                </tr>
            `;

            // Ocultar paginación y desactivar controles
            paginationContainer.style.display = 'none';
            searchInput.disabled = true;
            btnPdf.disabled = true;
            btnExcel.disabled = true;

            // 4. Petición AJAX (Fetch) a la nueva ruta
            const reporteId = this.getAttribute('data-id');
            try {
                const response = await fetch(`/Refaccionaria_Monterrey/CONTROLADOR/ReportesController.php?id=${reporteId}`);
                const json = await response.json();

                if (json.status === 'success') {
                    rawData = json.data;
                    filteredData = [...rawData]; // Inicialmente no hay filtro
                    currentPage = 1; // Reiniciar página

                    renderTable();

                    // Activar controles si hay datos
                    if (rawData.length > 0) {
                        searchInput.disabled = false;
                        searchInput.value = ''; // Limpiar búsqueda anterior
                        btnPdf.disabled = false;
                        btnExcel.disabled = false;
                    }
                } else {
                    mostrarError(json.message);
                }
            } catch (error) {
                mostrarError("Error de conexión con el servidor.");
                console.error(error);
            }
        });
    });

    // Función para renderizar la tabla con Paginación
    function renderTable() {
        tableHead.innerHTML = '';
        tableBody.innerHTML = '';

        if (filteredData.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="100%">
                        <div class="empty-message">
                            <i class='bx bx-info-circle' style="font-size:40px; color:#9ca3af;"></i>
                            <p>No se encontraron resultados para mostrar.</p>
                        </div>
                    </td>
                </tr>
            `;
            paginationContainer.style.display = 'none';
            return;
        }

        // Obtener las llaves para las cabeceras
        const llaves = Object.keys(filteredData[0]);

        // Crear Thead
        let trHead = document.createElement('tr');
        llaves.forEach(llave => {
            let th = document.createElement('th');
            th.textContent = llave;
            trHead.appendChild(th);
        });
        tableHead.appendChild(trHead);

        // Lógica de Paginación
        const totalItems = filteredData.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, totalItems);

        const pageData = filteredData.slice(startIndex, endIndex);

        // Crear Tbody con efecto cascada (animation delay)
        pageData.forEach((fila, index) => {
            let tr = document.createElement('tr');
            tr.className = 'fade-in-row';
            tr.style.animationDelay = `${index * 0.05}s`;

            llaves.forEach(llave => {
                let td = document.createElement('td');
                td.textContent = fila[llave] !== null ? fila[llave] : 'N/A';
                tr.appendChild(td);
            });
            tableBody.appendChild(tr);
        });

        // Actualizar textos de paginación
        pagStart.textContent = startIndex + 1;
        pagEnd.textContent = endIndex;
        pagTotal.textContent = totalItems;
        paginationContainer.style.display = 'flex';

        renderPaginationControls(totalPages);
    }

    // Dibujar botones de paginación
    function renderPaginationControls(totalPages) {
        paginationControls.innerHTML = '';

        if (totalPages <= 1) return;

        // Botón Anterior
        const btnPrev = document.createElement('button');
        btnPrev.className = 'page-btn';
        btnPrev.innerHTML = "<i class='bx bx-chevron-left'></i>";
        btnPrev.disabled = currentPage === 1;
        btnPrev.addEventListener('click', () => {
            currentPage--;
            renderTable();
        });
        paginationControls.appendChild(btnPrev);

        // Números
        for (let i = 1; i <= totalPages; i++) {
            // Mostrar solo algunas páginas si son muchas (lógica simplificada)
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                const btnPage = document.createElement('button');
                btnPage.className = `page-btn ${i === currentPage ? 'active' : ''}`;
                btnPage.textContent = i;
                btnPage.addEventListener('click', () => {
                    currentPage = i;
                    renderTable();
                });
                paginationControls.appendChild(btnPage);
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                const dots = document.createElement('span');
                dots.textContent = '...';
                dots.style.padding = '0 5px';
                dots.style.color = '#9ca3af';
                paginationControls.appendChild(dots);
            }
        }

        // Botón Siguiente
        const btnNext = document.createElement('button');
        btnNext.className = 'page-btn';
        btnNext.innerHTML = "<i class='bx bx-chevron-right'></i>";
        btnNext.disabled = currentPage === totalPages;
        btnNext.addEventListener('click', () => {
            currentPage++;
            renderTable();
        });
        paginationControls.appendChild(btnNext);
    }

    // Búsqueda en tiempo real
    searchInput.addEventListener('input', function () {
        const textoBusqueda = this.value.toLowerCase();

        if (textoBusqueda === '') {
            filteredData = [...rawData];
        } else {
            filteredData = rawData.filter(fila => {
                return Object.values(fila).some(valor =>
                    String(valor).toLowerCase().includes(textoBusqueda)
                );
            });
        }

        currentPage = 1; // Reiniciar a la primera página tras buscar
        renderTable();
    });

    // Mostrar error en tabla
    function mostrarError(mensaje) {
        tableHead.innerHTML = '';
        tableBody.innerHTML = `
            <tr>
                <td colspan="100%">
                    <div class="empty-message" style="color: #ef4444;">
                        <i class='bx bx-error-circle' style="font-size:40px;"></i>
                        <p>Error: ${mensaje}</p>
                    </div>
                </td>
            </tr>
        `;
        paginationContainer.style.display = 'none';
    }

    // Exportar a Excel (SheetJS)
    // Exporta TODOS los datos filtrados (no solo la página actual)
    btnExcel.addEventListener('click', () => {
        if (filteredData.length === 0) return;

        const titulo = tituloTabla.innerText.split('-')[0].trim();
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.json_to_sheet(filteredData);

        const colWidths = Object.keys(filteredData[0]).map(() => ({ wch: 20 }));
        ws['!cols'] = colWidths;

        XLSX.utils.book_append_sheet(wb, ws, "Reporte");
        XLSX.writeFile(wb, `${titulo}_Refaccionaria_MTY.xlsx`);
    });

    // Exportar a PDF (jsPDF + AutoTable)
    // Exporta TODOS los datos filtrados (no solo la página actual)
    btnPdf.addEventListener('click', () => {
        if (filteredData.length === 0) return;

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'pt', 'a4');

        const tituloCompleto = tituloTabla.innerText;
        const nombreReporte = tituloCompleto.split('-')[0].trim();

        doc.setFontSize(18);
        doc.text("Refaccionaria Monterrey", 40, 40);
        doc.setFontSize(14);
        doc.setTextColor(100);
        doc.text(`Reporte: ${tituloCompleto}`, 40, 60);

        const llaves = Object.keys(filteredData[0]);
        const columnas = llaves.map(llave => ({ header: llave, dataKey: llave }));

        doc.autoTable({
            columns: columnas,
            body: filteredData,
            startY: 80,
            theme: 'grid',
            styles: { fontSize: 9, cellPadding: 4 },
            headStyles: { fillColor: [59, 130, 246], textColor: [255, 255, 255], fontStyle: 'bold' },
            alternateRowStyles: { fillColor: [249, 250, 251] }
        });

        doc.save(`${nombreReporte}_Refaccionaria_MTY.pdf`);
    });
});
