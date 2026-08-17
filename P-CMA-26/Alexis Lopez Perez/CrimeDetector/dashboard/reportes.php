<?php
session_start();

require_once "../libs/conexion.php";
require_once "../controllers/ReporteDAO.php";
require_once "../controllers/UsuarioDAO.php";
require_once "../controllers/ColoniaDAO.php";

$dashboard = true;
$paginaActual = 'reportes';
$self = "reportes.php";

$reporteDAO = new ReporteDAO($pdo);
$usuarioDAO = new UsuarioDAO($pdo);
$coloniaDAO = new ColoniaDAO($pdo);

$listaReportes = $reporteDAO->obtenerTodos();
$listaUsuarios = $usuarioDAO->obtenerTodos();
$listaColonias = $coloniaDAO->obtenerTodas();

// Si se edita un reporte
$reporteEditar = null;
if (isset($_GET['editar'])) {
    $folioEditar = (int) $_GET['editar'];
    $reporteEditar = $reporteDAO->obtenerPorFolio($folioEditar);
}

// Mensajes de estado
$mensajesPersonalizados = [
    'reporte_creado'      => 'Reporte registrado exitosamente',
    'reporte_actualizado' => 'Reporte actualizado correctamente',
    'reporte_eliminado'   => 'Reporte eliminado correctamente'
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
    <title>Reportes | Dashboard</title>
</head>
<!-- MODAL / POPUP DE CRÍMENES -->
<div id="modalCrimenes" class="modal-overlay" style="display: none;">
    <div class="modal-contenido">
        <div class="modal-header">
            <h3>Crímenes Reportados (Folio <span id="modalFolio"></span>)</h3>
            <button type="button" class="btn-cerrar-modal" id="cerrarModal">&times;</button>
        </div>
        <div class="modal-body">
            <ul id="listaCrimenesModal">
                <!-- Se llena dinámicamente con JS -->
            </ul>
        </div>
    </div>
</div>
<body>

<?php require_once "../templates/mensajes_popover.php"; ?>

<div class="dashboard-container">
    
    <?php require_once "../templates/sidebar.php"; ?>

    <main class="contenido-dashboard">                

        <section class="seccion-interaccion">
            <div class="card-formulario" style="display: none">
                
                <!-- FORMULARIO DE REPORTES -->
                <form action="../controllers/procesarReporteForm.php" method="POST">
                    <input type="hidden" name="accion" value="<?= $reporteEditar ? 'actualizar_reporte' : 'crear_reporte' ?>">

                    <?php if ($reporteEditar): ?>
                        <input type="hidden" name="folio" value="<?= htmlspecialchars($reporteEditar['FOLIO']) ?>">
                    <?php endif; ?>

                    <!-- SELECT DE USUARIOS (FK) -->
                    <div class="input-group">
                        <label for="usuario">Usuario que reporta</label>
                        <select id="usuario" name="usuario" required>
                            <option value="">Seleccione un usuario...</option>
                            <?php foreach ($listaUsuarios as $usr): ?>
                                <option value="<?= htmlspecialchars($usr->correo) ?>" 
                                    <?= ($reporteEditar && $reporteEditar['USUARIO'] === $usr->correo) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($usr->nombre) ?> (<?= htmlspecialchars($usr->correo) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- SELECT DE COLONIAS (FK) -->
                    <div class="input-group">
                        <label for="colonia">Colonia</label>
                        <select id="colonia" name="colonia" required>
                            <option value="">Seleccione una colonia...</option>
                            <?php foreach ($listaColonias as $col): ?>
                                <option value="<?= htmlspecialchars($col['CODIGO_POSTAL']) ?>" 
                                    <?= ($reporteEditar && (int)$reporteEditar['COLONIA'] === (int)$col['CODIGO_POSTAL']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($col['NOMBRE']) ?> (CP: <?= htmlspecialchars($col['CODIGO_POSTAL']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="direccion">Dirección / Referencia</label>
                        <input type="text" id="direccion" name="direccion" required 
                               value="<?= $reporteEditar ? htmlspecialchars($reporteEditar['DIRECCION']) : '' ?>" />
                    </div>

                    <div class="input-group">
                        <label for="latitud">Latitud</label>
                        <input type="number" step="any" id="latitud" name="latitud" required 
                               value="<?= $reporteEditar ? htmlspecialchars($reporteEditar['LATITUD']) : '' ?>" />
                    </div>

                    <div class="input-group">
                        <label for="longitud">Longitud</label>
                        <input type="number" step="any" id="longitud" name="longitud" required 
                               value="<?= $reporteEditar ? htmlspecialchars($reporteEditar['LONGITUD']) : '' ?>" />
                    </div>

                    <div class="input-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="3" required><?= $reporteEditar ? htmlspecialchars($reporteEditar['DESCRIPCION']) : '' ?></textarea>
                    </div>

                    <div class="botones-form">
                        <input type="submit" value="<?= $reporteEditar ? 'Guardar Cambios' : 'Añadir Reporte' ?>" class="btn-primary">
                    </div>
                </form>

                <?php if ($reporteEditar): ?> 
                    <a href="<?= $self ?>"><button style="margin-top: 15px" class="btn-primary">Nuevo Reporte</button></a>
                <?php endif; ?> 
            </div>

            <!-- TABLA DE REPORTES -->
            <div class="tabla-container">       
                <table>
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Usuario</th>
                            <th>Colonia</th>
                            <th>Fecha</th>
                            <th>Dirección</th>
                            <th>Coordenadas</th>
                            <th>Acciones</th>              
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($listaReportes)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No hay reportes registrados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listaReportes as $rep): ?>
                                <tr>
                                    <td>#<?= htmlspecialchars($rep['FOLIO']) ?></td>
                                    <td><?= htmlspecialchars($rep['NOMBRE_USUARIO'] ?? $rep['USUARIO'] ?? 'Usuario eliminado') ?></td>
                                    <td><?= htmlspecialchars($rep['NOMBRE_COLONIA'] ?? $rep['COLONIA']) ?></td>
                                    <td><?= date("d/m/Y H:i", strtotime($rep['FECHA_CREACION'])) ?></td>
                                    <td><?= htmlspecialchars($rep['DIRECCION']) ?></td>
                                    <td><?= htmlspecialchars($rep['LATITUD']) ?>, <?= htmlspecialchars($rep['LONGITUD']) ?></td>
                                    <td class="acciones-cell">
                                        <div class="acciones-wrapper">
                                            <!-- Ver Crímenes -->
                                            <button type="button" 
                                                    class="btn-tabla btn-ver-crimenes" 
                                                    title="Ver crímenes"
                                                    data-folio="<?= htmlspecialchars($rep['FOLIO']) ?>"
                                                    data-crimenes="<?= htmlspecialchars($rep['CRIMENES_LISTA'] ?? 'Sin crímenes registrados') ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </button>                                            

                                            <!-- Eliminar -->
                                            <form action="../controllers/procesarReporteForm.php" method="POST" class="form-eliminar-inline" onsubmit="return confirm('¿Eliminar este reporte?');">
                                                <input type="hidden" name="accion" value="eliminar_reporte">
                                                <input type="hidden" name="folio" value="<?= htmlspecialchars($rep['FOLIO']) ?>">
                                                <button type="submit" class="btn-tabla btn-eliminar" title="Eliminar reporte">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                                </button>
                                            </form>
                                        </div>
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
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalCrimenes');
    const cerrarModal = document.getElementById('cerrarModal');
    const modalFolio = document.getElementById('modalFolio');
    const listaCrimenes = document.getElementById('listaCrimenesModal');

    document.querySelectorAll('.btn-ver-crimenes').forEach(boton => {
        boton.addEventListener('click', () => {
            const folio = boton.getAttribute('data-folio');
            const crimenesCadena = boton.getAttribute('data-crimenes');

            modalFolio.textContent = `#${folio}`;
            listaCrimenes.innerHTML = '';

            if (crimenesCadena && crimenesCadena !== 'Sin crímenes registrados') {
                const arregloCrimenes = crimenesCadena.split('||');
                arregloCrimenes.forEach(crimen => {
                    const li = document.createElement('li');
                    li.textContent = `⚠️ ${crimen}`;
                    listaCrimenes.appendChild(li);
                });
            } else {
                const li = document.createElement('li');
                li.textContent = 'No hay crímenes asociados a este reporte.';
                listaCrimenes.appendChild(li);
            }

            modal.style.display = 'flex';
        });
    });

    // Cerrar modal al hacer clic en la 'X' o fuera del modal
    cerrarModal.addEventListener('click', () => modal.style.display = 'none');
    
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>
</html>