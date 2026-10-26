<?php
session_start();
require_once '../vendor/autoload.php'; 
require_once '../config/conexion.php';        

$clientID = '.';
$clientSecret = '.';
$redirectUri = 'http://localhost/nextec/auth/login_google.php';

$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);    
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);
    
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    
    $google_id = $google_account_info->id;
    $correo = $google_account_info->email;
    $nombre_completo = $google_account_info->name;
    
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE google_id = :google_id OR correo = :correo");
    $stmt->execute(['google_id' => $google_id, 'correo' => $correo]);
    $usuario_db = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario_db) {
        if (empty($usuario_db['google_id'])) {
            $update = $conexion->prepare("UPDATE usuarios SET google_id = :google_id WHERE id = :id");
            $update->execute(['google_id' => $google_id, 'id' => $usuario_db['id']]);
        }
        $_SESSION['usuario_id'] = $usuario_db['id'];
        $_SESSION['rol'] = $usuario_db['rol'];
        $_SESSION['nombre'] = $usuario_db['nombre_completo'];
        
        // Salimos de 'auth' para ir al panel principal
        header("Location: ../dashboard.php");
        exit();
        
    } else {
        $_SESSION['temp_google_id'] = $google_id;
        $_SESSION['temp_correo'] = $correo;
        $_SESSION['temp_nombre'] = $nombre_completo;
        
        header("Location: completar_registro.php");
        exit();
    }
} else {
    $url_login = $client->createAuthUrl();
    header('Location: ' . filter_var($url_login, FILTER_SANITIZE_URL));
    exit();
}
?>