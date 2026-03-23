<?php
require_once "../includes/bd.php";
session_start();

// Protección login
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Obtener cursos
$sqlCursos = "SELECT * FROM cursos";
$resultadoCursos = mysqli_query($conexion, $sqlCursos);

// Preparar foto desde sesión (seguro)
$foto = (!empty($_SESSION["foto"]) &&
    file_exists("uploads/perfiles/" . $_SESSION["foto"]))
    ? "uploads/perfiles/" . $_SESSION["foto"]
    : "https://ui-avatars.com/api/?name=" . urlencode($_SESSION["nombre"]) . "&background=random&color=fff";
?>

<!DOCTYPE html>
<html>

<head>
    <title>WebDev Academy</title>
</head>

<body>

    <!-- NAVBAR USUARIO -->
    <div
        style="display:flex; align-items:center; justify-content:space-between; padding:10px; border-bottom:1px solid #ccc;">

        <div style="display:flex; align-items:center; gap:10px;">
            <img src="<?php echo $foto; ?>" width="40" height="40" style="border-radius:50%; object-fit:cover;">

            <strong>Hola, <?php echo htmlspecialchars($_SESSION["nombre"]); ?> 👋</strong>
        </div>

        <div>
            <?php if ($_SESSION["rol"] === "admin"): ?>
                <a href="../admin/panel.php">Panel Admin</a> |
            <?php endif; ?>

            <a href="perfil.php">Mi perfil</a> |
            <a href="logout.php">Cerrar sesión</a>
        </div>

    </div>

    <h2>Academia</h2>

    <h3>Cursos disponibles</h3>

    <?php if (mysqli_num_rows($resultadoCursos) > 0):
        $usuario_id = $_SESSION["usuario_id"]; ?>
        <?php while ($curso = mysqli_fetch_assoc($resultadoCursos)): ?>

            <?php
            // Imagen
            $imagen = (!empty($curso["imagen_portada"]) &&
                file_exists("uploads/cursos/" . $curso["imagen_portada"]))
                ? "uploads/cursos/" . $curso["imagen_portada"]
                : "https://via.placeholder.com/400x200?text=Sin+imagen";

            // Precio
            $precio = ($curso["precio"] > 0)
                ? number_format($curso["precio"], 2) . " €"
                : "Gratis";
            ?>

            <div style="
        border:1px solid #ddd;
        border-radius:10px;
        padding:15px;
        margin:20px 0;
        max-width:600px;
        box-shadow:0 2px 6px rgba(0,0,0,0.1);
    ">

                <img src="<?php echo $imagen; ?>" style="width:100%; height:200px; object-fit:cover; border-radius:8px;">

                <h3 style="margin-top:10px;">
                    <?php echo htmlspecialchars($curso["titulo"]); ?>
                </h3>

                <p>
                    <?php echo htmlspecialchars($curso["descripcion"]); ?>
                </p>

                <p style="font-weight:bold;">
                    💰 <?php echo $precio; ?>
                </p>
                <?php // Comprobar estado inscripción
                        $sql_ins = "SELECT estado FROM inscripciones
                WHERE usuario_id = ? AND curso_id = ?";
                        $stmt_ins = mysqli_prepare($conexion, $sql_ins);
                        mysqli_stmt_bind_param($stmt_ins, "ii", $usuario_id, $curso["id"]);
                        mysqli_stmt_execute($stmt_ins);
                        $res_ins = mysqli_stmt_get_result($stmt_ins);
                        $inscripcion = mysqli_fetch_assoc($res_ins); ?>

                <?php if (!$inscripcion): ?>

                    <a href="curso.php?id=<?php echo $curso["id"]; ?>"
                        style="display:inline-block;padding:8px 12px;background:#007bff;color:white;text-decoration:none;border-radius:5px;">
                        Ver curso
                    </a>

                <?php elseif ($inscripcion["estado"] === "pendiente"): ?>

                    <span style="color:orange; font-weight:bold;">
                        ⏳ Solicitud pendiente
                    </span>

                <?php elseif ($inscripcion["estado"] === "aprobado"): ?>

                    <span style="color:green; font-weight:bold;">
                        ✔ Inscrito
                    </span>
                    <br><br>
                    <a href="curso.php?id=<?php echo $curso["id"]; ?>"
                        style="display:inline-block;padding:8px 12px;background:#28a745;color:white;text-decoration:none;border-radius:5px;">
                        Entrar al curso
                    </a>

                <?php endif; ?>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p>No hay cursos disponibles aún.</p>

    <?php endif; ?>

</body>

</html>