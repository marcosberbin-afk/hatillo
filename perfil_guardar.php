<?php
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");

// 1. Seguridad: Verificar que el usuario está logueado y que el método es POST
if (!isset($_SESSION['usuario'])) {
    header("Location: index_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirigir si no es una solicitud POST
    header("Location: perfil.php");
    exit();
}

// 2. Obtener los datos del formulario
$nombres = $_POST['nombres'] ?? '';
$apellidos = $_POST['apellidos'] ?? '';
$educacion = $_POST['educacion'] ?? '';
$ubicacion = $_POST['ubicacion'] ?? '';
$notas = $_POST['notas'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$fecha_nacimiento = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;

$foto_nombre = null;

// Procesar subida de foto
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $target_dir = "dist/img/personal/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $foto_nombre = uniqid('perfil_') . '.' . $ext;
    $target_file = $target_dir . $foto_nombre;

    $check = getimagesize($_FILES['foto']['tmp_name']);
    if ($check !== false) {
        move_uploaded_file($_FILES['foto']['tmp_name'], $target_file);
    }
}

// Obtener el ID del personal desde la sesión
$personal_id = $_SESSION['usuario']['personal_id'] ?? 0;

// --- AUTO-CORRECCIÓN: Si no hay personal_id, intentar vincular o crear uno ---
if (empty($personal_id) || $personal_id == 0) {
    $usuario_id = $_SESSION['usuario']['id'];
    $correo_usuario = $_SESSION['usuario']['correo'];

    // 1. Buscar si ya existe un personal con este correo
    $stmt_check = $pdo->prepare("SELECT id FROM personal WHERE correo = ? LIMIT 1");
    $stmt_check->execute([$correo_usuario]);
    $row_check = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($row_check) {
        $personal_id = $row_check['id'];
    } else {
        // 2. Si no existe, crear un nuevo registro de personal
        // Generar cédula temporal para evitar error de duplicado (Unique Key)
        $cedula_temp = 'TMP' . mt_rand(100000, 999999);
        $sql_insert = "INSERT INTO personal (nombres, apellidos, correo, cedula, activo, fecha_sistema, hash) VALUES (?, ?, ?, ?, 1, NOW(), MD5(CONCAT(?, NOW())))";
        $stmt_insert = $pdo->prepare($sql_insert);
        // Usar nombres del formulario o defaults
        $n = !empty($nombres) ? $nombres : 'Usuario';
        $a = !empty($apellidos) ? $apellidos : 'Sistema';
        $stmt_insert->execute([$n, $a, $correo_usuario, $cedula_temp, $correo_usuario]);
        $personal_id = $pdo->lastInsertId();
    }

    // 3. Vincular el usuario con el personal encontrado/creado
    if ($personal_id) {
        $stmt_link = $pdo->prepare("UPDATE usuarios SET personal_id = ? WHERE id = ?");
        $stmt_link->execute([$personal_id, $usuario_id]);
        
        // Actualizar la sesión
        $_SESSION['usuario']['personal_id'] = $personal_id;
    }
}

if ($personal_id > 0) {
    try {
        // 3. Preparar y ejecutar la actualización en la base de datos
        // Asumimos que los campos 'educacion', 'ubicacion', 'notas' existen en la tabla 'personal'.
        // Si no existen, habrá que añadirlos con un ALTER TABLE.
        $sql = "UPDATE personal SET 
                    nombres = ?, 
                    apellidos = ?, 
                    educacion = ?, 
                    ubicacion = ?, 
                    notas = ?,
                    telefono = ?,
                    fecha_nacimiento = ?";
        
        $params = [$nombres, $apellidos, $educacion, $ubicacion, $notas, $telefono, $fecha_nacimiento];

        if ($foto_nombre) {
            $sql .= ", foto = ?";
            $params[] = $foto_nombre;
        }

        $sql .= " WHERE id = ?";
        $params[] = $personal_id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // 4. Actualizar la variable de sesión para reflejar los cambios inmediatamente
        $_SESSION['usuario']['nombres'] = $nombres;
        $_SESSION['usuario']['apellidos'] = $apellidos;
        $_SESSION['usuario']['educacion'] = $educacion;
        $_SESSION['usuario']['ubicacion'] = $ubicacion;
        $_SESSION['usuario']['notas'] = $notas;
        $_SESSION['usuario']['telefono'] = $telefono;
        $_SESSION['usuario']['fecha_nacimiento'] = $fecha_nacimiento;
        
        if ($foto_nombre) {
            $_SESSION['usuario']['foto'] = $foto_nombre;
        }

        // 5. Redirigir de vuelta al perfil con un mensaje de éxito
        header("Location: perfil.php?status=success");
        exit();

    } catch (PDOException $e) {
        // Si el error es por columnas que no existen, damos una pista.
        if (strpos($e->getMessage(), 'Unknown column') !== false) {
            echo "<div style='font-family:sans-serif; border: 2px solid red; padding: 20px; margin: 20px;'><h3>Error de Base de Datos</h3><p>Parece que a tu tabla 'personal' le faltan algunas columnas para guardar toda la información del perfil.</p><p>Ejecuta un comando SQL similar a este en tu base de datos (usando phpMyAdmin) para añadir las columnas que falten:</p><pre style='background:#f4f4f4; padding:10px; border:1px solid #ccc;'>ALTER TABLE `personal` \nADD COLUMN `educacion` TEXT NULL, \nADD COLUMN `ubicacion` VARCHAR(255) NULL, \nADD COLUMN `notas` TEXT NULL, \nADD COLUMN `telefono` VARCHAR(50) NULL, \nADD COLUMN `fecha_nacimiento` DATE NULL, \nADD COLUMN `foto` VARCHAR(255) NULL;</pre><a href='perfil.php'>Volver al perfil</a></div>";
        } else {
            // Otro tipo de error, redirigimos con un mensaje genérico
            $error_msg = $e->getMessage();
            header("Location: perfil.php?status=error&msg=" . urlencode($error_msg));
            exit();
        }
    }
} else {
    // No se encontró un ID de personal en la sesión, algo anda mal.
    header("Location: perfil.php?status=error_no_id&msg=" . urlencode("No se encontró un ID de personal asociado a tu usuario. Intenta cerrar sesión y volver a entrar."));
    exit();
}
?>