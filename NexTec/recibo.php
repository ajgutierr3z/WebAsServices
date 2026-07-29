<?php
session_start();
require_once 'config/conexion.php';

// Solo las empresas pueden ver y descargar sus recibos
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'empresa') {
    die("Acceso denegado.");
}

$id_proyecto = $_GET['id'] ?? 0;
$id_empresa = $_SESSION['usuario_id'];

// Buscamos el proyecto asegurándonos de que le pertenezca a esta empresa
$stmt = $conexion->prepare("SELECT p.*, u.nombre_completo as investigador FROM proyectos p JOIN usuarios u ON p.id_investigador = u.id WHERE p.id = :id AND p.id_empresa = :id_empresa AND p.estado IN ('financiado', 'finalizado')");
$stmt->execute(['id' => $id_proyecto, 'id_empresa' => $id_empresa]);
$proyecto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$proyecto) {
    die("Recibo no encontrado o proyecto no financiado.");
}

$fecha_actual = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo - <?php echo htmlspecialchars($proyecto['titulo']); ?></title>
    <!-- Librería mágica para convertir HTML a PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; padding: 20px; text-align: center; }
        .recibo-container { background: white; max-width: 600px; margin: 0 auto; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: left; }
        .header-recibo { border-bottom: 2px solid #0c2340; padding-bottom: 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .titulo-nextec { color: #0c2340; margin: 0; font-size: 28px; }
        .btn-imprimir { background-color: #ef4255; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 16px; margin-bottom: 20px; transition: background-color 0.3s; }
        .btn-imprimir:hover { background-color: #c9302c; }
    </style>
</head>
<body>

<!-- Botones de acción (No se verán en el PDF) -->
<button class="btn-imprimir" onclick="generarPDF()" id="btn-descargar">⬇️ Descargar en PDF</button>
<br>
<a href="dashboard.php" style="display: inline-block; margin-bottom: 20px; color: #1d70b8; text-decoration: none;" id="btn-volver">Volver al Dashboard</a>

<div class="recibo-container" id="area-recibo">
    <div class="header-recibo">
        <h1 class="titulo-nextec"> NexTec</h1>
        <div style="text-align: right; color: #666; font-size: 14px;">
            <b>Comprobante de Inversión</b><br>
            Fecha de emisión: <?php echo $fecha_actual; ?><br>
            Folio: #NXT-<?php echo str_pad($proyecto['id'], 5, "0", STR_PAD_LEFT); ?>
        </div>
    </div>

    <h3 style="color: #1d70b8; font-size: 16px; margin-bottom: 5px;">Datos del Inversor:</h3>
    <p style="margin-top: 0; font-size: 15px;"><b>Empresa:</b> <?php echo htmlspecialchars($_SESSION['nombre']); ?></p>

    <h3 style="color: #1d70b8; font-size: 16px; margin-top: 30px; margin-bottom: 5px;">Detalles del Proyecto:</h3>
    <p style="margin: 0 0 5px 0; font-size: 15px;"><b>Título:</b> <?php echo htmlspecialchars($proyecto['titulo']); ?></p>
    <p style="margin: 0; font-size: 15px;"><b>Investigador a cargo:</b> <?php echo htmlspecialchars($proyecto['investigador']); ?></p>

    <div style="background: #f9f9f9; padding: 20px; border-radius: 4px; margin-top: 30px; border: 1px solid #ddd;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 15px;">
            <span>Financiamiento al Investigador:</span>
            <span>$<?php echo number_format($proyecto['presupuesto_requerido'], 2); ?> MXN</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: #666; font-size: 15px;">
            <span>Comisión NexTec (5%):</span>
            <span>$<?php echo number_format($proyecto['comision'], 2); ?> MXN</span>
        </div>
        
        <hr style="border: 0; border-top: 1px dashed #ccc; margin: 15px 0;">
        
        <div style="display: flex; justify-content: space-between; font-size: 20px; color: #28a745; font-weight: bold;">
            <span>Total Pagado:</span>
            <span>$<?php echo number_format($proyecto['presupuesto_final'], 2); ?> MXN</span>
        </div>
    </div>

    <div style="margin-top: 50px; text-align: center; color: #888; font-size: 11px;">
        <p>Este documento es un comprobante digital de financiamiento emitido por la plataforma NexTec.<br>Universidad Tecnológica de Tabasco (UTTAB).</p>
    </div>
</div>

<script>
    function generarPDF() {
        document.getElementById('btn-descargar').style.display = 'none';
        document.getElementById('btn-volver').style.display = 'none';
        
        const elemento = document.getElementById('area-recibo');
        
        const opciones = {
            margin:       10,
            filename:     'Recibo_NexTec_<?php echo $proyecto['id']; ?>.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };
        
        html2pdf().set(opciones).from(elemento).save().then(() => {
            document.getElementById('btn-descargar').style.display = 'inline-block';
            document.getElementById('btn-volver').style.display = 'inline-block';
        });
    }
</script>

</body>
</html>