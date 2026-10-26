<?php

session_start();
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<?php require_once 'includes/header.php'; ?>

<div class="tarjeta" style="max-width: 600px; margin-top: 50px;">
    <h1 style="color: #0c2340;">Bienvenido a NexTec</h1>
    <p>La plataforma para la vinculación y financiamiento de proyectos de ciencia y tecnología en la Universidad Tecnológica de Tabasco.</p>
    
    <br>
    <a href="auth/login.php" class="btn-principal">Iniciar Sesión</a>
    <a href="auth/registro.php" class="btn-principal" style="background-color: #555;">Crear Cuenta</a>
    
    <div style="margin-top: 30px; font-size: 14px; color: #777;">
        <p>¿Eres estudiante o investigador? NexTec centraliza las patentes y proyectos académicos para conectarlos con el sector productivo.</p>
    </div>
</div>

</body>
</html>