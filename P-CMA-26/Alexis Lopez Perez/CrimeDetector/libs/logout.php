<?php
    session_start();
    session_unset();
    session_destroy();

    if ($_SERVER['REQUEST_METHOD'] === "GET" && $_GET['error'] === "no_sesion") {
        header("Location: ../index.php?error=no_sesion");
        exit();    
    }
    else if ($_SERVER['REQUEST_METHOD'] === "GET" && $_GET['status'] === "usuario_eliminado") {
        header("Location: ../index.php?status=usuario_eliminado");
        exit();    
    }
    else if ($_SERVER['REQUEST_METHOD'] === "GET" && $_GET['status'] === "no_permission") {
        header("Location: ../index.php?status=no_permission");
        exit();    
    }    

    header("Location: ../index.php");
    exit();
?>