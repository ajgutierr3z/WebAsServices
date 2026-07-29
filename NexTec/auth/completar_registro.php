<?php
session_start();
require_once '../config/conexion.php';

if (!isset($_SESSION['temp_google_id'])) {
    header("Location: login.php");
    exit();
}

$nombre = $_SESSION['temp_nombre'];
$correo = $_SESSION['temp_correo'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rol = $_POST['rol']; 
    $google_id = $_SESSION['temp_google_id'];
    $nombre_empresa = isset($_POST['nombre_empresa']) ? trim($_POST['nombre_empresa']) : null;

    $insert = $conexion->prepare("INSERT INTO usuarios (nombre_completo, correo, google_id, mymlh_id, rol, nombre_empresa) VALUES (:nombre, :correo, :id_externo, :id_externo, :rol, :nombre_empresa)");
    
    $exito = $insert->execute([
        'nombre' => $nombre,
        'correo' => $correo,
        'id_externo' => $google_id, 
        'rol' => $rol,
        'nombre_empresa' => ($rol === 'empresa') ? $nombre_empresa : null
    ]);

    if ($exito) {
        unset($_SESSION['temp_google_id']);
        unset($_SESSION['temp_correo']);
        unset($_SESSION['temp_nombre']);

        $_SESSION['usuario_id'] = $conexion->lastInsertId();
        $_SESSION['rol'] = $rol;
        $_SESSION['nombre'] = $nombre;

        header("Location: ../dashboard.php");
        exit();
    }
}
?>

<?php require_once '../includes/header.php'; ?>

<div class="tarjeta" style="max-width: 450px; margin: 60px auto; text-align: center;">
    <h2 style="color: #0c2340;">¡Casi listos, <?php echo htmlspecialchars($nombre); ?>!</h2>
    <p style="font-size: 14px; color: #555; margin-bottom: 20px;">
        Para terminar de configurar tu cuenta con <b><?php echo htmlspecialchars($correo); ?></b>, por favor dinos cómo usarás NexTec.
    </p>

    <form action="completar_registro.php" method="POST">
        <select name="rol" id="selector_rol" required style="width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" onchange="mostrarCampoEmpresa()">
            <option value="" disabled selected>¿Cuál es tu perfil?</option>
            <option value="investigador">Profesor / Investigador</option>
            <option value="empresa">Empresa / Inversionista</option>
        </select>
        
        <div id="campo_empresa" style="display: none;">
            <input type="text" name="nombre_empresa" id="input_empresa" placeholder="Nombre de tu Empresa u Organización" style="width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>
        
        <button type="submit" class="btn-principal" style="width: 100%; margin-top: 15px; font-size: 16px;">Finalizar Registro</button>
    </form>
</div>

<script>
    function mostrarCampoEmpresa() {
        var rol = document.getElementById("selector_rol").value;
        var divEmpresa = document.getElementById("campo_empresa");
        var inputEmpresa = document.getElementById("input_empresa");

        if (rol === "empresa") {
            divEmpresa.style.display = "block";  
            inputEmpresa.required = true;        
        } else {
            divEmpresa.style.display = "none";   
            inputEmpresa.required = false;       
            inputEmpresa.value = "";             
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>