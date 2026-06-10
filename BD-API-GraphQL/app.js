// app.js
const lista = document.getElementById('listaTareas');
const input = document.getElementById('nuevaTarea');

// Función auxiliar para pintar en el HTML
function renderizarTareas(tareas) {
    lista.innerHTML = '';
    tareas.forEach(t => {
        lista.innerHTML += `<li>${t.id} - ${t.titulo}
        <div>
        <button class='rest-btn' onclick='eliminarTareaREST(${t.id})'>EliminarREST</button>
        <button class='rest-btn' onclick='actualizarTareaREST(${t.id})'>EditarREST</button>
        <button class='gql-btn' onclick='eliminarTareaGraphQL(${t.id})'>EliminarGQL</button>
        <button class='gql-btn' onclick='actualizarTareaGraphQL(${t.id})'>EditarGQL</button></div></li>`;
    });
}

// ==========================================
// INTERACCIONES RESTful
// ==========================================

function cargarTareasREST() {
    // REST: Se hace un simple GET a la URL del recurso
    fetch('api.php')
        .then(res => res.json())
        .then(data => renderizarTareas(data));
}

function crearTareaREST() {
    const titulo = input.value;
    // REST: Se hace un POST enviando un objeto JSON plano
    fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ titulo: titulo })
    }).then(() => {
        input.value = '';
        cargarTareasREST();
    });
}
//MI CODIGO -I-
function actualizarTareaREST(id) {
    const nuevoTitulo = prompt("Ingrese el nuevo titulo");    

    if (!nuevoTitulo) return; 

    fetch('api.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, titulo: nuevoTitulo })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la actualización');
        }
        return response.json();
    })
    .then(data => {        
        console.log(data.mensaje);
        cargarTareasREST();
    })
    .catch(error => console.error('Error:', error));
}

function eliminarTareaREST(id) {
    
    let eliminar = confirm("¿Esta seguro que quiere eliminar esta tarea?");

    if (eliminar) {
        fetch('api.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    }).then(() => {        
        cargarTareasREST();
    });    
    }
    
}
//MI CODIGO -F-
// ==========================================
// INTERACCIONES GRAPHQL
// ==========================================

function cargarTareasGraphQL() {
    // GraphQL: se manda un POST con un string especificando qué campos queremos
    const query = `
        query {
            tareas {
                id
                titulo
            }
        }
    `;
    fetch('graphql.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ query: query })
    })
    .then(res => res.json())
    .then(response => {
        // GraphQL siempre devuelve los datos dentro de un objeto "data"
        renderizarTareas(response.data.tareas);
    });
}

function crearTareaGraphQL() {
    const titulo = input.value;
    // GraphQL: Mandamos un POST con un string tipo "mutation" y pasamos variables
    const mutation = `
        mutation($tituloStr: String!) {
            crearTarea(titulo: $tituloStr)
        }
    `;
    fetch('graphql.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            query: mutation,
            variables: { tituloStr: titulo }
        })
    }).then(() => {
        input.value = '';
        cargarTareasGraphQL();
    });
}

function actualizarTareaGraphQL(id) {
    const nuevoTitulo = prompt("Ingrese el nuevo titulo");    
    if (!nuevoTitulo) return;

    const mutation = `
        mutation($id: Int!, $titulo: String!) {
            actualizarTarea(id: $id , titulo: $titulo)
        }
    `;
    fetch('graphql.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            query: mutation,
            variables: { titulo: nuevoTitulo, id: parseInt(id) }
        })
    }).then(res => res.json())
    .then(res => {
        if (res.errors) {
            console.error("Error GraphQL:", res.errors);
        } else {
            console.log(res.data.actualizarTarea);
            cargarTareasGraphQL(); 
        }
    })
    .catch(err => console.error(err));        
}

function eliminarTareaGraphQL(id) {
    
    let eliminar = confirm("¿Esta seguro que quiere eliminar esta tarea?");


    const mutation = `
        mutation($id: Int!) {
            eliminarTarea(id: $id)
        }
    `;

    if (eliminar) {
        fetch('graphql.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            query: mutation,
            variables: {id: parseInt(id) }
        })
        
    }).then(() => {        
        cargarTareasGraphQL();
    });    
    }
    
}