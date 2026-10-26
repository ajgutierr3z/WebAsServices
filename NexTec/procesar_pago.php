<?php
session_start();
require_once 'config/conexion.php';

$datos = json_decode(file_get_contents('php://input'), true);

if (isset($datos['id_proyecto']) && isset($_SESSION['usuario_id'])) {
    $id_proyecto = $datos['id_proyecto'];
    $id_empresa = $_SESSION['usuario_id']; // Capturamos a la empresa que está pagando

    $stmt = $conexion->prepare("UPDATE proyectos SET estado = 'financiado', id_empresa = :id_empresa WHERE id = :id");
    
    if ($stmt->execute(['id_empresa' => $id_empresa, 'id' => $id_proyecto])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se pudo actualizar la BD']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos o sesión expirada']);
}
?>