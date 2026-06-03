<?php
// api.php - El Backend Seguro


// 1. Simulamos el archivo .env o la configuración segura

$endpoint = ".";
$apiKey   = ".";

// 2. Definimos la query de GraphQL que queremos hacer
$graphQLQuery = [
    "query" => "query MiConsultaDeClase { hoja1 { nombre categoria telefono imagen } }"
];




// 3. Configuramos cURL para hacer la petición POST segura hacia BaseQL
$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($graphQLQuery));

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

// Configuramos las cabeceras incluyendo la API Key
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey // <- La llave viaja oculta desde el servidor
]);

// 4. Ejecutamos la petición y recibimos la respuesta
$response = curl_exec($ch);


if(curl_errno($ch)){
    $error_msg = curl_error($ch);
    header('Content-Type: application/json');
    // Devolvemos un JSON válido con el error para que JS no se rompa
    echo json_encode(["errors" => [["message" => "Error de cURL: " . $error_msg]]]);
    curl_close($ch);
    exit; // Detenemos la ejecución
}
curl_close($ch);
// 5. Le devolvemos el JSON limpio a nuestro JavaScript en el Frontend
header('Content-Type: application/json');
echo $response;
?>