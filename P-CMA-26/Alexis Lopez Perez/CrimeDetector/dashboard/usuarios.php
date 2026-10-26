<?php
require_once "../libs/conexion.php";
require_once "../controllers/UsuarioDAO.php";
 
session_start();

$dashboard = true;

$self = "usuarios.php";
$paginaActual = 'usuarios';

$usuarioDAO = new UsuarioDAO($pdo);

$listaUsuarios = $usuarioDAO->obtenerTodos();

$usuarioEditar = null;
if (isset($_GET['editar'])) {
    $correoEditar = $_GET['editar'];
    $usuarioEditar = $usuarioDAO->obtenerUsuarioPorCorreo($correoEditar);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <link rel="stylesheet" href="../styles/variables.css">    
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/dashboard.css">
    <link rel="stylesheet" href="../styles/generico.css">
    <link rel="shortcut icon" href="../resources/img/CrimeDetectorLogo.png" type="image/x-icon">
    <title>Usuarios | Dashboard</title>
</head>
<body>
    <div class="dashboard-container">
        <?php require_once "../templates/sidebar.php"; ?>
        <?php
        $mensajesPersonalizados = [
            'usuario_creado' => '¡Nuevo usuario registrado con éxito!',
            'auto_eliminacion' => '¡Atención! No puedes eliminar tu propia cuenta en sesión.'
        ];

        require_once "../templates/mensajes_popover.php"; 
        ?>
        <main class="contenido-dashboard">        
            <section class="seccion-interaccion">
                <div class="card-formulario">
                    <!-- ---------------------------FORMULARIO--------------------------- -->
                    <form action="../controllers/procesarUsuarioForm.php" method="POST">
                        <!-- Acción oculta para procesar por el controlador general -->
                        <input type="hidden" name="accion" value="<?= $usuarioEditar ? 'actualizar_usuario_admin' : 'crear_usuario' ?>">
                        
                        <?php if ($usuarioEditar): ?>
                            <input type="hidden" name="correo_original" value="<?= htmlspecialchars($usuarioEditar->correo) ?>">
                        <?php endif; ?>

                        <div class="input-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" autofocus required
                                value="<?= $usuarioEditar ? htmlspecialchars($usuarioEditar->nombre) : '' ?>" />
                        </div>

                        <div class="input-group">
                        <label for="correo">Correo Electrónico</label>            
                        <input type="email" id="correo" name="correo" required
                                value="<?= $usuarioEditar ? htmlspecialchars($usuarioEditar->correo) : '' ?>" 
                                <?= $usuarioEditar ? 'readonly' : '' ?> />
                        </div>

                        <div class="input-group">
                        <label for="password">Contraseña <?= $usuarioEditar ? '(Dejar en blanco para conservar la actual)' : '' ?></label>            
                        <input type="password" id="password" name="password" <?= $usuarioEditar ? '' : 'required' ?> />
                        </div>

                        <div class="input-group">
                            <label for="rol">Rol</label>
                            <select id="rol" name="rol" required>                        
                                <option value="cliente" <?= ($usuarioEditar && strtolower($usuarioEditar->rol) == 'cliente') ? 'selected' : '' ?>>Cliente</option>  
                                <option value="administrador" <?= ($usuarioEditar && strtolower($usuarioEditar->rol) == 'administrador') ? 'selected' : '' ?>>Administrador</option>                      
                            </select>
                        </div>                                            
                        
                        <div class="botones-form">
                        <input type="submit" value="<?= $usuarioEditar ? 'Guardar Cambios' : 'Añadir' ?>" class="btn-primary">
                        </div>
                    </form>

                    <!-- BOTÓN PARA CANCELAR EDICIÓN Y CREAR UNO NUEVO -->
                    <?php if ($usuarioEditar): ?> 
                    <a href="<?= $self ?>"><button style="margin-top: 15px" class="btn-primary">Nuevo Usuario</button></a>
                    <?php endif; ?> 
                    
                </div>
        <div class="tabla-container">       
            <!-- --------------------------TABLA--------------------------- -->
            <table>
                <thead>
                    <tr>
                    <th>Foto</th>
                    <th>ID</th>
                    <th>Nombre</th>              
                    <th>Rol</th>
                    <th>Acciones</th>              
                    </tr>
                </thead>
                <tbody>
                        <?php if (empty($listaUsuarios)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No hay usuarios registrados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listaUsuarios as $usr): ?>
                                <tr>
                                    <td>
                                        <img src="../<?= htmlspecialchars($usr->foto_perfil) ?>" 
                                            alt="Foto" 
                                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    </td>
                                    <td><?= htmlspecialchars($usr->nombre) ?></td>
                                    <td><?= htmlspecialchars($usr->correo) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($usr->rol)) ?></td>
                                    <td>
                                        <!-- Botón para Editar -->
                                        <a href="<?= $self ?>?editar=<?= urlencode($usr->correo) ?>" class="btn-tabla btn-editar" title="Editar usuario">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z"/></svg>
                                        </a>

                                        <!-- Formulario para Eliminar de forma segura vía POST -->
                                        <form action="../controllers/procesarUsuarioForm.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');">
                                            <input type="hidden" name="accion" value="eliminar_usuario">
                                            <input type="hidden" name="correo" value="<?= htmlspecialchars($usr->correo) ?>">
                                            <button type="submit" class="btn-tabla btn-eliminar" title="Eliminar usuario">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                </tbody>
            </table>    
        </div>        
        </main>        
    </div>
</body>
</html>