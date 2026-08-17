<?php
session_start();

require_once '../libs/conexion.php';
require_once 'ReporteDAO.php';

$reporteDAO = new ReporteDAO($pdo);
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === "POST" && isset($_POST['accion'])) {
    
    $accion = $_POST['accion'];
    $urlAnterior = $_SERVER['HTTP_REFERER'] ?? '../dashboard/reportes.php';
    $urlLimpia = strtok($urlAnterior, '?');

    try {
        switch ($accion) {
            case 'crear_reporte':
                // Si 'usuario' viene en POST se usa, de lo contrario se toma de la sesión activa
                $usuario = trim($_POST['usuario'] ?? $_SESSION['usuario'] ?? '');
                $colonia = (int) $_POST['colonia'];
                $direccion = trim($_POST['direccion']);
                $lat = (float) $_POST['latitud'];
                $lng = (float) $_POST['longitud'];
                $descripcion = trim($_POST['descripcion']);
                
                // Array con los IDs de tipos de crimen marcados en los checkboxes
                $crimenes = $_POST['crimenes'] ?? [];

                if (!empty($usuario) && $colonia > 0 && !empty($direccion)) {
                    // Pasamos $crimenes como nuevo argumento
                    $reporteDAO->registrarReporte($usuario, $colonia, $direccion, $lat, $lng, $descripcion, $crimenes);
                    header("Location: " . $urlLimpia . "?status=reporte_creado");
                } else {
                    header("Location: " . $urlLimpia . "?error=campos_incompletos");                    
                }
                exit;

            case 'actualizar_reporte':
                $folio = (int) $_POST['folio'];
                $usuario = trim($_POST['usuario']);
                $colonia = (int) $_POST['colonia'];
                $direccion = trim($_POST['direccion']);
                $lat = (float) $_POST['latitud'];
                $lng = (float) $_POST['longitud'];
                $descripcion = trim($_POST['descripcion']);

                if ($folio > 0 && !empty($usuario) && $colonia > 0) {
                    $reporteDAO->actualizarReporte($folio, $usuario, $colonia, $direccion, $lat, $lng, $descripcion);
                    header("Location: " . $urlLimpia . "?status=reporte_actualizado");
                } else {
                    header("Location: " . $urlLimpia . "?error=campos_incompletos");
                }
                exit;

            case 'eliminar_reporte':
                $folio = (int) ($_POST['folio'] ?? 0);

                if ($folio > 0) {
                    $reporteDAO->borrarReporte($folio);
                    header("Location: " . $urlLimpia . "?status=reporte_eliminado");
                } else {
                    header("Location: " . $urlLimpia . "?error=campos_incompletos");
                }
                exit;

            default:
                header("Location: ../dashboard/reportes.php");
                exit;
        }

    } catch (PDOException $e) {
        //print_r($e);
        header("Location: " . $urlLimpia . "?error=db");        
        exit;
    }

} else {
    header("Location: ../dashboard/reportes.php");
    exit;
}