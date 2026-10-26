<?php
$base_url = '/nextec'; 

$notificaciones_count = 0;
$rev = 0;
$ret = 0;

if (isset($_SESSION['usuario_id']) && isset($conexion)) {
    $uid = $_SESSION['usuario_id'];
    $rol_notif = strtolower($_SESSION['rol']);

    if ($rol_notif == 'admin' || $rol_notif == 'administrador') {
        $stmt_rev = $conexion->query("SELECT COUNT(*) as c FROM proyectos WHERE estado = 'revision'");
        $rev = $stmt_rev->fetch(PDO::FETCH_ASSOC)['c'] ?? 0;
        
        $stmt_ret = $conexion->query("SELECT COUNT(*) as c FROM proyectos WHERE estado_pago = 'solicitado'");
        $ret = $stmt_ret->fetch(PDO::FETCH_ASSOC)['c'] ?? 0;
        
        $notificaciones_count = $rev + $ret;
    } else {
        $stmt_proy = $conexion->prepare("SELECT id FROM proyectos WHERE id_investigador = :uid OR id_empresa = :uid");
        $stmt_proy->execute(['uid' => $uid]);
        $mis_proy_ids = $stmt_proy->fetchAll(PDO::FETCH_COLUMN);

        if (count($mis_proy_ids) > 0) {
            $in = str_repeat('?,', count($mis_proy_ids) - 1) . '?';
            $params = $mis_proy_ids;
            $params[] = $uid; 
            
            $stmt_msg = $conexion->prepare("SELECT COUNT(*) as c FROM seguimiento_proyectos WHERE id_proyecto IN ($in) AND id_usuario != ? AND leido = 0");
            $stmt_msg->execute($params);
            $notificaciones_count = $stmt_msg->fetch(PDO::FETCH_ASSOC)['c'] ?? 0;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexTec - Universidad Tecnológica de Tabasco</title>
    
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/estilos.css">
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; }
        .navbar { background-color: #0c2340; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .navbar a { color: white; text-decoration: none; font-weight: bold; font-size: 18px; }
        body.fondo-invitado { background-color: #f4f7f6; }
        .tarjeta { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin: 0 auto; text-align: center; }
        .btn-principal { padding: 10px; background-color: #0c2340; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; text-decoration: none; display: inline-block; transition: background-color 0.3s;}
        .btn-principal:hover { background-color: #1d70b8; }
        
        /* Nuevos estilos para el Menú Desplegable de Notificaciones */
        .notif-dropdown { display: none; position: absolute; right: 0; top: 35px; background: white; min-width: 280px; box-shadow: 0 8px 16px rgba(0,0,0,0.2); border-radius: 8px; z-index: 1000; padding: 15px; color: #333; }
        .notif-dropdown h4 { margin: 0 0 10px 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #0c2340; font-size: 16px; text-align: left; }
        .notif-item { display: block; padding: 10px 0; color: #1d70b8 !important; font-size: 14px !important; text-decoration: none; border-bottom: 1px solid #f9f9f9; text-align: left;}
        .notif-item:hover { background-color: #f4f7f6; padding-left: 5px; transition: 0.3s; border-radius: 4px; }
        .notif-item:last-child { border-bottom: none; }
    </style>
</head>
<?php 
    $clase_fondo = isset($_SESSION['rol']) ? strtolower($_SESSION['rol']) : 'invitado'; 
?>
<body class="fondo-<?php echo $clase_fondo; ?>">

<?php if (isset($_SESSION['usuario_id'])): ?>
    <div class="navbar">
        <div>
            <a href="<?php echo $base_url; ?>/dashboard.php"> NexTec</a>
        </div>
        
        <div style="display: flex; align-items: center; gap: 25px;">
            
            <div style="position: relative;">
                <a href="#" onclick="toggleNotif(event)" style="position: relative; text-decoration: none; font-size: 22px; transition: transform 0.2s;" title="Notificaciones">
                    🔔
                    <?php if($notificaciones_count > 0): ?>
                        <span style="position: absolute; top: -5px; right: -8px; background-color: #ef4255; color: white; border-radius: 50%; padding: 2px 6px; font-size: 11px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                            <?php echo $notificaciones_count; ?>
                        </span>
                    <?php endif; ?>
                </a>
                
                <!-- El Cuadro del Menú -->
                <div id="menu-notificaciones" class="notif-dropdown">
                    <h4>Tus Notificaciones</h4>
                    
                    <?php if($rol_notif == 'admin' || $rol_notif == 'administrador'): ?>
                        <?php if($rev > 0): ?>
                            <a href="<?php echo $base_url; ?>/admin/validar_proyectos.php" class="notif-item">📋 Tienes <b><?php echo $rev; ?></b> proyecto(s) por revisar.</a>
                        <?php endif; ?>
                        <?php if($ret > 0): ?>
                            <a href="<?php echo $base_url; ?>/dashboard.php" class="notif-item">💸 Hay <b><?php echo $ret; ?></b> solicitud(es) de retiro de fondos.</a>
                        <?php endif; ?>
                        <?php if($notificaciones_count == 0): ?>
                            <p style="font-size: 13px; color: #888; margin: 0; text-align: center;">No hay notificaciones nuevas.</p>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <?php if($notificaciones_count > 0): ?>
                            <a href="<?php echo $base_url; ?>/dashboard.php" class="notif-item">💬 Tienes <b><?php echo $notificaciones_count; ?></b> mensaje(s) sin leer.<br><span style="font-size: 11px; color:#666;">Revisa tu Sala de Trabajo.</span></a>
                        <?php else: ?>
                            <p style="font-size: 13px; color: #888; margin: 0; text-align: center;">No tienes mensajes nuevos.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Avatar dinámico -->
            <?php
                $foto_header = (isset($_SESSION['foto_perfil']) && !empty($_SESSION['foto_perfil'])) 
                               ? $base_url . '/uploads/perfiles/' . $_SESSION['foto_perfil'] 
                               : 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
            ?>
            <a href="<?php echo $base_url; ?>/perfil.php" title="Configurar Perfil" style="display: inline-block;">
                <img src="<?php echo $foto_header; ?>" alt="Perfil" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
            </a>
        </div>
    </div>

    <!-- Script para abrir y cerrar el menú -->
    <script>
        function toggleNotif(event) {
            event.preventDefault(); // Evita que la página brinque hacia arriba
            const menu = document.getElementById('menu-notificaciones');
            if (menu.style.display === 'block') {
                menu.style.display = 'none';
            } else {
                menu.style.display = 'block';
            }
        }

        document.addEventListener('click', function(event) {
            const menu = document.getElementById('menu-notificaciones');
            const campana = menu.previousElementSibling;
            if (!menu.contains(event.target) && !campana.contains(event.target)) {
                menu.style.display = 'none';
            }
        });
    </script>
<?php endif; ?>

<main style="padding: 20px; min-height: calc(100vh - 160px);">