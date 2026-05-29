<?php

// OBTENER MEDIA Y TOTAL VALORACIONES
function obtenerMediaCurso($conexion, $curso_id)
{
    $sql = "SELECT AVG(puntuacion) as media, COUNT(*) as total FROM valoraciones WHERE curso_id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $curso_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($res);
}

// OBTENER INSCRIPCIÓN
function obtenerInscripcion($conexion, $usuario_id, $curso_id)
{
    $sql = "SELECT estado FROM inscripciones WHERE usuario_id = ? AND curso_id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $curso_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($res);
}

// OBTENER CURSO
function obtenerCurso($conexion, $curso_id)
{
    $sql = "SELECT * FROM cursos WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $curso_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($res);
}

// CANCELAR INSCRIPCIÓN
function cancelarInscripcion($conexion, $usuario_id, $curso_id)
{
    $sql = "DELETE FROM inscripciones WHERE usuario_id = ? AND curso_id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $curso_id);
    mysqli_stmt_execute($stmt);
}

// GESTIONAR INSCRIPCIÓN
function gestionarInscripcion($conexion, $usuario_id, $curso_id)
{
    $sqlCheck = "SELECT estado FROM inscripciones WHERE usuario_id = ? AND curso_id = ?";
    $stmtCheck = mysqli_prepare($conexion, $sqlCheck);
    mysqli_stmt_bind_param($stmtCheck, "ii", $usuario_id, $curso_id);
    mysqli_stmt_execute($stmtCheck);
    $resCheck = mysqli_stmt_get_result($stmtCheck);
    $insc = mysqli_fetch_assoc($resCheck);

    if (!$insc) {
        $sqlInsert = "INSERT INTO inscripciones (usuario_id, curso_id, estado) VALUES (?, ?, 'pendiente')";
        $stmt = mysqli_prepare($conexion, $sqlInsert);
        mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $curso_id);
        mysqli_stmt_execute($stmt);

    } elseif ($insc["estado"] === "rechazado") {
        $sqlUpdate = "UPDATE inscripciones SET estado = 'pendiente' WHERE usuario_id = ? AND curso_id = ?";
        $stmt = mysqli_prepare($conexion, $sqlUpdate);
        mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $curso_id);
        mysqli_stmt_execute($stmt);
    }
}

// OBTENER LECCIONES
function obtenerLecciones($conexion, $curso_id)
{
    $sql = "SELECT * FROM lecciones WHERE curso_id = ? ORDER BY orden ASC";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $curso_id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}


// LECCIÓN COMPLETADA
function leccionCompletada($conexion, $usuario_id, $leccion_id)
{
    $sql = "SELECT id FROM progreso WHERE usuario_id = ? AND leccion_id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $leccion_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    return mysqli_stmt_num_rows($stmt) > 0;
}

// ELIMINAR VALORACIÓN
function eliminarValoracion($conexion, $id, $usuario_id)
{
    $sql = "DELETE FROM valoraciones WHERE id = ? AND usuario_id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id, $usuario_id);
    mysqli_stmt_execute($stmt);
}

