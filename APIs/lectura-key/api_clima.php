<?php
// Requerimos las constantes desde la raíz (subiendo un nivel con ../)
require_once '../config.php';

// Le decimos al navegador que devolveremos JSON
header('Content-Type: application/json');

$ciudad = $_GET['ciudad'] ?? 'London';

// Armamos la URL (units=metric es para que devuelva Celsius)
$url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($ciudad) . "&appid=" . OPENWEATHER_API_KEY . "&units=metric";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Desactivar verificación SSL para evitar errores en localhost (XAMPP)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$respuesta = curl_exec($ch);


// Imprimimos el JSON tal cual nos lo entregó OpenWeather
echo $respuesta;
?>