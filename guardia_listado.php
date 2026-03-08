<?php
$modulo = 'guardia';
$modulo_titulo = 'Cambio de Guardia';

include_once(realpath(dirname(__FILE__)) . "/include/header.php");

$guardias = [];

try {
    $sql = "SELECT * FROM guardia_registros";
    // Filtros por Rol
    if ($_SESSION['usuario']['rol_id'] == 5) {
        $sql .= " WHERE tipo = 'conductor'";
    } elseif ($_SESSION['usuario']['rol_id'] == 6) {
        $sql .= " WHERE tipo = 'paramedico'";
    }
    $sql .= " ORDER BY id DESC";
    $stmt = $pdo->query($sql);
    $guardias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Si la tabla no existe aún, no pasa nada
}

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
				<div class="col-md-12">

					<div class="card  card-<? echo $color; ?>">
						<div class="card-header">
							<h3 class="card-title">Listado de Cambios de Guardia</h3>
						</div>

						<div class="card-body">
							<table id="example1" class="table table-bordered table-striped">
								<thead>
									<tr>
										<th class="text-center">ID</th>
										<th class="text-center">Fecha</th>
										<th class="text-center">Tipo</th>
										<th class="text-center">Unidad</th>
										<th class="text-center">Responsable</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<?php
									if ($guardias) {
										foreach ($guardias as $guardia) {
											?>
											<tr>
												<td class="text-center"><?= $guardia['id']; ?></td>
												<td class="text-center"><?= date('d/m/Y', strtotime($guardia['fecha'])); ?></td>
												<td class="text-center"><?= ucfirst($guardia['tipo']); ?></td>
												<td class="text-center"><?= $guardia['unidad']; ?></td>
												<td><?= $guardia['usuario']; ?></td>

												<td class="text-center" width="100">
													<div class="btn-group">
														<!-- Botón Ver -->
														<a class="btn btn-outline btn-info dim"
															href="guardia_detalle.php?id=<?= $guardia['id']; ?>"
															type="button" alt="Consultar" title="Consultar"><i
																class="fa fa-search"></i></a>
														
														<!-- Botón Editar (Redirige según el tipo) -->
														<?php $link_edit = ($guardia['tipo'] == 'conductor') ? 'guardia_conductor.php' : 'guardia_paramedico.php'; ?>
														<a class="btn btn-outline btn-warning dim"
															href="<?= $link_edit; ?>?id=<?= $guardia['id']; ?>"
															type="button" alt="Editar" title="Editar"><i
																class="fa fa-pen"></i></a>

														<!-- Botón Eliminar -->
														<a class="btn btn-outline btn-danger dim"
															href="guardia_operacion.php?op=del&id=<?= $guardia['id']; ?>"
															onclick="return confirm('¿Está seguro de eliminar este reporte?');"
															type="button" alt="Eliminar" title="Eliminar"><i
																class="fa fa-trash-alt"></i></a>
													</div>
												</td>
											</tr>
											<?php
										}
									} ?>
									</tfoot>
							</table>
						</div>
					</div>
				</div>
			</div>
	</section>

</div>

<?php
include_once(realpath(dirname(__FILE__)) . "/include/footer.php");
?>

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
	<!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->
<!-- jQuery -->
<script src="./plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="./plugins/datatables/jquery.dataTables.min.js"></script>
<script src="./plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="./plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="./plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<!-- AdminLTE App -->
<script src="./dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="./dist/js/demo.js"></script>
<!-- page script -->
<script>
	$(function () {
		$("#example1").DataTable({
			"responsive": true,
			"autoWidth": false,
			"ordering": true,
			"lengthMenu": [
				[12, 25, 50, -1],
				[12, 25, 50, "Todos"]
			],
			"order": [
				[0, "desc"]
			],
			"language": {
				"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
			}
		});
	});
</script>
</body>
</html>