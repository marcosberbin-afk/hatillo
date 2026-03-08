<?php
$modulo = 'usuarios';
$modulo_titulo = 'Usuarios';

include_once(realpath(dirname(__FILE__)) . "/include/header.php");

$usuarios_obj = new Usuarios();
$usuarios_sistema = [];

if ($_SESSION['usuario']['rol_id'] == 1 || $_SESSION['usuario']['rol_id'] == 2) {
	$usuarios_sistema = $usuarios_obj->ListadoGeneral($_DB_);
}

?>

<head>
	<!-- DataTables -->
	<link rel="stylesheet" href="./plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
	<link rel="stylesheet" href="./plugins/datatables-responsive/css/responsive.bootstrap4.min.css">

	<!-- fullCalendar -->
	<link rel="stylesheet" href="./plugins/fullcalendar/main.min.css">
	<link rel="stylesheet" href="./plugins/fullcalendar-daygrid/main.min.css">
	<link rel="stylesheet" href="./plugins/fullcalendar-timegrid/main.min.css">
	<link rel="stylesheet" href="./plugins/fullcalendar-bootstrap/main.min.css">
</head>

<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-12">
					<h1 class="m-0 text-<? echo $color; ?>"><img src="./dist/img/logo2.png" width="55"> <? echo $modulo_titulo; ?> </h1>
				</div>
			</div>
		</div>
	</div>

	<section class="content">
		<div class="container-fluid">
			<div class="row">

				<?
				/** /
				echo "<pre>";
				print_r($usuarios_sistema);
				echo "</pre>";
				/**/
				?>


				<?php //if(tieneCapacidadConstanteUsuario(['C_PACIENTES_LISTAR'])): 
				?>
				<div class="col-md-12">

					<div class="card  card-<? echo $color; ?>">
						<div class="card-header">
							<h3 class="card-title">Listado de Usuarios</h3>
						</div>

						<div class="card-body">
							<table id="example1" class="table table-bordered table-striped">
								<thead>
									<tr>
										<th class="text-center">Usuario</th>
										<th class="text-center">Apellido y Nombre</th>
										<th class="text-center">Teléfono</th>
										<th class="text-center">Tipo</th>
										<th class="text-center">Activa</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<?php
									if ($usuarios_sistema) {
										foreach ($usuarios_sistema as $usuario_sis) {
									?>
												<tr>
													<td class="text-left"><? echo $usuario_sis['correo'] ?></td>
													<td><?= $usuario_sis['nombres'] . ', ' . $usuario_sis['apellidos']; ?></td>

													<td><?= $usuario_sis['telefono']; ?></td>

													<td><?= $usuario_sis['nombre_rol']; ?></td>

													<td class="text-center">
														<? if ($usuario_sis['personal_activo']) {
															echo "Si";
														} else {
															echo "No";
														} ?>
													</td>


													<td class="text-center" width="100">

														<div class="btn-group">
															<a class="btn btn-outline btn-info dim" href="usuarios_gestion.php?o=vie&id=<?= $usuario_sis['hash']; ?>" type="button" alt="Consultar" title="Consultar"><i class="fa fa-search"></i></a>

															<?php
															if ($_SESSION['usuario']['rol_id'] == 1 || $_SESSION['usuario']['rol_id'] == 2)
															{
															?>
																<a class="btn btn-outline btn-warning dim" href="usuarios_gestion.php?o=mod&id=<?= $usuario_sis['hash']; ?>" type="button" alt="Modificar" title="Modificar"><i class="fa fa-pen"></i></a>
															
																<a class="btn btn-outline btn-danger dim" href="usuarios_gestion.php?o=del&id=<?= $usuario_sis['hash']; ?>" type="button" alt="Eliminar" title="Eliminar"><i class="fa fa-trash-alt"></i></a>
															<? }
															?>
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
				<?php if ($_SESSION['usuario']['rol_id'] == 1 || $_SESSION['usuario']['rol_id'] == 2): ?>
				<div class="col-md-3">
					<div class="card  card-<? echo $color; ?>">
						<div class="card-header">
							<h5 class="card-title m-0">Opciones</h5>
						</div>

						<div class="card-body">

							<form action="./usuarios_gestion.php" method="POST">
								<button type="submit" class="btn btn-primary float-right"><i class="fas fa-plus"></i>
									Agregar Usuario</button>
						</div>
						</form>
					</div>
				</div>
				<?php endif; ?>
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
	$(function() {
		$("#example1").DataTable({
			"responsive": true,
			"autoWidth": false,
			"ordering": true,
			"lengthMenu": [
				[12, 25, 50, -1],
				[12, 25, 50, "Todos"]
			],
			"order": [
				[1, "asc"]
			],
			"language": {
				"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
			}
		});
	});
</script>
</body>

</html>