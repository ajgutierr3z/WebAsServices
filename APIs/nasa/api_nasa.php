<?php
// 1. Cargar las credenciales de forma segura
require_once 'config.php';

// 2. Indicar que la respuesta será un JSON
header('Content-Type: application/json');

// 3. Capturar la fecha (si no envían una, usamos la fecha de hoy)
$fecha = $_GET['fecha'] ?? date('Y-m-d');

// 4. Armar la URL real de la NASA
// Documentación: https://api.nasa.gov/
$url = "https://api.nasa.gov/planetary/apod?api_key=" . NASA_API_KEY . "&date=" . $fecha;

// 5. Configurar y ejecutar cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// IMPORTANTE: Desactivar verificación SSL para evitar errores en XAMPP/Localhost
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$respuesta = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

// 6. Devolver la respuesta al Frontend
if ($error) {
    // Si cURL falla (ej. sin internet), armamos un JSON de error
    echo json_encode(["code" => 500, "msg" => "Error de cURL: " . $error]);
} else {
    // Si todo sale bien, imprimimos el JSON de la NASA directamente
    echo $respuesta;
}
?>