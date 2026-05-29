<!-- // cambio! -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$logueado = isset($_SESSION["usuario_id"]);
$esAdmin = $logueado && $_SESSION["rol"] === "admin";
?>
<!-- // -->
<nav>
    <div class="navbar">

        <!-- IZQUIERDA -->
        <div class="navbar-left">

            <a href="index.php" class="logo">
                <img src="assets/logowebdev.png" alt="WebDevAcademy logo" class="logo-img">

                <span>WebDevAcademy</span>
            </a>

        </div>

        <!-- CENTRO -->
        <form method="GET" class="nav-search">

            <input type="text" name="buscar" placeholder="Buscar cursos...">

            <select name="precio">

                <option value="">Todos</option>

                <option value="gratis" <?php if (($_GET['precio'] ?? '') == 'gratis')
                    echo 'selected'; ?>>

                    Gratis

                </option>

                <option value="pago" <?php if (($_GET['precio'] ?? '') == 'pago')
                    echo 'selected'; ?>>

                    De pago

                </option>

            </select>

            <select name="orden">

                <option value="">Ordenar</option>

                <option value="rating" <?php if (($_GET['orden'] ?? '') == 'rating')
                    echo 'selected'; ?>>

                    ⭐ Top

                </option>

                <option value="inscritos" <?php if (($_GET['orden'] ?? '') == 'inscritos')
                    echo 'selected'; ?>>

                    🔥 Popular

                </option>

                <option value="recientes" <?php if (($_GET['orden'] ?? '') == 'recientes')
                    echo 'selected'; ?>>

                    🆕 Nuevo

                </option>

            </select>

            <button type="submit" class="btn btn-primary">
                Buscar
            </button>

        </form>
        <!-- // cambio todo el navbar right -->
        <!-- DERECHA -->

        <div class="navbar-right">

            <?php if ($logueado): ?>

                <?php if ($esAdmin): ?>
                    <a href="../admin/panel.php" class="nav-link">
                        Admin
                    </a>
                <?php endif; ?>

                <a href="perfil.php" class="nav-link">
                    Perfil
                </a>

                <a href="misCursos.php" class="nav-link">
                    Mis cursos
                </a>

                <a href="logout.php" class="btn btn-primary">
                    Salir
                </a>

            <?php else: ?>

                <a href="login.php" class="nav-link">
                    Iniciar sesión
                </a>

                <a href="registro.php" class="btn btn-primary">
                    Registrarse
                </a>

            <?php endif; ?>

        </div>
        <button class="menu-toggle">☰</button>

    </div> <!-- navbar -->
</nav>