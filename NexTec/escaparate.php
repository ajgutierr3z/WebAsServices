<?php
session_start();
require_once 'config/conexion.php';

$stmt = $conexion->query("SELECT p.*, u.nombre_completo as investigador FROM proyectos p JOIN usuarios u ON p.id_investigador = u.id WHERE p.estado = 'publicado' ORDER BY p.fecha_publicacion DESC");
$proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require_once 'includes/header.php'; ?>

<style>
    .escaparate-container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
    .panel-filtros { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 30px; display: flex; gap: 15px; flex-wrap: wrap; }
    .grid-proyectos { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
    .tarjeta-proyecto { background: white; border: 1px solid #ddd; border-radius: 8px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between; }
    .badge-trl { background-color: #1d70b8; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
</style>

<div class="escaparate-container">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #0c2340; margin-bottom: 10px;">Escaparate Tecnológico UTTAB</h1>
        <p style="color: #555;">Explora propuestas de innovación universitaria e invierte en proyectos con alto impacto.</p>
    </div>

    <div class="panel-filtros">
        <input type="text" id="input_busqueda" placeholder="🔍 Buscar por título o descripción..." style="flex: 2; min-width: 200px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        
        <select id="select_trl" style="flex: 1; min-width: 180px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            <option value="">Todos los Niveles TRL</option>
            <option value="1">TRL 1 - Principios Básicos</option>
            <option value="2">TRL 2 - Concepto Formulado</option>
            <option value="3">TRL 3 - Prueba de Concepto</option>
            <option value="4">TRL 4 - Validación en Lab</option>
            <option value="5">TRL 5 - Validación en Entorno</option>
            <option value="6">TRL 6 - Modelo de Sistema</option>
            <option value="7">TRL 7 - Prototipo Real</option>
            <option value="8">TRL 8 - Sistema Certificado</option>
            <option value="9">TRL 9 - Sistema Probrado</option>
        </select>
    </div>

    <div id="sin_resultados" style="display: none; text-align: center; padding: 40px; background: white; border-radius: 8px; color: #666;">
        <h3>No se encontraron proyectos que coincidan con tu búsqueda.</h3>
        <p>Intenta con otras palabras clave o borra los filtros de TRL.</p>
    </div>

    <div class="grid-proyectos">
        <?php if(count($proyectos) > 0): ?>
            <?php foreach($proyectos as $p): ?>
                <div class="tarjeta-proyecto" 
                     data-titulo="<?php echo htmlspecialchars(mb_strtolower($p['titulo'])); ?>" 
                     data-descripcion="<?php echo htmlspecialchars(mb_strtolower($p['descripcion'])); ?>" 
                     data-trl="<?php echo $p['nivel_trl']; ?>">
                    
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                            <span class="badge-trl">TRL <?php echo htmlspecialchars($p['nivel_trl']); ?></span>
                            <span style="font-size: 12px; color: #777;"><?php echo date('d/m/Y', strtotime($p['fecha_publicacion'])); ?></span>
                        </div>
                        
                        <h3 style="color: #0c2340; margin: 10px 0; font-size: 18px;"><?php echo htmlspecialchars($p['titulo']); ?></h3>
                        <p style="color: #555; font-size: 14px; line-height: 1.4; margin-bottom: 15px;">
                            <?php echo htmlspecialchars(substr($p['descripcion'], 0, 120)) . '...'; ?>
                        </p>
                        <p style="font-size: 12px; color: #888;">Investigador: <b><?php echo htmlspecialchars($p['investigador']); ?></b></p>
                    </div>

                    <div style="border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <span style="font-size: 11px; color: #777; display: block;">Inversión requerida:</span>
                            <b style="color: #28a745; font-size: 16px;">$<?php echo number_format($p['presupuesto_final'], 2); ?></b>
                        </div>

                        <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'empresa'): ?>
                            <a href="checkout.php?id=<?php echo $p['id']; ?>" class="btn-principal" style="background-color: #28a745; text-decoration: none; font-size: 13px; padding: 8px 12px;">Financiar</a>
                        <?php else: ?>
                            <a href="auth/login.php" class="btn-principal" style="background-color: #1d70b8; text-decoration: none; font-size: 13px; padding: 8px 12px;">Ingresar para Invertir</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1 / -1; text-align: center; color: #666; padding: 40px; background: white; border-radius: 8px;">
                No hay proyectos publicados disponibles para financiar en este momento.
            </p>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputBusqueda = document.getElementById('input_busqueda');
    const selectTrl = document.getElementById('select_trl');
    const proyectos = document.querySelectorAll('.tarjeta-proyecto');
    const msjSinResultados = document.getElementById('sin_resultados');

    function filtrarProyectos() {
        const textoBusqueda = inputBusqueda.value.toLowerCase();
        const trlSeleccionado = selectTrl.value;
        let proyectosVisibles = 0;

        proyectos.forEach(proyecto => {
            const titulo = proyecto.getAttribute('data-titulo') || "";
            const descripcion = proyecto.getAttribute('data-descripcion') || "";
            const trl = proyecto.getAttribute('data-trl') || "";

            const coincideTexto = titulo.includes(textoBusqueda) || descripcion.includes(textoBusqueda);
            const coincideTrl = (trlSeleccionado === "") || (trl === trlSeleccionado);

            if (coincideTexto && coincideTrl) {
                proyecto.style.display = 'flex';
                proyectosVisibles++;
            } else {
                proyecto.style.display = 'none';
            }
        });

        if (proyectosVisibles === 0) {
            msjSinResultados.style.display = 'block';
        } else {
            msjSinResultados.style.display = 'none';
        }
    }

    if (inputBusqueda && selectTrl) {
        inputBusqueda.addEventListener('input', filtrarProyectos);
        selectTrl.addEventListener('change', filtrarProyectos);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>