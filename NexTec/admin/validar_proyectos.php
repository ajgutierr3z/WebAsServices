<?php
session_start();
require_once '../config/conexion.php';

// Validamos minúsculas por seguridad
$rol = strtolower($_SESSION['rol']);

// Validación para que solo entre el administrador (reconoce ambos términos)
if ($rol !== 'admin' && $rol !== 'administrador') {
    header("Location: ../dashboard.php");
    exit();
}

// Lógica para cambiar estado o eliminar
if (isset($_GET['accion']) && isset($_GET['id'])) {
    if ($_GET['accion'] == 'publicar') {
        $update = $conexion->prepare("UPDATE proyectos SET estado = 'publicado' WHERE id = :id");
        $update->execute(['id' => $_GET['id']]);
    } elseif ($_GET['accion'] == 'eliminar') {
        $delete = $conexion->prepare("DELETE FROM proyectos WHERE id = :id");
        $delete->execute(['id' => $_GET['id']]);
    }
    
    header("Location: validar_proyectos.php");
    exit();
}

$query = $conexion->query("SELECT * FROM proyectos WHERE estado = 'revision'");
$pendientes = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require_once '../includes/header.php'; ?>

<div class="tarjeta" style="max-width: 800px; margin: 40px auto; text-align: left;">
    <h2 style="color: #0c2340;">Panel de Validación de Proyectos</h2>
    <p>Revisa las propuestas antes de mostrarlas a las empresas.</p>
    
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <tr style="background: #0c2340; color: white;">
            <th style="padding: 10px; text-align: left;">Título del Proyecto</th>
            <th style="padding: 10px; text-align: left;">TRL</th>
            <th style="padding: 10px; text-align: right;">Acciones</th>
        </tr>
        <?php if(count($pendientes) > 0): ?>
            <?php foreach($pendientes as $p): ?>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 10px;"><b><?php echo htmlspecialchars($p['titulo']); ?></b></td>
                <td style="padding: 10px;">Nivel <?php echo htmlspecialchars($p['nivel_trl']); ?></td>
                <td style="padding: 10px; text-align: right;">
                    <a href="validar_proyectos.php?accion=publicar&id=<?php echo $p['id']; ?>" class="btn-principal" style="background-color: #28a745; padding: 5px 10px; font-size: 12px; margin-right: 5px;">Publicar</a>
                    <a href="validar_proyectos.php?accion=eliminar&id=<?php echo $p['id']; ?>" class="btn-principal" style="background-color: #ef4255; padding: 5px 10px; font-size: 12px;" onclick="return confirm('¿Seguro que deseas eliminar esta propuesta?');">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3" style="padding: 20px; text-align: center;">No hay proyectos en fase de revisión.</td>
            </tr>
        <?php endif; ?>
    </table>
    
    <div style="text-align: center; margin-top: 20px;">
        <a href="../dashboard.php" style="color: #1d70b8; text-decoration: none;">← Volver a mi panel</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>