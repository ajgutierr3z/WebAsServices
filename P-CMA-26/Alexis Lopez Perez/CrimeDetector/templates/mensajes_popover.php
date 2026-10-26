<?php
// Diccionario base de mensajes
$mensajesBase = [
    // --- Módulos Generales y Perfil ---
    'password_updated'     => 'La contraseña fue actualizada',
    'success'              => 'El nombre fue actualizado',
    'foto_actualizada'     => 'La foto de perfil fue actualizada',
    'passwords_dont_match' => 'Las contraseñas no coinciden',
    'nombre_vacio'         => 'Escribe un nombre en el campo',
    'redireccion_fallida'  => 'Hubo un error al procesar el usuario :(',
    'formato_invalido'     => 'El formato de la imagen no es válido',
    'subida_fallida'       => 'Hubo un error al subir el archivo',
    'no_file'              => 'No se subió ninguna foto',
    'db'                   => 'Ocurrió un error en la base de datos',

    // --- Módulo Dashboard / Usuarios ---
    'usuario_creado'       => 'Usuario registrado exitosamente',
    'usuario_actualizado'  => 'Datos de usuario actualizados',
    'usuario_eliminado'    => 'Usuario eliminado correctamente',
    'correo_registrado'    => 'El correo electrónico ya está registrado',
    'auto_eliminacion'     => 'No puedes eliminar tu propio usuario',
    'campos_incompletos'   => 'Por favor llena todos los campos requeridos'
];

// Si la página que incluye este template definió $mensajesPersonalizados, los unimos
if (isset($mensajesPersonalizados) && is_array($mensajesPersonalizados)) {
    $mensajesBase = array_merge($mensajesBase, $mensajesPersonalizados);
}

$mensaje = "";
$estiloMensaje = "";

if (isset($_GET['status'])) {
    $clave = $_GET['status'];
    $estiloMensaje = "mensajeStatus";
    $mensaje = $mensajesBase[$clave] ?? "Operación realizada con éxito";
} else if (isset($_GET['error'])) {
    $clave = $_GET['error'];
    $estiloMensaje = "mensajeError";
    $mensaje = $mensajesBase[$clave] ?? "Error desconocido :(";
}
?>

<?php if (!empty($mensaje)): ?>
    <div id="popoverMensajes" popover>
        <span><?= htmlspecialchars($mensaje) ?></span>
        <button id="cerrarPopoverMensajes" commandFor="popoverMensajes" command="hide-popover">X</button>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const popoverMensajes = document.getElementById('popoverMensajes');
            const cerrarPopoverMensajes = document.getElementById('cerrarPopoverMensajes');

            if (popoverMensajes) {
                popoverMensajes.showPopover();
                popoverMensajes.classList.add('<?= $estiloMensaje ?>');
                cerrarPopoverMensajes.classList.add('<?= $estiloMensaje ?>');
            }

            setTimeout(() => {
                    // Verificamos si sigue abierto antes de intentar cerrarlo
                    if (popoverMensajes.matches(':popover-open')) {
                        popoverMensajes.hidePopover();
                    }
                }, 5000);
        });
    </script>
<?php endif; ?>