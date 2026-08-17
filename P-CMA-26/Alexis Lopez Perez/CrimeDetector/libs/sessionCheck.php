<?php 
    if (!isset($_SESSION['nombre'])) {            
        echo "<script>window.location = 'libs/logout.php?error=no_sesion'</script>";
        session_destroy();
    }          