<?php
session_start();
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: index_login.php");
    exit();
}

// Crear tabla si no existe (Solo para asegurar funcionamiento inicial)
$sql_create = "CREATE TABLE IF NOT EXISTS guardia_registros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50),
    fecha DATE,
    unidad VARCHAR(50),
    usuario VARCHAR(100),
    datos LONGTEXT,
    fotos LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$pdo->exec($sql_create);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $id = $_POST['id'] ?? ''; // Verificar si es edición
        $tipo = $_POST['tipo_reporte'];
        $fecha = $_POST['fecha'];
        $unidad = $_POST['unidad'];
        
        // Usamos nombres y apellidos de la sesión por defecto
        $usuario = ($_SESSION['usuario']['nombres'] ?? '') . ' ' . ($_SESSION['usuario']['apellidos'] ?? '');
        // Si se envió un responsable (Admin/Superadmin), lo usamos
        if (!empty($_POST['responsable'])) {
            $usuario = $_POST['responsable'];
        }
        
        // Procesar Fotos
        $fotos_subidas = [];
        if (isset($_FILES['fotos'])) {
            $total = count($_FILES['fotos']['name']);
            for ($i = 0; $i < $total; $i++) {
                if ($_FILES['fotos']['error'][$i] == 0) {
                    $ext = pathinfo($_FILES['fotos']['name'][$i], PATHINFO_EXTENSION);
                    $nombre_archivo = uniqid('img_') . '.' . $ext;
                    $ruta_destino = 'uploads/' . $nombre_archivo;
                    
                    if (!is_dir('uploads')) mkdir('uploads');
                    
                    if (move_uploaded_file($_FILES['fotos']['tmp_name'][$i], $ruta_destino)) {
                        $fotos_subidas[] = $ruta_destino;
                    }
                }
            }
        }

        // Si estamos editando y no se subieron fotos nuevas, intentamos mantener las viejas
        if ($id && empty($fotos_subidas)) {
            $stmt_old = $pdo->prepare("SELECT fotos FROM guardia_registros WHERE id = ?");
            $stmt_old->execute([$id]);
            $reg_old = $stmt_old->fetch(PDO::FETCH_ASSOC);
            if ($reg_old) {
                // Mantenemos el JSON de fotos anterior
                $fotos_json = $reg_old['fotos']; 
            } else {
                $fotos_json = json_encode([]);
            }
        } else {
            // Si es nuevo o se subieron fotos nuevas (reemplazo simple)
            $fotos_json = json_encode($fotos_subidas);
        }

        // Guardar todos los datos del formulario como JSON
        $datos_json = json_encode($_POST, JSON_UNESCAPED_UNICODE);

        if ($id) {
            // UPDATE
            $sql = "UPDATE guardia_registros SET fecha=?, unidad=?, datos=?, fotos=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$fecha, $unidad, $datos_json, $fotos_json, $id]);
            $id_insertado = $id;
        } else {
            // INSERT
            $sql = "INSERT INTO guardia_registros (tipo, fecha, unidad, usuario, datos, fotos) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tipo, $fecha, $unidad, $usuario, $datos_json, $fotos_json]);
            $id_insertado = $pdo->lastInsertId();
        }

        // Redireccionar al detalle
        header("Location: guardia_detalle.php?id=" . $id_insertado);
        exit();

    } catch (Exception $e) {
        echo "Error al guardar: " . $e->getMessage();
    }
} else {
    header("Location: guardia_listado.php");
}
?>