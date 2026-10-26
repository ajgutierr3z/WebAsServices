<?php
session_start();
require_once 'config/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'empresa') {
    header("Location: dashboard.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: escaparate.php");
    exit();
}

$id_proyecto = $_GET['id'];
$stmt = $conexion->prepare("SELECT * FROM proyectos WHERE id = :id AND estado = 'publicado'");
$stmt->execute(['id' => $id_proyecto]);
$proyecto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$proyecto) {
    die("<h2 style='text-align:center; margin-top:50px;'>Proyecto no encontrado o ya fue financiado.</h2>");
}
?>

<?php require_once 'includes/header.php'; ?>

<!-- Script de PayPal SDK con tu Client ID -->
<script src="https://www.paypal.com/sdk/js?client-id=TU_API_KEY_AQUI&currency=MXN"></script>

<div class="tarjeta" style="max-width: 600px; margin: 40px auto; text-align: center;">
    <h2 style="color: #0c2340;">Confirmar Inversión</h2>
    
    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ddd; text-align: left;">
        <h3 style="color: #1d70b8; margin-top: 0; text-align: center;"><?php echo htmlspecialchars($proyecto['titulo']); ?></h3>
        <p style="font-size: 14px; color: #666; text-align: center;">Nivel TRL: <?php echo htmlspecialchars($proyecto['nivel_trl']); ?></p>
        
        <hr style="border: 0; border-top: 1px solid #ccc; margin: 15px 0;">
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <span>Presupuesto para el Investigador:</span>
            <b>$<?php echo number_format($proyecto['presupuesto_requerido'], 2); ?> MXN</b>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: #666;">
            <span>Comisión de Plataforma (5%):</span>
            <b>$<?php echo number_format($proyecto['comision'], 2); ?> MXN</b>
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 2px dashed #ccc; font-size: 20px; color: #28a745;">
            <b>Total a Pagar:</b>
            <b>$<?php echo number_format($proyecto['presupuesto_final'], 2); ?> MXN</b>
        </div>
    </div>

    <!-- Contenedor donde aparecerán los botones de PayPal -->
    <div id="paypal-button-container" style="max-width: 400px; margin: 0 auto;"></div>
    
    <div style="margin-top: 20px;">
        <a href="escaparate.php" style="color: #ef4255; text-decoration: none;">Cancelar y volver al escaparate</a>
    </div>
</div>

<script>
    paypal.Buttons({
        // 1. Configuramos el pago con el presupuesto FINAL (incluyendo comisión)
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: '<?php echo $proyecto['presupuesto_final']; ?>'
                    },
                    description: 'Inversión en proyecto NexTec: <?php echo htmlspecialchars($proyecto['titulo']); ?>'
                }]
            });
        },
        // 2. Qué hacer cuando el usuario aprueba el pago
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(detalles) {
                // El pago fue exitoso, avisamos a nuestro servidor para cambiar el estado y registrar la empresa
                fetch('procesar_pago.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id_proyecto: <?php echo $id_proyecto; ?>,
                        id_transaccion: detalles.id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('¡Pago completado! Gracias por invertir en innovación.');
                        window.location.href = 'dashboard.php'; 
                    } else {
                        alert('Hubo un error al actualizar la base de datos.');
                    }
                });
            });
        }
    }).render('#paypal-button-container');
</script>

<?php require_once 'includes/footer.php'; ?>