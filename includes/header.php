<div class="navbar">

    <!-- IZQUIERDA -->
    <div class="navbar-left">
        <a href="/webdevacademy/public/index.php" class="logo">
            🚀 WebDev Academy
        </a>
    </div>

    <!-- CENTRO (BUSCADOR PRO) -->
    <form method="GET" class="nav-search">

        <input type="text" name="buscar" placeholder="Buscar cursos..."
            value="<?php echo $_GET['buscar'] ?? ''; ?>">

        <select name="precio">
            <option value="">Todos</option>
            <option value="gratis" <?php if (($_GET['precio'] ?? '') == 'gratis') echo 'selected'; ?>>
                Gratis
            </option>
            <option value="pago" <?php if (($_GET['precio'] ?? '') == 'pago') echo 'selected'; ?>>
                De pago
            </option>
        </select>

        <select name="orden">
            <option value="">Ordenar</option>

            <option value="rating" <?php if (($_GET['orden'] ?? '') == 'rating') echo 'selected'; ?>>
                ⭐ Top
            </option>

            <option value="inscritos" <?php if (($_GET['orden'] ?? '') == 'inscritos') echo 'selected'; ?>>
                🔥 Popular
            </option>

            <option value="recientes" <?php if (($_GET['orden'] ?? '') == 'recientes') echo 'selected'; ?>>
                🆕 Nuevo
            </option>
        </select>

        <button type="submit">Buscar</button>
    </form>

    <!-- DERECHA -->
    <div class="navbar-right">

        <?php if ($_SESSION["rol"] === "admin"): ?>
            <a href="../admin/panel.php" class="nav-link">Admin</a>
        <?php endif; ?>

        <a href="perfil.php" class="nav-link">Perfil</a>
        <a href="misCursos.php" class="nav-link">Mis cursos</a>

        <a href="logout.php" class="btn-logout">Salir</a>

    </div>

</div>