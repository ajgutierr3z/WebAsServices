<?php
session_start();
require_once 'config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: auth/login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$rol = strtolower($_SESSION['rol']);
$id_proyecto = $_GET['id'] ?? 0;

$stmt = $conexion->prepare("SELECT * FROM proyectos WHERE id = :id AND (id_investigador = :uid OR id_empresa = :uid) AND estado IN ('financiado', 'finalizado')");
$stmt->execute(['id' => $id_proyecto, 'uid' => $usuario_id]);
$proyecto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$proyecto) {
    die("<h2 style='text-align:center; margin-top:50px;'>No tienes acceso a esta Sala de Trabajo.</h2>");
}
$update_leidos = $conexion->prepare("UPDATE seguimiento_proyectos SET leido = 1 WHERE id_proyecto = :id_p AND id_usuario != :id_u AND leido = 0");
$update_leidos->execute(['id_p' => $id_proyecto, 'id_u' => $usuario_id]);   

// 1. Lógica para Enviar Mensaje y Archivos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mensaje'])) {
    $mensaje = trim($_POST['mensaje']);
    $archivo_nombre = null;

    // Si subieron un archivo
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0) {
        $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
        $archivo_nombre = time() . '_' . rand(100,999) . '.' . $ext;
        move_uploaded_file($_FILES['archivo']['tmp_name'], 'uploads/' . $archivo_nombre);
    }

    if (!empty($mensaje) || $archivo_nombre) {
        $insert = $conexion->prepare("INSERT INTO seguimiento_proyectos (id_proyecto, id_usuario, mensaje, archivo_adjunto) VALUES (:ip, :iu, :m, :a)");
        $insert->execute([
            'ip' => $id_proyecto,
            'iu' => $usuario_id,
            'm' => $mensaje,
            'a' => $archivo_nombre
        ]);
        header("Location: workspace.php?id=" . $id_proyecto);
        exit();
    }
}

if (isset($_POST['accion']) && $_POST['accion'] == 'finalizar' && $rol == 'investigador') {
    $conexion->prepare("UPDATE proyectos SET estado = 'finalizado' WHERE id = ?")->execute([$id_proyecto]);
    header("Location: workspace.php?id=" . $id_proyecto);
    exit();
}

$stmt_chat = $conexion->prepare("SELECT s.*, u.nombre_completo FROM seguimiento_proyectos s JOIN usuarios u ON s.id_usuario = u.id WHERE s.id_proyecto = :id ORDER BY s.fecha_envio ASC");
$stmt_chat->execute(['id' => $id_proyecto]);
$mensajes = $stmt_chat->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require_once 'includes/header.php'; ?>

<style>
    .workspace-container { max-width: 900px; margin: 30px auto; display: grid; grid-template-columns: 1fr; gap: 20px; }
    .panel-info { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .chat-box { background: #e5ddd5; padding: 20px; height: 400px; overflow-y: scroll; border-radius: 8px; display: flex; flex-direction: column; gap: 15px; }
    .mensaje { max-width: 70%; padding: 10px 15px; border-radius: 8px; position: relative; }
    .mensaje.mio { background: #dcf8c6; align-self: flex-end; border-bottom-right-radius: 0; }
    .mensaje.otro { background: white; align-self: flex-start; border-bottom-left-radius: 0; }
    .fecha-msg { font-size: 10px; color: #888; text-align: right; margin-top: 5px; display: block; }
    .form-chat { display: flex; gap: 10px; margin-top: 15px; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .alerta-finalizado { background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; border: 1px solid #c3e6cb; margin-bottom: 20px; text-align: center; font-size: 18px;}
</style>

<div class="workspace-container">
    
    <div class="panel-info">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin: 0; color: #0c2340;"><?php echo htmlspecialchars($proyecto['titulo']); ?></h2>
                <p style="margin: 5px 0 0 0; color: #666;">
                    Estado: <b><?php echo strtoupper($proyecto['estado']); ?></b>
                </p>
            </div>
            <a href="dashboard.php" class="btn-principal" style="background: #666;">Volver al Panel</a>
        </div>

        <?php if ($proyecto['estado'] == 'finalizado'): ?>
            <div class="alerta-finalizado" style="margin-top: 20px;">
                🎉 <b>¡El proyecto ha sido marcado como Finalizado!</b><br>
                <?php if ($rol == 'empresa'): ?>
                    Ya puedes proceder con los acuerdos de compra, préstamo o licenciamiento tecnológico con el investigador a través del chat o correo externo.
                <?php else: ?>
                    La empresa ha sido notificada. Usa el chat para coordinar la entrega final del proyecto.
                <?php endif; ?>
            </div>
        <?php elseif ($rol == 'investigador'): ?>
            <form action="workspace.php?id=<?php echo $id_proyecto; ?>" method="POST" style="margin-top: 15px; text-align: right;" onsubmit="return confirm('¿Estás seguro de finalizar el proyecto? Se notificará a la empresa.');">
                <input type="hidden" name="accion" value="finalizar">
                <button type="submit" class="btn-principal" style="background: #ef4255;">✅ Marcar Proyecto como Finalizado</button>
            </form>
        <?php endif; ?>
    </div>

    <div>
        <div class="chat-box" id="caja_chat">
            <?php if(count($mensajes) == 0): ?>
                <p style="text-align: center; color: #666; margin-top: auto; margin-bottom: auto;">No hay mensajes aún. ¡Comienza la conversación!</p>
            <?php endif; ?>

            <?php foreach($mensajes as $m): ?>
                <div class="mensaje <?php echo ($m['id_usuario'] == $usuario_id) ? 'mio' : 'otro'; ?>">
                    <b style="color: #1d70b8; font-size: 13px;"><?php echo htmlspecialchars($m['nombre_completo']); ?></b><br>
                    <span style="font-size: 15px;"><?php echo nl2br(htmlspecialchars($m['mensaje'])); ?></span>
                    
                    <?php if($m['archivo_adjunto']): ?>
                        <div style="margin-top: 10px; padding: 10px; background: rgba(0,0,0,0.05); border-radius: 4px; font-size: 13px;">
                            📎 <a href="uploads/<?php echo htmlspecialchars($m['archivo_adjunto']); ?>" target="_blank" style="color: #0c2340; font-weight: bold; text-decoration: none;">Ver / Descargar Archivo Adjunto</a>
                        </div>
                    <?php endif; ?>
                    
                    <span class="fecha-msg"><?php echo date('d/m/Y H:i', strtotime($m['fecha_envio'])); ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <form action="workspace.php?id=<?php echo $id_proyecto; ?>" method="POST" enctype="multipart/form-data" class="form-chat">
            <textarea name="mensaje" placeholder="Escribe un mensaje o reporte de avance..." style="flex: 1; padding: 10px; border-radius: 4px; border: 1px solid #ccc; resize: none;" rows="2" required></textarea>
            
            <div style="display: flex; flex-direction: column; gap: 5px;">
                <input type="file" name="archivo" id="archivo" style="display: none;" onchange="document.getElementById('file-label').innerText = '✅ Archivo cargado';">
                <label for="archivo" id="file-label" class="btn-principal" style="background: #f39c12; cursor: pointer; text-align: center; font-size: 12px; padding: 8px;">📎 Adjuntar Archivo</label>
                
                <button type="submit" class="btn-principal" style="background: #28a745; padding: 8px;">Enviar ➔</button>
            </div>
        </form>
    </div>
</div>

<script>
    var chatBox = document.getElementById("caja_chat");
    chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php require_once 'includes/footer.php'; ?>