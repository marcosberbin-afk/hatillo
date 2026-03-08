<?php
$modulo = 'inventario';
$menu_abierto = 'inventario';
$modulo_titulo = 'Inventario';

include_once(realpath(dirname(__FILE__)) . "/include/header.php");

$inventario_obj = new Inventario();
$almacenes = $inventario_obj->getAlmacenes($_DB_);
$stock = [];

if (isset($_GET['id_almacen'])) {
    $id_almacen = $_GET['id_almacen'];
    $stock = $inventario_obj->getStock($_DB_, $id_almacen);
}

?>

<head>
	<!-- DataTables -->
	<link rel="stylesheet" href="./plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
	<link rel="stylesheet" href="./plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
	<link rel="stylesheet" href="./plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
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
							<h3 class="card-title">Seleccione Almacén</h3>
						</div>
						<div class="card-body">
							<form method="GET" action="">
								<div class="form-group">
									<label for="id_almacen">Almacén:</label>
									<select name="id_almacen" id="id_almacen" class="form-control" onchange="this.form.submit()">
										<option value="">Seleccione...</option>
										<?php foreach ($almacenes as $almacen): ?>
											<option value="<?php echo $almacen['id_almacen']; ?>" <?php echo (isset($_GET['id_almacen']) && $_GET['id_almacen'] == $almacen['id_almacen']) ? 'selected' : ''; ?>>
												<?php echo $almacen['nombre'] . ' (' . $almacen['tipo'] . ')'; ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</form>
						</div>
					</div>
				</div>

				<?php if (isset($id_almacen) && !empty($id_almacen) && !empty($stock)): ?>
				<div class="col-12">
					<div class="card">
						<div class="card-header">
							<h3 class="card-title">Stock en <?php echo $almacenes[array_search($id_almacen, array_column($almacenes, 'id_almacen'))]['nombre']; ?></h3>
							<div class="card-tools">
								<a href="inventario_operacion.php?action=transferir&id_almacen_origen=<?php echo $id_almacen; ?>" class="btn btn-primary btn-sm">Transferir</a>
							</div>
						</div>
						<div class="card-body">
							<table id="stockTable" class="table table-bordered table-striped">
								<thead>
									<tr>
										<th>Producto</th>
										<th>Código</th>
										<th>Marca</th>
										<th>Lote</th>
										<th>Fecha Vencimiento</th>
										<th>Cantidad</th>
										<th>Stock Mínimo</th>
										<th>Estado</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($stock as $item): ?>
									<tr>
										<td><?php echo $item['producto_nombre']; ?></td>
										<td><?php echo $item['codigo_producto']; ?></td>
										<td><?php echo $item['marca']; ?></td>
										<td><?php echo $item['numero_lote']; ?></td>
										<td><?php echo $item['fecha_vencimiento']; ?></td>
										<td><?php echo $item['cantidad']; ?></td>
										<td><?php echo $item['stock_minimo']; ?></td>
										<td>
											<?php
											$estado = 'Normal';
											if ($item['cantidad'] <= $item['stock_minimo']) $estado = 'Stock Bajo';
											if (strtotime($item['fecha_vencimiento']) < strtotime('+30 days')) $estado = 'Próximo a Vencer';
											echo $estado;
											?>
										</td>
									</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<?php elseif (isset($id_almacen) && !empty($id_almacen) && empty($stock)): ?>
				<div class="col-12">
					<div class="alert alert-info">
						<h5><i class="icon fas fa-info"></i> Almacén Vacío</h5>
						No se encontraron productos registrados en este almacén.
						<br>
						Para agregar inventario, vaya a <a href="inventario_operacion.php" class="text-bold" style="text-decoration: underline;">Operaciones</a> y registre una entrada.
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
</div>

<?php include_once(realpath(dirname(__FILE__)) . "/include/footer.php"); ?>

<!-- jQuery -->
<script src="./plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="./dist/js/adminlte.min.js"></script>
<!-- DataTables -->
<script src="./plugins/datatables/jquery.dataTables.min.js"></script>
<script src="./plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="./plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="./plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="./plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="./plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="./plugins/jszip/jszip.min.js"></script>
<script src="./plugins/pdfmake/pdfmake.min.js"></script>
<script src="./plugins/pdfmake/vfs_fonts.js"></script>
<script src="./plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="./plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="./plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

<script>
$(function () {
	$('#stockTable').DataTable({
		"responsive": true,
		"lengthChange": false,
		"autoWidth": false,
		"buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
	}).buttons().container().appendTo('#stockTable_wrapper .col-md-6:eq(0)');
});
</script>