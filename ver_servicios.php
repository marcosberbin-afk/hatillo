<?php
$pagina = '';
include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");

// Lógica de búsqueda
$busqueda = $_GET['buscar'] ?? '';
$query_str = "SELECT s.id, s.nro_servicio, s.fecha_registro, t.abreviacion, t.nombre as actividad, s.parroquia 
              FROM servicios s 
              JOIN tipos_actividad t ON s.tipo_actividad_id = t.id";

if ($busqueda) {
    $query_str .= " WHERE s.nro_servicio LIKE ? OR t.nombre LIKE ? OR s.resumen_novedad LIKE ?";
    $stmt = $pdo->prepare($query_str);
    $stmt->execute(["%$busqueda%", "%$busqueda%", "%$busqueda%"]);
} else {
    $query_str .= " ORDER BY s.fecha_registro DESC LIMIT 50";
    $stmt = $pdo->query($query_str);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial - PC El Hatillo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Historial de Servicios</h2>
            <a href="index.php" class="btn btn-outline-secondary">Volver al Inicio</a>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <form method="GET" class="row g-2">
                    <div class="col-md-10">
                        <input type="text" name="buscar" class="form-control" placeholder="Buscar por Nro de servicio, actividad o palabras clave..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-danger w-100">Buscar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive shadow-sm bg-white p-3 rounded">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nro Servicio</th>
                        <th>Fecha</th>
                        <th>Actividad</th>
                        <th>Parroquia</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch()): ?>
                    <tr>
                        <td class="fw-bold"><?php echo $row['nro_servicio']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['fecha_registro'])); ?></td>
                        <td><span class="badge bg-danger"><?php echo $row['abreviacion']; ?></span> - <?php echo $row['actividad']; ?></td>
                        <td><?php echo $row['parroquia']; ?></td>
                        <td class="text-center">
                            <a href="detalle.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Ver Reporte</a>
                            <a href="generar_pdf.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger">Descargar PDF</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>