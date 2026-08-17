<?php
    session_start();

    require_once "conexion.php";
    require_once "config.php";

    if ($_SERVER['REQUEST_METHOD'] === "POST" && $_POST['loginPeticion'] === 'login') {
        //CODIGO PARA INICIAR SESION CON CORREO ELECTRONICO
        $email = trim($_POST['email']);        
        $password = trim($_POST['password']);

        if (empty($email) || empty($password)) {
            header("Location: ../index.php?error=campos_vacios");
            exit();
        }
        try {
            $usuario = seleccionarUsuarioConCorreo($pdo, $email);

            if ($usuario) {
                if (password_verify($password, $usuario['password'])) {
                    $_SESSION['nombre'] = $usuario['nombre'];
                    $_SESSION['correo'] = $usuario['correo'];
                    $_SESSION['foto_perfil'] = $usuario['foto_perfil'];
                    $_SESSION['rol'] = $usuario['rol'];

                    header('Location: ../mapa.php');
                    exit();
                }else {
                    header('Location: ../index.php?error=password_incorrecta');
                }
            }else {
                header("Location: ../index.php?error=correo_no_registrado");
                exit();
            }
        } catch (PDOException $e) {
            header("Location: ../index.php?error=error_servidor");
            exit();
        }
    }elseif ($_SERVER['REQUEST_METHOD'] === "POST" && $_POST['loginPeticion'] === 'sign up') {
        //CODIGO PARA REGISTRARSE CON CORREO ELECTRONICO
        $username        = trim($_POST['username']);
        $email           = trim($_POST['email']);
        $password        = trim($_POST['password']);
        $passwordConfirm = trim($_POST['passwordConfirm']);
    
        $loginMode       = $_POST['loginMode'] ?? 'classic';
        $loginPeticion   = $_POST['loginPeticion'] ?? '';

        if (empty($username) || empty($email) || empty($password) || empty($passwordConfirm)) {
            header("Location: ../index.php?error=campos_vacios");
            exit();
        }

        if ($password !== $passwordConfirm) {
            header("Location: ../index.php?error=password_no_coincide");
            exit();
        }

        try {
            $stmtCheck = verificarCorreoEnBase($pdo, $email);

            if ($stmtCheck) {
                header("Location: ../index.php?error=correo_duplicado");
                exit();
            }   
            
            // Foto de perfil por defecto si es un usuario nuevo
            $fotoDefecto = DEFAULT_PROFILE_IMAGE; 

            $stmtInsert = crearUsuario($pdo, $username, $email, $password, $fotoDefecto);                        

            if ($stmtInsert) {
                //REDIRECCIONA SI EL REGISTRO FUE EXITOSO
                $_SESSION['nombre'] = $username;
                $_SESSION['correo'] = $email;
                $_SESSION['foto_perfil'] = DEFAULT_PROFILE_IMAGE;
                $_SESSION['rol'] = "cliente";

                header("Location: ../mapa.php?registro=exito");
                exit();
            } else {
                header("Location: ../index.php?error=error_registro");
                exit();
            }

        } catch (PDOException $e) {            
            header("Location: ../index.php?error=error_servidor.");
            exit();
        }
    }elseif ($_SERVER['REQUEST_METHOD'] === "GET") {
        //CODIGO PARA INICIAR SESION CON MLH
        try {
            $correo = $_SESSION['correo'];

            $usuarioExiste = verificarCorreoEnBase($pdo, $correo);

            if ($usuarioExiste) {
                $usuario = seleccionarUsuarioConCorreo($pdo, $correo);

                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['foto_perfil'] = $usuario['foto_perfil'];
                $_SESSION['rol'] = $usuario['rol'];

                header("Location: ../mapa.php");
                exit();
            }else {
                $nombre = $_SESSION['nombre'];                
                $foto = DEFAULT_PROFILE_IMAGE;

                $_SESSION['foto_perfil'] = $foto;
                $_SESSION['rol'] = "cliente";

                crearUsuario($pdo, $nombre, $correo, null, $foto);
                
            }

            
        } catch (PDOException $e) {                        
            echo "<pre>";
            print_r($e);
            echo "</pre>";
            echo '<a href="../index.php?error=error_servidor"></a>';
            exit();
        }    

        header('Location: ../mapa.php');
        exit();
    }
    else {
        header("Location: ../index.php");
        exit();
    }

    function verificarCorreoEnBase($pdo, $email){
        $queryCheck = "SELECT correo FROM usuarios WHERE correo = :correo LIMIT 1";
        $stmtCheck = $pdo->prepare($queryCheck);
        $stmtCheck->bindParam(':correo', $email, PDO::PARAM_STR);
        $stmtCheck->execute();
        return $stmtCheck->fetch();
    }

    function crearUsuario($pdo, $username, $email, $password, $foto){
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $queryInsert = "INSERT INTO usuarios (nombre, correo, password, foto_perfil) 
                        VALUES (:nombre, :correo, :contrasena, :foto_perfil)";
            
        $stmtInsert = $pdo->prepare($queryInsert);                

        $stmtInsert->bindParam(':nombre', $username, PDO::PARAM_STR);
        $stmtInsert->bindParam(':correo', $email, PDO::PARAM_STR);
        $stmtInsert->bindParam(':contrasena', $passwordHash, PDO::PARAM_STR);
        $stmtInsert->bindParam(':foto_perfil', $foto, PDO::PARAM_STR);

        //DEVUELVE SI LA CONSULTA SE REALIZO EXITOSAMENTE
        return $stmtInsert->execute();
    }

    function seleccionarUsuarioConCorreo($pdo, $email){
        $query = "SELECT nombre, correo, password, foto_perfil, rol FROM usuarios WHERE correo = :correo LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":correo", $email, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
?>