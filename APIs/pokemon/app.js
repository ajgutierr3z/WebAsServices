document.getElementById('btnCargar').addEventListener('click', cargarDatos);

async function cargarDatos() {
    const contenedor = document.getElementById('contenedorPersonajes');
    contenedor.innerHTML = 'Cargando...';

    try {
    
        const respuestaLista = await fetch('https://pokeapi.co/api/v2/pokemon?');
        const datosLista = await respuestaLista.json();
        
        contenedor.innerHTML = ''; //

        const promesas = datosLista.results.map(async (pokemon) => {
            const res = await fetch(pokemon.url);
            return res.json();
        });

        
        const pokemonesDetallados = await Promise.all(promesas);

        pokemonesDetallados.forEach(datosPokemon => {
            const tarjeta = document.createElement('div');
            tarjeta.className = 'tarjeta';
            
            tarjeta.innerHTML = `
                <img src="${datosPokemon.sprites.front_default}" alt="Sprite de ${datosPokemon.name}">
                <h3>${datosPokemon.name}</h3>
            `;
            
            contenedor.appendChild(tarjeta);
        });
            
    } catch (error) {
        console.error("Error en la petición:", error);
        contenedor.innerHTML = 'Hubo un error al cargar los datos.';
    }
}