<?php
session_start();
require_once '../config/conexion.php'; 

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $password = trim($_POST['password']);
    $rol = $_POST['rol']; 
    $nombre_empresa = isset($_POST['nombre_empresa']) ? trim($_POST['nombre_empresa']) : null;

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido. Asegúrate de incluir el '@' y un dominio correcto.";
    } else {
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE correo = :correo");
        $stmt->execute(['correo' => $correo]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Ese correo ya está registrado. Por favor, inicia sesión.";
        } else {
            $password_encriptada = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conexion->prepare("INSERT INTO usuarios (nombre_completo, correo, contrasena, rol, nombre_empresa) VALUES (:nombre, :correo, :password, :rol, :nombre_empresa)");
            
            $exito = $insert->execute([
                'nombre' => $nombre, 
                'correo' => $correo, 
                'password' => $password_encriptada, 
                'rol' => $rol,
                'nombre_empresa' => ($rol === 'empresa') ? $nombre_empresa : null
            ]);

            if ($exito) {
                $_SESSION['usuario_id'] = $conexion->lastInsertId();
                $_SESSION['rol'] = $rol;
                $_SESSION['nombre'] = $nombre;
                
                header("Location: ../dashboard.php");
                exit();
            } else {
                $error = "Hubo un problema al crear tu cuenta. Intenta de nuevo.";
            }
        }
    }
}
?>

<?php require_once '../includes/header.php'; ?>

<div class="tarjeta" style="max-width: 450px; margin: 40px auto; text-align: center;">
    <h2 style="color: #0c2340;">Crear Cuenta en NexTec</h2>
    
    <?php if($error): ?>
        <div style="color: red; font-size: 14px; margin-bottom: 15px; font-weight: bold; background-color: #ffe6e6; padding: 10px; border-radius: 4px;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="registro.php" method="POST">
        <input type="text" name="nombre" placeholder="Nombre Completo" required style="width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        
        <input type="email" name="correo" placeholder="Correo Electrónico (ej. usuario@correo.com)" required style="width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        
        <input type="password" name="password" placeholder="Crea una contraseña" required style="width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        
        <select name="rol" id="selector_rol" required style="width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" onchange="mostrarCampoEmpresa()">
            <option value="" disabled selected>¿Cuál es tu perfil?</option>
            <option value="investigador">Profesor / Investigador / Estudiante</option>
            <option value="empresa">Empresa / Inversionista</option>
        </select>

        <div id="campo_empresa" style="display: none;">
            <input type="text" name="nombre_empresa" id="input_empresa" placeholder="Nombre de tu Empresa u Organización" style="width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>
        
        <button type="submit" class="btn-principal" style="width: 100%; margin-top: 15px; font-size: 16px;">Registrarme</button>
    </form>

    <div style="margin-top: 25px; border-top: 1px solid #eee; padding-top: 20px;">
        <p style="font-size: 14px; margin-bottom: 15px; color: #555;">O regístrate rápidamente con:</p>
        <a href="login_google.php" class="btn-principal" style="background-color: #db4437; width: 100%; box-sizing: border-box;">
            Registrarme con Google
        </a>
    </div>
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