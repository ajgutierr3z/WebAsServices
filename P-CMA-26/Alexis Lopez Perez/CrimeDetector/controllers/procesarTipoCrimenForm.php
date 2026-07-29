<?php
session_start();

require_once '../libs/conexion.php';
require_once 'TipoCrimenDAO.php';

$tipoCrimenDAO = new TipoCrimenDAO($pdo);
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === "POST" && isset($_POST['accion'])) {
    
    $accion = $_POST['accion'];
    $urlAnterior = $_SERVER['HTTP_REFERER'] ?? '../dashboard/tipos_crimen.php';
    $urlLimpia = strtok($urlAnterior, '?');

    try {
        switch ($accion) {
            case 'crear_tipo_crimen':
                $nombre = trim($_POST['nombre']);
                $gravedad = (int) $_POST['gravedad'];

                if (!empty($nombre) && $gravedad >= 1 && $gravedad <= 5) {
                    $tipoCrimenDAO->registrarTipoCrimen($nombre, $gravedad);
                    header("Location: " . $urlLimpia . "?status=tipo_creado");
                } else {
                    header("Location: " . $urlLimpia . "?error=campos_incompletos");
                }
                exit;

            case 'actualizar_tipo_crimen':
                $cve = (int) $_POST['cve_tipo_crimen'];
                $nombre = trim($_POST['nombre']);
                $gravedad = (int) $_POST['gravedad'];

                if ($cve > 0 && !empty($nombre) && $gravedad >= 1 && $gravedad <= 5) {
                    $tipoCrimenDAO->actualizarTipoCrimen($cve, $nombre, $gravedad);
                    header("Location: " . $urlLimpia . "?status=tipo_actualizado");
                } else {
                    header("Location: " . $urlLimpia . "?error=campos_incompletos");
                }
                exit;

            case 'eliminar_tipo_crimen':
                $cve = (int) ($_POST['cve_tipo_crimen'] ?? 0);

                if ($cve > 0) {
                    $tipoCrimenDAO->borrarTipoCrimen($cve);
                    header("Location: " . $urlLimpia . "?status=tipo_eliminado");
                } else {
                    header("Location: " . $urlLimpia . "?error=campos_incompletos");
                }
                exit;

            default:
                header("Location: ../dashboard/tipos_crimen.php");
                exit;
        }

    } catch (PDOException $e) {
        //print_r($e);
        header("Location: " . $urlLimpia . "?error=db");
        exit;
    }

} else {
    header("Location: ../dashboard/tipos_crimen.php");
    exit;
}