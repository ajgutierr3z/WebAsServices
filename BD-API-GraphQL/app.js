const lista = document.getElementById('listaTareas');
const input = document.getElementById('nuevaTarea');

// Función para pintar en el HTML con los botones simplificados y etiqueta de origen
function renderizarTareas(tareas, origen) {
    lista.innerHTML = '';
    
    // 1. Creamos una pequeña etiqueta visual (badge) dependiendo del origen
    const colorEtiqueta = origen === 'rest' ? '#4CAF50' : '#E10098'; 
    const textoEtiqueta = origen === 'rest' ? 'REST' : 'GraphQL';
    const etiquetaHTML = `<span style="background-color: ${colorEtiqueta}; color: white; font-size: 12px; padding: 3px 6px; border-radius: 10px; margin-left: 10px; font-weight: bold;">Vía ${textoEtiqueta}</span>`;

    tareas.forEach(t => {
        // 2. Botones dinámicos (se mantienen igual)
        const btnEliminar = origen === 'rest' 
            ? `<button onclick="eliminarTareaREST(${t.id})" style="margin-left: 10px; background: #ff4c4c; color: white; border: none; cursor: pointer; padding: 5px; border-radius: 3px;">Eliminar</button>`
            : `<button onclick="eliminarTareaGraphQL(${t.id})" style="margin-left: 10px; background: #ff4c4c; color: white; border: none; cursor: pointer; padding: 5px; border-radius: 3px;">Eliminar</button>`;
            
        const btnActualizar = origen === 'rest'
            ? `<button onclick="pedirActualizacionREST(${t.id}, '${t.titulo}')" style="margin-left: 10px; background: #ffc107; color: black; border: none; cursor: pointer; padding: 5px; border-radius: 3px;">Actualizar</button>`
            : `<button onclick="pedirActualizacionGraphQL(${t.id}, '${t.titulo}')" style="margin-left: 10px; background: #ffc107; color: black; border: none; cursor: pointer; padding: 5px; border-radius: 3px;">Actualizar</button>`;

        // 3. Pintamos la lista agregando la etiquetaHTML justo después del título
        lista.innerHTML += `<li style="display: flex; align-items: center;">
                                <span style="flex-grow: 1;">${t.id} - ${t.titulo} ${etiquetaHTML}</span>
                                ${btnActualizar} 
                                ${btnEliminar}
                            </li>`;
    });
}

// ==========================================
// INTERACCIONES RESTful
// ==========================================

function cargarTareasREST() {
    fetch('api.php')
        .then(res => res.json())
        .then(data => renderizarTareas(data, 'rest'));
}

function crearTareaREST() {
    const titulo = input.value;
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ titulo: titulo })
    }).then(() => {
        input.value = '';
        cargarTareasREST();
    });
}

function eliminarTareaREST(id) {
    fetch(`api.php?id=${id}`, { method: 'DELETE' })
        .then(() => cargarTareasREST());
}

function pedirActualizacionREST(id, tituloActual) {
    const nuevoTitulo = prompt("Edita la tarea:", tituloActual);
    if (nuevoTitulo && nuevoTitulo.trim() !== "") {
        fetch('api.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, titulo: nuevoTitulo })
        }).then(() => cargarTareasREST());
    }
}

// ==========================================
// INTERACCIONES GRAPHQL
// ==========================================

function cargarTareasGraphQL() {
    const query = `
        query {
            tareas { id titulo }
        }
    `;
    fetch('graphql.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ query: query })
    })
    .then(res => res.json())
    .then(response => {
        renderizarTareas(response.data.tareas, 'graphql');
    });
}

function crearTareaGraphQL() {
    const titulo = input.value;
    const mutation = `
        mutation($tituloStr: String!) {
            crearTarea(titulo: $tituloStr)
        }
    `;
    fetch('graphql.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ query: mutation, variables: { tituloStr: titulo } })
    }).then(() => {
        input.value = '';
        cargarTareasGraphQL();
    });
}

function eliminarTareaGraphQL(id) {
    const mutation = `
        mutation($idVal: Int!) {
            eliminarTarea(id: $idVal)
        }
    `;
    fetch('graphql.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ query: mutation, variables: { idVal: id } })
    }).then(() => cargarTareasGraphQL());
}

function pedirActualizacionGraphQL(id, tituloActual) {
    const nuevoTitulo = prompt("Edita la tarea:", tituloActual);
    if (nuevoTitulo && nuevoTitulo.trim() !== "") {
        const mutation = `
            mutation($idVal: Int!, $tituloStr: String!) {
                actualizarTarea(id: $idVal, titulo: $tituloStr)
            }
        `;
        fetch('graphql.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query: mutation, variables: { idVal: id, tituloStr: nuevoTitulo } })
        }).then(() => cargarTareasGraphQL());
    }
}