// OBTENER VALORACIÓN
function obtenerValoracion($conexion, $id, $usuario_id)
{
    $sql = "SELECT * FROM valoraciones WHERE id = ? AND usuario_id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id, $usuario_id);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// GUARDAR VALORACIÓN
function guardarValoracion($conexion, $usuario_id, $curso_id, $data)
{
    $puntuacion = intval($data["puntuacion"]);
    $comentario = trim($data["comentario"] ?? "");

    if ($puntuacion < 1 || $puntuacion > 5)
        return;

    $sqlCheck = "SELECT id FROM valoraciones WHERE usuario_id = ? AND curso_id = ?";
    $stmtCheck = mysqli_prepare($conexion, $sqlCheck);
    mysqli_stmt_bind_param($stmtCheck, "ii", $usuario_id, $curso_id);
    mysqli_stmt_execute($stmtCheck);
    $resCheck = mysqli_stmt_get_result($stmtCheck);
    $existe = mysqli_fetch_assoc($resCheck);

    if ($existe) {
        $sql = "UPDATE valoraciones SET puntuacion = ?, comentario = ? WHERE usuario_id = ? AND curso_id = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "isii", $puntuacion, $comentario, $usuario_id, $curso_id);
    } else {
        $sql = "INSERT INTO valoraciones (usuario_id, curso_id, puntuacion, comentario) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "iiis", $usuario_id, $curso_id, $puntuacion, $comentario);
    }

    mysqli_stmt_execute($stmt);
}

// VALORACIÓN DEL USUARIO
function obtenerValoracionUsuario($conexion, $usuario_id, $curso_id)
{
    $sql = "SELECT * FROM valoraciones WHERE usuario_id = ? AND curso_id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $curso_id);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// EXAMEN APROBADO
function examenAprobado($conexion, $usuario_id, $curso_id)
{
    $sql = "
    SELECT aprobado 
    FROM resultados_examen 
    WHERE usuario_id = ? AND curso_id = ?
    ORDER BY fecha DESC
    LIMIT 1
    ";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $curso_id);
    mysqli_stmt_execute($stmt);

    $res = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    return $res && $res["aprobado"] == 1;
}

// LISTAR COMENTARIOS

function obtenerComentariosCurso($conexion, $curso_id)
{
    $sql = "SELECT v.id, v.usuario_id, v.puntuacion, v.comentario, v.fecha, u.nombre
            FROM valoraciones v
            JOIN usuarios u ON v.usuario_id = u.id
            WHERE v.curso_id = ?
            ORDER BY v.fecha DESC";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $curso_id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

// OBTENER USUARIO POR EMAIL
function obtenerUsuarioPorEmail($conexion, $email)
{
    $sql = "SELECT id, nombre, password, rol, activo, foto 
            FROM usuarios 
            WHERE email = ?";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// VERIFICAR PASSWORD
function verificarPassword($password, $hash)
{
    return password_verify($password, $hash);
}

// CREAR SESIÓN
function crearSesionUsuario($usuario)
{
    $_SESSION["usuario_id"] = $usuario["id"];
    $_SESSION["nombre"] = $usuario["nombre"];
    $_SESSION["rol"] = $usuario["rol"];
    $_SESSION["foto"] = $usuario["foto"];
}

// COMPROBAR SI EXISTE USUARIO
function existeUsuarioPorEmail($conexion, $email)
{
    $sql = "SELECT id FROM usuarios WHERE email = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    return mysqli_stmt_num_rows($stmt) > 0;
}

// CREAR USUARIO
function crearUsuario($conexion, $nombre, $email, $password)
{
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nombre, email, password)
            VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $nombre, $email, $password_hash);

    return mysqli_stmt_execute($stmt);
}

// OBTENER USUARIO POR ID
function obtenerUsuarioPorId($conexion, $id)
{
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// OBTENER FOTO PERFIL
function obtenerFotoUsuario($nombre, $foto)
{
    $rutaFisica = __DIR__ . "/../public/uploads/perfiles/" . $foto;
    $rutaWeb = "uploads/perfiles/" . $foto;

    if (!empty($foto) && file_exists($rutaFisica)) {
        return $rutaWeb;
    }

    return "https://ui-avatars.com/api/?name=" . urlencode($nombre) . "&background=random&color=fff";
}


// CAMBIAR PASSWORD
function cambiarPassword($conexion, $usuario, $data)
{
    $actual = $data["password_actual"] ?? "";
    $nueva = $data["password_nueva"] ?? "";
    $confirmar = $data["password_confirmar"] ?? "";

    if (!password_verify($actual, $usuario["password"])) {
        return "La contraseña actual no es correcta.";
    }

    if (strlen($nueva) < 6) {
        return "La nueva contraseña debe tener al menos 6 caracteres.";
    }

    if ($nueva !== $confirmar) {
        return "Las contraseñas no coinciden.";
    }

    $hash = password_hash($nueva, PASSWORD_DEFAULT);

    $sql = "UPDATE usuarios SET password = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "si", $hash, $usuario["id"]);
    mysqli_stmt_execute($stmt);

    return true;
}

// SUBIR FOTO PERFIL
function subirFotoPerfil($conexion, $id, $archivo)
{
    if ($archivo["error"] !== 0) {
        return "Error al subir archivo.";
    }

    $tiposPermitidos = ["image/jpeg", "image/png", "image/webp"];

    if (!in_array($archivo["type"], $tiposPermitidos)) {
        return "Formato no permitido.";
    }

    if ($archivo["size"] > 2 * 1024 * 1024) {
        return "La imagen supera el tamaño máximo (2MB).";
    }

    $extension = pathinfo($archivo["name"], PATHINFO_EXTENSION);

    $nombre = "usuario_" . $id . "." . $extension;

    $ruta = __DIR__ . "/../public/uploads/perfiles/" . $nombre;

    if (!move_uploaded_file($archivo["tmp_name"], $ruta)) {
        return "Error al mover archivo.";
    }

    $sql = "UPDATE usuarios SET foto = ? WHERE id = ?";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param($stmt, "si", $nombre, $id);

    mysqli_stmt_execute($stmt);

    $_SESSION["foto"] = $nombre;

    return true;
}
// OBTENER LECCIÓN
function obtenerLeccion($conexion, $leccion_id)
{
    $sql = "SELECT l.*, c.titulo AS curso_titulo, c.id AS curso_id
            FROM lecciones l
            JOIN cursos c ON l.curso_id = c.id
            WHERE l.id = ?";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $leccion_id);
    mysqli_stmt_execute($stmt);

    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}
// COMPROBAR ACCESO A LECCIÓN
function puedeAccederLeccion($conexion, $usuario_id, $curso_id)
{
    // CURSO ACTIVO
    $sql = "SELECT activo FROM cursos WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $curso_id);
    mysqli_stmt_execute($stmt);
    $curso = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$curso || $curso["activo"] != 1) {
        return false;
    }

    // INSCRIPCIÓN APROBADA
    $sql = "SELECT estado FROM inscripciones WHERE usuario_id = ? AND curso_id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $curso_id);
    mysqli_stmt_execute($stmt);
    $ins = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    return $ins && $ins["estado"] === "aprobado";
}
// TOGGLE PROGRESO
function toggleProgreso($conexion, $usuario_id, $leccion_id, $completada)
{
    if ($completada) {

        $sql = "DELETE FROM progreso WHERE usuario_id = ? AND leccion_id = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $leccion_id);

    } else {

        $sql = "INSERT INTO progreso (usuario_id, leccion_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $leccion_id);
    }

    mysqli_stmt_execute($stmt);
}

// OBTENER PROGRESO DEL CURSO
function obtenerProgresoCurso($conexion, $usuario_id, $curso_id)
{
    // TOTAL LECCIONES
    $sql_total = "SELECT COUNT(*) as total FROM lecciones WHERE curso_id = ?";
    $stmt = mysqli_prepare($conexion, $sql_total);
    mysqli_stmt_bind_param($stmt, "i", $curso_id);
    mysqli_stmt_execute($stmt);

    $total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))["total"];

    // COMPLETADAS
    $sql_comp = "
    SELECT COUNT(*) as completadas
    FROM progreso p
    JOIN lecciones l ON p.leccion_id = l.id
    WHERE p.usuario_id = ? AND l.curso_id = ?
    ";

    $stmt = mysqli_prepare($conexion, $sql_comp);
    mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $curso_id);
    mysqli_stmt_execute($stmt);

    $completadas = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))["completadas"];

    $porcentaje = $total > 0 ? round(($completadas / $total) * 100) : 0;

    return [
        "total" => $total,
        "completadas" => $completadas,
        "porcentaje" => $porcentaje
    ];
}

