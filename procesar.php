<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include 'conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO servicios (nro_servicio, fecha_registro, parroquia, cuadrante, direccion_detallada, resumen_novedad, tipo_actividad_id, estatus_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['nro_servicio'], $_POST['fecha'], $_POST['parroquia'], 
            $_POST['cuadrante'], $_POST['direccion'], $_POST['resumen'], $_POST['tipo_actividad_id']
        ]);

        $servicio_id = $pdo->lastInsertId();

        if (!empty($_POST['personal_id'])) {
            $stmt_act = $pdo->prepare("INSERT INTO servicio_actuantes (servicio_id, personal_id, rol) VALUES (?, ?, ?)");
            foreach ($_POST['personal_id'] as $key => $p_id) {
                if ($p_id) $stmt_act->execute([$servicio_id, $p_id, $_POST['rol'][$key]]);
            }
        }

        $pdo->commit();
        echo "<script>alert('Registrado!'); window.location.href='ver_servicios.php';</script>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>