<?php
session_start();
require_once 'config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: auth/login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre_completo']);
    
    // Si el usuario subió una nueva foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nombre_foto = 'perfil_' . $usuario_id . '_' . time() . '.' . $ext;
        $ruta_destino = 'uploads/perfiles/' . $nombre_foto;
        
        // Movemos la imagen a nuestra carpeta
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
            $conexion->prepare("UPDATE usuarios SET nombre_completo = ?, foto_perfil = ? WHERE id = ?")->execute([$nombre, $nombre_foto, $usuario_id]);
            
            $_SESSION['nombre'] = $nombre;
            $_SESSION['foto_perfil'] = $nombre_foto; // Actualizamos la sesión para que el header lo note de inmediato
            
            $mensaje = "¡Perfil y foto actualizados con éxito!";
        } else {
            $mensaje = "Error al subir la imagen.";
        }
    } else {
        // Si solo actualizó el nombre, sin subir foto nueva
        $conexion->prepare("UPDATE usuarios SET nombre_completo = ? WHERE id = ?")->execute([$nombre, $usuario_id]);
        $_SESSION['nombre'] = $nombre;
        $mensaje = "¡Perfil actualizado con éxito!";
    }
}

// Obtener los datos actuales del usuario
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Definimos qué foto mostrar
$ruta_foto_actual = !empty($usuario['foto_perfil']) ? 'uploads/perfiles/' . $usuario['foto_perfil'] : 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
?>

<?php require_once 'includes/header.php'; ?>

<div class="tarjeta" style="max-width: 500px; margin: 40px auto; padding: 40px;">
    <h2 style="color: #0c2340; margin-top: 0;">Configuración de Perfil</h2>
    
    <?php if($mensaje): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <!-- Formulario para actualizar datos (Nota el enctype para subir archivos) -->
    <form action="perfil.php" method="POST" enctype="multipart/form-data" style="text-align: left;">
        
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="<?php echo $ruta_foto_actual; ?>" alt="Avatar" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #1d70b8; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <br><br>
            <label for="foto" style="cursor: pointer; color: #1d70b8; font-weight: bold; text-decoration: underline;">Cambiar Foto de Perfil</label>
            <input type="file" name="foto" id="foto" accept="image/*" style="display: none;" onchange="document.getElementById('file-nombre').innerText = 'Imagen seleccionada lista para guardar';">
            <p id="file-nombre" style="font-size: 12px; color: #28a745; margin-top: 5px;"></p>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; color: #555;">Nombre Completo / Empresa:</label>
            <input type="text" name="nombre_completo" value="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; color: #555;">Correo Electrónico (No editable):</label>
            <input type="email" value="<?php echo htmlspecialchars($usuario['correo']); ?>" disabled style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; background-color: #eee;">
        </div>

        <button type="submit" class="btn-principal" style="width: 100%; padding: 12px; font-size: 16px;">Guardar Cambios</button>
    </form>
    
    <div style="margin-top: 20px; text-align: center;">
        <a href="auth/logout.php" style="color: #ef4255; text-decoration: none; font-weight: bold;">Cerrar Sesión 🚪</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>