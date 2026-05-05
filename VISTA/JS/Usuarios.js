// =============================================================
// USUARIOS.JS - Lógica del módulo de usuarios
// =============================================================

// ---- Buscador con debounce + restauración de foco ----
const searchInput = document.getElementById('searchInput');
let debounceTimer;

if (searchInput) {
    // Al escribir, esperar 500ms y luego enviar el formulario
    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            document.getElementById('formBusqueda').submit();
        }, 500);
    });

    // Restaurar el foco y mover el cursor al final del texto
    // (se ejecuta inmediatamente después de que la página carga con un valor en el buscador)
    if (searchInput.value.trim() !== '') {
        searchInput.focus();
        // Truco para mover el cursor al final: vaciar y restaurar el valor
        const valorActual = searchInput.value;
        searchInput.value = '';
        searchInput.value = valorActual;
    }
}

// ---- Auto-ocultar alertas flash después de 4 segundos ----
const alertMsg = document.getElementById('alertMsg');
if (alertMsg) {
    setTimeout(() => {
        alertMsg.style.transition = 'opacity 0.5s ease';
        alertMsg.style.opacity = '0';
        setTimeout(() => alertMsg.remove(), 500);
    }, 4000);
}

// ---- Mostrar/ocultar contraseña en el modal ----
const toggleModalPass = document.getElementById('toggleModalPass');
const inpPassword = document.getElementById('inp_password');

if (toggleModalPass && inpPassword) {
    toggleModalPass.addEventListener('click', function () {
        const tipo = inpPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        inpPassword.setAttribute('type', tipo);
        this.classList.toggle('bx-show');
        this.classList.toggle('bx-hide');
    });
}

// ---- Validación del formulario de Agregar Usuario (Client-side) ----
const formAgregar = document.getElementById('formAgregarUsuario');

