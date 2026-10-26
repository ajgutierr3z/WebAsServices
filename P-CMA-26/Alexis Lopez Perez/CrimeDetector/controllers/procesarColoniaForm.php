<?php
session_start();

require_once '../libs/conexion.php';
require_once 'ColoniaDAO.php';

$coloniaDAO = new ColoniaDAO($pdo);
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === "POST" && isset($_POST['accion'])) {
    
    $accion = $_POST['accion'];
    $urlAnterior = $_SERVER['HTTP_REFERER'] ?? '../dashboard/colonias.php';
    $urlLimpia = strtok($urlAnterior, '?');

    try {
        switch ($accion) {
            case 'crear_colonia':
                $cp = (int) $_POST['codigo_postal'];
                $nombre = trim($_POST['nombre']);
                $latitud = (float) $_POST['latitud'];
                $longitud = (float) $_POST['longitud'];

                if ($cp > 0 && !empty($nombre)) {
                    // Validar si el Código Postal ya existe
                    if ($coloniaDAO->obtenerPorCodigoPostal($cp)) {
                        header("Location: " . $urlLimpia . "?error=cp_registrado");
                        exit;
                    }

                    $coloniaDAO->registrarColonia($cp, $nombre, $latitud, $longitud);
                    header("Location: " . $urlLimpia . "?status=colonia_creada");
                } else {
                    header("Location: " . $urlLimpia . "?error=campos_incompletos");
                }
                exit;

            case 'actualizar_colonia':
                $cp = (int) $_POST['codigo_postal'];
                $nombre = trim($_POST['nombre']);
                $latitud = (float) $_POST['latitud'];
                $longitud = (float) $_POST['longitud'];

                if ($cp > 0 && !empty($nombre)) {
                    $coloniaDAO->actualizarColonia($cp, $nombre, $latitud, $longitud);
                    header("Location: " . $urlLimpia . "?status=colonia_actualizada");
                } else {
                    header("Location: " . $urlLimpia . "?error=campos_incompletos");
                }
                exit;

            case 'eliminar_colonia':
                $cp = (int) ($_POST['codigo_postal'] ?? 0);

                if ($cp > 0) {
                    $coloniaDAO->borrarColonia($cp);
                    header("Location: " . $urlLimpia . "?status=colonia_eliminada");
                } else {
                    header("Location: " . $urlLimpia . "?error=campos_incompletos");
                }
                exit;

            default:
                header("Location: ../dashboard/colonias.php");
                exit;
        }

    } catch (PDOException $e) {
        //print_r($e);
        header("Location: " . $urlLimpia . "?error=db");
        exit;
    }

} else {
    header("Location: ../dashboard/colonias.php");
    exit;
}