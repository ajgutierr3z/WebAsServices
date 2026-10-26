<?php
session_start();


require_once '../libs/conexion.php';
require_once 'UsuarioDAO.php';

$usuarioDAO = new UsuarioDAO($pdo);
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === "POST" && isset($_POST['accion'])) {
    
    $accion = $_POST['accion'];
    $usuarioCorreo = $_SESSION['correo'] ?? null; 

    if (!$usuarioCorreo) {
        die("No autorizado");
    }

    $urlAnterior = $_SERVER['HTTP_REFERER'] ?? '../profile.php';
    $urlLimpia = strtok($urlAnterior, '?');

    try {
        switch ($accion) {
            case 'actualizar_nombre':
                $nuevoNombre = trim($_POST['nuevoNombre']);
                if (!empty($nuevoNombre)) {
                    $usuarioDAO->actualizarNombre($usuarioCorreo, $nuevoNombre);
                    $_SESSION['nombre'] = $nuevoNombre; 
                    header("Location: " . $urlLimpia . "?status=success");
                }else{
                    header("Location: " . $urlLimpia . "?error=nombre_vacio");
                }                                
                exit;

            case 'actualizar_password':
                $pass1 = $_POST['nuevoPassword'];
                $pass2 = $_POST['nuevoPassword2'];

                if (!empty($pass1) && $pass1 === $pass2) {
                    $passHash = password_hash($pass1, PASSWORD_DEFAULT);
                    $usuarioDAO->actualizarPassword($usuarioCorreo, $passHash);
                    
                    header("Location: " . $urlLimpia . "?status=password_updated");
                } else {
                    header("Location: " . $urlLimpia . "?error=passwords_dont_match");
                }
                exit;
            case 'borrar_cuenta':
                $usuarioDAO->borrarUsuario($usuarioCorreo);                
                header("Location: ../libs/logout.php?status=usuario_eliminado");
                break;   
            case 'actualizar_foto':
                // Verificar que se haya subido un archivo sin errores
                if (isset($_FILES['fotoPerfil']) && $_FILES['fotoPerfil']['error'] === UPLOAD_ERR_OK) {
                    
                    $archivo = $_FILES['fotoPerfil'];
                    $nombreOriginal = $archivo['name'];
                    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
                    
                    // Validar extensiones permitidas
                    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array($extension, $extensionesPermitidas)) {
                        header("Location: " . $urlLimpia . "?error=formato_invalido");
                        exit;
                    }

                    // Definir carpeta de destino (relativa al controlador y para la URL)
                    $directorioDestino = "../uploads/fotosPerfil";
                    
                    // Crear el directorio si no existe
                    if (!file_exists($directorioDestino)) {
                        mkdir($directorioDestino, 0777, true);
                    }

                    // Crear un nombre único para evitar que se sobrescriban imágenes
                    // Ejemplo: foto_alexis_1716584930.jpg
                    $nombreLimpioCorreo = preg_replace('/[^a-zA-Z0-9]/', '_', $usuarioCorreo);
                    $nuevoNombreArchivo = "foto_" . $nombreLimpioCorreo . "_" . time() . "." . $extension;
                    
                    $rutaCompleta = $directorioDestino . $nuevoNombreArchivo; // Para mover el archivo física/servidor
                    $rutaRelativaBD = "uploads/fotosPerfil" . $nuevoNombreArchivo;      // Para guardar en la BD

                    // Mover el archivo del directorio temporal a la carpeta del proyecto
                    if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                        $_SESSION['foto_perfil']=$rutaRelativaBD;
                        // Actualizar la ruta en la Base de Datos
                        $usuarioDAO->actualizarFotoPerfil($usuarioCorreo, $rutaRelativaBD);
                        
                        header("Location: " . $urlLimpia . "?status=foto_actualizada");
                    } else {
                        header("Location: " . $urlLimpia . "?error=subida_fallida");
                    }
                } else {                    
                    header("Location: " . $urlLimpia);                    
                }         
                exit; 
                case 'crear_usuario':
                $nombre = trim($_POST['nombre']);
                $correo = trim($_POST['correo']);
                $pass = $_POST['password'];
                $rol = $_POST['rol'];

                if (!empty($nombre) && !empty($correo) && !empty($pass)) {
                    if ($usuarioDAO->obtenerUsuarioPorCorreo($correo)) {
                        header("Location: " . $urlLimpia . "?error=correo_registrado");
                        exit;
                    }

                    $passHash = password_hash($pass, PASSWORD_DEFAULT);
                    $usuarioDAO->registrarUsuario($nombre, $correo, $passHash, $rol);
                    
                    header("Location: " . $urlLimpia . "?status=usuario_creado");
                } else {
                    header("Location: " . $urlLimpia . "?error=campos_incompletos");
                }
                exit;

                case 'actualizar_usuario_admin':
                    $correoOriginal = $_POST['correo_original'];
                    $nombre = trim($_POST['nombre']);
                    $pass = $_POST['password'];
                    $rol = $_POST['rol'];

                    if (!empty($nombre) && !empty($correoOriginal)) {
                        // Actualizar nombre y rol
                        $usuarioDAO->actualizarNombre($correoOriginal, $nombre);
                        $usuarioDAO->actualizarRol($correoOriginal, $rol);

                        // Solo encriptar y actualizar la contraseña si el admin ingresó una nueva
                        if (!empty($pass)) {
                            $passHash = password_hash($pass, PASSWORD_DEFAULT);
                            $usuarioDAO->actualizarPassword($correoOriginal, $passHash);
                        }

                        header("Location: " . $urlLimpia . "?status=usuario_actualizado");
                    } else {
                        header("Location: " . $urlLimpia . "?error=campos_incompletos");
                    }
                    exit;

                case 'eliminar_usuario':
                    $correoEliminar = $_POST['correo'] ?? null;

                    // Evitar que un admin elimine su propia cuenta desde el panel
                    if ($correoEliminar === $usuarioCorreo) {
                        header("Location: " . $urlLimpia . "?error=auto_eliminacion");
                        exit;
                    }

                    if ($correoEliminar) {
                        $usuarioDAO->borrarUsuario($correoEliminar);
                        header("Location: " . $urlLimpia . "?status=usuario_eliminado");
                    } else {
                        header("Location: " . $urlLimpia . "?error=usuario_no_encontrado");
                    }
                    exit;       
            default:
                header("Location: ../profile.php?error=redireccion_fallida");
                exit;
        }

    } catch (PDOException $e) {
        //print_r($e);
        header("Location: " . $urlLimpia . "?error=db");
        exit;
    }
    
} 

else {
    header("Location: ../profile.php");
    exit;
}