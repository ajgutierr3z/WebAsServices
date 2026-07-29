<?php
    session_start();

    require_once "libs/config.php";  
    
    if (isset($_SESSION['correo'])) {
        header("Location: mapa.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="resources/img/CrimeDetectorLogo.png" type="image/x-icon">
    <link rel="stylesheet" href="styles/normalize.css">
    <link rel="stylesheet" href="styles/variables.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/generico.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <title>Bienvenido | CrimeDetector</title>
</head>
<body>        
<!-- POPOVER DE MENSAJES DE ERROR O STATUS -->
    <?php if (isset($_GET['error']) || isset($_GET['status'])): ?>
    <div id="popoverMensajes" popover>
        <span>
        <?php
        $estiloMensaje="";
        if (isset($_GET['error'])) {  
            $estiloMensaje="'mensajeError'";
            switch ($_GET['error']) {
                case 'correo_no_registrado':
                    echo "El correo electrónico no está registrado.";
                    break;
                case 'password_incorrecta':
                    echo "La contraseña es incorrecta.";
                    break;
                case 'campos_vacios':
                    echo "Por favor, llena todos los campos.";
                    break;
                case 'correo_duplicado':
                    echo "Este correo electrónico ya se encuentra registrado.";
                    break;
                case 'error_servidor':
                    echo "Hubo un problema con el servidor. Inténtalo más tarde.";
                    break;
                case 'no_sesion':
                    echo "Nesecita iniciar sesión para acceder al mapa.";
                    break;
                case 'no_permission':
                    echo "No tiene autorizado entrar a las opciones de administrtador";
                    break;                    
                default:
                    echo "Error desconocido";
                    break;
            }   
        } 
        if (isset($_GET['status'])) {   
            $estiloMensaje="'mensajeStatus'";
            switch ($_GET['status']) {
                case 'usuario_eliminado':
                    echo "El usuario se ha eliminado con exito.";
                    break;                
                default:
                    echo "Bienvenido";
                    break;
            }   
        }   
        ?>
        </span>
        <button id="cerrarPopoverMensajes" commandFor="popoverMensajes" command="hide-popover">X</button>
    </div>
    <?php endif; ?>
    <?php if (isset($_GET['error']) || isset($_GET['status'])):?>
    <script>
        const popoverMensajes = document.getElementById('popoverMensajes');
        const cerrarPopoverMensajes = document.getElementById('cerrarPopoverMensajes');

        popoverMensajes.showPopover();
        popoverMensajes.classList.add(<?php echo $estiloMensaje; ?>);
        cerrarPopoverMensajes.classList.add(<?php echo $estiloMensaje; ?>);
    </script>
    <?php endif;?>    
<!------------------------------------------->
    <dialog id="dialogRegistro">              
        <button class="exitDialog" onclick="cerrarDialogoRegistro()" title="Cerrar">✕</button>                    
        
        <h2 id="tituloModal">Iniciar Sesión</h2>
        
        <div class="cajaBotonesVertical">                
            <button class="btn-oauth btn-mlh" onclick="iniciarSesionMLH()">
                <span class="icon-oauth">🎓</span> Continuar con MLH
            </button>

            <button id="btnGoogleCustom" class="btn-oauth btn-google" onclick="activarLoginGoogle()">
                <svg class="icon-svg" viewBox="0 0 24 24" width="18" height="18">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg> 
                Continuar con Google
            </button>

            <button class="btn-oauth btn-email" onclick="mostrarFormularioRegistro()">
                <span class="icon-oauth">✉️</span> Usar correo electrónico
            </button>

            <button id="cambioDialogRegistro" class="btn-link-toggle"></button>
        </div> 

        <!-- Contenedor oculto para Google API -->
        <div id="buttonDivContainer" style="display: none;"></div>

        <!-- Formulario Tradicional --> 
        <form action="libs/validar.php" method="POST" class="form-dialog">
            <div class="field-group" id="groupUsername">
                <label for="username" id="labelUsername">Nombre de usuario</label>
                <input id="username" name="username" type="text" placeholder="Ej. alexis_dev" required>
            </div>
            
            <div class="field-group" id="groupEmail">
                <label for="email" id="labelEmail">Correo Electrónico</label>
                <input id="email" name="email" type="email" placeholder="correo@ejemplo.com" required>        
            </div>

            <div class="field-group">
                <label for="password">Contraseña</label>
                <input id="password" name="password" type="password" placeholder="••••••••" required>
            </div>

            <div class="field-group" id="groupPasswordConfirm">
                <label for="passwordConfirm" id="labelPasswordConfirm">Confirmar Contraseña</label>
                <input id="passwordConfirm" name="passwordConfirm" type="password" placeholder="••••••••" required>
            </div>

            <input id="loginMode" name="loginMode" type="hidden" value="classic">
            <input id="loginPeticion" name="loginPeticion" type="hidden" value="">
            <span id="mensajeErrorLoginClassic" class="error-msg">Las contraseñas no coinciden</span>
            
            <input type="submit" class="btn-submit" value="Entrar">
        </form>                      
    </dialog>
    <?php require_once "templates/header.php"; ?>
    <div class="contenedor">        
        <main class="hero-container">            
            <div class="hero-header">
                <span class="badge-tag">Plataforma de Seguridad Ciudadana</span>
                <h1 class="hero-title">Bienvenido a <span>CrimeDetector</span></h1>
                <p class="hero-subtitle">
                    Mapea, reporta y consulta incidentes delictivos en tu comunidad de manera precisa, transparente e interactiva.
                </p>
            </div>
            
            <section class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📍</div>
                    <h3>Mapeo Preciso</h3>
                    <p>Registra e identifica reportes georreferenciados sobre el mapa para conocer los puntos de riesgo en tiempo real.</p>
                </div>                

                <div class="feature-card">
                    <div class="feature-icon">🤝</div>
                    <h3>Red Colaborativa</h3>
                    <p>Contribuye activamente a la seguridad de tu entorno compartiendo información veraz para proteger a otros.</p>
                </div>
            </section>
            
            <section class="cta-section">
                <p class="cta-text">Inicia sesión para gestionar tus reportes o regístrate para comenzar a colaborar.</p>
                <div class="cajaBotones">
                    <button class="btn-primario" onclick="mostrarDialogoRegistro('inicio')">Iniciar Sesión</button>
                    <button class="btn-secundario" onclick="mostrarDialogoRegistro('Crear Cuenta')">Registrarse</button>
                </div>
            </section>
        </main>                
    </div>
    <script src="scripts/index.js"></script>
    <script>
        function iniciarSesionMLH() {
            const clientId = '<?php echo CLIENT_ID_MLH; ?>';  //Recordar quitar esto
            
            const redirectUri = encodeURIComponent('<?php echo REDIRECT_URI; ?>'); 
            
            const mlhAuthUrl = `https://www.mlh.com/oauth/authorize?client_id=${clientId}&redirect_uri=${redirectUri}&scope=user%3Aread%3Aprofile+user%3Aread%3Aemail+public&response_type=code`;
            
            window.location.href = mlhAuthUrl;
        }
    </script>
    <script>
        window.onload = function () {
            google.accounts.id.initialize({
                client_id: "<?php echo CLIENT_ID_GOOGLE ?>", 
                callback: handleCredentialResponse
            });

            google.accounts.id.renderButton(
                document.getElementById("buttonDivContainer"),
                { theme: "outline", size: "large" } 
            );
        }

        function activarLoginGoogle() {
            const googleButton = document.getElementById("buttonDivContainer").querySelector('[role="button"]');
            
            if (googleButton) {
                googleButton.click();
            } else {
                google.accounts.id.prompt(); 
            }
        }

        function handleCredentialResponse(response) {
            fetch('libs/callbackGoogle.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token: response.credential })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    window.location.href = 'libs/validar.php'; 
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(err => console.error("Error:", err));            
        }
    </script>        
</body>
</html>