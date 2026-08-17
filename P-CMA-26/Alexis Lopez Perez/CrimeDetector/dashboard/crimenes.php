<?php
session_start();

require_once "../libs/conexion.php";
require_once "../controllers/TipoCrimenDAO.php";

$dashboard = true;
$paginaActual = 'crimenes'; 
$self = "crimenes.php";

$tipoCrimenDAO = new TipoCrimenDAO($pdo);
$listaTipos = $tipoCrimenDAO->obtenerTodos();

$tipoEditar = null;
if (isset($_GET['editar'])) {
    $cveEditar = (int) $_GET['editar'];
    $tipoEditar = $tipoCrimenDAO->obtenerPorCve($cveEditar);
}

$mensajesPersonalizados = [
    'tipo_creado'      => 'Tipo de crimen registrado exitosamente',
    'tipo_actualizado' => 'Tipo de crimen actualizado correctamente',
    'tipo_eliminado'   => 'Tipo de crimen eliminado correctamente'
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
    <title>Tipos de Crimen | Dashboard</title>
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
                
                <!-- FORMULARIO DE TIPOS DE CRIMEN -->
                <form action="../controllers/procesarTipoCrimenForm.php" method="POST">
                    <input type="hidden" name="accion" value="<?= $tipoEditar ? 'actualizar_tipo_crimen' : 'crear_tipo_crimen' ?>">

                    <?php if ($tipoEditar): ?>
                        <input type="hidden" name="cve_tipo_crimen" value="<?= htmlspecialchars($tipoEditar['CVE_TIPO_CRIMEN']) ?>">
                    <?php endif; ?>

                    <div class="input-group">
                        <label for="nombre">Nombre del Crimen</label>
                        <input type="text" id="nombre" name="nombre" required 
                               value="<?= $tipoEditar ? htmlspecialchars($tipoEditar['NOMBRE']) : '' ?>" />
                    </div>

                    <div class="input-group">
                        <label for="gravedad">Nivel de Gravedad (1 a 5)</label>            
                        <select id="gravedad" name="gravedad" required>
                            <option value="">Selecciona la gravedad...</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= ($tipoEditar && (int)$tipoEditar['GRAVEDAD'] === $i) ? 'selected' : '' ?>>
                                    Nivel <?= $i ?> <?php 
                                    switch ($i) {
                                        case 1:
                                            echo "Leve / Incidencia Menor";
                                            break;
                                        case 2:
                                            echo "Moderado";
                                            break;
                                        case 3:
                                            echo "Relevante / Medio";
                                            break;
                                        case 4:
                                            echo "Grave / Alto Impacto";
                                            break;
                                        case 5:
                                            echo "Critico / Extrema Gravedad";
                                            break;
                                        default:
                                            echo "Error";
                                            break;
                                    }
                                    ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>                                          
                    
                    <div class="botones-form">
                        <input type="submit" value="<?= $tipoEditar ? 'Guardar Cambios' : 'Añadir' ?>" class="btn-primary">
                    </div>
                </form>

                <!-- BOTÓN PARA DESHACER EDICIÓN -->
                <?php if ($tipoEditar): ?> 
                    <a href="<?= $self ?>"><button style="margin-top: 15px" class="btn-primary">Nuevo Tipo de Crimen</button></a>
                <?php endif; ?> 
            </div>

            <!-- TABLA DE TIPOS DE CRIMEN -->
            <div class="tabla-container">       
                <table>
                    <thead>
                        <tr>
                            <th>Clave</th>
                            <th>Nombre</th>
                            <th>Gravedad</th>
                            <th>Acciones</th>              
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($listaTipos)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">No hay tipos de crimen registrados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listaTipos as $tipo): ?>
                                <tr>
                                    <td><?= htmlspecialchars($tipo['CVE_TIPO_CRIMEN']) ?></td>
                                    <td><?= htmlspecialchars($tipo['NOMBRE']) ?></td>
                                    <td>Nivel <?= htmlspecialchars($tipo['GRAVEDAD']) ?></td>
                                    <td>
                                        <!-- Editar -->
                                        <a href="<?= $self ?>?editar=<?= urlencode($tipo['CVE_TIPO_CRIMEN']) ?>" class="btn-tabla btn-editar" title="Editar usuario">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z"/></svg>
                                        </a>

                                        <!-- Eliminar -->
                                        <form action="../controllers/procesarTipoCrimenForm.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este tipo de crimen?');">
                                            <input type="hidden" name="accion" value="eliminar_tipo_crimen">
                                            <input type="hidden" name="cve_tipo_crimen" value="<?= htmlspecialchars($tipo['CVE_TIPO_CRIMEN']) ?>">
                                            <button type="submit" class="btn-tabla btn-eliminar" title="Eliminar crimen">
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