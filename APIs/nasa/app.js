document.getElementById('btnBuscar').addEventListener('click', async () => {
    const fecha = document.getElementById('fecha').value;
    const resultadoDiv = document.getElementById('resultado');

    if (!fecha) {
        resultadoDiv.innerHTML = "<p style='color: red;'>Por favor, selecciona una fecha.</p>";
        return;
    }

    resultadoDiv.innerHTML = "<p>Viajando al espacio para traer tu imagen...</p>";

    try {
        // Hacemos la petición a NUESTRO backend (PHP), enviando la fecha
        const respuesta = await fetch(`api_nasa.php?fecha=${fecha}`);
        const datos = await respuesta.json();

        // Validamos si la NASA devolvió un error
        if (datos.code && datos.code !== 200) {
            resultadoDiv.innerHTML = `<p style='color: red;'>Error: ${datos.msg}</p>`;
            return;
        }

        // Construimos el HTML dinámico
        let contenidoMultimedia = '';
        
        // La NASA puede devolver imágenes o videos, debemos revisar el "media_type"
        if (datos.media_type === "video") {
            contenidoMultimedia = `<iframe width="560" height="315" src="${datos.url}" frameborder="0" allowfullscreen></iframe>`;
        } else {
            contenidoMultimedia = `<img src="${datos.url}" alt="${datos.title}">`;
        }

        // Inyectamos todo en el DOM
        resultadoDiv.innerHTML = `
            <h2>${datos.title}</h2>
            ${contenidoMultimedia}
            <p><strong>Créditos:</strong> ${datos.copyright || 'NASA / Dominio Público'}</p>
            <p class="explicacion">${datos.explanation}</p>
        `;

    } catch (error) {
        console.error("Error de conexión:", error);
        resultadoDiv.innerHTML = "<p style='color: red;'>Error al comunicarse con el servidor local.</p>";
    }
});