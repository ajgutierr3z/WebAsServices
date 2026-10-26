<?php
session_start();
require_once '../config/conexion.php';

$client_id = '.';
$client_secret = '.';
$redirect_uri = 'http://localhost/nextec/auth/login_mymlh.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    $token_url = "https://my.mlh.io/oauth/token";
    $post_data = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $code,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $token_data = json_decode($response, true);

    if (isset($token_data['access_token'])) {
        $access_token = $token_data['access_token'];

        $user_url = "https://api.mlh.com/v4/users/me";
        $ch2 = curl_init($user_url);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true); 
        curl_setopt($ch2, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $access_token,
            'Accept: application/json'
        ]);
        $user_response = curl_exec($ch2);
        if (curl_errno($ch2)) {
            session_destroy();
            die('Error de cURL: '. curl_error($ch2));
        }
        curl_close($ch2);

        $mlh_data = json_decode($user_response, true);

        /*if (isset($mlh_data['data']['id'])) {
            $mlh_user = $mlh_data['data'];
            
            $mymlh_id = $mlh_user['id'];
            $correo = $mlh_user['email'];
            $nombre_completo = trim($mlh_user['first_name'] . ' ' . $mlh_user['last_name']);

            $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE mymlh_id = :mymlh_id OR correo = :correo");
            $stmt->execute(['mymlh_id' => $mymlh_id, 'correo' => $correo]);
            $usuario_db = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario_db) {
                if (empty($usuario_db['mymlh_id'])) {
                    $update = $conexion->prepare("UPDATE usuarios SET mymlh_id = :mymlh_id WHERE id = :id");
                    $update->execute(['mymlh_id' => $mymlh_id, 'id' => $usuario_db['id']]);
                }
                
                $_SESSION['usuario_id'] = $usuario_db['id'];
                $_SESSION['rol'] = $usuario_db['rol'];
                $_SESSION['nombre'] = $usuario_db['nombre_completo'];
                
                header("Location: ../dashboard.php");
                exit();
                
            } else {
                $_SESSION['temp_google_id'] = $mymlh_id; 
                $_SESSION['temp_correo'] = $correo;
                $_SESSION['temp_nombre'] = $nombre_completo;
                
                header("Location: completar_registro.php");
                exit();
            }
        } */
       if (isset($mlh_data['first_name'])) {
            $mlh_user = $mlh_data;
            
            $mymlh_id = $mlh_user['id'];
            $correo = $mlh_user['email'];
            $nombre_completo = trim($mlh_user['first_name'] . ' ' . $mlh_user['last_name']);

            $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE mymlh_id = :mymlh_id OR correo = :correo");
            $stmt->execute(['mymlh_id' => $mymlh_id, 'correo' => $correo]);
            $usuario_db = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario_db) {
                if (empty($usuario_db['mymlh_id'])) {
                    $update = $conexion->prepare("UPDATE usuarios SET mymlh_id = :mymlh_id WHERE id = :id");
                    $update->execute(['mymlh_id' => $mymlh_id, 'id' => $usuario_db['id']]);
                }
                
                $_SESSION['usuario_id'] = $usuario_db['id'];
                $_SESSION['rol'] = $usuario_db['rol'];
                $_SESSION['nombre'] = $usuario_db['nombre_completo'];
                
                header("Location: ../dashboard.php");
                exit();
                
            } else {
                $_SESSION['temp_google_id'] = $mymlh_id; 
                $_SESSION['temp_correo'] = $correo;
                $_SESSION['temp_nombre'] = $nombre_completo;
                
                header("Location: completar_registro.php");
                exit();
            }
        } else {
            die("<h3 style='color:red;'>Error crítico: MyMLH no devolvió tus datos. Respuesta: " . htmlspecialchars($user_response) . "</h3>");
        }
    } else {
        die("<h3 style='color:red;'>Error crítico: No se pudo obtener el token de acceso.</h3>
             <p><b>Detalle del error de MyMLH:</b> " . htmlspecialchars($response) . "</p>");
    }
} else {
    $auth_url = "https://my.mlh.io/oauth/authorize?client_id=" . $client_id . "&redirect_uri=" . urlencode($redirect_uri) . "&response_type=code&scope=public";
    header("Location: " . $auth_url);
    exit();
}
?>