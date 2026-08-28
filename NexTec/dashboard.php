<?php
session_start();
require_once 'config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: auth/login.php");
    exit();
}

$rol = strtolower($_SESSION['rol']);
$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['solicitar_retiro']) && $rol == 'investigador') {
        $conexion->prepare("UPDATE proyectos SET estado_pago = 'solicitado' WHERE id_investigador = :id AND estado IN ('financiado', 'finalizado') AND estado_pago = 'pendiente'")->execute(['id' => $usuario_id]);
        header("Location: dashboard.php");
        exit();
    }
    if (isset($_POST['marcar_pagado']) && ($rol == 'admin' || $rol == 'administrador')) {
        $conexion->prepare("UPDATE proyectos SET estado_pago = 'pagado' WHERE id = ?")->execute([$_POST['id_proyecto']]);
        header("Location: dashboard.php");
        exit();
    }
}

if ($rol == 'investigador') {
    $stmt = $conexion->prepare("SELECT * FROM proyectos WHERE id_investigador = :id ORDER BY fecha_publicacion DESC");
    $stmt->execute(['id' => $usuario_id]);
    $mis_proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt_saldo = $conexion->prepare("SELECT SUM(presupuesto_requerido) as saldo_disponible FROM proyectos WHERE id_investigador = :id AND estado IN ('financiado', 'finalizado') AND estado_pago = 'pendiente'");
    $stmt_saldo->execute(['id' => $usuario_id]);
    $saldo_disponible = $stmt_saldo->fetch(PDO::FETCH_ASSOC)['saldo_disponible'] ?? 0;
}