if (formAgregar) {
    formAgregar.addEventListener('submit', function (e) {
        let valido = true;

        // Limpiar errores previos
        document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
        document.querySelectorAll('.form-group-modal input').forEach(el => el.classList.remove('invalid'));

        const nombre   = document.getElementById('inp_nombre');
        const apaterno = document.getElementById('inp_apaterno');
        const amaterno = document.getElementById('inp_amaterno');
        const correo   = document.getElementById('inp_correo');
        const password = document.getElementById('inp_password');

        if (nombre.value.trim() === '') {
            mostrarError(nombre, 'err_nombre', 'El nombre es obligatorio.');
            valido = false;
        } else if (nombre.value.length > 35) {
            mostrarError(nombre, 'err_nombre', 'Máximo 35 caracteres.');
            valido = false;
        }

        if (apaterno.value.trim() === '') {
            mostrarError(apaterno, 'err_apaterno', 'El apellido paterno es obligatorio.');
            valido = false;
        } else if (apaterno.value.length > 35) {
            mostrarError(apaterno, 'err_apaterno', 'Máximo 35 caracteres.');
            valido = false;
        }

        if (amaterno.value.length > 35) {
            mostrarError(amaterno, 'err_amaterno', 'Máximo 35 caracteres.');
            valido = false;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (correo.value.trim() === '') {
            mostrarError(correo, 'err_correo', 'El correo es obligatorio.');
            valido = false;
        } else if (!emailRegex.test(correo.value)) {
            mostrarError(correo, 'err_correo', 'Ingresa un correo válido.');
            valido = false;
        } else if (correo.value.length > 50) {
            mostrarError(correo, 'err_correo', 'Máximo 50 caracteres.');
            valido = false;
        }

        if (password.value === '') {
            mostrarError(password, 'err_password', 'La contraseña es obligatoria.');
            valido = false;
        } else if (password.value.length < 6) {
            mostrarError(password, 'err_password', 'La contraseña debe tener al menos 6 caracteres.');
            valido = false;
        }

        if (!valido) {
            e.preventDefault();
        }
    });
}

function mostrarError(inputEl, errorId, mensaje) {
    inputEl.classList.add('invalid');
    document.getElementById(errorId).textContent = mensaje;
}

// ---- Modal de Confirmar Cambio de Estado ----
function confirmarCambioEstado(idUsuario, nuevoEstado, nombre, esActivo) {
    const modal = document.getElementById('modalConfirmarEstado');
    const icono = document.getElementById('iconEstadoModal');
    const iconWrap = document.getElementById('iconConfirm');
    const titulo = document.getElementById('tituloConfirmar');
    const mensaje = document.getElementById('mensajeConfirmar');
    const btnConfirmar = document.getElementById('btnConfirmarEstado');

    if (esActivo) {
        // Desactivar
        icono.className = 'bx bx-user-minus';
        iconWrap.style.background = '#fef2f2';
        iconWrap.style.color = '#dc2626';
        titulo.textContent = 'Deshabilitar Acceso';
        mensaje.textContent = `¿Deseas deshabilitar el acceso de "${nombre}" al sistema?`;
        btnConfirmar.style.background = '#dc2626';
        btnConfirmar.style.boxShadow = '0 4px 12px rgba(220,38,38,0.25)';
    } else {
        // Activar
        icono.className = 'bx bx-user-plus';
        iconWrap.style.background = '#ecfdf5';
        iconWrap.style.color = '#059669';
        titulo.textContent = 'Habilitar Acceso';
        mensaje.textContent = `¿Deseas habilitar el acceso de "${nombre}" al sistema?`;
        btnConfirmar.style.background = '#3b82f6';
        btnConfirmar.style.boxShadow = '0 4px 12px rgba(59,130,246,0.25)';
    }

    const url = `/Refaccionaria_Monterrey/CONTROLADOR/UsuariosController.php?accion=cambiar_estado&id=${idUsuario}&estado=${nuevoEstado}`;
    btnConfirmar.href = url;

    modal.classList.add('show');
}

// Cerrar modales al hacer clic en el overlay (desactivado intencionalmente)
// Los modales solo se cierran con los botones "Cancelar" o "X"

// ============================================================
// MODAL DE EDITAR USUARIO (con carga AJAX)
// ============================================================

function abrirModalEditar(idUsuario) {
    const modal       = document.getElementById('modalEditarUsuario');
    const loadingDiv  = document.getElementById('editLoadingState');
    const form        = document.getElementById('formEditarUsuario');

    // Mostrar loading, ocultar form
    loadingDiv.style.display = 'block';
    form.style.display = 'none';
    modal.classList.add('show');

    // Petición AJAX para obtener datos del usuario
    const url = `/Refaccionaria_Monterrey/CONTROLADOR/UsuariosController.php?accion=get_usuario&id=${idUsuario}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.ok) {
                const u = data.usuario;
                document.getElementById('edit_id_usuario').value = u.id_usuario;
                document.getElementById('edit_nombre').value     = u.nombre    ?? '';
                document.getElementById('edit_apaterno').value  = u.apaterno  ?? '';
                document.getElementById('edit_amaterno').value  = u.amaterno  ?? '';
                document.getElementById('edit_correo').value    = u.correo    ?? '';
                document.getElementById('edit_password').value  = '';

                // Ocultar loading y mostrar form
                loadingDiv.style.display = 'none';
                form.style.display = 'block';
            } else {
                loadingDiv.innerHTML = `<i class='bx bx-error-circle' style="font-size:32px; color:#ef4444;"></i><p style="margin-top:10px; color:#ef4444;">${data.msg}</p>`;
            }
        })
        .catch(() => {
            loadingDiv.innerHTML = `<i class='bx bx-error-circle' style="font-size:32px; color:#ef4444;"></i><p style="margin-top:10px; color:#ef4444;">Error de conexión al servidor.</p>`;
        });
}

function cerrarModalEditar() {
    document.getElementById('modalEditarUsuario').classList.remove('show');
    limpiarFormEditar();
}

function limpiarFormEditar() {
    const form = document.getElementById('formEditarUsuario');
    if (form) form.reset();
    document.querySelectorAll('#formEditarUsuario .field-error').forEach(el => el.textContent = '');
    document.querySelectorAll('#formEditarUsuario input').forEach(el => el.classList.remove('invalid'));
    // Restaurar loading para la próxima apertura
    const loadingDiv = document.getElementById('editLoadingState');
    if (loadingDiv) {
        loadingDiv.style.display = 'block';
        loadingDiv.innerHTML = `<i class='bx bx-loader-alt' style="font-size:32px; animation: spin 1s linear infinite;"></i><p style="margin-top:10px; font-size:14px;">Cargando datos...</p>`;
    }
    const formEl = document.getElementById('formEditarUsuario');
    if (formEl) formEl.style.display = 'none';
}

// Toggle contraseña en modal de edición
const toggleEditPass = document.getElementById('toggleEditPass');
const editPassword   = document.getElementById('edit_password');

if (toggleEditPass && editPassword) {
    toggleEditPass.addEventListener('click', function () {
        const tipo = editPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        editPassword.setAttribute('type', tipo);
        this.classList.toggle('bx-show');
        this.classList.toggle('bx-hide');
    });
}

// Validación del formulario de Editar
const formEditar = document.getElementById('formEditarUsuario');

if (formEditar) {
    formEditar.addEventListener('submit', function (e) {
        let valido = true;
        document.querySelectorAll('#formEditarUsuario .field-error').forEach(el => el.textContent = '');
        document.querySelectorAll('#formEditarUsuario input').forEach(el => el.classList.remove('invalid'));

        const nombre   = document.getElementById('edit_nombre');
        const apaterno = document.getElementById('edit_apaterno');
        const amaterno = document.getElementById('edit_amaterno');
        const correo   = document.getElementById('edit_correo');
        const password = document.getElementById('edit_password');

        if (nombre.value.trim() === '') {
            mostrarError(nombre, 'edit_err_nombre', 'El nombre es obligatorio.'); valido = false;
        } else if (nombre.value.length > 35) {
            mostrarError(nombre, 'edit_err_nombre', 'Máximo 35 caracteres.');     valido = false;
        }

        if (apaterno.value.trim() === '') {
            mostrarError(apaterno, 'edit_err_apaterno', 'El apellido paterno es obligatorio.'); valido = false;
        } else if (apaterno.value.length > 35) {
            mostrarError(apaterno, 'edit_err_apaterno', 'Máximo 35 caracteres.');              valido = false;
        }

        if (amaterno.value.length > 35) {
            mostrarError(amaterno, 'edit_err_amaterno', 'Máximo 35 caracteres.'); valido = false;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (correo.value.trim() === '') {
            mostrarError(correo, 'edit_err_correo', 'El correo es obligatorio.');   valido = false;
        } else if (!emailRegex.test(correo.value)) {
            mostrarError(correo, 'edit_err_correo', 'Ingresa un correo válido.');   valido = false;
        } else if (correo.value.length > 50) {
            mostrarError(correo, 'edit_err_correo', 'Máximo 50 caracteres.');       valido = false;
        }

        if (password.value !== '' && password.value.length < 6) {
            mostrarError(password, 'edit_err_password', 'Si cambias la contraseña, mínimo 6 caracteres.'); valido = false;
        }

        if (!valido) e.preventDefault();
    });
}

