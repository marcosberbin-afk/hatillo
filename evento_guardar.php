<?php
// session_start(); // Comentado porque conexion.php ya inicia la sesión
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: index_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $id = $_POST['id'] ?? ''; // ID para edición

        // 1. Autogenerar Nro de Servicio (Solo si es nuevo)
        if (empty($id)) {
            $nro_servicio = '';
            $stmt_last = $pdo->query("SELECT nro_servicio FROM servicios ORDER BY id DESC LIMIT 1");
            $last_svc = $stmt_last->fetch(PDO::FETCH_ASSOC);
            if ($last_svc) {
                $ultimo_num = (int) preg_replace('/[^0-9]/', '', $last_svc['nro_servicio']);
                $nro_servicio = $ultimo_num + 1;
            } else {
                $nro_servicio = "1";
            }
        } else {
            // Si es edición, mantenemos el número (o lo recuperamos si no se envía, aunque el form lo tiene readonly)
            $nro_servicio = $_POST['nro_servicio']; 
        }

        $fecha = $_POST['fecha'] ?? '';           // Corregido: antes fecha_registro
        $hora = $_POST['hora'] ?? date('H:i:s');
        $parroquia = $_POST['parroquia'] ?? '';
        $cuadrante = $_POST['cuadrante'] ?? '';
        $direccion = $_POST['direccion'] ?? '';   // Corregido: antes direccion_detallada
        $categoria = $_POST['categoria'] ?? '';   // Nuevo campo: Categoría General
        $tipo_actividad = !empty($_POST['tipo_actividad']) ? $_POST['tipo_actividad'] : null; // Enviar NULL si está vacío
        $resumen = $_POST['resumen'] ?? '';       // Corregido: antes resumen_novedad
        $reporte_sitio = $_POST['reporte_sitio'] ?? ''; // Nuevo campo
        $unidad = !empty($_POST['unidad']) ? $_POST['unidad'] : null;         // Enviar NULL si está vacío
        $estatus = !empty($_POST['estatus']) ? $_POST['estatus'] : null;       // Enviar NULL si está vacío
        $organismo = $_POST['organismo'] ?? '';   // Nuevo campo para otros actuantes
        $foto_nombre = null;

        // --- Manejo de subida de foto ---
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $target_dir = "dist/img/eventos/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $foto_nombre = uniqid('evento_') . '.' . $ext;
            $target_file = $target_dir . $foto_nombre;

            // Validar que es una imagen
            $check = getimagesize($_FILES['foto']['tmp_name']);
            if($check !== false) {
                move_uploaded_file($_FILES['foto']['tmp_name'], $target_file);
            } else {
                $foto_nombre = null; // No es una imagen
            }
        }

        // 2. Preparar la consulta SQL
        
        if ($id) {
            // --- UPDATE ---
            $sql = "UPDATE servicios SET 
                        fecha_registro=?, hora_inicio=?, parroquia=?, cuadrante=?, direccion_detallada=?, categoria=?,
                        resumen_novedad=?, reporte_sitio=?, unidad_id=?, estatus_id=?, tipo_actividad_id=?, organismo=?";
            
            $params = [$fecha, $hora, $parroquia, $cuadrante, $direccion, $categoria, $resumen, $reporte_sitio, $unidad, $estatus, $tipo_actividad, $organismo];
            
            if ($foto_nombre) {
                $sql .= ", foto=?";
                $params[] = $foto_nombre;
            }
            
            $sql .= " WHERE id=?";
            $params[] = $id;

        } else {
            // --- INSERT ---
            $sql = "INSERT INTO servicios (
                    nro_servicio, 
                    fecha_registro, 
                    hora_inicio,
                    parroquia, 
                    cuadrante, 
                    direccion_detallada, 
                    categoria,
                    resumen_novedad, 
                    reporte_sitio,
                    unidad_id,          
                    estatus_id,         
                    tipo_actividad_id,
                    organismo,
                    foto
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [$nro_servicio, $fecha, $hora, $parroquia, $cuadrante, $direccion, $categoria, $resumen, $reporte_sitio, $unidad, $estatus, $tipo_actividad, $organismo, $foto_nombre];
        }

        // 3. Ejecutar la consulta
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if (empty($id)) {
            $id = $pdo->lastInsertId();
        }

        // 4. Guardar Funcionarios Actuantes
        if (!empty($_POST['personal_id'])) {
            // Si es edición, borramos los anteriores para insertar los nuevos
            if (!empty($_POST['id'])) {
                $stmt_del = $pdo->prepare("DELETE FROM servicio_actuantes WHERE servicio_id = ?");
                $stmt_del->execute([$id]);
            }

           // Crear tabla si no existe
            $pdo->exec("CREATE TABLE IF NOT EXISTS servicio_actuantes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                servicio_id INT,
                personal_id INT,
                rol VARCHAR(50)
            )");

            $stmt_act = $pdo->prepare("INSERT INTO servicio_actuantes (servicio_id, personal_id, rol) VALUES (?, ?, ?)");
            $personal_ids = $_POST['personal_id'];
            $roles = $_POST['rol'];
            $personal_ids_acomp = $_POST['personal_id_acomp'];
            $roles_acomp = $_POST['rol_acomp'];

            for ($i = 0; $i < count($personal_ids); $i++) {
                if (!empty($personal_ids[$i])) {
                    $stmt_act->execute([$id, $personal_ids[$i], $roles[$i]]);
                }
            }

            for ($i = 0; $i < count($personal_ids_acomp); $i++) {
                if (!empty($personal_ids_acomp[$i])) {
                    $stmt_act->execute([$id, $personal_ids_acomp[$i], $roles_acomp[$i]]);
                }
            }



        }

        header("Location: detalle.php?id=" . $id);
        exit();

    } catch (Exception $e) {
        echo "<div style='font-family:sans-serif; padding: 20px;'>";
        echo "<h3 style='color:red'>Error al guardar evento</h3>";
        echo "<p>" . $e->getMessage() . "</p>";
        
        // Ayuda para depuración de columnas
        if (strpos($e->getMessage(), 'Unknown column') !== false) {
            echo "<hr><p><strong>Posible solución:</strong> Tu tabla 'servicios' no tiene alguna de las columnas nuevas. Ejecuta este SQL en tu base de datos (phpMyAdmin):</p>";
            echo "<pre style='background:#f4f4f4; padding:10px; border:1px solid #ccc;'>";
            echo "ALTER TABLE servicios \n";
            echo "ADD COLUMN organismo VARCHAR(100) NULL,\n";
            echo "ADD COLUMN hora_inicio TIME NULL,\n";
            echo "ADD COLUMN reporte_sitio TEXT NULL,\n";
            echo "ADD COLUMN foto VARCHAR(255) NULL,\n";
            echo "ADD COLUMN categoria VARCHAR(100) NULL,\n";
            echo "ADD COLUMN cant_funcionarios INT NULL,\n";
            echo "ADD COLUMN supervisor VARCHAR(100) NULL,\n";
            echo "ADD COLUMN conductor VARCHAR(100) NULL,\n";
            // Si unidad_id ya existe, no agregues esta línea, o cámbiala a MODIFY si necesitas que sea VARCHAR
            echo "MODIFY COLUMN unidad_id VARCHAR(50) NULL, \n"; 
            echo "MODIFY COLUMN estatus_id VARCHAR(50) NULL, \n";
            echo "MODIFY COLUMN tipo_actividad_id VARCHAR(100) NULL;";
            echo "</pre>";
        }
        echo "<br><a href='javascript:history.back()'>Volver al formulario</a>";
        echo "</div>";
    }
}
?>