<?php
session_start();
require_once '../config/conexion.php'; 

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identificador = trim($_POST['identificador']); 
    $password = trim($_POST['password']);

    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE correo = :identificador OR nombre_completo = :identificador");
    $stmt->execute(['identificador' => $identificador]);
    $usuario_db = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario_db && password_verify($password, $usuario_db['contrasena'])) {
        $_SESSION['usuario_id'] = $usuario_db['id'];
        $_SESSION['rol'] = $usuario_db['rol'];
        $_SESSION['nombre'] = $usuario_db['nombre_completo'];
        
        header("Location: ../dashboard.php");
        exit();
    } else {
        $error = "Datos incorrectos. Verifica tu nombre/correo o tu contraseña.";
    }
}
?>

<?php require_once '../includes/header.php'; ?>

<div class="tarjeta" style="max-width: 400px; margin: 60px auto; text-align: center;">
    <h2 style="color: #0c2340;">Iniciar Sesión en NexTec</h2>
    
    <?php if($error): ?>
        <div style="color: red; font-size: 14px; margin-bottom: 15px; font-weight: bold; background-color: #ffe6e6; padding: 10px; border-radius: 4px;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <input type="text" name="identificador" placeholder="Correo electrónico o Nombre" required style="width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        <input type="password" name="password" placeholder="Contraseña" required style="width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        
        <button type="submit" class="btn-principal" style="width: 100%; margin-top: 15px; font-size: 16px;">Entrar a mi cuenta</button>
    </form>

    <div style="margin-top: 25px; margin-bottom: 20px; border-top: 1px solid #eee; padding-top: 20px;">
        <p style="font-size: 14px; color: #555; margin-bottom: 15px;">O ingresa de forma rápida con:</p>
        
        <a href="login_google.php" class="btn-principal" style="background-color: #4285F4; width: 100%; box-sizing: border-box; margin-bottom: 10px;">
            Continuar con Google
        </a>
        
        <a href="login_mymlh.php" class="btn-principal" style="background-color: #ef4255; width: 100%; box-sizing: border-box;">
            Continuar con MyMLH
        </a>
    </div>

    <a href="registro.php" style="font-size: 14px; color: #1d70b8; text-decoration: none; font-weight: bold;">¿Aún no tienes cuenta? Regístrate aquí</a>
</div>

<?php require_once '../includes/footer.php'; ?>