
<?php

// Foto desde sesión 
$foto = isset($_SESSION["foto"]) && $_SESSION["foto"]
    ? "../public/uploads/perfiles/" . $_SESSION["foto"]
    : "https://placekitten.com/640/360"; ?>

<nav class="admin-header">

    <div class="admin-user">
        <img src="<?php echo $foto; ?>" alt="Imagen Usuario">
        <div>
            <strong><?php echo $_SESSION["nombre"]; ?></strong>
            <span>Administrador</span>
        </div>
    </div>

    <!-- BOTÓN -->
    <button class="admin-toggle">☰</button>

    <div class="admin-menu">

        <a href="../admin/panel.php" class="nav-link">Panel</a>
        <a href="../public/index.php" class="nav-link">Web</a>
        <a href="../public/logout.php" class="btn btn-primary">Salir</a>

    </div>

</nav>