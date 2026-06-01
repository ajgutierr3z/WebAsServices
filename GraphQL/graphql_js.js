// 1. Definimos la URL del único endpoint de la API
const URL_API = "https://rickandmortyapi.com/graphql";

// 2. Guardamos nuestra consulta GraphQL en un String
const QUERY_PERSONAJES = `
  query {
    characters(page: 1) {
      results {
        id
        name
        status
        image
      }
    }
  }
`;

// 3. Creamos la función para hacer la petición
async function obtenerDatos() {
  try {
    const respuesta = await fetch(URL_API, {
      method: "POST", // GraphQL siempre usa POST para enviar la query en el cuerpo
      headers: {
        "Content-Type": "application/json",
      },
      // Pasamos la query dentro de un objeto JSON bajo la propiedad "query"
      body: JSON.stringify({ query: QUERY_PERSONAJES }),
    });

    const resultado = await respuesta.json();
    
    // Los datos siempre vienen dentro de la propiedad "data"
    const personajes = resultado.data.characters.results;
    console.log(personajes);
    
  } catch (error) {
    console.error("Hubo un error al traer los datos:", error);
  }
}

// Ejecutamos la función
obtenerDatos();