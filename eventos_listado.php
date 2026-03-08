<?php
$modulo = 'eventos';
$modulo_titulo = 'Listado de los Eventos';

include_once(realpath(dirname(__FILE__)) . "/include/header.php");

$servicios_obj = new Servicios();
$servicios = [];

if (in_array($_SESSION['usuario']['rol_id'], [1, 2, 3, 5, 6])) {
    // Consulta directa para asegurar que se obtengan todos los campos necesarios (incluyendo ID)
    $sql = "SELECT s.*, t.nombre as tipo_actividad_nombre, t.abreviacion as tipo_actividad_abreviacion, e.nombre as estatus_nombre,
                   u.codigo as unidad_codigo, u.descripcion as unidad_descripcion,
                   (SELECT GROUP_CONCAT(CONCAT(p.nombres, ' ', p.apellidos, ' (', sa.rol, ')') SEPARATOR '<br>') 
                    FROM servicio_actuantes sa 
                    JOIN personal p ON sa.personal_id = p.id 
                    WHERE sa.servicio_id = s.id) as lista_actuantes
            FROM servicios s 
            LEFT JOIN tipos_actividad t ON s.tipo_actividad_id = t.id 
            LEFT JOIN estatus e ON s.estatus_id = e.id 
            LEFT JOIN unidades u ON s.unidad_id = u.id
            WHERE s.eliminado = 0 
            ORDER BY s.id DESC";
    $stmt = $pdo->query($sql);
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
					<h1 class="m-0 text-<? echo $color; ?>"><img src="./dist/img/logo2.png" width="55">
						<? echo $modulo_titulo; ?> </h1>
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
							<h3 class="card-title">Listado de Eventos</h3>
						</div>

						<div class="card-body">
							<table id="example1" class="table table-bordered table-striped">
								<thead>
									<tr>
										<th class="text-center">Nº</th>
										<th class="text-center">Fecha</th>
										<th class="text-center">Hora</th>
										<th class="text-center">Tipo</th>
										<th class="text-center">Estatus</th>
										<th class="text-center">Unidades</th>
										<th class="text-center">Actuantes</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<?php
									if ($servicios) {
										foreach ($servicios as $servicio) {
											?>
											<tr>
												<td class="text-center"><? echo $servicio['nro_servicio'] ?></td>
												<td class="text-center"><? if($servicio['fecha_registro'] != '0000-00-00'){echo DateTime::createFromFormat('Y-m-d', $servicio['fecha_registro'])->format('d-m-Y');}?></td>
												<td class="text-center"><?= $servicio['hora_inicio']; ?></td>
												<td class="text-left"><span class="badge bg-danger"><?php echo $servicio['tipo_actividad_abreviacion']; ?></span> - <?php echo $servicio['tipo_actividad_nombre']; ?></td>
												<td class="text-center">
													<?php 
													$est_nombre = strtoupper($servicio['estatus_nombre'] ?? '');
													$est_color = 'bg-secondary'; // Color por defecto (Cerrado u otro)
													if($est_nombre == 'ABIERTO') $est_color = 'bg-danger';
													elseif($est_nombre == 'EN PROCESO') $est_color = 'bg-warning';
													elseif($est_nombre == 'CULMINADO') $est_color = 'bg-success';
													?>
													<span class="badge <?= $est_color; ?>"><?= $servicio['estatus_nombre']; ?></span>
												</td>
												<td class="text-center">
                                                    <?php if(!empty($servicio['unidad_codigo'])): ?>
                                                        <?= $servicio['unidad_codigo'] . ' - ' . $servicio['unidad_descripcion']; ?>
                                                    <?php endif; ?>
                                                </td>
												<td><small><?= $servicio['lista_actuantes']; ?></small></td>

												<td class="text-center" width="100">

													<div class="btn-group">
														<a class="btn btn-info btn-sm" href="detalle.php?id=<?= $servicio['id'] ?? ''; ?>" title="Consultar"><i class="fas fa-search"></i></a>

														<?php
														if ($_SESSION['usuario']['rol_id'] == 1 or $_SESSION['usuario']['rol_id'] == 2) {
															?>
															<a class="btn btn-warning btn-sm" href="evento_registrar.php?id=<?= $servicio['id'] ?? ''; ?>" title="Modificar"><i class="fas fa-pen"></i></a>

															<a class="btn btn-danger btn-sm" href="eventos_operacion.php?op=del&id=<?= $servicio['id'] ?? ''; ?>"
																onclick="return confirm('¿Está seguro de eliminar este evento?');"
																title="Eliminar"><i class="fas fa-trash-alt"></i></a>
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