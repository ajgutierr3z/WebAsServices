<?php
    session_start();
    
    require_once 'libs/sessionCheck.php';
    require_once "libs/conexion.php";
    require_once "controllers/UsuarioDAO.php";    

    $usuarioDao = new UsuarioDAO($pdo);

    $correoABuscar = $_SESSION['correo'];

    $usuario = $usuarioDao->obtenerUsuarioPorCorreo($correoABuscar);

    $nombre = $usuario->nombre;
    $correo = $usuario->correo;
    $foto_perfil = $usuario->foto_perfil;    
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="resources/img/CrimeDetectorLogo.png" type="image/x-icon">
    <link rel="stylesheet" href="styles/normalize.css">
    <link rel="stylesheet" href="styles/variables.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/generico.css">
    <link rel="stylesheet" href="styles/profile.css">
    <title><?php echo htmlspecialchars($nombre); ?> | CrimeDetector</title>
</head>
<body>            
    <!-- DIALOG PARA CAMBIAR EL NOMBRE -->
    <dialog id="dialogCambiarNombre" class="dialogForm">
        <button class="exitDialog" commandFor="dialogCambiarNombre" command="close">✕</button>
        <h2>Cambiar nombre de usuario</h2>
        <form action="controllers/procesarUsuarioForm.php" method="POST">        
            <input type="hidden" name="accion" value="actualizar_nombre">
            <input type="text" value="<?php echo htmlspecialchars($nombre); ?>" name="nuevoNombre" required>
            <div class="cajaBotonesDialog">
                <button type="button" class="btn-dialog-cancel" commandFor="dialogCambiarNombre" command="close">Cancelar</button>
                <button type="submit" class="btn-dialog-submit">Guardar</button>
            </div>
        </form>
    </dialog>

    <!-- DIALOG PARA CAMBIAR LA CONTRASEÑA -->
    <dialog id="dialogCambiarPassword" class="dialogForm">
        <button class="exitDialog" commandFor="dialogCambiarPassword" command="close">✕</button>
        <h2>Nueva contraseña</h2>
        <form action="controllers/procesarUsuarioForm.php" method="POST" id="formularioCambiarPassword">        
            <input type="hidden" name="accion" value="actualizar_password">
            <input type="password" placeholder="Nueva Contraseña" name="nuevoPassword" id="nuevoPassword" required>
            <input type="password" placeholder="Confirmar Contraseña" name="nuevoPassword2" id="nuevoPassword2" required>
            <span id="mensajeErrorPassword" style="color: #dc2626; font-size: 0.85rem; display: none">Las contraseñas no coinciden</span>
            <div class="cajaBotonesDialog">
                <button type="button" class="btn-dialog-cancel" commandFor="dialogCambiarPassword" command="close">Cancelar</button>
                <button type="submit" class="btn-dialog-submit">Actualizar</button>
            </div>
        </form>                
    </dialog>

    <!-- DIALOG PARA ELIMINAR LA CUENTA -->
    <dialog id="dialogEliminarCuenta" class="dialogForm">
        <button class="exitDialog" commandFor="dialogEliminarCuenta" command="close">✕</button>
        <h2 style="color: #dc2626;">¿Deseas eliminar tu cuenta?</h2>
        <p style="color: #6b7280; font-size: 0.9rem; margin-top: -8px;">Esta acción es irreversible y se perderán todos tus datos asociados.</p>
        <form action="controllers/procesarUsuarioForm.php" method="POST" id="formularioEliminarCuenta">        
            <input type="hidden" name="accion" value="borrar_cuenta">            
            <div class="cajaBotonesDialog">                
                <button type="button" class="btn-dialog-cancel" commandFor="dialogEliminarCuenta" command="close">Cancelar</button>
                <button type="submit" class="btn-dialog-submit" style="background-color: #dc2626;">Sí, eliminar</button>
            </div>
        </form>
    </dialog>

    <!-- DIALOG PARA CAMBIAR FOTO DE PERFIL -->
    <dialog id="dialogCambiarFoto" class="dialogForm">
        <button class="exitDialog" commandFor="dialogCambiarFoto" command="close">✕</button>
        <h2>Actualizar foto de perfil</h2>
        
        <form action="controllers/procesarUsuarioForm.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="accion" value="actualizar_foto">
            
            <!-- Dropzone personalizada -->
            <div class="dropzone" id="dropzoneFoto">
                <input type="file" name="fotoPerfil" id="inputFotoPerfil" accept="image/png, image/jpeg, image/webp" required hidden>
                
                <div class="dropzone-content" id="dropzoneContent">
                    <div class="dropzone-icon">📷</div>
                    <p class="dropzone-text"><strong>Haz clic para subir</strong> o arrastra una imagen</p>
                    <span class="dropzone-hint">PNG, JPG o WEBP (máx. 5MB)</span>
                </div>

                <div class="preview-container" id="previewContainer" style="display: none;">
                    <img id="imgPreview" src="" alt="Vista previa">
                    <span id="fileName" class="file-name"></span>
                </div>
            </div>

            <div class="cajaBotonesDialog">
                <button type="button" class="btn-dialog-cancel" commandFor="dialogCambiarFoto" command="close">Cancelar</button>
                <button type="submit" class="btn-dialog-submit">Subir foto</button>
            </div>
        </form>
    </dialog>

    <?php require_once "templates/header.php"; ?>

    <main class="profile-container">
        <?php require_once "templates/mensajes_popover.php"; ?>  
        
        <div class="profile-card">
            <div class="profile-header">
                <h2>Ajustes del Perfil</h2>
            </div>

            <div class="profile-grid">
                <!-- Columna Izquierda: Imagen -->
                <div class="avatar-wrapper">
                    <div class="avatar-container">
                        <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de Perfil"> 
                    </div>
                    <button class="btn-change-photo" commandFor="dialogCambiarFoto" command="show-modal">Cambiar foto</button>
                </div>        

                <!-- Columna Derecha: Información del Usuario -->
                <div class="user-info">
                    <div class="info-group">
                        <span class="info-label">Nombre de usuario</span>
                        <p class="info-value"><?php echo htmlspecialchars($nombre); ?></p>
                    </div>

                    <div class="info-group">
                        <span class="info-label">Correo electrónico</span>
                        <p class="info-value"><?php echo htmlspecialchars($correo); ?></p>
                    </div>

                    <!-- Grupo de Botones -->
                    <div class="actions-group">                    
                        <button class="btn-action" commandFor="dialogCambiarNombre" command="show-modal">Editar nombre</button>
                        <button class="btn-action" commandFor="dialogCambiarPassword" command="show-modal">Cambiar contraseña</button>
                        <button class="btn-action btn-danger" commandFor="dialogEliminarCuenta" command="show-modal">Eliminar cuenta</button>
                    </div>                
                </div>
            </div>
        </div>
    </main>        
</body>
<script src="scripts/profile.js"></script>
</html>