// OBTENER CURSOS DEL USUARIO
function obtenerCursosUsuario($conexion, $usuario_id)
{
    $sql = "
    SELECT c.*
    FROM inscripciones i
    JOIN cursos c ON i.curso_id = c.id
    WHERE i.usuario_id = ?
    AND i.estado = 'aprobado'
    AND c.activo = 1
    ";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $usuario_id);
    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt);
}
// BORRAR RESULTADO ANTERIOR
function borrarResultadoExamen($conexion, $usuario_id, $curso_id)
{
    $sql = "DELETE FROM resultados_examen WHERE usuario_id = ? AND curso_id = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $curso_id);
    mysqli_stmt_execute($stmt);
}
// GUARDAR RESULTADO EXAMEN
function guardarResultadoExamen($conexion, $usuario_id, $curso_id, $nota, $aprobado)
{
    $sql = "INSERT INTO resultados_examen 
            (usuario_id, curso_id, examen_id, nota, aprobado)
            VALUES (?, ?, 1, ?, ?)";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "iiii", $usuario_id, $curso_id, $nota, $aprobado);
    mysqli_stmt_execute($stmt);
}

// CURSOS FILTRADOS

function obtenerCursosFiltrados($conexion, $buscar, $precio, $orden, $limit, $offset)
{
    $sql = "
    SELECT c.*, 
    COUNT(DISTINCT i.id) as total_inscritos,
    AVG(v.puntuacion) as media_rating
    FROM cursos c
    LEFT JOIN inscripciones i ON c.id = i.curso_id
    LEFT JOIN valoraciones v ON c.id = v.curso_id
    WHERE c.activo = 1
    ";

    if (!empty($buscar)) {
        $buscarSeguro = mysqli_real_escape_string($conexion, $buscar);
        $sql .= " AND c.titulo LIKE '%$buscarSeguro%'";
    }

    if ($precio === "gratis") {
        $sql .= " AND c.precio = 0";
    }

    if ($precio === "pago") {
        $sql .= " AND c.precio > 0";
    }

    $sql .= " GROUP BY c.id";

    if ($orden === "rating") {
        $sql .= " ORDER BY media_rating DESC";
    } elseif ($orden === "inscritos") {
        $sql .= " ORDER BY total_inscritos DESC";
    } elseif ($orden === "recientes") {
        $sql .= " ORDER BY c.id DESC";
    }

    $sql .= " LIMIT $limit OFFSET $offset";

    return mysqli_query($conexion, $sql);
}

