<?php
$modulo = 'eventos';
$modulo_titulo = 'Módulo de Eventos';

include_once(realpath(dirname(__FILE__)) . "/include/conexion.php");

$op = (isset($_GET['o']) && $_GET['o']) ? strval($_GET['o']) : '';
$id = (isset($_GET['id']) && $_GET['id']) ? strval($_GET['id']) : '';

$servicios_obj = new Servicios();
$servicio[0]['nro_servicio'] = '';
$servicio[0]['fecha_registro'] = date('Y-m-d');
$servicio[0]['parroquia'] = 'El Hatillo';
$servicio[0]['cuadrante'] = '';
$servicio[0]['direccion_detallada'] = '';
$servicio[0]['resumen_novedad'] = '';
$servicio[0]['tipo_actividad_id'] = '';
$servicio[0]['id'] = '';

if ($op == '' and $id == '') {

	$op = "add";
	$titulo = "Crear Evento";
	$modificar = 1;
} else {

	$servicio = $servicios_obj->Consultar($_DB_, 'id', $id);

	if ($servicio and $op == 'mod') {

		$op = "mod";
		$titulo = "Modificar Evento";
		$modificar = 1;
	} elseif ($servicio and $op == 'del') {

		$op = "del";
		$titulo = "Eliminar Evento";
		$modificar = 1;
	} else {

		$titulo = "Consultar Evento";
		$modificar = 0;
	}
}

include_once(realpath(dirname(__FILE__)) . "/include/header.php");
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
						<? echo $modulo_titulo; ?></h1>
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.container-fluid -->
	</div>

	<? /** /
echo "<pre>";
print_r($id);
echo "</pre>";
/**/
	?>
	<!-- Main content -->
	<section class=" content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-12">

					<form action="eventos_operacion.php" method="POST" enctype="multipart/form-data">
						<input type="hidden" name="op" value="<? echo $op; ?>">
						<input type="hidden" name="id" value="<? echo $servicio[0]['id']; ?>">

						<div class="card card-<? echo $color; ?>">
							<div class="card-header">
								<h3 class="card-title">Datos del Evento</h3>
							</div>
							<div class="card-body">

								<div class="row">

									<div class="col-sm-4">
										<!-- text input -->
										<div class="form-group">
											<b>Código</b>
											<input type="text" name="codigo" value="<? echo $unidades[0]['codigo']; ?>"
												class="form-control" placeholder="" <? if ($modificar) {
													echo 'required';
												} else {
													echo 'readonly';
												} ?>>
										</div>
									</div>

									<div class="col-sm-4">
										<!-- text input -->
										<div class="form-group">
											<b>Descripción</b>
											<input type="text" name="descripcion"
												value="<? echo $unidades[0]['descripcion']; ?>" class="form-control"
												placeholder="" <? if ($modificar) {
												} else {
													echo 'readonly';
												} ?>>
										</div>
									</div>

									<div class="col-sm-4">
										<!-- text input -->
										<div class="form-group">
											<b>Activa</b>


											<select name="activo" class="form-control" <? if ($modificar) {
											} else {
												echo 'readonly';
											} ?>>

												<option value="1" <? if ($unidades[0]['activo'] == 1) {
													echo " selected";
												} ?>>Si</option>
												<option value="0" <? if ($unidades[0]['activo'] == 0) {
													echo " selected";
												} ?>>No</option>
											</select>

										</div>
									</div>

								</div>
							</div>
						</div>





						<div class="row">
							<div class="col-sm-6">
								<div class="text-left">
									<a onclick="history.back();" class="btn btn-default">
										Volver</a>
								</div>
							</div>
							<div class="col-sm-6">
								<?
								if ($modificar) { ?>
									<div class="text-right">
										<?
										if ($op == "add") { ?><button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar
												Evento</button><? } elseif ($op == "mod") { ?><button type="submit"
												class="btn btn-warning"><i class="fas fa-pen"></i> Modificar
												Evento</button><? } elseif ($op == "del") { ?><button type="submit"
												class="btn btn-danger"><i class="fas fa-trash-alt"></i> Eliminar
												Evento</button>
										<? }
										?>
									</div>
								<?
								} 
								/*
								else {
									if (
										(($_SESSION['usuario']['tipo_usuario'] >= 10) and ($_SESSION['usuario']['tipo_usuario'] < 20)) or
										($_SESSION['usuario']['tipo_usuario'] >= 90)
									) {
										?>

										<div class="text-right">
											<a href="./horarios_gestion.php?o=mod&id=<? echo $id; ?>" type="submit"
												class="btn btn-primary"><i class="fas fa-pen"></i> Modificar Horario</a>
										</div>
									<? }
								}*/
								?>
							</div>
							<br>
						</div>
					</form>
					<br>
				</div>

			</div>
		</div>
	</section>
