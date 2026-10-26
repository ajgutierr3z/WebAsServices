<?php
session_start();

require_once "../libs/conexion.php";
require_once "../controllers/ColoniaDAO.php";

$dashboard = true;
$paginaActual = 'colonias'; 
$self = "colonias.php";

$coloniaDAO = new ColoniaDAO($pdo);
$listaColonias = $coloniaDAO->obtenerTodas();

$coloniaEditar = null;
if (isset($_GET['editar'])) {
    $cpEditar = (int) $_GET['editar'];
    $coloniaEditar = $coloniaDAO->obtenerPorCodigoPostal($cpEditar);
}

$mensajesPersonalizados = [
    'colonia_creada'      => 'Colonia registrada exitosamente',
    'colonia_actualizada' => 'Colonia actualizada correctamente',
    'colonia_eliminada'   => 'Colonia eliminada correctamente',
    'cp_registrado'       => 'El Código Postal ya existe en el sistema'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <link rel="stylesheet" href="../styles/variables.css">    
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/dashboard.css">
    <link rel="stylesheet" href="../styles/generico.css">
    <link rel="shortcut icon" href="../resources/img/CrimeDetectorLogo.png" type="image/x-icon">
    <title>Colonias | Dashboard</title>
</head>
<body>

<!-- NOTIFICACIONES POPOVER -->
<?php require_once "../templates/mensajes_popover.php"; ?>

<div class="dashboard-container">
    
    <!-- BARRA LATERAL -->
    <?php require_once "../templates/sidebar.php"; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-dashboard">                

        <section class="seccion-interaccion">
            <div class="card-formulario">
                
                <!-- FORMULARIO DE COLONIAS -->
                <form action="../controllers/procesarColoniaForm.php" method="POST">
                    <input type="hidden" name="accion" value="<?= $coloniaEditar ? 'actualizar_colonia' : 'crear_colonia' ?>">

                    <div class="input-group">
                        <label for="codigo_postal">Código Postal</label>
                        <input type="number" id="codigo_postal" name="codigo_postal" required 
                               value="<?= $coloniaEditar ? htmlspecialchars($coloniaEditar['CODIGO_POSTAL']) : '' ?>" 
                               <?= $coloniaEditar ? 'readonly' : '' ?> />
                    </div>

                    <div class="input-group">
                        <label for="nombre">Nombre de la Colonia</label>            
                        <input type="text" id="nombre" name="nombre" required 
                               value="<?= $coloniaEditar ? htmlspecialchars($coloniaEditar['NOMBRE']) : '' ?>" />
                    </div>

                    <div class="input-group">
                        <label for="latitud">Latitud</label>            
                        <input type="number" step="any" id="latitud" name="latitud" placeholder="Ej: 17.9892" required 
                               value="<?= $coloniaEditar ? htmlspecialchars($coloniaEditar['LATITUD']) : '' ?>" />
                    </div>

                    <div class="input-group">
                        <label for="longitud">Longitud</label>            
                        <input type="number" step="any" id="longitud" name="longitud" placeholder="Ej: -92.9181" required 
                               value="<?= $coloniaEditar ? htmlspecialchars($coloniaEditar['LONGITUD']) : '' ?>" />
                    </div>                                            
                    
                    <div class="botones-form">
                        <input type="submit" value="<?= $coloniaEditar ? 'Guardar Cambios' : 'Añadir' ?>" class="btn-primary">
                    </div>
                </form>

                <!-- BOTÓN PARA DESHACER EDICIÓN Y CREAR NUEVA COLONIA -->
                <?php if ($coloniaEditar): ?> 
                    <a href="<?= $self ?>"><button style="margin-top: 15px" class="btn-primary">Nueva Colonia</button></a>
                <?php endif; ?> 
            </div>

            <!-- TABLA DE COLONIAS -->
            <div class="tabla-container">       
                <table>
                    <thead>
                        <tr>
                            <th>C.P.</th>
                            <th>Nombre</th>
                            <th>Latitud</th>
                            <th>Longitud</th>
                            <th>Acciones</th>              
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($listaColonias)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No hay colonias registradas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listaColonias as $col): ?>
                                <tr>
                                    <td><?= htmlspecialchars($col['CODIGO_POSTAL']) ?></td>
                                    <td><?= htmlspecialchars($col['NOMBRE']) ?></td>
                                    <td><?= htmlspecialchars($col['LATITUD']) ?></td>
                                    <td><?= htmlspecialchars($col['LONGITUD']) ?></td>
                                    <td>
                                        <!-- Editar -->                                        
                                        <a href="<?= $self ?>?editar=<?= urlencode($col['CODIGO_POSTAL']) ?>" class="btn-tabla btn-editar" title="Editar colonia">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z"/></svg>
                                        </a>

                                        <!-- Eliminar -->
                                        <form action="../controllers/procesarColoniaForm.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar esta colonia?');">
                                            <input type="hidden" name="accion" value="eliminar_colonia">
                                            <input type="hidden" name="codigo_postal" value="<?= htmlspecialchars($col['CODIGO_POSTAL']) ?>">
                                            <button type="submit" class="btn-tabla btn-eliminar" title="Eliminar colonia">
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
        </section>
    </main>        
</div>

</body>
</html>