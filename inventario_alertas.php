<?php
$modulo = 'inventario';
$menu_abierto = 'inventario';
$modulo_titulo = 'Alertas de Inventario';

include_once(realpath(dirname(__FILE__)) . "/include/header.php");

$inventario_obj = new Inventario();

// --- LÓGICA PARA RESOLVER ALERTA ---
if (isset($_GET['action']) && $_GET['action'] == 'resolver' && isset($_GET['id'])) {
    $id_alerta = intval($_GET['id']);
    $id_usuario = $_SESSION['usuario']['id'] ?? 1;
    $inventario_obj->resolverAlerta($_DB_, $id_alerta, $id_usuario);
    // Recargar para limpiar la URL y actualizar la tabla
    echo "<script>window.location.href='inventario_alertas.php';</script>";
    exit();
}

$alertas = $inventario_obj->getAlertas($_DB_);

?>

<head>
	<!-- DataTables -->
	<link rel="stylesheet" href="./plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
	<link rel="stylesheet" href="./plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
</head>

<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-12">
					<h1 class="m-0 text-<? echo $color; ?>"><img src="./dist/img/logo2.png" width="55">
						<? echo $modulo_titulo; ?> </h1>
				</div>
			</div>
		</div>
	</div>

	<section class="content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-12">
					<div class="card">
						<div class="card-header">
							<h3 class="card-title">Alertas Activas</h3>
						</div>
						<div class="card-body">
							<table id="alertasTable" class="table table-bordered table-striped">
								<thead>
									<tr>
										<th>Tipo</th>
										<th>Producto</th>
										<th>Lote</th>
										<th>Mensaje</th>
										<th>Fecha</th>
										<th>Acciones</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($alertas as $alerta): ?>
									<tr>
										<td><?php echo $alerta['tipo_alerta']; ?></td>
										<td><?php echo $alerta['producto_nombre'] ?: 'N/A'; ?></td>
										<td><?php echo $alerta['numero_lote'] ?: 'N/A'; ?></td>
										<td><?php echo $alerta['mensaje']; ?></td>
										<td><?php echo $alerta['fecha_alerta']; ?></td>
										<td>
											<button class="btn btn-success btn-sm" onclick="resolverAlerta(<?php echo $alerta['id_alerta']; ?>)">Resolver</button>
										</td>
									</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>

<!-- DataTables -->
<script src="./plugins/datatables/jquery.dataTables.min.js"></script>
<script src="./plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="./plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="./plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<script>
$(function () {
	$('#alertasTable').DataTable({
		"responsive": true,
		"lengthChange": false,
		"autoWidth": false
	});
});

function resolverAlerta(id_alerta) {
    if (confirm('¿Marcar esta alerta como resuelta?')) {
        // Redirigir con el parámetro para que PHP lo procese
        window.location.href = 'inventario_alertas.php?action=resolver&id=' + id_alerta;
    }
}
</script>

<?php include_once(realpath(dirname(__FILE__)) . "/include/footer.php"); ?>