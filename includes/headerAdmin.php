<!-- HEADER ADMIN -->

<?php

// Foto desde sesión 
$foto = isset($_SESSION["foto"]) && $_SESSION["foto"]
    ? "../public/uploads/perfiles/" . $_SESSION["foto"]
    : "https://placekitten.com/640/360"; ?>

            <nav class="admin-header">

                <div class="admin-user">
                    <img src="<?php echo $foto; ?>">
                    <div>
                        <strong><?php echo $_SESSION["nombre"]; ?></strong>
                        <span>Administrador</span>
                    </div>
                </div>

                <div class="admin-actions">
                    <a href="../public/index.php" class="btn btn-soft">Ver web</a>
                    <a href="../public/logout.php" class="btn btn-primary">Salir</a>
                </div>

</nav>