</div>


<?php
include_once(realpath(dirname(__FILE__)) . "/include/footer.php");
?>

<!-- jQuery -->
<script src="./plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Select2 -->
<script src="./plugins/select2/js/select2.full.min.js"></script>
<!-- Bootstrap4 Duallistbox -->
<script src="./plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script>
<!-- InputMask -->
<script src="./plugins/moment/moment.min.js"></script>
<script src="./plugins/inputmask/min/jquery.inputmask.bundle.min.js"></script>
<!-- date-range-picker -->
<script src="./plugins/daterangepicker/daterangepicker.js"></script>
<!-- bootstrap color picker -->
<script src="./plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="./plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Bootstrap Switch -->
<script src="./plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<!-- AdminLTE App -->
<script src="./dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="./dist/js/demo.js"></script>
<!-- Page script -->
<script>
	$(function () {
		//Initialize Select2 Elements
		$('.select2').select2()

		//Initialize Select2 Elements
		$('.select2bs4').select2({
			theme: 'bootstrap4'
		})

		//Datemask dd/mm/yyyy
		$('#datemask').inputmask('dd-mm-yyyy', {
			'placeholder': 'dd-mm-yyyy'
		})
		//Datemask2 mm/dd/yyyy
		$('#datemask2').inputmask('mm-dd-yyyy', {
			'placeholder': 'mm-dd-yyyy'
		})
		//Money Euro
		$('[data-mask]').inputmask()

		//Date range picker
		$('#reservationdate').datetimepicker({
			format: 'L'
		});
		//Date range picker
		$('#reservation').daterangepicker()
		//Date range picker with time picker
		$('#reservationtime').daterangepicker({
			timePicker: true,
			timePickerIncrement: 30,
			locale: {
				format: 'MM/DD/YYYY hh:mm A'
			}
		})
		//Date range as a button
		$('#daterange-btn').daterangepicker({
			ranges: {
				'Today': [moment(), moment()],
				'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
				'Last 7 Days': [moment().subtract(6, 'days'), moment()],
				'Last 30 Days': [moment().subtract(29, 'days'), moment()],
				'This Month': [moment().startOf('month'), moment().endOf('month')],
				'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
			},
			startDate: moment().subtract(29, 'days'),
			endDate: moment()
		},
			function (start, end) {
				$('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'))
			}
		)

		//Timepicker
		$('#timepicker').datetimepicker({
			format: 'LT'
		})

		//Bootstrap Duallistbox
		$('.duallistbox').bootstrapDualListbox()

		//Colorpicker
		$('.my-colorpicker1').colorpicker()
		//color picker with addon
		$('.my-colorpicker2').colorpicker()

		$('.my-colorpicker2').on('colorpickerChange', function (event) {
			$('.my-colorpicker2 .fa-square').css('color', event.color.toString());
		});

		$("input[data-bootstrap-switch]").each(function () {
			$(this).bootstrapSwitch('state', $(this).prop('checked'));
		});

	})
</script>
</body>

</html>