if ($rol == 'empresa') {
    $stmt = $conexion->prepare("SELECT * FROM proyectos WHERE id_empresa = :id ORDER BY fecha_publicacion DESC");
    $stmt->execute(['id' => $usuario_id]);
    $mis_inversiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($rol == 'admin' || $rol == 'administrador') {
    $stmt = $conexion->query("SELECT COUNT(*) as pendientes FROM proyectos WHERE estado = 'revision'");
    $notificacion_admin = $stmt->fetch(PDO::FETCH_ASSOC)['pendientes'];

    $stmt_finanzas = $conexion->query("SELECT SUM(presupuesto_final) as total_reunido, SUM(comision) as total_ganancia FROM proyectos WHERE estado IN ('financiado', 'finalizado')");
    $finanzas = $stmt_finanzas->fetch(PDO::FETCH_ASSOC);
    $total_reunido = $finanzas['total_reunido'] ?? 0;
    $total_ganancia = $finanzas['total_ganancia'] ?? 0;

    $stmt_retiros = $conexion->query("SELECT p.id, p.titulo, p.presupuesto_requerido, u.nombre_completo, u.correo FROM proyectos p JOIN usuarios u ON p.id_investigador = u.id WHERE p.estado_pago = 'solicitado'");
    $retiros_pendientes = $stmt_retiros->fetchAll(PDO::FETCH_ASSOC);

    $stmt_estados = $conexion->query("SELECT estado, COUNT(*) as cantidad FROM proyectos GROUP BY estado");
    $datos_grafica = ['revision' => 0, 'publicado' => 0, 'financiado' => 0, 'finalizado' => 0];
    while($row = $stmt_estados->fetch(PDO::FETCH_ASSOC)) {
        $datos_grafica[$row['estado']] = $row['cantidad'];
    }
}
?>

<?php require_once 'includes/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .dashboard-container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
    .bienvenida { background-color: #0c2340; color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; }
    .grid-tarjetas { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .tarjeta-dash { background: white; border: 1px solid #ddd; border-radius: 8px; padding: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .badge { padding: 5px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; color: white; }
    .bg-revision { background-color: #f39c12; }
    .bg-publicado { background-color: #28a745; }
    .bg-financiado { background-color: #1d70b8; }
    .bg-finalizado { background-color: #8e44ad; }
</style>

<div class="dashboard-container">
    <div class="bienvenida">
        <h1 style="margin: 0; font-size: 28px;">¡Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?>!</h1>
        <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">Panel de control - Perfil: <b><?php echo strtoupper($rol); ?></b></p>
    </div>

    <?php if ($rol == 'investigador'): ?>
        <div class="grid-tarjetas">
            <div class="tarjeta-dash" style="border-top: 4px solid #0c2340;">
                <h3 style="color: #1d70b8; margin-top: 0;">Subir Propuesta</h3>
                <a href="subir_proyecto.php" class="btn-principal" style="display: block; text-align: center;">+ Nuevo Proyecto</a>
            </div>
            
            <div class="tarjeta-dash" style="border-top: 4px solid #f39c12;">
                <h3 style="color: #f39c12; margin-top: 0;">Mi Billetera</h3>
                <p style="margin-bottom: 5px;">Saldo disponible de inversiones:</p>
                <h2 style="margin: 0 0 15px 0; color: #28a745;">$<?php echo number_format($saldo_disponible, 2); ?> MXN</h2>
                
                <?php if($saldo_disponible > 0): ?>
                    <form action="dashboard.php" method="POST">
                        <input type="hidden" name="solicitar_retiro" value="1">
                        <button type="submit" class="btn-principal" style="background-color: #f39c12; width: 100%;">Solicitar Retiro de Fondos</button>
                    </form>
                <?php else: ?>
                    <button class="btn-principal" style="background-color: #ccc; width: 100%; cursor: not-allowed;" disabled>No hay saldo pendiente</button>
                <?php endif; ?>
            </div>
        </div>

        <div class="tarjeta-dash">
            <h3 style="color: #1d70b8; margin-top: 0;">Mis Proyectos</h3>
            <?php if(count($mis_proyectos) > 0): ?>
                <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                    <tr style="border-bottom: 2px solid #eee; text-align: left;">
                        <th style="padding: 10px;">Proyecto</th>
                        <th style="padding: 10px;">Estado</th>
                        <th style="padding: 10px;">Acción</th>
                    </tr>
                    <?php foreach($mis_proyectos as $p): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px 10px;"><b><?php echo htmlspecialchars($p['titulo']); ?></b></td>
                            <td style="padding: 15px 10px;">
                                <?php 
                                    if($p['estado'] == 'revision') echo '<span class="badge bg-revision">En Revisión</span>';
                                    elseif($p['estado'] == 'publicado') echo '<span class="badge bg-publicado">Publicado</span>';
                                    elseif($p['estado'] == 'financiado') echo '<span class="badge bg-financiado">Financiado (En Curso)</span>';
                                    elseif($p['estado'] == 'finalizado') echo '<span class="badge bg-finalizado">Finalizado</span>';
                                ?>
                            </td>
                            <td style="padding: 15px 10px;">
                                <?php if($p['estado'] == 'financiado' || $p['estado'] == 'finalizado'): ?>
                                    <a href="workspace.php?id=<?php echo $p['id']; ?>" class="btn-principal" style="background-color: #1d70b8; padding: 5px 10px; font-size: 12px;">Entrar al Chat</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p style="color: #666;">Aún no has publicado ningún proyecto.</p>
            <?php endif; ?>
        </div>

    <?php elseif ($rol == 'empresa'): ?>
        <div class="grid-tarjetas">
            <div class="tarjeta-dash" style="border-top: 4px solid #28a745;">
                <h3 style="color: #1d70b8; margin-top: 0;">Escaparate Tecnológico</h3>
                <a href="escaparate.php" class="btn-principal" style="background-color: #28a745; display: block; text-align: center;">Explorar Proyectos</a>
            </div>
        </div>

        <div class="tarjeta-dash">
            <h3 style="color: #1d70b8; margin-top: 0;">Mis Inversiones (Proyectos Financiados)</h3>
            <?php if(count($mis_inversiones) > 0): ?>
                <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                    <tr style="border-bottom: 2px solid #eee; text-align: left;">
                        <th style="padding: 10px;">Proyecto</th>
                        <th style="padding: 10px;">Estado</th>
                        <th style="padding: 10px;">Acción</th>
                    </tr>
                    <?php foreach($mis_inversiones as $p): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px 10px;"><b><?php echo htmlspecialchars($p['titulo']); ?></b></td>
                            <td style="padding: 15px 10px;">
                                <?php 
                                    if($p['estado'] == 'financiado') echo '<span class="badge bg-financiado">En Desarrollo</span>';
                                    elseif($p['estado'] == 'finalizado') echo '<span class="badge bg-finalizado">Finalizado / Listo</span>';
                                ?>
                            </td>
                            <td style="padding: 15px 10px;">
                                <div style="display: flex; gap: 5px;">
                                    <a href="workspace.php?id=<?php echo $p['id']; ?>" class="btn-principal" style="background-color: #1d70b8; padding: 5px 10px; font-size: 12px;">Sala de Trabajo</a>
                                    <a href="recibo.php?id=<?php echo $p['id']; ?>" class="btn-principal" style="background-color: #ef4255; padding: 5px 10px; font-size: 12px;">📄 Recibo PDF</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p style="color: #666;">Aún no has financiado ningún proyecto.</p>
            <?php endif; ?>
        </div>

    <?php elseif ($rol == 'admin' || $rol == 'administrador'): ?>
        <div class="grid-tarjetas">
            <div class="tarjeta-dash" style="border-top: 4px solid #ef4255;">
                <h3 style="color: #1d70b8; margin-top: 0;">Validación de Calidad (SQA)</h3>
                <a href="admin/validar_proyectos.php" class="btn-principal" style="background-color: #ef4255; display: block; text-align: center;">
                    Revisar Proyectos <?php if($notificacion_admin > 0) echo "(<b style='color:#fff;'>$notificacion_admin</b>)"; ?>
                </a>
            </div>
            
            <div class="tarjeta-dash" style="border-top: 4px solid #0c2340;">
                <h3 style="color: #1d70b8; margin-top: 0;">Métricas Globales Financieras</h3>
                <p style="margin-bottom: 5px;">Capital inyectado a proyectos:</p>
                <h2 style="margin: 0 0 15px 0; color: #28a745;">$<?php echo number_format($total_reunido, 2); ?> MXN</h2>
                <p style="margin-bottom: 5px;">Ganancia retenida por NexTec (5%):</p>
                <h2 style="margin: 0; color: #1d70b8;">$<?php echo number_format($total_ganancia, 2); ?> MXN</h2>
            </div>
        </div>

        <div class="tarjeta-dash" style="border-top: 4px solid #8e44ad; display: flex; flex-direction: column; align-items: center;">
            <h3 style="color: #8e44ad; margin-top: 0; width: 100%; text-align: left;">Distribución de Proyectos</h3>
            <div style="width: 100%; max-width: 400px; height: 300px; margin-top: 15px;">
                <canvas id="graficaProyectos"></canvas>
            </div>
        </div>

        <div class="tarjeta-dash" style="border-top: 4px solid #f39c12; margin-top: 30px;">
            <h3 style="color: #f39c12; margin-top: 0;">Solicitudes de Retiro Pendientes</h3>
            <?php if(count($retiros_pendientes) > 0): ?>
                <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                    <tr style="border-bottom: 2px solid #eee; text-align: left;">
                        <th style="padding: 10px;">Investigador</th>
                        <th style="padding: 10px;">Proyecto</th>
                        <th style="padding: 10px;">Monto a Pagar</th>
                        <th style="padding: 10px;">Acción</th>
                    </tr>
                    <?php foreach($retiros_pendientes as $r): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px 10px;">
                                <b><?php echo htmlspecialchars($r['nombre_completo']); ?></b><br>
                                <span style="font-size: 12px; color: #666;"><?php echo htmlspecialchars($r['correo']); ?></span>
                            </td>
                            <td style="padding: 15px 10px;"><?php echo htmlspecialchars($r['titulo']); ?></td>
                            <td style="padding: 15px 10px; color: #28a745; font-weight: bold;">$<?php echo number_format($r['presupuesto_requerido'], 2); ?></td>
                            <td style="padding: 15px 10px;">
                                <form action="dashboard.php" method="POST" onsubmit="return confirm('¿Confirmas que ya transferiste este dinero al investigador?');">
                                    <input type="hidden" name="id_proyecto" value="<?php echo $r['id']; ?>">
                                    <input type="hidden" name="marcar_pagado" value="1">
                                    <button type="submit" class="btn-principal" style="background-color: #28a745; padding: 5px 10px; font-size: 12px;">Marcar Pagado</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p style="color: #666;">No hay investigadores solicitando retiros en este momento.</p>
            <?php endif; ?>
        </div>
        
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const ctx = document.getElementById('graficaProyectos').getContext('2d');
                const datosGrafica = <?php echo json_encode([
                    $datos_grafica['revision'] ?? 0, 
                    $datos_grafica['publicado'] ?? 0, 
                    $datos_grafica['financiado'] ?? 0, 
                    $datos_grafica['finalizado'] ?? 0
                ]); ?>;
                
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['En Revisión', 'Publicados', 'Financiados', 'Finalizados'],
                        datasets: [{
                            data: datosGrafica,
                            backgroundColor: ['#f39c12', '#28a745', '#1d70b8', '#8e44ad'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            });
        </script>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>