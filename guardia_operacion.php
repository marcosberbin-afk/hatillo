<?php
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");

$op = $_GET['op'] ?? '';
$id = $_GET['id'] ?? 0;

if ($op == 'del' && $id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM guardia_registros WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: guardia_listado.php?msg=eliminado");
    } catch (Exception $e) {
        echo "Error al eliminar: " . $e->getMessage();
    }
} else {
    header("Location: guardia_listado.php");
}
?>