<?php
// Si no está definida la página actual, la inicializamos
$paginaActual = $paginaActual ?? '';
?>
<aside class="sidebar-dashboard">
    <div class="sidebar-logo">
        <img src="../resources/img/CrimeDetectorLogo.png" alt="CrimeDetector Logo">
        <h2>CrimeDetector</h2>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li class="<?= $paginaActual === 'reportes' ? 'active' : '' ?>">
                <a href="reportes.php">Reportes</a>
            </li>
            <li class="<?= $paginaActual === 'colonias' ? 'active' : '' ?>">
                <a href="colonias.php">Colonias</a>
            </li>
            <li class="<?= $paginaActual === 'usuarios' ? 'active' : '' ?>">
                <a href="usuarios.php">Usuarios</a>
            </li>
            <li class="<?= $paginaActual === 'crimenes' ? 'active' : '' ?>">
                <a href="crimenes.php">Crímenes</a>
            </li>            
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="../index.php" class="btn-salir">Salir de Administración</a>
        <a href="../libs/logout.php" class="btn-logout">Cerrar sesión</a>
    </div>
</aside>