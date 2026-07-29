<?php
session_start();
require_once 'config/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'investigador') {
    header("Location: dashboard.php");
    exit();
}

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $nivel_trl = $_POST['nivel_trl'];
    $presupuesto = $_POST['presupuesto'];
    $id_investigador = $_SESSION['usuario_id'];

    if (empty($titulo) || empty($descripcion) || empty($nivel_trl) || empty($presupuesto)) {
        $mensaje = "Por favor, completa todos los campos requeridos.";
        $tipo_mensaje = "error";
    } else {
        // CÁLCULO DE COMISIÓN (5%)
        $porcentaje_comision = 0.05; 
        $comision = $presupuesto * $porcentaje_comision;
        $presupuesto_final = $presupuesto + $comision;

        $stmt = $conexion->prepare("INSERT INTO proyectos (id_investigador, titulo, descripcion, nivel_trl, presupuesto_requerido, comision, presupuesto_final) VALUES (:id_investigador, :titulo, :descripcion, :nivel_trl, :presupuesto, :comision, :presupuesto_final)");
        
        $exito = $stmt->execute([
            'id_investigador' => $id_investigador,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'nivel_trl' => $nivel_trl,
            'presupuesto' => $presupuesto,
            'comision' => $comision,
            'presupuesto_final' => $presupuesto_final
        ]);

        if ($exito) {
            $mensaje = "¡Propuesta enviada con éxito! El administrador la revisará pronto.";
            $tipo_mensaje = "exito";
        } else {
            $mensaje = "Hubo un error al guardar el proyecto. Intenta de nuevo.";
            $tipo_mensaje = "error";
        }
    }
}
?>

<?php require_once 'includes/header.php'; ?>

<div class="tarjeta" style="max-width: 650px; margin: 40px auto; text-align: left;">
    <h2 style="color: #0c2340; border-bottom: 2px solid #eee; padding-bottom: 10px;">Subir Nueva Propuesta Tecnológica</h2>
    <p style="color: #555; font-size: 14px; margin-bottom: 20px;">Registra tu proyecto de I+D para que el sector productivo pueda evaluarlo y financiarlo.</p>
    
    <?php if($mensaje): ?>
        <div style="padding: 10px; border-radius: 4px; margin-bottom: 15px; font-weight: bold; 
             <?php echo ($tipo_mensaje == 'error') ? 'background-color: #ffe6e6; color: red;' : 'background-color: #d4edda; color: #155724;'; ?>">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <form action="subir_proyecto.php" method="POST">
        
        <label style="font-weight: bold; color: #555;">Título del Proyecto:</label>
        <input type="text" name="titulo" placeholder="Ej. Nuevo Bioplástico a base de..." required style="width: 100%; padding: 12px; margin: 5px 0 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">

        <label style="font-weight: bold; color: #555;">Descripción / Resumen:</label>
        <textarea name="descripcion" rows="5" placeholder="Describe brevemente el objetivo, la metodología y el impacto de tu proyecto..." required style="width: 100%; padding: 12px; margin: 5px 0 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; resize: vertical;"></textarea>

        <label style="font-weight: bold; color: #555;">Nivel de Madurez Tecnológica (TRL):</label>
        <select name="nivel_trl" required style="width: 100%; padding: 12px; margin: 5px 0 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            <option value="" disabled selected>Selecciona el nivel actual</option>
            <option value="1">TRL 1 - Principios básicos observados</option>
            <option value="2">TRL 2 - Concepto tecnológico formulado</option>
            <option value="3">TRL 3 - Prueba de concepto experimental</option>
            <option value="4">TRL 4 - Validación en entorno de laboratorio</option>
            <option value="5">TRL 5 - Validación en entorno relevante</option>
            <option value="6">TRL 6 - Modelo de sistema en entorno relevante</option>
            <option value="7">TRL 7 - Prototipo validado en entorno real</option>
            <option value="8">TRL 8 - Sistema completo y certificado</option>
            <option value="9">TRL 9 - Sistema probado con éxito en entorno real</option>
        </select>

        <label style="font-weight: bold; color: #555;">Presupuesto Requerido (MXN):</label>
        <input type="number" name="presupuesto" min="1000" step="0.01" placeholder="Ej. 150000.00" required style="width: 100%; padding: 12px; margin: 5px 0 20px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">

        <button type="submit" class="btn-principal" style="width: 100%; font-size: 16px;">Enviar Proyecto a Revisión</button>
    </form>
    
    <div style="text-align: center; margin-top: 20px;">
        <a href="dashboard.php" style="color: #1d70b8; text-decoration: none;">← Cancelar y Volver</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>