// TOTAL CURSOS
function contarCursosFiltrados($conexion, $buscar, $precio)
{
    $sql = "SELECT COUNT(*) as total FROM cursos WHERE activo = 1";

    if (!empty($buscar)) {
        $buscarSeguro = mysqli_real_escape_string($conexion, $buscar);
        $sql .= " AND titulo LIKE '%$buscarSeguro%'";
    }

    if ($precio === "gratis") {
        $sql .= " AND precio = 0";
    }

    if ($precio === "pago") {
        $sql .= " AND precio > 0";
    }

    $res = mysqli_query($conexion, $sql);
    return mysqli_fetch_assoc($res)["total"];
}

// CURSO CONTINUAR
function obtenerCursoContinuar($conexion, $usuario_id)
{
    $sql = "
    SELECT c.id, c.titulo
    FROM inscripciones i
    JOIN cursos c ON i.curso_id = c.id
    WHERE i.usuario_id = ? 
    AND i.estado = 'aprobado'
    ORDER BY i.ultima_visita DESC
    LIMIT 1
    ";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $usuario_id);
    mysqli_stmt_execute($stmt);

    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}


function validarCursoActivo($curso)
{
    if (!$curso || $curso["activo"] != 1) {
        header("Location: index.php");
        exit;
    }
}