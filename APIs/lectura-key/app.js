document.getElementById('btnBuscar').addEventListener('click', async () => {
    const ciudadInput = document.getElementById('ciudad').value;
    const resultadoDiv = document.getElementById('resultado');

    if (!ciudadInput) {
        resultadoDiv.innerText = "Por favor, escribe una ciudad.";
        return;
    }

    resultadoDiv.innerText = "Consultando...";

    try {
        // Apuntamos a NUESTRO backend en PHP
        const respuesta = await fetch(`api_clima.php?ciudad=${ciudadInput}`);
        const datos = await respuesta.json();

        // Validamos si la API de OpenWeather devolvió error
        if (datos.cod != 200) {
            resultadoDiv.innerText = `Error: ${datos.message}`;
            return;
        }

        
        const ciudad = datos.name;
        const pais = datos.sys.country;                     
        const temperatura = datos.main.temp;              
        const sensacion = datos.main.feels_like;           
        const tempMin = datos.main.temp_min;              
        const tempMax = datos.main.temp_max;               
        const humedad = datos.main.humidity;                
        const viento = datos.wind.speed;                    
        const descripcion = datos.weather[0].description;   

       
        resultadoDiv.innerHTML = `
            <h3 style="margin-bottom: 10px;">Clima en ${ciudad}, ${pais}</h3>
            <ul style="list-style-type: none; padding: 0; line-height: 1.8;">
                <li><strong>Condición:</strong> <span style="text-transform: capitalize;">${descripcion}</span></li>
                <li><strong>Temperatura:</strong> ${temperatura}°C (Se siente de ${sensacion}°C)</li>
                <li><strong>Mínima / Máxima:</strong> ${tempMin}°C / ${tempMax}°C</li>
                <li><strong>Humedad:</strong> ${humedad}%</li>
                <li><strong>Viento:</strong> ${viento} m/s</li>
            </ul>
        `;

    } catch (error) {
        console.error("Error:", error);
        resultadoDiv.innerText = 'Error al comunicarse con el servidor local.';
    }
});