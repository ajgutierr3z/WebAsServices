<header>
    <img src="<?php if (isset($dashboard)) { echo "../"; } ?>resources/img/CrimeDetectorLogo.png" alt="Logo" id="logo_header">                        
    <h1 id="tituloLogo">CrimeDetector</h1>      
    
    <?php if (isset($_SESSION['nombre'])):?>
    <button id="botonMenuPerfil" commandFor="menuPerfil" command="toggle-popover">
        <img src="<?php if (isset($dashboard)) { echo "../"; } ?><?php echo $_SESSION['foto_perfil']; ?>" alt="foto_perfil" id="foto_perfil_header">
        <span id="nombreUsuario"><?php echo $_SESSION['nombre']; ?></span>
        <span class="flecha-menu">▾</span>
    </button>      
    
    <div id="menuPerfil" popover>
        <!-- Encabezado sutil dentro del popover -->
        <div class="menu-header">
            <span class="menu-usuario-nombre"><?php echo $_SESSION['nombre']; ?></span>
            <span class="menu-usuario-rol"><?php echo ucfirst($_SESSION['rol'] ?? 'Usuario'); ?></span>
        </div>
        
        <div class="cajaBotonesPerfil">
            <button id="configPerfilBtn" class="btn-menu-item">
                <span class="icon-menu">👤</span> Mi cuenta
            </button>  
            
            <?php if ($_SESSION['rol'] === "administrador"): ?>
            <button id="dashboardBtn" class="btn-menu-item">
                <span class="icon-menu">📊</span> Dashboard
            </button>
            <?php endif; ?>
            
            <div class="menu-divider"></div>

            <button id="logoutBtn" class="btn-menu-item btn-logout">
                <span class="icon-menu">🚪</span> Cerrar sesión
            </button>
        </div>                
    </div>
    <?php endif; ?>
</header>

<style>    
    h1, #nombreUsuario, span, button {
        font-family: Arial, Helvetica, sans-serif;
    }

    h1, #logo_header {
        cursor: pointer;
    }

    header {
        display: flex;
        background-color: var(--colorRojoHeader, #dc2626);
        align-items: center;
        padding: 0 1rem;        
        position: relative;
    }

    h1 {
        color: white;
        font-size: 2rem;
        font-weight: 700;
        margin-left: 0.5rem;
    }

    img#logo_header{
        width: var(--heightTituloHeader);
        height: var(--heightTituloHeader);
        background-color: white;
        margin: var(--marginTituloHeader);

        object-fit: cover; 
        border-radius: 50%;
    }

    img#foto_perfil_header{
        width: 36px;
        height: 36px;
        object-fit: cover; 
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.8);
    }

    /* BOTÓN TRIGGER PERFIL */
    #botonMenuPerfil {    
        display: flex;
        align-items: center;    
        gap: 8px;       
        position: absolute;
        right: 1.5rem;
        background-color: transparent;
        border: none;
        padding: 6px 12px;
        border-radius: 20px;
        transition: background-color 0.2s ease;
        anchor-name: --btnPerfil;
    }

    #botonMenuPerfil:hover {
        background-color: rgba(0, 0, 0, 0.15);
        cursor: pointer;    
    }

    #nombreUsuario {
        color: white;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .flecha-menu {
        color: white;
        font-size: 0.8rem;
    }

    /* POPOVER MENÚ DESPLEGABLE */
    #menuPerfil {        
        position-anchor: --btnPerfil;
        position-area: bottom span-left;
        margin-top: 8px;
        
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 8px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        min-width: 190px;
    }

    /* Encabezado del menú */
    .menu-header {
        display: flex;
        flex-direction: column;
        padding: 8px 12px 10px 12px;
        border-bottom: 1px solid #f3f4f6;
        margin-bottom: 6px;
    }

    .menu-usuario-nombre {
        font-weight: 700;
        font-size: 0.9rem;
        color: #111827;
    }

    .menu-usuario-rol {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: capitalize;
    }

    .cajaBotonesPerfil {        
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .menu-divider {
        height: 1px;
        background-color: #f3f4f6;
        margin: 4px 0;
    }

    /* ÍTEMS DE BOTONES */
    .btn-menu-item {        
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 9px 12px;
        border: none;
        background: transparent;
        color: #374151;
        font-size: 0.88rem;
        font-weight: 600;
        border-radius: 6px;
        text-align: left;
        cursor: pointer;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .icon-menu {
        font-size: 1rem;
    }

    .btn-menu-item:hover {
        background-color: #f3f4f6;
        color: #111827;
    }

    /* Botón específico de Cerrar Sesión */
    .btn-logout:hover {
        background-color: #fee2e2;
        color: #dc2626;
    }
</style>

<?php if (isset($_SESSION['rol'])){
    if($_SESSION['rol'] === "administrador"): ?>
<script>
    document.getElementById("dashboardBtn").addEventListener("click", () => {
        window.location.href="<?php if (isset($dashboard)) { echo "../"; } ?>dashboard/reportes.php";
    });
</script>
<?php endif;} ?>
<?php if(isset($_SESSION['nombre'])): ?>
<script>
    document.getElementById("configPerfilBtn").addEventListener("click", () => {
        window.location.href="<?php if (isset($dashboard)) { echo "../"; } ?>profile.php";
    });    

    document.getElementById("logoutBtn").addEventListener("click", () => {
        window.location.href="<?php if (isset($dashboard)) { echo "../"; } ?>libs/logout.php";
    });

    document.getElementById("tituloLogo").addEventListener("click", () => {
        window.location.href="<?php if (isset($dashboard)) { echo "../"; } ?>index.php";
    });

    document.getElementById("logo_header").addEventListener("click", () => {
        window.location.href="<?php if (isset($dashboard)) { echo "../"; } ?>index.php";
    });
</script>
<?php endif; ?>